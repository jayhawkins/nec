<?php

require '../vendor/autoload.php';

date_default_timezone_set('America/New_York');

session_start();

require '../../nec_config.php';

Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=' . DBNAME, DBUSER, DBPASS ), function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'utf8'");
    $db->exec("SET NAMES 'utf8';");
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
});

$db = Flight::db();

// 111.045 AS distance_unit for Kilometers
// 69.0 AS distance_unit for miles

$result = $db->prepare("SELECT  z.originationZip,
                                z.originationCity,
                                z.originationLng,
                                z.originationLat,
                                p.distance_unit
                                       * DEGREES(ACOS(COS(RADIANS(p.latpoint))
                                       * COS(RADIANS(z.originationLat))
                                       * COS(RADIANS(p.longpoint) - RADIANS(z.originationLng))
                                       + SIN(RADIANS(p.latpoint))
                                       * SIN(RADIANS(z.originationLat)))) AS distance_in_miles
                        FROM customer_needs AS z
                        JOIN (   /* these are the query parameters */
                              SELECT  39.758  AS latpoint,  -84.191 AS longpoint,
                                      50.0 AS radius,      69.0 AS distance_unit
                          ) AS p ON 1=1
                        WHERE z.originationLat
                           BETWEEN p.latpoint  - (p.radius / p.distance_unit)
                               AND p.latpoint  + (p.radius / p.distance_unit)
                          AND z.originationLng
                           BETWEEN p.longpoint - (p.radius / (p.distance_unit * COS(RADIANS(p.latpoint))))
                               AND p.longpoint + (p.radius / (p.distance_unit * COS(RADIANS(p.latpoint))))
                        ORDER BY distance_in_miles
                        LIMIT 15
                        ");

$result->execute();
header('Content-type: application/json');
echo json_encode($result->fetchAll());

$db = null;
