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
require 'models/Users.php';

Flight::register( 'user', 'User' );
