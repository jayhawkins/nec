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
    'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..ZzdmZi1Dbxm4C-UxGnU65A.oQdIxMAuLuX26eDkLe3l_5IIh7hy4I2PRu5dGEID7HRPaUvi5c4uy8FU3ykKzz6OXAxQBOmc0Mi2vdU1aGqO8ygmcFXOgoHaPftotNxzipq6f0kBSB0FCnv9-kN46jW7whuNa_eJBOzzQqn20n8LKq5ORo29RqMxM6qA4eJ7EGyY9lCia5wyPlM44csCbGGoL4BbwD8i3oQM23qgvSWy07X30pUFFv2neA_DqfsArrM-m8vMHm4hyuiQbpU1Ahnb4zJcFlQRE3dHYS7ZfE4XrwsWEsKa6HDodxFDY52yDd5GUqNNDMljnUXjUF1yMytvCethe1K0T6PXG9S2ekQWhf2dRsmk9pF82St938xNKgc-We4Mc7Uu2caeIUuDHlOdR6XszLnD_804WbdStDVORcjMQDRV1ij-cE8o3N9oOM8ZakyivnyUGYnXM_UUt0YSMFUiV5SX--O2J73yTLApMpFgRRuurVRbQHQ1X_Qc8BWODn8gjmdYVc71IUUyIi4CZA6E6DK_T0QUTdChcget4Oq-S7sNz5ijMNnX3y2FF3xItJ4aHehkZy1uq5ncLDEmU0-4-RO1voJ9wFG2wl2J-slWKcNi0krwXnzdCx99jm5WbzS1BwnQQnnGl8VbHY0Rp_tX74-x3j7ymdN_QIV3lVUHATDK2ogIqCbfVfapvi9yu5CwVOQToWxipcsWc13Y.ZJoG7VRPh17F7jxRmgIJ3Q",
    'refreshTokenKey' => "Q011528393690gmnRRyqs04lrJ5vj3wcdKLVitLlwsAqDK3888",
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

print_r($accessToken);
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
