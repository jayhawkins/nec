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
    'RedirectURI' => "http://nec.dubtel.com/QuickBooks/src/pages/OAuth2TokenGeneration.php",
  'scope' => "com.intuit.quickbooks.accounting",
  'baseUrl' => "development"
));


$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

$url = $OAuth2LoginHelper->getAuthorizationCodeURL();
//It will return something like:https://b200efd8.ngrok.io/OAuth2_c/OAuth_2/OAuth2PHPExample.php?state=RandomState&code=Q0115106996168Bqap6xVrWS65f2iXDpsePOvB99moLCdcUwHq&realmId=193514538214074
//get the Code and realmID, use for the exchangeAuthorizationCodeForToken
$accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken("eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..5s8B6uBbqgd5abWcx6fc4g.BdQPAYZjj2IlAfZDJJFAUkve5cCN-JER5dkk8q3bt0PPrB36N8QU2mT_n341WXulhdEJRLc-uFz0xx9owENeqrAUYs-axDYv7BORmHxZPksHIdsoDa3PIxquQ0wmXcLnHBvTV2LPNv3AZlT2Ehzgz1EZUSpNh7GfJq_OEY13F8jrY1EmYLwPnMC_xBuyJI6aafXo0-O2p4kw_qCaG_zlT9cBaAs2OPQUrWc6083LkBU53nSogfwRt-LBcNZe8fEhxwBesIXhOuugavaVEyYkIzvw8pU357vC8SmO_4x-2ayyRCqg56F8aSvuvT1kuG5aru7iZlEoc1CdPBd_za2k_UZqC2na5n6Xj36gMDfHg3E-6h7xku2-ap2uqMBrLHHYyfTSjZO2gN4sRfJEEbKUJFgTkDB_of3A4o2VjYbuNF1SkLsjS3B-BT8nBcNpSPoLmfFafH34U17iuUYJM_eC5qKctVi3Vj4s13IbU2fPO2FJ2K1eXvqmhZFyrtz6zr28i8iaG-EUL4EO60JOZGnJDaLZPLK-cGY4ksl2pW70oCifiVkBuFFBsOYqtApdENr7pYQ7g8tdPZX-uJSHYeFVf-CsDvGINqJ_MHD8LeFTcv_z0d3yele0Nr7M-g2aMitJxriI9R8DRaAjw71t1WDXptKY_uQ69iE2jGXsqlKUl4T5UNR1NVCOxQTCyu5WIJn9.bbwaSoyfy4XtGPFCbSGcvQ", "123145985783569");
$dataService->updateOAuth2Token($accessToken);
$dataService->throwExceptionOnError(true);
$CompanyInfo = $dataService->getCompanyInfo();
$nameOfCompany = $CompanyInfo->CompanyName;
echo "Test for OAuth Complete. Company Name is {$nameOfCompany}. Returned response body:\n\n";
$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($CompanyInfo, $somevalue);
echo $xmlBody . "\n";

//$result = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken("Q0115103503429HrpsLMzMwNXyd3phqSFStBXsUsEPffiPlvzQ");

/*
$error = $OAuth2LoginHelper->getLastError();
if ($error != null) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
    return;
}

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
