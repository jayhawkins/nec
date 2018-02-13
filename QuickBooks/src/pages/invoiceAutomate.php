<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
error_reporting(E_ALL);
ini_set('display_errors', 1);


include('../config.php');
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;



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

    //echo $row['line_id'] .' from '.$row['originationCity'] . " to " . $row['originationState'] . " for ". $row['name'] ." ". $row['address1'] ." ". $row['city'] ." ". $row['state'] ." ".$row['zip'] . " Cost:". $row['cost']. " <br>" ; 

    $customer['origin_city'] = $row['originationCity'];
    $customer['origin_state'] = $row['originationState'];
    
    
    $customer['destination_city'] = $row['destinationCity'];
    $customer['destination_state'] = $row['destinationState'];
    
    
    $customer['description'] = $customer['origin_city'].", ".$customer['origin_state']." to ".$customer['destination_city'].", ".$customer['destination_state']; 
            
            
    $customer['customer_fname'] = $row['firstName'];
    $customer['customer_lname'] = $row['lastName'];
    $customer['customer_name'] = $row['name'];
    $customer['customer_contact'] = $customer['customer_fname'] + ' ' + $customer['customer_lname'];
    $customer['customer_address'] = $row['address1'];
    $customer['customer_city'] = $row['city'];
    $customer['customer_state'] = $row['state'];
    $customer['customer_zip'] = $row['zip'];
    $customer['customer_phone'] = $row['primaryPhone'];
    $customer['customer_email'] = $row['emailAddress'];
    $customer['cid'] = $row['line_id'];
    $customer['cost'] = $row['cost'];
    
    //print_r($customer);
    //echo '<hr>';
   createCustomerInvoice($customer);
    
    
    
}

exit();




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

//var_dump($resultingCustomerObj);



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


function createCustomerInvoice(Array $cust){
    
   print_r($cust);

    
    //query for cutomer// Prep Data Services
$dataService = DataService::Configure(array(
       'auth_mode' => 'oauth2',
         'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
         'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
         'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..MYCjL9qsSTiEUkAkNa2xww.8yIxASMsMf_Ja0KrKdcsl3uSxvIAeDDZSL1_7UVI5wnC-WjF296B7MkgS1wVGCOf3B__gM0AZod906l3k8Xzi2VkuxAJdk62SCfH_F-VH7ZqMOA4mJ6EoI-7ModHVioBhHbeIn8SymnEdwRYZaPvjKH992tarI5975Zd5p9LL5K2xLh6paSmmeyFz4hqxvIbtJBRhTG9qqd-dzpCRq8H0hLQcNG1J76Tj3rhHiCUGn37oJ-YNsHFFbcJugJCxUjeMZn3-dsyL75x1heu-NZ5E00RQGNwhj1O2cRkwy_rOqNrDgkMfIkV0qdX6S5VHNraElrCTFcFXJbveGw_nYZYlMloQqUhOYcG5G-tERHxjK3c4hxkW-l829xgxdcD08E1zhfsiPZEqRRCqS4qs4v93hDQHG_oHvFOkNncz9_rb7DC5AcbvqWV0WXuCOyxnB8a5iCHG0EMBbnZXyuighZ4rpQXfsErS9AyZ8ie3gAaaALgk51oyiDdmbWgVKJe2l4nRfJJw2gHxpw9E7EC9Cq0dF7NLz0O3qFnY54pExA79hWeUl6pjyGWEYbt8IG_UIaeYPph37Z2SGaDkpUAPHChTzhZLk-pnSXwBtiTbjJLAJ7NLhjIr5DHKKa73S8u417K-zSmGAt1S5VVwhQFuoXrQ0rG52plaE-jBPqhWscD2nCwdZWJl7HUgTGHT0mCpqZl.Qw3m05El6lMYHC98z1_0Gg",
         'refreshTokenKey' => 'Q011527248404CPAzFuTBTfBq3PpXD20z9GYqacJYxVbkVID9L',
         'QBORealmID' => "123145985783569",
         'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
));

$found_customer_id = 0;


$entities = $dataService->Query("SELECT * FROM Customer");
$error = $dataService->getLastError();
    if ($error != null) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
        exit();
    }


// Echo some formatted output
$i = 0;
foreach($entities as $oneCustomer)
{
	//echo $oneCustomer->DisplayName;
        //echo '<hr>';
    
    if ($cust['customer_name']==$oneCustomer->DisplayName){
        $customer_found = TRUE;
        $found_customer_id = $oneCustomer->Id;
        //echo $vendorid;
        //exit();
    }
    
   
	$i++;
}


echo $found_customer_id;
  

if ($found_customer_id==0){
    //create new customer
    
    $dataService = DataService::Configure(array(
           'auth_mode' => 'oauth2',
         'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
         'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
          'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..MYCjL9qsSTiEUkAkNa2xww.8yIxASMsMf_Ja0KrKdcsl3uSxvIAeDDZSL1_7UVI5wnC-WjF296B7MkgS1wVGCOf3B__gM0AZod906l3k8Xzi2VkuxAJdk62SCfH_F-VH7ZqMOA4mJ6EoI-7ModHVioBhHbeIn8SymnEdwRYZaPvjKH992tarI5975Zd5p9LL5K2xLh6paSmmeyFz4hqxvIbtJBRhTG9qqd-dzpCRq8H0hLQcNG1J76Tj3rhHiCUGn37oJ-YNsHFFbcJugJCxUjeMZn3-dsyL75x1heu-NZ5E00RQGNwhj1O2cRkwy_rOqNrDgkMfIkV0qdX6S5VHNraElrCTFcFXJbveGw_nYZYlMloQqUhOYcG5G-tERHxjK3c4hxkW-l829xgxdcD08E1zhfsiPZEqRRCqS4qs4v93hDQHG_oHvFOkNncz9_rb7DC5AcbvqWV0WXuCOyxnB8a5iCHG0EMBbnZXyuighZ4rpQXfsErS9AyZ8ie3gAaaALgk51oyiDdmbWgVKJe2l4nRfJJw2gHxpw9E7EC9Cq0dF7NLz0O3qFnY54pExA79hWeUl6pjyGWEYbt8IG_UIaeYPph37Z2SGaDkpUAPHChTzhZLk-pnSXwBtiTbjJLAJ7NLhjIr5DHKKa73S8u417K-zSmGAt1S5VVwhQFuoXrQ0rG52plaE-jBPqhWscD2nCwdZWJl7HUgTGHT0mCpqZl.Qw3m05El6lMYHC98z1_0Gg",
         'refreshTokenKey' => 'Q011527248404CPAzFuTBTfBq3PpXD20z9GYqacJYxVbkVID9L',
         'QBORealmID' => "123145985783569",
         'baseUrl' => "development"
));
    
            // Add a customer
            $customerObj = Customer::create([
              "BillAddr" => [
                 "Line1"=>  $cust['customer_address'],
                 "City"=>  $cust['customer_city'],
                 "Country"=>  "USA",
                 "CountrySubDivisionCode"=>  $cust['customer_state'],
                 "PostalCode"=>  $cust['customer_zip']
             ],
             "Notes" =>  "",
             "GivenName"=>  $cust['customer_fname'],
             "FamilyName"=>  $cust['customer_lname'],
             "FullyQualifiedName"=>  $cust['customer_contact'],
             "CompanyName"=>  $cust['customer_name'],
             "DisplayName"=>  $cust['customer_name'],
             "PrimaryPhone"=>  [
                 "FreeFormNumber"=>  $cust['customer_phone']
             ],
             "PrimaryEmailAddr"=>  [
                 "Address" => $cust['customer_email']
             ]
            ]);
            $resultingCustomerObj = $dataService->Add($customerObj);
            $error = $dataService->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
            } else {
                $found_customer_id = $resultingCustomerObj->Id;
                //print_r($resultingCustomerObj);
            }
    
    
}

 echo $found_customer_id;
    echo '<hr>';
    //exit();


           //create invoice
    
 $dataService = DataService::Configure(array(
          'auth_mode' => 'oauth2',
         'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
         'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
          'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..MYCjL9qsSTiEUkAkNa2xww.8yIxASMsMf_Ja0KrKdcsl3uSxvIAeDDZSL1_7UVI5wnC-WjF296B7MkgS1wVGCOf3B__gM0AZod906l3k8Xzi2VkuxAJdk62SCfH_F-VH7ZqMOA4mJ6EoI-7ModHVioBhHbeIn8SymnEdwRYZaPvjKH992tarI5975Zd5p9LL5K2xLh6paSmmeyFz4hqxvIbtJBRhTG9qqd-dzpCRq8H0hLQcNG1J76Tj3rhHiCUGn37oJ-YNsHFFbcJugJCxUjeMZn3-dsyL75x1heu-NZ5E00RQGNwhj1O2cRkwy_rOqNrDgkMfIkV0qdX6S5VHNraElrCTFcFXJbveGw_nYZYlMloQqUhOYcG5G-tERHxjK3c4hxkW-l829xgxdcD08E1zhfsiPZEqRRCqS4qs4v93hDQHG_oHvFOkNncz9_rb7DC5AcbvqWV0WXuCOyxnB8a5iCHG0EMBbnZXyuighZ4rpQXfsErS9AyZ8ie3gAaaALgk51oyiDdmbWgVKJe2l4nRfJJw2gHxpw9E7EC9Cq0dF7NLz0O3qFnY54pExA79hWeUl6pjyGWEYbt8IG_UIaeYPph37Z2SGaDkpUAPHChTzhZLk-pnSXwBtiTbjJLAJ7NLhjIr5DHKKa73S8u417K-zSmGAt1S5VVwhQFuoXrQ0rG52plaE-jBPqhWscD2nCwdZWJl7HUgTGHT0mCpqZl.Qw3m05El6lMYHC98z1_0Gg",
         'refreshTokenKey' => 'Q011527248404CPAzFuTBTfBq3PpXD20z9GYqacJYxVbkVID9L',
         'QBORealmID' => "123145985783569",
         'baseUrl' => "development"
));       
//$dataService->throwExceptionOnError(true);
//Add a new Invoice
$theResourceObj = Invoice::create([
     "Line" => [
   [
     "Amount" => floatval($cust['cost']),
     "DetailType" => "SalesItemLineDetail",
       "Description" =>  $cust['description'],
     "SalesItemLineDetail" => [
       "ItemRef" => [
         "value" => 1,
         "name" => "Hours"
        ]
      ]
      ]
    ],
"CustomerRef"=> [
  "value"=> $found_customer_id
],
      "BillEmail" => [
            "Address" => $cust['customer_email']
      ],
      "BillEmailCc" => [
            "Address" => "ygtandoh@gmail.com"
      ]//,
      //"BillEmailBcc" => [
        //    "Address" => "v@intuit.com"
      //]
]);
$resultingObj = $dataService->Add($theResourceObj);


$error = $dataService->getLastError();
if ($error != null) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
    $invoice_id = $resultingObj->Id;
    echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
    //$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
    //echo $xmlBody . "\n";
}

$servername = "45.55.1.81";
$username = "nec_qa";
$password = "Yellow10!";
$dbname = "nec";

// Create connection
$conn = new mysqli("45.55.1.81", "nec_qa", "Yellow10!", "nec");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$id = $cust['cid'];

$sql = "UPDATE approved_pod SET hasBeenInvoiced=1, qbInvoiceNumber ='".$invoice_id."' WHERE id=$id";
echo $sql;
if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
exit();



    //update db
    
    
}


   
?>
