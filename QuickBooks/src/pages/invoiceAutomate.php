<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
error_reporting(E_ALL);
ini_set('display_errors', 1);


require '../../../../nec_config.php';
include('../config.php');
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;






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
   createCustomerInvoice($customer, $lineItemList);
}

exit();





function createCustomerInvoice(Array $cust, Array $lineItems){
    
    //query for cutomer// Prep Data Services
    $dataService = DataService::Configure(array(
           'auth_mode' => 'oauth2',
             'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
             'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..ITW5E3vqInW5MNwJBbqGcw.bA5DpR6ErMN5G99xTEG4gVXgdIFGI--SQrOiHDpzXLDyeP6MPMEQm850S3j439huD5KgdE2DDTf441jM1-J1jx2SwMTDKOrS4pT6EPj5YuP-vn7wRzIIVPRsDZBhXlMFnGPzifx2roVAj1y5plMHMZ1OLXfEhi_YGEwovzdavDkkshaJ7lJdMExu3PYt89EWGHGvefFLNx8U1B76GrQpm1a2rcI7xObVwGafdSsa0iVHnArOiC3NErnu6jBJy8xAxqosdsydmc84tSKhSWuq2RLtlrhqCnCfA2wVzg9w1c5l8-WBRt6BY2ljZs_GmR0Qc3sI5e9mlcGn8Uzl5q7xgB6ljmNAzmkpYNvwWxX6nbw6x47jXWTYsb5UPIcJ8qmnV3-BnCdYpSbJ4osUDtYpnW5Nr4PXAOnaC1WOF__FX0d1cm8zdgFsfVyiqW1CW3yUCNntVlYZt1nSh8CeXLTbetm4vNUXzJSXpOF-1wvduawTfksscPL0_1NVEB01f4pTwKKlbb8DG75u6ZTlz1giknob8pphO7MwtJegKStkmzQYeqwEsgCWv7YykDxoC2TuldUj1BYyMwsEFl1ExJb1yTxl1N1MnKuV5CmhdB85emr6a8I3wvZneCKknU4aRh72Gj2_U8wW-AJ73ghMnhi3j9I9q4VgO2_302Ck8V3w5vT7zKPR2ExZCADgId7TVnls.Dsw1tAFJm89R_dg4R7lDdw",
             'refreshTokenKey' => 'Q011527438107CNPRG7G95W9DNU4tXwESsQ6Vmp9Gw8K6nSgO4',
             'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
    ));

    $found_customer_id = 0;

    $entities = $dataService->Query("SELECT * FROM Customer");
    $error = $dataService->getLastError();
    
    if ($error != null) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "<br><br>";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "<br><br>";
        echo "The Response message is: " . $error->getResponseBody() . "<br><br>";
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

    if ($found_customer_id==0){
        //create new customer

        echo "Making Connection Before Creating New Customer";
        echo '<hr>';

        $dataService = DataService::Configure(array(
             'auth_mode' => 'oauth2',
             'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
             'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..ITW5E3vqInW5MNwJBbqGcw.bA5DpR6ErMN5G99xTEG4gVXgdIFGI--SQrOiHDpzXLDyeP6MPMEQm850S3j439huD5KgdE2DDTf441jM1-J1jx2SwMTDKOrS4pT6EPj5YuP-vn7wRzIIVPRsDZBhXlMFnGPzifx2roVAj1y5plMHMZ1OLXfEhi_YGEwovzdavDkkshaJ7lJdMExu3PYt89EWGHGvefFLNx8U1B76GrQpm1a2rcI7xObVwGafdSsa0iVHnArOiC3NErnu6jBJy8xAxqosdsydmc84tSKhSWuq2RLtlrhqCnCfA2wVzg9w1c5l8-WBRt6BY2ljZs_GmR0Qc3sI5e9mlcGn8Uzl5q7xgB6ljmNAzmkpYNvwWxX6nbw6x47jXWTYsb5UPIcJ8qmnV3-BnCdYpSbJ4osUDtYpnW5Nr4PXAOnaC1WOF__FX0d1cm8zdgFsfVyiqW1CW3yUCNntVlYZt1nSh8CeXLTbetm4vNUXzJSXpOF-1wvduawTfksscPL0_1NVEB01f4pTwKKlbb8DG75u6ZTlz1giknob8pphO7MwtJegKStkmzQYeqwEsgCWv7YykDxoC2TuldUj1BYyMwsEFl1ExJb1yTxl1N1MnKuV5CmhdB85emr6a8I3wvZneCKknU4aRh72Gj2_U8wW-AJ73ghMnhi3j9I9q4VgO2_302Ck8V3w5vT7zKPR2ExZCADgId7TVnls.Dsw1tAFJm89R_dg4R7lDdw",
             'refreshTokenKey' => 'Q011527438107CNPRG7G95W9DNU4tXwESsQ6Vmp9Gw8K6nSgO4',
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

//    echo "Customer ID: " . $found_customer_id;
//    echo '<hr>';
    //exit();

    //Query db for all $cust['orderDetailID']
    //eg SELECT * FROM nec.approved_pod where orderDetailID = 83;


//    echo "Making Connection Before Creating New Invoice";
//    echo '<hr>';

    //create invoice
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
        'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
        'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..ITW5E3vqInW5MNwJBbqGcw.bA5DpR6ErMN5G99xTEG4gVXgdIFGI--SQrOiHDpzXLDyeP6MPMEQm850S3j439huD5KgdE2DDTf441jM1-J1jx2SwMTDKOrS4pT6EPj5YuP-vn7wRzIIVPRsDZBhXlMFnGPzifx2roVAj1y5plMHMZ1OLXfEhi_YGEwovzdavDkkshaJ7lJdMExu3PYt89EWGHGvefFLNx8U1B76GrQpm1a2rcI7xObVwGafdSsa0iVHnArOiC3NErnu6jBJy8xAxqosdsydmc84tSKhSWuq2RLtlrhqCnCfA2wVzg9w1c5l8-WBRt6BY2ljZs_GmR0Qc3sI5e9mlcGn8Uzl5q7xgB6ljmNAzmkpYNvwWxX6nbw6x47jXWTYsb5UPIcJ8qmnV3-BnCdYpSbJ4osUDtYpnW5Nr4PXAOnaC1WOF__FX0d1cm8zdgFsfVyiqW1CW3yUCNntVlYZt1nSh8CeXLTbetm4vNUXzJSXpOF-1wvduawTfksscPL0_1NVEB01f4pTwKKlbb8DG75u6ZTlz1giknob8pphO7MwtJegKStkmzQYeqwEsgCWv7YykDxoC2TuldUj1BYyMwsEFl1ExJb1yTxl1N1MnKuV5CmhdB85emr6a8I3wvZneCKknU4aRh72Gj2_U8wW-AJ73ghMnhi3j9I9q4VgO2_302Ck8V3w5vT7zKPR2ExZCADgId7TVnls.Dsw1tAFJm89R_dg4R7lDdw",
             'refreshTokenKey' => 'Q011527438107CNPRG7G95W9DNU4tXwESsQ6Vmp9Gw8K6nSgO4',
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
    exit();
    
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
    $orderDetailID = $cust['orderDetailID'];

    $sql = "UPDATE approved_pod SET hasBeenInvoiced=1, qbInvoiceNumber ='".$invoice_id."' WHERE orderDetialID=$orderDetailID";
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
