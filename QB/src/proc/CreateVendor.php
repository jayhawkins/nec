<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
include('../config.php');

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Vendor;
use QuickBooksOnline\API\Facades\PurchaseOrder;

// Prep Data Services
$dataService = DataService::Configure(array(
       'auth_mode' => 'oauth1',
         'consumerKey' => "qyprd3v8NFl7W89u9z1rvgIsxSu6zo",
         'consumerSecret' => "NQzoaHCCRB3vXqTzYZ4GYE2OTtPFbP5lh3zhxYMb",
         'accessTokenKey' => "lvprdDbaPZADwjzvbaMy4ZDyJEap060RtzE3moYbjeZcv6E3",
         'accessTokenSecret' => "t5SyVjHgT1UVMQu22Wl05HACZ4T8Wmgeorfqba2h",
         'QBORealmID' => "123145766093222",
         'baseUrl' => "https://qbonline-e2e.api.intuit.com/"
));

//$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");

$vendorName = $_REQUEST['vendorName'];
$vendorAddress = $_REQUEST['vendorAddress'];
$vendorCity = $_REQUEST['vendorCity'];
$vendorState = $_REQUEST['vendorState'];
$vendorZip = $_REQUEST['vendorZip'];
$vendorPrice = $_REQUEST['vendorPrice'];
$verdorCustomerID = $_REQUEST['customerID'];
$vendorNotes = "Nationwide Equipment Control: ".$_REQUEST['vendorNotes'];

//Add a new Invoice
$theResourceObj = Vendor::create([
  "BillAddr" => [
        "Line1"=> $vendorName,
        "Line2"=> $vendorName,
        "Line3"=> $vendorAddress,
        "City"=> $vendorCity,
        "Country"=> "U.S.A",
        "CountrySubDivisionCode"=> $vendorState,
        "PostalCode"=> $vendorZip
    ],
    "TaxIdentifier"=> "",
    "GivenName"=> $vendorName,
    "FamilyName"=> $vendorName,
    "Suffix"=> "",
    "CompanyName"=> $vendorName,
    "DisplayName"=> $vendorName,
    "PrintOnCheckName"=> $vendorName,
    "PrimaryPhone"=> [
        "FreeFormNumber"=> ""
    ],
    "Mobile"=> [
        "FreeFormNumber"=> ""
    ],
    "PrimaryEmailAddr"=> [
        "Address"=> ""
    ],
    "WebAddr"=> [
        "URI"=> ""
    ]
]);
$resultingObj = $dataService->Add($theResourceObj);
$error = $dataService->getLastError();
if ($error != null) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
    echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
    $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
    echo $xmlBody . "\n";
}



$purchaseObj = PurchaseOrder::create([
    "VendorRef"=> [
        "value"=> $resultingObj->Id,
        "name"=> $vendorName
    ],
    "TotalAmt"=> floatval($vendorPrice),
  "BillAddr" => [
        "Line1"=> $vendorName,
        "Line2"=> $vendorName,
        "Line3"=> $vendorAddress,
        "City"=> $vendorCity,
        "Country"=> "U.S.A",
        "CountrySubDivisionCode"=> $vendorState,
        "PostalCode"=> $vendorZip
    ],
     "Line"=>[
        "Id"=>"1",
        "Amount"=> floatval($vendorPrice),
        "DetailType"=> "ItemBasedExpenseLineDetail",
        "ItemBasedExpenseLineDetail"=> [
            "CustomerRef"=> [
                "value"=> "69",
                "name"=>"Nationwide"
            ],
            "UnitPrice"=> intval($vendorPrice),
            "Qty"=> 1,
            "TaxCodeRef"=> [
                "value"=>"NON"
            ]
        ]
    ]
]);

$resultingObj = $dataService->Add($purchaseObj);
$error = $dataService->getLastError();
if ($error != null) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
    echo "Created Purchase Order Id={$resultingObj->Id}. Reconstructed response body:\n\n";
    $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
    echo $xmlBody . "\n";
}