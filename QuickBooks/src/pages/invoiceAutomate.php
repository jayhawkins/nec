<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
error_reporting(E_ALL);
ini_set('display_errors', 1);


include('../config.php');


//db call 

$dbh = mysqli_connect("45.55.1.81", "nec_qa", "Yellow10!", "nec")
     or die ('cannot connect to database because ' . mysqli_connect_error());
   


//select from orders that have not been invoiced
//see joins
   
   //run the query
$loop = mysqli_query($dbh, "SELECT p.id as line_id,p.customerID,p.cost, p.orderID, p.orderDetailID,c.*,d.originationCity,d.originationState,d.destinationCity,d.destinationState ,e.name,l.address1,l.city,l.state,l.zip FROM nec.approved_pod p join order_details d on p.orderDetailID = d.id join entities e on p.customerID = e.id join locations l on e.id = l.entityID  join contacts c on e.contactID = c.id  where l.locationTypeID = 1 and p.hasBeenInvoiced = 0")
   or die (mysqli_error($dbh));

while ($row = mysqli_fetch_array($loop))
{
     //echo $row['id'] . " " .echo $row['orderID'] . " " . $row['originationCity'] . " " . $row['originationState'] . " " . $row['destinationCity'] . " " . $row['destinationState'] . " " . $row['name'] . " " . $row['address1']." " . $row['city']." " . $row['state'] ." " . $row['zip'].   "<br/>";

    echo $row['line_id'] .' from '.$row['originationCity'] . " to " . $row['originationState'] . " for ". $row['name'] ." ". $row['address1'] ." ". $row['city'] ." ". $row['state'] ." ".$row['zip'] . " Cost:". $row['cost']. " <br>" ; 

    $customer['origin_city'] = $row['originationCity'];
    $customer['origin_state'] = $row['originationState'];
    
    
    $customer['destination_city'] = $row['destinationCity'];
    $customer['destination_state'] = $row['destinationState'];
    
    $customer['customer_name'] = $row['name'];
    $customer['customer_contact'] = $row['firstName'] + ' ' + $row['lastName'];
    $customer['customer_address'] = $row['address1'];
    $customer['customer_city'] = $row['city'];
    $customer['customer_state'] = $row['state'];
    $customer['customer_zip'] = $row['zip'];
    $customer['customer_phone'] = $row['primaryPhone'];
    $customer['customer_email'] = $row['emailAddress'];
    $customer['cid'] = $row['line_id'];
    
    
    print_r($customer);
    
    
    
    
}

exit();

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;



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
         'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..BQHzO8guehposne17tDweg.yuUkeci4FL6WhKL5_fvCSP8FYVfy1mZF_Qrl9mW20wKYHojKwpNfpXyGKUEe8UUudqFaak47YAS6IKGPJiJz9W6oGqByYpGwr1xDP8WwuRlEWXOigqcJC9QMXgHSD5Ld7-lZ68dnIeCwMKY2k7-fR7qv9IYp790jpOwebSgGbhK3AmnUgIBr_Y885OHsJbaRHGmIQGdhXV6IQHSoHBU7-lrLHruJiOB50KzpGkn6gNIZHTBECCm3X4wqXWMrWjyOJ56dZKqwiCKCoWA-RbhWuunbiln9EeGxK0qB7IZPd1Ozcc0emMCWUvrKTpcFHyAzL5F-qJtlhFDIlyImyT678Ya0esM8p0sdVuKQsOHGN2nTH1zhuYD0vYVHZEL_NJXHV_W_c8MY-sE36yelL8gI4G9UAs6iJEp0mV0-E8FvV6bCwScyLjskIu7GbdYaCl1wolKoDLKO6xrsbM65np7OgU1zTlGWDRwQ8NO9zuCzcGo2CkdCDf8sRKepid8s_S1HJ18JT69qpAjYUiC5Cc2YqJD-BOr26cwHqLAjPo1oA2sLAT0XOKA5CF8gwNuJ-tdAJ6J9VgvE_h-7XfOrfTFFrBqgRUl013ERd229PSdcyZFU92J7QA-4JpC50iBRiAS3QpDYIPrC4ZEprHMdLT1mnKDM88x34k9P06PTZF2BjQdBKECdyS7s4nNwS_jubITX.aOcohUp4tavS4Ils4Jug5g",
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
    print_r($resultingCustomerObj);
}

//var_dump($resultingCustomerObj);

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


   
?>
