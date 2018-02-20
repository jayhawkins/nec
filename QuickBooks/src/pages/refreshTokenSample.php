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
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..x4x0QPk8x75jxIFMXa886w.rI-BwixcIKsTaFaa6nkJdbnAFqeXlnPDrFk9EMxpRJRPBdVoxbTt0zXLp1eybRyMnou6jDFG7GQRxk9XF_mEdv34jIyTM6jC-3sBeY_3X9YoxTknSwllQqiqHQYgw6alVJBkb_qrbEs63Z847cHFSc7w5k678UWAet3am47-qhzOekta-t8KyX5IsbWHuQ695_hKrwGCowEQfBaQmBnvM0CvF_EYLFguai61loC6C2Q28OSMp9MsfgbIJPHt2B0fdc25hPB4d3otKs_w3vJgKQACnUBhh72R9gH3c6kTrrU17rszRCmJSSxP17Q3a7ZK-jKf2gd5OCteni9Fs4_iExUDMT7owPsnkPgSiBa1IPoT6nVX1L85HBryDYOhBEkPU0YPqB1Dc3hBqtEKu4dT94JOdZT5IxJdW7lvr9BiJfVUkwLjSWIj9ODX7vj0-oqpsqixJB4oquf9oJVG-Q9lXWxeOcrmUZt1EskfOlhEcwCpXXXR62_ON0svZ9OoKrsYlAFJdXHKva4yVxusNYQj-1r_bXovuQsXnI5OtgQKJjsBQkX7bxA8NeIIgj8bTiU5rFaZPAqeC5D-_3xojbAu--ZyqGHERx7uerJsSaoXYam2RUhVXosBV8a6MS3x-AcA-XoqaLAweOyDiTuqpFskJw_oJ-ZyhKrARRIqjFUhgFvBcMxqxoDGWkSI_GbwQW31.RfRMBO5JPTVE149xLKDglw",
             'refreshTokenKey' => "L011527882801dhYayH5fuOAxyivAIIJHxTKAoIg7U2pPANVh2",
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
