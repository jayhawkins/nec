<?php

class CustomerNeedCommit
{

    private $customerNeedsID;
    private $entityID;
    private $originationAddress1;
    private $originationCity;
    private $originationState;
    private $originationZip;
    private $destinationAddress1;
    private $destinationCity;
    private $destinationState;
    private $destinationZip;
    private $originationLat;
    private $originationLng;
    private $destinationLat;
    private $destinationLng;
    private $status;
    private $qty;
    private $rate;
    private $pickupDate;
    private $deliveryDate;

    public function __construct() {

    }

    public function post() {

    }

    public function load($api_host,$id) {

      $args = array(
            "transform"=>"1"
      );

      $url = $api_host."/api/customer_needs_commit/".$id."?".http_build_query($args);
      $options = array(
          'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'GET'
          )
      );
      $context  = stream_context_create($options);
      $result = json_decode(file_get_contents($url,false,$context),true);

      $this->customerNeedsID = $result["customerNeedsID"];
      $this->entityID = $result["entityID"];
      //$this->originationAddress1 = $result["originationAddress1"];
      $this->originationCity = $result["originationCity"];
      $this->originationState = $result["originationState"];
      $this->originationZip = $result["originationZip"];
      //$this->destinationAddress1 = $result["destinationAddress1"];
      $this->destinationCity = $result["destinationCity"];
      $this->destinationState = $result["destinationState"];
      $this->destinationZip = $result["destinationZip"];
      $this->originationLat = $result["originationLat"];
      $this->originationLng = $result["originationLng"];
      $this->destinationLat = $result["destinationLat"];
      $this->destinationLng = $result["destinationLng"];
      $this->status = $result["status"];
      $this->qty = $result["qty"];
      $this->rate = $result["rate"];
      $this->pickupDate = $result["pickupDate"];
      $this->deliveryDate = $result["deliveryDate"];

    }

    public function put($locationid,$address1,$address2,$city,$state,$zip) {
        try {

        } catch (Exception $e) { // The authorization query failed verification
              header('HTTP/1.1 401 Unauthorized');
              header('Content-Type: text/plain; charset=utf8');
              return $e->getMessage();
        }
    }

    public function delete() {

    }

    public function getContactEmails() { // Contact Emails are stored as a JSON object/array in a JSON type field
        return $this->contactEmails; // Return as an object
    }

    // Not Used Yet - just wanted to keep the code so we know how to loop through the carrier needs contacts
    public function sendToContacts() {
        foreach ($this->contactEmails[0] as $key => $value) {
            $contactargs = array(
                  "transform"=>1,
                  "filter"=>"id,eq,".$key
            );
            $contacturl = API_HOST."/api/contacts?".http_build_query($contactargs);
            $contactoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $contactcontext  = stream_context_create($contactoptions);
            $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);
            //return $contactresult;
            $to = array($contactresult['contacts'][0]['emailAddress'] => $contactresult['contacts'][0]['firstName'] . " " . $contactresult['contacts'][0]['lastName']);

            $numSent = 0;

            $body = "Hello " . $contactresult['contacts'][0]['firstName'] . ",<br /><br />";
            $body .= $templateresult['email_templates'][0]['body'];
            if (count($templateresult) > 0) {
              try {
                $numSent = sendmail($to, $subject, $body, $from);
              } catch (Exception $mailex) {
                return $mailex;
              }
            }
        }
    }

    public function sendRepNotification($api_host,$id) {
        // Load the carrier need data to send notification
        $this->load($api_host,$id);

        if ($this->customerNeedsID > 0) {

            $entityargs = array(
                "transform"=>1
            );
            $entityurl = API_HOST."/api/entities/".$this->entityID."?".http_build_query($entityargs);
            $entityoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $entitycontext  = stream_context_create($entityoptions);
            $entityresult = json_decode(file_get_contents($entityurl,false,$entitycontext),true);

            $contactargs = array(
                "transform"=>1
            );
            $contacturl = API_HOST."/api/contacts/".$entityresult['contactID']."?".http_build_query($contactargs);
            $contactoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $contactcontext  = stream_context_create($contactoptions);
            $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);

            $templateargs = array(
                "transform"=>1,
                "filter"=>"title,eq,Carrier Commit Notification"
            );
            $templateurl = API_HOST."/api/email_templates?".http_build_query($templateargs);
            $templateoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $templatecontext  = stream_context_create($templateoptions);
            $templateresult = json_decode(file_get_contents($templateurl,false,$templatecontext),true);
            if (count($templateresult) > 0) {
                $subject = $templateresult['email_templates'][0]['subject'];
            } else {
                $subject = "Nationwide Equipment Control - Carrier Commit Notification";
            }


            $from = array("operations@nationwide-equipment.com" => "Nationwide Operations Control Manager");
            $numSent = 0;

            if (count($templateresult) > 0) {
              try {
                    $to = array($contactresult['emailAddress'] => $contactresult['firstName'] . " " . $contactresult['lastName']);

                    $body = "Hello " . $contactresult['firstName'] . ",<br /><br />";
                    $body .= $templateresult['email_templates'][0]['body'];
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

        return "Your Committment has been recorded, and NEC will be notified";
    }
    
    public function sendAcceptNotification($api_host,$id){
        // Load the carrier need data to send notification
        $this->load($api_host,$id);

        if ($this->customerNeedsID > 0) {

            $entityargs = array(
                "transform"=>1
            );
            $entityurl = API_HOST."/api/entities/".$this->entityID."?".http_build_query($entityargs);
            $entityoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $entitycontext  = stream_context_create($entityoptions);
            $entityresult = json_decode(file_get_contents($entityurl,false,$entitycontext),true);

            $contactargs = array(
                "transform"=>1
            );
            $contacturl = API_HOST."/api/contacts/".$entityresult['contactID']."?".http_build_query($contactargs);
            $contactoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $contactcontext  = stream_context_create($contactoptions);
            $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);

            $templateargs = array(
                "transform"=>1,
                "filter"=>"title,eq,Carrier Commit Notification"
            );
            $templateurl = API_HOST."/api/email_templates?".http_build_query($templateargs);
            $templateoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $templatecontext  = stream_context_create($templateoptions);
            $templateresult = json_decode(file_get_contents($templateurl,false,$templatecontext),true);
            if (count($templateresult) > 0) {
                $subject = $templateresult['email_templates'][0]['subject'];
            } else {
                $subject = "Nationwide Equipment Control - Carrier Commit Notification";
            }


            $from = array("operations@nationwide-equipment.com" => "Nationwide Operations Control Manager");
            $numSent = 0;

            if (count($templateresult) > 0) {
              try {
                    $to = array($contactresult['emailAddress'] => $contactresult['firstName'] . " " . $contactresult['lastName']);

                    $body = "Hello " . $contactresult['firstName'] . ",<br /><br />";
                    $body .= $templateresult['email_templates'][0]['body'];
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

        return "Your Committment has been recorded, and NEC will be notified";
    }
}
