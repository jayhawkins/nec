<?php

function sendmail($to, $subject, $body, $from, $document='') {

  // Create the Transport
  $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'tls')
    ->setUsername('jaycarl.hawkins@gmail.com')
    ->setPassword('turnsomepages1978');

  // Create the Mailer using your created Transport
  $mailer = Swift_Mailer::newInstance($transport);

  if (empty($subject)) {
      $subject = 'Notification from Nationwide Equipment Control';
  }

  if (empty($fromemail)) {
      $fromemail = array('jaycarl.hawkins@gmail.com' => 'Jay Hawkins');
  }
  $setFrom = array($fromemail => $fromname);

  // Send an account activation to the member
  // Create the message
  $message = Swift_Message::newInstance()

    // Give the message a subject
    ->setSubject($subject)

    // Set the From address with an associative array
    ->setFrom($setFrom)

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
    foreach ($to as $address => $name)
    {
      if (is_int($address)) {
        $message->setTo($name);
      } else {
        $message->setTo(array($address => $name));
      }

      $numSent += $mailer->send($message, $failedRecipients);
    }

    return $numSent;

}
