<?php

class Reports
{

    public function getdeliveredtrailers(&$db, $entitytype, $entityid) {

          $returnArray = "";

          $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);
          $querystring = "select *, order_statuses.status as statusesstatus, orders.customerID as custID
                                     from order_details
                                     join order_statuses on order_statuses.orderDetailID = order_details.id
                                     left join orders on orders.id = order_details.orderID
                                     left join entities on entities.id = order_details.carrierID
                                     where order_details.status = 'Open'
                                     and order_statuses.status = 'Trailer Delivered'";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and order_details.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by order_details.createdAt desc";

          $result = $dbhandle->query($querystring);

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

    public function getdeliveredtrailerscsv(&$db, $entitytype, $entityid) {
          $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);
          $querystring = "select *, order_statuses.status as statusesstatus, orders.customerID as custID
                                     from order_details
                                     join order_statuses on order_statuses.orderDetailID = order_details.id
                                     left join orders on orders.id = order_details.orderID
                                     left join entities on entities.id = order_details.carrierID
                                     where order_details.status = 'Open'
                                     and order_statuses.status = 'Trailer Delivered'";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and order_details.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by order_details.createdAt desc";

          $result = $dbhandle->query($querystring);

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

    public function getundeliveredtrailers(&$db, $entitytype, $entityid) {

          $returnArray = "";

          $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);
          $querystring = "select *, order_statuses.status as statusesstatus, orders.customerID as custID
                                     from order_details
                                     join order_statuses on order_statuses.orderDetailID = order_details.id
                                     left join orders on orders.id = order_details.orderID
                                     left join entities on entities.id = order_details.carrierID
                                     where order_details.status = 'Open'
                                     and order_statuses.status != 'Trailer Delivered'";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and order_details.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by order_details.createdAt desc";

          $result = $dbhandle->query($querystring);

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

    public function getundeliveredtrailerscsv(&$db, $entitytype, $entityid) {
          $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);
          $querystring = "select *, order_statuses.status as statusesstatus, orders.customerID as custID
                                     from order_details
                                     join order_statuses on order_statuses.orderDetailID = order_details.id
                                     left join orders on orders.id = order_details.orderID
                                     left join entities on entities.id = order_details.carrierID
                                     where order_details.status = 'Open'
                                     and order_statuses.status != 'Trailer Delivered'";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and order_details.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by order_details.createdAt desc";

          $result = $dbhandle->query($querystring);

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

    public function getarsummary(&$db, $entitytype, $entityid) {

          $currentRevenue = 0;
          $currentPayout = 0;
          $currentDifference = 0;
          $previousRevenue = 0;
          $previousPayout = 0;
          $previousDifference = 0;
          $monthRevenue = 0;
          $monthPayout = 0;
          $monthDifference = 0;

          $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);

          /* Current Week */
          $querystring = "SELECT *
                                     FROM approved_pod
                                     JOIN `orders` on orders.id = approved_pod.orderDetailID
                                     WHERE approved_pod.createdAt BETWEEN CURDATE()-INTERVAL 1 WEEK AND CURDATE()";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and approved_pod.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by approved_pod.createdAt desc";

          $result = $dbhandle->query($querystring);
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
          $querystring = "SELECT * FROM approved_pod
                                        JOIN `orders` on orders.id = approved_pod.orderDetailID
                                        WHERE approved_pod.createdAt >= CURDATE() - INTERVAL DAYOFWEEK(CURDATE())+6 DAY
                                        AND approved_pod.createdAt < CURDATE() - INTERVAL DAYOFWEEK(CURDATE())-1 DAY";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and approved_pod.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by approved_pod.createdAt desc";

          $result = $dbhandle->query($querystring);

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
          $querystring = "SELECT * FROM approved_pod
                                      JOIN `orders` on orders.id = approved_pod.orderDetailID
                                      WHERE MONTH(approved_pod.createdAt)=MONTH(NOW()) AND YEAR(approved_pod.createdAt)=YEAR(NOW())";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and approved_pod.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by approved_pod.createdAt desc";

          $result = $dbhandle->query($querystring);

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

    public function getarsummarycsv(&$db, $entitytype, $entityid) {
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

          $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);

          /* Current Week */
          $querystring = "SELECT *
                                     FROM approved_pod
                                     JOIN `orders` on orders.id = approved_pod.orderDetailID
                                     WHERE approved_pod.createdAt BETWEEN CURDATE()-INTERVAL 1 WEEK AND CURDATE()";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and approved_pod.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by approved_pod.createdAt desc";

          $result = $dbhandle->query($querystring);

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
          $querystring = "SELECT * FROM approved_pod
                                        JOIN `orders` on orders.id = approved_pod.orderDetailID
                                        WHERE approved_pod.createdAt >= CURDATE() - INTERVAL DAYOFWEEK(CURDATE())+6 DAY
                                        AND approved_pod.createdAt < CURDATE() - INTERVAL DAYOFWEEK(CURDATE())-1 DAY";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and approved_pod.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by approved_pod.createdAt desc";

          $result = $dbhandle->query($querystring);

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
          $querystring = "SELECT * FROM approved_pod
                                      JOIN `orders` on orders.id = approved_pod.orderDetailID
                                      WHERE MONTH(approved_pod.createdAt)=MONTH(NOW()) AND YEAR(approved_pod.createdAt)=YEAR(NOW())";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and approved_pod.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by approved_pod.createdAt desc";

          $result = $dbhandle->query($querystring);

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

    public function getardetail(&$db,$startDate,$endDate,$entitytype,$entityid) {

          $returnArray = "";

          $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);

          /* Get approved_pod records */
          $querystring = "SELECT approved_pod.orderID, approved_pod.orderDetailID, approved_pod.carrierID, approved_pod.cost, approved_pod.qbInvoiceNumber, approved_pod.qbInvoiceStatus, entities.name
                                     FROM approved_pod
                                     JOIN `orders` on orders.id = approved_pod.orderID
                                     LEFT JOIN `entities` on entities.id = approved_pod.customerID
                                     WHERE approved_pod.createdAt BETWEEN '" . $startDate . "' AND '" . $endDate . "'";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and approved_pod.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by approved_pod.createdAt desc";

          $result = $dbhandle->query($querystring);

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
                    $detailsResult = $dbhandle->query("SELECT order_details.carrierRate, order_details.qty, entities.name
                                                        FROM order_details
                                                        JOIN entities on entities.id = order_details.carrierID
                                                        WHERE order_details.carrierID = '" . $approvedPodData[$c]['carrierID'] . "'
                                                        AND order_details.orderID = '" . $approvedPodData[$c]['orderID'] . "'
                                                        AND order_details.id = '" . $approvedPodData[$c]['orderDetailID'] . "'");
                    $costToCarrier = 0;
                    $carrierName = "";
                    $detailsData = $detailsResult->fetchAll();
                    for ($d = 0; $d < count($detailsData); $d++) {
                        $carrierName = $detailsData[$d]['name'];
                        if ($detailsData[$d]['qty'] > 0) {
                            $costToCarrier = $detailsData[$d]['carrierRate'] / $detailsData[$d]['qty'];
                        }

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

    public function getardetailcsv(&$db,$startDate,$endDate,$entitytype,$entityid) {

          $data = "Date Range,".$startDate.",".$endDate."\n";
          $data .= "Order ID,Customer Name,Carrier Name,Cost To Customer,Cost To Carrier,QB Invoice #,QB Status\n";

          $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);

          /* Get approved_pod records */
          $querystring = "SELECT approved_pod.orderID, approved_pod.orderDetailID, approved_pod.carrierID, approved_pod.cost, approved_pod.qbInvoiceNumber, approved_pod.qbInvoiceStatus, entities.name
                                     FROM approved_pod
                                     JOIN `orders` on orders.id = approved_pod.orderID
                                     LEFT JOIN `entities` on entities.id = approved_pod.customerID
                                     WHERE approved_pod.createdAt BETWEEN '" . $startDate . "' AND '" . $endDate . "'";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and approved_pod.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by approved_pod.createdAt desc";

          $result = $dbhandle->query($querystring);

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
                    $detailsResult = $dbhandle->query("SELECT order_details.carrierRate, order_details.qty, entities.name
                                                        FROM order_details
                                                        JOIN entities on entities.id = order_details.carrierID
                                                        WHERE order_details.carrierID = '" . $approvedPodData[$c]['carrierID'] . "'
                                                        AND order_details.orderID = '" . $approvedPodData[$c]['orderID'] . "'
                                                        AND order_details.id = '" . $approvedPodData[$c]['orderDetailID'] . "'");
                    $costToCarrier = 0;
                    $carrierName = "";
                    $detailsData = $detailsResult->fetchAll();
                    for ($d = 0; $d < count($detailsData); $d++) {
                        $carrierName = $detailsData[$d]['name'];
                        if ($detailsData[$d]['qty'] > 0) {
                            $costToCarrier = $detailsData[$d]['carrierRate'] / $detailsData[$d]['qty'];
                        }

                    }

                    $data .= $orderID.",".$customerName.",".$carrierName.",".$costToCustomer.",".$costToCarrier.",".$qbInvoiceNumber.",".$qbInvoiceStatus."\n";

              }
          }

          if ($data) {
              echo $data;
          } else {
              echo '{}';
          }
    }

    public function getrevenueanalysis(&$db,$startDate,$endDate,$entitytype,$entityid) {

          $returnArray = "";
          $entityArray = array();

          $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);

          /* Get Entities for Report */
          $querystring = "SELECT entities.id, entities.name, entities.rateType, entities.negotiatedRate
                          FROM entities";

          if ($entityid > 0) {
              $querystring .= " WHERE id = '" . $entityid . "'";
          } else {
              $querystring .= " WHERE entityTypeID = '1'";
          }

          $querystring .= " ORDER BY entities.name";

          $result = $dbhandle->query($querystring);

          if (count($result) > 0) {
              $entityData = $result->fetchAll();
              foreach ($entityData as $entityRow) {
                  $entityArray[$entityRow['id']]['name'] = $entityRow['name'];
                  $entityArray[$entityRow['id']]['rateType'] = $entityRow['rateType'];
                  $entityArray[$entityRow['id']]['negotiatedRate'] = $entityRow['negotiatedRate'];
                  $entityArray[$entityRow['id']]['potentialSales'] = 0; // Set up for the Customer to keep track below
                  $entityArray[$entityRow['id']]['actualSales'] = 0; // Set up for the Customer to keep track below
                  $entityArray[$entityRow['id']]['repairCosts'] = 0; // Set up for the Customer to keep track below
                  $entityArray[$entityRow['id']]['totalRevenue'] = 0; // Set up for the Customer to keep track below
              }
          }

          /* Get Potential Sales */
          $querystring = "SELECT customer_needs.entityID, customer_needs.distance, customer_needs.qty, customer_needs.rate, customer_needs.rateType
                          FROM customer_needs
                          WHERE customer_needs.availableDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " AND customer_needs.entityID = '" . $entityid . "'";
              }
          }

          $querystring .= " ORDER BY customer_needs.availableDate desc";

          $result = $dbhandle->query($querystring);

          if (count($result) > 0) {
              $needsData = $result->fetchAll();
              foreach ($needsData as $needs) {
                    $potentialSales = 0;
                    if (!isset($entityArray[$needs['entityID']]['potentialSales'])) {
                        $entityArray[$needs['entityID']]['potentialSales'] = 0;
                    }
                    if ($needs['rateType'] == "Flat Rate") {
                        $potentialSales = $needs['rate'];
                    } else {
                        $potentialSales = ($needs['rate'] * $needs['qty']) * $needs['distance'];
                    }
                    $entityArray[$needs['entityID']]['potentialSales'] += $potentialSales;

              }
          }

          /* Get Actual Sales */
          $querystring = "SELECT approved_pod.orderID, approved_pod.orderDetailID, approved_pod.cost, entities.id
                          FROM approved_pod
                          JOIN `orders` on orders.id = approved_pod.orderID
                          LEFT JOIN `entities` on entities.id = approved_pod.customerID
                          WHERE approved_pod.createdAt BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                          AND approved_pod.hasBeenInvoiced = 1";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " AND orders.customerID = '" . $entityid . "'";
              }
          }

          $querystring .= " ORDER BY approved_pod.createdAt desc";

          $result = $dbhandle->query($querystring);

          if (count($result) > 0) {
              $approvedPodData = $result->fetchAll();
              foreach ($approvedPodData as $approvedData) {
                    $actualSales = 0;
                    if (!isset($entityArray[$approvedData['id']]['actualSales'])) {
                        $entityArray[$approvedData['id']]['actualSales'] = 0;
                    }

                    $entityArray[$approvedData['id']]['actualSales'] += approvedData['cost'];
              }
          }

          /* Get Damage Claims */
          $querystring = "SELECT entityID, negotiatedRepairCost
                          FROM damage_claims
                          WHERE damage_claims.createdAt BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                          AND damage_claims.status = 'Active'";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " AND damage_claims.entityID = '" . $entityid . "'";
              }
          }

          $querystring .= " ORDER BY damage_claims.createdAt desc";

          $result = $dbhandle->query($querystring);

          if (count($result) > 0) {
              $damageClaimData = $result->fetchAll();
              foreach ($damageClaimData as $damageClaim) {
                    $damageAmount = 0;
                    if (!isset($entityArray[$damageClaim['entityID']]['cost']) && isset($entityArray[$damageClaim['entityID']]['name'])) {
                        $entityArray[$damageClaim['entityID']]['repairCosts'] = 0;
                    }

                    if (isset($entityArray[$damageClaim['entityID']]['name'])) {
                        $entityArray[$damageClaim['entityID']]['repairCosts'] += $damageClaim['negotiatedRepairCost'];
                    }
              }
          }

          // Setup to return $entityArray as a JSON object array
          $counter = 0;
          foreach($entityArray as $k => $v) {
            $jcounter = 0;
            $returnArray .= "{";
            foreach($v as $key => $value) {
                if ($jcounter > 0) {
                    $returnArray .= ", ";
                }
                $returnArray .= "\"{$key}\": \"{$value}\"";
                $jcounter++;

            }

            $returnArray .= "}";

            if ($counter < count($entityArray) - 1) {
                $returnArray .= ",";
                $counter++;
            }

          }

          $return = "{ \"analysis\": [".$returnArray."]}";

          if ($return) {
              echo $return;
          } else {
              echo '{}';
          }
    }

    public function getrevenueanalysiscsv(&$db,$startDate,$endDate,$entitytype,$entityid) {

          $data = "Date Range,".$startDate.",".$endDate."\n";
          $data .= "Order ID,Customer Name,Carrier Name,Cost To Customer,Cost To Carrier,QB Invoice #,QB Status\n";

          $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);

          /* Get approved_pod records */
          $querystring = "SELECT approved_pod.orderID, approved_pod.orderDetailID, approved_pod.carrierID, approved_pod.cost, approved_pod.qbInvoiceNumber, approved_pod.qbInvoiceStatus, entities.name
                                     FROM approved_pod
                                     JOIN `orders` on orders.id = approved_pod.orderID
                                     LEFT JOIN `entities` on entities.id = approved_pod.customerID
                                     WHERE approved_pod.createdAt BETWEEN '" . $startDate . "' AND '" . $endDate . "'";

          if ($entityid > 0) {
              if ($entitytype == 1) {
                  $querystring .= " and orders.customerID = '" . $entityid . "'";
              } else {
                  $querystring .= " and approved_pod.carrierID = '" . $entityid . "'";
              }
          }

          $querystring .= " order by approved_pod.createdAt desc";

          $result = $dbhandle->query($querystring);

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
                    $detailsResult = $dbhandle->query("SELECT order_details.carrierRate, order_details.qty, entities.name
                                                        FROM order_details
                                                        JOIN entities on entities.id = order_details.carrierID
                                                        WHERE order_details.carrierID = '" . $approvedPodData[$c]['carrierID'] . "'
                                                        AND order_details.orderID = '" . $approvedPodData[$c]['orderID'] . "'
                                                        AND order_details.id = '" . $approvedPodData[$c]['orderDetailID'] . "'");
                    $costToCarrier = 0;
                    $carrierName = "";
                    $detailsData = $detailsResult->fetchAll();
                    for ($d = 0; $d < count($detailsData); $d++) {
                        $carrierName = $detailsData[$d]['name'];
                        if ($detailsData[$d]['qty'] > 0) {
                            $costToCarrier = $detailsData[$d]['carrierRate'] / $detailsData[$d]['qty'];
                        }

                    }

                    $data .= $orderID.",".$customerName.",".$carrierName.",".$costToCustomer.",".$costToCarrier.",".$qbInvoiceNumber.",".$qbInvoiceStatus."\n";

              }
          }

          if ($data) {
              echo $data;
          } else {
              echo '{}';
          }
    }

    public function getavailabilitywithnocommits(&$db, $entitytype, $entityid) {

        try {
              $returnArray = "";

              $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);
              $querystring = "SELECT *
                              FROM customer_needs
                              WHERE id NOT IN (SELECT customerNeedsID FROM customer_needs_commit)
                              AND status = 'Available'
                              AND expirationDate < NOW()";

              if ($entityid > 0) {
                  if ($entitytype == 1) {
                      $querystring .= " AND entityID = '" . $entityid . "'";
                  }
              }

              $querystring .= " ORDER BY createdAt desc";

              $result = $dbhandle->query($querystring);

              if (count($result) > 0) {
                  $data = $result->fetchAll();
                  for ($c = 0; $c < count($data); $c++) {
                        $customerName = "*UNAVAILABLE*";
                        /* Get carrier name for approved_pod record */
                        $entitiesResult = $dbhandle->query("SELECT name FROM entities WHERE id = '" . $data[$c]['entityID'] . "'");

                        $entitiesData = $entitiesResult->fetchAll();
                        for ($e = 0; $e < count($entitiesData); $e++) {
                            $customerName = $entitiesData[$e]['name'];
                        }

                        $returnArray .= json_encode(array('customerName' => $customerName, 'originationCity' => $data[$c]['originationCity'], 'originationState' => $data[$c]['originationState'], 'destinationCity' => $data[$c]['destinationCity'], 'destinationState' => $data[$c]['destinationState'], 'availableDate' => $data[$c]['availableDate'], 'expirationDate' => $data[$c]['expirationDate'], 'distance' => $data[$c]['distance']));

                        if ($c < count($data) - 1) {
                            $returnArray .= ",";
                        }

                  }
                  echo "{ \"customer_needs\": [".$returnArray."]}";
              } else {
                  echo '{}';
              }
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getavailabilitywithnocommitscsv(&$db, $entitytype, $entityid) {

        try {
              $data = "Customer Name,Origination City,Origination State,Destination City,Destination State,Available Date,Expiration Date,Distance\n";

              $returnArray = "";

              $dbhandle = new $db('mysql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);
              $querystring = "SELECT *
                              FROM customer_needs
                              WHERE id NOT IN (SELECT customerNeedsID FROM customer_needs_commit)
                              AND status = 'Available'
                              AND expirationDate < NOW()";

              if ($entityid > 0) {
                  if ($entitytype == 1) {
                      $querystring .= " AND entityID = '" . $entityid . "'";
                  }
              }

              $querystring .= " ORDER BY createdAt desc";

              $result = $dbhandle->query($querystring);

              if (count($result) > 0) {
                  $data = $result->fetchAll();
                  for ($c = 0; $c < count($data); $c++) {
                        $customerName = "*UNAVAILABLE*";
                        /* Get carrier name for approved_pod record */
                        $entitiesResult = $dbhandle->query("SELECT name FROM entities WHERE id = '" . $data[$c]['entityID'] . "'");

                        $entitiesData = $entitiesResult->fetchAll();
                        for ($e = 0; $e < count($entitiesData); $e++) {
                            $customerName = $entitiesData[$e]['name'];
                        }

                        $data .= $customerName.",".$entitiesData[$c]['originationCity'].",".$entitiesData[$c]['originationState'].",".$entitiesData[$c]['destinationCity'].",".$entitiesData[$c]['destinationState'].",".$entitiesData[$c]['availableDate'].",".$entitiesData[$c]['expirationDate'].",".$entitiesData[$c]['distance']."\n";

                  }
                  echo $data;
              } else {
                  echo '{}';
              }
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }

}
