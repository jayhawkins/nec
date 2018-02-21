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
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..zTHj-fOjXuVoAcUxHU_IwQ.yBuPQ8Wtti1QYkyoJ21n6VJA0MUQTtL_FgpHMOlqxypYoUEZ2ICkHkoKLLj88VhxnKLoHGkUAOvWXCXizhHLDQV60gavMxz7qu19eY8LLRKPyzMdw2LV5lURC6hx6VPJ2zKMxF0vGvng1iCUo6EleTya18BafJ5MZhKUt1iW9J40gCg4RLzMKQQbS2OE9xuhlnMQ9YYbQWX4XEsfEeUmZSxpiwIAxKjbueadcHtFKZqOEFkYisllwaWnzKISk0zqHHjUPaVnLexmmrxnj0rpQznSLilE3JTrXLkGdL682tY55wZFieVEFlNXKfKc4amBuGwtzG_u2GU5QWWy0yZN0D3trCioLbagcmn0-8NlUxO-LCwssAzaJ15Ks1AiTXLbPiVyOvs4TTLqdYlE4uWD5plcWjdJEDASlC-OS2h5mGoOtzueZAJGUw9SYwAtg8xOK4LtPgc2RFeu2lDn5f3G42A75Hmk4oxn3pQ9ZLgzhaJKaoBt4uj5TpqkA53kjHXNtwnjoovjWpfrLHBqrzgXcPNKY57XE5_4zxdPKXKKvQ9o7V4w3xeFnyUXSECSa2sF-2a6JjSWvnAzdOcDxsx77Q4ilNOsIv_yiJdc_0d1sroGvIGULD4YzohglhLupXTKKJS6Y3mDpOIvDm3eCH9GGfW5MzJ9mMY8Ny0yHwszLGvnxXv8Mkgs2OkNf60splA_.B0SVhTJv6aBDgTIF7YB7hQ",
             'refreshTokenKey' => "L0115279329299uo64LeFMxhpMvTmVJ4DG29eW2fGu2ay8XCPm",
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
