<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config.php');
<<<<<<< HEAD
//require '../../../../nec_config.php';
=======
require '../../../../nec_config.php';

>>>>>>> 32805f76b91f3ee6e6148b3bdd2cf23e34c2922f
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;

<<<<<<< HEAD

define("DBHOST", "hometree.dubtel.com"); 
define("DBNAME", "nec"); 
define("DBUSER", "nec_qa"); 
define("DBPASS", "Yellow10!");

=======
>>>>>>> 32805f76b91f3ee6e6148b3bdd2cf23e34c2922f
//db call 

$dbh = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME)
     or die ('cannot connect to database because ' . mysqli_connect_error());

//select from orders that have not been invoiced
//see joins
   
   //run the query
    $loop = mysqli_query($dbh, "SELECT p.id as line_id, p.atFaultEntityID, p.cost, p.damageClaimID, p.vinNumber, p.damage_description, c.*, e.name,l.address1,l.city,l.state,l.zip FROM nec.approved_damage_claims p join entities e on p.atFaultEntityID = e.id join locations l on e.id = l.entityID  join contacts c on e.contactID = c.id  where l.locationTypeID = 1 and p.hasBeenInvoiced = 0")
       or die (mysqli_error($dbh));

    while ($row = mysqli_fetch_array($loop))
    {
        $lineItemList = array();
    
        $customer['description'] = "Damage Claim on Trailer VIN #: " . $row['vinNumber'] .  " | Damage: " . $row['damage_description']; 


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
        $customer['damageClaimID'] = $row['damageClaimID'];
        $lineItem = array(
            "Amount" => floatval($row["cost"]), 
            "DetailType" => "SalesItemLineDetail",
            "Description" => $customer['description'],
            "SalesItemLineDetail" => [
                "ItemRef" => [
                    "value" => 1,
                    "name" => "Hours"
                ]
            ]
        );
        
        array_push($lineItemList, $lineItem);
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
               'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..Ce32qXYv-kLgXGmkWjstpg.H1fa-x4Ax63i4mZsCf_N_WRy-bIHXfwIOI1TiKpxU4FSECoCqKG4CzYjC5i74qpgDfl-FbOrkBkzkD8ofZ_6rJ4xT2qNxn8m8NrTI6ZC5QIOTNrbLtQZKP9OpkCZIa1ltvg9GPU4IkKRJ7b9L6oynxr-92W6-mGsmBU1DJQJJxXBzVrOgn_ZeyrWOtgmXpdMz10ZLrvbWq6o9aQ3nCb1ylgwe9yBtAA1kOpW77wFPGvZtwwPP_zUt6W17FQ9zU5kL6UixZdPnkaOMYPa-1bnjw_OWmcua6l8VMjLqbkbKu5IuQaHayTAY5zNw_QyxA-sX3tfNukwsIP7zddg73kDOqSyN10i1Clcfl7wCpOsqPgDBG-IGjfH1I9EF-ufEJrYEpuI-YqUMvmplHnDlNwUCYvkfhIJJg_-ih194j0kKhiXsaHKWhuERGHQJnZfqEKKCMjbvYZhHy9vpoIgaPKA0iuDciB-DfUgOJttx-MefrhXL-yMeIV_EKLUnxaJjUzjzE_DiwjVBmLzzgGHY79qy11I_P-4vJGJUGbN96VrotWgZQNP8UbhkBqCGGYXorD7M0iKsFcw1ccvd5CquKYbLX9PU3Sr9ytDc7UMqizTVuRspQwMMDp28Hjs4vFJF8QyOgvMAhOskHCzjGsZH4x8iZ9SvdpuYqMjLVEKnlBYdmWSV2DLNAEjk8EFVCIjvXWI.WCM6NvYn3ag3aRWpb8ra0A",
             'refreshTokenKey' => "L011527876920Kw1fefUpJhPmxFgEGbMXzRCmm28xdM4F78he1",
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
            'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..Ce32qXYv-kLgXGmkWjstpg.H1fa-x4Ax63i4mZsCf_N_WRy-bIHXfwIOI1TiKpxU4FSECoCqKG4CzYjC5i74qpgDfl-FbOrkBkzkD8ofZ_6rJ4xT2qNxn8m8NrTI6ZC5QIOTNrbLtQZKP9OpkCZIa1ltvg9GPU4IkKRJ7b9L6oynxr-92W6-mGsmBU1DJQJJxXBzVrOgn_ZeyrWOtgmXpdMz10ZLrvbWq6o9aQ3nCb1ylgwe9yBtAA1kOpW77wFPGvZtwwPP_zUt6W17FQ9zU5kL6UixZdPnkaOMYPa-1bnjw_OWmcua6l8VMjLqbkbKu5IuQaHayTAY5zNw_QyxA-sX3tfNukwsIP7zddg73kDOqSyN10i1Clcfl7wCpOsqPgDBG-IGjfH1I9EF-ufEJrYEpuI-YqUMvmplHnDlNwUCYvkfhIJJg_-ih194j0kKhiXsaHKWhuERGHQJnZfqEKKCMjbvYZhHy9vpoIgaPKA0iuDciB-DfUgOJttx-MefrhXL-yMeIV_EKLUnxaJjUzjzE_DiwjVBmLzzgGHY79qy11I_P-4vJGJUGbN96VrotWgZQNP8UbhkBqCGGYXorD7M0iKsFcw1ccvd5CquKYbLX9PU3Sr9ytDc7UMqizTVuRspQwMMDp28Hjs4vFJF8QyOgvMAhOskHCzjGsZH4x8iZ9SvdpuYqMjLVEKnlBYdmWSV2DLNAEjk8EFVCIjvXWI.WCM6NvYn3ag3aRWpb8ra0A",
             'refreshTokenKey' => "L011527876920Kw1fefUpJhPmxFgEGbMXzRCmm28xdM4F78he1",
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
              'accessTokenKey' =>  "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..Ce32qXYv-kLgXGmkWjstpg.H1fa-x4Ax63i4mZsCf_N_WRy-bIHXfwIOI1TiKpxU4FSECoCqKG4CzYjC5i74qpgDfl-FbOrkBkzkD8ofZ_6rJ4xT2qNxn8m8NrTI6ZC5QIOTNrbLtQZKP9OpkCZIa1ltvg9GPU4IkKRJ7b9L6oynxr-92W6-mGsmBU1DJQJJxXBzVrOgn_ZeyrWOtgmXpdMz10ZLrvbWq6o9aQ3nCb1ylgwe9yBtAA1kOpW77wFPGvZtwwPP_zUt6W17FQ9zU5kL6UixZdPnkaOMYPa-1bnjw_OWmcua6l8VMjLqbkbKu5IuQaHayTAY5zNw_QyxA-sX3tfNukwsIP7zddg73kDOqSyN10i1Clcfl7wCpOsqPgDBG-IGjfH1I9EF-ufEJrYEpuI-YqUMvmplHnDlNwUCYvkfhIJJg_-ih194j0kKhiXsaHKWhuERGHQJnZfqEKKCMjbvYZhHy9vpoIgaPKA0iuDciB-DfUgOJttx-MefrhXL-yMeIV_EKLUnxaJjUzjzE_DiwjVBmLzzgGHY79qy11I_P-4vJGJUGbN96VrotWgZQNP8UbhkBqCGGYXorD7M0iKsFcw1ccvd5CquKYbLX9PU3Sr9ytDc7UMqizTVuRspQwMMDp28Hjs4vFJF8QyOgvMAhOskHCzjGsZH4x8iZ9SvdpuYqMjLVEKnlBYdmWSV2DLNAEjk8EFVCIjvXWI.WCM6NvYn3ag3aRWpb8ra0A",
             'refreshTokenKey' => "L011527876920Kw1fefUpJhPmxFgEGbMXzRCmm28xdM4F78he1",
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
    $damageClaimID = $cust['damageClaimID'];

    $sql = "UPDATE approved_damage_claims SET hasBeenInvoiced=1, qbInvoiceNumber ='".$invoice_id."', updatedAt = NOW() WHERE damageClaimID=".$damageClaimID;
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
