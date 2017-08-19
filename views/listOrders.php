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
                { data: "status",visible: false },
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
        var blnShow = false;
        
        switch(entityType){
            case 0:     // URL for the Admin.
                url += '/api/order_details?include=orders&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID&filter=orderID,eq,' + orderID + '&transform=1';
                break;
            case 1:    // URL for Customer.
                url += '/api/order_details?include=orders&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID&filter=orderID,eq,' + orderID + '&transform=1';
                blnShow = true;
                break;
            case 2:     // URL for the Carrier. The Customer can only see order details of their route.
                url += '/api/order_details?include=orders&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID&filter[]=orderID,eq,' + orderID + '&filter[]=carrierID,eq,' + entityid + '&transform=1';
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
                                status: order_details[0].orders[0].status,
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
                    { data: "status", visible: false },
                    { data: "qty" },
                    { data: "transportationMode" },
                    { data: "pickupDate" },
                    { data: "deliveryDate" },
                    { data: "originationCity" },
                    { data: "originationState" },
                    { data: "destinationCity" },
                    { data: "destinationState" },
                    { data: "distance" },
                    { data: "carrierRate" },
                    {
                        data: null,
                        "bSortable": false,
                        "mRender": function (o) {
                            var buttons = '';

                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-edit text-info\"></i> <span class=\"text-info\">Edit</span></button>';

                            return buttons;
                        }, visible: blnShow
                    }
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
    
    function loadOrderStatusesAJAX(orderID){
        
        var url = '<?php echo API_HOST; ?>/api/order_statuses?columns=id,orderID,city,state,status,note,createdAt&filter=orderID,eq,' + orderID + '&transform=1';
        var blnShow = false;
        
        if(entityType != 1) blnShow = true;
        
        if ( ! $.fn.DataTable.isDataTable( '#order-history-table' ) ) {
        
            var order_history_table = $('#order-history-table').DataTable({
                retrieve: true,
                processing: true,
                ajax: {
                    url: url,
                    dataSrc: 'order_statuses'
                },
                columns: [
                    { data: "createdAt" },
                    { data: "city" },
                    { data: "state" },
                    { data: "status" },
                    { data: "note", visible: blnShow }
                ]
              });

            //To Reload The Ajax
            //See DataTables.net for more information about the reload method
            order_history_table.ajax.reload();
        }
        else{
            //The URL will change with each "View Commit" button click
          // Must load new Url each time.
            var reload_table = $('#order-history-table').DataTable();
            reload_table.ajax.url(url).load();
        }
        
        
    }
    
    
    function loadPODListAJAX(orderID){
        
        var url = '<?php echo API_HOST; ?>/api/orders?columns=podList&filter=id,eq,' + orderID + '&transform=1';
        
        
        if ( ! $.fn.DataTable.isDataTable( '#pod-list-table' ) ) {
        
            var order_history_table = $('#pod-list-table').DataTable({
                retrieve: true,
                processing: true,
                ajax: {
                    url: url,
                    dataSrc: 'orders[0].podList'
                },
                columns: [
                    { data: "vinNumber" },
                    { data: "deliveryDate" },
                    { data: "notes" },
                    {  
                        data: null,
                        "bSortable": false,
                        "mRender": function (o) {
                            var buttons = '';

                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"fa fa-download text-info\"></i> <span class=\"text-info\">Download POD</span></button>';

                            return buttons;
                        }
                    },
                    {  
                        data: null,
                        "bSortable": false,
                        "mRender": function (o) {
                            var buttons = '';

                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"fa fa-upload text-info\"></i> <span class=\"text-info\">Upload POD</span></button>';

                            return buttons;
                        }
                    }
                ]
              });

            //To Reload The Ajax
            //See DataTables.net for more information about the reload method
            order_history_table.ajax.reload();
        }
        else{
            //The URL will change with each "View Commit" button click
          // Must load new Url each time.
            var reload_table = $('#pod-list-table').DataTable();
            reload_table.ajax.url(url).load();
        }
        
        
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

 <div id="orders">
     
 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">View Orders</li>
 </ol>
 <section  class="widget">
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
 </div>
 
 <div id="order-details" style="display: none;">
    <ol class="breadcrumb">
      <li>ADMIN</li>
      <li>View Orders</li>
      <li class="active">View Order Details</li>
    </ol>
     
    <section class="widget">
         <header>
             <h4><span class="fw-semi-bold">Order Details</span></h4>  
             <div class="widget-controls">
                 <a data-widgster="close" title="Close" href="Javascript:closeOrderDetails()"><i class="glyphicon glyphicon-remove"></i></a>
             </div>
         </header>
        <br>
        <br>
        <div class="widget-body">

            <div id="dataTable-1" class="mt">
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
                        <th class="no-sort pull-right"></th>
                    </tr>
                    </thead>
                    <tbody>
                        <!-- loadTableAJAX() is what populates this area -->
                    </tbody>
                </table>
            </div>
        </div>
        
    <br>
    
        <div id="dataTable-2" class="mt">
            <h5><span class="fw-semi-bold">Order Tracking History</span></h5>
            <table id="order-history-table" class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Status</th>
                    <th>Note</th>
                </tr>
                </thead>
                <tbody>
                    <!-- loadTableAJAX() is what populates this area -->
                </tbody>
             </table>
        </div>
    
              <?php 
              
              if ($_SESSION['entitytype'] != 1){ 
                  
                  ?>
    
        <div class="row">            
            <div class="col-sm-4">
                <a data-widgster="addDeliveryStatus" title="Add" href="Javascript:addDeliveryStatus();"><i class="fa fa-plus-square-o"></i> Add Delivery Status</a>
            </div>
        </div>
    
              <?php 
              
              } 
              
              ?>
    
    <br>
    
        <div id="dataTable-3" class="mt">
            <h5><span class="fw-semi-bold">POD List</span></h5>
            <table id="pod-list-table" class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Trailer VIN</th>
                    <th>Delivery Date</th>
                    <th>Notes</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                    <!-- loadTableAJAX() is what populates this area -->
                </tbody>
             </table>
        </div>
    
    
     </section>
     
     

      
 </div>
    
<!-- Modal -->
  <div class="modal fade" id="addOrderStatus" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Add Order Status</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
                <form id="formNeed" class="register-form mt-lg">
                  <input type="hidden" id="id" name="id" value="" />
                  <div class="row">
                      <div class="col-sm-3">
                          <label for="city">City</label>
                          <div class="form-group">
                              <input type="text" id="city" name="city" class="form-control mb-sm" placeholder="City" required="true" />
                          </div>
                      </div>
                      <div class="col-sm-3">
                          <label for="state">State</label>
                          <div class="form-group">
                            <select id="state" name="state" data-placeholder="State" class="form-control chzn-select" data-ui-jq="select2">
                              <option value="">*Select State...</option>
             <?php
                              foreach($states->states->records as $value) {
                                  $selected = ($value[0] == $state) ? 'selected=selected':'';
                                  echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                              }
             ?>
                            </select>
                          </div>
                      </div>                 
                      <div class="col-sm-3">
                        <label for="orderStatusDiv">Order Status</label>
                        <div id="orderStatusDiv" class="form-group">
                            
                        </div>
                      </div>
                  </div>
                  <hr/>
                  <div class="row">                 
                      <div class="col-sm-12">
                        <label for="statusNotes">Notes</label>
                        <div class="form-group">
                            <textarea id="statusNotes" rows="4" cols="50" class="form-control mb-sm" maxlength="600"></textarea>
                        </div>
                      </div>
                  </div>
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary btn-md" onclick="saveDeliveryStatus();" id="saveOrderStatus">Save</button>
        </div>
      </div>
    </div>
  </div>

 
 
 
 <script>

    loadTableAJAX();
    
/*
    $('.datepicker').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });
*/

    var table = $("#orders-table").DataTable();
    
    $("#order-details").css("display", "none");
    
    function closeOrderDetails(){
        
        $("#order-details").css("display", "none");
        $("#orders").css("display", "block");
        table.ajax.reload();
    }


    function saveDeliveryStatus(){        
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0!
        var yyyy = today.getFullYear();
        var hours = today.getHours();
        var min = today.getMinutes();
        var sec = today.getSeconds();

        if(dd<10) {
            dd='0'+dd;
        }

        if(mm<10) {
            mm='0'+mm;
        }

        if(hours<10) {
            hours='0'+hours;
        }

        if(min<10) {
            min='0'+min;
        }

        if(sec<10) {
            sec='0'+sec;
        }

        today = yyyy+"-"+mm+"-"+dd+" "+hours+":"+min+":"+sec;

        var orderHistoryTable = $('#order-history-table').DataTable();
        
        var id = $("#id").val();
        var city = $("#city").val();
        var state = $("#state").val();
        var status = $("#orderStatus").val();
        var notes = $("#statusNotes").val();
        
        var orderStatus = {orderID: id, city: city, state: state, status: status, note: notes, createdAt: today, updatedAt: today};
        
        $.ajax({
           url: '<?php echo API_HOST."/api/order_statuses" ?>/',
           type: "POST",
           data: JSON.stringify(orderStatus),
           contentType: "application/json",
           async: false,
           success: function(data){
                orderHistoryTable.ajax.reload();
                $("#addOrderStatus").modal('hide');
           },
           error: function() {
              alert("There Was An Error Saving the Status");
           }
        }); 
        
    }

    function addDeliveryStatus() {
        var orderDetailsTable = $("#order-details-table").DataTable();
        var json = orderDetailsTable.ajax.json();
        var data = json.order_details[0];

        console.log(data);

            var orderStatusSelect = '<select id="orderStatus" name="orderStatus" class="form-control mb-sm" required="required">\n';
        
            $("#id").val(data.orders[0].id);
            
            var inTransit = "";
            var inCarriersYard = "";
            var atShipper = "";
            var trailerLoaded = "";
            var atConsignee = "";
            var trailerDelivered = "";
            
            if (data['status'] == "In Transit") {
                inTransit = "selected=selected";
            } else if (data['status'] == "In Carrier's Yard"){
                inCarriersYard = "selected=selected";
            } else if (data['status'] == "At Shipper To Be Loaded"){
                atShipper = "selected=selected";
            } else if (data['status'] == "Trailer Loaded In Route"){
                trailerLoaded = "selected=selected";
            } else if (data['status'] == "At Consignee To Be Unloaded"){
                atConsignee = "selected=selected";
            } else if (data['status'] == "Trailer Delivered"){
                trailerDelivered = "selected=selected";
            }
            
            orderStatusSelect += '<option value="">Please Select...</option>\n';
            orderStatusSelect += '<option value="In Carrier\'s Yard" '+inCarriersYard+'>In Carrier\'s Yard</option>\n';
            orderStatusSelect += '<option value="At Shipper To Be Loaded" '+atShipper+'>At Shipper To Be Loaded</option>\n';
            orderStatusSelect += '<option value="Trailer Loaded In Route" '+trailerLoaded+'>Trailer Loaded In Route</option>\n';
            orderStatusSelect += '<option value="At Consignee To Be Unloaded" '+atConsignee+'>At Consignee To Be Unloaded</option>\n';
            orderStatusSelect += '<option value="Trailer Delivered" '+trailerDelivered+'>Trailer Delivered</option>\n';
            orderStatusSelect += '<option value="In Transit" '+inTransit+'>In Transit</option>\n';
                    
            orderStatusSelect += '</select>\n';
            $("#orderStatusDiv").html(orderStatusSelect);
            
            $("#addOrderStatus").modal('show');
        
    }

    $('#orders-table tbody').on( 'click', 'td.order-details-link', function () {
        var data = table.row( $(this).parents('tr') ).data();

        var orderID = data["id"];

        loadOrderDetailsAJAX(orderID);
        loadOrderStatusesAJAX(orderID);
        loadPODListAJAX(orderID);
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
