<?php

class CustomerNeed
{

    private $id;
    private $entityID;
    private $originationCity;
    private $originationState;
    private $originationZip;
    private $destinationCity;
    private $destinationState;
    private $destinationZip;
    private $originationLat;
    private $originationLng;
    private $destinationLat;
    private $destinationLng;
    private $needsDataPoints;
    private $status;
    private $qty;
    private $availableDate;
    private $contactEmails;

    public function __construct() {

    }

    public function post() {

    }

    public function createFromExisting($api_host,$id,$qty,$originationAddress1,$originationCity,$originationState,$originationZip,$destinationAddress1,$destinationCity,$destinationState,$destinationZip,$originationLat,$originationLng,$destinationLat,$destinationLng,$distance,$transportation_mode,$transportation_type,$pickupDate,$deliveryDate) {

          /******** WE ARE NOT USING THE LNG AND LAT FROM THIS CALL - WE WILL NEED TO GO GET THE GEOCODE BASED ON THE NEW ORIGINATION AND DESTINATION *****/

          $google = new googleApiClass();

          // Load the carrier need data to send notification
          $this->load($api_host,$id);

          $original_originationaddress = $this->originationCity . ", " . $this->originationState . ", " . $this->originationZip;
          $original_destinationaddress = $this->destinationCity . ", " . $this->destinationState . ", " . $this->destinationZip;

          $entered_originationaddress = $originationCity . ", " . $originationState . ", " . $originationZip;
          $entered_destinationaddress = $destinationCity . ", " . $destinationState . ", " . $destinationZip;

          //$google->setFromAddress($entered_originationaddress);
          //$google->setToAddress($entered_destinationaddress);
          //$google->setLanguage('us');
          //$google->findAddress();
          //$instructions = $google->getInstructions();
          //echo "Instructions: " . $instructions;
          //die();

          try {
/*
              if ($original_originationaddress == $entered_originationaddress && $original_destinationaddress == $entered_destinationaddress) {

                      $data = array(
                        "qty"=>$qty,
                        //"originationAddress1"=>$originationAddress1,
                        "originationCity"=>$originationCity,
                        "originationState"=>$originationState,
                        "originationZip"=>$originationZip,
                        //"destinationAddress1"=>$destinationAddress1,
                        "destinationCity"=>$destinationCity,
                        "destinationState"=>$destinationState,
                        "destinationZip"=>$destinationZip,
                        "originationLat"=>$originationLat,
                        "originationLng"=>$originationLng,
                        "destinationLat"=>$destinationLat,
                        "destinationLng"=>$destinationLng,
                        "distance"=>$distance,
                        "entityID"=>$this->entityID,
                        "needsDataPoints"=>$this->needsDataPoints,
                        "status"=>$this->status,
                        "availableDate"=>$this->availableDate,
                        "contactEmails"=>$this->contactEmails,
                        "createdAt" => date('Y-m-d H:i:s'),
                        "updatedAt" => date('Y-m-d H:i:s')
                      );
                      $url = $api_host."/api/customer_needs/";
                      $options = array(
                          'http' => array(
                              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                              'method'  => 'POST',
                              'content' => http_build_query($data)
                          )
                      );
                      $context  = stream_context_create($options);
                      $result = json_decode(file_get_contents($url,false,$context),true);

              } else {
                      $data = array(
                        "qty"=>$qty,
                        //"originationAddress1"=>$originationAddress1,
                        "originationCity"=>$originationCity,
                        "originationState"=>$originationState,
                        "originationZip"=>$originationZip,
                        //"destinationAddress1"=>$destinationAddress1,
                        "destinationCity"=>$destinationCity,
                        "destinationState"=>$destinationState,
                        "destinationZip"=>$destinationZip,
                        "originationLat"=>$originationLat,
                        "originationLng"=>$originationLng,
                        "destinationLat"=>$destinationLat,
                        "destinationLng"=>$destinationLng,
                        "distance"=>$distance,
                        "entityID"=>$this->entityID,
                        "needsDataPoints"=>$this->needsDataPoints,
                        "status"=>$this->status,
                        "availableDate"=>$this->availableDate,
                        "contactEmails"=>$this->contactEmails,
                        "createdAt" => date('Y-m-d H:i:s'),
                        "updatedAt" => date('Y-m-d H:i:s')
                      );
                      $url = $api_host."/api/customer_needs/";
                      $options = array(
                          'http' => array(
                              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                              'method'  => 'POST',
                              'content' => http_build_query($data)
                          )
                      );
                      $context  = stream_context_create($options);
                      $result = json_decode(file_get_contents($url,false,$context),true);
              }
*/
              $data = array(
                "qty"=>$qty,
                //"originationAddress1"=>$originationAddress1,
                "originationCity"=>$originationCity,
                "originationState"=>$originationState,
                "originationZip"=>$originationZip,
                //"destinationAddress1"=>$destinationAddress1,
                "destinationCity"=>$destinationCity,
                "destinationState"=>$destinationState,
                "destinationZip"=>$destinationZip,
                "originationLat"=>$originationLat,
                "originationLng"=>$originationLng,
                "destinationLat"=>$destinationLat,
                "destinationLng"=>$destinationLng,
                "distance"=>$distance,
                "entityID"=>$this->entityID,
                "needsDataPoints"=>$this->needsDataPoints,
                "status"=>$this->status,
                "availableDate"=>$this->availableDate,
                "contactEmails"=>$this->contactEmails,
                "createdAt" => date('Y-m-d H:i:s'),
                "updatedAt" => date('Y-m-d H:i:s')
              );
              $url = $api_host."/api/customer_needs/";
              $options = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'POST',
                      'content' => http_build_query($data)
                  )
              );
              $context  = stream_context_create($options);
              $result = json_decode(file_get_contents($url,false,$context),true);

              if ($result > 0) {
                  $data = array(
                            "customerNeedsID"=>$result,
                            "qty"=>$qty,
                            //"originationAddress1"=>$originationAddress1,
                            "originationCity"=>$originationCity,
                            "originationState"=>$originationState,
                            "originationZip"=>$originationZip,
                            //"destinationAddress1"=>$destinationAddress1,
                            "destinationCity"=>$destinationCity,
                            "destinationState"=>$destinationState,
                            "destinationZip"=>$destinationZip,
                            "originationLat"=>$originationLat,
                            "originationLng"=>$originationLng,
                            "destinationLat"=>$destinationLat,
                            "destinationLng"=>$destinationLng,
                            "distance"=>$distance,
                            "entityID"=>$this->entityID,
                            "status"=>$this->status,
                            "transportation_type"=>$transportation_type,
                            "transportation_mode"=>$transportation_mode,
                            "pickupDate"=>$pickupDate,
                            "deliveryDate"=>$deliveryDate,
                            "rate"=>0,
                            "createdAt" => date('Y-m-d H:i:s'),
                            "updatedAt" => date('Y-m-d H:i:s')
                        );

                        $url = $api_host."/api/customer_needs_commit/";
                        $options = array(
                              'http' => array(
                                  'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                  'method'  => 'POST',
                                  'content' => http_build_query($data)
                              )
                        );
                        $context  = stream_context_create($options);
                        try {
                              $defaultresult = json_decode(file_get_contents($url,false,$context),true);
                              if ($defaultresult > 0) {
                                  // KEEP GOING!
                                  //return "success";
                                  $noticesent = $this->sendCommitNotification($api_host);
                              } else {
                                  return "Failed creating commit record.";
                              }
                        } catch (Exception $e) {
                            return $e;
                        }

              }

              if ($result > 0) {
                  // Write the new avaialbility record if origination was changed
                  if ($original_originationaddress != $entered_originationaddress) {
                        $data = array(
                            "qty"=>$qty,
                            //"originationAddress1"=>$originationAddress1,
                            "originationCity"=>$originationCity,
                            "originationState"=>$originationState,
                            "originationZip"=>$originationZip,
                            //"destinationAddress1"=>$this->destinationAddress1,
                            "destinationCity"=>$this->destinationCity,
                            "destinationState"=>$this->destinationState,
                            "destinationZip"=>$this->destinationZip,
                            "originationLat"=>$originationLat,
                            "originationLng"=>$originationLng,
                            "destinationLat"=>$this->destinationLat,
                            "destinationLng"=>$this->destinationLng,
                            "distance"=>$distance,
                            "entityID"=>$this->entityID,
                            "needsDataPoints"=>$this->needsDataPoints,
                            "status"=>$this->status,
                            "availableDate"=>$this->availableDate,
                            "contactEmails"=>$this->contactEmails,
                            "createdAt" => date('Y-m-d H:i:s'),
                            "updatedAt" => date('Y-m-d H:i:s')
                        );

                        $url = $api_host."/api/customer_needs/";
                        $options = array(
                              'http' => array(
                                  'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                  'method'  => 'POST',
                                  'content' => http_build_query($data)
                              )
                        );
                        $context  = stream_context_create($options);
                        try {
                            $originresult = json_decode(file_get_contents($url,false,$context),true);
                        } catch (Exception $e) {
                            return $e;
                        }
                  } else if ($original_destinationaddress != $entered_destinationaddress) {
                        $data = array(
                            "qty"=>$qty,
                            //"originationAddress1"=>$destinationAddress1,
                            "originationCity"=>$destinationCity,
                            "originationState"=>$destinationState,
                            "originationZip"=>$destinationZip,
                            //"destinationAddress1"=>$this->destinationAddress1,
                            "destinationCity"=>$this->destinationCity,
                            "destinationState"=>$this->destinationState,
                            "destinationZip"=>$this->destinationZip,
                            "originationLat"=>$originationLat,
                            "originationLng"=>$originationLng,
                            "destinationLat"=>$destinationLat,
                            "destinationLng"=>$destinationLng,
                            "distance"=>$distance,
                            "entityID"=>$this->entityID,
                            "needsDataPoints"=>$this->needsDataPoints,
                            "status"=>$this->status,
                            "availableDate"=>$this->availableDate,
                            "contactEmails"=>$this->contactEmails,
                            "createdAt" => date('Y-m-d H:i:s'),
                            "updatedAt" => date('Y-m-d H:i:s')
                        );

                        $url = $api_host."/api/customer_needs/";
                        $options = array(
                            'http' => array(
                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method'  => 'POST',
                                'content' => http_build_query($data)
                            )
                        );
                        $context  = stream_context_create($options);
                        try {
                            $destresult = json_decode(file_get_contents($url,false,$context),true);
                        } catch (Exception $e) {
                            return $e;
                        }
                  }

                  return "success";

              }
          } catch (Exception $e) {
              return $e;
          }

          return "success";

    }

    public function load($api_host,$id) {

      $args = array(
            "transform"=>"1"
      );

      $url = $api_host."/api/customer_needs/".$id."?".http_build_query($args);
      $options = array(
          'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'GET'
          )
      );
      $context  = stream_context_create($options);
      $result = json_decode(file_get_contents($url,false,$context),true);

      $this->entityID = $result["entityID"];
      $this->originationCity = $result["originationCity"];
      $this->originationState = $result["originationState"];
      $this->originationZip = $result["originationZip"];
      $this->destinationCity = $result["destinationCity"];
      $this->destinationState = $result["destinationState"];
      $this->destinationZip = $result["destinationZip"];
      $this->originationLat = $result["originationLat"];
      $this->originationLng = $result["originationLng"];
      $this->destinationLat = $result["destinationLat"];
      $this->destinationLng = $result["destinationLng"];
      $this->needsDataPoints = $result["needsDataPoints"];
      $this->status = $result["status"];
      $this->qty = $result["qty"];
      $this->availableDate = $result["availableDate"];
      $this->contactEmails = $result["contactEmails"];

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

    public function sendNotification($api_host,$id) {
        // Load the carrier need data to send notification
        $this->load($api_host,$id);

        if (count($this->contactEmails) > 0) {

            $entityFilter = "entityID,in,(0,".$this->entityID.")";
            $entitycontactargs = array(
                "transform"=>1,
                "filter[0]"=>$entityFilter,
                "filter[1]"=>"contactTypeID,eq,1"
            );
            $entitycontacturl = API_HOST."/api/contacts?".http_build_query($entitycontactargs);
            $entitycontactoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $entitycontactcontext  = stream_context_create($entitycontactoptions);
            $entitycontactresult = json_decode(file_get_contents($entitycontacturl,false,$entitycontactcontext),true);

            $templateargs = array(
                "transform"=>1,
                "filter"=>"title,eq,Customer Availability Notification"
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
                $subject = "Nationwide Equipment Control - Trailer Availability Notification";
            }


            $from = array("operations@nationwide-equipment.com" => "Nationwide Operations Control Manager");
            $numSent = 0;

            if (count($templateresult) > 0) {
              try {
                  for ($ec=0;$ec<count($entitycontactresult['contacts']);$ec++) {

                      $to = array($entitycontactresult['contacts'][$ec]['emailAddress'] => $entitycontactresult['contacts'][$ec]['firstName'] . " " . $entitycontactresult['contacts'][$ec]['lastName']);

                      $body = "Hello " . $entitycontactresult['contacts'][$ec]['firstName'] . ",<br /><br />";
                      $body .= $templateresult['email_templates'][0]['body'];
                      if (sendmail($to, $subject, $body, $from)) {
                          $numSent++;
                      } else {
                          return $mailex;
                      }
                  }
              } catch (Exception $mailex) {
                return $mailex;
              }
            }
        }

        return "Your Availability Notification has been recorded, and Carriers will be notified";
    }

    public function sendCommitNotification($api_host) {

        if (count($this->contactEmails) > 0) {

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
              } catch (Exception $mailex) {
                return $mailex;
              }
            }

        }

        //return "Your Committment has been recorded, and NEC will be notified";
        return true;
    }
}
