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
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;



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


//db call 

$dbh = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME)
     or die ('cannot connect to database because ' . mysqli_connect_error());

   
   //run the query
$invoiceNumberLoop = mysqli_query($dbh, "select distinct(qbInvoiceNumber) as qbInvoiceNumber from approved_damage_claims where hasBeenInvoiced = 1")
   or die (mysqli_error($dbh));


while($invoiceNumberRow = mysqli_fetch_array($invoiceNumberLoop)){
    
    $found_invoice_id = $invoiceNumberRow['qbInvoiceNumber'];
    


$dataService = DataService::Configure(array(
           'auth_mode' => 'oauth2',
            'ClientID' => $ClientID,
             'ClientSecret' => $ClientSecret,
    'accessTokenKey' =>  $accessTokenKey,
    'refreshTokenKey' => $refreshTokenKey,
              'QBORealmID' => $QBORealmID,
             'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
    ));

    //$found_invoice_id = 0;

//    echo "Making Connection Before Checking for Invoice";
//    echo '<hr>';

    $invoicedata = $dataService->Query("SELECT * FROM Invoice WHERE id in ('{$found_invoice_id}')");
    $error = $dataService->getLastError();
    
    if ($error != null) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "<br><br>";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "<br><br>";
        echo "The Response message is: " . $error->getResponseBody() . "<br><br>";
        exit();
    }
    else{
        
        $TotalAmount = floatval($invoicedata[0]->TotalAmt);
        $RemainingBalance = floatval($invoicedata[0]->Balance);
        
        $quickBooksStatus = "Open";
        
        if($RemainingBalance == 0){
            $quickBooksStatus = "Paid";
        }
        elseif($RemainingBalance < $TotalAmount){
            $quickBooksStatus = "Partial";
        }
        
        
        // Create connection
        $conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 

        $sql = "UPDATE approved_damage_claims SET qbInvoiceStatus ='".$quickBooksStatus."', updatedAt = NOW() WHERE qbInvoiceNumber=".$found_invoice_id;
        echo $sql;
        echo '<br>';
        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
            echo '<hr>';
        } else {
            echo "Error updating record: " . $conn->error;
        }

        $conn->close();
    }
    
}

exit();