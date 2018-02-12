<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
error_reporting(E_ALL ^ E_WARNING); 
ini_set('display_errors', 1);


include('../config.php');

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;

//echo 'Hello World';
//exit();

// Prep Data Services
/*
$dataService = DataService::Configure(array(
       'auth_mode' => 'oauth1',
         'consumerKey' => "qyprdUSoVpIHrtBp0eDMTHGz8UXuSz",
         'consumerSecret' => "TKKBfdlU1I1GEqB9P3AZlybdC8YxW5qFSbuShkG7",
         'accessTokenKey' => "qyprdxUakMagH93t01x1Z5wmIfIy3OiZcTqzI2EALXqhOaGE",
         'accessTokenSecret' => "QqQhCSvDgMvnJmoMbXI5d9TIVj9wKU1w4yIEaFNC",
         'QBORealmID' => "193514340994122",
         'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
));

*/
$dataService = DataService::Configure(array(
       'auth_mode' => 'oauth2',
         'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
         'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
         'accessTokenKey' =>  "Q011518454042iEc2Y9p2EccDxgT912tSoDGMEzHq4ak57sPfn",
         'refreshTokenKey' => 'Q011527180159s7nYP5Sx9GZyHaMPBA2qmTyK78BgQTvIBi0Dt',
         'QBORealmID' => "123145985783569",
         'baseUrl' => "development"
));

//$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");


// Add a customer
$customerObj = Customer::create([
  "BillAddr" => [
     "Line1"=>  "123 Main Ave",
     "City"=>  "Mountain View",
     "Country"=>  "USA",
     "CountrySubDivisionCode"=>  "CA",
     "PostalCode"=>  "94042"
 ],
 "Notes" =>  "Test 2",
 "Title"=>  "Mr",
 "GivenName"=>  "Dennis",
 "MiddleName"=>  "Michael",
 "FamilyName"=>  "Smith",
 "Suffix"=>  "Jr",
 "FullyQualifiedName"=>  "Dennis Smith",
 "CompanyName"=>  "Dubtel",
 "DisplayName"=>  "Dubtel",
 "PrimaryPhone"=>  [
     "FreeFormNumber"=>  "(513) 418-3718"
 ],
 "PrimaryEmailAddr"=>  [
     "Address" => "ygtandoh@gmail.com"
 ]
]);
$resultingCustomerObj = $dataService->Add($customerObj);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
} else {
    var_dump($resultingCustomerObj);
}

var_dump($resultingCustomerObj);

echo "Hello World";
exit();

/*
Created Customer Id=801. Reconstructed response body:

<?xml version="1.0" encoding="UTF-8"?>
<ns0:Customer xmlns:ns0="http://schema.intuit.com/finance/v3">
  <ns0:Id>801</ns0:Id>
  <ns0:SyncToken>0</ns0:SyncToken>
  <ns0:MetaData>
    <ns0:CreateTime>2013-08-05T07:41:45-07:00</ns0:CreateTime>
    <ns0:LastUpdatedTime>2013-08-05T07:41:45-07:00</ns0:LastUpdatedTime>
  </ns0:MetaData>
  <ns0:GivenName>GivenName21574516</ns0:GivenName>
  <ns0:FullyQualifiedName>GivenName21574516</ns0:FullyQualifiedName>
  <ns0:CompanyName>CompanyName426009111</ns0:CompanyName>
  <ns0:DisplayName>GivenName21574516</ns0:DisplayName>
  <ns0:PrintOnCheckName>CompanyName426009111</ns0:PrintOnCheckName>
  <ns0:Active>true</ns0:Active>
  <ns0:Taxable>true</ns0:Taxable>
  <ns0:Job>false</ns0:Job>
  <ns0:BillWithParent>false</ns0:BillWithParent>
  <ns0:Balance>0</ns0:Balance>
  <ns0:BalanceWithJobs>0</ns0:BalanceWithJobs>
  <ns0:PreferredDeliveryMethod>Print</ns0:PreferredDeliveryMethod>
</ns0:Customer>
*/

//db call 

$dbh = mysqli_connect("localhost", "root", "pqlamz", "nec")
     or die ('cannot connect to database because ' . mysqli_connect_error());
   


//select from orders that have not been invoiced
//see joins
   
   //run the query
$loop = mysqli_query($dbh, "SELECT p.id,p.customerID,p.cost, p.orderID, p.orderDetailID,d.originationCity,d.originationState,d.destinationCity,d.destinationState ,e.name,l.address1,l.city,l.state,l.zip FROM nec.approved_pod p join order_details d on p.orderDetailID = d.id join entities e on p.customerID = e.id join locations l on e.id = l.entityID where l.locationTypeID = 1 and p.hasBeenInvoiced = 0")
   or die (mysqli_error($dbh));

while ($row = mysqli_fetch_array($loop))
{
     //echo $row['id'] . " " .echo $row['orderID'] . " " . $row['originationCity'] . " " . $row['originationState'] . " " . $row['destinationCity'] . " " . $row['destinationState'] . " " . $row['name'] . " " . $row['address1']." " . $row['city']." " . $row['state'] ." " . $row['zip'].   "<br/>";

    echo 'from '.$row['originationCity'] . " to " . $row['originationState'] . " for ". $row['name'] ." Cost:". $row['cost']. " <br>" ; 
}
   
?>
