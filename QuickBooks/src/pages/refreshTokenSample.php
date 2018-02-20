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
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..pWHWcPAb0SRoV-LqT3zS3w.MhNeeZxM846yqKqXYghgnh1HJ2ilW0xeHSlNqZ84jAPgCM3dm8LdsUboA-Y-koN0r7hC8qrJ1MEgLnqnxYqu1F3Ir9LMlvpcWdHmyQLEC_GPpj5cINAvrMFtSLT7ofcQoavVr8eTPpsdsyBrvZ37AZa7Z_eovN1sNxlDJFgJKeioA3dBAwGqgvqW0Ej0b_5uCJdWY6QeRC8FT-KdyXGMgyt1aE26h44tXpd_eziihvTpeGcCRqFBJKXJlZz6ukwJUWWtRb_vK9CBmUG8ULcUvnEZ48mKmPwEszBRkoF5egAA-PO0NdFF1XvvBePlpJvj1AypqQ24IXQ8aPJg5NHKyi7dFG_40klxeScHMnIZxC5mYRNZNbNT4d7P_P6uK4VKL99GifX_SWa94MLclYbVhLho1p1f6B4A44DUjOCr7uge7StuwwuCxH49W2NGXtodgFT6LHOKUz8IjZ7mkac4I8oJOYpSeFi2fBx33W8aEhxnlbrEPkl4FBjvcAmRXzC1sXHCwEuSM33fnFIfb_RltlWoLCwTHF7cMe_wgmRQ17IVxSTMf0bCMlGm0Ah1pllKAEUC0epshL_q-IOpChrtFsp2XUqF2MSSz_h-UrdEKgm30NW7w4voP0utIqBfbxDxx-rK_QrCcgjhlVmO8SBfyTy6A0fiS1TNU2WYHtoECQ3OvcU2-JH6KCcO1L9KBdlH._fBQ0SIlL03GyGioeG97Ew",
             'refreshTokenKey' => "L0115278118481GsGr0RKgm2GE2sGcDGu1XgdSapyxmyAZhVN4",
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
