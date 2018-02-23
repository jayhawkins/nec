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


//db call 

$dbh = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME)
     or die ('cannot connect to database because ' . mysqli_connect_error());

   
   //run the query
$invoiceNumberLoop = mysqli_query($dbh, "select distinct(qbInvoiceNumber) as qbInvoiceNumber from approved_pod where hasBeenInvoiced = 1")
   or die (mysqli_error($dbh));


while($invoiceNumberRow = mysqli_fetch_array($invoiceNumberLoop)){
    
    $found_invoice_id = $invoiceNumberRow['qbInvoiceNumber'];
    


$dataService = DataService::Configure(array(
           'auth_mode' => 'oauth2',
             'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
             'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
    'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..ZbE_cCRvpC1Jv16WNWI1gw.8PK-TS5MM1XRG2GS_Xh_ztSaDMR-UDPFY9uqQAV1lm0_LnQxe_N9AuIFHI0YbabJLMgzsyE1jzpLeV3C09OnoVDkArf_gErfhk92FTHIAouiWDjBTqDJCNC6vG11fb0lUxlcNtTFqHIMe6HkvC7odJnsF3qeLn4P3xvS0wtjpAiQwg5-AWrzm_4lybAPYoBKjU8Ss610qFioRodGB3cxe_mAmfnI-38Pj7I6D4gRgkOGlU0V9Bb_WiUG35ODhy_0sGcBRf6yjXdDm8ZuRmZNMafVP8DgupQEUtLMw9t2mxQECsTHGJ3Z8s6tTfxQV--1ok2uD_pzwc35YUHUs7j0wFEanFBEyQfqEJBoHLvkTa8_bD8sTTdx23nYtGzKq-hj_fAxXdWIJyPfHqGj45GE5KqvHGQTjALQ_BB2mqknOGsWjBAYAdjmpxFQEC3Q2dtGleRNXc7AFtNzOgr1o7_FtKZZxO0zVf6d8xO0qIAjGz-vYOBbM4bgaQ0tT-rrPxvLHIHw4VHO2CoqtcCbP0OXA58K3BmcsmwYTIeY3uq_-H6LdCgoCWVrioZxIMJoaw3gRcCLzrrFE2sP-ZIzP-JaxGo2UbY6PAxf7HG7KHc49LNBBEUmZux2rJRcvn8ZKaNEq4fHyWCz6aVQ_caTivkASuXogybQqQo0jgJqt6-mZfGvv4UKjLBzcS-DTpXvvtma.y9bsUKtWaPxDwHh1qqDieg",
    'refreshTokenKey' => "L011528129387zcnvZehMd7sUoxqBLXInaqONHEP81FDDSZCoC",
              'QBORealmID' => "123145985783569",
             'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
    ));

    //$found_invoice_id = 0;

    echo "Making Connection Before Checking for Invoice";
    echo '<hr>';

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

        $sql = "UPDATE approved_pod SET qbInvoiceStatus ='".$quickBooksStatus."', updatedAt = NOW() WHERE qbInvoiceNumber=".$found_invoice_id;
        echo $sql;
        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }

        $conn->close();
    }
    
}

exit();