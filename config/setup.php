<?php

date_default_timezone_set('America/New_York');

session_start();

Flight::set('flight.views.path', 'views');

require '../nec_config.php';

// Development Debugging
if(ENVIRONMENT == 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS ), function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
});

require 'lib/common.php';
require "lib/googleApiClass.php";
require 'lib/fpdf/fpdf.php';
require "lib/fpdi/src/autoload.php";

require 'models/CarrierNeeds.php';
require 'models/Contacts.php';
require 'models/CustomerNeeds.php';
require 'models/CustomerNeedsCommit.php';
require 'models/Documents.php';
require 'models/Entities.php';
require 'models/FleetList.php';
require 'models/InsuranceCarriers.php';
require 'models/Locations.php';
require 'models/LocationsContacts.php';
require 'models/Members.php';
require 'models/MessageCenter.php';
require 'models/Orders.php';
require 'models/Reports.php';
require 'models/States.php';
require 'models/Users.php';

Flight::register('carrierNeeds', 'CarrierNeeds');
Flight::register('contacts', 'Contacts' );
Flight::register('customerNeeds', 'CustomerNeeds' );
Flight::register('customerNeedsCommit', 'CustomerNeedsCommit');
Flight::register('documents', 'Documents');
Flight::register('entities', 'Entities');
Flight::register('fleetList', 'FleetList');
Flight::register('insuranceCarrier', 'InsuranceCarriers');
Flight::register('locations', 'Locations');
Flight::register('locationsContacts', 'LocationsContacts' );
Flight::register('members', 'Members');
Flight::register('messagecenter', 'MessageCenter');
Flight::register('orders', 'Orders');
Flight::register('reports', 'Reports');
Flight::register('states','States');
Flight::register('users', 'Users');

