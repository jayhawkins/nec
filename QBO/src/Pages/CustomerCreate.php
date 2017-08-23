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



$carrier_name = $_REQUEST['carrierName'];
$customer_name = $_REQUEST['customerName'];
$customer_rate = $_REQUEST['customerRate'];
$customer_notes = "Nationwide Equipment Control: ".$_REQUEST['customerNotes'];
$customer_found = FALSE;
// Run a query to see if customer exists
$entities = $dataService->Query("SELECT * FROM Customer");

// Echo some formatted output
$i = 0;
foreach($entities as $oneCustomer)
{
	
    
    if ($customer_name==$oneCustomer->DisplayName){
        $customer_found = TRUE;
        $customerid = $oneCustomer->Id;
    }
    
   
	$i++;
}






//$customer_name = 'Yaw Tandoh';
//$customer_rate = '150.00';
//$customer_notes = 'This is a Test';

// Add a customer
if ($customer_found == FALSE){
$customerObj = new IPPCustomer();
$customerObj->Name = $customer_name;
$customerObj->CompanyName = $customer_name;
$customerObj->GivenName = $customer_name;
$customerObj->DisplayName = $customer_name;
$resultingCustomerObj = $dataService->Add($customerObj);
$customerid = $resultingCustomerObj->Id;
echo "Created Customer Id={$customerid}. :\n\n";
echo 'Success';
}
else{
    
   echo "Customer already exists Id={$customerid}. :\n\n";
echo 'Success'; 
    
}




$invoiceObj = new IPPInvoice();

$Line = new IPPline();
$Line->Amount = floatval($customer_rate);
$Line->DetailType = 'SalesItemLineDetail';
$Line->Description = $customer_notes;
 
$saleItemLineDetail = new IPPSalesItemLineDetail();
$saleItemLineDetail->ItemRef = 1;
$Line->SalesItemLineDetail = $saleItemLineDetail;

$invoiceObj->Line = $Line;
$invoiceObj->CustomerRef = intval($customerid);

try{
 $resultingInvoiceObj = $dataService->Add($invoiceObj);
 print_r($resultingInvoiceObj); 
} catch (Exception $e){
 echo $e->getMessage();
}



if (isset($carrier_name)){
$vendorObj = new IPPVendor();
$vendorObj->GivenName  = $carrier_name;
$vendorObj->FamilyName = $carrier_name;
$vendorObj->DisplayName = $carrier_name;   
$vendorObj->CompanyName = $carrier_name;   
$vendorObj->Active= true;

try{
 $resultVendorObj = $dataService->Add($vendorObj);
 print_r($resultVendorObj); 
} catch (Exception $e){
 echo $e->getMessage();
}
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
