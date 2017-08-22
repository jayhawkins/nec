<?php

class Order
{
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
            $customerurl = API_HOST."/api/entities/".$customerID."?".http_build_query($customerargs);
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
            $entityurl = API_HOST."/api/entities/0?".http_build_query($entityargs);
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
            $contacturl = API_HOST."/api/contacts/".$entityresult['assignedMemberID']."?".http_build_query($contactargs);
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
            $adminurl = API_HOST."/api/contacts/".$entityresult['assignedMemberID']."?".http_build_query($adminargs);
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
                    
                    if (sendmail($to, $subject, $body, $from)) {
                        $numSent++;
                    } else {
                        return $mailex;
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
                    
                    if (sendmail($to, $subject, $body, $from)) {
                        $numSent++;
                    } else {
                        return $mailex;
                    }
              } catch (Exception $mailex) {
                return $mailex;
              }
    }
}

