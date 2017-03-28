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


/*****************************************************************************/
// Test routes
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

$app->route('/', function() {
    Flight::render('login');
});

/*****************************************************************************/
// Admin routes
/*****************************************************************************/

$app->route('POST /login', function() {
    $username = Flight::request()->data['username'];
    $password = Flight::request()->data['password'];
    $user = Flight::user();
    $return = $user->loginapi($username,$password);
    if ($return) {
      Flight::render('dashboard', array());
    } else {
      $invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:'';
      Flight::render('login', array('invalidPassword'=> $invalidPassword));
    }
});

$app->route('POST /register', function() {
    $password = Flight::request()->data['password'];
    $firstName = Flight::request()->data['firstName'];
    $lastName = Flight::request()->data['lastName'];
    $email = Flight::request()->data['email'];
    $businessName = Flight::request()->data['businessName'];
    $businessType = Flight::request()->data['businessType'];
    $user = Flight::user();
    $return = $user->registerapi($password,$firstName,$lastName,$email,$businessName,$businessType);
    if ($return) {
      Flight::render('registrationsuccessful', array());
    } else {
      $invalidPassword = (isset($_SESSION['invalidPassword'])) ? $_SESSION['invalidPassword']:'';
      Flight::render('login', array('invalidPassword'=> $invalidPassword));
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


$app->route('/dashboard', function() {
    if (is_authorized()) {
      Flight::render('dashboard', array());
    } else {
      Flight::render('login', array());
    }
});



// Start the framework
$app->start();
