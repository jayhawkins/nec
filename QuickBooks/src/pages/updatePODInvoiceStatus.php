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
         'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..e-IiSX8UTrMsuFyNB2vmUA.YKfO2160BXS9Z9tMyQcwpC9x8rOHR9F67U3Scl_NQD289JUpgqx--jJGLTLvnhpxDk5CS7DJzCiRxLLVNJXD2SsIWstdy_xW5Gcr8yTBx7vpkX63u5IMoJdTys1A9LWkqJqpqTWr864z-MprTQAT7Y26IjCijR-mXEjBNsP1P-8htJAx_bSQ1NbHGVk2J4Cw5lWHQiUOZOEONQgUO3nKUkc_CZPsQayplOn1Zq_31lz-pcZowEWiMSICPt8BcvcgH9xLzDRCubFI-Ng4UboVdVSFjB7iaDhJy-UXSVB7AjAoVtpx-6s8A0I57GB8maBMeDB3quJ13c8JFzYbsAMcmJizE0ubFde2pzngnYo5Gx67QZ4V0DEStzxpIX6Py1L37NmrMQzqwBrOlq3KXy3-gQi67hJuez-a3uJtjm3bzw9Ud6lE1ELFuWXMdaIfbF_AAM72S5s3k3fgWfrg9bpgH7DxiN5BjtsHVpuWp65isj7Mj0wVseRrIDZA4pzGzv2V2p55vhFMeIhm_mjSeaCSTqdzNsNDnUlWyESkETuAkzLEDD2EGGRR1uamCmwDXmOIRawFEX2o2qj3XGbePzrtL7IRuhPREDeandpmwrvIaHPWysKLEgLrkFJ0arEkB0PD1c6BzK3JJcVlVTQfgej8QU6yzxhzfBB9i1qA8RGWzu3YmbfaVj6YV5ZvJEYZz6ij.c9DsaUVZFhV2NQnKAiN0Og",
             'refreshTokenKey' => "L011528043040CnP7tXBsJ0l1WGzmDauGC6ri4KtRS9AejIgbO",
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
        
        print_r($found_invoice_id);
        print_r($invoicedata[0]['TotalAmt']);
        print_r($invoicedata[0]['Balance']);
        echo '<hr>';
    }
    
}

exit();