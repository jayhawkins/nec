<?php

class CustomerNeed
{

    private $id;
    private $rootCustomerNeedsID;
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
    private $needsDataPoints;
    private $status;
    private $transportationMode;
    private $distance;
    private $qty;
    private $availableDate;
    private $contactEmails;
    private $createdAt;
    private $updatedAt;

    public function __construct() {

    }

    public function post() {

    }

    public function createFromExisting($api_host,$id,$rootCustomerNeedsID,$carrierID,$qty,$originationAddress1,$originationCity,$originationState,$originationZip,$destinationAddress1,$destinationCity,$destinationState,$destinationZip,$originationLat,$originationLng,$destinationLat,$destinationLng,$distance,$transportationMode,$transportation_mode,$transportation_type,$pickupDate,$deliveryDate,$google_maps_api) {

          /******** WE ARE NOT USING THE LNG AND LAT FROM THIS CALL - WE WILL NEED TO GO GET THE GEOCODE BASED ON THE NEW ORIGINATION AND DESTINATION *****/

          // Load the customer need data to send notification
          $this->load($api_host,$id);

          $original_originationaddress = $this->originationCity . ", " . $this->originationState;
          $original_destinationaddress = $this->destinationCity . ", " . $this->destinationState;

          $entered_originationaddress = $originationCity . ", " . $originationState;
          $entered_destinationaddress = $destinationCity . ", " . $destinationState;

          try {

              $data = array(
                //"rootCustomerNeedsID"=>$rootCustomerNeedsID,
                "rootCustomerNeedsID"=>$id,
                "qty"=>$qty,
                //"originationAddress1"=>$originationAddress1,
                "originationCity"=>$originationCity,
                "originationState"=>$originationState,
                //"originationZip"=>$originationZip,
                //"destinationAddress1"=>$destinationAddress1,
                "destinationCity"=>$destinationCity,
                "destinationState"=>$destinationState,
                //"destinationZip"=>$destinationZip,
                "originationLat"=>$originationLat,
                "originationLng"=>$originationLng,
                "destinationLat"=>$destinationLat,
                "destinationLng"=>$destinationLng,
                "distance"=>$distance,
                "transportationMode"=>$transportationMode,
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
                            //"originationZip"=>$originationZip,
                            //"destinationAddress1"=>$destinationAddress1,
                            "destinationCity"=>$destinationCity,
                            "destinationState"=>$destinationState,
                            //"destinationZip"=>$destinationZip,
                            "originationLat"=>$originationLat,
                            "originationLng"=>$originationLng,
                            "destinationLat"=>$destinationLat,
                            "destinationLng"=>$destinationLng,
                            "distance"=>$distance,
                            "entityID"=>$carrierID,
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

                        $oaddress = urlencode($this->originationCity . ", " . $this->originationState);
                        $daddress = urlencode($originationCity . ", " . $originationState);

                        $details = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=".urlencode($oaddress)."&destinations=".urlencode($daddress)."&mode=driving&sensor=false";
                        $json = file_get_contents($details);
                        $details = json_decode($json, TRUE);
                        //echo "<pre>"; print_r($details); echo "</pre>";

                        $distance = ( ($details['rows'][0]['elements'][0]['distance']['value'] / 1000) * .6214 );

                        $data = array(
                            //"rootCustomerNeedsID"=>$rootCustomerNeedsID,
                            "rootCustomerNeedsID"=>$id,
                            "qty"=>$qty,
                            //"originationAddress1"=>$this->originationAddress1,
                            "originationCity"=>$this->originationCity,
                            "originationState"=>$this->originationState,
                            //"originationZip"=>$this->originationZip,
                            //"destinationAddress1"=>$originationAddress1,
                            "destinationCity"=>$originationCity,
                            "destinationState"=>$originationState,
                            //"destinationZip"=>$originationZip,
                            "originationLat"=>$this->originationLat,
                            "originationLng"=>$this->originationLng,
                            "destinationLat"=>$originationLat,
                            "destinationLng"=>$originationLng,
                            "distance"=>$distance,
                            "transportationMode"=>$this->transportationMode,
                            "entityID"=>$this->entityID,
                            "needsDataPoints"=>$this->needsDataPoints,
                            "status"=>$this->status,
                            "availableDate"=>$deliveryDate,
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

                        $oaddress = urlencode($destinationCity . ", " . $destinationState);
                        $daddress = urlencode($this->destinationCity . ", " . $this->destinationState);

                        $details = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=".urlencode($oaddress)."&destinations=".urlencode($daddress)."&mode=driving&sensor=false";
                        $json = file_get_contents($details);
                        $details = json_decode($json, TRUE);
                        //echo "<pre>"; print_r($details); echo "</pre>";

                        $distance = ( ($details['rows'][0]['elements'][0]['distance']['value'] / 1000) * .6214 );

                        $data = array(
                            //"rootCustomerNeedsID"=>$rootCustomerNeedsID,
                            "rootCustomerNeedsID"=>$id,
                            "qty"=>$qty,
                            //"originationAddress1"=>$this->originationAddress1,
                            "originationCity"=>$destinationCity,
                            "originationState"=>$destinationState,
                            //"originationZip"=>$this->originationZip,
                            //"destinationAddress1"=>$originationAddress1,
                            "destinationCity"=>$this->destinationCity,
                            "destinationState"=>$this->destinationState,
                            //"destinationZip"=>$originationZip,
                            "originationLat"=>$destinationLat,
                            "originationLng"=>$destinationLng,
                            "destinationLat"=>$this->destinationLat,
                            "destinationLng"=>$this->destinationLng,
                            "distance"=>$distance,
                            "transportationMode"=>$this->transportationMode,
                            "entityID"=>$this->entityID,
                            "needsDataPoints"=>$this->needsDataPoints,
                            "status"=>$this->status,
                            "availableDate"=>$deliveryDate,
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

                  // If quantity was not the total quantity for the need, we need to create another leg of the original need with adjusted quantity
                  if ($qty != $this->qty) {

                        $newqty = $this->qty - $qty;

                        $data = array(
                            "rootCustomerNeedsID"=>$this->rootCustomerNeedsID,
                            "qty"=>$newqty,
                            "originationAddress1"=>$this->originationAddress1,
                            "originationCity"=>$this->originationCity,
                            "originationState"=>$this->originationState,
                            "originationZip"=>$this->originationZip,
                            "destinationAddress1"=>$this->destinationAddress1,
                            "destinationCity"=>$this->destinationCity,
                            "destinationState"=>$this->destinationState,
                            "destinationZip"=>$this->destinationZip,
                            "originationLat"=>$this->originationLat,
                            "originationLng"=>$this->originationLng,
                            "destinationLat"=>$this->destinationLat,
                            "destinationLng"=>$this->destinationLng,
                            "distance"=>$this->distance,
                            "transportationMode"=>$this->transportationMode,
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

      $this->rootCustomerNeedsID = $result["rootCustomerNeedsID"];
      $this->entityID = $result["entityID"];
      $this->originationAddress1 = $result["originationAddress1"];
      $this->originationCity = $result["originationCity"];
      $this->originationState = $result["originationState"];
      $this->originationZip = $result["originationZip"];
      $this->destinationAddress1 = $result["destinationAddress1"];
      $this->destinationCity = $result["destinationCity"];
      $this->destinationState = $result["destinationState"];
      $this->destinationZip = $result["destinationZip"];
      $this->originationLat = $result["originationLat"];
      $this->originationLng = $result["originationLng"];
      $this->destinationLat = $result["destinationLat"];
      $this->destinationLng = $result["destinationLng"];
      $this->needsDataPoints = $result["needsDataPoints"];
      $this->transportationMode = $result["transportationMode"];
      $this->distance = $result["distance"];
      $this->status = $result["status"];
      $this->qty = $result["qty"];
      $this->availableDate = $result["availableDate"];
      $this->contactEmails = $result["contactEmails"];
      $this->createdAt = $result["createdAt"];
      $this->updatedAt = $result["updatedAt"];

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
        // Load the customer need data to send notification
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

    public function writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched") {

        try {

              $data = array(
                "needsMatchTypeID"=>$typeID,
                "customerEntityID"=>$customerEntityID,
                "carrierEntityID"=>$carrierEntityID,
                "customerNeedsID"=>$customerNeedsID,
                "carrierNeedsID"=>$carrierNeedsID,
                "status"=>$status,
                "createdAt" => date('Y-m-d H:i:s'),
                "updatedAt" => date('Y-m-d H:i:s')
              );
              $url = $api_host."/api/needs_match/";
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
                  $this->sendNeedsMatchNotification();
              }
        } catch (Exception $e) {
              // Something here
        }

    }

    public function sendNeedsMatchNotification() {

        $templateargs = array(
            "transform"=>1,
            "filter"=>"title,eq,Carrier Match Notification"
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
            $subject = "Nationwide Equipment Control - Carrier Match Notification";
        }

    }

    public function availabilityMatching($api_host, $id) {
        // Load the customer need data to send notification
        $this->load($api_host,$id);

        /* Go out to the carrier_needs table and get any matching needs based on:
        - Exact match of Origination and Destination City and State
        - Exact match of Origination City and State
        - Exact match of Destination City and State
        - Match of Origination City
        - Match of Destination City
        - Match of Origination State
        - Match of Destination State
        - Match of Origination and Destination City and State for expired or completed orders (historical data)
        - Match of Origination City and State for expired or completed orders (historical data)
        - Match of Destination City and State for expired or completed orders (historical data)
        - Match of Origination and Destination City for expired or completed orders (historical data)
        - Match of Origination City for expired or completed orders (historical data)
        - Match of Destination City for expired or completed orders (historical data)
        - Match of Origination and Destination State for expired or completed orders (historical data)
        - Match of Origination State for expired or completed orders (historical data)
        - Match of Destination State for expired or completed orders (historical data)
        */

        // Look at current data
        $args = array(
            "transform"=>1,
            "filter[]"=>"originationState,eq,".$this->originationState,
            "filter[]"=>"status,eq,Available",
            "filter[]"=>"availableDate,gt,".date("Y-m-d")
        );
        $url = API_HOST."/api/carrier_needs?".http_build_query($args);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'GET'
            )
        );
        $context  = stream_context_create($options);
        $result = json_decode(file_get_contents($url,false,$context),true);

        if (count($result) > 0) {

            for ($i = 0; $i < count($result['carrier_needs']); $i++ ) {

                // 1 - Exact Match of Origination City and State and Destination City and Destination State
                if ($result['carrier_needs'][$i]['originationCity'] == $this->originationCity &&
                    $result['carrier_needs'][$i]['originationState'] == $this->originationState &&
                    $result['carrier_needs'][$i]['destinationCity'] == $this->destinationCity &&
                    $result['carrier_needs'][$i]['destinationState'] == $this->destinationState) {
                    $type1found++;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(1, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 2 - Exact Match of Origination City and State
                if ($result['carrier_needs'][$i]['originationCity'] == $this->originationCity &&
                    $result['carrier_needs'][$i]['originationState'] == $this->originationState) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(2, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 3 - Exact Match of Destination City and State
                if ($result['carrier_needs'][$i]['destinationCity'] == $this->destinationCity &&
                    $result['carrier_needs'][$i]['destinationState'] == $this->destinationState) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(3, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 4 - Match of Origination City
                if ($result['carrier_needs'][$i]['originationCity'] == $this->originationCity) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(4, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 5 - Match of Destination City
                if ($result['carrier_needs'][$i]['destinationCity'] == $this->destinationCity) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(5, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 6 - Match of Origination State
                if ($result['carrier_needs'][$i]['originationState'] == $this->originationState) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(6, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 7 - Match of Destination State
                if ($result['carrier_needs'][$i]['destinationState'] == $this->destinationState) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(7, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

            }
        }

        // Look at historical data
        $args = array(
            "transform"=>1,
            "filter[]"=>"originationState,eq,".$this->originationState,
            "filter[]"=>"status,ne,Available",
            "filter[]"=>"availableDate,lt,".date("Y-m-d")
        );
        $url = API_HOST."/api/orders?".http_build_query($args);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'GET'
            )
        );
        $context  = stream_context_create($options);
        $result = json_decode(file_get_contents($url,false,$context),true);

        if (count($result) > 0) {

            for ($i = 0; $i < count($result['orders']); $i++ ) {

                // 8 - Match of Origination City and State and Destination City and Destination State Expired or Completed Orders
                if ($result['orders'][$i]['originationCity'] == $this->originationCity &&
                    $result['orders'][$i]['originationState'] == $this->originationState &&
                    $result['orders'][$i]['destinationCity'] == $this->destinationCity &&
                    $result['orders'][$i]['destinationState'] == $this->destinationState) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(8, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 9 - Match of Origination City and State Expired or Completed Orders
                if ($result['orders'][$i]['originationCity'] == $this->originationCity &&
                    $result['orders'][$i]['originationState'] == $this->originationState) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(9, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 10 - Match of Destination City and State Expired or Completed Orders
                if ($result['orders'][$i]['destinationCity'] == $this->destinationCity &&
                    $result['orders'][$i]['destinationState'] == $this->destinationState) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(10, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 11 - Match of Origination and Destination City Expired or Completed Orders
                if ($result['orders'][$i]['originationCity'] == $this->originationCity &&
                    $result['orders'][$i]['destinationCity'] == $this->destinationCity) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(11, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 12 - Match of Origination City Expired or Completed Orders
                if ($result['orders'][$i]['originationCity'] == $this->originationCity) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(12, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 13 - Match of Destination City Expired or Completed Orders
                if ($result['orders'][$i]['destinationCity'] == $this->destinationCity) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(13, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 14 - Match of Origination and Destination State Expired or Completed Orders
                if ($result['orders'][$i]['originationState'] == $this->originationState &&
                    $result['orders'][$i]['destinationState'] == $this->destinationState) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(14, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 15 - Match of Origination State Expired or Completed Orders
                if ($result['orders'][$i]['originationState'] == $this->originationState) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(15, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

                // 16 - Match of Destination State Expired or Completed Orders
                if ($result['orders'][$i]['destinationState'] == $this->destinationState) {
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $status = "Matched")
                    $this->writeNeedsMatch(16, 0, $carrierEntityID, $customerNeedsID, 0, "Matched");
                    break;
                }

            }
        }

        return  $type1found . "<br />" .
                $type2found . "<br />" .
                $type3found . "<br />" .
                $type4found . "<br />" .
                $type5found . "<br />" .
                $type6found . "<br />" .
                $type7found . "<br />" .
                $historytype1found . "<br />" .
                $historytype2found . "<br />" .
                $historytype3found . "<br />" .
                $historytype4found . "<br />" .
                $historytype5found . "<br />" .
                $historytype6found . "<br />" .
                $historytype7found . "<br />";



    }
}
