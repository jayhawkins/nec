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
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..wNwH1l5VLmh23Ku981d8SA.OEEj8OPBnrBAOLQjF9QyQvcNJ_trY4dKlaHADN85e0FXddZS9oTPVNINSX2QQRDHnvzP6Kdl6FaReiB6nZcFz93prqkZVvZcULKFhzYyhryWeder1UQdioLe25HT_TfqItxO_OQ2jqJwAIrBYt2VMLNAwcro_Eu_vxc0BPAA8AsJMaUdBInXmZkD2HTDJt3V2CNhVxdAD-uWxOfgocwd3XxZGpoacFY86J9WZGJxsuegqIt5tXLRlz0Klnf_KjT8qN27awWx971gY8yerh23ME1Hi3BidBqf9FvA9E1Iq9YGlvEnMKdu7VJN_gDV3nBk9nHgbAyz8VZ3-rzzmM42Q1Og8UbqHiL98t3S8lKWbegeiOR7PpSyJGmhkqgMA-rumOs6AlRAcDdkZsZVMF2s15LwNvml0sm9-CHcVYy6MX7ChHKDOg3-GtCgsOrA6dWtuPXfzEtpjvHMIzrixXmseIxDIOaOVTN5tlIudfOpSXxAg9c4AqFuMrwro-IL3KYBGSoN2U1_gv9q_RLhhoHqlL1bLvi6445-SzPjLi5NEMwDY4ojDwW2ZH--M_PN7wcJYVBEwkQdJeF-YPxbllh8XNC0PsCuirMfDRyZw7hwZ0-ODCnrH4_5mIbj8MAb-jgzMTGO4wOShIJuvD8TBj85hUGD7EQdk3VQc96-IRO2pFXcvElx-1cbNtQeILjDWyul.YEo__R5_NzqG2fdQVZi7dw",
             'refreshTokenKey' => "L011527709793Dx94leg6BsFkMc188WsoVcLEqJfWcYpY7gzMK",
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
