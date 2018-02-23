<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config.php');
require '../../../../nec_config.php';

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;


$dbh = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME)
     or die ('cannot connect to database because ' . mysqli_connect_error());


// Run the query
$orderLoop = mysqli_query($dbh, "select distinct(a.orderID), count(a.vinNumber) as totalApproved, b.qty from approved_pod a, orders b where a.hasBeenInvoiced=1 and b.id=a.orderID and b.status='Open' group by a.orderID, a.vinNumber")
   or die (mysqli_error($dbh));


while($orderRow = mysqli_fetch_array($orderLoop)){
    
    $orderID = $orderRow['orderID']; 
    $totalInvoiced = $orderRow['totalApproved']; 
    $qty = $orderRow['qty']; 
    
    // Was all the trailers invoiced?
    if($totalInvoiced >= $qty){
        // Yes,
        // Close the order
        
        // Create connection
        $conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 

        $sql = "UPDATE orders SET status='Closed', updatedAt = NOW() WHERE id=".$orderID;
        echo $sql;
        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }

        $conn->close();
    }
    
}
?>
