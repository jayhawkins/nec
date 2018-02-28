<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../config.php');

require '../../../../nec_config.php';


use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;


$ClientID = ""; 
$ClientSecret = "";
$accessTokenKey = "";
$refreshTokenKey = "";
$QBORealmID = "";
$dbh = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME)
     or die ('cannot connect to database because ' . mysqli_connect_error());


//get quickbooks credentials from db
   //run the query
$qbLoop = mysqli_query($dbh, "select * from quickbooks_authentication where id = 1")
   or die (mysqli_error($dbh));


while($qbRow = mysqli_fetch_array($qbLoop)){
    
$ClientID = $qbRow['clientID']; 
$ClientSecret = $qbRow['ClientSecret']; 
$accessTokenKey = $qbRow['accessTokenKey']; 
$refreshTokenKey = $qbRow['refreshToken']; 
$QBORealmID =  $qbRow['realmID']; 
    
}

$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => $ClientID,
    'ClientSecret' => $ClientSecret,
    'scope' => "com.intuit.quickbooks.accounting",
    'QBORealmID' => $QBORealmID,
    'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
));

$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

$authorizationUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

print_r($authorizationUrl);
exit();

?>
