<?php
//error_reporting(E_ALL);
error_reporting(E_ALL ^ E_WARNING); 
ini_set('display_errors', 1);

require_once('../config.php');

require_once(PATH_SDK_ROOT . 'Core/ServiceContext.php');
require_once(PATH_SDK_ROOT . 'DataService/DataService.php');
require_once(PATH_SDK_ROOT . 'PlatformService/PlatformService.php');
require_once(PATH_SDK_ROOT . 'Utility/Configuration/ConfigurationManager.php');

//Specify QBO or QBD
$serviceType = IntuitServicesType::QBO;

// Get App Config
$realmId = ConfigurationManager::AppSettings('RealmID');
if (!$realmId)
	exit("Please add realm to App.Config before running this sample.\n");

// Prep Service Context
$requestValidator = new OAuthRequestValidator(ConfigurationManager::AppSettings('AccessToken'),
                                              ConfigurationManager::AppSettings('AccessTokenSecret'),
                                              ConfigurationManager::AppSettings('ConsumerKey'),
                                              ConfigurationManager::AppSettings('ConsumerSecret'));
$serviceContext = new ServiceContext($realmId, $serviceType, $requestValidator);
if (!$serviceContext)
	exit("Problem while initializing ServiceContext.\n");

// Prep Data Services
$dataService = new DataService($serviceContext);
if (!$dataService)
	exit("Problem while initializing DataService.\n");



$serviceContext = new ServiceContext($realmId, $serviceType, $requestValidator);
if (!$serviceContext)
	exit("Problem while initializing ServiceContext.\n");


//vendorName,vendorAddress,vendorCity,vendorState,vendorZip,vendorPrice,vendorNotes

$vendorName = $_REQUEST['vendorName'];
$vendorAddress = $_REQUEST['vendorAddress'];
$vendorCity = $_REQUEST['vendorCity'];
$vendorState = $_REQUEST['vendorState'];
$vendorZip = $_REQUEST['vendorZip'];
$vendorPrice = $_REQUEST['vendorPrice'];
$vendorNotes = "Nationwide Equipment Control: ".$_REQUEST['vendorNotes'];
$customer_found = FALSE;
// Run a query to see if customer exists
$entities = $dataService->Query("SELECT * FROM Vendor");

// Echo some formatted output
$i = 0;
foreach($entities as $oneCustomer)
{
	
    
    if ($vendorName==$oneCustomer->DisplayName){
        $customer_found = TRUE;
        $customerid = $oneCustomer->Id;
    }
    
   
	$i++;
}


// Add a customer
////vendorName,vendorAddress,vendorCity,vendorState,vendorZip,vendorPrice,vendorNotes
if ($customer_found == FALSE){
$customerObj = new IPPvendor();
$customerObj->Name = $vendorName;
$customerObj->CompanyName = $vendorName;
$customerObj->GivenName = $vendorName;
$customerObj->DisplayName = $vendorName;



$BillAddr = new IPPPhysicalAddress();
$BillAddr->Line1 = $vendorAddress;
        
$BillAddr->City = $vendorCity;
$BillAddr->CountrySubDivisionCode = $vendorState;
$BillAddr->PostalCode = $vendorZip;
$customerObj->BillAddr = $BillAddr;


$resultingVendorObj = $dataService->Add($customerObj);
$vendorid = $resultingVendorObj->Id;
echo "Vendor Id={$vendorid}. :\n\n";
echo 'Success';
}
else{
    //vendorName,vendorAddress,vendorCity,vendorState,vendorZip,vendorPrice,vendorNotes
    $customerObj = $dataService->FindById(new IPPCustomer( array('Id' => $customerid), true));

    $customerObj->Name = $vendorName;
    $customerObj->CompanyName = $vendorName;
    $customerObj->GivenName = $vendorName;
    $customerObj->DisplayName = $vendorName;

    $BillAddr = new IPPPhysicalAddress();
    $BillAddr->Line1 = $vendorAddress;        
    $BillAddr->City = $vendorCity;
    $BillAddr->CountrySubDivisionCode = $vendorState;
    $BillAddr->PostalCode = $vendorZip;
    $customerObj->BillAddr = $BillAddr;

    //update Vendor

    $resultingCustomerObj = $dataService->Add($customerObj);
    
   echo "vendor already exists but has been updated Id={$vendorid}. :\n\n";
echo 'Success'; 
    
}

////vendorName,vendorAddress,vendorCity,vendorState,vendorZip,vendorPrice,vendorNotes
$purchaseorderObject = new IPPPurchaseOrder();
$purchaseorderObject->Memo = $customer_notes;
$purchaseorderObject->VendorRef =  intval($vendorid);
$Line2 = new IPPline();
$Line2->Amount = floatval($vendorPrice);
$Line2->DetailType = 'SalesItemLineDetail';
$Line2->Description = $vendorNotes;

$iBillAddr = new IPPPhysicalAddress();
    $iBillAddr->Line1 = $vendorAddress;        
    $iBillAddr->City = $vendorCity;
    $iBillAddr->CountrySubDivisionCode = $vendorState;
    $iBillAddr->PostalCode = $vendorZip;
$purchaseorderObject->ShipAddr = $iBillAddr;
 
$saleItemLineDetail2 = new IPPSalesItemLineDetail();
$saleItemLineDetail2->ItemRef = 1;
$Line2->SalesItemLineDetail = $saleItemLineDetail2;

$purchaseorderObject->Line = $Line2;

try{
 $resultingPoObj = $dataService->Add($purchaseorderObject);
 print_r($resultingPoObj); 
} catch (Exception $e){
 echo $e->getMessage();
}

exit();




//$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingCustomerObj, $urlResource);
//echo $xmlBody . "\n";

//$estimateObj = new IPPEstimate();
//$estimateObj->TotalAmt = $customer_rate;
//$estimateObj->CustomerRef = $resultingCustomerObj->Id;
//$estimateObj->PrivateNote = $customer_notes;
//$resultEstimateobj = $dataService.Add($estimateObj);

//echo "Created Estimate Id={$resultEstimateobj->Id}. :\n\n";

//$vendorObj = new IPPVendor();
//$vendorObj->CompanyName = $customer_name;
//$vendorObj->DisplayName = $customer_name;
//$vendorObj->Notes = $customer_notes;
//$resultVendorObj = $dataService->Add($vendorObj);
//$vendorID = $resultVendorObj->Id;

//echo "Created Vendor Id={$vendorID}. :\n\n";


//$workOrderObj = new IPPPurchaseOrder();
//$workOrderObj->VendorRef = $vendorID;
//$workOrderObj->TotalAmt =  $customer_rate;
//$workOrderObj->Memo = $customer_notes;
//$resultPOObj = $dataService->Add($workOrderObj);
//$PoID = $resultPOObj->id;
//echo "Created Purchase Order Id={$PoID}. :\n\n";
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
