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
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..CyZ-_Ue97Ufy4IC5h5Xxqg.Vvjo_ImHr7dLLsKCELuaF_-8Ex4sxVGWUrWaARjNp-0uz_N7DiEYHNy32dBB-ZkV5060ixYo_EVYtJGaLMoTxoe-SHFkw1j2D9wMuIITv4Riy1k96ZHI4etG8V_83oXk7yqlO8km9vI_t18vjMurTx1oDmkpMUCprvKnV6TXedPgI5YaGJURxqccH81vc1i7Iy_zWZ9cnYBn6DtHKZDt-kfF5TFFFQAPl8W_kk6k5z91lgez2NV_bNlyzdmgB0V6EiT8p6xvJqX2cNAp_Q5yjeKRQh_yK4kOyypwevSrRRnpSSJZ_onZ3WAQOZPbZyxzjNg1W-QCfRDVpnWlxphZZ9oV-k1vUwDWKYnn0T2KTvmeNqJTQ8TV4vPTdyhZyhLC9n4k-QO9ZBDpTg_W9msSROOSvNDf1FnkLvviU2jN9ePf3wN7n8RX4Ir85j8ktkgUsskN3OB7iM2HJOJTdcO-uqH56Ls16bgsBOm_Qk0vmb8GWmHlS3_T4abK8MU26m6wNrF6V08m80bYKUsl6UlbtsP4GWexz8QYngy-FvU6Upu89u2_9HYOvV6QRboTjygX5VpUuadvZyIuyIy9I478mQPE4-rSCNcLtaeF-RhKjUZeTs7jeAYCXO2nsmRIR6fvGRji-VdRGVdyEjJ9t6NhhtRaEEO_8RQlSPb1cixd37Ady_t2UzvCom5wVUVMgG64.Bl9KCNT-N6Ve0Yh7RtvuWw",
             'refreshTokenKey' => "Q011527441086eioY9H0tJqQBkFwLTxyKsQ9ZFPps9SRNUZJjI",
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
