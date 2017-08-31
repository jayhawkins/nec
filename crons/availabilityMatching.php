<?php

date_default_timezone_set('America/New_York');

require '../vendor/autoload.php';

require '../../nec_config.php';

require '../lib/common.php';

require '../models/CustomerNeeds.php';
$customerneed = new CustomerNeed();

$needsMatchedArray = array();

//$date = new DateTime(date("Y-m-d 00:00:00"));
//$date->sub(new DateInterval('P30D')); // Get anything older than 30 days ago to look at

$date = date("Y-m-d 00:00:00");

// Get availability created today
$args = array(
    "transform"=>1,
    "filter[0]"=>"availableDate,gt," . $date,
    "filter[1]"=>"status,eq,Available"
);
$url = API_HOST."/api/customer_needs?".http_build_query($args);
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'GET'
    )
);
$context  = stream_context_create($options);
$result = json_decode(file_get_contents($url,false,$context),true);

if (count($result) > 0) {
      try {
          for ($cn=0;$cn<count($result['customer_needs']);$cn++) {

                $id = $result['customer_needs'][$cn]['id'];
                //echo $id . "<br />\n";
                //echo $result['customer_needs'][$cn]['createdAt'] . "<br />\n";
                //echo $result['customer_needs'][$cn]['originationState'] . "<br />\n";
                $matchingresult = $customerneed->availabilityMatching(API_HOST,$id);

          }
      } catch (Exception $e) {
        return $e;
      }
}

// Get new needs_match created today and send notifications
$enddate = date("Y-m-d 23:59:59");
$args = array(
    "transform"=>1,
    "filter[0]"=>"createdAt,gt," . $date,
    "filter[1]"=>"createdAt,lt," . $enddate,
    "filter[2]"=>"status,eq,Matched",
    "satisfy"=>"all"
);
$url = API_HOST."/api/needs_match?".http_build_query($args);
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'GET'
    )
);
$context  = stream_context_create($options);
$result = json_decode(file_get_contents($url,false,$context),true);

if (count($result) > 0) {
      try {
          for ($nm=0;$nm<count($result['needs_match']);$nm++) {

                $id = $result['needs_match'][$nm]['id'];
                //echo "ID being passed to notification function: " . $id . "<br />\n";
                $emailresult = $customerneed->sendNeedsMatchNotification(API_HOST, $id);

          }
      } catch (Exception $e) {
        return $e;
      }
}

