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
         'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..e-IiSX8UTrMsuFyNB2vmUA.YKfO2160BXS9Z9tMyQcwpC9x8rOHR9F67U3Scl_NQD289JUpgqx--jJGLTLvnhpxDk5CS7DJzCiRxLLVNJXD2SsIWstdy_xW5Gcr8yTBx7vpkX63u5IMoJdTys1A9LWkqJqpqTWr864z-MprTQAT7Y26IjCijR-mXEjBNsP1P-8htJAx_bSQ1NbHGVk2J4Cw5lWHQiUOZOEONQgUO3nKUkc_CZPsQayplOn1Zq_31lz-pcZowEWiMSICPt8BcvcgH9xLzDRCubFI-Ng4UboVdVSFjB7iaDhJy-UXSVB7AjAoVtpx-6s8A0I57GB8maBMeDB3quJ13c8JFzYbsAMcmJizE0ubFde2pzngnYo5Gx67QZ4V0DEStzxpIX6Py1L37NmrMQzqwBrOlq3KXy3-gQi67hJuez-a3uJtjm3bzw9Ud6lE1ELFuWXMdaIfbF_AAM72S5s3k3fgWfrg9bpgH7DxiN5BjtsHVpuWp65isj7Mj0wVseRrIDZA4pzGzv2V2p55vhFMeIhm_mjSeaCSTqdzNsNDnUlWyESkETuAkzLEDD2EGGRR1uamCmwDXmOIRawFEX2o2qj3XGbePzrtL7IRuhPREDeandpmwrvIaHPWysKLEgLrkFJ0arEkB0PD1c6BzK3JJcVlVTQfgej8QU6yzxhzfBB9i1qA8RGWzu3YmbfaVj6YV5ZvJEYZz6ij.c9DsaUVZFhV2NQnKAiN0Og",
             'refreshTokenKey' => "L011528043040CnP7tXBsJ0l1WGzmDauGC6ri4KtRS9AejIgbO",
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
