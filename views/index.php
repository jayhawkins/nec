<?php

if ($_SESSION['userid'] <= 0 || $_SESSION['userid'] == "") {
    header("Location: " . HTTP_HOST . "/login");
}

// Use arrays to determine which user types get access to each section
$needsMenuAccessList = array(0,1,2,4);
$availabilityMenuAccessList = array(0,1,2,4);
$ordersMenuAccessList = array(0,1,2,3,4,5);
$invoicingMenuAccessList = array(0,1,2,3);
$claimsMenuAccessList = array(0,1,2,3,4);
$collectionsMenuAccessList = array(0,1,2,4);
$profilesMenuAccessList = array(0,1);
$myneedsMenuAccessList = array(0,1,2,4);
$myavailabilityMenuAccessList = array(0,1,2,3,4);
$mapsMenuAccessList = array(0,1,2);
$settingsMenuAccessList = array(0,1,2);

/*
$cityargs = array(
      //"transform"=>"1",
      "columns"=>"originationCity,destinationCity",
      "filter[]"=> "entityID,eq,".$_SESSION['entityid'],
      "filter[]"=>"rootCustomNeedsID,eq,0",
      "filter[]"=>"expirationDate,ge,".date('Y-m-d')
);


$cityurl = API_HOST_URL . "/customer_needs?".http_build_query($cityargs);
$cityoptions = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'GET'
    )
);
$citycontext  = stream_context_create($cityoptions);
$cityresult = json_decode(file_get_contents($cityurl,false,$citycontext), true);
*/
//print_r($cityresult);
//die();

// Get States
$stateargs = array(
    "transform"=>"1",
    "columns"=>"abbreviation,name"
);

$stateurl = API_HOST_URL . "/states?".http_build_query($stateargs);
$stateoptions = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'GET'
    )
);
$statecontext  = stream_context_create($stateoptions);
$stateresult = json_decode(file_get_contents($stateurl,false,$statecontext), true);
//print_r($stateresult);
//die();

$member = json_decode(file_get_contents(API_HOST_URL . '/users?include=members&filter=id,eq,'.$_SESSION['userid']));

$firstName = $member->members->records[0][3];
$lastName = $member->members->records[0][4];

$eargs = array(
      "transform"=>"1",
      "columns"=>"entityTypeID,name",
      "filter[]"=> "id,eq,".$_SESSION['entityid']
);

$eurl = API_HOST_URL . "/entities?".http_build_query($eargs);
$eoptions = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'GET'
    )
);
$econtext  = stream_context_create($eoptions);
$eresult = json_decode(file_get_contents($eurl,false,$econtext), true);

$cncount = 0;
$locresult = 0;
$loccount = 0;

if ($_SESSION['entityid'] > 0) {
    if ( $eresult['entities'][0]['entityTypeID'] == 1 ) { // Customer
        $cnargs = array(
              "transform"=>"1",
              "filter[]"=>"rootCustomerNeedsID,eq,0",
              "filter[]"=>"status,eq,Available",
              "filter[]"=>"expirationDate,ge," . date("Y-m-d 00:00:00"),
              "filter[]"=>"entityID,eq," . $_SESSION['entityid']
        );
    } elseif ( $eresult['entities'][0]['entityTypeID'] == 2 ) { // Carrier
        $cnargs = array(
              "transform"=>"1",
              //"filter[0]"=>"rootCustomerNeedsID,eq,0",
              "filter[]"=>"status,eq,Available",
              "filter[]"=>"expirationDate,ge," . date("Y-m-d 00:00:00"),
              "filter[]"=>"entityID,eq," . $_SESSION['entityid']
        );
    }


    if ( $eresult['entities'][0]['entityTypeID'] == 1 ) { // Customer
        $entityname = $eresult['entities'][0]['name'] . " - (Customer)";
        $cnurl = API_HOST_URL . "/customer_needs?".http_build_query($cnargs);
    } elseif ( $eresult['entities'][0]['entityTypeID'] == 2 ) { // Carrier
        $entityname = $eresult['entities'][0]['name'] . " - (Carrier)";
        $cnurl = API_HOST_URL . "/carrier_needs?".http_build_query($cnargs);
    }

    $cnoptions = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'GET'
        )
    );

    $cncontext  = stream_context_create($cnoptions);
    $cnresult = file_get_contents($cnurl,false,$cncontext);
    $cnresult2 = json_decode($cnresult,true);
    if ( $eresult['entities'][0]['entityTypeID'] == 1 ) { // Customer
        $cncount = count($cnresult2['customer_needs']);
    } elseif ( $eresult['entities'][0]['entityTypeID'] == 2 ) { // Carrier
        $cncount = count($cnresult2['carrier_needs']);
    }

    // Get locations for plotting on map
    $locargs = array(
          "transform"=>"1",
          "filter[0]"=>"entityID,eq," . $_SESSION['entityid'],
          "filter[1]"=>"status,eq,Active"
    );

    $locoptions = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'GET'
        )
    );

    $locurl = API_HOST_URL . "/locations?".http_build_query($locargs);
    $loccontext  = stream_context_create($locoptions);
    $locresult = file_get_contents($locurl,false,$loccontext);
    $locresult2 = json_decode($locresult,true);
    $loccount = count($locresult2['locations']);

    $carrierneedresult = '{}';
    $customerneedresult = '{}';

    //print_r($cnresult);
    //die();

} else {

    // Now get resultsets and counts for Admin Logins
    $cnargs = array(
          "transform"=>"1",
          //"filter[]"=>"entityID,eq," . $_SESSION['entityid'],
          "filter[0]"=>"status,eq,Available",
          "filter[1]"=>"expirationDate,ge," . date("Y-m-d 00:00:00")
    );

    $entityname = $eresult['entities'][0]['name'] . " - (Admin)";
    $cnurl = API_HOST_URL . "/carrier_needs?".http_build_query($cnargs);
    $cnoptions = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'GET'
        )
    );
    $cncontext  = stream_context_create($cnoptions);
    $carrierneedresult = file_get_contents($cnurl,false,$cncontext);
    $cnresult2 = json_decode($carrierneedresult,true);
    $carrierncount = count($cnresult2['carrier_needs']);

    $cnargs = array(
          "transform"=>"1",
          //"filter[0]"=>"entityID,eq," . $_SESSION['entityid'],
          "filter[0]"=>"status,eq,Available",
          "filter[1]"=>"expirationDate,ge," . date("Y-m-d 00:00:00")
    );

    $entityname = $eresult['entities'][0]['name'] . " - (Admin)";
    $cnurl = API_HOST_URL . "/customer_needs?".http_build_query($cnargs);
    $cnoptions = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'GET'
        )
    );

    $cncontext  = stream_context_create($cnoptions);
    $customerneedresult = file_get_contents($cnurl,false,$cncontext);
    $cnresult2 = json_decode($customerneedresult,true);
    $customerncount = count($cnresult2['customer_needs']);


    // Get locations for plotting on map
    $locargs = array(
          "transform"=>"1",
          "filter[1]"=>"status,eq,Active"
    );

    $locoptions = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'GET'
        )
    );

    //$locurl = API_HOST_URL . "/locations?".http_build_query($locargs);
    //$loccontext  = stream_context_create($locoptions);
    //$locresult = file_get_contents($locurl,false,$loccontext);
    //$locresult2 = json_decode($locresult,true);
    //$loccount = count($locresult2['locations']);
    $locresult = '{}';

    $cnresult = '{}';
}

?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Nationwide Equipment Control - Dashboard</title>
    <link href="css/application.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/select2/select2.css">
    <link rel="stylesheet" href="vendor/select2/select2-bootstrap.css">
    <link rel="stylesheet" href="vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">
    <link rel="stylesheet" href="vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css.map">
    <!-- as of IE9 cannot parse css files with more that 4K classes separating in two files -->
    <!--[if IE 9]>
        <link href="css/application-ie9-part2.css" rel="stylesheet">
    <![endif]-->

<?php if(ENVIRONMENT == 'development') { ?>
	<link rel="stylesheet" type="text/css" href="vendor/datatables/media/css/dataTables-r-2_2_0.min.css"/>
<?php } else { ?>
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/b-1.4.2/r-2.2.0/datatables.min.css"/>
<?php } ?>

    <link rel="shortcut icon" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <style type="text/css">

        /* Specific mapael css class are below
         * 'mapael' class is added by plugin
        */

        .mapael .map {
            position: relative;
        }

        .mapael .mapTooltip {
            position: absolute;
            background-color: #fff;
            moz-opacity: 0.70;
            opacity: 0.70;
            filter: alpha(opacity=70);
            border-radius: 10px;
            padding: 10px;
            z-index: 1000;
            max-width: 200px;
            display: none;
            color: #343434;
        }

    </style>

    <script>
        /* yeah we need this empty stylesheet here. It's cool chrome & chromium fix
         chrome fix https://code.google.com/p/chromium/issues/detail?id=167083
         https://code.google.com/p/chromium/issues/detail?id=332189
         */

         var orders;

         // Main call to change main content area based on menu item selected
         function ajaxFormCall(form) {
           var host = location.protocol+'//'+window.location.hostname;
           var url = host+'/views/'+form+'.php';
           $.ajax({
              type: "GET",
              url: url,
              dataType: "html",
              async: false,
              success: function(data){
                 $("#maincontent").html(data);
                 $("#maincontent").find("script").each(function(i) {
                    eval($(this).text());
                 });
              },
              error: function() {
                 alert("Can't Get Template");
              }
           });
         }


        function countUserOrders(){

            var entityid = <?php echo $_SESSION['entityid']; ?>;
            var entityType = <?php echo $_SESSION['entitytype'];  ?>;

            var today = new Date();
            var date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
            var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            var dateTime = date+' '+time;
            var theDate = date;

            var url = '<?php echo API_HOST_URL; ?>';
            var orderCount = 0;
            switch(entityType){
                case 0:     // URL for the Admin. The admin can see ALL Orders.
                    //url += '/orders?include=order_details&columns=id,customerID,carrierIDs,orderID,originationCity,originationState,destinationCity,destinationState,originationLat,originationLng,destinationLat,destinationLng,distance,needsDataPoints,status,qty,rateType,transportationMode,order_details.pickupDate,order_details.deliveryDate';
                    url += '/order_details?include=orders';
                    break;
                case 1:    // URL for Customer. The Customer can only see their orders.
                    url += '/orders?include=documents,entities,order_details&columns=id,customerID,carrierIDs,documentID,orderID,originationAddress,originationCity,originationState,originationZip,destinationAddress,destinationCity,destinationState,destinationZip,originationLat,originationLng,destinationLat,destinationLng,distance,needsDataPoints,status,qty,rateType,transportationMode,entities.id,entities.name,documents.id,documents.documentURL,order_details.pickupDate&filter=customerID,eq,' + entityid;
                    break;
                case 2:     // URL for the Carrier. Same as the admin but will be filtered below.
                    url += '/orders?include=documents,entities,order_details&columns=id,customerID,carrierIDs,documentID,orderID,originationAddress,originationCity,originationState,originationZip,destinationAddress,destinationCity,destinationState,destinationZip,originationLat,originationLng,destinationLat,destinationLng,distance,needsDataPoints,status,qty,rateType,transportationMode,entities.id,entities.name,documents.id,documents.documentURL,order_details.pickupDate';
                    break;
            }

            url += '&filter[]=status,eq,Open&filter[]=deliveryDate,ge,'+theDate+'&satisfy=all&transform=1';
//console.log(url);
            $.ajax({
               //url: '<?php echo API_HOST_URL . "/orders" ?>?transform=1',
               url: url,
               type: "GET",
               contentType: "application/json",
               async: false,
               success: function(json){

                    //orders = json.order;
                    orders = json.order_details;
                    //console.log(orders);

                    if(entityType == 2) {

                        orders.forEach(function(order){
                            var carrierIDs = order.orders[0].carrierIDs;

                            for(var i = 0; i < carrierIDs.length; i++){
                                if(carrierIDs[i].carrierID == entityid){
                                    orderCount++;
                                    break;
                                }
                            }
                        });
                    }
                    else {
                        orderCount = orders.length;
                    }

                    $('#orderCount').html(orderCount);
               },
               error: function() {
                  alert("There Was An Error Getting User Orders Count");
               }
            });

        }

    function countCommitments(){

        var url = '<?php echo API_HOST_URL; ?>' + '/customer_needs?columns=id,rootCustomerNeedsID&filter[]=rootCustomerNeedsID,neq,0&filter[]=status,eq,Available&transform=1';

        $.ajax({
           url: url,
           type: "GET",
           contentType: "application/json",
           success: function(json){

                var customer_needs = json.customer_needs;
                var customer_needs_commit = new Array();

                customer_needs.forEach(function(customer_need){

                    if(customer_needs_commit.indexOf(customer_need.rootCustomerNeedsID) == -1){
                        customer_needs_commit.push(customer_need.rootCustomerNeedsID);
                    }
                });

                countCommitted(customer_needs_commit.toString());

           },
           error: function() {
              alert("There Was An Error Getting Commitments Count");
           }
        });

    }

        function countCommitted(committed){

            var baseUrl = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,rate,availableDate,expirationDate,transportationMode,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,customer_needs_commit.id,customer_needs_commit.status,customer_needs_commit.rate,customer_needs_commit.transporation_mode,entities.name,entities.rateType,entities.negotiatedRate&filter[]=id,in,' + committed + '&filter[]=status,eq,Available';

            var url = baseUrl + '&order[]=entityID&order[]=rootCustomerNeedsID&order[]=availableDate,desc&transform=1';

            $.ajax({
               url: url,
               type: "GET",
               contentType: "application/json",
               async: false,
               success: function(json){

                    var customer_needs = json.customer_needs;

                    var commitmentCount = customer_needs.length;

                    $('#commitmentCount').html(commitmentCount);
               },
               error: function() {
                  alert("There Was An Error Getting Committed Count");
               }
            });

        }

        function getOrdersByFilters(){

            var today = new Date();
            var date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
            var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            var dateTime = date+' '+time;

            // Parse each elements
            // This variable will hold all the plots of our map
            var plots = {};
            var links = {};
            var linktitle = "";
            var linkobjecttitle = "";
            var originationPlotColor = "";

            var entityid = <?php echo $_SESSION['entityid']; ?>;
            var orderCount = 0;

            var strarray = "";
            var statearray = "";
            var cityarray = "";

            if ($("#activityFilter").val() > '') {
                var str = $("#activityFilter").val().toString();
                var strarray = str.split(",");
            }

            if ($("#stateFilter").val() > '') {
                var str = $("#stateFilter").val().toString();
                var statearray = str.split(",");
            }

            if ($("#cityFilter").val() > '') {
                var str = $("#cityFilter").val().toString();
                var cityarray = str.split(",");
            }

            if (strarray) {
                    strarray.forEach(function(string) {
                            var url = '<?php echo API_HOST_URL; ?>';
                            var filter = '';
                            var satisfy = '';
                            switch ( string ) {
                                case 'Availability':
                                    url += "/customer_needs?";
                                    filter += '&filter[]=rootCustomerNeedsID,eq,0';
                                    filter += '&filter[]=status,eq,Available';
                                    filter += '&filter[]=expirationDate,ge,'+dateTime;
                                    if (entityid > 0) {
                                        filter += '&filter[]=entityID,eq,'+entityid;
                                    }
                                    if (statearray) {
                                        if ($('input[name=locationStatus]:checked').val() == "Origination") {
                                            filter += '&filter[]=originationState,in,'+statearray;
                                        } else {
                                            filter += '&filter[]=destinationState,in,'+statearray;
                                        }
                                        satisfy = '&satisfy=all';
                                    }
                                    if (cityarray) {
                                        if ($('input[name=locationStatus]:checked').val() == "Origination") {
                                            filter += '&filter[]=originationCity,in,'+cityarray;
                                        } else {
                                            filter += '&filter[]=destinationCity,in,'+cityarray;
                                        }
                                        satisfy = '&satisfy=all';
                                    }
                                    originationPlotColor = "red";
                                    break;
                                case 'Needs':
                                    url += "/carrier_needs?";
                                    filter += '&filter[]=status,eq,Available';
                                    filter += '&filter[]=expirationDate,ge,'+dateTime;
                                    if (statearray) {
                                        if ($('input[name=locationStatus]:checked').val() == "Origination") {
                                            filter += '&filter[]=originationState,in,'+statearray;
                                        } else {
                                            filter += '&filter[]=destinationState,in,'+statearray;
                                        }
                                        satisfy = '&satisfy=all';
                                    }
                                    if (cityarray) {
                                        if ($('input[name=locationStatus]:checked').val() == "Origination") {
                                            filter += '&filter[]=originationCity,in,'+cityarray;
                                        } else {
                                            filter += '&filter[]=destinationCity,in,'+cityarray;
                                        }
                                        satisfy = '&satisfy=all';
                                    }
                                    originationPlotColor = "blue";
                                    break;
                                case 'Commitments':
                                    url += "/customer_needs_commit?";
                                    //url += "include=customer_needs";
                                    filter += '&filter[]=status,eq,Available';
                                    filter += '&filter[]=deliveryDate,ge,'+dateTime;
                                    if (statearray) {
                                        if ($('input[name=locationStatus]:checked').val() == "Origination") {
                                            filter += '&filter[]=originationState,in,'+statearray;
                                        } else {
                                            filter += '&filter[]=destinationState,in,'+statearray;
                                        }
                                        satisfy = '&satisfy=all';
                                    }
                                    if (cityarray) {
                                        if ($('input[name=locationStatus]:checked').val() == "Origination") {
                                            filter += '&filter[]=originationCity,in,'+cityarray;
                                        } else {
                                            filter += '&filter[]=destinationCity,in,'+cityarray;
                                        }
                                        satisfy = '&satisfy=all';
                                    }
                                    originationPlotColor = "green";
                                    break;
                                case 'Orders':
                                    url += "/orders?";
                                    url += "include=order_details";
                                    filter += '&filter[]=status,eq,Open';
                                    filter += '&filter[]=order_details.deliveryDate,ge,'+dateTime;
                                    if (statearray) {
                                        if ($('input[name=locationStatus]:checked').val() == "Origination") {
                                            filter += '&filter[]=orders.originationState,in,'+statearray;
                                        } else {
                                            filter += '&filter[]=orders.destinationState,in,'+statearray;
                                        }
                                        satisfy = '&satisfy=all';
                                    }
                                    if (cityarray) {
                                        if ($('input[name=locationStatus]:checked').val() == "Origination") {
                                            filter += '&filter[]=orders.originationCity,in,'+cityarray;
                                        } else {
                                            filter += '&filter[]=orders.destinationCity,in,'+cityarray;
                                        }
                                        satisfy = '&satisfy=all';
                                    }
                                    originationPlotColor = "orange";
                                    break;
                                default:

                            }

                            url += filter+satisfy+'&transform=1';
//console.log(url);
                            $.ajax({
                                 url: url,
                                 type: 'GET',
                                 contentType: "application/json",
                                 async: false,
                                 success: function(response){
                                    //console.log(response);
                                    if(string == "Availability") {

                                           $.each(response.customer_needs, function (index, value) {

                                               var availableDate = value.availableDate;
                                               var expirationDate = value.expirationDate;
                                               var expirationDateStatus = 'Closed';

                                               // Check if we have the GPS position of the element
                                               if (value.originationLat) {
                                                   // Setup Availability Date
                                                   if (value.availableDate > '') {
                                                       var availableDate = 'Availability Date: ' + formatDate(new Date(availableDate));
                                                   } else {
                                                       var availableDate = 'Availability Date: ' + expirationDateStatus;
                                                   }
                                                   if (value.expirationDate > '') {
                                                       var expirationDate = formatDate(new Date(expirationDate));
                                                   } else {
                                                       var expirationDate = expirationDateStatus;
                                                   }
                                                   // Will hold the plot information
                                                   var plot = {};
                                                   var link = {};
                                                   // Assign position
                                                   plot.latitude = parseFloat(value.originationLat);
                                                   plot.longitude = parseFloat(value.originationLng);
                                                   plot.size = 10;
                                                   plot.type = "circle";
                                                   plot.value = "H";
                                                   // Assign some information inside the tooltip
                                                   plot.tooltip = {
                                                       content: "<span style='font-weight:bold;'>" +
                                                                   "Origin: " + value.originationCity + ", " + value.originationState +
                                                                   "<br />" +
                                                                   "Dest: " + value.destinationCity + ", " + value.destinationState +
                                                                   "<br /># of Trailers: " +
                                                                   value.qty +
                                                                   "<br />" +
                                                                   availableDate +
                                                                   "<br />Click for more details" +
                                                                "</span>"
                                                   };

                                                   plot.text = {
                                                        //content: qty,
                                                        position: "inner",
                                                        attrs: {
                                                            "font-size": 16,
                                                            "font-weight": "bold",
                                                            "fill": "#fff"
                                                        }
                                                   };

                                                   plot.eventHandlers = {
                                                        click: function() {
                                                                ajaxFormCall('listCustomerNeeds')
                                                        }
                                                   };

                                                   // Assign the background color randomize from a scale
                                                   plot.attrs = {
                                                       fill: originationPlotColor,
                                                       cursor: "pointer"
                                                   };

                                                   // Set plot element to array
                                                   plots[value.id+'-'+value.originationCity] = plot;

                                                   // Now plot the destination
                                                   var plot = {};
                                                   // Assign position
                                                   plot.latitude = parseFloat(value.destinationLat);
                                                   plot.longitude = parseFloat(value.destinationLng);
                                                   plot.size = 3;
                                                   plot.type = "";
                                                   // Assign some information inside the tooltip
                                                   plot.tooltip = {
                                                       content: "<span style='font-weight:bold;'>" +
                                                                   value.destinationCity +
                                                                "</span>"
                                                   };

                                                   plot.text = {
                                                        //content: value.qty,
                                                        position: "inner",
                                                        attrs: {
                                                            "font-size": 16,
                                                            "font-weight": "bold",
                                                            "fill": "#fff"
                                                        }
                                                   };

                                                   // Assign the background color randomize from a scale
                                                   plot.attrs = {
                                                       //fill: plotsColors(Math.random())
                                                       fill: "#fff"
                                                   };

                                                   // Set plot element to array
                                                   plots[value.id+'-'+value.destinationCity] = plot;

                                                   linktitle = value.originationCity+'-'+value.destinationCity;
                                                   linkobjecttitle = value.originationCity+value.destinationCity;
                                                   link.factor = 0.2;
                                                   //link.between = [{"latitude": value.originationLat, "longitude": value.originationLng}, {"latitude": value.destinationLat, "longitude": value.destinationLng}];
                                                   link.between = [value.id+'-'+value.originationCity, value.id+'-'+value.destinationCity];
                                                   link.attrs = {
                                                                "stroke": "#a4e100",
                                                                "stroke-width": 2,
                                                                "stroke-linecap": "round",
                                                                "opacity": 0.6,
                                                                "arrow-end": "classic-wide-long"
                                                            };
                                                   link.tooltip = {"content": linktitle};
                                                   links[linkobjecttitle] = link;
                                               } else {
                                                   console.warn("Ignored element " + id + " without GPS position");
                                               }
                                           });


                                    } else if(string == 'Needs') {

                                           $.each(response.carrier_needs, function (index, value) {

                                               var availableDate = value.availableDate;
                                               var expirationDate = value.expirationDate;
                                               var expirationDateStatus = 'Closed';

                                               // Check if we have the GPS position of the element
                                               if (value.originationLat) {
                                                   // Setup Availability Date
                                                   if (value.availableDate > '') {
                                                       var availableDate = 'Availability Date: ' + formatDate(new Date(availableDate));
                                                   } else {
                                                       var availableDate = 'Availability Date: ' + expirationDateStatus;
                                                   }
                                                   if (value.expirationDate > '') {
                                                       var expirationDate = formatDate(new Date(expirationDate));
                                                   } else {
                                                       var expirationDate = expirationDateStatus;
                                                   }
                                                   // Will hold the plot information
                                                   var plot = {};
                                                   var link = {};
                                                   // Assign position
                                                   plot.latitude = parseFloat(value.originationLat);
                                                   plot.longitude = parseFloat(value.originationLng);
                                                   plot.size = 10;
                                                   plot.type = "circle";
                                                   plot.value = "H";
                                                   // Assign some information inside the tooltip
                                                   plot.tooltip = {
                                                       content: "<span style='font-weight:bold;'>" +
                                                                   "Origin: " + value.originationCity + ", " + value.originationState +
                                                                   "<br />" +
                                                                   "Dest: " + value.destinationCity + ", " + value.destinationState +
                                                                   "<br /># of Trailers: " +
                                                                   value.qty +
                                                                   "<br />" +
                                                                   availableDate +
                                                                   "<br />Click for more details" +
                                                                "</span>"
                                                   };

                                                   plot.text = {
                                                        //content: qty,
                                                        position: "inner",
                                                        attrs: {
                                                            "font-size": 16,
                                                            "font-weight": "bold",
                                                            "fill": "#fff"
                                                        }
                                                   };

                                                   plot.eventHandlers = {
                                                        click: function() {
                                                                ajaxFormCall('listCarrierNeeds')
                                                        }
                                                   };

                                                   // Assign the background color randomize from a scale
                                                   plot.attrs = {
                                                       fill: originationPlotColor,
                                                       cursor: "pointer"
                                                   };

                                                   // Set plot element to array
                                                   plots[value.id+'-'+value.originationCity] = plot;

                                                   // Now plot the destination
                                                   var plot = {};
                                                   // Assign position
                                                   plot.latitude = parseFloat(value.destinationLat);
                                                   plot.longitude = parseFloat(value.destinationLng);
                                                   plot.size = 3;
                                                   plot.type = "";
                                                   // Assign some information inside the tooltip
                                                   plot.tooltip = {
                                                       content: "<span style='font-weight:bold;'>" +
                                                                   value.destinationCity +
                                                                "</span>"
                                                   };

                                                   plot.text = {
                                                        //content: value.qty,
                                                        position: "inner",
                                                        attrs: {
                                                            "font-size": 16,
                                                            "font-weight": "bold",
                                                            "fill": "#fff"
                                                        }
                                                   };

                                                   // Assign the background color randomize from a scale
                                                   plot.attrs = {
                                                       //fill: plotsColors(Math.random())
                                                       fill: "#fff"
                                                   };

                                                   // Set plot element to array
                                                   plots[value.id+'-'+value.destinationCity] = plot;

                                                   linktitle = value.originationCity+'-'+value.destinationCity;
                                                   linkobjecttitle = value.originationCity+value.destinationCity;
                                                   link.factor = 0.2;
                                                   //link.between = [{"latitude": value.originationLat, "longitude": value.originationLng}, {"latitude": value.destinationLat, "longitude": value.destinationLng}];
                                                   link.between = [value.id+'-'+value.originationCity, value.id+'-'+value.destinationCity];
                                                   link.attrs = {
                                                                "stroke": "#a4e100",
                                                                "stroke-width": 2,
                                                                "stroke-linecap": "round",
                                                                "opacity": 0.6,
                                                                "arrow-end": "classic-wide-long"
                                                            };
                                                   link.tooltip = {"content": linktitle};
                                                   links[linkobjecttitle] = link;
                                               } else {
                                                   console.warn("Ignored element " + id + " without GPS position");
                                               }
                                           });

                                    } else if(string == 'Commitments') {

                                           $.each(response.customer_needs_commit, function (index, value) {

                                               var availableDate = value.pickupDate;
                                               var expirationDate = value.deliveryDate;
                                               var expirationDateStatus = 'Closed';

                                               // Check if we have the GPS position of the element
                                               if (value.originationLat) {
                                                   // Setup Availability Date
                                                   if (value.availableDate > '') {
                                                       var availableDate = 'Availability Date: ' + formatDate(new Date(availableDate));
                                                   } else {
                                                       var availableDate = 'Availability Date: ' + expirationDateStatus;
                                                   }
                                                   if (value.expirationDate > '') {
                                                       var expirationDate = formatDate(new Date(expirationDate));
                                                   } else {
                                                       var expirationDate = expirationDateStatus;
                                                   }
                                                   // Will hold the plot information
                                                   var plot = {};
                                                   var link = {};
                                                   // Assign position
                                                   plot.latitude = parseFloat(value.originationLat);
                                                   plot.longitude = parseFloat(value.originationLng);
                                                   plot.size = 10;
                                                   plot.type = "circle";
                                                   plot.value = "H";
                                                   // Assign some information inside the tooltip
                                                   plot.tooltip = {
                                                       content: "<span style='font-weight:bold;'>" +
                                                                   "Origin: " + value.originationCity + ", " + value.originationState +
                                                                   "<br />" +
                                                                   "Dest: " + value.destinationCity + ", " + value.destinationState +
                                                                   "<br /># of Trailers: " +
                                                                   value.qty +
                                                                   "<br />" +
                                                                   availableDate +
                                                                   "<br />Click for more details" +
                                                                "</span>"
                                                   };

                                                   plot.text = {
                                                        //content: qty,
                                                        position: "inner",
                                                        attrs: {
                                                            "font-size": 16,
                                                            "font-weight": "bold",
                                                            "fill": "#fff"
                                                        }
                                                   };

                                                   plot.eventHandlers = {
                                                        click: function() {
                                                                ajaxFormCall('listCommitments')
                                                        }
                                                   };

                                                   // Assign the background color randomize from a scale
                                                   plot.attrs = {
                                                       fill: originationPlotColor,
                                                       cursor: "pointer"
                                                   };

                                                   // Set plot element to array
                                                   plots[value.id+'-'+value.originationCity] = plot;

                                                   // Now plot the destination
                                                   var plot = {};
                                                   // Assign position
                                                   plot.latitude = parseFloat(value.destinationLat);
                                                   plot.longitude = parseFloat(value.destinationLng);
                                                   plot.size = 3;
                                                   plot.type = "";
                                                   // Assign some information inside the tooltip
                                                   plot.tooltip = {
                                                       content: "<span style='font-weight:bold;'>" +
                                                                   value.destinationCity +
                                                                "</span>"
                                                   };

                                                   plot.text = {
                                                        //content: value.qty,
                                                        position: "inner",
                                                        attrs: {
                                                            "font-size": 16,
                                                            "font-weight": "bold",
                                                            "fill": "#fff"
                                                        }
                                                   };

                                                   // Assign the background color randomize from a scale
                                                   plot.attrs = {
                                                       //fill: plotsColors(Math.random())
                                                       fill: "#fff"
                                                   };

                                                   // Set plot element to array
                                                   plots[value.id+'-'+value.destinationCity] = plot;

                                                   linktitle = value.originationCity+'-'+value.destinationCity;
                                                   linkobjecttitle = value.originationCity+value.destinationCity;
                                                   link.factor = 0.2;
                                                   //link.between = [{"latitude": value.originationLat, "longitude": value.originationLng}, {"latitude": value.destinationLat, "longitude": value.destinationLng}];
                                                   link.between = [value.id+'-'+value.originationCity, value.id+'-'+value.destinationCity];
                                                   link.attrs = {
                                                                "stroke": "#a4e100",
                                                                "stroke-width": 2,
                                                                "stroke-linecap": "round",
                                                                "opacity": 0.6,
                                                                "arrow-end": "classic-wide-long"
                                                            };
                                                   link.tooltip = {"content": linktitle};
                                                   links[linkobjecttitle] = link;
                                               } else {
                                                   console.warn("Ignored element " + id + " without GPS position");
                                               }
                                           });

                                    } else if(string == 'Orders') {

                                           $.each(response.orders, function (index, value) {
                                               // Setup Pickup Date
                                               //alert(formatDate(new Date(value.order_details[0].pickupDate)));
                                               var pickupDate = formatDate(new Date(value.order_details[0].pickupDate));
                                               var deliveryDate = formatDate(new Date(value.order_details[0].deliveryDate));
                                               // Check if we have the GPS position of the element
                                               if (value.originationLat) {
                                                   // Will hold the plot information
                                                   var plot = {};
                                                   var link = {};
                                                   // Assign position
                                                   plot.latitude = parseFloat(value.originationLat);
                                                   plot.longitude = parseFloat(value.originationLng);
                                                   plot.size = 10;
                                                   plot.type = "circle";
                                                   // Assign some information inside the tooltip
                                                   plot.tooltip = {
                                                       content: "<span style='font-weight:bold;'>" +
                                                               value.originationCity + ", " + value.originationState +
                                                               "<br />" +
                                                               value.destinationCity + ", " + value.destinationState +
                                                               "<br /># of Trailers: " +
                                                               value.qty +
                                                               "<br />" +
                                                               "Pickup: " + pickupDate +
                                                               "<br />Click for more details" +
                                                            "</span>"
                                                   };

                                                   plot.text = {
                                                        //content: value.qty,
                                                        position: "inner",
                                                        attrs: {
                                                            "font-size": 16,
                                                            "font-weight": "bold",
                                                            "fill": "#fff"
                                                        }
                                                   };

                                                   // Assign the background color randomize from a scale
                                                   plot.attrs = {
                                                       //fill: plotsColors(Math.random())
                                                       fill: originationPlotColor,
                                                       cursor: "pointer"
                                                   };

                                                   plot.eventHandlers = {
                                                        click: function() {
                                                                ajaxFormCall('listOrders')
                                                        }
                                                   };

                                                   // Set plot element to array
                                                   plots[value.id+'-'+value.originationCity] = plot;

                                                   // Now plot the destination
                                                   var plot = {};
                                                   // Assign position
                                                   plot.latitude = parseFloat(value.destinationLat);
                                                   plot.longitude = parseFloat(value.destinationLng);
                                                   plot.size = 3;
                                                   plot.type = "";
                                                   // Assign some information inside the tooltip
                                                   plot.tooltip = {
                                                       content: "<span style='font-weight:bold;'>" +
                                                                   value.destinationCity +
                                                                "</span>"
                                                   };

                                                   plot.text = {
                                                        //content: value.qty,
                                                        position: "inner",
                                                        attrs: {
                                                            "font-size": 16,
                                                            "font-weight": "bold",
                                                            "fill": "#fff"
                                                        }
                                                   };

                                                   // Assign the background color randomize from a scale
                                                   plot.attrs = {
                                                       //fill: plotsColors(Math.random())
                                                       fill: "#fff"
                                                   };

                                                   // Set plot element to array
                                                   plots[value.id+'-'+value.destinationCity] = plot;

                                                   linktitle = value.originationCity+'-'+value.destinationCity;
                                                   linkobjecttitle = value.originationCity+value.destinationCity;
                                                   link.factor = 0.2;
                                                   link.between = [value.id+'-'+value.originationCity, value.id+'-'+value.destinationCity];
                                                   link.attrs = {
                                                                "stroke": "#ffffff",
                                                                "stroke-width": 2,
                                                                "stroke-linecap": "round",
                                                                "opacity": 0.6,
                                                                "arrow-end": "classic-wide-long"
                                                            };
                                                   link.tooltip = {"content": linktitle};
                                                   links[linkobjecttitle] = link;
                                               } else {
                                                   console.warn("Ignored element " + id + " without GPS position");
                                               }
                                           });

                                    } else {

                                        // Do nothing at this time

                                    }

                                    //$('#orderCount').html(orderCount);

                               },
                               error: function() {
                                  //alert("There Was An Error Getting Data!");
                               }
                            });
                    });
            }
            // Clear and reload the map plots and links based on latest filters
            $(".mapcontainer").trigger('update', [{newPlots: plots, newLinks: links, deletePlotKeys: "all", deleteLinkKeys: "all"}]);

        }

    </script>
</head>
<body>
<!--
  Main sidebar seen on the left. may be static or collapsing depending on selected state.

    * Collapsing - navigation automatically collapse when mouse leaves it and expand when enters.
    * Static - stays always open.
-->
<nav id="sidebar" class="sidebar" role="navigation">
    <!-- need this .js class to initiate slimscroll -->
    <div class="js-sidebar-content">
        <header class="logo hidden-sm-down">
            <img src="img/nec_logo.png" width="120"/>
            <!--a href="/">NEC</a-->
        </header>
        <!-- seems like lots of recent admin template have this feature of user info in the sidebar.
             looks good, so adding it and enhancing with notifications -->
        <div class="sidebar-status hidden-md-up">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <span class="thumb-sm avatar pull-xs-right">
                    <img class="img-circle" src="demo/img/people/a5.jpg" alt="...">
                </span>
                <!-- .circle is a pretty cool way to add a bit of beauty to raw data.
                     should be used with bg-* and text-* classes for colors -->
                <span class="circle bg-warning fw-bold text-gray-dark">
                    13
                </span>
                &nbsp;
                <?php echo $firstName; ?> <strong><?php echo $lastName; ?></strong>
                <b class="caret"></b>
            </a>
            <!-- #notifications-dropdown-menu goes here when screen collapsed to xs or sm -->
        </div>
        <!-- main notification links are placed inside of .sidebar-nav -->
        <ul class="sidebar-nav">
            <li class="active">
                <!-- an example of nested submenu. basic bootstrap collapse component -->
                <!--a href="#sidebar-dashboard" data-toggle="collapse" data-parent="#sidebar"-->
                <a href="/dashboard">
                    <span class="icon">
                        <i class="fa fa-desktop"></i>
                    </span>
<?php
    if ($_SESSION['entitytype'] == 1) {
        echo "Customer ";
    } else if ($_SESSION['entitytype'] == 2) {
        echo "Carrier ";
    }
?>
                    Dashboard</a>
                    <!--i class="toggle fa fa-angle-down"></i>
                </a>
                <ul id="sidebar-dashboard" class="collapse in">
                    <li class="active"><a href="/">Dashboard</a></li>
                    <li><a href="/"><i>(Mashup)</i></a></li>
                </ul-->
            </li>
            <li>
                <span class="icon">
                    <i></i>
                </span>
                <?php echo "<b>".$_SESSION['usertypename']."</b>"; ?>
            </li>
<?php

    if ( ($_SESSION['entitytype'] == 1 || $_SESSION['entityid'] == 0) && in_array($_SESSION['usertypeid'], $needsMenuAccessList) ) {
?>
            <li>
                <a href="#" onclick="ajaxFormCall('listNeeds');">
                    <span class="icon">
                        <i class="fa fa-truck"></i>
                    </span>
                    Needs
                    <span class="label label-danger">
                        <?php
                            if ( $_SESSION['entitytype'] == 1 ) {
                                echo $cncount;
                            } elseif ( $_SESSION['entityid'] == 0 ) {
                                echo $carrierncount;
                            }
                        ?>
                    </span>
                </a>
            </li>
<?php
    }

    if ( $_SESSION['entityid'] == 0 ) {
 ?>
             <li>
                 <a href="#" style="line-height: 20px; padding-bottom: 15px;" onclick="ajaxFormCall('listAvailability');">
                     <span class="icon">
                         <i class="fa fa-users"></i>
                     </span>
                     One Way Trailer Opportunities
                     <span class="label label-danger">
                         <?php
                            echo $customerncount;
                        ?>
                     </span>
                 </a>
             </li>
<?php
    }

    if ( ($_SESSION['entitytype'] == 2) && in_array($_SESSION['usertypeid'], $needsMenuAccessList) ) {
 ?>
             <li>
                 <a href="#" style="line-height: 20px; padding-bottom: 15px;" onclick="ajaxFormCall('listAvailability');">
                     <span class="icon">
                         <i class="fa fa-users"></i>
                     </span>
                     My One Way Opportunities
                     <span class="label label-danger">
                         <?php
                            echo $cncount;
                        ?>
                     </span>
                 </a>
             </li>
 <?php
    }

    if ($_SESSION['entityid'] == 0) {
 ?>
             <li>
                 <a href="#" onclick="ajaxFormCall('listCommitment');">
                     <span class="icon">
                         <i class="fa fa-thumbs-up"></i>
                     </span>
                     Commitment
                     <span id="commitmentCount" class="label label-danger">

                     </span>
                 </a>
             </li>
 <?php
    }

    if ($_SESSION['entityid'] == 0) {
 ?>

 <?php
    }

    if ( ($_SESSION['entityid'] == 0 ) || ($_SESSION['entitytype'] == 2) && in_array($_SESSION['usertypeid'], $ordersMenuAccessList)) { // Let NEC Admin and Carriers see it as Orders
 ?>
            <li>
                <a href="#" onclick="ajaxFormCall('listOrders');">
                    <span class="icon">
                        <i class="fa fa-check-square-o"></i>
                    </span>
                    Orders
                    <span id="orderCount" class="label label-danger">

                    </span>
                </a>
            </li>
<?php
    }

    if ( ($_SESSION['entitytype'] == 1) && in_array($_SESSION['usertypeid'], $ordersMenuAccessList) ) {
 ?>
            <li>
                <a href="#" onclick="ajaxFormCall('listOrders');">
                    <span class="icon">
                        <i class="fa fa-check-square-o"></i>
                    </span>
                    My Trailers in Route
                    <span id="orderCount" class="label label-danger">

                    </span>
                </a>
            </li>
<?php
    }

    if ( ($_SESSION['entitytype'] == 2 || $_SESSION['entityid'] == 0 ) && in_array($_SESSION['usertypeid'], $invoicingMenuAccessList) ) {
 ?>
            <li>
                <a href="#">
                    <span class="icon">
                        <i class="fa fa-dollar"></i>
                    </span>
                    Invoicing
                </a>
            </li>
<?php
    }

    if ( ($_SESSION['entitytype'] == 2 || $_SESSION['entityid'] == 0 ) && in_array($_SESSION['usertypeid'], $claimsMenuAccessList) ) {
 ?>
            <li>
                <a href="#">
                    <span class="icon">
                        <i class="fa fa-thumbs-down"></i>
                    </span>
                    Damage Claims
                </a>
            </li>
<?php
    }

    if ( ($_SESSION['entitytype'] == 2 || $_SESSION['entityid'] == 0 ) && in_array($_SESSION['usertypeid'], $collectionsMenuAccessList) ) {
 ?>
            <!--
             # Menu is being hidden

            <li>
                <a href="#">
                    <span class="icon">
                        <i class="fa fa-money"></i>
                    </span>
                    Collections
                </a>
            </li>

             -->
<?php
    }
?>
        </ul>
        <!-- every .sidebar-nav may have a title -->
        <!--h5 class="sidebar-nav-title">&nbsp; <a class="action-link" href="#"><i class="glyphicon glyphicon-refresh"></i></a></h5-->
        <ul class="sidebar-nav">
<?php
?>
            <li>
                <!-- an example of nested submenu. basic bootstrap collapse component -->
                <a class="collapsed" href="#sidebar-forms" data-toggle="collapse" data-parent="#sidebar">
                    <span class="icon">
                        <i class="glyphicon glyphicon-align-right"></i>
                    </span>
                    Profiles
                    <i class="toggle fa fa-angle-down"></i>
                </a>
                <ul id="sidebar-forms" class="collapse">

                    <?php
                    if ( in_array($_SESSION['usertypeid'], $profilesMenuAccessList) ) { // Determine is user type id has access to this menu item
                        if ($_SESSION['entityid'] == 0) {
                        ?>
                            <li><a href="#" onclick="ajaxFormCall('listBusinesses');">Businesses</a></li>
                        <?php
                        } else {
                        ?>
                            <li><a href="#" onclick="ajaxFormCall('businessProfile');">Business</a></li>
                        <?php
                        }

                    }
                    ?>
                    <?php
                        if ($_SESSION['usertypeid'] < 2) {
                          echo "<li><a href=\"#\" onclick=\"ajaxFormCall('listUsers');\">Users</a></li>";
                        }
                    ?>

                    <li><a href="#" onclick="ajaxFormCall('listContacts');">Contacts</a></li>
                    <li><a class="collapsed" href="#sidebar-sub-levels" data-toggle="collapse" data-parent="#sidebar-levels">
                              Location
                              <i class="toggle fa fa-angle-down"></i>
                        </a>
                      <ul id="sidebar-sub-levels" class="collapse">
                          <li><a href="#" onclick="ajaxFormCall('listLocationTypes');">Location Types</a></li>
                          <li><a href="#" onclick="ajaxFormCall('listLocations');">Locations</a></li>
                      </ul>
                    </li>

                    <?php
                        if ($_SESSION['entitytype'] == 2) {
                          //echo "<li><a href=\"#\" onclick=\"ajaxFormCall('ratesProfile');\">Rates</a></li>";
                        } else if ($_SESSION['entitytype'] == 0) {
                          // Must be NEC Admin So Show it all...
                          //echo "<li><a href=\"#\" onclick=\"ajaxFormCall('listRates');\">Rates</a></li>";
                        }
                    ?>

                    <?php
                    if ( in_array($_SESSION['usertypeid'], $profilesMenuAccessList) ) { // Determine is user type id has access to this menu item

                        if ($_SESSION['entitytype'] == 1) {
                          //echo "<li><a href=\"#\" onclick=\"ajaxFormCall('listTrailers');\">Trailers</a></li>";
                        } else if ($_SESSION['entitytype'] == 2) {
                          echo "<li><a href=\"#\" onclick=\"ajaxFormCall('listInsurance');\">Insurance</a></li>";
                        } else {
                          // Must be NEC Admin So Show it all...
                          //echo "<li><a href=\"#\" onclick=\"ajaxFormCall('listTrailers');\">Trailers</a></li>";
                          echo "<li><a href=\"#\" onclick=\"ajaxFormCall('listInsurance');\">Insurance</a></li>";
                        }
                    }
                    ?>
                    <li><a href="#" onclick="ajaxFormCall('listLinks');">Links</a></li>
                </ul>
            </li>
<?php

    if ( ($_SESSION['entityid'] == 0) ) {
?>
                        <li>
                            <a href="#" onclick="ajaxFormCall('listCustomerNeeds');">
                                <span class="icon">
                                    <i class="fa fa-users"></i>
                                </span>
                                Availablity
                            </a>
                        </li>
<?php
    } // End check for profilesMenuAccessList

    if ( ($_SESSION['entitytype'] == 1) && in_array($_SESSION['usertypeid'], $myavailabilityMenuAccessList) ) {
?>
                        <li>
                            <a style="line-height:20px;padding-bottom:15px;" href="#" onclick="ajaxFormCall('listCustomerNeeds');">
                                <span class="icon">
                                    <i class="fa fa-users"></i>
                                </span>
                                Manage My Availablity
                            </a>
                        </li>
<?php
    }

    if ( ($_SESSION['entityid'] == 0) ) {
 ?>
                         <li>
                             <a href="#" onclick="ajaxFormCall('listCarrierNeeds');">
                                 <span class="icon">
                                     <i class="fa fa-truck"></i>
                                 </span>
                                 Needs
                             </a>
                         </li>
<?php
    }

    if ( ($_SESSION['entitytype'] == 2)  && in_array($_SESSION['usertypeid'], $myneedsMenuAccessList) ) {
 ?>
                         <li>
                             <a href="#" onclick="ajaxFormCall('listCarrierNeeds');">
                                 <span class="icon">
                                     <i class="fa fa-truck"></i>
                                 </span>
                                 Manage My Needs
                             </a>
                         </li>
 <?php
    }

    if ( ($_SESSION['entityid'] == 0)  && in_array($_SESSION['usertypeid'], $mapsMenuAccessList) ) {
 ?>


            <li>
                <a class="collapsed" href="#sidebar-maps" data-toggle="collapse" data-parent="#sidebar">
                    <span class="icon">
                        <i class="glyphicon glyphicon-map-marker"></i>
                    </span>
                    Maps
                    <i class="toggle fa fa-angle-down"></i>
                </a>
                <ul id="sidebar-maps" class="collapse">
                    <!-- data-no-pjax turns off pjax loading for this link. Use in case of complicated js loading on the
                         target page -->
                    <li><a href="#" onclick="ajaxFormCall('listGoogleMaps');" data-no-pjax>Google Maps</a></li>
                    <li><a href="maps_vector.html">Vector Maps</a></li>
                </ul>
            </li>
<?php
    }

    if ( ($_SESSION['entityid'] == 0)  && in_array($_SESSION['usertypeid'], $settingsMenuAccessList) ) {
 ?>
            <li>
                <a class="collapsed" href="#sidebar-settings" data-toggle="collapse" data-parent="#sidebar">
                    <span class="icon">
                        <i class="fa fa-gear"></i>
                    </span>
                    Settings
                    <i class="toggle fa fa-angle-down"></i>
                </a>
                <ul id="sidebar-settings" class="collapse">
                    <!-- data-no-pjax turns off pjax loading for this link. Use in case of complicated js loading on the
                         target page -->
               <?php
                   if ($_SESSION['entitytype'] == 1) {
                     echo "<li><a href=\"#\" onclick=\"ajaxFormCall('listTrailerSpecs');\">Trailer Specs</a></li>";
                   } else if ($_SESSION['entitytype'] == 2) {
                     echo "<li><a href=\"#\" onclick=\"ajaxFormCall('listTrailerSpecs');\">Trailer Specs</a></li>";
                   } else {
                     // Must be NEC Admin So Show it all...
                     echo "<li><a href=\"#\" onclick=\"ajaxFormCall('listTrailerSpecs');\">Trailer Specs</a></li>";
                   }
               ?>
                </ul>
            </li>
<?php
    }
?>
        </ul>
    </div>
</nav>
<!-- This is the white navigation bar seen on the top. A bit enhanced BS navbar. See .page-controls in _base.scss. -->
<nav class="page-controls navbar navbar-dashboard">
    <div class="container-fluid">
        <!-- .navbar-header contains links seen on xs & sm screens -->
        <div class="navbar-header">
            <ul class="nav navbar-nav">
                <li class="nav-item">
                    <!-- whether to automatically collapse sidebar on mouseleave. If activated acts more like usual admin templates -->
                    <a class="hidden-md-down nav-link" id="nav-state-toggle" href="#" data-toggle="tooltip" data-html="true" data-original-title="Turn<br>on/off<br>sidebar<br>collapsing" data-placement="bottom">
                        <i class="fa fa-bars fa-lg"></i>
                    </a>
                    <!-- shown on xs & sm screen. collapses and expands navigation -->
                    <a class="hidden-lg-up nav-link" id="nav-collapse-toggle" href="#" data-html="true" title="Show/hide<br>sidebar" data-placement="bottom">
                        <span class="rounded rounded-lg bg-gray text-white hidden-md-up"><i class="fa fa-bars fa-lg"></i></span>
                        <i class="fa fa-bars fa-lg hidden-sm-down"></i>
                    </a>
                </li>
                <li class="nav-item hidden-sm-down"><a href="#" class="nav-link"><i class="fa fa-refresh fa-lg"></i></a></li>
                <li class="nav-item ml-n-xs hidden-sm-down"><a href="#" class="nav-link"><i class="fa fa-times fa-lg"></i></a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right hidden-md-up">
                <li>
                    <!-- toggles chat -->
                    <a href="#" data-toggle="chat-sidebar">
                        <span class="rounded rounded-lg bg-gray text-white"><i class="fa fa-globe fa-lg"></i></span>
                    </a>
                </li>
            </ul>
            <!-- xs & sm screen logo -->
            <a class="navbar-brand hidden-md-up" href="index.html">
                <i class="fa fa-circle text-gray mr-n-sm"></i>
                <i class="fa fa-circle text-warning"></i>
                &nbsp;
                sing
                &nbsp;
                <i class="fa fa-circle text-warning mr-n-sm"></i>
                <i class="fa fa-circle text-gray"></i>
            </a>
        </div>

        <!-- this part is hidden for xs screens -->
        <div class="collapse navbar-collapse">
            <!-- search form! link it to your search server -->
            <form class="navbar-form pull-xs-left" role="search">
                <div class="form-group">
                    <div class="input-group input-group-no-border">
                    <span class="input-group-addon">
                        <i class="fa fa-search"></i>
                    </span>
                        <input class="form-control" type="text" placeholder="Search Dashboard">
                    </div>
                </div>
            </form>

            <ul class="nav navbar-nav pull-xs-right">
                <li class="nav navbar-form pull-xs-left">
                    <span class="form-control"><strong><?php echo $entityname; ?></strong></span>&nbsp;&nbsp;
                </li>
                <li class="dropdown nav-item">
                    <a href="#" class="dropdown-toggle dropdown-toggle-notifications nav-link" id="notifications-dropdown-toggle" data-toggle="dropdown">
                        <span class="thumb-sm avatar pull-xs-left">
                            <img class="img-circle" src="demo/img/people/a5.jpg" alt="...">
                        </span>
                        &nbsp;
                        <?php echo $firstName; ?> <strong><?php echo $lastName; ?></strong>&nbsp;
                        <span class="circle bg-warning fw-bold">
                            13
                        </span>
                        <b class="caret"></b></a>
                    <!-- ready to use notifications dropdown.  inspired by smartadmin template.
                         consists of three components:
                         notifications, messages, progress. leave or add what's important for you.
                         uses Sing's ajax-load plugin for async content loading. See #load-notifications-btn -->
                    <div class="dropdown-menu dropdown-menu-right animated fadeInUp" id="notifications-dropdown-menu">
                        <section class="card notifications">
                            <header class="card-header">
                                <div class="text-xs-center mb-sm">
                                    <strong>You have 13 notifications</strong>
                                </div>
                                <div class="btn-group btn-group-sm btn-group-justified" id="notifications-toggle" data-toggle="buttons">
                                    <label class="btn btn-secondary active">
                                        <!-- ajax-load plugin in action. setting data-ajax-load & data-ajax-target is the
                                             only requirement for async reloading -->
                                        <input type="radio" checked
                                               data-ajax-trigger="change"
                                               data-ajax-load="demo/ajax/notifications.html"
                                               data-ajax-target="#notifications-list"> Notifications
                                    </label>
                                    <label class="btn btn-secondary">
                                        <input type="radio"
                                               data-ajax-trigger="change"
                                               data-ajax-load="demo/ajax/messages.html"
                                               data-ajax-target="#notifications-list"> Messages
                                    </label>
                                    <label class="btn btn-secondary">
                                        <input type="radio"
                                               data-ajax-trigger="change"
                                               data-ajax-load="demo/ajax/progress.html"
                                               data-ajax-target="#notifications-list"> Progress
                                    </label>
                                </div>
                            </header>
                            <!-- notification list with .thin-scroll which styles scrollbar for webkit -->
                            <div id="notifications-list" class="list-group thin-scroll">
                                <div class="list-group-item">
                                <span class="thumb-sm pull-xs-left mr clearfix">
                                    <img class="img-circle" src="demo/img/people/a3.jpg" alt="...">
                                </span>
                                    <p class="no-margin overflow-hidden">
                                        1 new user just signed up! Check out
                                        <a href="#">Monica Smith</a>'s account.
                                        <time class="help-block no-margin">
                                            2 mins ago
                                        </time>
                                    </p>
                                </div>
                                <a class="list-group-item" href="#">
                                <span class="thumb-sm pull-xs-left mr">
                                    <i class="glyphicon glyphicon-upload fa-lg"></i>
                                </span>
                                    <p class="text-ellipsis no-margin">
                                        2.1.0-pre-alpha just released. </p>
                                    <time class="help-block no-margin">
                                        5h ago
                                    </time>
                                </a>
                                <a class="list-group-item" href="#">
                                <span class="thumb-sm pull-xs-left mr">
                                    <i class="fa fa-bolt fa-lg"></i>
                                </span>
                                    <p class="text-ellipsis no-margin">
                                        Server load limited. </p>
                                    <time class="help-block no-margin">
                                        7h ago
                                    </time>
                                </a>
                                <div class="list-group-item">
                                <span class="thumb-sm pull-xs-left mr clearfix">
                                    <img class="img-circle" src="demo/img/people/a5.jpg" alt="...">
                                </span>
                                    <p class="no-margin overflow-hidden">
                                        User <a href="#">Jeff</a> registered
                                        &nbsp;&nbsp;
                                        <a class="label label-success">Allow</a>
                                        <a class="label label-danger">Deny</a>
                                        <time class="help-block no-margin">
                                            12:18 AM
                                        </time>
                                    </p>
                                </div>
                                <div class="list-group-item">
                                    <span class="thumb-sm pull-xs-left mr">
                                        <i class="fa fa-shield fa-lg"></i>
                                    </span>
                                    <p class="no-margin overflow-hidden">
                                        Instructions for changing your Envato Account password. Please
                                        check your account <a href="#">security page</a>.
                                        <time class="help-block no-margin">
                                            12:18 AM
                                        </time>
                                    </p>
                                </div>
                                <a class="list-group-item" href="#">
                                <span class="thumb-sm pull-xs-left mr">
                                    <span class="rounded bg-primary rounded-lg">
                                        <i class="fa fa-facebook text-white"></i>
                                    </span>
                                </span>
                                    <p class="text-ellipsis no-margin">
                                        New <strong>76</strong> facebook likes received.</p>
                                    <time class="help-block no-margin">
                                        15 Apr 2014
                                    </time>
                                </a>
                                <a class="list-group-item" href="#">
                                <span class="thumb-sm pull-xs-left mr">
                                    <span class="circle circle-lg bg-gray-dark">
                                        <i class="fa fa-circle-o text-white"></i>
                                    </span>
                                </span>
                                    <p class="text-ellipsis no-margin">
                                        Dark matter detected.</p>
                                    <time class="help-block no-margin">
                                        15 Apr 2014
                                    </time>
                                </a>
                            </div>
                            <footer class="card-footer text-sm">
                                <!-- ajax-load button. loads demo/ajax/notifications.php to #notifications-list
                                     when clicked -->
                                <button class="btn-label btn-link pull-xs-right"
                                        id="load-notifications-btn"
                                        data-ajax-load="demo/ajax/notifications.php"
                                        data-ajax-target="#notifications-list"
                                        data-loading-text="<i class='fa fa-refresh fa-spin mr-xs'></i> Loading...">
                                    <i class="fa fa-refresh"></i>
                                </button>
                                <span>Synced at: 21 Apr 2014 18:36</span>
                            </footer>
                        </section>
                    </div>
                </li>
                <li class="dropdown nav-item">
                    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                        <i class="fa fa-cog fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><a class="dropdown-item" href="profile.html"><i class="glyphicon glyphicon-user"></i> &nbsp; My Account</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="calendar.html">Calendar</a></li>
                        <li><a class="dropdown-item" href="inbox.html">Inbox &nbsp;&nbsp;<span class="label label-pill label-danger animated bounceIn">9</span></a></li>
                        <li class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout"><i class="fa fa-sign-out"></i> &nbsp; Log Out</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-toggle="chat-sidebar">
                        <i class="fa fa-globe fa-lg"></i>
                    </a>
                    <div id="chat-notification" class="chat-notification hide">
                        <div class="chat-notification-inner">
                            <h6 class="title">
                                <span class="thumb-xs">
                                    <img src="demo/img/people/a6.jpg" class="img-circle mr-xs pull-xs-left">
                                </span>
                                Jess Smith
                            </h6>
                            <p class="text">Hey! What's up?</p>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="chat-sidebar" id="chat">
    <div class="chat-sidebar-content">
        <header class="chat-sidebar-header">
            <h5 class="chat-sidebar-title">Contacts</h5>
            <div class="form-group no-margin">
                <div class="input-group input-group-dark">
                    <input class="form-control fs-mini" id="chat-sidebar-search" type="text" placeholder="Search...">
                    <span class="input-group-addon">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
            </div>
        </header>
        <div class="chat-sidebar-contacts chat-sidebar-panel open">
            <h5 class="sidebar-nav-title">Today</h5>
            <div class="list-group chat-sidebar-user-group">
                <a class="list-group-item" href="#chat-sidebar-user-1">
                    <i class="fa fa-circle text-success pull-xs-right"></i>
                    <span class="thumb-sm pull-xs-left mr">
                        <img class="img-circle" src="demo/img/people/a2.jpg" alt="...">
                    </span>
                    <h6 class="message-sender">Chris Gray</h6>
                    <p class="message-preview">Hey! What's up? So many times since we</p>
                </a>
                <a class="list-group-item" href="#chat-sidebar-user-2">
                    <i class="fa fa-circle text-gray-light pull-xs-right"></i>
                <span class="thumb-sm pull-xs-left mr">
                    <img class="img-circle" src="img/avatar.png" alt="...">
                </span>
                    <h6 class="message-sender">Jamey Brownlow</h6>
                    <p class="message-preview">Good news coming tonight. Seems they agreed to proceed</p>
                </a>
                <a class="list-group-item" href="#chat-sidebar-user-3">
                    <i class="fa fa-circle text-danger pull-xs-right"></i>
                <span class="thumb-sm pull-xs-left mr">
                    <img class="img-circle" src="demo/img/people/a1.jpg" alt="...">
                </span>
                    <h6 class="message-sender">Livia Walsh</h6>
                    <p class="message-preview">Check out my latest email plz!</p>
                </a>
                <a class="list-group-item" href="#chat-sidebar-user-4">
                    <i class="fa fa-circle text-gray-light pull-xs-right"></i>
                <span class="thumb-sm pull-xs-left mr">
                    <img class="img-circle" src="img/avatar.png" alt="...">
                </span>
                    <h6 class="message-sender">Jaron Fitzroy</h6>
                    <p class="message-preview">What about summer break?</p>
                </a>
                <a class="list-group-item" href="#chat-sidebar-user-5">
                    <i class="fa fa-circle text-success pull-xs-right"></i>
                <span class="thumb-sm pull-xs-left mr">
                    <img class="img-circle" src="demo/img/people/a4.jpg" alt="...">
                </span>
                    <h6 class="message-sender">Mike Lewis</h6>
                    <p class="message-preview">Just ain't sure about the weekend now. 90% I'll make it.</p>
                </a>
            </div>
            <h5 class="sidebar-nav-title">Last Week</h5>
            <div class="list-group chat-sidebar-user-group">
                <a class="list-group-item" href="#chat-sidebar-user-6">
                    <i class="fa fa-circle text-gray-light pull-xs-right"></i>
                <span class="thumb-sm pull-xs-left mr">
                    <img class="img-circle" src="demo/img/people/a6.jpg" alt="...">
                </span>
                    <h6 class="message-sender">Freda Edison</h6>
                    <p class="message-preview">Hey what's up? Me and Monica going for a lunch somewhere. Wanna join?</p>
                </a>
                <a class="list-group-item" href="#chat-sidebar-user-7">
                    <i class="fa fa-circle text-success pull-xs-right"></i>
                <span class="thumb-sm pull-xs-left mr">
                    <img class="img-circle" src="demo/img/people/a5.jpg" alt="...">
                </span>
                    <h6 class="message-sender">Livia Walsh</h6>
                    <p class="message-preview">Check out my latest email plz!</p>
                </a>
                <a class="list-group-item" href="#chat-sidebar-user-8">
                    <i class="fa fa-circle text-warning pull-xs-right"></i>
                <span class="thumb-sm pull-xs-left mr">
                    <img class="img-circle" src="demo/img/people/a3.jpg" alt="...">
                </span>
                    <h6 class="message-sender">Jaron Fitzroy</h6>
                    <p class="message-preview">What about summer break?</p>
                </a>
                <a class="list-group-item" href="#chat-sidebar-user-9">
                    <i class="fa fa-circle text-gray-light pull-xs-right"></i>
                <span class="thumb-sm pull-xs-left mr">
                    <img class="img-circle" src="img/avatar.png" alt="...">
                </span>
                    <h6 class="message-sender">Mike Lewis</h6>
                    <p class="message-preview">Just ain't sure about the weekend now. 90% I'll make it.</p>
                </a>
            </div>
        </div>
        <div class="chat-sidebar-chat chat-sidebar-panel" id="chat-sidebar-user-1">
            <h6 class="title">
                <a class="js-back" href="#">
                    <i class="fa fa-angle-left mr-xs"></i>
                    Chris Gray
                </a>
            </h6>
            <ul class="message-list">
                <li class="message">
                    <span class="thumb-sm">
                        <img class="img-circle" src="demo/img/people/a2.jpg" alt="...">
                    </span>
                    <div class="message-body">
                        Hey! What's up?
                    </div>
                </li>
                <li class="message">
                    <span class="thumb-sm">
                        <img class="img-circle" src="demo/img/people/a2.jpg" alt="...">
                    </span>
                    <div class="message-body">
                        Are you there?
                    </div>
                </li>
                <li class="message">
                    <span class="thumb-sm">
                        <img class="img-circle" src="demo/img/people/a2.jpg" alt="...">
                    </span>
                    <div class="message-body">
                        Let me know when you come back.
                    </div>
                </li>
                <li class="message from-me">
                    <span class="thumb-sm">
                        <img class="img-circle" src="img/avatar.png" alt="...">
                    </span>
                    <div class="message-body">
                        I am here!
                    </div>
                </li>
            </ul>
        </div>
        <div class="chat-sidebar-chat chat-sidebar-panel" id="chat-sidebar-user-2">
            <h6 class="title">
                <a class="js-back" href="#">
                    <i class="fa fa-angle-left mr-xs"></i>
                    Jamey Brownlow
                </a>
            </h6>
            <ul class="message-list">
            </ul>
        </div>
        <div class="chat-sidebar-chat chat-sidebar-panel" id="chat-sidebar-user-3">
            <h6 class="title">
                <a class="js-back" href="#">
                    <i class="fa fa-angle-left mr-xs"></i>
                    Livia Walsh
                </a>
            </h6>
            <ul class="message-list">
            </ul>
        </div>
        <div class="chat-sidebar-chat chat-sidebar-panel" id="chat-sidebar-user-4">
            <h6 class="title">
                <a class="js-back" href="#">
                    <i class="fa fa-angle-left mr-xs"></i>
                    Jaron Fitzroy
                </a>
            </h6>
            <ul class="message-list">
            </ul>
        </div>
        <div class="chat-sidebar-chat chat-sidebar-panel" id="chat-sidebar-user-5">
            <h6 class="title">
                <a class="js-back" href="#">
                    <i class="fa fa-angle-left mr-xs"></i>
                    Mike Lewis
                </a>
            </h6>
            <ul class="message-list">
            </ul>
        </div>
        <div class="chat-sidebar-chat chat-sidebar-panel" id="chat-sidebar-user-6">
            <h6 class="title">
                <a class="js-back" href="#">
                    <i class="fa fa-angle-left mr-xs"></i>
                    Freda Edison
                </a>
            </h6>
            <ul class="message-list">
            </ul>
        </div>
        <div class="chat-sidebar-chat chat-sidebar-panel" id="chat-sidebar-user-7">
            <h6 class="title">
                <a class="js-back" href="#">
                    <i class="fa fa-angle-left mr-xs"></i>
                    Livia Walsh
                </a>
            </h6>
            <ul class="message-list">
            </ul>
        </div>
        <div class="chat-sidebar-chat chat-sidebar-panel" id="chat-sidebar-user-8">
            <h6 class="title">
                <a class="js-back" href="#">
                    <i class="fa fa-angle-left mr-xs"></i>
                    Jaron Fitzroy
                </a>
            </h6>
            <ul class="message-list">
            </ul>
        </div>
        <div class="chat-sidebar-chat chat-sidebar-panel" id="chat-sidebar-user-9">
            <h6 class="title">
                <a class="js-back" href="#">
                    <i class="fa fa-angle-left mr-xs"></i>
                    Mike Lewis
                </a>
            </h6>
            <ul class="message-list">
            </ul>
        </div>
        <footer class="chat-sidebar-footer form-group">
            <input class="form-control input-dark" id="chat-sidebar-input" type="text"  placeholder="Type your message">
        </footer>
    </div>
</div>

<div class="content-wrap">
    <!-- main page content. the place to put widgets in. usually consists of .row > .col-lg-* > .widget.  -->
    <main id="maincontent" class="content" role="main">
        <!-- h1 class="page-title">Dashboard <small><small>The Lucky One</small></small></h1 -->

        <!-- Default Load -->
        <div class="row">
            <div class="col-lg-8">
                <!-- minimal widget consist of .widget class. note bg-transparent - it can be any background like bg-gray,
                bg-primary, bg-white -->
                <section class="widget bg-transparent">
                    <!-- .widget-body is a mostly semantic class. may be a sibling to .widget>header or .widget>footer -->
                    <div class="widget-body">
                        <div class="mapcontainer">
                              <div class="map">
                                  <span>Prepare map...</span>
                              </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-lg-4">
                <section class="widget bg-transparent">
                    <header>
                        <h5>
                            Map
                            <span class="fw-semi-bold">Statistics</span>
                        </h5>
                        <div class="widget-controls widget-controls-hover">
                            <a href="#"><i class="glyphicon glyphicon-cog"></i></a>
                            <a href="#"><i class="fa fa-refresh"></i></a>
                            <a href="#" data-widgster="close"><i class="glyphicon glyphicon-remove"></i></a>
                        </div>
                    </header>
                    <div class="widget-body">
                        <p>Status: <strong>Live</strong></p>
                        <p>
                            <span class="circle bg-warning"><i class="fa fa-map-marker"></i></span>
                            146 Active Locations
                        </p>
                        <div class="row progress-stats">
                            <div class="col-md-9">
                                <h6 class="name m-t-1">Needs</h6>
                                <p class="description deemphasize">open needs</p>
                                <div class="bg-white progress-bar">
                                    <progress class="progress progress-primary progress-sm js-progress-animate" value="100" max="100" style="width: 0%" data-width="60%"></progress>
                                </div>
                            </div>
                            <div class="col-md-3 text-xs-center">
                                <!--span class="status rounded rounded-lg bg-body-light"-->
                                <span class="label label-pill label-primary">
                                    <small><span id="percent-1">63</span></small>
                                </span>
                            </div>
                        </div>
                        <div class="row progress-stats">
                            <div class="col-md-9">
                                <h6 class="name m-t-1">Orders</h6>
                                <p class="description deemphasize">current open orders</p>
                                <div class="bg-white progress-bar">
                                    <progress class="progress progress-sm progress-success js-progress-animate" value="100" max="100" style="width: 0%" data-width="12%"></progress>
                                </div>
                            </div>
                            <div class="col-md-3 text-xs-center">
                                <!--span class="status rounded rounded-lg bg-body-light"-->
                                <span class="label label-pill label-success">
                                    <small><span  id="percent-2">12</span></small>
                                </span>
                            </div>
                        </div>
                        <div class="row progress-stats">
                            <div class="col-md-9">
                                <h6 class="name m-t-1">Availablity</h6>
                                <p class="description deemphasize">available trailers</p>
                                <div class="bg-white progress-bar">
                                    <progress class="progress progress-sm progress-warning js-progress-animate" value="100" max="100" style="width: 0%" data-width="50%"></progress>
                                </div>
                            </div>
                            <div class="col-md-3 text-xs-center">
                                <!--span class="status rounded rounded-lg bg-body-light"-->
                                <span class="label label-pill label-warning">
                                    <small><span id="percent-3">37</span></small>
                                </span>
                            </div>
                        </div>
                        <div>
                            <hr>
                        </div>

                        <div>
                            <h5 class="fw-semi-bold mt">Filters</h5>
                            <div class="input-group mt" style="width: 100%">
                                <label for="activityFilter">Type:</label>
                                <br/>
                                  <select id="activityFilter" name="activityFilter" multiple style="width: 100%">


<?php
if ($_SESSION['entitytype'] == 0) {
?>
                                <option value="Availability">Customer Availability</option>
                                <option value="Needs">Carrier Needs</option>
                                <option value="Commitments">Commitments</option>
                                <option value="Orders" selected=selected>Orders</option>
<?php
} else if ($_SESSION['entitytype'] == 1) {
?>
                                <option value="Availability" selected=selected>My Availability</option>
                                <option value="Needs">Carrier Needs</option>
                                <option value="Commitments">Commitments</option>
                                <option value="Orders">My Orders</option>
<?php
} else if ($_SESSION['entitytype'] == 2) {
?>
                                <option value="Availability">Customer Availability</option>
                                <option value="Needs" selected=selected>My Needs</option>
                                <option value="Commitments">My Commitments</option>
                                <option value="Orders">My Orders</option>
<?php
}
 ?>

                                </select>

                                <br /><br />

                                <label for="stateFilter">Location Status:</label>
                                <br/>
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-default active">
                                        <input type="radio" name="locationStatus" id="locationStatus" value="Origination" checked>
                                    Origination </label>
                                    <label class="btn btn-default">
                                        <input type="radio" name="locationStatus" id="locationStatus" value="Destination">
                                    Destination </label>
                                </div>

                                <br /><br />

                                <label for="stateFilter">State(s):</label>
                                <br/>
                                  <select id="stateFilter" name="stateFilter" multiple style="width: 100%">
                                      <!--option value="">ALL</option-->


<?php
                                    foreach($stateresult['states'] as $value) {
                                       echo "<option value=" .$value['abbreviation'] . ">" . $value['name'] . "</option>\n";
                                    }
 ?>

                                </select>

                                <br /><br />

                                <label for="cityFilter">Cities: (Separate cities by comma to filter on multiple cities)</label>
                                <br/>
                                <div class="form-group" width="100%">
                                  <input type="text" class="form-control" id="cityFilter" name="cityFilter" placeholder="City to Search For" value="" />
                                </div>
                                  <!--select id="cityFilter" name="cityFilter" multiple style="width: 100%"-->
                                      <!--option value="">ALL</option-->


<?php
//                                    foreach($cityresult['customer_needs']['records'] as $value) {
//                                       echo "<option value=" .$value[0] . ">" . $value[0] . "</option>\n";
//                                       echo "<option value=" .$value[1] . ">" . $value[1] . "</option>\n";
//                                    }
 ?>

                                <!--/select-->

                            </div>
                        </div>
<!--
                        <h6 class="fw-semi-bold mt">Map Distributions</h6>
                        <p>Tracking: <strong>Active</strong></p>
                        <p>
                            <span class="circle bg-warning"><i class="fa fa-cog"></i></span>
                            391 elements installed, 84 sets
                        </p>
                        <div class="input-group mt">
                            <input type="text" class="form-control" placeholder="Search Map">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-search text-gray"></i>
                                </button>
                            </span>
                        </div>
-->
                    </div>
                </section>
            </div>

        </div>
        <!-- End Default Load -->

    </main>
</div>
<!-- The Loader. Is shown when pjax happens -->
<div class="loader-wrap hiding hide">
    <i class="fa fa-circle-o-notch fa-spin-fast"></i>
</div>

<!-- common libraries. required for every page-->
<script src="vendor/jquery/dist/jquery.min.js"></script>
<script src="vendor/jquery-pjax/jquery.pjax.js"></script>
<script src="vendor/tether/dist/js/tether.js"></script>
<script src="vendor/bootstrap/js/dist/util.js"></script>
<script src="vendor/bootstrap/js/dist/collapse.js"></script>
<script src="vendor/bootstrap/js/dist/dropdown.js"></script>
<script src="vendor/bootstrap/js/dist/button.js"></script>
<script src="vendor/bootstrap/js/dist/tooltip.js"></script>
<script src="vendor/bootstrap/js/dist/alert.js"></script>
<script src="vendor/bootstrap/js/dist/modal.js"></script>
<script src="vendor/slimScroll/jquery.slimscroll.js"></script>
<script src="vendor/widgster/widgster.js"></script>
<script src="vendor/pace.js/pace.js" data-pace-options='{ "target": ".content-wrap", "ghostTime": 1000 }'></script>
<script src="vendor/jquery-touchswipe/jquery.touchSwipe.js"></script>
<script src="js/bootstrap-fix/button.js"></script>

<!-- common app js -->
<script src="js/settings.js"></script>
<script src="js/app.js"></script>
<script src="js/common.js"></script>

<!-- page specific libs -->
<script id="test" src="vendor/underscore/underscore.js"></script>
<script src="vendor/jquery.sparkline/index.js"></script>
<script src="vendor/d3/d3.min.js"></script>
<script src="vendor/rickshaw/rickshaw.min.js"></script>
<!--script src="vendor/raphael/raphael-min.js"></script-->
<script src="vendor/jQuery-Mapael/js/raphael/raphael-min.js" charset="utf-8"></script>
<!--script src="vendor/jQuery-Mapael/js/jquery.mapael.js" charset="utf-8"></script-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mapael/2.1.0/js/jquery.mapael.js"></script>
<script src="vendor/jQuery-Mapael/js/maps/usa_states.js" charset="utf-8"></script>
<script src="vendor/jQuery-Mapael/js/maps/world_countries.js" charset="utf-8"></script>
<script src="vendor/bootstrap/js/dist/popover.js"></script>
<script src="vendor/bootstrap_calendar/bootstrap_calendar/js/bootstrap_calendar.min.js"></script>
<script src="vendor/jquery-animateNumber/jquery.animateNumber.min.js"></script>

<!-- page specific libs -->
<script src="vendor/underscore/underscore-min.js"></script>
<script src="vendor/backbone/backbone.js"></script>
<script src="vendor/backbone.paginator/lib/backbone.paginator.min.js"></script>
<script src="vendor/backgrid/lib/backgrid.js"></script>
<script src="vendor/backgrid-paginator/backgrid-paginator.js"></script>
<script src="vendor/datatables/media/js/jquery.dataTables.js"></script>
<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API ?>&callback=initMap"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/b-1.4.2/r-2.2.0/datatables.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/1.1.1/chroma.min.js" charset="utf-8"></script>

<?php if(ENVIRONMENT == 'development') { ?>
<!--script type="text/javascript" src="vendor/datatables/media/js/dataTables-r-2_2_0.min.js"></script-->
<!--script type="text/javascript" src="vendor/bootstrap3-typeahead/js/bootstrap3-typeahead-4_0_2.min.js"></script-->
<!--script type="text/javascript" src="vendor/chroma/js/chroma-1_1_1.min.js"></script-->
<!--script type="text/javascript" src="vendor/googlemaps/js/googlemaps.js"></script-->
<?php } else { ?>
<!--script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/b-1.4.2/r-2.2.0/datatables.min.js"></script-->
<!--script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script-->
<!--script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/1.1.1/chroma.min.js" charset="utf-8"></script-->
<?php } ?>

<!-- Can't use or the settings gear dropdown won't work -->
<!--script src="vendor/bootstrap/dist/js/bootstrap.min.js"></script-->

<!-- page specific js -->
<script src="js/tables-dynamic.js"></script>
<script src="vendor/select2/select2.min.js"></script>map
<script src="vendor/parsleyjs/dist/parsley.min.js"></script>


<!-- page specific js -->
<!--script src="js/index.js"></script-->

<script type="text/javascript">

    countUserOrders();
    countCommitments();

    $("#activityFilter").select2().on('change', function(e) {
            getOrdersByFilters();
    });

    $("#stateFilter").select2().on('change', function(e) {
            //$("#cityFilter").each(function() {
            //    $(this).select2('val', '');
            //});
            $("#cityFilter").val('');
            getOrdersByFilters();
    });

    //$("#cityFilter").select2().on('change', function(e) {
    //        $("#stateFilter").each(function() {
    //            $(this).select2('val', '');
    //        });
    //        getOrdersByFilters();
    //});

    $("#cityFilter").on('change', function(e) {
            $("#stateFilter").each(function() {
                $(this).select2('val', '');
            });
            getOrdersByFilters();
    });

    $('input[name="locationStatus"]').on('click change', function(e) {
        getOrdersByFilters();
    });

$(function() {

   // Show loading message
   $(".mapcontainer span").html("Loading Customer Needs Locations").css({"color":"blue"});

   // Assign JS var to PHP customer needs JSON list
   var cnresult = <?php echo $cnresult; ?>;
   var carrierneedresult = <?php echo $carrierneedresult; ?>;
   var customerneedresult = <?php echo $customerneedresult; ?>;
   var locresult = <?php echo $locresult; ?>;
   var entityTypeID = <?php echo $eresult['entities'][0]['entityTypeID']; ?>;

   if ( entityTypeID == 1 ) { // Customer
       cnresult = cnresult['customer_needs'];
       var originationPlotColor = "red";
       var list = "listCustomerNeeds";
   } else if ( entityTypeID == 2 ) { // Carrier
       cnresult = cnresult['carrier_needs'];
       var originationPlotColor = "blue";
       var list = "listCarrierNeeds";
   } else {
       if (orders) {
            //cnresult = orders['orders'];
            cnresult = orders['order_details'];
       } else {
           cnresult = [];
       }
       var originationPlotColor = "green";
       var list = "listOrders";
   }

   // We need a setTimeout (~200ms) in order to allow the UI to be refreshed for the message to be shown
   setTimeout(function(){

           if (cnresult) {
               var data = cnresult;
           } else {
               var data = [];
           }

           var locations = locresult;

           // Parse each elements
           // This variable will hold all the plots of our map
           var plots = {};
           var links = {};
           var linktitle = "";
           var linkobjecttitle = "";

           if (entityTypeID > 0) {
                   var plotsColors = chroma.scale("Blues");
                   $.each(data, function (index, value) {

                       if (entityTypeID > 0) {
                           var availableDate = value.availableDate;
                           var expirationDate = value.expirationDate;
                           var expirationDateStatus = 'Closed';
                       } else {
                           var availableDate = value.pickupDate;
                           var expirationDate = value.deliveryDate;
                           var expirationDateStatus = 'Delivered';
                       }

                       // Check if we have the GPS position of the element
                       if (value.originationLat) {
                           // Setup Availability Date
                           if (value.availableDate > '') {
                               var availableDate = 'Availability Date: ' + formatDate(new Date(availableDate));
                           } else {
                               var availableDate = 'Availability Date: ' + expirationDateStatus;
                           }
                           if (value.expirationDate > '') {
                               var expirationDate = formatDate(new Date(expirationDate));
                           } else {
                               var expirationDate = expirationDateStatus;
                           }
                           // Will hold the plot information
                           var plot = {};
                           var link = {};
                           // Assign position
                           plot.latitude = parseFloat(value.originationLat);
                           plot.longitude = parseFloat(value.originationLng);
                           plot.size = 10;
                           plot.type = "circle";
                           plot.value = "H";
                           // Assign some information inside the tooltip
                           plot.tooltip = {
                               content: "<span style='font-weight:bold;'>" +
                                           "Origin: " + value.originationCity + ", " + value.originationState +
                                           "<br />" +
                                           "Dest: " + value.destinationCity + ", " + value.destinationState +
                                           "<br /># of Trailers: " +
                                           value.qty +
                                           "<br />" +
                                           availableDate +
                                           "<br />Click for more details" +
                                        "</span>"
                           };

                           plot.text = {
                                //content: qty,
                                position: "inner",
                                attrs: {
                                    "font-size": 16,
                                    "font-weight": "bold",
                                    "fill": "#fff"
                                }
                           };

                           plot.eventHandlers = {
                                click: function() {
                                        ajaxFormCall(list)
                                }
                           };

                           // Assign the background color randomize from a scale
                           plot.attrs = {
                               //fill: plotsColors(Math.random())
                               fill: originationPlotColor,
                               cursor: "pointer"
                           };

                           // Set plot element to array
                           plots[value.id+'-'+value.originationCity] = plot;

                           // Now plot the destination
                           var plot = {};
                           // Assign position
                           plot.latitude = parseFloat(value.destinationLat);
                           plot.longitude = parseFloat(value.destinationLng);
                           plot.size = 3;
                           plot.type = "";
                           // Assign some information inside the tooltip
                           plot.tooltip = {
                               content: "<span style='font-weight:bold;'>" +
                                           value.destinationCity +
                                        "</span>"
                           };

                           plot.text = {
                                //content: value.qty,
                                position: "inner",
                                attrs: {
                                    "font-size": 16,
                                    "font-weight": "bold",
                                    "fill": "#fff"
                                }
                           };

                           // Assign the background color randomize from a scale
                           plot.attrs = {
                               //fill: plotsColors(Math.random())
                               fill: "#fff"
                           };

                           // Set plot element to array
                           plots[value.id+'-'+value.destinationCity] = plot;

                           linktitle = value.originationCity+'-'+value.destinationCity;
                           linkobjecttitle = value.originationCity+value.destinationCity;
                           link.factor = 0.2;
                           //link.between = [{"latitude": value.originationLat, "longitude": value.originationLng}, {"latitude": value.destinationLat, "longitude": value.destinationLng}];
                           link.between = [value.id+'-'+value.originationCity, value.id+'-'+value.destinationCity];
                           link.attrs = {
                                        "stroke": "#a4e100",
                                        "stroke-width": 2,
                                        "stroke-linecap": "round",
                                        "opacity": 0.6,
                                        "arrow-end": "classic-wide-long"
                                    };
                           link.tooltip = {"content": linktitle};
                           links[linkobjecttitle] = link;
                       } else {
                           console.warn("Ignored element " + id + " without GPS position");
                       }
                   });

                   // Parse location elements
                   // This variable will hold all the plots of our map - ALREADY INIT ABOVE
                   //var plots = {};
/* Don't show Locations for now. Map is already too busy.
                   var plotsColors = chroma.scale("Yellows");
                   //console.log(locations);
                   $.each(locations, function (index, values) {
                       //console.log(values);
                       $.each(values, function (index, value) {
                               //console.log(value);
                               // Check if we have the GPS position of the element
                               if (value.latitude) {
                                   if (value.locationTypeID == 1) {
                                        var fontsize = 14;
                                   } else {
                                        var fontsize = 9;
                                   }
                                   // Will hold the plot information
                                   var plot = {};
                                   // Assign position
                                   plot.latitude = parseFloat(value.latitude);
                                   plot.longitude = parseFloat(value.longitude);
                                   plot.size = fontsize;
                                   plot.type = "circle";
                                   // Assign some information inside the tooltip
                                   plot.tooltip = {
                                       content: "<span style='font-weight:bold;'>" +
                                                   value.city + ', ' + value.state +
                                                "</span>"
                                   };

                                   plot.text = {
                                        //content: value.qty,
                                        position: "inner",
                                        attrs: {
                                            "font-size": 16,
                                            "font-weight": "bold",
                                            "fill": "#fff"
                                        }
                                   };

                                   // Assign the background color randomize from a scale
                                   //plot.attrs = {
                                   //    fill: plotsColors(Math.random())
                                   //};

                                   // Set plot element to array
                                   plots[value.id+'-'+value.city] = plot;
                               } else {
                                   console.warn("Ignored element " + vcolumbusalue.id + " without GPS position");
                               }

                       });
                   });
*/
           } else {

                   // Setup Orders plots
                   var plotsColors = chroma.scale("Oranges");
                   $.each(data, function (index, value) {
                           // Setup Pickup Date
                           //alert(formatDate(new Date(value.order_details[0].pickupDate)));
                           //var pickupDate = formatDate(new Date(value.order_details[0].pickupDate));
                           var pickupDate = formatDate(new Date(value.pickupDate));
                           // Check if we have the GPS position of the element
                           if (value.originationLat) {
                               // Will hold the plot information
                               var plot = {};
                               var link = {};
                               // Assign position
                               plot.latitude = parseFloat(value.originationLat);
                               plot.longitude = parseFloat(value.originationLng);
                               plot.size = 10;
                               plot.type = "circle";
                               // Assign some information inside the tooltip
                               plot.tooltip = {
                                   content: "<span style='font-weight:bold;'>" +
                                           value.originationCity + ", " + value.originationState +
                                           "<br />" +
                                           value.destinationCity + ", " + value.destinationState +
                                           "<br /># of Trailers: " +
                                           value.qty +
                                           "<br />" +
                                           "Pickup: " + pickupDate +
                                           "<br />Click for more details" +
                                        "</span>"
                               };

                               plot.text = {
                                    //content: value.qty,
                                    position: "inner",
                                    attrs: {
                                        "font-size": 16,
                                        "font-weight": "bold",
                                        "fill": "#fff"
                                    }
                               };

                               // Assign the background color randomize from a scale
                               plot.attrs = {
                                   //fill: plotsColors(Math.random())
                                   fill: "orange",
                                   cursor: "pointer"
                               };

                               plot.eventHandlers = {
                                    click: function() {
                                            ajaxFormCall(list)
                                    }
                               };

                               // Set plot element to array
                               plots[value.id+'-'+value.originationCity] = plot;

                               // Now plot the destination
                               var plot = {};
                               // Assign position
                               plot.latitude = parseFloat(value.destinationLat);
                               plot.longitude = parseFloat(value.destinationLng);
                               plot.size = 3;
                               plot.type = "";
                               // Assign some information inside the tooltip
                               plot.tooltip = {
                                   content: "<span style='font-weight:bold;'>" +
                                               value.destinationCity +
                                            "</span>"
                               };

                               plot.text = {
                                    //content: value.qty,
                                    position: "inner",
                                    attrs: {
                                        "font-size": 16,
                                        "font-weight": "bold",
                                        "fill": "#fff"
                                    }
                               };

                               // Assign the background color randomize from a scale
                               plot.attrs = {
                                   fill: plotsColors(Math.random())
                               };

                               // Set plot element to array
                               plots[value.id+'-'+value.destinationCity] = plot;

                               linktitle = value.originationCity+'-'+value.destinationCity;
                               linkobjecttitle = value.originationCity+value.destinationCity;
                               link.factor = 0.2;
                               link.between = [value.id+'-'+value.originationCity, value.id+'-'+value.destinationCity];
                               link.attrs = {
                                            "stroke": "#ffffff",
                                            "stroke-width": 2,
                                            "stroke-linecap": "round",
                                            "opacity": 0.6,
                                            "arrow-end": "classic-wide-long"
                                        };
                               link.tooltip = {"content": linktitle};
                               links[linkobjecttitle] = link;
                           } else {
                               console.warn("Ignored element " + id + " without GPS position");
                           }
                   });
           }

           // Create map
           var $map = $('.mapcontainer');//,
               //state;
           $map.mapael({
               map:{
                   name : "usa_states",
                   defaultArea : {
                       attrsHover : {
                           fill : '#242424',
                           animDuration : 100
                       },
                       tooltip: {
                           content: function(){
                               return '<strong>' + state + '</strong>';
                           }
                       },
                       eventHandlers: {
                           mouseover: function(e, id){
                               state = id;
                           }
                       }
                   },
                   defaultPlot:{
                       size: 17,
                       attrs : {
                           fill : Sing.colors['brand-warning'],
                           stroke : "#fff",
                           "stroke-width" : 0,
                           "stroke-linejoin" : "round"
                       },
                       attrsHover : {
                           "stroke-width" : 1,
                           animDuration : 100
                       }
                   },
                   zoom : {
                       enabled : true,
                       step : 0.75
                   }
               },
               plots: plots,
               links: links

           });

           //ie svg height fix
           function _fixMapHeight(){
               $map.find('svg').css('height', function(){
                   return $(this).attr('height') + 'px';
               });
           }

           _fixMapHeight();
           SingApp.onResize(function(){
               setTimeout(function(){
                   _fixMapHeight();
               }, 100)
           });

   }, 200);

});

</script>
</body>
</html>
