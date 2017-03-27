<?php

function is_authorized() {
  if (isset($_SESSION['userid'])) {
    try {
        $db = Flight::db();
        $stmt = $db->prepare("SELECT id FROM users where id = '" . $_SESSION['userid'] . "'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
          return true;
        } else {
          return false;
        }
    } catch (Exception $e) { // The authorization query failed verification
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: text/plain; charset=utf8');
        echo $e->getMessage();
        exit();
    }
  } else {
    return false;
  }
}
