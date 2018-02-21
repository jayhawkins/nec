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
         'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..zTHj-fOjXuVoAcUxHU_IwQ.yBuPQ8Wtti1QYkyoJ21n6VJA0MUQTtL_FgpHMOlqxypYoUEZ2ICkHkoKLLj88VhxnKLoHGkUAOvWXCXizhHLDQV60gavMxz7qu19eY8LLRKPyzMdw2LV5lURC6hx6VPJ2zKMxF0vGvng1iCUo6EleTya18BafJ5MZhKUt1iW9J40gCg4RLzMKQQbS2OE9xuhlnMQ9YYbQWX4XEsfEeUmZSxpiwIAxKjbueadcHtFKZqOEFkYisllwaWnzKISk0zqHHjUPaVnLexmmrxnj0rpQznSLilE3JTrXLkGdL682tY55wZFieVEFlNXKfKc4amBuGwtzG_u2GU5QWWy0yZN0D3trCioLbagcmn0-8NlUxO-LCwssAzaJ15Ks1AiTXLbPiVyOvs4TTLqdYlE4uWD5plcWjdJEDASlC-OS2h5mGoOtzueZAJGUw9SYwAtg8xOK4LtPgc2RFeu2lDn5f3G42A75Hmk4oxn3pQ9ZLgzhaJKaoBt4uj5TpqkA53kjHXNtwnjoovjWpfrLHBqrzgXcPNKY57XE5_4zxdPKXKKvQ9o7V4w3xeFnyUXSECSa2sF-2a6JjSWvnAzdOcDxsx77Q4ilNOsIv_yiJdc_0d1sroGvIGULD4YzohglhLupXTKKJS6Y3mDpOIvDm3eCH9GGfW5MzJ9mMY8Ny0yHwszLGvnxXv8Mkgs2OkNf60splA_.B0SVhTJv6aBDgTIF7YB7hQ",
             'refreshTokenKey' => "L0115279329299uo64LeFMxhpMvTmVJ4DG29eW2fGu2ay8XCPm",
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
        
        print_r( $invoicedata);
    }
    
}

exit();