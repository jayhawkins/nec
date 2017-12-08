<?php

class Orders
{

    /**
     * The table name
     *
     * @var string
     */
    public $table = "orders";

    /*
    private $rateType;
    private $transportationMode;
    private $originationAddress;
    private $originationCity;
    private $originationState;
    private $originationZip;
    private $destinationAddress;
    private $destinationCity;
    private $destinationState;
    private $destinationZip;
    private $distance;
    private $updatedAt;
    private $orderNumber;
    private $customerID;
    private $podList;
     *
     */

    public function sendEmailNotification($rateType, $transportationMode, $originationAddress, $originationCity, $originationState, $originationZip,
            $destinationAddress, $destinationCity, $destinationState, $destinationZip, $distance, $updatedAt, $orderNumber, $customerID, $podList){

            // Customer Entity
            $customerargs = array(
                "transform"=>1
            );
            $customerurl = API_HOST_URL . "/entities/".$customerID."?".http_build_query($customerargs);
            $customeroptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $customercontext  = stream_context_create($customeroptions);
            $customerresult = json_decode(file_get_contents($customerurl,false,$customercontext),true);

            // Admin Entity
            $entityargs = array(
                "transform"=>1
            );
            $entityurl = API_HOST_URL . "/entities/0?".http_build_query($entityargs);
            $entityoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $entitycontext  = stream_context_create($entityoptions);
            $entityresult = json_decode(file_get_contents($entityurl,false,$entitycontext),true);

            // Customer Contact
            $contactargs = array(
                "transform"=>1
            );
            $contacturl = API_HOST_URL . "/contacts/".$customerresult['assignedMemberID']."?".http_build_query($contactargs);
            $contactoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $contactcontext  = stream_context_create($contactoptions);
            $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);

            // Admin Contact
            $adminargs = array(
                "transform"=>1
            );
            $adminurl = API_HOST_URL . "/contacts/".$entityresult['assignedMemberID']."?".http_build_query($adminargs);
            $adminoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $admincontext  = stream_context_create($adminoptions);
            $adminresult = json_decode(file_get_contents($adminurl,false,$admincontext),true);

            // Setting up Email
            $subject = "Update To Order #" . $orderNumber;
            $from = array("operations@nationwide-equipment.com" => "Nationwide Operations Control Manager");

            $changeList = "Rate Type: " . $rateType . "<br />";
            $changeList .= "Transportation Mode: " . $transportationMode . "<br />";
            $changeList .= "Origination Address: " . $originationAddress . "<br />";
            $changeList .= "Origination City: " . $originationCity . "<br />";
            $changeList .= "Origination State: " . $originationState . "<br />";
            $changeList .= "Origination Zip: " . $originationZip . "<br />";
            $changeList .= "Destination Address: " . $destinationAddress . "<br />";
            $changeList .= "Destination City: " . $destinationCity . "<br />";
            $changeList .= "Destination State: " . $destinationState . "<br />";
            $changeList .= "Destination Zip: " . $destinationZip . "<br />";
            $changeList .= "Distance: " . number_format($distance) . " Miles<br />";

            if(empty($podList) == false) {
                $changeList .= "VIN Numbers:";
                $changeList .= "<ul>";
                foreach($podList as $pod){
                    $changeList .= "<li>" . $pod['vinNumber'] . "</li>";
                }
                $changeList .= "</ul>";
            }

            $this->sendEmailToCustomer($subject, $from, $adminresult, $contactresult, $changeList, $orderNumber);
            $this->sendEmailToAdmin($subject, $from, $adminresult, $customerresult, $changeList, $updatedAt);

            return "Your order has been updated and Nationwide Equipment Control will be notified.";
    }

    private function sendEmailToAdmin($subject, $from, $adminresult, $customerresult, $changeList, $updatedAt){
        try {
                    $to = array($adminresult['emailAddress'] => $adminresult['firstName'] . " " . $adminresult['lastName']);

                    $body = "Customer: " . $customerresult['name'] . "<br /><br />";
                    $body .= "Date of Changes: " . $updatedAt . "<br /><br />";
                    $body .= "Change List: <br/>";
                    $body .= $changeList . "<br/><br/>";

                    $returnObject = sendmail($to, $subject, $body, $from);

                    // Are there any failed emails?
                    if(sizeof($returnObject["failedRecipients"]) > 0){
                       // Send the list to the admin
                        $contactargs = array(
                                "transform"=>1,
                                "filter"=>"entityID,eq,0"
                          );
                          $contacturl = API_HOST_URL . "/contacts?".http_build_query($contactargs);
                          $contactoptions = array(
                              'http' => array(
                                  'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                  'method'  => 'GET'
                              )
                          );
                          $contactcontext  = stream_context_create($contactoptions);
                          $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);

                          $contactList = $contactresult["contacts"];

                          for($i=0; $i<sizeof($contactList); $i++){

                              $adminTo = array($contactList[$i]['emailAddress'] => $contactList[$i]['firstName'] . " " . $contactList[$i]['lastName']);

                                $adminBody = "Hello " . $contactList[$i]['firstName'] . ",<br /><br />";
                                $adminBody .= "The following emails were returned as failures: <br />";

                                $adminBody .= implode("<br/>", $returnObject["failedRecipients"]);

                                $adminSubject = "Rejected Email Addresses";

                                $adminReturnObject = sendmail($adminTo, $adminSubject, $adminBody, $from);
                          }

                    }

              } catch (Exception $mailex) {
                return $mailex;
              }
    }

    private function sendEmailToCustomer($subject, $from, $adminresult, $contactresult, $changeList, $orderNumber){
        try {
                    $to = array($contactresult['emailAddress'] => $contactresult['firstName'] . " " . $contactresult['lastName']);

                    $body = "Thank you for updating Order #" . $orderNumber . "<br /><br />";
                    $body .= $changeList . "<br/><br/>";
                    $body .= "A Nationwide Equipment Control representative will contact you if additional information is required.<br/>";
                    $body .= "If you would like to view these changes, please login to the Nationwide Equipment Control website at: " . HTTP_HOST . "/login <br/><br/><br/>";
                    $body .= "Thank you for your order,<br/>";
                    $body .= $adminresult['firstName'] . " " . $adminresult['lastName'] . "<br/>";
                    $body .= "Nationwide Equipment Control<br/>";

                    $returnObject = sendmail($to, $subject, $body, $from);

                    // Are there any failed emails?
                    if(sizeof($returnObject["failedRecipients"]) > 0){
                       // Send the list to the admin
                        $contactargs = array(
                                "transform"=>1,
                                "filter"=>"entityID,eq,0"
                          );
                          $contacturl = API_HOST_URL . "/contacts?".http_build_query($contactargs);
                          $contactoptions = array(
                              'http' => array(
                                  'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                  'method'  => 'GET'
                              )
                          );
                          $contactcontext  = stream_context_create($contactoptions);
                          $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);

                          $contactList = $contactresult["contacts"];

                          for($i=0; $i<sizeof($contactList); $i++){

                              $adminTo = array($contactList[$i]['emailAddress'] => $contactList[$i]['firstName'] . " " . $contactList[$i]['lastName']);

                                $adminBody = "Hello " . $contactList[$i]['firstName'] . ",<br /><br />";
                                $adminBody .= "The following emails were returned as failures: <br />";

                                $adminBody .= implode("<br/>", $returnObject["failedRecipients"]);

                                $adminSubject = "Rejected Email Addresses";

                                $adminReturnObject = sendmail($adminTo, $adminSubject, $adminBody, $from);
                          }

                    }

              } catch (Exception $mailex) {
                return $mailex;
              }
    }

    public function sendOrderStatusNotification($orderNumber, $carrierID, $customerID){

        $carrierContact = $this->getContactInformation($carrierID);
        $customerContact = $this->getContactInformation($customerID);
        $adminContact = $this->getContactInformation(0);

        // Setting up Email
        $subject = "Status update To Order #" . $orderNumber;
        $from = array("operations@nationwide-equipment.com" => "Nationwide Operations Control Manager");

        $adminBody = "An order status has changed for Order #" . $orderNumber . " with Nationwide Equipment Control has a status change. "
                . "To view the status of this order please visit the Nationwide Equipment Control website " . HTTP_HOST . "/login.  "
                . "Login to the website to view the order status. ";

        $body = "Order #" . $orderNumber . " with Nationwide Equipment Control has a status change. "
                . "To view the status of this order please visit the Nationwide Equipment Control website " . HTTP_HOST . "/login.  "
                . "Login to the website to view the order status. <br /><br />"
                . "Thank you,<br />"
                . $adminContact['firstName'] . " " . $adminContact['lastName'] . "<br />"
                . "Nationwide Equipment Control";

        // Send to Admin
        try {
            $to = array($adminContact['emailAddress'] => $adminContact['firstName'] . " " . $adminContact['lastName']);

            $returnObject = sendmail($to, $subject, $body, $from);

            // Are there any failed emails?
            if(sizeof($returnObject["failedRecipients"]) > 0){
               // Send the list to the admin
                $contactargs = array(
                        "transform"=>1,
                        "filter"=>"entityID,eq,0"
                  );
                  $contacturl = API_HOST_URL . "/contacts?".http_build_query($contactargs);
                  $contactoptions = array(
                      'http' => array(
                          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                          'method'  => 'GET'
                      )
                  );
                  $contactcontext  = stream_context_create($contactoptions);
                  $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);

                  $contactList = $contactresult["contacts"];

                  for($i=0; $i<sizeof($contactList); $i++){

                      $adminTo = array($contactList[$i]['emailAddress'] => $contactList[$i]['firstName'] . " " . $contactList[$i]['lastName']);

                        $adminBody = "Hello " . $contactList[$i]['firstName'] . ",<br /><br />";
                        $adminBody .= "The following emails were returned as failures: <br />";

                        $adminBody .= implode("<br/>", $returnObject["failedRecipients"]);

                        $adminSubject = "Rejected Email Addresses";

                        $adminReturnObject = sendmail($adminTo, $adminSubject, $adminBody, $from);
                  }
            }

        }
        catch (Exception $mailex) {
          return $mailex;
        }

        /*
        // Send to Carrier
        try {
            $to = array($carrierContact['emailAddress'] => $carrierContact['firstName'] . " " . $carrierContact['lastName']);

            $returnObject = sendmail($to, $subject, $body, $from);

            // Are there any failed emails?
            if(sizeof($returnObject["failedRecipients"]) > 0){
               // Send the list to the admin
                $contactargs = array(
                        "transform"=>1,
                        "filter"=>"entityID,eq,0"
                  );
                  $contacturl = API_HOST_URL . "/contacts?".http_build_query($contactargs);
                  $contactoptions = array(
                      'http' => array(
                          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                          'method'  => 'GET'
                      )
                  );
                  $contactcontext  = stream_context_create($contactoptions);
                  $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);

                  $contactList = $contactresult["contacts"];

                  for($i=0; $i<sizeof($contactList); $i++){

                      $adminTo = array($contactList[$i]['emailAddress'] => $contactList[$i]['firstName'] . " " . $contactList[$i]['lastName']);

                        $adminBody = "Hello " . $contactList[$i]['firstName'] . ",<br /><br />";
                        $adminBody .= "The following emails were returned as failures: <br />";

                        $adminBody .= implode("<br/>", $returnObject["failedRecipients"]);

                        $adminSubject = "Rejected Email Addresses";

                        $adminReturnObject = sendmail($adminTo, $adminSubject, $adminBody, $from);
                  }

            }

        }
        catch (Exception $mailex) {
          return $mailex;
        }


        // Send to Customer
        try {
            $to = array($customerContact['emailAddress'] => $customerContact['firstName'] . " " . $customerContact['lastName']);

            $returnObject = sendmail($to, $subject, $body, $from);

            // Are there any failed emails?
            if(sizeof($returnObject["failedRecipients"]) > 0){
               // Send the list to the admin
                $contactargs = array(
                        "transform"=>1,
                        "filter"=>"entityID,eq,0"
                  );
                  $contacturl = API_HOST_URL . "/contacts?".http_build_query($contactargs);
                  $contactoptions = array(
                      'http' => array(
                          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                          'method'  => 'GET'
                      )
                  );
                  $contactcontext  = stream_context_create($contactoptions);
                  $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);

                  $contactList = $contactresult["contacts"];

                  for($i=0; $i<sizeof($contactList); $i++){

                      $adminTo = array($contactList[$i]['emailAddress'] => $contactList[$i]['firstName'] . " " . $contactList[$i]['lastName']);

                        $adminBody = "Hello " . $contactList[$i]['firstName'] . ",<br /><br />";
                        $adminBody .= "The following emails were returned as failures: <br />";

                        $adminBody .= implode("<br/>", $returnObject["failedRecipients"]);

                        $adminSubject = "Rejected Email Addresses";

                        $adminReturnObject = sendmail($adminTo, $adminSubject, $adminBody, $from);
                  }

            }

        }
        catch (Exception $mailex) {
          return $mailex;
        }
        */

        return "The order status has been successfully updated.";
    }

    private function getContactInformation($entityID){

            // Entity
            $entityargs = array(
                "transform"=>1
            );
            $entityurl = API_HOST_URL . "/entities/" . $entityID . "?".http_build_query($entityargs);
            $entityoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $entitycontext  = stream_context_create($entityoptions);
            $entityresult = json_decode(file_get_contents($entityurl,false,$entitycontext),true);

            // Contact
            $contactargs = array(
                "transform"=>1
            );
            $contacturl = API_HOST_URL . "/contacts/".$entityresult['assignedMemberID']."?".http_build_query($contactargs);
            $contactoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $contactcontext  = stream_context_create($contactoptions);
            $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);

            return $contactresult;
    }

    public function indexgetorders(&$db,$locationStatus,$stateFilter,$cityFilter,$entityid = 0) {

        try {

              $query = " select *";

              if ($entityid > 0) {
                  $query .= ", orders.originationCity, orders.originationState, orders.originationLat, orders.originationLng,
                               orders.destinationCity, orders.destinationState, orders.destinationLat, orders.destinationLng";
              }

              $query .= " from order_details
                         left join orders on orders.id = order_details.orderID
                         where order_details.status = 'Open'
                         and order_details.deliveryDate >= '" . date('Y-m-d') . "'";

              if ($entityid > 0) {
                  $query .= " and orders.customerID = '" . $entityid . "'";
              }

              if (!empty($stateFilter)) {
                    if (count($stateFilter) == 1) {
                        $sfilter = "'" . $stateFilter[0] . "'";
                    } else {
                        $numStates = $stateFilter;
                        $sfilter = "";
                        for ($s=0;$s<count($numStates);$s++) {
                            $sfilter .= "'" . $numStates[$s] . "'";
                            if ($s < count($numStates) - 1) {
                                $sfilter .= ",";
                            }
                        }
                    }
                    if ($locationStatus == "Origination") {
                        $query .= " and orders.originationState in (" . $sfilter . ")";
                    } else if ($locationStatus == "Destination") {
                        $query .= " and orders.destinationState in (" . $sfilter . ")";
                    } else {
                        $query .= " and (orders.originationState in (" . $sfilter . ") or orders.destinationState in (" . $sfilter . "))";
                    }
              }
              if (!empty($cityFilter)) {
                    if (count($cityFilter) == 1) {
                        $cfilter = "'" . $cityFilter[0] . "'";
                    } else {
                        //$numCities = explode(",",$cityFilter);
                        $numCities = $cityFilter;
                        $cfilter = "";
                        for ($c=0;$c<count($numCities);$c++) {
                            $cfilter .= "'" . $numCities[$c] . "'";
                            if ($c < count($numCities) - 1) {
                                $cfilter .= ",";
                            }
                        }
                    }
                    if ($locationStatus == "Origination") {
                        $query .= " and orders.originationCity in (" . $cfilter . ")";
                    } else if ($locationStatus == "Destination") {
                        $query .= " and orders.destinationCity in (" . $cfilter . ")";
                    } else {
                        $query .= " and (orders.originationCity in (" . $sfilter . ") or orders.destinationCity in (" . $sfilter . "))";
                    }
              }

              $result = $db->query($query);

              if (count($result) > 0) {
                  echo "{ \"order_details\":".json_encode($result->fetchAll()) . "}";
              } else {
                  return false;
              }
        } catch (Exception $e) { // The indexgetorders query failed verification
              header('HTTP/1.1 404 Not Found');
              header('Content-Type: text/plain; charset=utf8');
              echo $e->getMessage();
              exit();
        }
    }

}

