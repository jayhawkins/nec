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
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..Ce32qXYv-kLgXGmkWjstpg.H1fa-x4Ax63i4mZsCf_N_WRy-bIHXfwIOI1TiKpxU4FSECoCqKG4CzYjC5i74qpgDfl-FbOrkBkzkD8ofZ_6rJ4xT2qNxn8m8NrTI6ZC5QIOTNrbLtQZKP9OpkCZIa1ltvg9GPU4IkKRJ7b9L6oynxr-92W6-mGsmBU1DJQJJxXBzVrOgn_ZeyrWOtgmXpdMz10ZLrvbWq6o9aQ3nCb1ylgwe9yBtAA1kOpW77wFPGvZtwwPP_zUt6W17FQ9zU5kL6UixZdPnkaOMYPa-1bnjw_OWmcua6l8VMjLqbkbKu5IuQaHayTAY5zNw_QyxA-sX3tfNukwsIP7zddg73kDOqSyN10i1Clcfl7wCpOsqPgDBG-IGjfH1I9EF-ufEJrYEpuI-YqUMvmplHnDlNwUCYvkfhIJJg_-ih194j0kKhiXsaHKWhuERGHQJnZfqEKKCMjbvYZhHy9vpoIgaPKA0iuDciB-DfUgOJttx-MefrhXL-yMeIV_EKLUnxaJjUzjzE_DiwjVBmLzzgGHY79qy11I_P-4vJGJUGbN96VrotWgZQNP8UbhkBqCGGYXorD7M0iKsFcw1ccvd5CquKYbLX9PU3Sr9ytDc7UMqizTVuRspQwMMDp28Hjs4vFJF8QyOgvMAhOskHCzjGsZH4x8iZ9SvdpuYqMjLVEKnlBYdmWSV2DLNAEjk8EFVCIjvXWI.WCM6NvYn3ag3aRWpb8ra0A",
             'refreshTokenKey' => "L011527876920Kw1fefUpJhPmxFgEGbMXzRCmm28xdM4F78he1",
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
