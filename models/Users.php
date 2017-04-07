<?php

class User
{
    public function __construct() {

    }

    public function loginapi($username,$password) {
        try {
              //$result = json_decode(file_get_contents(API_HOST.'/api/users?filter=username,eq,' . $username));
              $result = json_decode(file_get_contents(API_HOST.'/api/users?include=members,entities&filter=username,eq,' . $username));
              //print_r($result);
              //die();
              if (count($result) > 0) {
                  if ($result->users->records[0][3] == "Active") {
                      if (password_verify($password, $result->users->records[0][2])) {
                        $_SESSION['userid'] = $result->users->records[0][0];
                        $_SESSION['memberid'] = $result->members->records[0][0];
                        $_SESSION['entityid'] = $result->entities->records[0][0];
                        unset($_SESSION['invalidPassword']);
                        return true;
                      } else {
                        unset($_SESSION['userid']);
                        unset($_SESSION['memberid']);
                        unset($_SESSION['entityid']);
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
                        "latitude" => 0.00,
                        "longitude" => 0.00,
                        "timeZone" => '',
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
                    if ($memberresult > 0) {
                        $member_id = $memberresult;
                        $_SESSION['memberid'] = $member_id;
                        $code = 0;
                        $numSent = 0;
                        $to = array($email => $firstName . " " . $lastName);
                        $from = array('jaycarl.hawkins@gmail.com' => 'Jay Hawkins');
                        $templateresult = json_decode(file_get_contents(API_HOST."/api/email_templates?filter=title,eq,Authorize Account"));
                        $subject = $templateresult->email_templates->records[0][6];
                        $body = "Hello " . $firstName . ",<br /><br />";
                        $body .= $templateresult->email_templates->records[0][2];
                        $body .= "<a href='".HTTP_HOST."/verifyaccount/".$user_id."/".$code."'>Click HERE to Activate!</a>";
                        echo count($templateresult);
                        die();
                        if (count($templateresult) > 0) {
                            $numSent = sendmail($to, $subject, $body, $from);
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
                    }
                }

                return true; // Return true to the router so it knows everything was created!
            } else {
              return "There was an issue with creating the information. Please contact NEC!";  // There was an issue, let the router know something failed!
            }
      } catch (Exception $e) { // The authorization query failed verification
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: text/plain; charset=utf8');
            return $e->getMessage();
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
