<?php
//header('Content-type: application/xml');
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
require __DIR__ . '/../config.php';
require __DIR__ . '/../../../../nec_config.php';



use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;


$ClientID = ""; 
$ClientSecret = "";
$accessTokenKey = "";
$refreshTokenKey = "";
$QBORealmID = "";
$dbh = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME)
     or die ('cannot connect to database because ' . mysqli_connect_error());


//get quickbooks credentials from db
   //run the query
$qbLoop = mysqli_query($dbh, "select * from quickbooks_authentication where id = 1")
   or die (mysqli_error($dbh));


while($qbRow = mysqli_fetch_array($qbLoop)){
    
    $ClientID = $qbRow['clientID']; 
$ClientSecret = $qbRow['ClientSecret']; 
$accessTokenKey = $qbRow['accessTokenKey']; 
$refreshTokenKey = $qbRow['refreshToken']; 
$QBORealmID =  $qbRow['realmID']; 
    
}

$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => $ClientID,
    'ClientSecret' => $ClientSecret,
    'accessTokenKey' =>  $accessTokenKey,
    'refreshTokenKey' => $refreshTokenKey,
    'QBORealmID' => $QBORealmID,
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
else{
    
    // Create connection
    $conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $newAccessTokenKey = $accessToken->getAccessToken();
    $newRefreshTokenKey = $accessToken->getRefreshToken();
    

    $sql = "UPDATE quickbooks_authentication SET accessTokenKey='{$newAccessTokenKey}', refreshToken ='".$newRefreshTokenKey."', createdAt=NOW() WHERE id=1";
    echo $sql;
    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $conn->close();
}
/*
$dataService->updateOAuth2Token($accessToken);

print_r($accessToken->getRefreshToken());
print_r("<hr>");
print_r($accessToken->getAccessToken());
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
