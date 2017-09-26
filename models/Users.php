<?php

class User
{
    public function __construct() {

    }

    public function loginapi($username,$password) {
        try {
              //$result = json_decode(file_get_contents(API_HOST.'/api/users?filter=username,eq,' . $username));
              //$result = json_decode(file_get_contents(API_HOST.'/api/users?include=members,entities&filter=username,eq,' . $username));

              $loginargs = array(
                            "include"=>"members,entities",
                            "filter[0]"=>"username,eq,".$username
              );
              $loginurl = API_HOST."/api/users?".http_build_query($loginargs);
              $loginoptions = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'GET'
                  )
              );
              $logincontext  = stream_context_create($loginoptions);
              $result = json_decode(file_get_contents($loginurl,false,$logincontext));

              if (count($result) > 0) {
                  if ($result->users->records[0][4] == "Active") {
                      if (password_verify($password, $result->users->records[0][3])) {
                        $_SESSION['userid'] = $result->users->records[0][0];
                        $_SESSION['usertypeid'] = $result->users->records[0][1];
                        $_SESSION['memberid'] = $result->members->records[0][0];
                        $_SESSION['entityid'] = $result->entities->records[0][0];
                        $_SESSION['entitytype'] = $result->entities->records[0][1];
                        unset($_SESSION['invalidPassword']);
                        return true;
                      } else {
                        unset($_SESSION['userid']);
                        unset($_SESSION['usertypeid']);
                        unset($_SESSION['memberid']);
                        unset($_SESSION['entityid']);
                        unset($_SESSION['entitytype']);
                        $_SESSION['invalidPassword'] = 'Password is invalid!';
                        return false;
                      }
                  } else {
                    $_SESSION['invalidPassword'] = 'Account Has Not Been Activated!';
                    return false;
                  }
              } else {
                return false;
              }
        } catch (Exception $e) { // The authorization query failed verification
              header('HTTP/1.1 404 Not Found');
              header('Content-Type: text/plain; charset=utf8');
              echo $e->getMessage();
              exit();
        }
    }

    public function registerapi($password,$firstName,$lastName,$title,$address1,$address2,$city,$state,$zip,$phone,$fax,$email,$entityName,$entityTypeID) {
      try {

            // url encode the address
            $address = urlencode($address1.", ".$city.", ".$state.", ".$zip);

            // google map geocode api url
            $url = "http://maps.google.com/maps/api/geocode/json?address={$address}";

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

            $entityurl = API_HOST.'/api/entities';
            $entitydata = array(
                        "name" => $entityName,
                        "entityTypeID" => $entityTypeID,
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
            // Now create the entity location
            $locationurl = API_HOST.'/api/locations';
            $locationdata = array(
                        "entityID" => $entityresult, // this will contain the new entities id
                        "locationTypeID" => 1,
                        "name" => "Headquarters",
                        "address1" => $address1,
                        "address2" => $address2,
                        "city" => $city,
                        "state" => $state,
                        "zip" => $zip,
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
            if ($entityresult > 0) {
                $entity_id = $entityresult;
                $_SESSION['entityid'] = $entity_id;
                $userurl = API_HOST.'/api/users';
                $userdata = array("username" => $email,
                          "password" => password_hash($password, PASSWORD_BCRYPT),
                          "status" => "Inactive",
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
                    $_SESSION['userid'] = $user_id;
                    unset($_SESSION['invalidPassword']);
                    $memberurl = API_HOST.'/api/members';
                    $memberdata = array(
                                "firstName" => $firstName,
                                "lastName" => $lastName,
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
                    $contacturl = API_HOST.'/api/contacts';
                    $contactdata = array(
                                "entityID" => $entity_id,
                                "contactTypeID" => 1,
                                "firstName" => $firstName,
                                "lastName" => $lastName,
                                "title" => $title,
                                "emailAddress" => $email,
                                "primaryPhone" => $phone,
                                "secondaryPhone" => '',
                                "fax" => $fax,
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
/* Don't use now
                    $admimargs = array(
                        "transform"=>1,
                        "columns"=>"id",
                        "filter"=>"entityID,eq,0"
                    );

                    $adminurl = API_HOST."/api/contacts?".http_build_query($admimargs);
                    $adminoptions = array(
                      'http' => array(
                          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                          'method'  => 'GET'
                      )
                    );

                    $admincontext  = stream_context_create($adminoptions);
                    $adminresult = json_decode(file_get_contents($adminurl,false,$admincontext));

                    $admincontactid = $adminresult->contacts[0]->id;
*/

                    // Update entity contact id with newly created contact
                    $entityupdateurl = API_HOST.'/api/entities/' . $entity_id;
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
                        $_SESSION['memberid'] = $member_id;
                        $code = 0;
                        $numSent = 0;
                        $to = array($email => $firstName . " " . $lastName);
                        $from = array('jaycarl.hawkins@gmail.com' => 'Jay Hawkins');
                        //$templateresult = json_decode(file_get_contents(API_HOST.'/api/email_templates?filter=title,eq,Authorize Account'));

                        $templateargs = array("filter"=>"title,eq,Authorize Account");
                        $templateurl = API_HOST."/api/email_templates?".http_build_query($templateargs);
                        $templateoptions = array(
                            'http' => array(
                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method'  => 'GET'
                            )
                        );
                        $templatecontext  = stream_context_create($templateoptions);
                        $templateresult = json_decode(file_get_contents($templateurl,false,$templatecontext));
                        $subject = $templateresult->email_templates->records[0][6];
                        $body = "Hello " . $firstName . ",<br /><br />";
                        $body .= $templateresult->email_templates->records[0][2];
                        $body .= "<a href='".HTTP_HOST."/verifyaccount/".$user_id."/".$code."'>Click HERE to Activate!</a>";
                        if (count($templateresult) > 0) {
                          try {
                            $numSent = sendmail($to, $subject, $body, $from);
                          } catch (Exception $mailex) {
                            echo $mailex;
                          }
                        }
                        // Now that you have a member, update the memberID in the entity record
                        if ($member_id > 0) {
                            $updateentityurl = API_HOST.'/api/entities/'.$entity_id;
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
                    } else {
                        return "There was an issue with your member information. Please verify your information.";  // There was an issue, let the router know something failed!
                    }
                } else {
                    return "There was an issue with your Username information. Please verify you are using a valid email address.";  // There was an issue, let the router know something failed!
                }

                return "success"; // Return true to the router so it knows everything was created!

            } else {
              return "There was a possible issue with your location information. Please verify you are using a valid address.";  // There was an issue, let the router know something failed!
            }
      } catch (Exception $e) { // The authorization query failed verification
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: text/plain; charset=utf8');
            return "Catch Exception: " . $e->getMessage();
      }
    }

    public function verifyaccount($id,$code) {
      try {
            $userurl = API_HOST.'/api/users/'.$id;
            $userdata = array("status" => "Active",
                      "updatedAt" => date('Y-m-d H:i:s')
            );
            // use key 'http' even if you send the request to https://...
            $useroptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'PUT',
                    'content' => http_build_query($userdata)
                )
            );
            $usercontext  = stream_context_create($useroptions);
            $result = json_decode(file_get_contents($userurl,false,$usercontext));
            if ($result > 0) {
                return true;
            } else {
                return false;
            }
      } catch (Exception $e) { // The authorization query failed verification
            header('HTTP/1.1 404 Not Found');
            header('Content-Type: text/plain; charset=utf8');
            echo $e->getMessage();
            exit();
      }
    }
}

//$user = new User();
