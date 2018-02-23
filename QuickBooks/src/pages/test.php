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


    
    //query for cutomer// Prep Data Services
    $dataService = DataService::Configure(array(
           'auth_mode' => 'oauth2',
             'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
             'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
        'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..9yIB9pjdryM3QDYIZtqV-A.EhLOo88RDDLpYJh5Q8FRvW0vh_KhsS2_iQXGRYE30kR8lx9QxAT5y18-wXjmbA7V-AVqzBJyKhshxUUmlTETIdkLIAy-hIHevMNaZfDuqqFYfV2Vm5DXrg8JnhsLmRqGxKPLkfeR38v3kdF0golrMk0llkAZuNqUlekXkqtJo4mB72TRZ2B-iDtK0cPoO0KcqG_0DbJzV_kH104De5kMOZuGMxlIa-o_rHfWVdPbrhxtDsrHxVLgNmgg7jCCxLVJuwS0fNgKxi9bQ2OPXYM6wij0BRtLeiSzDdl7xTk6d81X_f2lH_Nlh8jTHSiamSeyl7dsfzHZZn8HNYxzQWdKlU9TrIxHr4tX9v9gtkL-IgnManTfxYFzyuUzbLFlGwcV9Ef32iFWGmbbrzHsyH5ByedAQBNDU0qA44otvokBQoFwdXYIhHsCwzRQvnfKM1vlTdAbuFH2dN3IYcxsSFF19Nrf-IFgNZ9FOkP1F1lgavpdEiG8PVw_AtXhOScjjpKFOdCM6KI1IMKgUTRtGVkh-uWm9Fzh7RnMRjKibcLnOJjIGg8KQAu1h2D9JUh-px5L3acg-cU3X8GtxqOWAyNakAkGPcZyisPRS0WZ8Z0qnJo_qSQxvPwZ2xgnqlvZxBOGTwOOQvsU1FeK-0g1YuSzSjLA_qkJSVKPAsQ_qYMBmb0bUtYUM34KHdT1Uyz2Rmnz.cufIfMnBktHRjkRvTa4lKA",
        'refreshTokenKey' => "L0115281440892w0kOQQPwOoR8QCd2BTH4akrsRUMLrG1Lof19",
              'QBORealmID' => "123145985783569",
             'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
    ));

    $entities = $dataService->Query("SELECT * FROM Item");
    $error = $dataService->getLastError();
    
    if ($error != null) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "<br><br>";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "<br><br>";
        echo "The Response message is: " . $error->getResponseBody() . "<br><br>";
        exit();
    }
    else{
        
        foreach($entities as $key => $value){
            
            print_r("ID: ". $value->Id);
            echo "<br>";
            print_r("Name: ". $value->Name);
            echo "<hr>";
        }
    }
    
   
?>
