<?php

class User
{
    public function __construct() {

    }

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
                                    $member_id = $db->lastInsertId();
                                    $to = array($email => $firstName . ' ' . $lastName);
                                    $from = array('jaycarl.hawkins@gmail.com' => 'Jay Hawkins');

                                    $numSent = sendmail($to, $subject, $body, $from);


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
}

//$user = new User();
