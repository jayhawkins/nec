<?php

function sendmail($to, $subject, $body, $from, $document='') {

  // Create the Transport
  $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 587, 'tls')
    ->setUsername('necmailer@gmail.com')
    ->setPassword('smzncalqysuakryl'); // This password was setup in the Gmail account Security Settings for Apps

  // Create the Mailer using your created Transport
  $mailer = Swift_Mailer::newInstance($transport);

  if (empty($subject)) {
      $subject = 'Notification from Nationwide Equipment Control';
  }

  if (empty($from) || count($from) <= 0) {
      $from = array('necmailer@gmail.com' => 'Nationwide Equipment Control');
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
            if ($mailer->send($message, $failedRecipients)) {
              $numSent++;
            }
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

function php_crud_api_transform(&$tables) {
	$get_objects = function (&$tables,$table_name,$where_index=false,$match_value=false) use (&$get_objects) {
		$objects = array();
		if (isset($tables[$table_name]['records'])) {
			foreach ($tables[$table_name]['records'] as $record) {
				if ($where_index===false || $record[$where_index]==$match_value) {
					$object = array();
					foreach ($tables[$table_name]['columns'] as $index=>$column) {
						$object[$column] = $record[$index];
						foreach ($tables as $relation=>$reltable) {
							if (isset($reltable['relations'])) {
								foreach ($reltable['relations'] as $key=>$target) {
									if ($target == "$table_name.$column") {
										$column_indices = array_flip($reltable['columns']);
										$object[$relation] = $get_objects($tables,$relation,$column_indices[$key],$record[$index]);
									}
								}
							}
						}
					}
					$objects[] = $object;
				}
			}
		}
		return $objects;
	};
	$tree = array();
	foreach ($tables as $name=>$table) {
		if (!isset($table['relations'])) {
			$tree[$name] = $get_objects($tables,$name);
			if (isset($table['results'])) {
				$tree['_results'] = $table['results'];
			}
		}
	}
	return $tree;
}
