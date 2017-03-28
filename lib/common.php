<?php

function sendmail($to, $subject, $body, $from, $document='') {

  // Create the Transport
  $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 587, 'tls')
    ->setUsername('jaycarl.hawkins@gmail.com')
    ->setPassword('turnsomepages1978');

  // Create the Mailer using your created Transport
  $mailer = Swift_Mailer::newInstance($transport);

  if (empty($subject)) {
      $subject = 'Notification from Nationwide Equipment Control';
  }

  if (empty($from) || count($from) <= 0) {
      $from = array('jaycarl.hawkins@gmail.com' => 'Jay Hawkins');
  }

  // Send an account activation to the member
  // Create the message
  $message = Swift_Message::newInstance()

    // Give the message a subject
    ->setSubject($subject)

    // Set the From address with an associative array
    ->setFrom($from)

    // Give it a body
    ->setBody($body)

    // And optionally an alternative body
    ->addPart($body, 'text/html');

    if (!empty($document)) {
        // Optionally add any attachments
        $message->attach(Swift_Attachment::fromPath($document));
    }

    /********************************************************************/
    // Send the message
    /********************************************************************/
    $failedRecipients = array();
    $numSent = 0;
    foreach ($to as $address => $name) {
        if (is_int($address)) {
          $message->setTo($name);
        } else {
          $message->setTo(array($address => $name));
        }

        try {
            $numSent += $mailer->send($message, $failedRecipients);
        } catch (Exception $e) {
            echo "<p>".$e."</p>";
            die();
        }
    }

    return $numSent;

}

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
