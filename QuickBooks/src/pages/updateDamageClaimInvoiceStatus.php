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
$invoiceNumberLoop = mysqli_query($dbh, "select distinct(qbInvoiceNumber) as qbInvoiceNumber from approved_damage_claims where hasBeenInvoiced = 1")
   or die (mysqli_error($dbh));


while($invoiceNumberRow = mysqli_fetch_array($invoiceNumberLoop)){
    
    $found_invoice_id = $invoiceNumberRow['qbInvoiceNumber'];
    


$dataService = DataService::Configure(array(
           'auth_mode' => 'oauth2',
             'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
             'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
        'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..kJ-R9Mb6aJUMOQ-11Nh6PA.xUuAyaV6Vq21zPZg7VwxkxRJZ4fAqn2iOxVgybxAkRwYZn7Vn-L1J5MYFi1f9k34vDDcb3SmVA7EboF2UwXjqe4TF_gjXinWNIMMN2_Hk-AX__vp-Kea1_icMDblBoGe8O6wOjPimxpUHUaNi9e4Sxp_l9x2mdYXMHQKtQMrCjy28PO-6PQLgRvo1YNfbocXIHR5y1_1EtUvWrPpfcvdgu0h0O9SqCioyp2q8XnRFAvHDdyicxHImmd7SpcX9S0dOSv-e3AppDdFaLfvnJ0lirPjTFHuI4ausUF52-ToeQBixo5UyL7Imwsfe5M8b9J9a_MnuwN9g3BmsOGLZWI3kbMoEMxZNstprNy3VLev3iHJClGugNbsVS2l7rrdy00bG9yJz4ybBAMo8AFd9wTgN84L0XkdK7lrwBLAe-4JFU_No1EruiINbMQUNXjSqXsQ3ntVqL-TxsfrE03kcojskrHnmsbv5vyqLfAB_kAYFMFSMlI7skxVuKVKsm1ra4Q8J7ZMvageSHLJZaL-Y4JClnRpCFP1sD3CHAen9WuLDvEHp7dFUp7GBx8udXitSIHNz1-sZ_fE2c5BFUCH7YZ3UNfCh44ykIjzNCfrbguK3t7AXz39vke5zNAibgE5AYBx5Z1V_4Yh4bWZoMphgFZnFI0drLFGxnEfSo2_egnK6jPG98rbrQk58drL94zANL6Z.R8RnVEwvck3UDLSBsu_ocg",
        'refreshTokenKey' => "L011528130384xJ2dkI5YPCK6MPw9LiKlosebIKt0HlJgaPB4d",
              'QBORealmID' => "123145985783569",
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