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
         'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..hvTQ5ZBtBOUHmOpcB7nC-A.AsusVbns191_9yCkcCQDNSgPsGy4ZWcuxdHcAfNEaDVR3hpELxcNJoBrLvtwdPumbgXPT1Ak_1b6u4tlv93HDmlbc0_O5uu3vYdgos1yMA-D3Eycv636uHRwcjsqlkv9tUSJP7OGK0F2T19sx6wMb7cFplLpUp_-WOgCtmd7LdpkN4Vp4cNT1r8X76kOE9XWV7nFzEQgsy4AJmVwKQODuaq1zY5lmVeKneTr5Onp1l2bKsbbyKJkGz6Ic46zOP8GgYtKJjcPSBIqJD-uG4HlwjFH4b-pFaanjNoJ8L9AztQdoepxhf7yYrvCHovoFvc7QbRA8lofl5GnHZVfeo6HPLhWQWMbuldoX8q89Psx0YJ46zTQdZ_UGaNhkA_Ec6Pd9FLhSQjotbilmkjhFyySGAyefPM969iMa7BG0-Y-ifvNd8onC3k9d4F0FPR5iqqDsaQmk0suzdD7_vLMsIIW2TC6grkuliZEL_jOHPgE9nEHDcqfjkKXMWXXD6hewSWQh92A9yoq2m9_aBa0dBq_cUOhTHdbxkNdlT2LnHAzB5vFOcbJLnTAGwA3IVIX2eq3MTn3W487mj0WJfP5xqWtFbIpTvEHHVS1XrhKMFdjRpJG_CvW5fHEjUcPC4cPh0YCbEMlPIoSL8LM9XhhKZyeN_0taiJl_w12T0QhtDDACMyc4JizKNeiEM5wBjz5t5Oq.41cywZD8fm48GypXo5Aing",
         'refreshTokenKey' => 'Q011527239671V2mZmda0bKvf4a4Mh7C0e8nTReVOI0kmLKGjp',
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
