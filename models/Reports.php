<?php

class Reports
{

    public function getundeliveredtrailers(&$db) {
          $dbhandle = new $db('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS);
          $result = $dbhandle->query("select *, order_statuses.status as statusesstatus
                                     from order_details
                                     join order_statuses on order_statuses.orderDetailID = order_details.id
                                     left join entities on entities.id = order_details.carrierID
                                     where order_details.status = 'Open'");

          if (count($result) > 0) {
              echo "{ \"order_details\":".json_encode($result->fetchAll()) . "}";
          } else {
              echo '{}';
          }
    }

    public function getundeliveredtrailerscsv(&$db) {
          $dbhandle = new $db('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS);
          $result = $dbhandle->query("select *, order_statuses.status as statusesstatus
                                     from order_details
                                     join order_statuses on order_statuses.orderDetailID = order_details.id
                                     left join entities on entities.id = order_details.carrierID
                                     where order_details.status = 'Open'");

          if (count($result) > 0) {
              $records = $result->fetchAll();
              $data = "";
              foreach($records as $record) {
                  $location = $record['city'] . " " . $record['state'];
                  $data .= $record['orderID'].",".$record['name'].",".$record['vinNumber'].",".$location.",".$record['statusesstatus']."\n";
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

          $currentData = $result->fetchAll();
          for ($c = 0; $c < count($currentData); $c++) {
                $currentRevenue += $currentData[$c]['totalRevenue'];
                $currentPayout += $currentData[$c]['carrierTotalRate'];
          }
          $currentDifference += $currentRevenue - $currentPayout;

          $returnArray = json_encode(array('weekTitle' => 'Current Week', 'revenue' => $currentRevenue, 'payout' => $currentPayout, 'difference' => $currentDifference));

          /* Previous Week */
          $result = $dbhandle->query("SELECT * FROM approved_pod
                                        WHERE createdAt >= CURDATE() - INTERVAL DAYOFWEEK(CURDATE())+6 DAY
                                        AND createdAt < CURDATE() - INTERVAL DAYOFWEEK(CURDATE())-1 DAY");

          $previousData = $result->fetchAll();
          for ($c = 0; $c < count($previousData); $c++) {
                $previousRevenue += $previousData[$c]['totalRevenue'];
                $previousPayout += $previousData[$c]['carrierTotalRate'];
          }
          $previousDifference += ($previousRevenue - $previousPayout);

          $returnArray .= "," . json_encode(array('weekTitle' => 'Previous Week', 'revenue' => $previousRevenue, 'payout' => $previousPayout, 'difference' => $previousDifference));

          /* This Month */
          $result = $dbhandle->query("SELECT * FROM approved_pod WHERE MONTH(`createdAt`)=MONTH(NOW()) AND YEAR(`createdAt`)=YEAR(NOW())");

          $monthData = $result->fetchAll();
          for ($c = 0; $c < count($monthData); $c++) {
                $monthRevenue += $monthData[$c]['totalRevenue'];
                $monthPayout += $monthData[$c]['carrierTotalRate'];
          }
          $monthDifference += $monthRevenue - $monthPayout;

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

          $currentData = $result->fetchAll();
          for ($c = 0; $c < count($currentData); $c++) {
                $currentRevenue += $currentData[$c]['totalRevenue'];
                $currentPayout += $currentData[$c]['carrierTotalRate'];
          }
          $currentDifference += $currentRevenue - $currentPayout;

          $data .= "Current Week,".$currentRevenue.",".$currentPayout.",".$currentDifference."\n";

          /* Previous Week */
          $result = $dbhandle->query("SELECT * FROM approved_pod
                                        WHERE createdAt >= CURDATE() - INTERVAL DAYOFWEEK(CURDATE())+6 DAY
                                        AND createdAt < CURDATE() - INTERVAL DAYOFWEEK(CURDATE())-1 DAY");

          $previousData = $result->fetchAll();
          for ($c = 0; $c < count($previousData); $c++) {
                $previousRevenue += $previousData[$c]['totalRevenue'];
                $previousPayout += $previousData[$c]['carrierTotalRate'];
          }
          $previousDifference += ($previousRevenue - $previousPayout);

          $data .= "Previous Week,".$previousRevenue.",".$previousPayout.",".$previousDifference."\n";

          /* This Month */
          $result = $dbhandle->query("SELECT * FROM approved_pod WHERE MONTH(`createdAt`)=MONTH(NOW()) AND YEAR(`createdAt`)=YEAR(NOW())");

          $monthData = $result->fetchAll();
          for ($c = 0; $c < count($monthData); $c++) {
                $monthRevenue += $monthData[$c]['totalRevenue'];
                $monthPayout += $monthData[$c]['carrierTotalRate'];
          }
          $monthDifference += $monthRevenue - $monthPayout;

          $data .= "Current Month,".$monthRevenue.",".$monthPayout.",".$monthDifference."\n";

          if (!empty($data)) {
              echo $data;
          } else {
              echo '{}';
          }
    }

}
