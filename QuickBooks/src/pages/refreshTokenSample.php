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
    'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..ZbE_cCRvpC1Jv16WNWI1gw.8PK-TS5MM1XRG2GS_Xh_ztSaDMR-UDPFY9uqQAV1lm0_LnQxe_N9AuIFHI0YbabJLMgzsyE1jzpLeV3C09OnoVDkArf_gErfhk92FTHIAouiWDjBTqDJCNC6vG11fb0lUxlcNtTFqHIMe6HkvC7odJnsF3qeLn4P3xvS0wtjpAiQwg5-AWrzm_4lybAPYoBKjU8Ss610qFioRodGB3cxe_mAmfnI-38Pj7I6D4gRgkOGlU0V9Bb_WiUG35ODhy_0sGcBRf6yjXdDm8ZuRmZNMafVP8DgupQEUtLMw9t2mxQECsTHGJ3Z8s6tTfxQV--1ok2uD_pzwc35YUHUs7j0wFEanFBEyQfqEJBoHLvkTa8_bD8sTTdx23nYtGzKq-hj_fAxXdWIJyPfHqGj45GE5KqvHGQTjALQ_BB2mqknOGsWjBAYAdjmpxFQEC3Q2dtGleRNXc7AFtNzOgr1o7_FtKZZxO0zVf6d8xO0qIAjGz-vYOBbM4bgaQ0tT-rrPxvLHIHw4VHO2CoqtcCbP0OXA58K3BmcsmwYTIeY3uq_-H6LdCgoCWVrioZxIMJoaw3gRcCLzrrFE2sP-ZIzP-JaxGo2UbY6PAxf7HG7KHc49LNBBEUmZux2rJRcvn8ZKaNEq4fHyWCz6aVQ_caTivkASuXogybQqQo0jgJqt6-mZfGvv4UKjLBzcS-DTpXvvtma.y9bsUKtWaPxDwHh1qqDieg",
    'refreshTokenKey' => "L011528129387zcnvZehMd7sUoxqBLXInaqONHEP81FDDSZCoC",
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

print_r($accessToken->accessTokenKey);
print_r("<hr>");

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
