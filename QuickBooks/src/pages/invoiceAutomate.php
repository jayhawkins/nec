<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config.php');
//require '../../../../nec_config.php';

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;


define("DBHOST", "hometree.dubtel.com"); 
define("DBNAME", "nec"); 
define("DBUSER", "nec_qa"); 
define("DBPASS", "Yellow10!");

// enter the IP numbers here, who you want to have access
$allowed_ips = array(
"127.0.0.1",
"127.0.0.2",
"127.0.0.3",
"127.0.0.4");

// set the loops to 0 before we start the check 
 $a = 0;
 $i = 0;
 
// start checking the ip numbers to see if they are allowed
foreach ($allowed_ips as $key => $value){
     
    if (preg_match($value, $_SERVER['REMOTE_ADDR']))
    { 
        $a++;
    }
}
 
// if ip address is not found send them somewhere else ...
if ($a == 0)   {
 echo ("Sorry, you do not have access to this section, please <a href=\"http://www.google.com\">click here</a>");
 exit();
}




//db call 

$dbh = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME)
     or die ('cannot connect to database because ' . mysqli_connect_error());



//select from orders that have not been invoiced
//see joins
   
   //run the query
$orderDetailLoop = mysqli_query($dbh, "select distinct(orderDetailID) as orderDetailID from approved_pod where hasBeenInvoiced = 0")
   or die (mysqli_error($dbh));


while($orderRow = mysqli_fetch_array($orderDetailLoop)){
    
    $orderDetailID = $orderRow['orderDetailID'];
    
    $loop = mysqli_query($dbh, "SELECT p.id as line_id,p.customerID,p.cost, p.orderID, p.orderDetailID, p.vinNumber, p.unitNumber,c.*,d.originationCity,d.originationState,d.destinationCity,d.destinationState ,e.name,l.address1,l.city,l.state,l.zip FROM nec.approved_pod p join order_details d on p.orderDetailID = d.id join entities e on p.customerID = e.id join locations l on e.id = l.entityID  join contacts c on e.contactID = c.id  where l.locationTypeID = 1 and p.orderDetailID = {$orderDetailID} and p.hasBeenInvoiced = 0")
       or die (mysqli_error($dbh));

    $lineItemList = array();
    
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
        $customer['orderDetailId'] = $row['orderDetailID'];
        $lineItem = array(
            "Amount" => floatval($row["cost"]), 
            "DetailType" => "SalesItemLineDetail",
            "Description" => "Trailer Vin#: " . $row['vinNumber'] . " | Unit #: " . $row['unitNumber'] . " | From " . $customer['description'],
            "SalesItemLineDetail" => [
                "ItemRef" => [
                    "value" => 1,
                    "name" => "Hours"
                ]
            ]
        );
        
        array_push($lineItemList, $lineItem);
    }
    //print_r($customer);
    //echo '<hr>';
    //print_r($lineItemList);
   createCustomerInvoice($customer, $lineItemList);
}

exit();





function createCustomerInvoice(Array $cust, Array $lineItems){
    
    //query for cutomer// Prep Data Services
    $dataService = DataService::Configure(array(
           'auth_mode' => 'oauth2',
             'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
             'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..CyZ-_Ue97Ufy4IC5h5Xxqg.Vvjo_ImHr7dLLsKCELuaF_-8Ex4sxVGWUrWaARjNp-0uz_N7DiEYHNy32dBB-ZkV5060ixYo_EVYtJGaLMoTxoe-SHFkw1j2D9wMuIITv4Riy1k96ZHI4etG8V_83oXk7yqlO8km9vI_t18vjMurTx1oDmkpMUCprvKnV6TXedPgI5YaGJURxqccH81vc1i7Iy_zWZ9cnYBn6DtHKZDt-kfF5TFFFQAPl8W_kk6k5z91lgez2NV_bNlyzdmgB0V6EiT8p6xvJqX2cNAp_Q5yjeKRQh_yK4kOyypwevSrRRnpSSJZ_onZ3WAQOZPbZyxzjNg1W-QCfRDVpnWlxphZZ9oV-k1vUwDWKYnn0T2KTvmeNqJTQ8TV4vPTdyhZyhLC9n4k-QO9ZBDpTg_W9msSROOSvNDf1FnkLvviU2jN9ePf3wN7n8RX4Ir85j8ktkgUsskN3OB7iM2HJOJTdcO-uqH56Ls16bgsBOm_Qk0vmb8GWmHlS3_T4abK8MU26m6wNrF6V08m80bYKUsl6UlbtsP4GWexz8QYngy-FvU6Upu89u2_9HYOvV6QRboTjygX5VpUuadvZyIuyIy9I478mQPE4-rSCNcLtaeF-RhKjUZeTs7jeAYCXO2nsmRIR6fvGRji-VdRGVdyEjJ9t6NhhtRaEEO_8RQlSPb1cixd37Ady_t2UzvCom5wVUVMgG64.Bl9KCNT-N6Ve0Yh7RtvuWw",
             'refreshTokenKey' => "Q011527441086eioY9H0tJqQBkFwLTxyKsQ9ZFPps9SRNUZJjI",
              'QBORealmID' => "123145985783569",
             'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
    ));

    $found_customer_id = 0;

    echo "Making Connection Before Checking for Customer";
    echo '<hr>';

    $entities = $dataService->Query("SELECT * FROM Customer");
    $error = $dataService->getLastError();
    
    if ($error != null) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "<br><br>";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "<br><br>";
        echo "The Response message is: " . $error->getResponseBody() . "<br><br>";
        exit();
    }
    else{
        
        //print_r( $entities);
    }
    
    
    //exit();


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


    echo "Customer ID: " . $found_customer_id;
    echo '<hr>';


    if ($found_customer_id==0){
        //create new customer

        echo "Making Connection Before Creating New Customer";
        echo '<hr>';

        $dataService = DataService::Configure(array(
             'auth_mode' => 'oauth2',
             'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
             'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..CyZ-_Ue97Ufy4IC5h5Xxqg.Vvjo_ImHr7dLLsKCELuaF_-8Ex4sxVGWUrWaARjNp-0uz_N7DiEYHNy32dBB-ZkV5060ixYo_EVYtJGaLMoTxoe-SHFkw1j2D9wMuIITv4Riy1k96ZHI4etG8V_83oXk7yqlO8km9vI_t18vjMurTx1oDmkpMUCprvKnV6TXedPgI5YaGJURxqccH81vc1i7Iy_zWZ9cnYBn6DtHKZDt-kfF5TFFFQAPl8W_kk6k5z91lgez2NV_bNlyzdmgB0V6EiT8p6xvJqX2cNAp_Q5yjeKRQh_yK4kOyypwevSrRRnpSSJZ_onZ3WAQOZPbZyxzjNg1W-QCfRDVpnWlxphZZ9oV-k1vUwDWKYnn0T2KTvmeNqJTQ8TV4vPTdyhZyhLC9n4k-QO9ZBDpTg_W9msSROOSvNDf1FnkLvviU2jN9ePf3wN7n8RX4Ir85j8ktkgUsskN3OB7iM2HJOJTdcO-uqH56Ls16bgsBOm_Qk0vmb8GWmHlS3_T4abK8MU26m6wNrF6V08m80bYKUsl6UlbtsP4GWexz8QYngy-FvU6Upu89u2_9HYOvV6QRboTjygX5VpUuadvZyIuyIy9I478mQPE4-rSCNcLtaeF-RhKjUZeTs7jeAYCXO2nsmRIR6fvGRji-VdRGVdyEjJ9t6NhhtRaEEO_8RQlSPb1cixd37Ady_t2UzvCom5wVUVMgG64.Bl9KCNT-N6Ve0Yh7RtvuWw",
             'refreshTokenKey' => "Q011527441086eioY9H0tJqQBkFwLTxyKsQ9ZFPps9SRNUZJjI",
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
            echo "The Status code is: " . $error->getHttpStatusCode() . "<br><br>";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "<br><br>";
            echo "The Response message is: " . $error->getResponseBody() . "<br><br>";
        } else {
            $found_customer_id = $resultingCustomerObj->Id;
            //print_r($resultingCustomerObj);
        }
    }

    echo "Customer ID: " . $found_customer_id;
    echo '<hr>';
    //exit();

    //Query db for all $cust['orderDetailID']
    //eg SELECT * FROM nec.approved_pod where orderDetailID = 83;


    echo "Making Connection Before Creating New Invoice";
    echo '<hr>';

    //create invoice
    $dataService = DataService::Configure(array(
          'auth_mode' => 'oauth2',
             'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
             'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..CyZ-_Ue97Ufy4IC5h5Xxqg.Vvjo_ImHr7dLLsKCELuaF_-8Ex4sxVGWUrWaARjNp-0uz_N7DiEYHNy32dBB-ZkV5060ixYo_EVYtJGaLMoTxoe-SHFkw1j2D9wMuIITv4Riy1k96ZHI4etG8V_83oXk7yqlO8km9vI_t18vjMurTx1oDmkpMUCprvKnV6TXedPgI5YaGJURxqccH81vc1i7Iy_zWZ9cnYBn6DtHKZDt-kfF5TFFFQAPl8W_kk6k5z91lgez2NV_bNlyzdmgB0V6EiT8p6xvJqX2cNAp_Q5yjeKRQh_yK4kOyypwevSrRRnpSSJZ_onZ3WAQOZPbZyxzjNg1W-QCfRDVpnWlxphZZ9oV-k1vUwDWKYnn0T2KTvmeNqJTQ8TV4vPTdyhZyhLC9n4k-QO9ZBDpTg_W9msSROOSvNDf1FnkLvviU2jN9ePf3wN7n8RX4Ir85j8ktkgUsskN3OB7iM2HJOJTdcO-uqH56Ls16bgsBOm_Qk0vmb8GWmHlS3_T4abK8MU26m6wNrF6V08m80bYKUsl6UlbtsP4GWexz8QYngy-FvU6Upu89u2_9HYOvV6QRboTjygX5VpUuadvZyIuyIy9I478mQPE4-rSCNcLtaeF-RhKjUZeTs7jeAYCXO2nsmRIR6fvGRji-VdRGVdyEjJ9t6NhhtRaEEO_8RQlSPb1cixd37Ady_t2UzvCom5wVUVMgG64.Bl9KCNT-N6Ve0Yh7RtvuWw",
             'refreshTokenKey' => "Q011527441086eioY9H0tJqQBkFwLTxyKsQ9ZFPps9SRNUZJjI",
              'QBORealmID' => "123145985783569",
        'baseUrl' => "development"
    )); 
    
    //$dataService->throwExceptionOnError(true);
    //Add a new Invoice
    $theResourceObj = Invoice::create([
        "Line" => $lineItems,
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
    
    print_r($theResourceObj);
    
    $resultingObj = $dataService->Add($theResourceObj);


    $error = $dataService->getLastError();
    if ($error != null) {        
        echo "The Status code is: " . $error->getHttpStatusCode() . "<br><br>";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "<br><br>";
        echo "The Response message is: " . $error->getResponseBody() . "<br><br>";
    }
    else {
        $invoice_id = $resultingObj->Id;
        echo "Created Id={$resultingObj->Id}. Reconstructed response body:<br><br>";
        //$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
        //echo $xmlBody . "\n";
    }

    // Create connection
    $conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $id = $cust['cid'];
    $orderDetailID = $cust['orderDetailId'];

    $sql = "UPDATE approved_pod SET hasBeenInvoiced=1, qbInvoiceNumber ='".$invoice_id."', updatedAt = NOW() WHERE orderDetailID=".$orderDetailID;
    echo $sql;
    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $conn->close();
    //exit();

    //update db
    
}


   
?>
