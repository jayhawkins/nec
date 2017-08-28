<?php
//error_reporting(E_ALL);
error_reporting(E_ALL ^ E_WARNING); 
ini_set('display_errors', 1);

require_once('../config.php');

require_once(PATH_SDK_ROOT . 'Core/ServiceContext.php');
require_once(PATH_SDK_ROOT . 'DataService/DataService.php');
require_once(PATH_SDK_ROOT . 'PlatformService/PlatformService.php');
require_once(PATH_SDK_ROOT . 'Utility/Configuration/ConfigurationManager.php');
require_once('../../CRUD/helper/PurchaseHelper.php'); 



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
$verdorCustomerID = $_REQUEST['customerID'];
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
        $vendorid = $oneCustomer->Id;
        //echo $vendorid;
        //exit();
    }
    
   
	$i++;
}


// Add a customer
////vendorName,vendorAddress,vendorCity,vendorState,vendorZip,vendorPrice,vendorNotes
if ($customer_found == FALSE){
$customerObj = new IPPvendor();
$customerObj->GivenName = $vendorName;
$customerObj->DisplayName = $vendorName;
$customerObj->Active = true;


$BillAddr = new IPPPhysicalAddress();
  $BillAddr->Line1 = $vendorAddress;        
    $BillAddr->Line2 = $vendorCity;
    $BillAddr->Line3 = $vendorState;
    $BillAddr->line4 = $vendorZip;
        
$BillAddr->City = $vendorCity;
$BillAddr->CountrySubDivisionCode = $vendorState;
$BillAddr->PostalCode = $vendorZip;

$customerObj->BillAddr = $BillAddr;
 print_r($customerObj); 
echo "adding new vendor";
try{
 $resultingCustomerObj = $dataService->Add($customerObj);

} catch (Exception $e){
 echo $e->getMessage();
 exit();
}
$vendorid =  $resultingCustomerObj->Id;
echo "Vendor Id={$vendorid}. :\n\n";
echo 'Success';
}
else{
    $customerObj = new IPPvendor();
    //vendorName,vendorAddress,vendorCity,vendorState,vendorZip,vendorPrice,vendorNotes
    $customerObj = $dataService->FindById(new IPPVendor( array('Id' => $vendorid), true));

    $customerObj->Name = $vendorName;
    $customerObj->CompanyName = $vendorName;
    $customerObj->GivenName = $vendorName;
    $customerObj->DisplayName = $vendorName;

    $BillAddr = new IPPPhysicalAddress();
      $BillAddr->Line1 = $vendorAddress;        
    $BillAddr->Line2 = $vendorCity;
    $BillAddr->Line3 = $vendorState;
    $BillAddr->line4 = $vendorZip;
    $customerObj->BillAddr = $BillAddr;

    //update Vendor

   
    
    try{
 $resultingCustomerObj = $dataService->Add($customerObj);
 //print_r($resultingCustomerObj); 
} catch (Exception $e){
 echo $e->getMessage();
}
    
   echo "vendor already exists but has been updated Id={$vendorid}. :\n\n";
echo 'Success'; 
    
}

//create final purchase order
$purchaseOrder = new IPPPurchaseOrder();
		
        $vendor = VendorHelper::getVendor($dataService);
        $purchaseOrder->VendorRef = $vendor->Id;

        $account = AccountHelper::getLiabilityBankAccount($dataService);
        $purchaseOrder->APAccountRef = $account->Id;
        
        $purchaseOrder->Memo = $vendorNotes;
        
        $line1 = new IPPLine();
        $line1->Amount = floatval($vendorPrice);
        $line->CustomerRef  = intval($verdorCustomerID);

        $lineDetailTypeEnum = new IPPLineDetailTypeEnum();
        $line1->DetailType = $lineDetailTypeEnum::IPPLINEDETAILTYPEENUM_ACCOUNTBASEDEXPENSELINEDETAIL;
        
        $detail = new IPPAccountBasedExpenseLineDetail();
        $account1 = AccountHelper::getExpenseBankAccount($dataService);
        //$detail->AccountRef = $account1->Id;
        $detail->AccountRef = 78;
        $line1->AccountBasedExpenseLineDetail = $detail;

        $purchaseOrder->Line = array($line1);

        $purchaseOrder->POEmail = Email::getEmailAddress();
        
        $purchaseOrder->Domain = "QBO";
        
        $globalTaxEnum= new IPPGlobalTaxCalculationEnum();
        $purchaseOrder->GlobalTaxCalculation = $globalTaxEnum::IPPGLOBALTAXCALCULATIONENUM_NOTAPPLICABLE;

        $purchaseOrder->ReplyEmail = Email::getEmailAddress();

        $purchaseOrder->ShipAddr = Address::getPhysicalAddress();

        $purchaseOrder->TotalAmt = floatval($vendorPrice);

        date_default_timezone_set('UTC');
        $purchaseOrder->TxnDate = date('Y-m-d', time());
        
        print_r($purchaseOrder);
        try{
         $result = $dataService->Add($purchaseOrder); 
         print_r($result); 
        } catch (Exception $e){
            //print_r($purchaseOrder);
         echo $e->getMessage();
        }

exit();

//create purchase order
$linedet = new IPPPurchaseOrderItemLineDetail();
$linedet->CustomerRef  = intval($verdorCustomerID);
$linedet->ItemRef = '20';
$linedet->Qty = '1';

$line = new IPPLine();
$line->Id = 0;
$line->Description = $vendorNotes;
$line->Amount = floatval($vendorPrice);
$line->DetailType= 'ItemBasedExpenseLineDetail ';
$line->ItemBasedExpenseLineDetail = $linedet;
$line->BillableStatus = 'Notbillable';
$line->ItemRef = '20';
$line->UnitPrice = $vendorPrice;
$line->Qty = '1';

$purchaseOrder = new IPPPurchaseOrder();
$purchaseOrder->Line = $line;
$purchaseOrder->VendorRef = intval($vendorid);
$purchaseOrder->APAccountRef = 84;



//TotalAmt
$purchaseOrder->TotalAmt = floatval($vendorPrice);


print_r($purchaseOrder);
try{
 $result = $dataService->Add($purchaseOrder); 
 print_r($result); 
} catch (Exception $e){
    //print_r($purchaseOrder);
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
