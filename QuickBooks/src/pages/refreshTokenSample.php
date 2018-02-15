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
         'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..ITW5E3vqInW5MNwJBbqGcw.bA5DpR6ErMN5G99xTEG4gVXgdIFGI--SQrOiHDpzXLDyeP6MPMEQm850S3j439huD5KgdE2DDTf441jM1-J1jx2SwMTDKOrS4pT6EPj5YuP-vn7wRzIIVPRsDZBhXlMFnGPzifx2roVAj1y5plMHMZ1OLXfEhi_YGEwovzdavDkkshaJ7lJdMExu3PYt89EWGHGvefFLNx8U1B76GrQpm1a2rcI7xObVwGafdSsa0iVHnArOiC3NErnu6jBJy8xAxqosdsydmc84tSKhSWuq2RLtlrhqCnCfA2wVzg9w1c5l8-WBRt6BY2ljZs_GmR0Qc3sI5e9mlcGn8Uzl5q7xgB6ljmNAzmkpYNvwWxX6nbw6x47jXWTYsb5UPIcJ8qmnV3-BnCdYpSbJ4osUDtYpnW5Nr4PXAOnaC1WOF__FX0d1cm8zdgFsfVyiqW1CW3yUCNntVlYZt1nSh8CeXLTbetm4vNUXzJSXpOF-1wvduawTfksscPL0_1NVEB01f4pTwKKlbb8DG75u6ZTlz1giknob8pphO7MwtJegKStkmzQYeqwEsgCWv7YykDxoC2TuldUj1BYyMwsEFl1ExJb1yTxl1N1MnKuV5CmhdB85emr6a8I3wvZneCKknU4aRh72Gj2_U8wW-AJ73ghMnhi3j9I9q4VgO2_302Ck8V3w5vT7zKPR2ExZCADgId7TVnls.Dsw1tAFJm89R_dg4R7lDdw",
             'refreshTokenKey' => 'Q011527438107CNPRG7G95W9DNU4tXwESsQ6Vmp9Gw8K6nSgO4',
         'QBORealmID' => "123145985783569",
  'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
));


$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");


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
