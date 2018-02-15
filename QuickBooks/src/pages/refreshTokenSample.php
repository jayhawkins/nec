<?php
//header('Content-type: application/xml');
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
include('../config.php');

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;


$dataService = DataService::Configure(array(
   'auth_mode' => 'oauth2',
         'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
         'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
        'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..aO495yFJtl4mRAXgOnPIYg.zJvjH4qg52Kt18rnFe7n33kdBBzGKvTPkA1_xnVHMGmeJkobPeuBIbam2t60KVIYRoRMAA85ZyzOOJMmasMES40yiLa75-mlE25v9mdSjOZk5z9Y_vYDKZKRiSWLm1WyPMKqVm1sCtZECRX8Y-voIcSiyDB5T-vUEOGfyhxmeg7NGFvrp--SH_h818LegYyVxSx5n4rvhgUyQmEZPMRAXIEULT4ydcfqJluIdfbVrI7j8hJ_zpHht5yaIv4ewo9JLA8XHKsBsGXtHK1o9WbSxwASXHTqrQxq1dK9R117-3FR_kHnesezQtAzfjOjNn34TUrcgNdX1V4Sw54XTlwR6Dh53gkBThrcV-nnVGWjnpxmrhHUEDZFUHtQbKGMCZjtvKy8_-3D-2eNbDN71DALkUuV8rUSBjtdts_4nUx7y9JigHNDuolVuviyj4bSlxfZJvQR8XWRQY3esKZIZC-b0VNDzME1Y2WazpbIB1oYBY8GPELebB2jO-6u9UmljAS4Rno9VNqAuXWh6T5c5_fMYsw2kDnjNgI7TpOnFeVWHz6pjVrEtqv2KDp8Xc3L17Em5I9dqVPueMq5aWB9cvq6U-teuLTZB6R37NbIH-k3TXEq5znbFReH1djrD1t6Z9DTYrvrq0a3z9lTOXnX9snOyAWHFcXTeKrdfb8IXHFPdFiLfSST0nruR8w3_IN802aW.DRc5hMfCGr8CZreeDnqpGQ",
             'refreshTokenKey' => "Q011527439580itWQTWhIRztT4o7UGo1S2ees1K8Oo4pjJsFt7",
         'QBORealmID' => "123145985783569",
  'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
));


//$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");


$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

$accessToken = $OAuth2LoginHelper->refreshToken();
$error = $OAuth2LoginHelper->getLastError();
if ($error != null) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
    return;
}
$dataService->updateOAuth2Token($accessToken);

$CompanyInfo = $dataService->getCompanyInfo();
$error = $dataService->getLastError();
if ($error != null) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
} else {
    $nameOfCompany = $CompanyInfo->CompanyName;
    echo "Test for OAuth Complete. Company Name is {$nameOfCompany}. Returned response body:\n\n";
    $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($CompanyInfo, $somevalue);
    echo $xmlBody . "\n";
}

/*

Example output:

Account[0]: Travel Meals
     * Id: NG:42315
     * AccountType: Expense
     * AccountSubType:

Account[1]: COGs
     * Id: NG:40450
     * AccountType: Cost of Goods Sold
     * AccountSubType:

...

*/
 ?>
