<?php

class Entities
{

    /**
     * The table name
     *
     * @var string
     */
    public $table = "entities";

    public function get() {

    }

    public function put($entityName,$configurationSettings) {
        try {
            $entityurl = API_HOST_URL . '/entities/'.$_SESSION['entityid'];
              $entitydata = array(
                          "name" => $entityName,
                          "configuration_settings"=>$configurationSettings,
                          "updatedAt" => date('Y-m-d H:i:s')
              );
              // use key 'http' even if you send the request to https://...
              $entityoptions = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'PUT',
                      'content' => http_build_query($entitydata)
                  )
              );
              $entitycontext  = stream_context_create($entityoptions);
              $entityresult = file_get_contents($entityurl, false, $entitycontext);
              return true;
        } catch (Exception $e) { // The authorization query failed verification
              header('HTTP/1.1 401 Unauthorized');
              header('Content-Type: text/plain; charset=utf8');
              return $e->getMessage();
        }
    }

    public function post($email,$password,$entityTypeID,$entityName,$contactID,$rateType,$negotiatedRate,$towAwayRateMin,$towAwayRateMax,$towAwayRateType,$loadOutRateMin,$loadOutRateMax,$loadOutRateType,$configurationSettings,$firstName,$lastName,$address1,$address2,$city,$state,$zip,$latitude,$longitude,$title,$phone,$phoneExt,$fax) {
        try {
              // Setup users record first
              $userurl = API_HOST_URL . '/users';
              $userdata = array(
                          "username" => $email,
                          "password" => $password,
                          "userTypeID" => 1,
                          "createdAt" => date("Y-m-d H:i:s"),
                          "updatedAt" => date('Y-m-d H:i:s')
              );
              // use key 'http' even if you send the request to https://...
              $useroptions = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'POST',
                      'content' => http_build_query($userdata)
                  )
              );
              $usercontext  = stream_context_create($useroptions);
              $userresult = file_get_contents($userurl, false, $usercontext);

              // Create entity
              $entityurl = API_HOST_URL . '/entities';
              $entitydata = array(
                          "entityTypeID" => $entityTypeID,
                          "name" => $entityName,
                          "rateType" => $rateType,
                          "negotiatedRate" => $negotiatedRate,
                          "towAwayRateMin" => $towAwayRateMin,
                          "towAwayRateMax" => $towAwayRateMax,
                          "towAwayRateType" => $towAwayRateType,
                          "loadOutRateMin" => $loadOutRateMin,
                          "loadOutRateMax" => $loadOutRateMax,
                          "loadOutRateType" => $loadOutRateType,
                          "configuration_settings"=>$configurationSettings,
                          "createdAt" => date('Y-m-d H:i:s'),
                          "updatedAt" => date('Y-m-d H:i:s')
              );
              // use key 'http' even if you send the request to https://...
              $entityoptions = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'POST',
                      'content' => http_build_query($entitydata)
                  )
              );
              $entitycontext  = stream_context_create($entityoptions);
              $entityresult = file_get_contents($entityurl, false, $entitycontext);

              // Create member
              $memberurl = API_HOST_URL . '/members';
              $memberdata = array(
                          "userID" => $userresult,
                          "entityID" => $entityresult,
                          "firstName" => $firstName,
                          "lastName" => $lastName,
                          "createdAt" => date('Y-m-d H:i:s'),
                          "updatedAt" => date('Y-m-d H:i:s')
              );
              // use key 'http' even if you send the request to https://...
              $memberoptions = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'POST',
                      'content' => http_build_query($memberdata)
                  )
              );
              $membercontext  = stream_context_create($memberoptions);
              $memberresult = file_get_contents($memberurl, false, $membercontext);

              // Create location
              $locationurl = API_HOST_URL . '/locations';
              $locationdata = array(
                          "entityID" => $entityresult,
                          "locationTypeID" => 1,
                          "name" => "Headquarters",
                          "address1" => $address1,
                          "address2" => $address2,
                          "city" => $city,
                          "state" => $state,
                          "zip" => $zip,
                          "latitude" => $latitude,
                          "longitude" => $longitude,
                          "status" => "Active",
                          "createdAt" => date('Y-m-d H:i:s'),
                          "updatedAt" => date('Y-m-d H:i:s')
              );
              // use key 'http' even if you send the request to https://...
              $locationoptions = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'POST',
                      'content' => http_build_query($locationdata)
                  )
              );
              $locationcontext  = stream_context_create($locationoptions);
              $locationresult = file_get_contents($locationurl, false, $locationcontext);

              // Create contact
              $contacturl = API_HOST_URL . '/contacts';
              $contactdata = array(
                          "entityID" => $entityresult,
                          "contactTypeID" => 1,
                          "firstName" => $firstName,
                          "lastName" => $lastName,
                          "title" => $title,
                          "emailAddress" => $email,
                          "primaryPhone" => $phone,
                          "secondaryPhone" => $phoneExt,
                          "fax" => $fax,
                          "contactRating" => 0,
                          "status" => "Active",
                          "createdAt" => date('Y-m-d H:i:s'),
                          "updatedAt" => date('Y-m-d H:i:s')
              );
              // use key 'http' even if you send the request to https://...
              $contactoptions = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'POST',
                      'content' => http_build_query($contactdata)
                  )
              );
              $contactcontext  = stream_context_create($contactoptions);
              $contactresult = file_get_contents($contacturl, false, $contactcontext);

              // Update entity with member id
              $entityurl = API_HOST_URL . '/entities/' . $entityresult;
              $entitydata = array(
                          "contactID" => $contactID,
                          "assignedMemberID" => $memberresult,
                          "updatedAt" => date('Y-m-d H:i:s')
              );
              // use key 'http' even if you send the request to https://...
              $entityoptions = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'PUT',
                      'content' => http_build_query($entitydata)
                  )
              );
              $entitycontext  = stream_context_create($entityoptions);
              $entityresult = file_get_contents($entityurl, false, $entitycontext);

              return true;
        } catch (Exception $e) { // The authorization query failed verification
              header('HTTP/1.1 401 Unauthorized');
              header('Content-Type: text/plain; charset=utf8');
              return $e->getMessage();
        }
    }

}
