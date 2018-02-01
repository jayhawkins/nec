<?php

class Reports
{

    public function getundeliveredtrailers(&$db) {

          $returnArray = "";

          $dbhandle = new $db('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS);
          $result = $dbhandle->query("select *, order_statuses.status as statusesstatus, orders.customerID as custID
                                     from order_details
                                     join order_statuses on order_statuses.orderDetailID = order_details.id
                                     left join orders on orders.id = order_details.orderID
                                     left join entities on entities.id = order_details.carrierID
                                     where order_details.status = 'Open'");

          if (count($result) > 0) {
              $data = $result->fetchAll();
              for ($c = 0; $c < count($data); $c++) {
                    /* Get carrier name for approved_pod record */
                    $entitiesResult = $dbhandle->query("SELECT name FROM entities WHERE id = '" . $data[$c]['custID'] . "'");

                    $entitiesData = $entitiesResult->fetchAll();
                    for ($e = 0; $e < count($entitiesData); $e++) {
                        $customerName = $entitiesData[$e]['name'];
                    }

                    $returnArray .= json_encode(array('customerName' => $customerName, 'carrierName' => $data[$c]['name'], 'orderID' => $data[$c]['orderID'], 'unitNumber' => $data[$c]['unitNumber'], 'vinNumber' => $data[$c]['vinNumber'], 'city' => $data[$c]['city'], 'state' => $data[$c]['state'], 'statusesstatus' => $data[$c]['statusesstatus']));

                    if ($c < count($data) - 1) {
                        $returnArray .= ",";
                    }

              }
              echo "{ \"order_details\": [".$returnArray."]}";
          } else {
              echo '{}';
          }
    }

    public function getundeliveredtrailerscsv(&$db) {
          $dbhandle = new $db('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS);
          $result = $dbhandle->query("select *, order_statuses.status as statusesstatus, orders.customerID as custID
                                     from order_details
                                     join order_statuses on order_statuses.orderDetailID = order_details.id
                                     left join orders on orders.id = order_details.orderID
                                     left join entities on entities.id = order_details.carrierID
                                     where order_details.status = 'Open'");

          if (count($result) > 0) {
              $records = $result->fetchAll();
              $data = "Order ID,Customer Name,Carrier Name,Unit Number,VIN,Location, Status\n";
              foreach($records as $record) {
                  /* Get carrier name for approved_pod record */
                  $entitiesResult = $dbhandle->query("SELECT name FROM entities WHERE id = '" . $record['custID'] . "'");

                  $entitiesData = $entitiesResult->fetchAll();
                  for ($e = 0; $e < count($entitiesData); $e++) {
                       $customerName = $entitiesData[$e]['name'];
                  }
                  $location = $record['city'] . " " . $record['state'];
                  $data .= $record['orderID'].",".$customerName.",".$record['name'].",".$record['unitNumber'].",".$record['vinNumber'].",".$location.",".$record['statusesstatus']."\n";
              }
              echo $data;
          } else {
              echo 'No records found that match criteria';
          }
    }

    public function getarsummary(&$db) {

          $currentRevenue = 0;
          $currentPayout = 0;
          $currentDifference = 0;
          $previousRevenue = 0;
          $previousPayout = 0;
          $previousDifference = 0;
          $monthRevenue = 0;
          $monthPayout = 0;
          $monthDifference = 0;

          $dbhandle = new $db('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS);

          /* Current Week */
          $result = $dbhandle->query("SELECT *
                                     FROM approved_pod
                                     JOIN `orders` on orders.id = approved_pod.orderDetailID
                                     WHERE approved_pod.createdAt BETWEEN CURDATE()-INTERVAL 1 WEEK AND CURDATE()");
          if (count($result) > 0) {
              $currentData = $result->fetchAll();
              for ($c = 0; $c < count($currentData); $c++) {
                    $currentRevenue += $currentData[$c]['totalRevenue'];
                    $currentPayout += $currentData[$c]['carrierTotalRate'];
              }
              $currentDifference += $currentRevenue - $currentPayout;
          }

          $returnArray = json_encode(array('weekTitle' => 'Current Week', 'revenue' => $currentRevenue, 'payout' => $currentPayout, 'difference' => $currentDifference));

          /* Previous Week */
          $result = $dbhandle->query("SELECT * FROM approved_pod
                                        JOIN `orders` on orders.id = approved_pod.orderDetailID
                                        WHERE approved_pod.createdAt >= CURDATE() - INTERVAL DAYOFWEEK(CURDATE())+6 DAY
                                        AND approved_pod.createdAt < CURDATE() - INTERVAL DAYOFWEEK(CURDATE())-1 DAY");

          if (count($result) > 0) {
              $previousData = $result->fetchAll();
              for ($c = 0; $c < count($previousData); $c++) {
                    $previousRevenue += $previousData[$c]['totalRevenue'];
                    $previousPayout += $previousData[$c]['carrierTotalRate'];
              }
              $previousDifference += ($previousRevenue - $previousPayout);
          }

          $returnArray .= "," . json_encode(array('weekTitle' => 'Previous Week', 'revenue' => $previousRevenue, 'payout' => $previousPayout, 'difference' => $previousDifference));

          /* This Month */
          $result = $dbhandle->query("SELECT * FROM approved_pod
                                      JOIN `orders` on orders.id = approved_pod.orderDetailID
                                      WHERE MONTH(approved_pod.createdAt)=MONTH(NOW()) AND YEAR(approved_pod.createdAt)=YEAR(NOW())");

          if (count($result) > 0) {
              $monthData = $result->fetchAll();
              for ($c = 0; $c < count($monthData); $c++) {
                    $monthRevenue += $monthData[$c]['totalRevenue'];
                    $monthPayout += $monthData[$c]['carrierTotalRate'];
              }
              $monthDifference += $monthRevenue - $monthPayout;
          }

          $returnArray .= "," . json_encode(array('weekTitle' => 'This Month', 'revenue' => $monthRevenue, 'payout' => $monthPayout, 'difference' => $monthDifference));

          $return = "{ \"approved_pod\": [".$returnArray."]}";

          if ($return) {
              echo $return;
          } else {
              echo '{}';
          }
    }

    public function getarsummarycsv(&$db) {
          $currentRevenue = 0;
          $currentPayout = 0;
          $currentDifference = 0;
          $previousRevenue = 0;
          $previousPayout = 0;
          $previousDifference = 0;
          $monthRevenue = 0;
          $monthPayout = 0;
          $monthDifference = 0;
          $data = "A/R Summary,,,\n\n";
          $data .= ",Revenue,Payout,Difference\n";

          $dbhandle = new $db('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS);

          /* Current Week */
          $result = $dbhandle->query("SELECT *
                                     FROM approved_pod
                                     JOIN `orders` on orders.id = approved_pod.orderDetailID
                                     WHERE approved_pod.createdAt BETWEEN CURDATE()-INTERVAL 1 WEEK AND CURDATE()");

          if (count($result) > 0) {
              $currentData = $result->fetchAll();
              for ($c = 0; $c < count($currentData); $c++) {
                    $currentRevenue += $currentData[$c]['totalRevenue'];
                    $currentPayout += $currentData[$c]['carrierTotalRate'];
              }
              $currentDifference += $currentRevenue - $currentPayout;
          }

          $data .= "Current Week,".$currentRevenue.",".$currentPayout.",".$currentDifference."\n";

          /* Previous Week */
          $result = $dbhandle->query("SELECT * FROM approved_pod
                                        JOIN `orders` on orders.id = approved_pod.orderDetailID
                                        WHERE approved_pod.createdAt >= CURDATE() - INTERVAL DAYOFWEEK(CURDATE())+6 DAY
                                        AND approved_pod.createdAt < CURDATE() - INTERVAL DAYOFWEEK(CURDATE())-1 DAY");

          if (count($result) > 0) {
              $previousData = $result->fetchAll();
              for ($c = 0; $c < count($previousData); $c++) {
                    $previousRevenue += $previousData[$c]['totalRevenue'];
                    $previousPayout += $previousData[$c]['carrierTotalRate'];
              }
              $previousDifference += ($previousRevenue - $previousPayout);
          }

          $data .= "Previous Week,".$previousRevenue.",".$previousPayout.",".$previousDifference."\n";

          /* This Month */
          $result = $dbhandle->query("SELECT * FROM approved_pod
                                      JOIN `orders` on orders.id = approved_pod.orderDetailID
                                      WHERE MONTH(approved_pod.createdAt)=MONTH(NOW()) AND YEAR(approved_pod.createdAt)=YEAR(NOW())");

          if (count($result) > 0) {
              $monthData = $result->fetchAll();
              for ($c = 0; $c < count($monthData); $c++) {
                    $monthRevenue += $monthData[$c]['totalRevenue'];
                    $monthPayout += $monthData[$c]['carrierTotalRate'];
              }
              $monthDifference += $monthRevenue - $monthPayout;
          }

          $data .= "Current Month,".$monthRevenue.",".$monthPayout.",".$monthDifference."\n";

          if (!empty($data)) {
              echo $data;
          } else {
              echo '{}';
          }
    }

    public function getardetail(&$db,$startDate,$endDate) {

          $returnArray = "";

          $dbhandle = new $db('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS);

          /* Get approved_pod records */
          $result = $dbhandle->query("SELECT approved_pod.orderID, approved_pod.carrierID, approved_pod.cost, approved_pod.qbInvoiceNumber, approved_pod.qbInvoiceStatus, entities.name
                                     FROM approved_pod
                                     JOIN `orders` on orders.id = approved_pod.orderDetailID
                                     LEFT JOIN `entities` on entities.id = approved_pod.customerID
                                     WHERE approved_pod.createdAt BETWEEN '" . $startDate . "' AND '" . $endDate . "'");

          if (count($result) > 0) {
              $approvedPodData = $result->fetchAll();
              for ($c = 0; $c < count($approvedPodData); $c++) {
                    $orderID = $approvedPodData[$c]['orderID'];
                    $costToCustomer = $approvedPodData[$c]['cost'];
                    $costToCarrier = $approvedPodData[$c]['cost'];
                    $customerName = $approvedPodData[$c]['name'];
                    $qbInvoiceNumber = $approvedPodData[$c]['qbInvoiceNumber'];
                    $qbInvoiceStatus = $approvedPodData[$c]['qbInvoiceStatus'];

                    /* Get carrier name for approved_pod record */
                    $entitiesResult = $dbhandle->query("SELECT name FROM entities WHERE id = '" . $approvedPodData[$c]['qbInvoiceStatus'] . "'");

                    $entitiesData = $entitiesResult->fetchAll();
                    for ($e = 0; $e < count($entitiesData); $e++) {
                        $carrierName = $entitiesData[$e]['name'];
                    }

                    $returnArray .= json_encode(array('orderID' => $orderID, 'customerName' => $customerName, 'carrierName' => $carrierName, 'costToCustomer' => $costToCustomer, 'costToCarrier' => $costToCarrier, 'qbInvoiceNumber' => $qbInvoiceNumber, 'qbStatus' => $qbInvoiceStatus));

                    if ($c < count($approvedPodData) - 1) {
                        $returnArray .= ",";
                    }
              }
          }

          //$returnArray = json_encode(array('orderID' => '59586', 'customerName' => "Trailers Galore", 'carrierName' => "Mac Truck", 'costToCustomer' => 500, 'costToCarrier' => 350, 'qbInvoiceNumber' => "QBNUMBER", 'qbStatus' => "Paid"));

          $return = "{ \"approved_pod\": [".$returnArray."]}";

          if ($return) {
              echo $return;
          } else {
              echo '{}';
          }
    }

    public function getardetailcsv(&$db,$startDate,$endDate) {

          $data = "Date Range,".$startDate.",".$endDate."\n";
          $data .= "Order ID,Customer Name,Carrier Name,Cost To Customer,Cost To Carrier,QB Invoice #,QB Status\n";

          $dbhandle = new $db('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS);

          /* Get approved_pod records */
          $result = $dbhandle->query("SELECT approved_pod.orderID, approved_pod.carrierID, approved_pod.cost, approved_pod.qbInvoiceNumber, approved_pod.qbInvoiceStatus, entities.name
                                     FROM approved_pod
                                     JOIN `orders` on orders.id = approved_pod.orderDetailID
                                     LEFT JOIN `entities` on entities.id = approved_pod.customerID
                                     WHERE approved_pod.createdAt BETWEEN '" . $startDate . "' AND '" . $endDate . "'");

          if (count($result) > 0) {
              $approvedPodData = $result->fetchAll();
              for ($c = 0; $c < count($approvedPodData); $c++) {
                    $orderID = $approvedPodData[$c]['orderID'];
                    $costToCustomer = $approvedPodData[$c]['cost'];
                    $costToCarrier = $approvedPodData[$c]['cost'];
                    $customerName = $approvedPodData[$c]['name'];
                    $qbInvoiceNumber = $approvedPodData[$c]['qbInvoiceNumber'];
                    $qbInvoiceStatus = $approvedPodData[$c]['qbInvoiceStatus'];

                    /* Get carrier name for approved_pod record */
                    $entitiesResult = $dbhandle->query("SELECT name FROM entities WHERE id = '" . $approvedPodData[$c]['qbInvoiceStatus'] . "'");

                    $entitiesData = $entitiesResult->fetchAll();
                    for ($e = 0; $e < count($entitiesData); $e++) {
                        $carrierName = $entitiesData[$e]['name'];
                    }

                    $data .= $orderID.",".$customerName.",".$carrerName.",".$costToCustomer.",".$costToCarrier.",".$qbInvoiceNumber.",".$qbInvoiceStatus."\n";

              }
          }

          if ($data) {
              echo $data;
          } else {
              echo '{}';
          }
    }

}
