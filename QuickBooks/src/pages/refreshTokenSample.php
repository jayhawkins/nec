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
         'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..j_JnY_hCsAPxvL_gr51pKw.PlmdIn1vTE-EB15FBlczoeqXP6XRWVpaoEVeq-TavZAMoYtv4dB6G6N2R9_jW83ecgOkYSpbGP7moZgWfmwVyU5SWn4_tRJlTGzmsLwwjgkjpWc4aEBl3_0xntiePSQOBZl5NqCEJYL82QG48y1V8xHHwCI81g5gGUxFg88ZwrpyJyzhiKkNZeSQbGh09Ik-6aV4zVbuAQUaTjPVi9eZnRhop19KRikbk233j-lSVDrol1c7W4fLccXghHSllEb8mY-ju6AQXxiJKMtT9zehFXdmBwIQVnvU1R538za1ad3jp55GCTi4qE-svUznEnjJ9umnKUA6dJS9fTvJq1c1Q6rofLCCx0ORofXOHZ247TfqwyjJOxUU3a-bqQmtOEj7jWDwtw8M-5Ayt2gIZWOQEJraYLI1DK91jRIGvABpSd9G4wqRN6kqc6o1rnAu8J-YAav9YwDbs_xeagmoUE5mHvkwX79Zl0YBPVQYMcF5iiaZ2RKdZkDL4urgo_tDPHECKGyAqWRyc0s_CWN8JP4q5oC5fonps_M3aBXke_hLCkekQvkFp7r9FuZ1M79nT94u0n5x9bQF3ZJfte-Gwc7hNGYHPdl5pBfF3-ujb6aSQbMTAeMOrpqVNEw7fyzPY1g66CBfAL-0TNa_J7f5UcN949GNXfW2ejbToW3TvcII0KU2OMDAUchtQW4Tej7ou5hd.8CfRT-gGSE5qiZvWLaShbw",
         'refreshTokenKey' => 'Q011527355634fFmbiEHSiomgcIYhgkd4C2gkLJHajvwq8PbSD',
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
