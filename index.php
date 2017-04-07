<?php

use flight\Engine;

require 'vendor/autoload.php';
require 'config/setup.php';

$app = new Engine();

/**********************/
//$request = $app->request();
//var_dump($request->data);
//echo "<br /><br />Session Data: ";
//print_r($_SESSION);
/**********************/

/*****************************************************************************/
// Test routes
$app->route('GET|POST /gmail', function() {

  $to = array('jhawkins@dynamasys.com' => 'Jay Hawkins');
  $from = array('jaycarl.hawkins@gmail.com' => 'Jay Hawkins');
  $subject = "NEC Test email";
  $body = "Body of NEC test email";

  if (sendmail($to, $subject, $body, $from)) {
    echo "Sent";
  } else {
    echo "Failed";
  }

});

$app->route('GET|POST /json-data', function() {
  if (is_authorized()) {
      $db = Flight::db();
      $stmt = $db->prepare('SELECT abbreviation, name FROM states ORDER BY abbreviation');
      $stmt->execute();

      header('Content-type: application/json');
      echo json_encode($stmt->fetchAll());

      $db = null;
  } else {
      header('HTTP/1.1 401 Unauthorized');
      header('Content-Type: text/plain; charset=utf8');
      echo "Failed";
      exit();
  }

});
/*****************************************************************************/

//echo $_SESSION['userid'];

$app->route('GET /login', function() {
  $invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:'';
  Flight::render('login', array('invalidPassword'=> $invalidPassword));
});

$app->route('GET /register', function() {
  Flight::render('register');
});

$app->route('GET /logout', function() {
  unset($_SESSION['userid']);
  Flight::redirect('/login');
});

$app->route('GET /accountverified', function() {
  Flight::render('accountverified');
});

$app->route('POST /login', function() {
    $username = Flight::request()->data['username'];
    $password = Flight::request()->data['password'];
    $user = Flight::user();
    $return = $user->loginapi($username,$password);
    if ($return) {
      Flight::redirect('dashboard');
    } else {
      $invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:'';
      Flight::render('login', array('invalidPassword'=> $invalidPassword));
    }
});

$app->route('POST /register', function() {
    $password = Flight::request()->data['password'];
    $firstName = Flight::request()->data['firstName'];
    $lastName = Flight::request()->data['lastName'];
    $title = Flight::request()->data['title'];
    $address1 = Flight::request()->data['address1'];
    $address2 = Flight::request()->data['address2'];
    $city = Flight::request()->data['city'];
    $state = Flight::request()->data['state'];
    $zip = Flight::request()->data['zip'];
    $phone = Flight::request()->data['phone'];
    $fax = Flight::request()->data['fax'];
    $email = Flight::request()->data['email'];
    $entityName = Flight::request()->data['entityName'];
    $entityTypeID = Flight::request()->data['entityTypeID'];
    $user = Flight::user();
    $return = $user->registerapi($password,$firstName,$lastName,$title,$address1,$address2,$city,$state,$zip,$phone,$fax,$email,$entityName,$entityTypeID);
    if ($return == "success") {
      Flight::render('registrationsuccessful');
    } else {
      echo $return;
      //$invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:'';
      //Flight::render('login', array('invalidPassword'=> $invalidPassword));
    }
});

$app->route('GET /verifyaccount/@id/@code', function($id,$code) {
    $user = Flight::user();
    $accountVerified = $user->verifyaccount($id,$code);
    if ($accountVerified) {
        Flight::redirect('/accountverified');
    } else {
        echo "Account Not Verified!";
        die();
    }
});

$app->route('POST /entities', function() {
    $locationid = 0;
    $locationresult = json_decode(file_get_contents(API_HOST.'/api/locations?filter=entityID,eq,' . $_SESSION['entityid']));
    for ($l=0; $l < count($locationresult->locations->records); $l++) {
        if ($locationresult->locations->records[$l][2] == 1) { // Get the main location information from the locations table locationTypeID = 1
            $locationid = $locationresult->locations->records[$l][0];
        }
    }

    $contactid = 0;
    $contactresult = json_decode(file_get_contents(API_HOST.'/api/contacts?filter=entityID,eq,' . $_SESSION['entityid']));
    for ($c=0; $c < count($contactresult->contacts->records); $c++) {
        if ($contactresult->contacts->records[$c][2] == 1) { // Get the main location information from the locations table locationTypeID = 1
            $contactid = $contactresult->contacts->records[$c][0];
        }
    }

    $password = Flight::request()->data['password'];
    $firstName = Flight::request()->data['firstName'];
    $lastName = Flight::request()->data['lastName'];
    $title = Flight::request()->data['title'];
    $address1 = Flight::request()->data['address1'];
    $address2 = Flight::request()->data['address2'];
    $city = Flight::request()->data['city'];
    $state = Flight::request()->data['state'];
    $zip = Flight::request()->data['zip'];
    $phone = Flight::request()->data['phone'];
    $fax = Flight::request()->data['fax'];
    $email = Flight::request()->data['email'];
    $entityName = Flight::request()->data['entityName'];
    $entityTypeID = Flight::request()->data['entityTypeID'];
    $entity = Flight::entity();
    $location = Flight::location();
    $contact = Flight::contact();
    $returnentity = $entity->put($entityName);
    $returnlocation = $location->put($locationid,$address1,$address2,$city,$state,$zip);
    $returncontact = $contact->put($contactid,$firstName,$lastName,$title,$phone,$fax,$email);
    if ($returnentity && $returnlocation && $returncontact) {
      Flight::redirect('/');
    } else {
      $invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:'';
      Flight::render('login', array('invalidPassword'=> $invalidPassword));
    }
});

/*****************************************************************************/
// Admin routes
/*****************************************************************************/

$app->route('/', function() {
  if (is_authorized()) {
    Flight::render('index', array());
  } else {
    Flight::render('login', array());
  }
});

$app->route('/dashboard', function() {
    if (is_authorized()) {
      Flight::render('index', array());
    } else {
      Flight::render('login', array());
    }
});


// Start the framework
$app->start();
