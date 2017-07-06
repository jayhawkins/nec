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

$app->route('GET|POST /testroute', function() {
    $carrierneedid = Flight::request()->data->id;
    $carrierneed = Flight::carrierneed();
    $result = $carrierneed->load(API_HOST,$carrierneedid);
    $notificationresult = $carrierneed->getContactEmails();
    var_dump($notificationresult);
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
      Flight::render('register', array('errorMessage'=> $return));
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

    // url encode the address
    $address = urlencode(Flight::request()->data['address1'].", ".Flight::request()->data['city'].", ".Flight::request()->data['state'].", ".Flight::request()->data['zip']);

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
    } else {
      $lati = 0.00;
      $longi = 0.00;
      $formatted_address = $resp['results'][0]['formatted_address'];
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
    $latitude = $lati;
    $longitude = $longi;
    $phone = Flight::request()->data['phone'];
    $fax = Flight::request()->data['fax'];
    $email = Flight::request()->data['email'];
    $entityName = Flight::request()->data['entityName'];
    $entityTypeID = Flight::request()->data['entityTypeID'];
    $entity = Flight::entity();
    $location = Flight::location();
    $contact = Flight::contact();
    $returnentity = $entity->put($entityName);
    $returnlocation = $location->put($locationid,$address1,$address2,$city,$state,$zip,$latitude,$longitude);
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

/*****************************************************************************/
// Locations Processes
/*****************************************************************************/
$app->route('POST /deletelocationcontacts', function() {
    $locationid = Flight::request()->data->location_id;
    $locationcontact = Flight::locationcontact();
    $recorddeleted = $locationcontact->delete($locationid);
    if ($recorddeleted) {
        echo "success";
    } else {
        echo $recorddeleted;
    }
});

$app->route('POST /getlocation', function() {
    $locationid = Flight::request()->data->id;
    $location = Flight::location();
    $result = $location->get($locationid);
    if ($result) {
        echo $result;
    } else {
        echo "Could not get location selected";
    }
});

$app->route('POST /getlocationbycitystatezip', function() {
    $city = Flight::request()->data->city;
    $state = Flight::request()->data->state;
    $zip = Flight::request()->data->zip;
    $entityID = Flight::request()->data->entityID;
    $locationType = Flight::request()->data->locationType;
    $location = Flight::location();
    $result = $location->getLocationByCityStateZip($city,$state,$zip,$entityID);
    if ($result == 0) { // Address does not exist as array count returned is 0 - So... Add the location as a new location to the database to be used moving forward
        // Create the address in the locations table
        // url encode the address
        $address = urlencode($city.", ".$state.", ".$zip);

        // google map geocode api url
        $url = "http://maps.google.com/maps/api/geocode/json?address={$address}";

        // get the json response
        $resp_json = file_get_contents($url);

        // decode the json
        $resp = json_decode($resp_json, true);

        // response status will be 'OK', if able to geocode given address
        if($resp['status']=='OK') {

            if ($locationType == "Origination") {
                $locationTypeID = 2;
            } else {
                $locationTypeID = 3;
            }

            // get the important data
            $lati = $resp['results'][0]['geometry']['location']['lat'];
            $longi = $resp['results'][0]['geometry']['location']['lng'];
            $formatted_address = $resp['results'][0]['formatted_address'];

            $result = $location->post($entityID,$locationTypeID,$city,"","",$city,$state,$zip,$lati,$longi);

            echo $result;

        } else {
            echo $resp['status'];
        }
    } else {
        if ( $result > 0 ) {
            echo "success";
        } else {
            echo $result;
        }
    }
});

/*****************************************************************************/
// Contacts Processes
/*****************************************************************************/
$app->route('POST /getcontactsbycarrier', function() {
    $entityid = Flight::request()->data->id;
    $contact = Flight::contact();
    $result = json_encode($contact->getContactsByEntity($entityid));
    if ($result) {
        echo $result;
    } else {
        echo "There was an error retrieving Contacts!";
    }
});

/*****************************************************************************/
// Carrier Needs Processes
/*****************************************************************************/
$app->route('POST /carrierneedsnotification', function() {
    $carrierneedid = Flight::request()->data->id;
    $carrierneed = Flight::carrierneed();
    $notificationresult = $carrierneed->sendNotification(API_HOST,$carrierneedid);
    if ($notificationresult) {
        print_r($notificationresult);
        //echo "success";
    } else {
        print_r($notificationresult);
    }
});

/*****************************************************************************/
// Carrier Needs Processes
/*****************************************************************************/
$app->route('POST /customerneedsnotification', function() {
    $customerneedid = Flight::request()->data->id;
    $customerneed = Flight::customerneed();
    $notificationresult = $customerneed->sendNotification(API_HOST,$customerneedid);
    if ($notificationresult) {
        print_r($notificationresult);
        //echo "success";
    } else {
        print_r($notificationresult);
    }
});

$app->route('POST /customerneedscommitnotification', function() {
    $customerneedid = Flight::request()->data->id;
    $customerneedcommit = Flight::customerneedcommit();
    $notificationresult = $customerneedcommit->sendRepNotification(API_HOST,$customerneedid);
    if ($notificationresult) {
        print_r($notificationresult);
        //echo "success";
    } else {
        print_r($notificationresult);
    }
});

// Start the framework
$app->start();
