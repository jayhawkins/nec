<?php

class CarrierNeeds
{

    /**
     * The table name
     *
     * @var string
     */
    public $table = "carrier_needs";

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

    public function load($api_host,$id) {

      $args = array(
            "transform"=>"1"
      );

      $url = $api_host . "/" . API_ROOT . "/carrier_needs/".$id."?".http_build_query($args);
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
                "filter"=>"title,eq,Carrier Need Notification"
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
                $subject = "Nationwide Equipment Control - Carrier Need Notification";
            }

            $from = array("operations@nationwide-equipment.com" => "Nationwide Operations Control Manager");

            $returnObject = array();

            if (count($templateresult) > 0) {
              try {
                  for ($ec=0;$ec<count($entitycontactresult['contacts']);$ec++) {

                      $to = array($entitycontactresult['contacts'][$ec]['emailAddress'] => $entitycontactresult['contacts'][$ec]['firstName'] . " " . $entitycontactresult['contacts'][$ec]['lastName']);

                      $body = "<img src=\"" . HTTP_HOST . "/img/nec_logo.png\"><br /><br />";
                      $body .= "Hello " . $entitycontactresult['contacts'][$ec]['firstName'] . ",<br /><br />";
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

        return "Your Need Notification has been recorded, and Customers will be notified";
    }

    public function indexgetneeds(&$db,$locationStatus,$stateFilter,$cityFilter,$entityid = 0,$entitytype = 0) {

        try {

              $query .= "select * from carrier_needs
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
                  echo "{ \"carrier_needs\":".json_encode($result->fetchAll()) . "}";
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
