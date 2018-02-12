<?php
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
         'accessTokenKey' =>  "Q011518454042iEc2Y9p2EccDxgT912tSoDGMEzHq4ak57sPfn",
         'refreshTokenKey' => '"eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..BQHzO8guehposne17tDweg.yuUkeci4FL6WhKL5_fvCSP8FYVfy1mZF_Qrl9mW20wKYHojKwpNfpXyGKUEe8UUudqFaak47YAS6IKGPJiJz9W6oGqByYpGwr1xDP8WwuRlEWXOigqcJC9QMXgHSD5Ld7-lZ68dnIeCwMKY2k7-fR7qv9IYp790jpOwebSgGbhK3AmnUgIBr_Y885OHsJbaRHGmIQGdhXV6IQHSoHBU7-lrLHruJiOB50KzpGkn6gNIZHTBECCm3X4wqXWMrWjyOJ56dZKqwiCKCoWA-RbhWuunbiln9EeGxK0qB7IZPd1Ozcc0emMCWUvrKTpcFHyAzL5F-qJtlhFDIlyImyT678Ya0esM8p0sdVuKQsOHGN2nTH1zhuYD0vYVHZEL_NJXHV_W_c8MY-sE36yelL8gI4G9UAs6iJEp0mV0-E8FvV6bCwScyLjskIu7GbdYaCl1wolKoDLKO6xrsbM65np7OgU1zTlGWDRwQ8NO9zuCzcGo2CkdCDf8sRKepid8s_S1HJ18JT69qpAjYUiC5Cc2YqJD-BOr26cwHqLAjPo1oA2sLAT0XOKA5CF8gwNuJ-tdAJ6J9VgvE_h-7XfOrfTFFrBqgRUl013ERd229PSdcyZFU92J7QA-4JpC50iBRiAS3QpDYIPrC4ZEprHMdLT1mnKDM88x34k9P06PTZF2BjQdBKECdyS7s4nNwS_jubITX.aOcohUp4tavS4Ils4Jug5g',
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
