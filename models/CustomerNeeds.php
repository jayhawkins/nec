<?php

class CustomerNeeds
{

    /**
     * The table name
     *
     * @var string
     */
    public $table = "customer_needs";

    private $id;
    private $rootCustomerNeedsID;
    private $entityID;
    private $pickupInformation;
    private $originationAddress1;
    private $originationCity;
    private $originationState;
    private $originationZip;
    private $originationNotes;
    private $deliveryInformation;
    private $destinationAddress1;
    private $destinationCity;
    private $destinationState;
    private $destinationZip;
    private $destinationNotes;
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
    private $expirationDate;
    private $contactEmails;
    private $createdAt;
    private $updatedAt;

    public function __construct() {

    }

    public function post() {

    }

    public function indexgetavailability(&$db,$locationStatus,$stateFilter,$cityFilter,$entityid = 0,$entitytype = 0) {

        try {

              $query .= "select * from customer_needs
                         where status = 'Available'
                         and expirationDate >= '" . date('Y-m-d') . "'";

              if ($entityid > 0 && $entitytype == 2) {
                  $query .= " and entityID = '" . $entityid . "'";
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
                        $query .= " and originationState in (" . $sfilter . ")";
                    } else if ($locationStatus == "Destination") {
                        $query .= " and destinationState in (" . $sfilter . ")";
                    } else {
                        $query .= " and (originationState in (" . $sfilter . ") or destinationState in (" . $sfilter . "))";
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
                        $query .= " and originationCity in (" . $cfilter . ")";
                    } else if ($locationStatus == "Destination") {
                        $query .= " and destinationCity in (" . $cfilter . ")";
                    } else {
                        $query .= " and (originationCity in (" . $sfilter . ") or destinationCity in (" . $sfilter . "))";
                    }
              }

              $result = $db->query($query);

              if (count($result) > 0) {
                  echo "{ \"customer_needs\":".json_encode($result->fetchAll()) . "}";
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

    public function createFromExisting($api_host,$id,$rootCustomerNeedsID,$carrierID,$qty,$originationAddress1,$originationCity,$originationState,$originationZip,$destinationAddress1,$destinationCity,$destinationState,$destinationZip,$originationLat,$originationLng,$destinationLat,$destinationLng,$distance,$transportationMode,$transportation_mode,$transportation_type,$pickupDate,$deliveryDate,$rate,$rateType,$google_maps_api) {

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
                "rate"=>$rate,
                "rateType"=>$rateType,
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
                "expirationDate"=>$this->expirationDate,
                "contactEmails"=>$this->contactEmails,
                "createdAt" => date('Y-m-d H:i:s'),
                "updatedAt" => date('Y-m-d H:i:s')
              );
              $url = $api_host . "/" . API_ROOT . "/customer_needs/";
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

                        $url = $api_host . "/" . API_ROOT . "/customer_needs_commit/";
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

            /* Don't do this right now. Creating too many un-necessary legs
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
                                                        //"rootCustomerNeedsID"=>$id,
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
                                                        "expirationDate"=>$this->expirationDate,
                                                        "contactEmails"=>$this->contactEmails,
                                                        "createdAt" => date('Y-m-d H:i:s'),
                                                        "updatedAt" => date('Y-m-d H:i:s')
                                                    );

                                                    $url = $api_host . "/" . API_ROOT . "/customer_needs/";
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
                                                        //"rootCustomerNeedsID"=>$id,
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
                                                        "expirationDate"=>$this->expirationDate,
                                                        "contactEmails"=>$this->contactEmails,
                                                        "createdAt" => date('Y-m-d H:i:s'),
                                                        "updatedAt" => date('Y-m-d H:i:s')
                                                    );

                                                    $url = $api_host . "/" . API_ROOT . "/customer_needs/";
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
                  */


                  // If quantity was not the total quantity for the need, we need to create another leg of the original need with adjusted quantity
                  if ($qty != $this->qty) {

                        $newqty = $this->qty - $qty;

                        $data = array(
                            "rootCustomerNeedsID"=>$this->rootCustomerNeedsID,
                            "qty"=>$newqty,
                            "rate"=>$rate,
                            "rateType"=>$rateType,
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
                            "expirationDate"=>$this->expirationDate,
                            "contactEmails"=>$this->contactEmails,
                            "createdAt" => date('Y-m-d H:i:s'),
                            "updatedAt" => date('Y-m-d H:i:s')
                        );

                        $url = $api_host . "/" . API_ROOT . "/customer_needs/";
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

    // Created for not creating additional Availability records - Not currently implemented
    public function commitToNeed($api_host,$id,$rootCustomerNeedsID,$carrierID,$qty,$originationAddress1,$originationCity,$originationState,$originationZip,$destinationAddress1,$destinationCity,$destinationState,$destinationZip,$originationLat,$originationLng,$destinationLat,$destinationLng,$distance,$transportationMode,$transportation_mode,$transportation_type,$pickupDate,$deliveryDate,$google_maps_api) {

          /******** WE ARE NOT USING THE LNG AND LAT FROM THIS CALL - WE WILL NEED TO GO GET THE GEOCODE BASED ON THE NEW ORIGINATION AND DESTINATION *****/

          // Load the customer need data to send notification
          $this->load($api_host,$id);

          $original_originationaddress = $this->originationCity . ", " . $this->originationState;
          $original_destinationaddress = $this->destinationCity . ", " . $this->destinationState;

          $entered_originationaddress = $originationCity . ", " . $originationState;
          $entered_destinationaddress = $destinationCity . ", " . $destinationState;

          try {

              $data = array(
                    "customerNeedsID"=>$id,
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

                $url = $api_host . "/" . API_ROOT . "/customer_needs_commit/";
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

          } catch (Exception $e) {
              return $e;
          }

          return "success";

    }

    public function load($api_host,$id) {

      $args = array(
            "transform"=>"1"
      );

      $url = $api_host . "/" . API_ROOT . "/customer_needs/".$id."?".http_build_query($args);
      $options = array(
          'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'GET'
          )
      );
      $context  = stream_context_create($options);
      $result = json_decode(file_get_contents($url,false,$context),true);

      $this->id = $result["id"];
      $this->rootCustomerNeedsID = $result["rootCustomerNeedsID"];
      $this->entityID = $result["entityID"];
      $this->pickupInformation = $result["pickupInformation"];
      $this->originationAddress1 = $result["originationAddress1"];
      $this->originationCity = $result["originationCity"];
      $this->originationState = $result["originationState"];
      $this->originationZip = $result["originationZip"];
      $this->originationNotes = $result["originationNotes"];
      $this->deliveryInformation = $result["deliveryInformation"];
      $this->destinationAddress1 = $result["destinationAddress1"];
      $this->destinationCity = $result["destinationCity"];
      $this->destinationState = $result["destinationState"];
      $this->destinationZip = $result["destinationZip"];
      $this->destinationNotes = $result["destinationNotes"];
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
      $this->expirationDate = $result["expirationDate"];
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

    public function getContactEmails() { // Contact Emails are stored as a JSON object/array in a JSON type field
        return $this->contactEmails; // Return as an object
    }

    // Not Used Yet - just wanted to keep the code so we know how to loop through the carrier needs contacts
    public function sendToContacts() {

        $from = array("operations@nationwide-equipment.com" => "Nationwide Operations Control Manager");

        foreach ($this->contactEmails[0] as $key => $value) {
            $contactargs = array(
                  "transform"=>1,
                  "filter"=>"id,eq,".$key
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
            //return $contactresult;
            $to = array($contactresult['contacts'][0]['emailAddress'] => $contactresult['contacts'][0]['firstName'] . " " . $contactresult['contacts'][0]['lastName']);

            $returnObject = array();

            $body = "Hello " . $contactresult['contacts'][0]['firstName'] . ",<br /><br />";
            $body .= $templateresult['email_templates'][0]['body'];

                $subject = $templateresult['email_templates'][0]['subject'];
            if (count($templateresult) > 0) {
              try {
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

                            $body = "Hello " . $contactList[$i]['firstName'] . ",<br /><br />";
                            $body .= "The following emails were returned as failures: <br />";

                            $body .= implode("<br/>", $returnObject["failedRecipients"]);
                      }

                }

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
            $entitycontacturl = API_HOST_URL . "/contacts?".http_build_query($entitycontactargs);
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
            $templateurl = API_HOST_URL . "/email_templates?".http_build_query($templateargs);
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

            $returnObject = array();


            if (count($templateresult) > 0) {
              try {
                  for ($ec=0;$ec<count($entitycontactresult['contacts']);$ec++) {

                      $to = array($entitycontactresult['contacts'][$ec]['emailAddress'] => $entitycontactresult['contacts'][$ec]['firstName'] . " " . $entitycontactresult['contacts'][$ec]['lastName']);

                      $body = "Hello " . $entitycontactresult['contacts'][$ec]['firstName'] . ",<br /><br />";
                      $body .= $templateresult['email_templates'][0]['body'];

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
            $templateurl = API_HOST_URL . "/email_templates?".http_build_query($templateargs);
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
                        $contacturl = API_HOST_URL . "/contacts?".http_build_query($contactargs);
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

                        $returnObject = array();

                        $body = "Hello " . $contactresult['contacts'][0]['firstName'] . ",<br /><br />";
                        $body .= $templateresult['email_templates'][0]['body'];
                        if (count($templateresult) > 0) {
                          try {
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
                  }
              } catch (Exception $mailex) {
                return $mailex;
              }
            }

        }

        //return "Your Committment has been recorded, and NEC will be notified";
        return true;
    }

    public function writeNeedsMatch($api_host, $typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched") {

        try {

                if ($carrierNeedsID > 0) {
                    $matchargs = array(
                          "transform"=>1,
                          "filter[]"=>"entityID,eq,".$carrierEntityID,
                          "filter[]"=>"carrierNeedsID,eq,".$carrierNeedsID
                    );
                } else {
                    $matchargs = array(
                          "transform"=>1,
                          "filter[]"=>"entityID,eq,".$carrierEntityID,
                          "filter[]"=>"orderID,eq,".$orderID
                    );
                }
                $matchurl = $api_host . "/" . API_ROOT . "/needs_match?".http_build_query($matchargs);
                $matchoptions = array(
                    'http' => array(
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'GET'
                    )
                );
                $matchcontext  = stream_context_create($matchoptions);
                $matchresult = json_decode(file_get_contents($matchurl,false,$matchcontext),true);

                if (count($matchresult['needs_match']) == 0) {

                      $data = array(
                        "needsMatchTypeID"=>$typeID,
                        "customerEntityID"=>$customerEntityID,
                        "carrierEntityID"=>$carrierEntityID,
                        "customerNeedsID"=>$customerNeedsID,
                        "carrierNeedsID"=>$carrierNeedsID,
                        "orderID"=>$orderID,
                        "status"=>$status,
                        "notificationSentDate"=>'0000-00-00 00:00:00',
                        "createdAt" => date('Y-m-d H:i:s'),
                        "updatedAt" => date('Y-m-d H:i:s')
                      );

                      $url = $api_host . "/" . API_ROOT . "/needs_match/";
                      $options = array(
                          'http' => array(
                              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                              'method'  => 'POST',
                              'content' => http_build_query($data)
                          )
                      );
                      $context  = stream_context_create($options);
                      $result = json_decode(file_get_contents($url,false,$context),true);

                      return $result;
                } else {
                      return "Match Exists";
                }

        } catch (Exception $e) {
              // Something here
              return "Failed: " . $e;
        }

    }

    public function sendNeedsMatchNotification($api_host,$id, $contact) { // $id is the needs_match id NOT the customer_needs id

        $url = $api_host . "/" . API_ROOT . "/needs_match/".$id;
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'GET'
            )
        );
        $context  = stream_context_create($options);
        $result = json_decode(file_get_contents($url,false,$context),true);

        if ($result['id'] > 0) {

             if ($result['carrierNeedsID'] > 0) {
                $carrierargs = array(
                    "transform"=>1,
                    "filter"=>"id,eq,".$result['carrierNeedsID']
                );
                $carrierurl = $api_host . "/" . API_ROOT . "/carrier_needs?".http_build_query($carrierargs);
                $carrieroptions = array(
                    'http' => array(
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'GET'
                    )
                );
                $carriercontext  = stream_context_create($carrieroptions);
                $carrierresult = json_decode(file_get_contents($carrierurl,false,$carriercontext),true);

                foreach ($carrierresult['carrier_needs'][0]['contactEmails'][0] as $key => $value) {

                    $contactargs = array(
                          "transform"=>1,
                          "filter"=>"id,eq,".$key
                    );

                    $contacturl = $api_host . "/" . API_ROOT . "/contacts?".http_build_query($contactargs);
                    $contactoptions = array(
                        'http' => array(
                            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method'  => 'GET'
                        )
                    );
                    $contactcontext  = stream_context_create($contactoptions);
                    $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);

                    $to = array($contactresult['contacts'][0]['emailAddress'] => $contactresult['contacts'][0]['firstName'] . " " . $contactresult['contacts'][0]['lastName']);
                }

                $admincontacts = $contact->getContactsByEntity(0); // Get the admin contacts to send email copies to
                for ($ac=0;$ac<count($admincontacts);$ac++) {
                    $bcc = array($admincontacts[$ac]['emailAddress'] => $admincontacts[$ac]['firstName'] . " " . $admincontacts[$ac]['lastName']);
                }

            } else if ($result['orderID'] > 0) {

                $contactargs = array(
                      "transform"=>1,
                      "filter[0]"=>"entityID,eq,".$result['carrierEntityID'],
                      "filter[1]"=>"contactTypeID,eq,1",
                      "filter[2]"=>"status,eq,Active"
                );

                $contacturl = $api_host . "/" . API_ROOT . "/contacts?".http_build_query($contactargs);
                $contactoptions = array(
                    'http' => array(
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'GET'
                    )
                );
                $contactcontext  = stream_context_create($contactoptions);
                $contactresult = json_decode(file_get_contents($contacturl,false,$contactcontext),true);

                $to = array($contactresult['contacts'][0]['emailAddress'] => $contactresult['contacts'][0]['firstName'] . " " . $contactresult['contacts'][0]['lastName']);

                $admincontacts = $contact->getContactsByEntity(0); // Get the admin contacts to send email copies to
                for ($ac=0;$ac<count($admincontacts);$ac++) {
                    $bcc = array($admincontacts[$ac]['emailAddress'] => $admincontacts[$ac]['firstName'] . " " . $admincontacts[$ac]['lastName']);
                }
            }

            $templateargs = array(
                "transform"=>1,
                "filter[]"=>"title,eq,Carrier Match Notification"
            );
            $templateurl = $api_host . "/" . API_ROOT . "/email_templates?".http_build_query($templateargs);
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


            $fromcontactargs = array(
                  "transform"=>1,
                  "filter[0]"=>"entityID,eq,".$this->entityID,
                  "filter[1]"=>"contactTypeID,eq,1",
                  "filter[2]"=>"status,eq,Active"
            );

            $fromcontacturl = $api_host . "/" . API_ROOT . "/contacts?".http_build_query($fromcontactargs);
            $fromcontactoptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $fromcontactcontext  = stream_context_create($fromcontactoptions);
            $fromcontactresult = json_decode(file_get_contents($fromcontacturl,false,$fromcontactcontext),true);

            $from = array($fromcontactresult['contacts'][0]['emailAddress'] => $fromcontactresult['contacts'][0]['firstName'] . " " . $fromcontactresult['contacts'][0]['lastName']);

            $returnObject = array();

            $body = "Hello " . $contactresult['contacts'][0]['firstName'] . ",<br /><br />";
            $body .= $templateresult['email_templates'][0]['body'];
            if (count($templateresult) > 0) {

                  try {
                        //echo "Sending email notification to: " . print_r($to);
                        $returnObject = sendmail($to, $subject, $body, $from, '', $bcc);
                        //echo "Notification Sent<br />\n";
                        $matchdata = array(
                              "status"=>"Notification Sent",
                              "notificationSentDate"=>date('Y-m-d H:i:s')
                        );
                        $matchurl = API_HOST_URL . "/needs_match/".$id;
                        $matchoptions = array(
                            'http' => array(
                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method'  => 'PUT',
                                'content' => http_build_query($matchdata)
                            )
                        );

                        $matchcontext  = stream_context_create($matchoptions);
                        $matchresult = json_decode(file_get_contents($matchurl,false,$matchcontext),true);

                        // Are there any failed emails?
                        if(sizeof($returnObject["failedRecipients"]) > 0){


                        $adminFrom = array("operations@nationwide-equipment.com" => "Nationwide Operations Control Manager");
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

                                    $adminReturnObject = sendmail($adminTo, $adminSubject, $adminBody, $adminFrom);
                              }

                        }

                  } catch (Exception $mailex) {
                    return $mailex;
                  }

                }
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

        $type1found = 0;
        $type2found = 0;
        $type3found = 0;
        $type4found = 0;
        $type5found = 0;
        $type6found = 0;
        $type7found = 0;
        $historytype8found = 0;
        $historytype9found = 0;
        $historytype10found = 0;
        $historytype11found = 0;
        $historytype12found = 0;
        $historytype13found = 0;
        $historytype14found = 0;
        $historytype15found = 0;
        $historytype16found = 0;

        // Look at current data
        $args = array(
            "transform"=>1,
            "filter[0]"=>"originationState,eq,".$this->originationState,
            "filter[1]"=>"status,eq,Available",
            "filter[2]"=>"availableDate,ge,".date("Y-m-d 00:00:00")
        );
        $url = API_HOST_URL . "/carrier_needs?".http_build_query($args);
        //echo $url . "<br />\n";
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'GET'
            )
        );
        $context  = stream_context_create($options);
        $result = json_decode(file_get_contents($url,false,$context),true);

        if (count($result['carrier_needs']) > 0) {

            //echo "availabilityMatching() - Carrier Needs count: " . count($result['carrier_needs']) . "<br />\n";

            for ($i = 0; $i < count($result['carrier_needs']); $i++ ) {

                //echo $result['carrier_needs'][$i]['originationCity'] . "<br />\n";
                //echo $result['carrier_needs'][$i]['originationState'] . "<br />\n";
                //echo $result['carrier_needs'][$i]['destinationCity'] . "<br />\n";
                //echo $result['carrier_needs'][$i]['destinationState'] . "<br />\n";

                // 1 - Exact Match of Origination City and State and Destination City and Destination State
                if ($result['carrier_needs'][$i]['originationCity'] == $this->originationCity &&
                    $result['carrier_needs'][$i]['originationState'] == $this->originationState &&
                    $result['carrier_needs'][$i]['destinationCity'] == $this->destinationCity &&
                    $result['carrier_needs'][$i]['destinationState'] == $this->destinationState) {
                    $type1found++;
                    $carrierEntityID = $result['carrier_needs'][$i]['entityID'];
                    $carrierNeedsID = $result['carrier_needs'][$i]['id'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 1, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, "Matched");
                    //echo "writeNeedsMatch($api_host, 1, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, \"Matched\")";
                    break;
                }

                // 2 - Exact Match of Origination City and State
                if ($result['carrier_needs'][$i]['originationCity'] == $this->originationCity &&
                    $result['carrier_needs'][$i]['originationState'] == $this->originationState) {
                    $type2found++;
                    $carrierEntityID = $result['carrier_needs'][$i]['entityID'];
                    $carrierNeedsID = $result['carrier_needs'][$i]['id'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 2, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, "Matched");
                    //echo "writeNeedsMatch($api_host, 2, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, \"Matched\")";
                    break;
                }

                // 3 - Exact Match of Destination City and State
                if ($result['carrier_needs'][$i]['destinationCity'] == $this->destinationCity &&
                    $result['carrier_needs'][$i]['destinationState'] == $this->destinationState) {
                    $type3found++;
                    $carrierEntityID = $result['carrier_needs'][$i]['entityID'];
                    $carrierNeedsID = $result['carrier_needs'][$i]['id'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 3, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, "Matched");
                    //echo "writeNeedsMatch($api_host, 3, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, \"Matched\")";
                    break;
                }

                // 4 - Match of Origination City
                if ($result['carrier_needs'][$i]['originationCity'] == $this->originationCity) {
                    $type4found++;
                    $carrierEntityID = $result['carrier_needs'][$i]['entityID'];
                    $carrierNeedsID = $result['carrier_needs'][$i]['id'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 4, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, "Matched");
                    //echo "writeNeedsMatch($api_host, 4, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, \"Matched\")";
                    break;
                }

                // 5 - Match of Destination City
                if ($result['carrier_needs'][$i]['destinationCity'] == $this->destinationCity) {
                    $type5found++;
                    $carrierEntityID = $result['carrier_needs'][$i]['entityID'];
                    $carrierNeedsID = $result['carrier_needs'][$i]['id'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 5, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, "Matched");
                    //echo "writeNeedsMatch($api_host, 5, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, \"Matched\")";
                    break;
                }

                // 6 - Match of Origination State
                if ($result['carrier_needs'][$i]['originationState'] == $this->originationState) {
                    $type6found++;
                    $carrierEntityID = $result['carrier_needs'][$i]['entityID'];
                    $carrierNeedsID = $result['carrier_needs'][$i]['id'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 6, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, "Matched");
                    //echo "writeNeedsMatch($api_host, 6, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, \"Matched\")";
                    break;
                }

                // 7 - Match of Destination State
                if ($result['carrier_needs'][$i]['destinationState'] == $this->destinationState) {
                    $type7found++;
                    $carrierEntityID = $result['carrier_needs'][$i]['entityID'];
                    $carrierNeedsID = $result['carrier_needs'][$i]['id'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 7, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, "Matched");
                    //echo "writeNeedsMatch($api_host, 7, $customerEntityID, $carrierEntityID, $customerNeedsID, $carrierNeedsID, 0, \"Matched\")";
                    break;
                }

            }
        }

        // Look at historical data
        $args = array(
            "transform"=>1,
            "filter[0]"=>"originationState,eq,".$this->originationState,
            "filter[1]"=>"createdAt,lt,".date("Y-m-d 00:00:00")
        );
        $url = API_HOST_URL . "/order_details?".http_build_query($args);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'GET'
            )
        );
        $context  = stream_context_create($options);
        $result = json_decode(file_get_contents($url,false,$context),true);

        if (count($result['order_details']) > 0) {

            //echo "availabilityMatching() - Order Details count: " . count($result['order_details']) . "<br />\n";

            for ($i = 0; $i < count($result['order_details']); $i++ ) {

                // 8 - Match of Origination City and State and Destination City and Destination State Expired or Completed Orders
                if ($result['order_details'][$i]['originationCity'] == $this->originationCity &&
                    $result['order_details'][$i]['originationState'] == $this->originationState &&
                    $result['order_details'][$i]['destinationCity'] == $this->destinationCity &&
                    $result['order_details'][$i]['destinationState'] == $this->destinationState) {
                    $historytype8found++;
                    $carrierEntityID = $result['order_details'][$i]['carrierID'];
                    $orderID = $result['order_details'][$i]['orderID'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 8, $customerEntityID, $carrierEntityID, $customerNeedsID, 0, $orderID, "Matched");
                    break;
                }

                // 9 - Match of Origination City and State Expired or Completed Orders
                if ($result['order_details'][$i]['originationCity'] == $this->originationCity &&
                    $result['order_details'][$i]['originationState'] == $this->originationState) {
                    $historytype9found++;
                    $carrierEntityID = $result['order_details'][$i]['carrierID'];
                    $orderID = $result['order_details'][$i]['orderID'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 9, $customerEntityID, $carrierEntityID, $customerNeedsID, 0, $orderID, "Matched");
                    break;
                }

                // 10 - Match of Destination City and State Expired or Completed Orders
                if ($result['order_details'][$i]['destinationCity'] == $this->destinationCity &&
                    $result['order_details'][$i]['destinationState'] == $this->destinationState) {
                    $historytype10found++;
                    $carrierEntityID = $result['order_details'][$i]['carrierID'];
                    $orderID = $result['order_details'][$i]['orderID'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 10, $customerEntityID, $carrierEntityID, $customerNeedsID, 0, $orderID, "Matched");
                    break;
                }

                // 11 - Match of Origination and Destination City Expired or Completed Orders
                if ($result['order_details'][$i]['originationCity'] == $this->originationCity &&
                    $result['order_details'][$i]['destinationCity'] == $this->destinationCity) {
                    $historytype11found++;
                    $carrierEntityID = $result['order_details'][$i]['carrierID'];
                    $orderID = $result['order_details'][$i]['orderID'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 11, $customerEntityID, $carrierEntityID, $customerNeedsID, 0, $orderID, "Matched");
                    break;
                }

                // 12 - Match of Origination City Expired or Completed Orders
                if ($result['order_details'][$i]['originationCity'] == $this->originationCity) {
                    $historytype12found++;
                    $carrierEntityID = $result['order_details'][$i]['carrierID'];
                    $orderID = $result['order_details'][$i]['orderID'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 12, $customerEntityID, $carrierEntityID, $customerNeedsID, 0, $orderID, "Matched");
                    break;
                }

                // 13 - Match of Destination City Expired or Completed Orders
                if ($result['order_details'][$i]['destinationCity'] == $this->destinationCity) {
                    $historytype13found++;
                    $carrierEntityID = $result['order_details'][$i]['carrierID'];
                    $orderID = $result['order_details'][$i]['orderID'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 13, $customerEntityID, $carrierEntityID, $customerNeedsID, 0, $orderID, "Matched");
                    break;
                }

                // 14 - Match of Origination and Destination State Expired or Completed Orders
                if ($result['order_details'][$i]['originationState'] == $this->originationState &&
                    $result['order_details'][$i]['destinationState'] == $this->destinationState) {
                    $historytype14found++;
                    $carrierEntityID = $result['order_details'][$i]['carrierID'];
                    $orderID = $result['order_details'][$i]['orderID'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 14, $customerEntityID, $carrierEntityID, $customerNeedsID, 0, $orderID, "Matched");
                    break;
                }

                // 15 - Match of Origination State Expired or Completed Orders
                if ($result['order_details'][$i]['originationState'] == $this->originationState) {
                    $historytype15found++;
                    $carrierEntityID = $result['order_details'][$i]['carrierID'];
                    $orderID = $result['order_details'][$i]['orderID'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 15, $customerEntityID, $carrierEntityID, $customerNeedsID, 0, $orderID, "Matched");
                    break;
                }

                // 16 - Match of Destination State Expired or Completed Orders
                if ($result['order_details'][$i]['destinationState'] == $this->destinationState) {
                    $historytype16found++;
                    $carrierEntityID = $result['order_details'][$i]['carrierID'];
                    $orderID = $result['order_details'][$i]['orderID'];
                    $customerNeedsID = $this->id;
                    $customerEntityID = $this->entityID;
                    //writeNeedsMatch($typeID, $customerEntityID = 0, $carrierEntityID = 0, $customerNeedsID = 0, $carrierNeedsID = 0, $orderID = 0, $status = "Matched")
                    $id = $this->writeNeedsMatch($api_host, 16, $customerEntityID, $carrierEntityID, $customerNeedsID, 0, $orderID, "Matched");
                    break;
                }

            }
        }
/*
        echo    $type1found . "<br />" .
                $type2found . "<br />" .
                $type3found . "<br />" .
                $type4found . "<br />" .
                $type5found . "<br />" .
                $type6found . "<br />" .
                $type7found . "<br />" .
                $historytype8found . "<br />" .
                $historytype9found . "<br />" .
                $historytype10found . "<br />" .
                $historytype11found . "<br />" .
                $historytype12found . "<br />" .
                $historytype13found . "<br />" .
                $historytype14found . "<br />" .
                $historytype15found . "<br />" .
                $historytype16found . "<br />";
*/
        return "success";

    }

}
