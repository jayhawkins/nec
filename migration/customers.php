<?php

session_start();

use flight\Engine;
require '../vendor/autoload.php';

require_once '../../nec_config.php';

Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS ), function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
});

// Get the Carrier info to migrate
$db = Flight::db();
$dbhandle = new $db('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS);
$result = $dbhandle->query(" select * from customer_import where Email > '' AND Main_Contact > '' ");

// Fields
/*
Entities:  Company

Contact:   Phone
Contact:   Fax

Locations: Street
Locations: City
Locations: State
Locations: Zip

Insurance_Carriers: Insurance Company
Insurance_Carriers: Insurance Contact
Insurance_Carriers: Insurance Phone
Insurance_Carriers: Insurance Policy Expiration Date

*/
foreach ($result as $row) {

      print_r($row);
      echo "<br /><br />\n";

      try {

            // url encode the address
            $address = urlencode($row['Street'] .", ".$row['City'].", ".$row['State'].", ".$row['Zip']);

            // google map geocode api url
            $url = "https://maps.google.com/maps/api/geocode/json?key=".GOOGLE_MAPS_API."&address={$address}";

            // get the json response
            $resp_json = file_get_contents($url);

            // decode the json
            $resp = json_decode($resp_json, true);

            // response status will be 'OK', if able to geocode given address
            if($resp['status']=='OK'){

                // get the important data
                $lati = $resp['results'][0]['geometry']['location']['lat'];
                $longi = $resp['results'][0]['geometry']['location']['lng'];
                $formatted_address = $resp['results'][0]['formatted_address'];

                // verify if data is complete
                if($lati && $longi && $formatted_address){

                    // put the data in the array
                    $data_arr = array();

                    array_push(
                        $data_arr,
                            $lati,
                            $longi,
                            $formatted_address
                        );

                } else {
                  array_push(
                      $data_arr,
                          0.00,
                          0.00,
                          $formatted_address
                      );
                }

            } else {
              array_push(
                  $data_arr,
                      0.00,
                      0.00,
                      $formatted_address
                  );
            }

            $entityurl = API_HOST_URL . '/entities';
            $entitydata = array(
                        "name" => $row['Company'],
                        "entityTypeID" => 1,
                        "assignedMemberID" => 0,
                        "status" => "Active",
                        "entityRating" => 0,
                        "createdAt" => date('Y-m-d H:i:s'),
                        "updatedAt" => date('Y-m-d H:i:s')
            );
            //print_r($entitydata)."<br/>\n";
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
            //echo $entityresult."<br/>\n";

//---------------------------------------------------------------------------------
            // Now create the entity location
            $locationurl = API_HOST_URL . '/locations';
            $locationdata = array(
                        "entityID" => $entityresult, // this will contain the new entities id
                        "locationTypeID" => 1,
                        "name" => "Headquarters",
                        "address1" => $row['Street'],
                        "address2" => '',
                        "city" => $row['City'],
                        "state" => $row['State'],
                        "zip" => $row['Zip'],
                        "latitude" => $data_arr[0],
                        "longitude" => $data_arr[1],
                        "timeZone" => '',
                        "createdAt" => date('Y-m-d H:i:s'),
                        "updatedAt" => date('Y-m-d H:i:s')
            );
            //print_r($locationdata);
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

            // Now create the entity mailing location
            $locationurl = API_HOST_URL . '/locations';
            $locationdata = array(
                        "entityID" => $entityresult, // this will contain the new entities id
                        "locationTypeID" => 9,
                        "name" => "Headquarters",
                        "address1" => $row['M-Street'],
                        "address2" => '',
                        "city" => $row['M-City'],
                        "state" => $row['State'],
                        "zip" => $row['M-Zip'],
                        "latitude" => $data_arr[0],
                        "longitude" => $data_arr[1],
                        "timeZone" => '',
                        "createdAt" => date('Y-m-d H:i:s'),
                        "updatedAt" => date('Y-m-d H:i:s')
            );
            //print_r($locationdata);
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
            //echo $locationresult;
            //die();
//---------------------------------------------------------------------------------

            if ($entityresult > 0) {
                $entity_id = $entityresult;
                $userurl = API_HOST_URL . '/users';
                $userdata = array("username" => $row['Email'],
                          "password" => password_hash($row['Zip'], PASSWORD_BCRYPT),
                          "status" => "Inactive",
                          "userTypeID" => 1,
                          "createdAt" => date('Y-m-d H:i:s'),
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
                if ($userresult > 0) {
                    $user_id = $userresult;
                    $memberurl = API_HOST_URL . '/members';
                    $importName = explode(" ", $row['Main_Contact']);
                    $memberdata = array(
                                "firstName" => $importName[0],
                                "lastName" => $importName[1],
                                "userID" => $user_id,
                                "entityID" => $entity_id,
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
                    // Insert contacts data
                    $contacturl = API_HOST_URL . '/contacts';
                    $phone = $row['Phone'];
                    if (!empty($row['Extension'])) {
                        $phone .= " -x" . $row['Extension'];
                    }
                    $contactdata = array(
                                "entityID" => $entity_id,
                                "contactTypeID" => 1,
                                "firstName" => $importName[0],
                                "lastName" => $importName[1],
                                "title" => '',
                                "emailAddress" => $row['Email'],
                                "primaryPhone" => $phone,
                                "secondaryPhone" => $row['Mobile'],
                                "fax" => $row['Fax'],
                                "contactRating" => 0,
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
//----------------------------------------------------------------------------

                    // Update entity contact id with newly created contact
                    $entityupdateurl = API_HOST_URL . '/entities/' . $entity_id;
                    $entityupdatedata = array("contactID" => $contactresult);
                    //print_r($entityupdatedata)."<br/>\n";
                    $entityupdateoptions = array(
                        'http' => array(
                            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method'  => 'PUT',
                            'content' => http_build_query($entityupdatedata)
                        )
                    );
                    $entityupdatecontext  = stream_context_create($entityupdateoptions);
                    $entityupdateresult = file_get_contents($entityupdateurl, false, $entityupdatecontext);
//----------------------------------------------------------------------------

                    if ($memberresult > 0) {
                        $member_id = $memberresult;
                        $code = 0;
                        $numSent = 0;
                        $to = array($row['Email'] => $importName[0] . " " . $importName[1]);
                        $from = array("operations@nationwide-equipment.com" => "Nationwide Operations Control Manager");
                        //$templateresult = json_decode(file_get_contents(API_HOST_URL . '/email_templates?filter=title,eq,Authorize Account'));

                        $templateargs = array("filter"=>"title,eq,Authorize Account");
                        $templateurl = API_HOST_URL . "/email_templates?".http_build_query($templateargs);
                        $templateoptions = array(
                            'http' => array(
                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method'  => 'GET'
                            )
                        );

                        /* Send "Set Password" email for migrated carriers */
                        $templatecontext  = stream_context_create($templateoptions);
                        $templateresult = json_decode(file_get_contents($templateurl,false,$templatecontext));
                        $subject = $templateresult->email_templates->records[0][6];
                        $body = "Hello " . $firstName . ",<br /><br />";
                        $body .= $templateresult->email_templates->records[0][2];
                        $body .= "<a href='".HTTP_HOST."/setmigratedpassword/".$user_id."/".$row['Zip']."'>Click HERE to Reset Password and Activate Account!</a>";
                        //if (count($templateresult) > 0) {
                        //  try {
                        //    $numSent = sendmail($to, $subject, $body, $from);
                        //  } catch (Exception $mailex) {
                        //    echo $mailex;
                        //  }
                        //}


                        // Now that you have a member, update the memberID in the entity record
                        if ($member_id > 0) {
                            $updateentityurl = API_HOST_URL . '/entities/'.$entity_id;
                            $updateentitydata = array(
                                        "assignedMemberID" => $member_id
                            );
                            // use key 'http' even if you send the request to https://...
                            $updateentityoptions = array(
                                'http' => array(
                                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                    'method'  => 'PUT',
                                    'content' => http_build_query($updateentitydata)
                                )
                            );
                            $updateentitycontext  = stream_context_create($updateentityoptions);
                            $updateentityresult = file_get_contents($updateentityurl, false, $updateentitycontext);
                        }

                        echo "Got er done!<br /><br />\n";

                    } else {
                        return "There was an issue with your member information. Please verify your information.";  // There was an issue, let the router know something failed!
                    }
                } else {
                    return "There was an issue with your Username information. Please verify you are using a valid email address.";  // There was an issue, let the router know something failed!
                }
            } else {
              return "There was a possible issue with your location information. Please verify you are using a valid address.";  // There was an issue, let the router know something failed!
            }
      } catch (Exception $e) { // The authorization query failed verification
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: text/plain; charset=utf8');
            return "Catch Exception: " . $e->getMessage();
      }

}

echo "Complete";
