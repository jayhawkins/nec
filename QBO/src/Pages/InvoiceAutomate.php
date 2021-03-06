<?php
/**
* Invoice Automation
* nec.dubtel.com
* Yaw G. Tandoh 2/6/2018
**/

//Initiate Quickbooks Settings
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



$customerName = 'Yaw Tandoh';
$customerAddress = '3305 Foxhound Dr';
$customerCity = 'Hamilton';
$customerState = 'OH';
$customerZip = '45011';
$customerPrice = '500';
$customerNotes = "Nationwide Equipment Control Test: ";
$customer_found = FALSE;
// Run a query to see if customer exists
$entities = $dataService->Query("SELECT * FROM Customer");

// Echo some formatted output
$i = 0;
foreach($entities as $oneCustomer)
{
	
    
    if ($customerName==$oneCustomer->DisplayName){
        $customer_found = TRUE;
        $customerid = $oneCustomer->Id;
    }
    
   
	$i++;
}


//echo 'Customer Found: '.$customer_found;

////customerName,customerAddress,customerCity,customerState,customerZip,customerPrice,customerNotes
// Add a customer
if ($customer_found == FALSE){
$customerObj = new IPPCustomer();
$customerObj->Name = $customerName;
$customerObj->CompanyName = $customerName;
$customerObj->GivenName = $customerName;
$customerObj->DisplayName = $customerName;

//$BillAddr = new IPPPhysicalAddress();
//$BillAddr->Line1 = $customerAddress;        
//$BillAddr->City = $customerCity;
//$BillAddr->CountrySubDivisionCode = $customerState;
//$BillAddr->PostalCode = $customerZip;
//$customerObj->BillAddr = $BillAddr;



try{
 $resultingCustomerObj = $dataService->Add($customerObj);
 //print_r($resultingInvoiceObj); 
 $customerid = $resultingCustomerObj->Id;
} catch (Exception $e){
    print_r($customerObj);
 echo $e->getMessage();
}

//echo $customerid;

}
else{
    $customerObj = new IPPCustomer();
    $customerObj = $dataService->FindById(
  new IPPCustomer( array('Id' => $customerid), true));

$customerObj->Name = $customerName;
$customerObj->CompanyName = $customerName;
$customerObj->GivenName = $customerName;
$customerObj->DisplayName = $customerName;

$BillAddr = new IPPPhysicalAddress();
$BillAddr->Line1 = $customerAddress;        
$BillAddr->City = $customerCity;
$BillAddr->CountrySubDivisionCode = $customerState;
$BillAddr->PostalCode = $customerZip;
$customerObj->BillAddr = $BillAddr;


try{
 $resultingCustomerObj = $dataService->Add($customerObj);
 //print_r($resultingInvoiceObj); 
} catch (Exception $e){
 echo $e->getMessage();
}

    
  //echo $customerid;
    
}



//connect to the server



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