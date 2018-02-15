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
//    $dataService = DataService::Configure(array(
//           'auth_mode' => 'oauth2',
//             'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
//             'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
//             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..j_JnY_hCsAPxvL_gr51pKw.PlmdIn1vTE-EB15FBlczoeqXP6XRWVpaoEVeq-TavZAMoYtv4dB6G6N2R9_jW83ecgOkYSpbGP7moZgWfmwVyU5SWn4_tRJlTGzmsLwwjgkjpWc4aEBl3_0xntiePSQOBZl5NqCEJYL82QG48y1V8xHHwCI81g5gGUxFg88ZwrpyJyzhiKkNZeSQbGh09Ik-6aV4zVbuAQUaTjPVi9eZnRhop19KRikbk233j-lSVDrol1c7W4fLccXghHSllEb8mY-ju6AQXxiJKMtT9zehFXdmBwIQVnvU1R538za1ad3jp55GCTi4qE-svUznEnjJ9umnKUA6dJS9fTvJq1c1Q6rofLCCx0ORofXOHZ247TfqwyjJOxUU3a-bqQmtOEj7jWDwtw8M-5Ayt2gIZWOQEJraYLI1DK91jRIGvABpSd9G4wqRN6kqc6o1rnAu8J-YAav9YwDbs_xeagmoUE5mHvkwX79Zl0YBPVQYMcF5iiaZ2RKdZkDL4urgo_tDPHECKGyAqWRyc0s_CWN8JP4q5oC5fonps_M3aBXke_hLCkekQvkFp7r9FuZ1M79nT94u0n5x9bQF3ZJfte-Gwc7hNGYHPdl5pBfF3-ujb6aSQbMTAeMOrpqVNEw7fyzPY1g66CBfAL-0TNa_J7f5UcN949GNXfW2ejbToW3TvcII0KU2OMDAUchtQW4Tej7ou5hd.8CfRT-gGSE5qiZvWLaShbw",
//             'refreshTokenKey' => 'Q011527355634fFmbiEHSiomgcIYhgkd4C2gkLJHajvwq8PbSD',
//             'baseUrl' => "https://sandbox-quickbooks.api.intuit.com"
//    ));

    
    $dataService = DataService::Configure(array(
       'auth_mode' => 'oauth2',
             'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
             'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..j_JnY_hCsAPxvL_gr51pKw.PlmdIn1vTE-EB15FBlczoeqXP6XRWVpaoEVeq-TavZAMoYtv4dB6G6N2R9_jW83ecgOkYSpbGP7moZgWfmwVyU5SWn4_tRJlTGzmsLwwjgkjpWc4aEBl3_0xntiePSQOBZl5NqCEJYL82QG48y1V8xHHwCI81g5gGUxFg88ZwrpyJyzhiKkNZeSQbGh09Ik-6aV4zVbuAQUaTjPVi9eZnRhop19KRikbk233j-lSVDrol1c7W4fLccXghHSllEb8mY-ju6AQXxiJKMtT9zehFXdmBwIQVnvU1R538za1ad3jp55GCTi4qE-svUznEnjJ9umnKUA6dJS9fTvJq1c1Q6rofLCCx0ORofXOHZ247TfqwyjJOxUU3a-bqQmtOEj7jWDwtw8M-5Ayt2gIZWOQEJraYLI1DK91jRIGvABpSd9G4wqRN6kqc6o1rnAu8J-YAav9YwDbs_xeagmoUE5mHvkwX79Zl0YBPVQYMcF5iiaZ2RKdZkDL4urgo_tDPHECKGyAqWRyc0s_CWN8JP4q5oC5fonps_M3aBXke_hLCkekQvkFp7r9FuZ1M79nT94u0n5x9bQF3ZJfte-Gwc7hNGYHPdl5pBfF3-ujb6aSQbMTAeMOrpqVNEw7fyzPY1g66CBfAL-0TNa_J7f5UcN949GNXfW2ejbToW3TvcII0KU2OMDAUchtQW4Tej7ou5hd.8CfRT-gGSE5qiZvWLaShbw",
             'refreshTokenKey' => 'Q011527355634fFmbiEHSiomgcIYhgkd4C2gkLJHajvwq8PbSD',
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


    echo "Customer ID: " . $found_customer_id;
    echo '<hr>';
    exit();


    if ($found_customer_id==0){
        //create new customer

        $dataService = DataService::Configure(array(
             'auth_mode' => 'oauth2',
             'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
             'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
             'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..j_JnY_hCsAPxvL_gr51pKw.PlmdIn1vTE-EB15FBlczoeqXP6XRWVpaoEVeq-TavZAMoYtv4dB6G6N2R9_jW83ecgOkYSpbGP7moZgWfmwVyU5SWn4_tRJlTGzmsLwwjgkjpWc4aEBl3_0xntiePSQOBZl5NqCEJYL82QG48y1V8xHHwCI81g5gGUxFg88ZwrpyJyzhiKkNZeSQbGh09Ik-6aV4zVbuAQUaTjPVi9eZnRhop19KRikbk233j-lSVDrol1c7W4fLccXghHSllEb8mY-ju6AQXxiJKMtT9zehFXdmBwIQVnvU1R538za1ad3jp55GCTi4qE-svUznEnjJ9umnKUA6dJS9fTvJq1c1Q6rofLCCx0ORofXOHZ247TfqwyjJOxUU3a-bqQmtOEj7jWDwtw8M-5Ayt2gIZWOQEJraYLI1DK91jRIGvABpSd9G4wqRN6kqc6o1rnAu8J-YAav9YwDbs_xeagmoUE5mHvkwX79Zl0YBPVQYMcF5iiaZ2RKdZkDL4urgo_tDPHECKGyAqWRyc0s_CWN8JP4q5oC5fonps_M3aBXke_hLCkekQvkFp7r9FuZ1M79nT94u0n5x9bQF3ZJfte-Gwc7hNGYHPdl5pBfF3-ujb6aSQbMTAeMOrpqVNEw7fyzPY1g66CBfAL-0TNa_J7f5UcN949GNXfW2ejbToW3TvcII0KU2OMDAUchtQW4Tej7ou5hd.8CfRT-gGSE5qiZvWLaShbw",
             'refreshTokenKey' => 'Q011527355634fFmbiEHSiomgcIYhgkd4C2gkLJHajvwq8PbSD',
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

    echo "Customer ID: " . $found_customer_id;
    echo '<hr>';
    //exit();

    //Query db for all $cust['orderDetailID']
    //eg SELECT * FROM nec.approved_pod where orderDetailID = 83;


    //create invoice
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => "Q0bCkjuFuWa8MxjEDqYenaCreMUZjyAJ2UyNhnOmdVGEDNkkkD",
        'ClientSecret' => "ahfR70aIvIatES37ZeoJztAJx7Ki1PvoGhfNVTja",
        'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..j_JnY_hCsAPxvL_gr51pKw.PlmdIn1vTE-EB15FBlczoeqXP6XRWVpaoEVeq-TavZAMoYtv4dB6G6N2R9_jW83ecgOkYSpbGP7moZgWfmwVyU5SWn4_tRJlTGzmsLwwjgkjpWc4aEBl3_0xntiePSQOBZl5NqCEJYL82QG48y1V8xHHwCI81g5gGUxFg88ZwrpyJyzhiKkNZeSQbGh09Ik-6aV4zVbuAQUaTjPVi9eZnRhop19KRikbk233j-lSVDrol1c7W4fLccXghHSllEb8mY-ju6AQXxiJKMtT9zehFXdmBwIQVnvU1R538za1ad3jp55GCTi4qE-svUznEnjJ9umnKUA6dJS9fTvJq1c1Q6rofLCCx0ORofXOHZ247TfqwyjJOxUU3a-bqQmtOEj7jWDwtw8M-5Ayt2gIZWOQEJraYLI1DK91jRIGvABpSd9G4wqRN6kqc6o1rnAu8J-YAav9YwDbs_xeagmoUE5mHvkwX79Zl0YBPVQYMcF5iiaZ2RKdZkDL4urgo_tDPHECKGyAqWRyc0s_CWN8JP4q5oC5fonps_M3aBXke_hLCkekQvkFp7r9FuZ1M79nT94u0n5x9bQF3ZJfte-Gwc7hNGYHPdl5pBfF3-ujb6aSQbMTAeMOrpqVNEw7fyzPY1g66CBfAL-0TNa_J7f5UcN949GNXfW2ejbToW3TvcII0KU2OMDAUchtQW4Tej7ou5hd.8CfRT-gGSE5qiZvWLaShbw",
        'refreshTokenKey' => 'Q011527355634fFmbiEHSiomgcIYhgkd4C2gkLJHajvwq8PbSD',
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
        echo "The Status code is: " . $error->getHttpStatusCode() . "<br>";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "<br>";
        echo "The Response message is: " . $error->getResponseBody() . "<br>";
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
