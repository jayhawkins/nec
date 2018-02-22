<?php

use flight\Engine;
use setasign\Fpdi\Fpdi;

require 'vendor/autoload.php';
require 'config/setup.php';
require "lib/php_crud_api_transform.php";

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

  $to = array('dsmith@dubtel.com' => 'Dennis Smith');
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
    $carrierneed = Flight::carrierNeeds();
    $result = $carrierneed->load(API_HOST,$carrierneedid);
    $notificationresult = $carrierneed->getContactEmails();
    var_dump($notificationresult);
});
/*****************************************************************************/

//echo $_SESSION['userid'];

$app->route('GET /login', function() {
    if(isset($_SESSION['userid'])){
        Flight::redirect('dashboard');
    }
    else{
        $invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:'';
        Flight::render('login', array('invalidPassword'=> $invalidPassword));
    }
});

$app->route('GET /register', function() {
  Flight::render('register');
});

$app->route('GET /forgot', function() {
  Flight::render('forgot');
});

$app->route('GET /logout', function() {
  unset($_SESSION['userid']);
  unset($_SESSION['existinguserid']);
  Flight::redirect('/login');
});

$app->route('GET /accountverified', function() {
  Flight::render('accountverified');
});

$app->route('POST /login', function() {
    $username = Flight::request()->data['username'];
    $password = Flight::request()->data['password'];
    $user = Flight::users();
    $db = Flight::db();
    //$return = $user->loginapi($username,$password);
    $return = $user->loginapi2($db,$username,$password);
    if ($return) {
      Flight::redirect('dashboard');
    } else {
      $invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:'';
      Flight::render('login', array('invalidPassword'=> $invalidPassword));
    }
});

$app->route('POST /forgot', function() {
    $username = Flight::request()->data['username'];
    $user = Flight::users();
    $return = $user->forgotpasswordapi($username);
    if ($return) {
      Flight::render('checkyouremail');
      //Flight::redirect('login');
    } else {
      $invalidUsername = (isset($_SESSION['invalidUsername'])) ? $_SESSION['invalidUsername']:''; // Just use the invalidPassword session var since it's just an error
      Flight::render('forgot', array('invalidUsername'=> $invalidUsername));
    }
});

$app->route('GET /resetpassword/@id/@code', function($id, $code) {
    $user = Flight::users();
    $password = $user->getPasswordById($id);
    $password = str_replace("/", "-", $password);
    $password = str_replace("?", "-", $password);
    if ($password == $code) {
        Flight::render('resetpassword', array('id'=>$id));
    } else {
        //Flight::render('forgot', array('invalidUsername'=> $invalidUsername));
        $_SESSION['invalidUsername'] = "Your user account may not be Activated. Please contact Nationwide Equipment Control";
        $invalidUsername = (isset($_SESSION['invalidUsername'])) ? $_SESSION['invalidUsername']:''; // Just use the invalidPassword session var since it's just an error
        Flight::redirect('/login');
    }

});

$app->route('POST /resetpassword', function() {
    $username = Flight::request()->data['username'];
    $password = Flight::request()->data['password'];
    $user = Flight::users();
    $return = $user->resetpasswordapi($username,$password);
    if ($return) {
      Flight::redirect('login');
    } else {
      $invalidUsername = (isset($_SESSION['invalidUsername'])) ? $_SESSION['invalidUsername']:''; // Just use the invalidPassword session var since it's just an error
      Flight::render('resetpassword', array('invalidUsername'=> $invalidUsername));
    }
});

$app->route('GET /setpassword/@username', function($username) {
    $user = Flight::users();
    $return = $user->getUserValidateById($username);
    if ($return == "success") {
        Flight::render('setpassword', array("username"=>$username));
    } else {
        Flight::render('invalidrequest');
    }
});

$app->route('POST /setpasswordvalidate', function() {
    $username = Flight::request()->data['username'];
    $password = Flight::request()->data['password'];
    $user = Flight::users();
    $return = $user->setpasswordvalidateapi($username,$password);
    if ($return) {
      Flight::redirect('login');
    } else {
      $invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:''; // Just use the invalidPassword session var since it's just an error
      Flight::render('setpassword', array('invalidPassword'=> $invalidPassword));
    }
});

$app->route('POST /proxylogin', function() {
    $username = Flight::request()->data['proxyid'];
    $user = Flight::users();
    $db = Flight::db();
    $return = $user->proxylogin($db,$username);
    if ($return) {
      Flight::redirect('dashboard');
    } else {
      echo $return;
      //$invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:'';
      //Flight::render('login', array('invalidPassword'=> $invalidPassword));
    }
});

$app->route('POST /proxylogout', function() {
    $existinguserid = Flight::request()->data['identifier'];
    $user = Flight::users();
    $db = Flight::db();
    $return = $user->proxylogout($db,$existinguserid);
    if ($return) {
        Flight::redirect('dashboard');
    } else {
        echo $return;
    }
});

//-----------------------------------------------------------------------------------------
// Used for migration only
$app->route('GET /setmigratedpassword/@username/@password', function($username,$password) {
    $user = Flight::users();
    $return = $user->getMigratedUserValidateById($username,$password);
    if ($return == "success") {
        Flight::render('setmigratedpassword', array("username"=>$username));
    } else {
        Flight::render('invalidrequest');
    }
});

$app->route('POST /setmigratedpasswordvalidate', function() {
    $username = Flight::request()->data['username'];
    $password = Flight::request()->data['password'];
    $user = Flight::users();
    $return = $user->setmigratedpasswordvalidateapi($username,$password);
    if ($return) {
      Flight::redirect('login');
    } else {
      $invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:''; // Just use the invalidPassword session var since it's just an error
      Flight::render('setpassword', array('invalidPassword'=> $invalidPassword));
    }
});
//-----------------------------------------------------------------------------------------

$app->route('POST /checkforuniqueid', function() {
    $uniqueID = Flight::request()->data['uniqueID'];
    $user = Flight::users();
    $return = $user->checkforuniqueid($uniqueID);

    echo $return;
});

$app->route('POST /checkforusername', function() {
    $username = Flight::request()->data['username'];
    $user = Flight::users();
    $return = $user->checkforusername($username);

    echo $return;
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
    $user = Flight::users();
    $return = $user->registerapi($password,$firstName,$lastName,$title,$address1,$address2,$city,$state,$zip,$phone,$fax,$email,$entityName,$entityTypeID);
    if ($return == "success") {
      Flight::render('registrationsuccessful');
    } else {
      Flight::render('register', array('errorMessage'=> $return));
    }
});

$app->route('GET /verifyaccount/@id/@code', function($id,$code) {
    $user = Flight::users();
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
    $locationresult = json_decode(file_get_contents(API_HOST_URL . '/locations?filter=entityID,eq,' . $_SESSION['entityid']));
    for ($l=0; $l < count($locationresult->locations->records); $l++) {
        if ($locationresult->locations->records[$l][2] == 1) { // Get the main location information from the locations table locationTypeID = 1
            $locationid = $locationresult->locations->records[$l][0];
        }
    }

    $contactid = 0;
    $contactresult = json_decode(file_get_contents(API_HOST_URL . '/contacts?filter=entityID,eq,' . $_SESSION['entityid']));
    for ($c=0; $c < count($contactresult->contacts->records); $c++) {
        if ($contactresult->contacts->records[$c][2] == 1) { // Get the main location information from the locations table locationTypeID = 1
            $contactid = $contactresult->contacts->records[$c][0];
        }
    }

    // url encode the address
    $address = urlencode(Flight::request()->data['address1'].", ".Flight::request()->data['city'].", ".Flight::request()->data['state'].", ".Flight::request()->data['zip']);

    // google map geocode api url
    $url = "https://maps.google.com/maps/api/geocode/json?key=".GOOGLE_MAPS_API."&address={$address}";

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
    $phoneExt = Flight::request()->data['phoneExt'];
    $fax = Flight::request()->data['fax'];
    $email = Flight::request()->data['email'];
    $entityName = Flight::request()->data['entityName'];
    $entityTypeID = Flight::request()->data['entityTypeID'];
    $configurationSettings = Flight::request()->data['configuration_settings'];
    $entity = Flight::entities();
    $location = Flight::locations();
    $contact = Flight::contacts();
    $returnentity = $entity->put($entityName,$configurationSettings);
    $returnlocation = $location->put($locationid,$address1,$address2,$city,$state,$zip,$latitude,$longitude);
    $returncontact = $contact->put($contactid,$firstName,$lastName,$title,$phone,$phoneExt,$fax,$email);
    if ($returnentity && $returnlocation && $returncontact) {
      Flight::redirect('/');
    } else {
      $invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:'';
      Flight::render('login', array('invalidPassword'=> $invalidPassword));
    }
});

$app->route('PUT|POST /addentity', function() {

    // url encode the address
    $address = urlencode(Flight::request()->data['address1'].", ".Flight::request()->data['city'].", ".Flight::request()->data['state'].", ".Flight::request()->data['zip']);

    // google map geocode api url
    $url = "https://maps.google.com/maps/api/geocode/json?key=".GOOGLE_MAPS_API."&address={$address}";

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

    //$password = Flight::request()->data['password'];
    $password = Flight::request()->data['zip'];
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
    $phoneExt = Flight::request()->data['phoneExt'];
    $fax = Flight::request()->data['fax'];
    $email = Flight::request()->data['email'];
    $entityName = Flight::request()->data['entityName'];
    $entityTypeID = Flight::request()->data['entityTypeID'];
    $contactID = Flight::request()->data['contactID'];
    $negotiatedRate = Flight::request()->data['negotiatedRate'];
    $rateType = Flight::request()->data['rateType'];
    $towAwayRateMin = Flight::request()->data['towAwayRateMin'];
    $towAwayRateMax = Flight::request()->data['towAwayRateMax'];
    $towAwayRateType = Flight::request()->data['towAwayRateType'];
    $loadOutRateMin = Flight::request()->data['loadOutRateMin'];
    $loadOutRateMax = Flight::request()->data['loadOutRateMax'];
    $loadOutRateType = Flight::request()->data['loadOutRateType'];
    $configurationSettings = Flight::request()->data['configuration_settings'];
    $entity = Flight::entities();
    $returnentity = $entity->post($email,$password,$entityTypeID,$entityName,$contactID,$rateType,$negotiatedRate,$towAwayRateMin,$towAwayRateMax,$towAwayRateType,$loadOutRateMin,$loadOutRateMax,$loadOutRateType,$configurationSettings,$firstName,$lastName,$address1,$address2,$city,$state,$zip,$latitude,$longitude,$title,$phone,$phoneExt,$fax);
    if ($returnentity) {
      return true;
    } else {
      return $returnentity;
    }
});

$app->route('PUT|POST /usermaintenance', function() {
    $userID = Flight::request()->data['userID'];
    $member_id = Flight::request()->data['member_id'];
    $type = Flight::request()->data['type'];
    $entityID = Flight::request()->data['entityID'];
    $firstName = Flight::request()->data['firstName'];
    $lastName = Flight::request()->data['lastName'];
    $username = Flight::request()->data['username'];
    $userTypeID = Flight::request()->data['userTypeID'];
    $password = Flight::request()->data['password'];
    $uniqueID = Flight::request()->data['uniqueID'];
    $textNumber = Flight::request()->data['textNumber'];
    $user = Flight::users();
    $return = $user->maintenanceapi($type,$userID,$member_id,$entityID,$firstName,$lastName,$username,$password,$userTypeID,$uniqueID,$textNumber);
    if ($return == "success") {
      echo $return;
    } else {
      echo $return;
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
    $locationcontact = Flight::locationsContacts();
    $recorddeleted = $locationcontact->deleteById($locationid);
    if ($recorddeleted) {
        echo "success";
    } else {
        echo $recorddeleted;
    }
});

$app->route('POST /getlocation', function() {
    $locationid = Flight::request()->data->id;
    $location = Flight::locations();
    $result = $location->get($locationid);
    if ($result) {
        echo $result;
    } else {
        echo "Could not get location selected";
    }
});

$app->route('POST /getlocationbycitystatezip', function() {
    $address1 = Flight::request()->data->address1;
    $address2 = Flight::request()->data->address2;
    $city = Flight::request()->data->city;
    $state = Flight::request()->data->state;
    $zip = Flight::request()->data->zip;
    $entityID = Flight::request()->data->entityID;
    $locationType = Flight::request()->data->locationType;
    $location = Flight::locations();
    //$result = $location->getLocationByCityStateZip($city,$state,$zip,$entityID);
    $result = $location->getLocationByAddressCityStateZip($address1,$city,$state,$zip,$entityID); // Use a more specific address

    // In lieu of creating the location in the locations table and returning, just return success and let everything keep going
    echo "success";

// Turn this off for now. I don't believe we need it anymore since we're not autoloading the dropdowns for Needs or Availability setup
// We track city/state/zip in each Need or Availability record and geocode those. May no longer need the locations table except for satellite locations for Carrier/Customer
// Jay Hawkins - 10/1/2017
/*
    if ($result == 0) { // Address does not exist as array count returned is 0 - So... Add the location as a new location to the database to be used moving forward
        // Create the address in the locations table
        // url encode the address
        $address = urlencode($address1.", ".$city.", ".$state.", ".$zip);

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

            $result = $location->post($entityID,$locationTypeID,$city,$address1,$address2,$city,$state,$zip,$lati,$longi); // If being added to db use city as location name

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
*/

});


/*****************************************************************************/
// Contacts Processes
/*****************************************************************************/
$app->route('POST /getcontactsbycustomer', function() {
    $entityid = Flight::request()->data->id;
    $contact = Flight::contacts();
    $result = json_encode($contact->getContactsByEntity($entityid));
    if ($result) {
        echo $result;
    } else {
        echo "There was an error retrieving Contacts!";
    }
});

/*****************************************************************************/
// Contacts Processes
/*****************************************************************************/
$app->route('POST /getcontactsbycarrier', function() {
    $entityid = Flight::request()->data->id;
    $contact = Flight::contacts();
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
    $carrierneed = Flight::carrierNeeds();
    $notificationresult = $carrierneed->sendNotification(API_HOST,$carrierneedid);
    if ($notificationresult) {
        print_r($notificationresult);
        //echo "success";
    } else {
        print_r($notificationresult);
    }
});

/*****************************************************************************/
// Customer Needs Processes
/*****************************************************************************/
$app->route('POST /customerneedsnotification', function() {
    $customerneedid = Flight::request()->data->id;
    $customerneed = Flight::customerNeeds();
    $notificationresult = $customerneed->sendNotification(API_HOST,$customerneedid);
    if ($notificationresult) {
        print_r($notificationresult);
        //echo "success";
    } else {
        print_r($notificationresult);
    }
});

$app->route('POST /commitacceptednotification', function() {
    $customerneedcommitid = Flight::request()->data->id;
    $customerneedcommit = Flight::customerNeedsCommit();
    $notificationresult = $customerneedcommit->sendAcceptNotification(API_HOST,$customerneedcommitid);
    if ($notificationresult) {
        print_r($notificationresult);
        //echo "success";
    } else {
        print_r($notificationresult);
    }
});

$app->route('POST /createcustomerneedsfromexisting', function() {
    $id = Flight::request()->data->id;
    $rootCustomerNeedsID = Flight::request()->data->rootCustomerNeedsID;
    $carrierID = Flight::request()->data->carrierID;
    $qty = Flight::request()->data->qty;
    $originationAddress1 = Flight::request()->data->originationAddress1;
    $originationCity = Flight::request()->data->originationCity;
    $originationState = Flight::request()->data->originationState;
    $originationZip = Flight::request()->data->originationZip;
    $destinationAddress1 = Flight::request()->data->destinationAddress1;
    $destinationCity = Flight::request()->data->destinationCity;
    $destinationState = Flight::request()->data->destinationState;
    $destinationZip = Flight::request()->data->destinationZip;
    $originationLat = Flight::request()->data->originationLat;
    $originationLng = Flight::request()->data->originationLng;
    $destinationLat = Flight::request()->data->destinationLat;
    $destinationLng = Flight::request()->data->destinationLng;
    $distance = Flight::request()->data->distance;
    $transportationMode = Flight::request()->data->transportationMode;
    $transportation_mode = Flight::request()->data->transportation_mode;
    $transportation_type = Flight::request()->data->transportation_type;
    $pickupDate = Flight::request()->data->pickupDate;
    $deliveryDate = Flight::request()->data->deliveryDate;
    $rate = Flight::request()->data->rate;
    $rateType = Flight::request()->data->rateType;
    $customerneed = Flight::customerNeeds();
    $result = $customerneed->createFromExisting(API_HOST,$id,$rootCustomerNeedsID,$carrierID,$qty,$originationAddress1,$originationCity,$originationState,$originationZip,$destinationAddress1,$destinationCity,$destinationState,$destinationZip,$originationLat,$originationLng,$destinationLat,$destinationLng,$distance,$transportationMode,$transportation_mode,$transportation_type,$pickupDate,$deliveryDate,$rate,$rateType,GOOGLE_MAPS_API);
    if ($result == "success") {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

/* No longer used
$app->route('POST /committoneed', function() {
    $id = Flight::request()->data->id;
    $rootCustomerNeedsID = Flight::request()->data->rootCustomerNeedsID;
    $carrierID = Flight::request()->data->carrierID;
    $qty = Flight::request()->data->qty;
    $originationAddress1 = Flight::request()->data->originationAddress1;
    $originationCity = Flight::request()->data->originationCity;
    $originationState = Flight::request()->data->originationState;
    $originationZip = Flight::request()->data->originationZip;
    $destinationAddress1 = Flight::request()->data->destinationAddress1;
    $destinationCity = Flight::request()->data->destinationCity;
    $destinationState = Flight::request()->data->destinationState;
    $destinationZip = Flight::request()->data->destinationZip;
    $originationLat = Flight::request()->data->originationLat;
    $originationLng = Flight::request()->data->originationLng;
    $destinationLat = Flight::request()->data->destinationLat;
    $destinationLng = Flight::request()->data->destinationLng;
    $distance = Flight::request()->data->distance;
    $transportationMode = Flight::request()->data->transportationMode;
    $transportation_mode = Flight::request()->data->transportation_mode;
    $transportation_type = Flight::request()->data->transportation_type;
    $pickupDate = Flight::request()->data->pickupDate;
    $deliveryDate = Flight::request()->data->deliveryDate;
    $customerneed = Flight::customerNeeds();
    $result = $customerneed->committoneed(API_HOST,$id,$rootCustomerNeedsID,$carrierID,$qty,$originationAddress1,$originationCity,$originationState,$originationZip,$destinationAddress1,$destinationCity,$destinationState,$destinationZip,$originationLat,$originationLng,$destinationLat,$destinationLng,$distance,$transportationMode,$transportation_mode,$transportation_type,$pickupDate,$deliveryDate,GOOGLE_MAPS_API);
    if ($result == "success") {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});
*/

$app->route('GET|POST /availabilitymatching/@id', function($id) {
    //$customerneedid = Flight::request()->data->id;
    $customerneed = Flight::customerNeeds();
    $matchingresult = $customerneed->availabilityMatching(API_HOST,$id);
    if ($matchingresult) {
        print_r($matchingresult);
        //echo "success";
    } else {
        print_r($matchingresult);
    }
});

/*****************************************************************************/
// Ducument Upload
/*****************************************************************************/
$app->route('POST /uploaddocument', function() {
	$name = Flight::request()->data->name;
	$fileupload = Flight::request()->files['fileupload'];
	$documentID = Flight::request()->data->documentID;
	$updatedAt = Flight::request()->data->updatedAt;
	$entityID = Flight::request()->data->entityID;
	$documentURL = HTTP_HOST."/viewdocument?entityID=".$entityID."&filename=".$fileupload['name'];
	$documents = Flight::documents();
    $result = $documents->createFromExisting(API_HOST,FILE_LOCATION,$fileupload,$name,$documentID,$documentURL,$updatedAt,$entityID);
    if ($result) {
        print_r($result);
    } else {
        print_r($result);
    }
});
$app->route('GET /viewdocument', function() {
	$entityID = Flight::request()->query['entityID'];
	$filename = Flight::request()->query['filename'];
	$documents = Flight::documents();
    $result = $documents->viewdocument($entityID,FILE_LOCATION,$filename);
});
/*****************************************************************************/
// Bulk Import
/*****************************************************************************/
$app->route('POST /carrierbulkupload', function() {
	$name = Flight::request()->data->name;
	$fileupload = Flight::request()->files['fileupload'];
	$documentID = Flight::request()->data->documentID;
	$updatedAt = Flight::request()->data->updatedAt;
	$entityID = Flight::request()->data->entityID;
	$documentURL = HTTP_HOST."/viewdocument?entityID=".$entityID."&filename=".$fileupload['name'];
	$documents = Flight::documents();
    $result = $documents->carrierBulkUpload(API_HOST,HTTP_HOST,FILE_LOCATION,$fileupload,$name,$documentID,$documentURL,$updatedAt,$entityID);
	echo $result;
});

$app->route('POST /customerbulkupload', function() {
	$name = Flight::request()->data->name;
	$fileupload = Flight::request()->files['fileupload'];
	$documentID = Flight::request()->data->documentID;
	$updatedAt = Flight::request()->data->updatedAt;
	$entityID = Flight::request()->data->entityID;
	$documentURL = HTTP_HOST."/viewdocument?entityID=".$entityID."&filename=".$fileupload['name'];
	$documents = Flight::documents();
    $result = $documents->customerBulkUpload(API_HOST,HTTP_HOST,FILE_LOCATION,$fileupload,$name,$documentID,$documentURL,$updatedAt,$entityID);
	echo $result;
});
/*****************************************************************************/
// Order Processes
/*****************************************************************************/
$app->route('POST /sendorderupdatenotification', function() {

    $rateType = Flight::request()->data->rateType;
    $transportationMode = Flight::request()->data->transportationMode;
    $originationAddress = Flight::request()->data->originationAddress;
    $originationCity = Flight::request()->data->originationCity;
    $originationState = Flight::request()->data->originationState;
    $originationZip = Flight::request()->data->originationZip;
    $destinationAddress = Flight::request()->data->destinationAddress;
    $destinationCity = Flight::request()->data->destinationCity;
    $destinationState = Flight::request()->data->destinationState;
    $destinationZip = Flight::request()->data->destinationZip;
    $distance = Flight::request()->data->distance;
    $updatedAt = Flight::request()->data->updatedAt;
    $orderNumber = Flight::request()->data->orderNumber;
    $customerID = Flight::request()->data->customerID;
    $podList = Flight::request()->data->podList;

    $orderNotification = Flight::orders();

    $result = $orderNotification->sendEmailNotification($rateType, $transportationMode, $originationAddress, $originationCity, $originationState, $originationZip,
            $destinationAddress, $destinationCity, $destinationState, $destinationZip, $distance, $updatedAt, $orderNumber, $customerID, $podList);

    print_r($result);
});

$app->route('POST /sendorderstatusnotification', function() {

    $orderNumber = Flight::request()->data->orderNumber;
    $customerID = Flight::request()->data->customerID;
    $carrierID = Flight::request()->data->carrierID;

    $orderNotification = Flight::orders();

    $result = $orderNotification->sendOrderStatusNotification($orderNumber, $carrierID, $customerID);
    print_r($result);
});

$app->route('POST /indexgetorders', function() {
	$locationStatus = Flight::request()->data->locationStatus;
	$stateFilter = Flight::request()->data->stateFilter;
    $cityFilter = Flight::request()->data->cityFilter;
    $entityid = Flight::request()->data->entityid;
    $entitytype = Flight::request()->data->entitytype;
	$orders = Flight::orders();
	$db = Flight::db();
    $return = $orders->indexgetorders($db,$locationStatus,$stateFilter,$cityFilter,$entityid,$entitytype);
    echo $return;
});

$app->route('POST /getorderdetailstatuses', function() {
	$orderID = Flight::request()->data->orderID;
	$orders = Flight::orders();
	$db = Flight::db();
    $return = $orders->getorderdetailstatuses($db,$orderID);
    echo $return;
});

/* Not used yet for dashboard filter Either/Or
$app->route('POST /indexgetneeds', function() {
	$locationStatus = Flight::request()->data->locationStatus;
	$stateFilter = Flight::request()->data->stateFilter;
    $cityFilter = Flight::request()->data->cityFilter;
    $entityid = Flight::request()->data->entityid;
    $entitytype = Flight::request()->data->entitytype;
	$carrierneeds = Flight::carrierNeeds();
	$db = Flight::db();
	echo "here";
    $return = $carrierneeds->indexgetneeds($db,$locationStatus,$stateFilter,$cityFilter,$entityid,$entitytype);
    echo $return;
});

$app->route('POST /indexgetavailability', function() {
	$locationStatus = Flight::request()->data->locationStatus;
	$stateFilter = Flight::request()->data->stateFilter;
    $cityFilter = Flight::request()->data->cityFilter;
    $entityid = Flight::request()->data->entityid;
    $entitytype = Flight::request()->data->entitytype;
	$customerneeds = Flight::customerNeeds();
	$db = Flight::db();
    $return = $customerneeds->indexgetavailability($db,$locationStatus,$stateFilter,$cityFilter,$entityid,$entitytype);
    echo $return;
});
*/

/*****************************************************************************/
// Reporting Processes
/*****************************************************************************/
$app->route('POST /getdeliveredaveragedays', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getdeliveredaveragedays($db,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getdeliveredaveragedayscsv', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getdeliveredaveragedayscsv($db,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getdeliveredstatebystate', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getdeliveredstatebystate($db,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getdeliveredstatebystatecsv', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getdeliveredstatebystatecsv($db,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getdeliveredtrailers', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getdeliveredtrailers($db,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getdeliveredtrailerscsv', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getdeliveredtrailerscsv($db,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getundeliveredtrailers', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getundeliveredtrailers($db,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getundeliveredtrailerscsv', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getundeliveredtrailerscsv($db,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getarsummary', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getarsummary($db,$entityType,$entityID);
        echo $result;
        die();
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getarsummarycsv', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getarsummarycsv($db,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getardetail', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $startDate = Flight::request()->data->startDate;
    $endDate = Flight::request()->data->endDate;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getardetail($db,$startDate,$endDate,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getardetailcsv', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $startDate = Flight::request()->data->startDate;
    $endDate = Flight::request()->data->endDate;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getardetailcsv($db,$startDate,$endDate,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getrevenueanalysis', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $startDate = Flight::request()->data->startDate;
    $endDate = Flight::request()->data->endDate;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getrevenueanalysis($db,$startDate,$endDate,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getrevenueanalysiscsv', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $startDate = Flight::request()->data->startDate;
    $endDate = Flight::request()->data->endDate;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getrevenueanalysiscsv($db,$startDate,$endDate,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getavailabilitywithnocommits', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getavailabilitywithnocommits($db,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /getavailabilitywithnocommitscsv', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->getavailabilitywithnocommitscsv($db,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /gettrailersbooked', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $startDate = Flight::request()->data->startDate;
    $endDate = Flight::request()->data->endDate;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->gettrailersbooked($db,$startDate,$endDate,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /gettrailersbookedcsv', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $startDate = Flight::request()->data->startDate;
    $endDate = Flight::request()->data->endDate;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->gettrailersbookedcsv($db,$startDate,$endDate,$entityType,$entityID);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /gettrends', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $trendEntityType = Flight::request()->data->trendEntityType;
    $timeFrame = Flight::request()->data->timeFrame;
    $source = Flight::request()->data->source;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->gettrends($db,$entityType,$entityID,$timeFrame,$trendEntityType,$source);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});

$app->route('POST /gettrendscsv', function() {
    $entityType = Flight::request()->data->entitytype;
    $entityID = Flight::request()->data->entityid;
    $trendEntityType = Flight::request()->data->trendEntityType;
    $timeFrame = Flight::request()->data->timeFrame;
    $source = Flight::request()->data->source;
    $reports = Flight::reports();
    $db = Flight::db();
    $result = $reports->gettrendscsv($db,$entityType,$entityID,$timeFrame,$trendEntityType,$source);
    if ($result) {
        print_r($result);
        //echo "success";
    } else {
        print_r($result);
    }
});


/*****************************************************************************/
// POD API Process
/*****************************************************************************/
$app->route('POST /pod_api', function() {

    // Data will be passed through using the format below
    //$customerneedid = Flight::request()->data->id;

    // This is setup using config/setup.php
    $podAPI = Flight::quickbooks();

    // This is the calling method inside the class
    $apiResponse = $podAPI->testMethod();


    if ($apiResponse) {
        print_r($apiResponse);
        //echo "success";
    } else {
        print_r($apiResponse);
    }
});

/*****************************************************************************/
// POD API Process
/*****************************************************************************/
$app->route('POST /pod_form_api', function() {

    $podFormType = Flight::request()->data->podFormType;
    $unitNumber = Flight::request()->data->unitNumber;
    $vinNumber = Flight::request()->data->vinNumber;
    $trailerProNumber = Flight::request()->data->trailerProNumber;
    $year = Flight::request()->data->year;
    $size = Flight::request()->data->size;
    $type = Flight::request()->data->type;
    $door = Flight::request()->data->door;
    $decals = Flight::request()->data->decals;
    $pickupLocation = Flight::request()->data->pickupLocation;
    $originationAddress = Flight::request()->data->originationAddress;
    $originationCity = Flight::request()->data->originationCity;
    $originationState = Flight::request()->data->originationState;
    $originationZipcode = Flight::request()->data->originationZipcode;
    $pickupContact = Flight::request()->data->pickupContact;
    $pickupPhoneNumber= Flight::request()->data->pickupPhoneNumber;
    $pickupHours = Flight::request()->data->pickupHours;
    $deliveryLocation = Flight::request()->data->deliveryLocation;
    $destinationAddress = Flight::request()->data->destinationAddress;
    $destinationCity = Flight::request()->data->destinationCity;
    $destinationState = Flight::request()->data->destinationState;
    $destinationZipcode = Flight::request()->data->destinationZipcode;
    $deliveryContact = Flight::request()->data->deliveryContact;
    $deliveryPhoneNumber= Flight::request()->data->deliveryPhoneNumber;
    $deliveryHours = Flight::request()->data->deliveryHours;

    // initiate FPDI
    $pdf = new FPDI();
    $fileName = "";

    if($podFormType == 'Hyundai') {

        try {

            $fileName = "downloadfiles/hyundai-release-form-report.pdf";

            $pageCount = $pdf->setSourceFile($fileName);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $dimSize = $pdf->getTemplateSize($templateId);

                if ($dimSize['width'] > $dimSize['height']) {
                    $pdf->AddPage('L', array($dimSize['width'], $dimSize['height']));
                } else {
                    $pdf->AddPage('P', array($dimSize['width'], $dimSize['height']));
                }

                $pdf->useTemplate($templateId);

                // Date:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(35, 53.5);
                $pdf->Write(0, date('m/d/Y'));

                // Unit #:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(104, 63.5);
                $pdf->Write(0, $unitNumber);

                // Type:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(104, 73.5);
                $pdf->Write(0, $type);

                // Vin #:
                $pdf->SetFont('Helvetica', 'B', 11);
                $pdf->SetXY(146, 63);
                $pdf->Write(0, $vinNumber);

                // Size:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(148, 73.5);
                $pdf->Write(0, $size);

                // Company :
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(35, 86);
                $pdf->Write(0, $pickupLocation);

                // pickupContact:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(35, 92);
                $pdf->Write(0, $pickupContact);

                // Street Address:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(35, 96);
                $pdf->Write(0, $originationAddress);

                // City, State Abbr, Zipcode:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(35, 100);
                $pdf->Write(0, $originationCity . ', ' .  $originationState . ' ' . $originationZipcode);

                // pickupPhoneNumber:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(35, 106);
                $pdf->Write(0, $pickupPhoneNumber);

                // Company :
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(126, 86);
                $pdf->Write(0, $deliveryLocation);

                // destinationContact:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(126, 92);
                $pdf->Write(0, $deliveryContact);

                // Street Address:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(126, 96);
                $pdf->Write(0, $destinationAddress);

                // City, State Abbr, Zipcode:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(126, 100);
                $pdf->Write(0, $destinationCity . ', ' .  $destinationState . ' ' . $destinationZipcode);

                // destinationPhoneNumber:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(126, 106);
                $pdf->Write(0, $deliveryPhoneNumber);

            }

            $fileName = "hyundai-release-form-report-" . str_replace(" ", "-", strtolower($podFormType)) . "-" . $vinNumber . ".pdf";
            $pdf->Output(TEMP_LOCATION . "/" . $fileName, 'F');

        }
        catch(Exception $e) {
            throw $e;
        }

    } else {

        try {

            $fileName = "downloadfiles/nationwide-pod-form.pdf";

            $pageCount = $pdf->setSourceFile($fileName);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $dimSize = $pdf->getTemplateSize($templateId);

                if ($dimSize['width'] > $dimSize['height']) {
                    $pdf->AddPage('L', array($dimSize['width'], $dimSize['height']));
                } else {
                    $pdf->AddPage('P', array($dimSize['width'], $dimSize['height']));
                }

                $pdf->useTemplate($templateId);

                // Column Headers
                // Trailer Description:
                $pdf->SetFont('Helvetica', 'B', 11);
                $pdf->SetXY(60, 15);
                $pdf->Write(0, "Trailer Description");

                // Pickup Location:
                $pdf->SetFont('Helvetica', 'B', 11);
                $pdf->SetXY(113, 15);
                $pdf->Write(0, "Pickup Location");

                // Delivery Location:
                $pdf->SetFont('Helvetica', 'B', 11);
                $pdf->SetXY(165, 15);
                $pdf->Write(0, "Delivery Location");



                // 1st Column
                // Unit #:
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(60, 20);
                $pdf->Write(0, "Unit #");
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(70, 20);
                $pdf->Write(0, $unitNumber);

                // Vin #:
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(60, 24);
                $pdf->Write(0, "VIN #");
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(70, 24);
                $pdf->Write(0, $vinNumber);

                // Sec. Unit #:
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(60, 28);
                $pdf->Write(0, "Sec. Unit #");
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(79, 28);
                $pdf->Write(0, $trailerProNumber);

                // Year:
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(60, 32);
                $pdf->Write(0, "Year");
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(70, 32);
                $pdf->Write(0, $year);

                // Size:
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(60, 36);
                $pdf->Write(0, "Size");
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(70, 36);
                $pdf->Write(0, $size);

                // Type:
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(60, 40);
                $pdf->Write(0, "Type");
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(70, 40);
                $pdf->Write(0, $type);

                // Door:
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(60, 44);
                $pdf->Write(0, "Door");
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(70, 44);
                $pdf->Write(0, $door);

                // Decals:
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(60, 48);
                $pdf->Write(0, "Decals");
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(70, 48);
                $pdf->Write(0, $decals);

                // 2nd Column

                // Company :
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(113, 20);
                $pdf->Write(0, $pickupLocation);

                if (strlen($originationCity) > 10 || strlen($originationZipcode) > 5) {

                    // Street Address:
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetXY(113, 24);
                    $pdf->Write(0, $originationAddress);

                    // City:
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetXY(113, 28);
                    $pdf->Write(0, $originationCity);

                    // State Abbr, Zipcode:
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetXY(113, 32);
                    $pdf->Write(0, $originationState . ' ' . $originationZipcode);

                } else {

                    // Street Address:
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetXY(113, 24);
                    $pdf->Write(0, $originationAddress);

                    $cityAddress = $originationCity . ', ' .  $originationState . ' ' . $originationZipcode;

                    // City, State Abbr, Zipcode:
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetXY(113, 28);
                    $pdf->Write(0, $cityAddress);

                }

                // pickupPhoneNumber:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(113, 40);
                $pdf->Write(0, $pickupPhoneNumber);

                // pickupContact:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(113, 44);
                $pdf->Write(0, $pickupContact);

                // pickupHours:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(113, 48);
                $pdf->Write(0, $pickupHours);

                // 3rd Column

                // Company :
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(165, 20);
                $pdf->Write(0, $deliveryLocation);

                if (strlen($destinationCity) > 10 || strlen($destinationZipcode) > 5) {

                    // Street Address:
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetXY(165, 24);
                    $pdf->Write(0, $destinationAddress);

                    // City:
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetXY(165, 28);
                    $pdf->Write(0, $destinationCity);

                    // State Abbr, Zipcode:
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetXY(165, 32);
                    $pdf->Write(0, $destinationState . ' ' . $destinationZipcode);

                } else {

                    // Street Address:
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetXY(165, 24);
                    $pdf->Write(0, $destinationAddress);

                    $cityAddress = $destinationCity . ', ' .  $destinationState . ' ' . $destinationZipcode;

                    // City, State Abbr, Zipcode:
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetXY(165, 28);
                    $pdf->Write(0, $cityAddress);

                }

                // destinationPhoneNumber:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(165, 40);
                $pdf->Write(0, $deliveryPhoneNumber);

                // destinationContact:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(165, 44);
                $pdf->Write(0, $deliveryContact);

                // destinationHours:
                $pdf->SetFont('Helvetica', '', 9);
                $pdf->SetXY(165, 48);
                $pdf->Write(0, $deliveryHours);

            }

            $fileName = "nationwide-pod-form-" . str_replace(' ', '-', strtolower($podFormType)) . "-" . $vinNumber . ".pdf";
            $pdf->Output(TEMP_LOCATION . '/' . $fileName, 'F');

        }
        catch(Exception $e) {
            throw $e;
        }

    }

    echo $fileName;
});

$app->route('GET /download-pdf/@filename', function($filename) {

    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . filesize(__DIR__ . "/tmp/" . $filename));
    header('Content-Disposition: attachment; filename=' . basename($filename));

    readfile(__DIR__ . "/tmp/" . $filename);
    unlink(__DIR__ . "/tmp/" . $filename);

});

/*****************************************************************************/
// Quickbooks API Status Page
/*****************************************************************************/

$app->route('GET /qb_api_status', function() {

    // Data will be passed through using the format below

    //$customerneedid = Flight::request()->data->id;

    // This is setup using config/setup.php
    $podAPI = Flight::quickbooks();

    // This is the calling method inside the class
    $apiResponse = $podAPI->isConnected();

    //echo $apiResponse;

   //Flight::render('qbstatus', array('response'=> $apiResponse));

   //Flight::render('qbstatus', array('response'=> $apiResponse));

    print_r($apiResponse);


   //Flight::render('qbstatus', array('response'=> $apiResponse));

});


$app->route('GET|POST /oauth', function() {
    // Data will be passed through using the format below
    //$customerneedid = Flight::request()->data->id;

    // This is setup using config/setup.php
    $podAPI = Flight::quickbooks();

    // This is the calling method inside the class
    $apiResponse = $podAPI->oauth();


   if ($apiResponse) {
        print_r($apiResponse);
        //echo "success";
    } else {
        print_r($apiResponse);
    }

});


//oauth

/**
 * APPLICATION API ROUTES
 */

/**
 * READ REQUESTS
 */

    $app->route('GET /profiles/business/info', function() {

        /* TODO: Handling Authenication */

        try {

            $states = Flight::states()->read(array(
                'columns' => array('abbreviation', 'name'),
                'order' => array('name')
            ));

            $entities = Flight::entities()->read(array(
                'include' => array('members', 'users', 'locations', 'contacts'),
                'filter' => array('id' => $_SESSION['entityid'])
            ));

            $response = array(
                'status' => 'success',
                'results' => array(
                    'id' => (isset($entities['entities']['records'][0][0])) ? $entities['entities']['records'][0][0] : 0,
                    'locationID' => 0,
                    'contactID' => 0,
                    'entityName' => (isset($entities['entities']['records'][0][2])) ? $entities['entities']['records'][0][2] : "",
                    'address1' => "",
                    'address2' => "",
                    'city' => "",
                    'state' => "",
                    'zip' => "",
                    'firstName' => "",
                    'lastName' => "",
                    'title' => "",
                    'emailAddress' => "",
                    'primaryPhone' => "",
                    'fax' => "",
                    'states' => (isset($states['states']['records'])) ? $states['states']['records'] : array()
                )
            );

            foreach ($entities['locations']['records'] as $row => $records) {
                foreach ($records as $column => $item) {
                    if ($column == 2 && $item == 1) {
                        $response['results']['locationID'] = $entities['locations']['records'][$row][0];
                        $response['results']['address1'] = $entities['locations']['records'][$row][4];
                        $response['results']['address2'] = $entities['locations']['records'][$row][5];
                        $response['results']['city'] = $entities['locations']['records'][$row][6];
                        $response['results']['state'] = $entities['locations']['records'][$row][7];
                        $response['results']['zip'] = $entities['locations']['records'][$row][8];
                    }
                }
            }

            foreach ($entities['contacts']['records'] as $row => $records) {
                foreach ($records as $column => $item) {
                    if ($column == 2 && $item == 1) {
                        $response['results']['contactID'] = $entities['contacts']['records'][$row][0];
                        $response['results']['firstName'] = $entities['contacts']['records'][$row][3];
                        $response['results']['lastName'] = $entities['contacts']['records'][$row][4];
                        $response['results']['title'] = $entities['contacts']['records'][$row][5];
                        $response['results']['emailAddress'] = $entities['contacts']['records'][$row][6];
                        $response['results']['primaryPhone'] = $entities['contacts']['records'][$row][7];
                        $response['results']['fax'] = $entities['contacts']['records'][$row][9];
                    }
                }
            }

            Flight::json($response);

        } catch (\ResponseException $responseException) {

            Flight::notFound();

        }

    });


/**
 * CREATE & UPDATE REQUESTS
 */

    $app->route('POST /profiles/business/info', function() {

        /* TODO: Handling Authenication */

        try {

            $response = array(
                'status' => 'success',
                'results' => array()
            );

            /* Validate fields */

            if (empty(Flight::request()->data->firstName)) {
                $response['status'] = "fail";
                $response['results']['firstName'] = "Please enter your first name";
            }

            if (empty(Flight::request()->data->lastName)) {
                $response['status'] = "fail";
                $response['results']['lastName'] = "Please enter your last name";
            }

            if (empty(Flight::request()->data->entityName)) {
                $response['status'] = "fail";
                $response['results']['entityName'] = "Please enter your company name";
            }

            if (empty(Flight::request()->data->primaryPhone)) {
                $response['status'] = "fail";
                $response['results']['primaryPhone'] = "Please enter your phone";
            }

            if (empty(Flight::request()->data->emailAddress)) {
                $response['status'] = "fail";
                $response['results']['emailAddress'] = "Please enter your email address";
            }

            if ($response['status'] === 'success') {

                if (Flight::request()->data->locationID > 0) {


                    // TODO: Handle webservice error 0 or [0,0]
                    //webservice did not update
                    $webservice = Flight::locations()->update(array(
                        'id' => Flight::request()->data->locationID,
                        'address1' => Flight::request()->data->address1,
                        'address2' => Flight::request()->data->address2,
                        'city' => Flight::request()->data->city,
                        'state' => Flight::request()->data->state,
                        'zip' => Flight::request()->data->zip,
                    ), array('type' => 'json'));


                } else {

                    $webservice = Flight::locations()->create(array(
                        'address1' => Flight::request()->data->address1,
                        'address2' => Flight::request()->data->address2,
                        'city' => Flight::request()->data->city,
                        'state' => Flight::request()->data->state,
                        'zip' => Flight::request()->data->zip,
                    ), array('type' => 'json'));

                }

                if (Flight::request()->data->contactID > 0) {

                    $webservice = Flight::contacts()->update(array(
                        'id' => Flight::request()->data->contactID,
                        'firstName' => Flight::request()->data->firstName,
                        'lastName' => Flight::request()->data->lastName,
                        'title' => Flight::request()->data->title,
                        'emailAddress' => Flight::request()->data->emailAddress,
                        'primaryPhone' => Flight::request()->data->primaryPhone,
                        'fax' => Flight::request()->data->fax
                    ), array('type' => 'json'));

                } else {

                    $webservice = Flight::contacts()->create(array(
                        'firstName' => Flight::request()->data->firstName,
                        'lastName' => Flight::request()->data->lastName,
                        'title' => Flight::request()->data->title,
                        'emailAddress' => Flight::request()->data->emailAddress,
                        'primaryPhone' => Flight::request()->data->primaryPhone,
                        'fax' => Flight::request()->data->fax
                    ), array('type' => 'json'));

                }

                $response['statusMessage'] = "Business Profile has been successfully updated!";

            }

            Flight::json($response);

        } catch (\ResponseException $responseException) {

            Flight::notFound();

        }

    });

/**
 * DELETE REQUESTS
 */


/*****************************************************************************/
// Logs API Calls
/*****************************************************************************/

$app->route('POST /save_to_log', function() {

    $log_type_name = Flight::request()->data->logTypeName;
    $log_msg = Flight::request()->data->logMessage;
    $ref_id = Flight::request()->data->referenceID;

    $logsAPI = Flight::logs();

    $apiResponse = $logsAPI->enter_log($log_type_name, $log_msg, $ref_id);

    print_r($apiResponse);
});

$app->route('GET /customer_needs_log/@customerNeedsID', function($customerNeedsID) {
    $logsAPI = Flight::logs();

    $apiResponse = $logsAPI->get_log_for_customer_needs($customerNeedsID);

    print_r($apiResponse);
});

$app->route('GET /orders_log/@orderID', function($ordersID) {
    $logsAPI = Flight::logs();

    $apiResponse = $logsAPI->get_log_for_orders($ordersID);

    print_r($apiResponse);
});

// Start the framework
$app->start();
