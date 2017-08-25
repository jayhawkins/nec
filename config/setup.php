<?php

date_default_timezone_set('America/New_York');

session_start();

Flight::set('flight.views.path', 'views');

require '../nec_config.php';

Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS ), function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
});
$db = Flight::db();

require 'lib/common.php';
require "lib/googleApiClass.php";
require 'models/Users.php';
require 'models/Entities.php';
require 'models/Members.php';
require 'models/Locations.php';
require 'models/Contacts.php';
require 'models/LocationsContacts.php';
require 'models/CarrierNeeds.php';
require 'models/CustomerNeeds.php';
require 'models/CustomerNeedsCommit.php';
require 'models/Documents.php';
require 'models/InsuranceCarriers.php';
require 'models/Orders.php';

Flight::register( 'user', 'User' );
Flight::register( 'entity', 'Entity' );
Flight::register( 'member', 'Member' );
Flight::register( 'location', 'Location' );
Flight::register( 'contact', 'Contact' );
Flight::register( 'locationcontact', 'LocationContact' );
Flight::register( 'carrierneed', 'CarrierNeed' );
Flight::register( 'customerneed', 'CustomerNeed' );
Flight::register( 'customerneedcommit', 'CustomerNeedCommit' );
Flight::register( 'documents', 'Documents' );
Flight::register( 'insurancecarrier', 'InsuranceCarrier' );
Flight::register( 'order', 'Order' );
