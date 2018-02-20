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
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..K0aeb7Q0MaDZYPwJl_qFvw.ekD796DWs5_1Umog1rkhgJERIblmUzvte_59Km5aavkiuJL1Y6FW8Qy6GEiMUmPXD8pe4Gb29meiCAVYmwjVjXELQWLOLPcAXTwA4WflbEssrXKdOSs4bfB2SBG7HWENct9-nHrQj20ls0cvl3CXHWTbh1JH5ChY3fWSSu3euxYzgZxnGQTlPktNmPdsQev_CadeknHbQ3BB3FyqowqRoGmvkiyaSevx2sazxcDSPehVt62QbaE-EqqBHhZOyuM3tuh-dwczSBWnIwbusIqEwutlnoQev77vGT9uhILP-h7a2WqI-KUnrqJn85DaynMfX4ohGoWF3xPagOyl88H2FPk4mbtqeRkeMGVX0Kets3-RIdjPJ139nbvAu7Kc8kRY8hGci9DB_3uy7piqarrJEmFAe5poKZmQVGUj1QXShSh9d0cBm0XNQMdWY2CmKAH9UibRveO-go3kxC57l-Zei-9JKyHZY9AxTVtNcxF0pQc5eRKV0v_YWFlbP1J_XSMpRY_HCns0xbpf1Z0TUgkr0QOunW4X2Jd8xiTs7dGgbVPHzZceUXgTM5uzxoJ3rZh4Z0m5yCVt3DNU5-MhAeCXodzf3-EBucoYkYC5zCqM31ClzzmSmFskGNdF0QLzGMjJPD5siitxYfI-F1MZK29n-2mcaydEANyXn3fT2q3B802Ni6a0S80m_N4XNo3ldyO2.NtHbrd8-sBe2GskJQ2kSwg",
             'refreshTokenKey' => "L011527862468O6PiLiFOHzKHsUOw9cMFziFoOUCrH1vEjvobP",
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
