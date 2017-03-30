<?php

class User
{
    public function __construct() {

    }
/*
    public function login($username,$password) {
        try {
              $db = Flight::db();
              $stmt = $db->prepare("SELECT id, password FROM users where username = '" . $username . "' and status = 'Active'");
              $stmt->execute();
              if ($stmt->rowCount() > 0) {
                  $row = $stmt->fetch(PDO::FETCH_ASSOC);
                  if (password_verify($password, $row['password'])) {
                    $_SESSION['userid'] = $row['id'];
                    unset($_SESSION['invalidPassword']);
                    return true;
                  } else {
                    unset($_SESSION['userid']);
                    $_SESSION['invalidPassword'] = 'Password is invalid!';
                    return false;
                  }
              } else {
                return false;
              }
        } catch (PDOException $e) { // The authorization query failed verification
              header('HTTP/1.1 401 Unauthorized');
              header('Content-Type: text/plain; charset=utf8');
              echo $e->getMessage();
              exit();
        }
    }
*/
    public function loginapi($username,$password) {
        try {
              $result = json_decode(file_get_contents(API_HOST.'/api/users?filter=username,eq,' . $username));
              //print_r ($result->users->records);
              //die();
              if (count($result) > 0) {
                  if (password_verify($password, $result->users->records[0][2])) {
                    $_SESSION['userid'] = $result->users->records[0][2];
                    unset($_SESSION['invalidPassword']);
                    return true;
                  } else {
                    unset($_SESSION['userid']);
                    $_SESSION['invalidPassword'] = 'Password is invalid!';
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
/*
    public function register($password1,$firstName,$lastName,$email,$businessName,$businessType) {
      try {
            $db = Flight::db();
            $stmt = $db->prepare("INSERT INTO users (username, password, status, createdAt, updatedAt)
                VALUES
                (:uname, :pass, :status, :createDate, :updateDate)");
            if ($stmt->execute(array(
                "uname" => $email,
                "pass" => password_hash($password1, PASSWORD_BCRYPT),
                "status" => "Inactive",
                "createDate" => date('Y-m-d H:i:s'),
                "updateDate" => date('Y-m-d H:i:s')
                ))) {
                      $user_id = $db->lastInsertId();
                      $_SESSION['userid'] = $user_id;
                      unset($_SESSION['invalidPassword']);
                      $stmt = $db->prepare("INSERT INTO members (firstName, lastName, userID, entityID, createdAt, updatedAt)
                          VALUES
                          (:firstname, :lastname, :userid, :entityid, :createDate, :updateDate)");
                          if ($stmt->execute(array(
                              "firstname" => $firstName,
                              "lastname" => $lastName,
                              "userid" => $user_id,
                              "entityid" => 1,
                              "createDate" => date('Y-m-d H:i:s'),
                              "updateDate" => date('Y-m-d H:i:s')
                              ))) {
                                      $to = array($email => $firstName . " " . $lastName);
                                      $from = array('jaycarl.hawkins@gmail.com' => 'Jay Hawkins');
                                      $templateresult = json_decode(file_get_contents(HTTP_HOST."/api/email_templates?filter=title,eq,'Authorize Account'"));
                                      if (count($templateresult) > 0) {
                                          $numSent = sendmail($to, $subject, $body, $from);
                                      }
                          }
                      return true;
            } else {
              return false;
            }
      } catch (PDOException $e) { // The authorization query failed verification
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: text/plain; charset=utf8');
            echo $e->getMessage();
            exit();
      }
    }
*/
    public function registerapi($password,$firstName,$lastName,$title,$address1,$address2,$city,$state,$zip,$phone,$fax,$email,$entityName,$entityTypeID) {
      try {
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
                            "entityID" => 1,
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
                if ($memberresult > 0) {
                    $member_id = $memberresult;
                    $code = 0;
                    $to = array($email => $firstName . " " . $lastName);
                    $from = array('jaycarl.hawkins@gmail.com' => 'Jay Hawkins');
                    $templateresult = json_decode(file_get_contents(API_HOST."/api/email_templates?filter=title,eq,Authorize Account"));
                    $subject = $templateresult->email_templates->records[0][6];
                    $body = "Hello " . $firstName . ",<br /><br />";
                    $body .= $templateresult->email_templates->records[0][2];
                    $body .= "<a href='".HTTP_HOST."/verifyaccount/".$user_id."/".$code."'>Click HERE to Activate!</a>";
                    if (count($templateresult) > 0) {
                        $numSent = sendmail($to, $subject, $body, $from);
                    }
                    // Now that you have a member, create the entity record
                    $entityurl = API_HOST.'/api/entities';
                    $entitydata = array(
                                "name" => $entityName,
                                "entityTypeID" => $entityTypeID,
                                "assignedMemberID" => $member_id,
                                "status" => "Active",
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
                    if ($entityresult > 0) {
                        $updatememberurl = API_HOST.'/api/members/'.$member_id;
                        $updatememberdata = array(
                                    "entityID" => $entityresult
                        );
                        // use key 'http' even if you send the request to https://...
                        $updatememberoptions = array(
                            'http' => array(
                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method'  => 'PUT',
                                'content' => http_build_query($updatememberdata)
                            )
                        );
                        $updatemembercontext  = stream_context_create($updatememberoptions);
                        $updatememberresult = file_get_contents($updatememberurl, false, $updatemembercontext);
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
