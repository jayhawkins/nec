<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

$state = '';
$states = json_decode(file_get_contents(API_HOST.'/api/states?columns=abbreviation,name&order=name'));

$entity = '';
$entity = json_decode(file_get_contents(API_HOST.'/api/entities?columns=rateType,negotiatedRate&filter[]=id,eq,' . $_SESSION['entityid']));

$entities = '';
$entities = json_decode(file_get_contents(API_HOST.'/api/entities?columns=id,name&order=name&filter[]=id,gt,0&filter[]=entityTypeID,eq,2'));

$carrierEntities = '';
$carrierEntities = json_decode(file_get_contents(API_HOST.'/api/entities?columns=id,name&order=name&filter[]=id,gt,0&filter[]=entityTypeID,eq,2&transform=1'));

$allEntities = '';
$allEntities = json_decode(file_get_contents(API_HOST.'/api/entities?columns=id,name&order=name&filter[]=id,gt,0&transform=1'));


$locationTypeID = '';
$locationTypes = json_decode(file_get_contents(API_HOST."/api/location_types?columns=id,name,status&filter[]=entityID,eq," . $_SESSION['entityid'] . "&filter[]=id,gt,0&satisfy=all&order=name"));

$contacts = '';
$contacts = json_decode(file_get_contents(API_HOST."/api/contacts?columns=id,firstName,lastName&order=lastName&filter=entityID,eq," . $_SESSION['entityid'] ));

$locations_contacts = '';
$locations_contacts = json_decode(file_get_contents(API_HOST."/api/locations_contacts?columns=location_id,contact_id&filter=entityID,eq," . $_SESSION['entityid'] ));

$loccon = array();
for ($lc=0;$lc<count($locations_contacts->locations_contacts->records);$lc++) {
    $loccon[$locations_contacts->locations_contacts->records[$lc][0]] = $locations_contacts->locations_contacts->records[$lc][1];
}

$dataPoints = json_decode(file_get_contents(API_HOST."/api/object_type_data_points?include=object_type_data_point_values&transform=1&columns=id,columnName,title,status,object_type_data_point_values.value&filter[]=entityID,in,(0," . $_SESSION['entityid'] . ")&filter[]=status,eq,Active" ));

$customer_needs_root = '';
$customer_needs_root = json_decode(file_get_contents(API_HOST."/api/customer_needs?columns=rootCustomerNeedsID&transform=1"));


 ?>

 <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

 <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API; ?>"></script>
 <!--script type="text/javascript" src="https://maps.google.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API; ?>"></script-->

 <script>

    var contacts = <?php echo json_encode($contacts); ?>;
    //console.log(contacts);

    var locations_contacts = <?php echo json_encode($locations_contacts); ?>;
    //console.log(locations_contacts);

    var dataPoints = <?php echo json_encode($dataPoints); ?>;
    //console.log(dataPoints);

    var entity = <?php echo json_encode($entity); ?>;
    //alert(JSON.stringify(entity));
    //console.log(JSON.stringify(entity.entities.records[0][1]));

    var entityid = <?php echo $_SESSION['entityid']; ?>;
    
    var allEntities = <?php echo json_encode($allEntities); ?>;
    
    var customerNeedsRootIDs = <?php echo json_encode($customer_needs_root)?>;
     
    var entityType = <?php echo $_SESSION['entitytype'];  ?>;
    
    var carrierEntities = <?php echo json_encode($carrierEntities); ?>;
     
    var myApp;
    myApp = myApp || (function () {
        var pleaseWaitDiv = $('<div class="modal hide" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false"><div class="modal-header"><h1>Processing...</h1></div><div class="modal-body"><div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div></div></div>');
        return {
            showPleaseWait: function() {
                pleaseWaitDiv.modal();
            },
            hidePleaseWait: function () {
                pleaseWaitDiv.modal('hide');
            }
        };
    })();

    function loadTableAJAX() {        
        
        var url = '<?php echo API_HOST; ?>';
        var blnShow = false;
        
        switch(entityType){
            case 0:     // URL for the Admin. The admin can see ALL Orders.
                url += '/api/orders?include=documents,entities&columns=id,customerID,carrierIDs,documentID,orderID,originationAddress,originationCity,originationState,originationZip,destinationAddress,destinationCity,destinationState,destinationZip,distance,needsDataPoints,status,qty,rateType,transportationMode,enitities.id,entities.name,documents.id,documents.documentURL&satisfy=all&transform=1';
                blnShow = true;
                break;
            case 1:    // URL for Customer. The Customer can only see their orders.
                url += '/api/orders?include=documents,entities&columns=id,customerID,carrierIDs,documentID,orderID,originationAddress,originationCity,originationState,originationZip,destinationAddress,destinationCity,destinationState,destinationZip,distance,needsDataPoints,status,qty,rateType,transportationMode,enitities.id,entities.name,documents.id,documents.documentURL&filter=customerID,eq,' + entityid + '&satisfy=all&transform=1';
                break;
            case 2:     // URL for the Carrier. Same as the admin but will be filtered below.
                url += '/api/orders?include=documents,entities&columns=id,customerID,carrierIDs,documentID,orderID,originationAddress,originationCity,originationState,originationZip,destinationAddress,destinationCity,destinationState,destinationZip,distance,needsDataPoints,status,qty,rateType,transportationMode,enitities.id,entities.name,documents.id,documents.documentURL&satisfy=all&transform=1';
                break;
        }        
        
        var orders_table = $('#orders-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                //dataSrc: 'orders',
                dataSrc: function ( json ) {
                    
                    var orders = json.orders;
                    
                    if(entityType == 0 || entityType == 1) return orders;   // Admin and Customer is already set
                    else {                                                  // Have to filter Carriers
                        
                        var carrierOrders = new Array();   
                        var carrier = {"carrierID": entityid};
                                            
                        orders.forEach(function(order){
                            var carrierIDs = order.carrierIDs;
                            
                            //console.log(carrierIDs, carrier);
                            
                            //if (carrierIDs.indexOf(carrier) > -1) carrierOrders.push(order);
                            
                            for(var i = 0; i < carrierIDs.length; i++){
                                carrierIDs[i].carrierID 
                                if(carrierIDs[i].carrierID == entityid){
                                    carrierOrders.push(order);
                                    break;
                                }
                            }
                            
                        });
                        
                        return carrierOrders;
                    }
                }
            },
            columns: [
                {
                    "className":      'details-control-add',
                    "orderable":      false,
                    "data":           null,
                    "defaultContent": ''
                },
                { data: "id", visible: false },
                { data: "orderID", className: 'order-details-link' },
                { data: "entities[0].name", visible: blnShow  },
                {                     
                    data: null,
                    "bSortable": true,
                    "mRender": function (o) {
                        var entityName = '';
                        var carrierIDs = o.carrierIDs;
                        
                        for(var i = 0; i < carrierIDs.length; i++){
                                                 
                            if(i > 0) entityName += ", ";
                            
                            allEntities.entities.forEach(function(entity){

                                if(carrierIDs[i].carrierID == entity.id){

                                    entityName += entity.name;
                                }                            
                            });
                            
                        }
                        
                        return entityName;
                    },
                    visible: blnShow
                },
                { data: "qty" },
                { data: "transportationMode" },
                { data: "originationAddress" },
                { data: "originationCity" },
                { data: "originationState" },
                { data: "originationZip" },
                { data: "destinationAddress" },
                { data: "destinationCity" },
                { data: "destinationState" },
                { data: "destinationZip" },
                { data: "distance", render: $.fn.dataTable.render.number(',', '.', 0, '')  },
                { data: "needsDataPoints", visible: false },
                { data: "status" },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var buttons = '';
                        var status = o.status;
                        
                        if(status == "Open"){                            
                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"fa fa-book\"></i> <span>View Order Details</span></button>';

                        }
                        else{
                            buttons += "Order Completed!" ;
                        }
                        
                        return buttons;
                    }, visible: false
                }
            ],
            scrollX: true
          });

        orders_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', orders_table.table().container() ) );
          
        //To Reload The Ajax
        //See DataTables.net for more information about the reload method
        orders_table.ajax.reload();

      }

      
    function loadOrderDetailsAJAX(orderID){
        
        var url = '<?php echo API_HOST; ?>';
        
        switch(entityType){
            case 0:     // URL for the Admin.
                url += '/api/order_details?include=orders&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID&filter=orderID,eq,' + orderID + '&transform=1';
                break;
            case 1:    // URL for Customer.
                url += '/api/order_details?include=orders&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID&filter=orderID,eq,' + orderID + '&transform=1';
                break;
            case 2:     // URL for the Carrier. The Customer can only see order details of their route.
                url += '/api/order_details?include=orders&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID&filter[]=orderID,eq,' + orderID + '&filter[]=carrierID,eq,' + entityid + '&transform=1';
                break;
        }        
        
        if ( ! $.fn.DataTable.isDataTable( '#order-details-table' ) ) {
        
            var order_details_table = $('#order-details-table').DataTable({
                retrieve: true,
                processing: true,
                ajax: {
                    url: url,
                    //dataSrc: 'order_details',
                    dataSrc: function ( json ) {

                        var order_details = json.order_details;

                        if(entityType == 2) return order_details;   // Carrier is already set
                        else {                                      // Have to manipulate Admin and Customer Data

                            var orderDetails = new Array();
                            
                            var earliestPickup = order_details[0].pickupDate;
                            var latestDelivery = order_details[0].deliveryDate;
                            
                            var originationCity = order_details[0].orders[0].originationCity;
                            var originationState = order_details[0].orders[0].originationState;
                            
                            var destinationCity = order_details[0].orders[0].destinationCity;
                            var destinationState = order_details[0].orders[0].destinationState;
                            
                            var carrierTotalRate = order_details[0].carrierRate;
                            var totalDistance = order_details[0].orders[0].distance;
                            
                            for(var i = 1; i < order_details.length; i++){
                                
                                var newPickupDate = new Date(order_details[i].pickupDate);
                                var newDeliveryDate = new Date(order_details[i].deliveryDate);
                                
                                var currentPickupDate = new Date(earliestPickup);
                                var currentDeliveryDate = new Date(latestDelivery);
                                
                                if (newPickupDate.getTime() < currentPickupDate.getTime()) {
                                    earliestPickup = order_details[i].pickupDate;
                                }
                                
                                if (newDeliveryDate.getTime() > currentDeliveryDate.getTime()) {
                                    latestDelivery = order_details[i].deliveryDate;
                                }
                                
                                carrierTotalRate += order_details[i].carrierRate;
                            }
                            
                            var orderDetail = {
                                orders: order_details[0].orders,
                                status: order_details[0].status,
                                qty: order_details[0].qty,
                                transportationMode: order_details[0].transportationMode,
                                pickupDate: earliestPickup,
                                deliveryDate: latestDelivery,
                                originationCity: originationCity,
                                originationState: originationState,
                                destinationCity: destinationCity,
                                destinationState: destinationState,
                                distance: totalDistance,
                                carrierRate: carrierTotalRate
                            };
                            
                            orderDetails.push(orderDetail);
                            
                            return orderDetails;
                        }
                    }
                },
                columns: [
                    { data: "orders[0].orderID" },
                    { data: "status" },
                    { data: "qty" },
                    { data: "transportationMode" },
                    { data: "pickupDate" },
                    { data: "deliveryDate" },
                    { data: "originationCity" },
                    { data: "originationState" },
                    { data: "destinationCity" },
                    { data: "destinationState" },
                    { data: "distance" },
                    { data: "carrierRate" }
                ]
              });

            //To Reload The Ajax
            //See DataTables.net for more information about the reload method
            order_details_table.ajax.reload();
        }
        else{
            //The URL will change with each "View Commit" button click
          // Must load new Url each time.
            var reload_table = $('#order-details-table').DataTable();
            reload_table.ajax.url(url).load();
        }
        
        $("#order-details").css("display", "block");
        $("#orders").css("display", "none");
    }
 </script>

 <style>
    /* CSS REQUIRED */
    .state-icon {
     left: -5px;
    }
    .list-group-item-primary {
     color: rgb(255, 255, 255);
     background-color: rgb(66, 139, 202);
    }

    /* DEMO ONLY - REMOVES UNWANTED MARGIN */
    .well .list-group {
     margin-bottom: 0px;
    }

    /***************** Autocomplete ***********************/

    .orgSearch {z-index: 9999}
    .destSearch {z-index: 9999}

    #origination-list{float:left;list-style:none;margin-top:-3px;padding:0;width:250px;position: inherit;}

    #origination-list li{padding: 10px; background: #f0f0f0; border-bottom: #bbb9b9 1px solid;}

    #origination-list li:hover{background:#ece3d2;cursor: pointer;}

    #destination-list{float:left;list-style:none;margin-top:-3px;padding:0;width:250px;position: realtive;}

    #destination-list li{padding: 10px; background: #f0f0f0; border-bottom: #bbb9b9 1px solid;}

    #destination-list li:hover{background:#ece3d2;cursor: pointer;}

    td.details-control {
        background: url('../img/details_open.png') no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url('../img/details_close.png') no-repeat center center;
    }

    td.details-control-add {
        background: url('../img/details_open.png') no-repeat center center;
        cursor: pointer;
    }
    td.details-control-minus {
        background: url('../img/details_close.png') no-repeat center center;
        cursor: pointer;
    }
    
    td.order-details-link {
        cursor: pointer;
        color: #00CCFF;
    }
    
    td.order-details-link:hover {
        text-decoration: underline;
        color: #0099CC;
    }

 </style>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">View Orders</li>
 </ol>
 <section id="orders" class="widget">
     <header>
         <h4><span class="fw-semi-bold">Orders</span></h4>
         <!--<div class="widget-controls">
             <a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
             <a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>
             <a data-widgster="close" title="Close" href="#"><i class="glyphicon glyphicon-remove"></i></a>
         </div>-->
     </header>
     <div class="widget-body">
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="orders-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th></th>
                     <th>ID</th>
                     <th>Order ID</th>
                     <th>Customer</th>
                     <th>Carrier(s)</th>
                     <th>Qty</th>
                     <th>Transport Mode</th>
                     <th class="hidden-sm-down">Orig. Address</th>
                     <th class="hidden-sm-down">Orig. City</th>
                     <th class="hidden-sm-down">Orig. State</th>
                     <th class="hidden-sm-down">Orig. Zip</th>
                     <th class="hidden-sm-down">Dest. Address</th>
                     <th class="hidden-sm-down">Dest. City</th>
                     <th class="hidden-sm-down">Dest. State</th>
                     <th class="hidden-sm-down">Dest. Zip</th>
                     <th class="hidden-sm-down">Mileage</th>
                     <th class="hidden-sm-down">Data Points</th>
                     <th>Status</th>
                     <th class="no-sort pull-right"></th>
                 </tr>
                 </thead>
                 <tbody>
                      <!-- loadTableAJAX() is what populates this area -->
                 </tbody>
             </table>
         </div>
     </div>
 </section>
 
 
<section class="widget"  id="order-details" style="display: none;">
     <header>
         <h4><span class="fw-semi-bold">Order Details</span></h4>  
         <div class="widget-controls">
             <a data-widgster="close" title="Close" href="Javascript:closeOrderDetails()"><i class="glyphicon glyphicon-remove"></i></a>
         </div>
     </header>
    <br>
    <br>
     <div class="widget-body">
         
         <div id="dataTable-2" class="mt">
            <table id="order-details-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th>Order ID</th>
                     <th>Status</th>
                     <th>Qty</th>
                     <th>Transport Mode</th>
                     <th>Pick Up</th>
                     <th>Delivery</th>
                     <th class="hidden-sm-down">Orig. City</th>
                     <th class="hidden-sm-down">Orig. State</th>
                     <th class="hidden-sm-down">Dest. City</th>
                     <th class="hidden-sm-down">Dest. State</th>
                     <th class="hidden-sm-down">Mileage</th>
                     <th>Rate</th>
                 </tr>
                 </thead>
                 <tbody>
                      <!-- loadTableAJAX() is what populates this area -->
                 </tbody>
             </table>
         </div>
                
 </section>
 
 
 <script>

    loadTableAJAX();

    $('.datepicker').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });

    var table = $("#orders-table").DataTable();
    
    $("#order-details").css("display", "none");
    
    function closeOrderDetails(){
        
        $("#order-details").css("display", "none");
        $("#orders").css("display", "block");
        table.ajax.reload();
    }


    $('#orders-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();

    });

    $('#orders-table tbody').on( 'click', 'td.order-details-link', function () {
        var data = table.row( $(this).parents('tr') ).data();

        var orderID = data["id"];

        loadOrderDetailsAJAX(orderID);
        
    });
    
    /* Formatting function for row details - modify as you need */
    function format ( d ) {

        var table = '<table  class="col-sm-12" cellpadding="5" cellspacing="0" border="0"><tr>';

        // `d` is the original data object for the row
        var ndp = d.needsDataPoints;

        for (var i = 0; i < dataPoints.object_type_data_points.length; i++) {
            var selected = '';
            var value = '';

            $.each(ndp, function(idx, obj) {
              $.each(obj, function(key, val) {
                if (dataPoints.object_type_data_points[i].columnName == key) {
                    value = val; // Get the value from the JSON data in the record to use to set the selected option in the dropdown
                }
              })
            });

            table += '<td>' + dataPoints.object_type_data_points[i].title;
            for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {

                if (dataPoints.object_type_data_points[i].object_type_data_point_values[v].value === value) {
                    table += ' <br/> <strong>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</strong>';
                }
            }

            table += '</td>\n';
        }

        table += '</tr></table>\n';
        return table;

    }

    $('#orders-table tbody').on('click', 'td.details-control-add', function () {

        var tr = $(this).closest('tr');
        var row = table.row( tr );
        var td = $(this).closest('td');

        // Open this row
        row.child( format(row.data()) ).show();
        td.addClass('details-control-minus');
        td.removeClass('details-control-add');

    } );

    $('#orders-table tbody').on('click', 'td.details-control-minus', function () {

        var tr = $(this).closest('tr');
        var row = table.row( tr );
        var td = $(this).closest('td');

        // This row is already open - close it
        row.child.hide();
        td.removeClass('details-control-minus');
        td.addClass('details-control-add');

    } );

 </script>
