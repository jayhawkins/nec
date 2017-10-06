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

    function addVINNumber(){
        var li = '';

        li += '<li class="list-group-item"><input type="text" class="form-control" value=""></li>\n';

        $("#input-list-box").append(li);
    }
      function post() {

          var result = true;

          var params = {
                address1: $("#originationAddress").val(),
                city: $("#originationCity").val(),
                state: $("#originationState").val(),
                zip: $("#originationZip").val(),
                entityID: $("#entityID").val(),
                locationType: "Origination"
          };
          //alert(JSON.stringify(params));
          $.ajax({
             url: '<?php echo HTTP_HOST."/getlocationbycitystatezip" ?>',
             type: 'POST',
             data: JSON.stringify(params),
             contentType: "application/json",
             async: false,
             success: function(response){
                if (response == "success") {
                    var params = {
                          address1: $("#destinationAddress").val(),
                          city: $("#destinationCity").val(),
                          state: $("#destinationState").val(),
                          zip: $("#destinationZip").val(),
                          entityID: $("#entityID").val(),
                          locationType: "Destination"
                    };
                    //alert(JSON.stringify(params));
                    $.ajax({
                       url: '<?php echo HTTP_HOST."/getlocationbycitystatezip" ?>',
                       type: 'POST',
                       data: JSON.stringify(params),
                       contentType: "application/json",
                       async: false,
                       success: function(response){
                          if (response == "success") {
                          } else {
                              alert("1: " + response);
                              result = false;
                              //alert('Preparation Failed!');
                          }
                       },
                       error: function(response) {
                          alert("2: " + response);
                          result = false;
                          //alert('Failed Searching for Destination Location! - Notify NEC of this failure.');
                       }
                    });
                } else {
                    alert("3: " + response);
                    result = false;
                    //alert('Preparation Failed!');
                }
             },
             error: function(response) {
                alert("4: " + JSON.stringify(response));
                result = false;
                //alert('Failed Searching for Origination Location! - Notify NEC of this failure.');
             }
          });

          if (result) {
            verifyAndPost(function(data) {
                alert(data);
                $("#load").html("Save Changes");
                $("#load").prop("disabled", false);
            });

              return true;
        }
        else { return false; }
      }

      function verifyAndPost() {

                $("#load").html("<i class='fa fa-spinner fa-spin'></i> Editing Order");
                $("#load").prop("disabled", true);


                var passValidation = false;
                var type = "";
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

                today = mm+'/'+dd+'/'+yyyy;
                today = yyyy+"-"+mm+"-"+dd+" "+hours+":"+min+":"+sec;

                var originationaddress = $("#originationAddress").val() + ', ' + $("#originationCity").val() + ', ' + $("#originationState").val() + ', ' + $("#originationZip").val();
                var destinationaddress = $("#destinationAddress").val() + ', ' + $("#destinationCity").val() + ', ' + $("#destinationState").val() + ', ' + $("#destinationZip").val();

                // getMapDirectionFromGoogle is defined in common.js
                newGetMapDirectionFromGoogle( originationaddress, destinationaddress, function(response) {

                              var originationlat = response.originationlat;
                              var originationlng = response.originationlng;
                              var destinationlat = response.destinationlat;
                              var destinationlng = response.destinationlng;
                              var distance = response.distance;

                                var url = '<?php echo API_HOST."/api/orders/"; ?>' + $("#id").val();
                                type = "PUT";


                              // Build the podList
                              var podArray = [];
                              var obj = $("#input-list-box li");
                              var item = {};
                              for (var i = 0; i < obj.length; i++) {

                                  if (obj[i].firstChild.value != ""){
                                    item = {vinNumber: obj[i].firstChild.value, deliveryDate: "", notes: "", fileName: "", carrier: ""};
                                    podArray.push(item);
                                  }
                              }


                              // Build the needsDataPoints
                              var needsarray = [];
                              var obj = $("#dp-check-list-box li select");
                              for (var i = 0; i < obj.length; i++) {
                                  item = {};
                                  item[obj[i].id] = obj[i].value;
                                  needsarray.push(item);
                              }
                              var needsdatapoints = needsarray;

                              var pickupInformation = {
                                  pickupLocation: $("#pickupLocation").val(),
                                  contactPerson: $("#pickupContactPerson").val(),
                                  phoneNumber: $("#pickupPhoneNumber").val(),
                                  hoursOfOperation: $("#pickupHoursOfOperation").val()
                              };

                              var deliveryInformation  = {
                                  deliveryLocation: $("#deliveryLocation").val(),
                                  contactPerson: $("#deliveryContactPerson").val(),
                                  phoneNumber: $("#deliveryPhoneNumber").val(),
                                  hoursOfOperation: $("#deliveryHoursOfOperation").val()
                              };

                            var date = today;
                            var data = {customerRate: $("#rate").val(), rateType: $('input[name="rateType"]:checked').val(), transportationMode: $("#transportationMode").val(), pickupInformation: pickupInformation, originationAddress: $("#originationAddress").val(), originationCity: $("#originationCity").val(), originationState: $("#originationState").val(), originationZip: $("#originationZip").val(), deliveryInformation: deliveryInformation, destinationAddress: $("#destinationAddress").val(), destinationCity: $("#destinationCity").val(), destinationState: $("#destinationState").val(), destinationZip: $("#destinationZip").val(), originationLat: originationlat, originationLng: originationlng, destinationLat: destinationlat, destinationLng: destinationlng, distance: distance, needsDataPoints: needsdatapoints, updatedAt: date};

                              if (podArray.length > 0){
                                  data.podList = podArray;
                              }

                              var emailData = data;
                              var orderDetailTable = $('#order-details-table').DataTable();
                              var orderDetailJSON = orderDetailTable.ajax.json();

                              var orderNumber = orderDetailJSON.order_details[0].orders[0].orderID;
                              var customerID = orderDetailJSON.order_details[0].orders[0].customerID;

                              emailData.orderNumber = orderNumber;
                              emailData.customerID = customerID;

                              //console.log(JSON.stringify(emailData));

                              $.ajax({
                                 url: url,
                                 type: type,
                                 data: JSON.stringify(data),
                                 contentType: "application/json",
                                 async: false,
                                 success: function(data){
                                    if (data > 0) {

                                      $.ajax({
                                          url: '<?php echo HTTP_HOST; ?>' + '/sendorderupdatenotification',
                                          type: "POST",
                                          data: JSON.stringify(emailData),
                                          contentType: "application/json",
                                          async:false,
                                          success: function(data){

                                        $("#load").html("Save Changes");
                                        $("#load").prop("disabled", false);

                                        if(entityid > 0) alert(data);

                                        $("#editOrder").modal('hide');

                                        var orderDetailTable = $('#order-details-table').DataTable();
                                        var podListTable = $('#pod-list-table').DataTable();

                                        orderDetailTable.ajax.reload();
                                        podListTable.ajax.reload();

                                        $("#id").val('');
                                        $("#qty").val('');
                                        $("#rate").val('');
                                        $("#originationAddress").val('');
                                        $("#originationCity").val('');
                                        $("#originationState").val('');
                                        $("#originationZip").val('');
                                        $("#destinationAddress").val('');
                                        $("#destinationCity").val('');
                                        $("#destinationState").val('');
                                        $("#destinationZip").val('');
                                        passValidation = true;

                                          },
                                          error: function(data){
                                              console.log("Notification Error: ", JSON.stringify(data));
                                          }
                                      });

                                    } else {
                                        $("#load").html("Save Changes");
                                        $("#load").prop("disabled", false);
                                        alert("Editing Order Failed! Please Verify Your Data.");
                                    }
                                 },
                                 error: function() {
                                    $("#load").html("Save Changes");
                                    $("#load").prop("disabled", false);
                                    alert("There Was An Error Editing The Order!");
                                 }
                              });

                              return passValidation;
              });
      }

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

                        orders.forEach(function(order){
                            var carrierIDs = order.carrierIDs;

                            for(var i = 0; i < carrierIDs.length; i++){
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
                            buttons += '<button class="btn btn-primary btn-xs" role="button"><i class="fa fa-book"></i> <span>View Order Details</span></button>';

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
        var blnCarrierRate = false;

        switch(entityType){
            case 0:     // URL for the Admin.
                url += '/api/order_details?include=orders,entities&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID,orders.customerID,orders.pickupInformation,orders.deliveryInformation,entities.name&filter=orderID,eq,' + orderID + '&transform=1';
                blnShow = true;
                blnCarrierRate = true;
                break;
            case 1:    // URL for Customer.
                url += '/api/order_details?include=orders,entities&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID,orders.customerID,orders.pickupInformation,orders.deliveryInformation,entities.name&filter=orderID,eq,' + orderID + '&transform=1';
                blnShow = true;
                break;
            case 2:     // URL for the Carrier. The Customer can only see order details of their route.
                url += '/api/order_details?include=orders,entities&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID,orders.customerID,orders.pickupInformation,orders.deliveryInformation,entities.name&filter[]=orderID,eq,' + orderID + '&filter[]=carrierID,eq,' + entityid + '&transform=1';
                blnCarrierRate = true;
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

                        if(entityType == 2 || entityType == 0) return order_details;   // Carrier and Admin is already set
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
                                entities: order_details[0].entities,
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
                    { data: "orders[0].orderID", visible: !blnShow },
                    {
                        data: null,
                        "bSortable": true,
                        "mRender": function (o) {
                            var carrierID = o.carrierID;
                            var entityName = "";

                            allEntities.entities.forEach(function(entity){

                                if(carrierID == entity.id){

                                    entityName = entity.name;
                                }
                            });

                            return entityName;
                        },
                        visible: blnCarrierRate
                    },
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
                    { data: "carrierRate", visible: blnCarrierRate },
                    {
                        data: null,
                        "bSortable": false,
                        "mRender": function (o) {
                            var buttons = '';

                            buttons += '<button class="btn btn-primary btn-xs" role="button"><i class="glyphicon glyphicon-edit text"></i> <span class="text">Edit</span></button>';

                            return buttons;
                        },visible: blnShow
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

        var url = '<?php echo API_HOST; ?>/api/order_statuses?include=entities&columns=id,orderID,carrierID,city,state,status,note,createdAt,entities.id,entities.name&filter=orderID,eq,' + orderID + '&transform=1';
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
                    { data: "entities[0].name" },
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

        var url = '<?php echo API_HOST; ?>/api/orders?columns=id,carrierIDs,deliveryInformation,pickupInformation,podList&filter=id,eq,' + orderID + '&transform=1';
        var blnShow = false;

        if(entityType == 0) blnShow = true;

        if ( ! $.fn.DataTable.isDataTable( '#pod-list-table' ) ) {

            var order_history_table = $('#pod-list-table').DataTable({
                retrieve: true,
                processing: true,
                ajax: {
                    url: url,
                    //dataSrc: 'orders[0].podList',
                    dataSrc: function(json){

                        var podList = json.orders[0].podList;
                        var deliveryInformation = json.orders[0].deliveryInformation;
                        var pickupInformation = json.orders[0].pickupInformation;

                        
                        if (podList == null){
                            podList = [];
                        }
                        
                        if (deliveryInformation == undefined){
                            podList.deliveryInformation = {};
                        }
                        else{
                            podList.deliveryInformation = deliveryInformation;
                        }
                        
                        if (pickupInformation == undefined){
                            podList.pickupInformation = {};
                        }
                        else{
                            podList.pickupInformation = pickupInformation;
                        }

                        return podList;
                    }

                },
                columns: [
                    {
                        data: null,
                        "bSortable": false,
                        "mRender": function (o) {
                            var buttons = orderID;

                            return buttons;
                        },visible: false
                    },
                    { data: "vinNumber" },
                    { data: "carrier", visible: blnShow },
                    { data: "deliveryDate" },
                    { data: "notes" },
                    {
                        data: null,
                        "bSortable": false,
                        "mRender": function (podDataJSON) {
                            var buttons = '';
                            var errorCount = 0;
                                                  
                                                  
                                                  
                            if(podDataJSON.unitNumber == "" || podDataJSON.unitNumber == undefined){
                                errorCount++;
                            }
                            if(podDataJSON.truckProNumber == "" || podDataJSON.truckProNumber == undefined){
                                errorCount++;
                            }   
                            if(podDataJSON.trailerYear == "" || podDataJSON.trailerYear == undefined){
                                errorCount++;
                            }
                            /*if(podDataJSON.deliveryInformation == {} || podDataJSON.deliveryInformation == undefined){
                                errorCount++;
                            }
                            else{
                                if(podDataJSON.deliveryInformation.phoneNumber == ""){
                                    errorCount++;
                                }
                                if(podDataJSON.deliveryInformation.contactPerson == ""){
                                    errorCount++;
                                }
                                if(podDataJSON.deliveryInformation.deliveryLocation == ""){
                                    errorCount++;
                                }
                                if(podDataJSON.deliveryInformation.hoursOfOperation == ""){
                                    errorCount++;
                                }
                            }
                            if(podDataJSON.pickupInformation == {} || podDataJSON.pickupInformation == undefined){
                                errorCount++;
                            }
                            else{
                                if(podDataJSON.pickupInformation.phoneNumber == ""){
                                    errorCount++;
                                }
                                if(podDataJSON.pickupInformation.contactPerson == ""){
                                    errorCount++;
                                }
                                if(podDataJSON.pickupInformation.pickupLocation == ""){
                                    errorCount++;
                                }
                                if(podDataJSON.pickupInformation.hoursOfOperation == ""){
                                    errorCount++;
                                }
                            }*/

                            //buttons += '<a class="btn btn-primary btn-xs" href="../downloadfiles/POD-Template.pdf" target="_blank"><i class="fa fa-download text"></i> <span class="text">Download POD</span></a>';
                            if(errorCount > 0){
                                buttons = '<button class="btn btn-primary btn-xs trailer-data-missing"><i class="fa fa-download text"></i> <span class="text">Download POD</span></button>';

                            }
                            else{
                                buttons = '<button class="btn btn-primary btn-xs download-pod"><i class="fa fa-download text"></i> <span class="text">Download POD</span></button>';
                            }
                            
                            return buttons;
                        }
                    },
                    {
                        data: null,
                        "bSortable": false,
                        "mRender": function (o) {
                            var buttons = '';
                            var fileName = o.fileName;

                            if(fileName == ""){

                                buttons += '<button class="btn btn-primary btn-xs upload-pod" role="button"><i class="fa fa-upload text"></i> <span class="text">Upload POD</span></button>';
                            }
                            else{
                                buttons += '<button class="btn btn-primary btn-xs view-edit-pod" role="button"><i class="glyphicon glyphicon-eye-open text"></i> <span class="text">View/Edit POD</span></button>';
                            }

                            return buttons;
                        }
                    },
                    {
                        data: null,
                        "bSortable": false,
                        "mRender": function (o) {
                            var buttons = '';

                            buttons += '<button class="btn btn-primary btn-xs edit-trailer-data" role="button"><i class="glyphicon glyphicon-edit text"></i> <span class="text">Edit Trailer Data</span></button>';


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

    function loadOrderComments(orderID){

        var url = '<?php echo API_HOST; ?>/api/orders?columns=comments&filter=id,eq,' + orderID + '&transform=1';

        $.ajax({
            url: url,
            type: "GET",
            async: false,
            success: function(data){
                var comments = "";

                if(data.orders.length > 0){
                    comments = data.orders[0].comments;
                }

                $("#orderComments").val(comments);
            },
            error: function(data){
                alert("Unable to retrieve order comments.");
            }
        });
    }

    function getOrderIDAndCustomerName(orderID){

        var url = '<?php echo API_HOST; ?>';
        url += '/api/orders?columns=id,customerID,orderID&filter=id,eq,' + orderID + '&transform=1';

          $.ajax({
             url: url,
             type: "GET",
             async: false,
             success: function(data){
                  var customerID = data.orders[0].customerID;
                  var orderID = data.orders[0].orderID;

                  $("#orderNumber").html(orderID);


                var url = '<?php echo API_HOST; ?>';
                url += '/api/entities?columns=id,name&filter=id,eq,' + customerID + '&transform=1';

                $.ajax({
                   url: url,
                   type: "GET",
                   async: false,
                   success: function(data){
                        var customerName = data.entities[0].name;

                        $("#customerName").html(customerName);

                   }
                });
             }
          });
    }

      function formatListBox() {
          // Bootstrap Listbox
          $('.list-group.checked-list-box .list-group-item').each(function () {

              // Settings
              var $widget = $(this),
                  $checkbox = $('<input type="checkbox" class="hidden" style="display: none" />'),
                  color = ($widget.data('color') ? $widget.data('color') : "primary"),
                  style = ($widget.data('style') == "button" ? "btn-" : "list-group-item-"),
                  settings = {
                      on: {
                          icon: 'glyphicon glyphicon-check'
                      },
                      off: {
                          icon: 'glyphicon glyphicon-unchecked'
                      }
                  };

              $widget.css('cursor', 'pointer');
              $widget.append($checkbox);

              // Event Handlers
              $widget.on('click', function () {
                  $checkbox.prop('checked', !$checkbox.is(':checked'));
                  $checkbox.triggerHandler('change');
                  //recordLocationContacts();
                  //updateDisplay();
              });
              $checkbox.on('change', function () {
                  updateDisplay();
              });




              // Actions
              function updateDisplay() {
                  var isChecked = $checkbox.is(':checked');

                  // Set the button's state
                  $widget.data('state', (isChecked) ? "on" : "off");

                  // Set the button's icon
                  $widget.find('.state-icon')
                      .removeClass()
                      .addClass('state-icon ' + settings[$widget.data('state')].icon);

                  // Update the button's color
                  if (isChecked) {
                      $widget.addClass(style + color + ' active');
                  } else {
                      $widget.removeClass(style + color + ' active');
                  }
              }

              // Initialization
              function init() {

                  if ($widget.data('checked') == true) {
                      $checkbox.prop('checked', !$checkbox.is(':checked'));
                  }

                  updateDisplay();var checkedItems = {}, counter = 0;
                  $("#check-list-box li.active").each(function(idx, li) {
                      //console.log($(li));
                      checkedItems[counter] = $(li).context.id;
                      counter++;
                  });
                  //$('#display-json').html(JSON.stringify(checkedItems, null, '\t'));
                  if ($widget.find('.state-icon').length == 0) {
                      $widget.prepend('<span class="state-icon ' + settings[$widget.data('state')].icon + '"></span>');
                  }
              }

              init();
          });

          // Doesn't get called - this is from the example but copied to the Save Changes button click
          $('#get-checked-data').on('click', function(event) {
              event.preventDefault();
              var checkedItems = {}, counter = 0;
              $("#check-list-box li.active").each(function(idx, li) {
                  checkedItems[counter] = $(li).context.id;
                  counter++;
              });
          });

      }

      function formatListBoxDP() {
          // Bootstrap Listbox
          $('.list-group.dp-checked-list-box .list-group-item').each(function () {

              // Settings
              var $widget = $(this),
                  $checkbox = $('<input type="checkbox" class="hidden" style="display: none" />'),
                  color = ($widget.data('color') ? $widget.data('color') : "primary"),
                  style = ($widget.data('style') == "button" ? "btn-" : "list-group-item-"),
                  settings = {
                      on: {
                          icon: 'glyphicon glyphicon-check'
                      },
                      off: {
                          icon: 'glyphicon glyphicon-unchecked'
                      }
                  };

              $widget.css('cursor', 'pointer');
              $widget.append($checkbox);

              // Event Handlers
              $widget.on('click', function () {
                  //$checkbox.prop('checked', !$checkbox.is(':checked'));
                  //$checkbox.triggerHandler('change');
                  //recordDataPoints();
                  //updateDisplay();
              });
              $checkbox.on('change', function () {
                  //updateDisplay();
              });

              function recordDataPoints() {
                  var passValidation = false;
                  var entityID = <?php echo $_SESSION['entityid']; ?>;
                  var contactdata = [];

                  $("#dp-check-list-box li.active").each(function(idx, li) {
                      contactdata.push({"entityID": entityID, "location_id": $("#id").val(), "contact_id": $(li).context.id});
                  });

                  if (contactdata.length > 0) {
                      var url = '<?php echo HTTP_HOST."/deletelocationcontacts"; ?>';
                      var data = {location_id: $("#id").val()};
                      var type = "POST";

                      $.ajax({
                         url: url,
                         type: type,
                         data: JSON.stringify(data),
                         contentType: "application/json",
                         async: false,
                         success: function(data){
                            if (data == "success") {

                                 $.ajax({
                                    url: url,
                                    type: type,
                                    data: JSON.stringify(data),
                                    contentType: "application/json",
                                    async: false,
                                    success: function(data){
                                         getLocationContacts();
                                    },
                                    error: function() {
                                       alert("There Was An Error Adding Need Contacts!");
                                    }
                                 });
                            } else {
                                  alert("There Was An Issue Clearing Need Contacts!");
                            }
                         },
                         error: function() {
                              alert("There Was An Error Deleting Need Records!");
                         }
                      });
                  }

                  //return passValidation;
              }


              // Actions
              function updateDisplay() {
                  var isChecked = $checkbox.is(':checked');

                  // Set the button's state
                  $widget.data('state', (isChecked) ? "on" : "off");

                  // Set the button's icon
                  $widget.find('.state-icon')
                      .removeClass()
                      .addClass('state-icon ' + settings[$widget.data('state')].icon);

                  // Update the button's color
                  if (isChecked) {
                      $widget.addClass(style + color + ' active');
                  } else {
                      $widget.removeClass(style + color + ' active');
                  }
              }

              // Initialization
              function init() {

                  if ($widget.data('checked') == true) {
                      $checkbox.prop('checked', !$checkbox.is(':checked'));
                  }

                  updateDisplay();var checkedItems = {}, counter = 0;
                  $("#dp-check-list-box li.active").each(function(idx, li) {
                    //console.log($(li));
                      checkedItems[counter] = $(li).context.id;
                      counter++;
                  });
                  //$('#display-json').html(JSON.stringify(checkedItems, null, '\t'));
                  if ($widget.find('.state-icon').length == 0) {
                      $widget.prepend('<span class="state-icon ' + settings[$widget.data('state')].icon + '"></span>');
                  }
              }

              init();
          });

          // Doesn't get called - this is from the example but copied to the Save Changes button click
          $('#get-checked-data').on('click', function(event) {
              event.preventDefault();
              var checkedItems = {}, counter = 0;
              $("#check-list-box li.active").each(function(idx, li) {
                  checkedItems[counter] = $(li).context.id;
                  counter++;
              });
          });

      }

      function getLocationContacts() {

          var url = '<?php echo API_HOST."/api/locations_contacts?columns=location_id,contact_id&filter=entityID,eq," . $_SESSION['entityid']; ?>';
          var type = "GET";

          $.ajax({
             url: url,
             type: type,
             async: false,
             success: function(data){
                  locations_contacts = data;
             },
             error: function() {
                alert("There Was An Error Retrieving Location Contacts!");
             }
          });
      }

      function getLocations(city) {

          var url = '<?php echo API_HOST."/api/locations?columns=id,city,state,zip&filter[]=entityID,eq," . $_SESSION['entityid']; ?>';
          url += "&filter[]=city,sw," + city;
          var type = "GET";

          $.ajax({
             url: url,
             type: type,
             async: false,
             success: function(data){
                  alert(JSON.stringify(data));
             },
             error: function() {
                alert("There Was An Error Retrieving Location Contacts!");
             }
          });
      }

    function editOrderComment(){
        var orderDetailsTable = $("#order-details-table").DataTable();
        var json = orderDetailsTable.ajax.json();
        var data = json.order_details[0];
        var id = data.orders[0].id;

        var comments = $("#orderComments").val();

        var orderComments = {comments: comments};

        $.ajax({
           url: '<?php echo API_HOST."/api/orders/"; ?>' + id,
           type: "PUT",
           data: JSON.stringify(orderComments),
           contentType: "application/json",
           async: false,
           success: function(){
               alert("Comment Saved.");
           },
           error: function() {
               alert("Comment could not save.");
           }
        });
    }

    function closeOrderDetails(){
        var table = $("#orders-table").DataTable();
        $("#order-details").css("display", "none");
        $("#orders").css("display", "block");
        table.ajax.reload();
    }

    function saveDeliveryStatus(){


        $("#saveOrderStatus").html("<i class='fa fa-spinner fa-spin'></i> Saving Order Status");
        $("#saveOrderStatus").prop("disabled", true);

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
        var orderDetailTable = $('#order-details-table').DataTable();
        var orderDetailJSON = orderDetailTable.ajax.json();

        var orderNumber = orderDetailJSON.order_details[0].orders[0].orderID;
        var customerID = orderDetailJSON.order_details[0].orders[0].customerID;


        var id = $("#id").val();
        var city = $("#city").val();
        var state = $("#state").val();
        var status = $("#orderStatus").val();
        var notes = $("#statusNotes").val();
        var carrierID = $("#carrierID").val();

        var orderStatus = {orderID: id, carrierID:carrierID, city: city, state: state, status: status, note: notes, createdAt: today, updatedAt: today};

        var emailData = {carrierID: carrierID, customerID: customerID, orderNumber: orderNumber};

        $.ajax({
           url: '<?php echo API_HOST."/api/order_statuses"; ?>',
           type: "POST",
           data: JSON.stringify(orderStatus),
           contentType: "application/json",
           async: false,
           success: function(data){

                $.ajax({
                    url: '<?php echo HTTP_HOST; ?>' + '/sendorderstatusnotification',
                    type: "POST",
                    data: JSON.stringify(emailData),
                    contentType: "application/json",
                    async:false,
                    success: function(data){
                        alert(data);
                        $("#id").val('');
                        $("#city").val('');
                        $("#state").val('');
                        $("#orderStatus").val('');
                        $("#statusNotes").val('');
                        $("#carrierID").val('');

                        $("#saveOrderStatus").html("Save");
                        $("#saveOrderStatus").prop("disabled", false);
                        orderHistoryTable.ajax.reload();
                        $("#addOrderStatus").modal('hide');
                    },
                    error: function(error){
                        alert("Unable to send notification about status change.");
                        $("#saveOrderStatus").html("Save");
                        $("#saveOrderStatus").prop("disabled", false);
                    }
                });

           },
           error: function() {
              alert("There Was An Error Saving the Status");
                $("#saveOrderStatus").html("Save");
                $("#saveOrderStatus").prop("disabled", false);
           }
        });

    }

    function addDeliveryStatus() {
        var orderDetailsTable = $("#order-details-table").DataTable();
        var json = orderDetailsTable.ajax.json();
        var data = json.order_details[0];

        //console.log(data);

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

    function saveTrailerData(){

        var podTable = $("#pod-list-table").DataTable();
        var podList = podTable.ajax.json().orders[0].podList;
        var index = $('#index').val();
        var orderID = $('#orderID').val();

        var data = podList[index];
        var vinNumber = data.vinNumber;
        var podNotes = data.notes;
        var deliveryDate = data.deliveryDate;
        var fileName = data.fileName;
        var carrier = data.carrier;

        var pod = {vinNumber: vinNumber, notes: podNotes, deliveryDate: deliveryDate, fileName: fileName, carrier: carrier,
        unitNumber: $('#unitNumber').val(), truckProNumber: $('#truckProNumber').val(), trailerYear: $('#year').val(),
        trailerNotes: $('#trailerNotes').val()};

        podList.splice(index, 1, pod);

        var orderData = {podList: podList};

        $.ajax({
            url: '<?php echo API_HOST."/api/orders/"; ?>' + orderID,
            type: 'PUT',
            data: JSON.stringify(orderData),
            contentType: "application/json",
            async: false,
            success: function(){

                alert("Trailer Data Successfully Saved.");
                var podListTable = $('#pod-list-table').DataTable();
                podListTable.ajax.reload();

                // Clear Form
                $('#orderID').val('');
                $('#index').val('');
                $('#customerID').val('');
                $('#unitNumber').val('');
                $('#truckProNumber').val('');
                $('#year').val('');
                $('#trailerNotes').val('');
                $("#editTrailerData").modal('hide');

            },
            error: function(error){
                alert("Unable to Save Trailer Data to Orders.");
            }
        });

    }

    function viewPOD(){
	    window.open( '<?php echo HTTP_HOST."/viewdocument" ?>?filename=' + $("#fileName").val() + '&entityID=' + $("#customerID").val(), '_blank');
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
   <li class="active"> My Trailers in Route</li>
 </ol>
 <section  class="widget">
     <header>
         <h4><span class="fw-semi-bold">Order Details for My Trailers in Route</span></h4>
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

                  <?php

                  if ($_SESSION['entitytype'] == 0){

                      ?>

        <h5><span class="fw-semi-bold">Order ID: </span><p id="orderNumber" style="display: inline;"></p></h5>
        <h5><span class="fw-semi-bold">Customer Name: </span><p id="customerName" style="display: inline;"></p></h5>
        <br>
            <?php

                  }
            ?>
        <div class="widget-body">

            <div id="dataTable-1" class="mt">
                <table id="order-details-table" class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Carrier</th>
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
            <br>
            <div id="dataTable-2" class="mt">
                <h5><span class="fw-semi-bold">Order Tracking History</span></h5>
                <table id="order-history-table" class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Carrier</th>
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
                        <th></th>
                        <th>Trailer VIN</th>
                        <th>Carrier</th>
                        <th>Delivery Date</th>
                        <th>Notes</th>
                        <th class="no-sort pull-right"></th>
                        <th class="no-sort pull-right"></th>
                        <th class="no-sort pull-right"></th>
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
            <br>

            <div class="row">
                <div class="col-sm-12">
                  <label for="orderComments"><span class="fw-semi-bold">Comments</span></label>
                  <div class="form-group">
                      <textarea id="orderComments" rows="4" cols="50" class="form-control mb-sm" maxlength="600"></textarea>
                  </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <button id="editOrderComments" class="btn btn-primary" role="button" onclick="editOrderComment();"><i class="glyphicon glyphicon-edit text"></i> <span class="text">Edit Comments</span></button>
                </div>
            </div>
            <?php

            }

            ?>

        </div>

     </section>

 </div>

<!-- Add Order Status Modal -->
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
                <form id="formAddOrderStatus" class="register-form mt-lg">
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
                      <div class="col-sm-3">
                          <div class="form-group">
              <?php if ($_SESSION['entityid'] > 0) { ?>
                             <input type="hidden" id="carrierID" name="carrierID" value="<?php echo $_SESSION['entityid']; ?>" />
              <?php } else { ?>
                              <label for="carrierID">Carrier</label>
                              <select id="carrierID" name="carrierID" data-placeholder="Carrier" class="form-control chzn-select" required="required">
                                <option value="">*Select Carrier...</option>
               <?php
                                foreach($entities->entities->records as $value) {
                                    $selected = ($value[0] == $entity) ? 'selected=selected':'';
                                    echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                                }
               ?>
                              </select>
               <?php } ?>
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

<!-- Upload POD Modal -->
  <div class="modal fade" id="uploadPOD" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Upload POD</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
                <form id="formUploadPOD" class="register-form mt-lg">
                  <input type="hidden" id="orderID" name="orderID" value="" />
                  <input type="hidden" id="index" name="index" value="" />
                  <input type="hidden" id="customerID" name="customerID" value="" />
                  <div class="row">
                        <div class="col-sm-3">
                            <label for="vinNumber">VIN Number</label>
                            <div class="form-group">
                                <input type="text" id="vinNumber" name="vinNumber" class="form-control mb-sm" placeholder="VIN Number" readonly />
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label for="deliveryDate">Delivery Date</label>
                            <div class="form-group">
                              <div id="sandbox-container" class="input-group date  datepicker">
                                 <input type="text" id="deliveryDate" name="deliveryDate" class="form-control" placeholder="Delivery Date" required="required"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                              </div>
                            </div>
                        </div>
                      <div class="col-sm-3">
                          <div class="form-group">
              <?php if ($_SESSION['entityid'] > 0) { ?>
                             <input type="hidden" id="podCarrierID" name="podCarrierID" value="<?php echo $_SESSION['entityid']; ?>" />
              <?php } else { ?>
                              <label for="podCarrierID">Carrier</label>
                              <select id="podCarrierID" name="podCarrierID" data-placeholder="Carrier" class="form-control chzn-select" required="required">
                                <option value="">*Select Carrier...</option>
               <?php
                                foreach($entities->entities->records as $value) {
                                    $selected = ($value[0] == $entity) ? 'selected=selected':'';
                                    echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                                }
               ?>
                              </select>
               <?php } ?>
                          </div>
                      </div>
                        <div class="col-sm-3" id="sectionPOD">
                            <label for="filePOD">Upload POD</label>
                            <div class="form-group">
                            <input type="hidden" id="fileName" name="fileName" value="" />
                              <input type="file" id="filePOD" name="filePOD" class="form-control-file mb-sm"/>
                            </div>
                        </div>
                  </div>
                  <hr/>
                  <div class="row" id="replacePOD">
                      <div class="col-sm-12">
                        <label for="blnReplacePOD"><input type="checkbox" id="blnReplacePOD">Replace POD</label>
                      </div>
                  </div>
                  <div class="row">
                      <div class="col-sm-12">
                        <label for="podNotes">Notes</label>
                        <div class="form-group">
                            <textarea id="podNotes" rows="4" cols="50" class="form-control mb-sm" maxlength="600"></textarea>
                        </div>
                      </div>
                  </div>
                </form>
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary btn-md" id="viewPOD" onclick="viewPOD();">View POD</button>
           <button type="button" class="btn btn-primary btn-md" id="btnUploadPOD">Save POD</button>
        </div>
      </div>
    </div>
  </div>


 <!-- Edit Order Modal -->
 <div class="modal fade" id="editOrder" z-index="1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel"><strong>Edit Order</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formEditOrder" class="register-form mt-lg">
                 <input type="hidden" id="id" name="id" value="" />
                 <div class="row">
                     <div class="col-sm-12">
                         <div style="display: inline-block; font-weight: 400;">Trailers Available:</div>
                         <div id="qty" class="form-group" style="display: inline-block;">
                           <!--<input type="text" id="qty" name="qty" class="form-control mb-sm" placeholder="# Available"
                           required="required" readonly/>-->
                         </div>
                     </div>

                 </div>

                  <?php

                  if ($_SESSION['entitytype'] != 1){

                      ?>
                 <div class="row">
                     <div class="col-sm-3">
                         <label for="pickupLocation">Pickup Location</label>
                         <div class="form-group">
                             <input type="text" id="pickupLocation" name="pickupLocation" class="form-control mb-sm" placeholder="Pickup Location" maxlength="30"/>
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="pickupContactPerson">Contact Person</label>
                         <div class="form-group">
                           <input type="text" id="pickupContactPerson" name="pickupContactPerson" class="form-control mb-sm" placeholder="Contact Person" maxlength="30" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="pickupPhoneNumber">Phone Number</label>
                         <div class="form-group">
                           <input type="text" id="pickupPhoneNumber" name="pickupPhoneNumber" class="form-control mb-sm" placeholder="Phone Number" maxlength="30" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="pickupHoursOfOperation">Hours of Operation</label>
                         <div class="form-group">
                           <input type="text" id="pickupHoursOfOperation" name="pickupHoursOfOperation" class="form-control mb-sm" placeholder="Hours of Operation" maxlength="30" />
                         </div>
                     </div>
                 </div>
                 <?php
                  }
                 ?>
                 <div class="row">
                     <div class="col-sm-4">
                         <label for="originationAddress">Origination Address</label>
                         <div class="form-group">
                           <input type="text" id="originationAddress" name="originationAddress" class="form-control mb-sm" placeholder="Origin Address" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="originationCity">Origination City</label>
                         <div class="form-group">
                           <input type="hidden" id="originationLocationID" name="originationLocationID" />
                           <input type="text" id="originationCity" name="originationCity" class="form-control mb-sm" placeholder="Origin City"
                           required="required" />
                         </div>
                         <div id="suggesstion-box" class="frmSearch"></div>
                     </div>
                     <div class="col-sm-3">
                         <label for="originationState">Origination State</label>
                         <div class="form-group">
                           <select id="originationState" name="origitnaionState" data-placeholder="Origin State" class="form-control chzn-select" data-ui-jq="select2" required="required">
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
                     <div class="col-sm-2">
                         <label for="originationZip">Origination Zip</label>
                         <div class="form-group">
                           <input type="text" id="originationZip" name="originationZip" class="form-control mb-sm" placeholder="Origin Zip" />
                         </div>
                     </div>
                 </div>

                  <?php

                  if ($_SESSION['entitytype'] != 1){

                      ?>
                 <div class="row">
                     <div class="col-sm-3">
                         <label for="deliveryLocation">Delivery Location</label>
                         <div class="form-group">
                             <input type="text" id="deliveryLocation" name="deliveryLocation" class="form-control mb-sm" placeholder="Delivery Location" maxlength="30"/>
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="deliveryContactPerson">Contact Person</label>
                         <div class="form-group">
                           <input type="text" id="deliveryContactPerson" name="deliveryContactPerson" class="form-control mb-sm" placeholder="Contact Person" maxlength="30" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="deliveryPhoneNumber">Phone Number</label>
                         <div class="form-group">
                           <input type="text" id="deliveryPhoneNumber" name="deliveryPhoneNumber" class="form-control mb-sm" placeholder="Phone Number" maxlength="30" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="deliveryHoursOfOperation">Hours of Operation</label>
                         <div class="form-group">
                           <input type="text" id="deliveryHoursOfOperation" name="deliveryHoursOfOperation" class="form-control mb-sm" placeholder="Hours of Operation" maxlength="30" />
                         </div>
                     </div>
                 </div>
                 <?php
                  }
                 ?>
                 <div class="row">
                   <div class="col-sm-4">
                       <label for="destinationAddress">Destination Address</label>
                       <div class="form-group">
                         <input type="text" id="destinationAddress" name="destinationAddress" class="form-control mb-sm" placeholder="Destination Address" />
                       </div>
                   </div>
                   <div class="col-sm-3">
                       <label for="DestinationCity">Destination City</label>
                       <div class="form-group">
                         <input type="text" id="destinationCity" name="destinationCity" class="form-control mb-sm" placeholder="Dest. City"
                         required="required" />
                       </div>
                   </div>
                   <div class="col-sm-3">
                       <label for="destinationState">Destination State</label>
                       <div class="form-group">
                         <select id="destinationState" name="destinationState" data-placeholder="Dest. State" class="form-control chzn-select" data-ui-jq="select2" required="required">
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
                   <div class="col-sm-2">
                       <label for="destinationZip">Destination Zip</label>
                       <div class="form-group">
                         <input type="text" id="destinationZip" name="destinationZip" class="form-control mb-sm" placeholder="Dest. Zip" />
                       </div>
                   </div>
                 </div>
                 <hr />
                 <div class="row">
                     <div class="col-sm-3">
                         <?php if ($_SESSION['entityid'] > 0) { ?>
                            <input type="hidden" id="rate" name="rate" />
             <?php } else { ?>
                        <label for="rate">Rate</label>
                        <div class="form-group">
                           <input type="text" id="rate" name="rate" class="form-control mb-sm"
                              placeholder="Rate $" data-parsley-type="number" />
                        </div>
              <?php } ?>

                     </div>
                     <div class="col-sm-3">
                         <label for="rateType">Rate Type</label>
                         <div class="form-group">
                           <div class="d-inline-block"><input type="radio" id="rateType" name="rateType" value="Flat Rate" /> Flat Rate
                           &nbsp;&nbsp;<input type="radio" id="rateType" name="rateType" value="Mileage" /> Mileage</div>
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="transportationMode">Transportation Mode</label>
                         <div class="form-group">
                           <select id="transportationMode" name="transportationMode" class="form-control chzn-select" required="required">
                             <option value="">*Select Mode...</option>
            <?php
                             // Here is check
            ?>
                                <option value="Empty">Empty</option>
                                <option value="Load Out">Load Out</option>
                                <option value="Either (Empty or Load Out)">Either (Empty or Load Out)</option>
                           </select>
                         </div>
                     </div>
                     <div class="col-sm-3">
                         &nbsp;
                     </div>
                 </div>
                 <hr />
                 <div class="container" style="margin-top:20px;">
                     <div class="row">
                       <div class="col-xs-6">
                            <h5 class="text-center"><strong>Trailer Data</strong></h5>
                            <div class="well" style="max-height: 200px;overflow: auto;">
                                <ul id="dp-check-list-box" class="list-group">

                                </ul>
                            </div>
                        </div>
                        <div class="col-xs-6">
                             <h5 class="text-center"><strong>Trailer VIN Numbers</strong></h5>
                             <a data-widgster="addVINNumber" title="Add" href="Javascript:addVINNumber();"><i class="fa fa-plus-square-o"></i> Add VIN Number</a>

                             <div class="well" style="max-height: 200px;overflow: auto;">
                                 <ul id="input-list-box" class="list-group">

                                 </ul>
                             </div>
                         </div>
                     </div>
                 </div>
               </form>
       </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button id="load" type="button" class="btn btn-primary" onclick="return post();">Save Changes</button>
        </div>
      </div>
    </div>
  </div>

<!-- Edit Trailer Data Modal -->
  <div class="modal fade" id="editTrailerData" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Edit Trailer Data</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
                <form id="formUploadPOD" class="register-form mt-lg">
                  <input type="hidden" id="orderID" name="orderID" value="" />
                  <input type="hidden" id="index" name="index" value="" />
                  <input type="hidden" id="customerID" name="customerID" value="" />
                  <div class="row">
                        <div class="col-sm-3">
                            <label for="trailerVIN">Trailer VIN</label>
                            <div class="form-group">
                                <input type="text" id="trailerVIN" name="trailerVIN" class="form-control mb-sm" placeholder="VIN Number" readonly />
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="trailerCarrier">Carrier</label>
                                <input type="text" id="trailerCarrier" name="trailerCarrier" class="form-control mb-sm" placeholder="Carrier" readonly />
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label for="pickupDate">Pick Up Date</label>
                            <div class="form-group">
                                 <input type="text" id="pickupDate" name="pickupDate" class="form-control" placeholder="Pickup Date" readonly>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label for="dropOffDate">Drop Off Date</label>
                            <div class="form-group">
                                 <input type="text" id="dropOffDate" name="dropOffDate" class="form-control" placeholder="Drop Off Date" readonly>
                            </div>
                        </div>
                  </div>
                  <hr/>
                  <div class="row">
                        <div class="col-sm-4">
                            <label for="unitNumber">Unit #</label>
                            <div class="form-group">
                                 <input type="text" id="unitNumber" name="unitNumber" class="form-control" placeholder="Unit #" maxlength="30">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label for="truckProNumber">Truck-Pro #</label>
                            <div class="form-group">
                                 <input type="text" id="truckProNumber" name="truckProNumber" class="form-control" placeholder="Truck-Pro #" maxlength="30">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label for="year">Year</label>
                            <div class="form-group">
                                 <input type="text" id="year" name="year" class="form-control" placeholder="Year" maxlength="30">
                            </div>
                        </div>
                        <div class="col-sm-4">
                        <label for="trailerNotes">Notes</label>
                            <div class="form-group">
                                 <input type="text" id="trailerNotes" name="trailerNotes" class="form-control" placeholder="Notes" maxlength="30">
                            </div>
                        </div>
                  </div>
                </form>
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary btn-md" id="btnSaveTrailerData" onclick="saveTrailerData()">Save Trailer Data</button>
        </div>
      </div>
    </div>
  </div>

<!-- Missing Trailer Data -->
<div class="modal fade" id="trailer-data-missing" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Missing Trailer Data</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            Trailer data is missing. Click "Edit Trailer Data" to complete this process.
            
            Thank you.
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

 <script>

    $(document).ready(function(){

    loadTableAJAX();

    $('.datepicker').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });

    var table = $("#orders-table").DataTable();

    $("#order-details").css("display", "none");


    $('#order-details-table tbody').on( 'click', 'button', function () {

        var table = $("#order-details-table").DataTable();
        var data = table.row( $(this).parents('tr') ).data();

        var orderID = data.orders[0].id;

        $("#id").val(orderID);

        var url = '<?php echo API_HOST . '/api/orders/' ?>' + orderID;

        $.ajax({
            url: url,
            type: "GET",
            contentType: "application/json",
            async: false,
            success: function(data){

            var dpli = '';
            var dpchecked = '';
            $("#id").val(data["id"]);
            $("#entityID").val(data["entityID"]);
            $("#qty").text(data["qty"]);
            $("#rate").val(data["customerRate"].toFixed(2));
<?php if ($_SESSION['entityid'] > 0) { ?>
            $("#rate").prop("disabled", true);
<?php } ?>


            if(data["pickupInformation"] == null){
                $("#pickupLocation").val('');
                $("#pickupContactPerson").val('');
                $("#pickupPhoneNumber").val('');
                $("#pickupHoursOfOperation").val('');
            }
            else{
                $("#pickupLocation").val(data["pickupInformation"].pickupLocation);
                $("#pickupContactPerson").val(data["pickupInformation"].contactPerson);
                $("#pickupPhoneNumber").val(data["pickupInformation"].phoneNumber);
                $("#pickupHoursOfOperation").val(data["pickupInformation"].hoursOfOperation);
            }

            if(data["deliveryInformation"] == null){
                $("#deliveryLocation").val('');
                $("#deliveryContactPerson").val('');
                $("#deliveryPhoneNumber").val('');
                $("#deliveryHoursOfOperation").val('');
            }
            else{
                $("#deliveryLocation").val(data["deliveryInformation"].deliveryLocation);
                $("#deliveryContactPerson").val(data["deliveryInformation"].contactPerson);
                $("#deliveryPhoneNumber").val(data["deliveryInformation"].phoneNumber);
                $("#deliveryHoursOfOperation").val(data["deliveryInformation"].hoursOfOperation);
            }

            $("#transportationMode").val(data["transportationMode"]);
            $("#originationAddress").val(data["originationAddress"]);
            $("#originationCity").val(data["originationCity"]);
            $("#originationState").val(data["originationState"]);
            $("#originationZip").val(data["originationZip"]);
            $("#destinationAddress").val(data["destinationAddress"]);
            $("#destinationCity").val(data["destinationCity"]);
            $("#destinationState").val(data["destinationState"]);
            $("#destinationZip").val(data["destinationZip"]);
            var ndp = data["needsDataPoints"];
            var pod = data["podList"];

            if (data['rateType'] == "Flat Rate") {
                $('input[name="rateType"][value="Flat Rate"]').prop('checked', true);
            } else {
                $('input[name="rateType"][value="Mileage"]').prop('checked', true);
            }

            if (pod != null){
                var li = '';
                for (var i = 0; i < pod.length; i++) {

                    li += '<li class="list-group-item"><input type="text" class="form-control" value="' + pod[i].vinNumber + '"></li>\n';
                }
                $("#input-list-box").html(li);

            }


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

                dpli += '<li>' + dataPoints.object_type_data_points[i].title +
                        ' <select class="form-control mb-sm" id="' + dataPoints.object_type_data_points[i].columnName + '" name="' + dataPoints.object_type_data_points[i].columnName + '" disabled>';
                for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {

                    if (dataPoints.object_type_data_points[i].object_type_data_point_values[v].value === value) {
                        selected = ' selected ';
                    } else {
                        selected = '';
                    }

                    dpli += '<option' + selected + '>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</option>\n';

                }

                dpli += '</select>' +
                        '</li>\n';
            }
            $("#dp-check-list-box").html(dpli);
            //formatListBox();
            formatListBoxDP();
            $("#entityID").prop('disabled', true);
            $("#editOrder").modal('show');
            }
        });
    });

    $('#orders-table tbody').on( 'click', 'td.order-details-link', function () {
        var data = table.row( $(this).parents('tr') ).data();

        var orderID = data["id"];

        getOrderIDAndCustomerName(orderID);
        loadOrderDetailsAJAX(orderID);
        loadOrderStatusesAJAX(orderID);
        loadPODListAJAX(orderID);
        loadOrderComments(orderID);

    });

    // Formatting function for row details - modify as you need
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

    $('#pod-list-table tbody').off('click', 'td button.download-pod').on('click', 'td button.download-pod', function () {

        var podTable = $("#pod-list-table").DataTable();
        var pod = podTable.row( $(this).parents('tr') ).data();
        var podList = podTable.ajax.json().orders[0].podList;

        var orderDetailsTable = $("#order-details-table").DataTable();
        var orderDetails = orderDetailsTable.ajax.json();
        var orderID = orderDetails.order_details[0].orderID;

        var url = '<?php echo API_HOST . '/api/orders/' ?>' + orderID;

        $.ajax({
            url: url,
            type: "GET",
            contentType: "application/json",
            async: false,
            success: function(data){
                var customerID = data.customerID;
                var customerName = "";

                allEntities.entities.forEach(function(entity){

                    if(customerID == entity.id){

                        customerName = entity.name;
                    }
                });

                var size = data.needsDataPoints[3].length + ' x ' + data.needsDataPoints[4].width + ' x ' + data.needsDataPoints[0].height;

                var podDataJSON = {
                    podFormType: customerName,
                    unitNumber: pod.unitNumber, vinNumber: pod.vinNumber, trailerProNumber: pod.truckProNumber, year: pod.trailerYear,
                    size: size, type: data.needsDataPoints[5].type, door: data.needsDataPoints[1].door, decals: data.needsDataPoints[13].decals,
                    originationAddress: data.originationAddress, originationCity: data.originationCity, originationState: data.originationState, originationZipcode: data.originationZip,
                    destinationAddress: data.destinationAddress, destinationCity: data.destinationCity, destinationState: data.destinationState, destinationZipcode: data.destinationZip,
                    pickupLocation: data.pickupInformation.pickupLocation, pickupContact: data.pickupInformation.contactPerson,
                    pickupPhoneNumber: data.pickupInformation.phoneNumber, pickupHours: data.pickupInformation.hoursOfOperation,
                    deliveryLocation: data.deliveryInformation.deliveryLocation, deliveryContact: data.deliveryInformation.contactPerson,
                    deliveryPhoneNumber: data.deliveryInformation.phoneNumber, deliveryHours: data.deliveryInformation.hoursOfOperation
                };
                
                var podURL = '<?php echo HTTP_HOST . '/pod_form_api'; ?>';

                $.ajax({
                    url: podURL,
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(podDataJSON),
                    success: function(data){
                        var win = window.open('about:blank'); 
                        var document = win.document;
                        document.open();
                        document.write(data);
                        document.close();

                    },
                    error: function(data){
                        console.log("Could not get POD Form.");
                    }
                });
            },
            error: function(data){
                console.log("Could not get Order Information.");
            }
        });

    } );

    $('#pod-list-table tbody').on('click', 'button.trailer-data-missing', function () {

        $("#trailer-data-missing").modal('show');
    } );
    $('#pod-list-table tbody').on('click', 'button.upload-pod', function () {

        var podTable = $("#pod-list-table").DataTable();
        var data = podTable.row( $(this).parents('tr') ).data();
        var podList = podTable.ajax.json().orders[0].podList;
        var index = podList.indexOf(data);
        var vinNumber = data.vinNumber;

        var orderDetailsTable = $("#order-details-table").DataTable();
        var orderDetails = orderDetailsTable.ajax.json();

        var orderID = orderDetails.order_details[0].orderID;
        var customerID = orderDetails.order_details[0].orders[0].customerID;

        $('#vinNumber').val(vinNumber);
        $('#index').val(index);
        $('#orderID').val(orderID);
        $('#customerID').val(customerID);
        $('#filePOD').val('');
        $('#deliveryDate').val('');
        $('#podNotes').val('');
        $('#fileName').val('');
        $('#viewPOD').hide();
        $('#replacePOD').hide();
        $('#sectionPOD').show();
        $('#blnReplacePOD').attr('checked', false);
        $("#uploadPOD").modal('show');
    } );

    $('#pod-list-table tbody').on('click', 'button.view-edit-pod', function () {

        var podTable = $("#pod-list-table").DataTable();
        var data = podTable.row( $(this).parents('tr') ).data();
        var podList = podTable.ajax.json().orders[0].podList;
        var index = podList.indexOf(data);
        var vinNumber = data.vinNumber;

        var podNotes = data.notes;
        var deliveryDate = data.deliveryDate;
        var fileName = data.fileName;

        var carrierID = 0;
        var carrier = data.carrier;

        allEntities.entities.forEach(function(entity){

            if(carrier == entity.name){

                carrierID = entity.id;
            }
        });

        var orderDetailsTable = $("#order-details-table").DataTable();
        var orderDetails = orderDetailsTable.ajax.json();

        var orderID = orderDetails.order_details[0].orderID;
        var customerID = orderDetails.order_details[0].orders[0].customerID;

        $('#vinNumber').val(vinNumber);
        $('#index').val(index);
        $('#orderID').val(orderID);
        $('#customerID').val(customerID);
        $('#filePOD').val('');
        $('#deliveryDate').val(deliveryDate);
        $('#podNotes').val(podNotes);
        $('#podCarrierID').val(carrierID);
        $('#fileName').val(fileName);

        $('#viewPOD').show();
        $('#replacePOD').show();
        $('#sectionPOD').hide();
        $('#blnReplacePOD').attr('checked', false);
        $("#uploadPOD").modal('show');
    } );

    $('#pod-list-table tbody').on('click', 'button.edit-trailer-data', function () {

        var orderDetailsTable = $("#order-details-table").DataTable();
        var orderDetails = orderDetailsTable.ajax.json();

        var orderID = orderDetails.order_details[0].orderID;
        var customerID = orderDetails.order_details[0].orders[0].customerID;

        var podTable = $("#pod-list-table").DataTable();
        var data = podTable.row( $(this).parents('tr') ).data();
        var podList = podTable.ajax.json().orders[0].podList;
        var carrierIDs = podTable.ajax.json().orders[0].carrierIDs;
        var index = podList.indexOf(data);
        var vinNumber = data.vinNumber;

        var unitNumber = data.unitNumber;
        var truckProNumber = data.truckProNumber;
        var trailerYear = data.trailerYear;
        var trailerNotes = data.trailerNotes;

        var entityName = "";

        for(var i = 0; i < carrierIDs.length; i++){

            if(i > 0) entityName += ", ";

            allEntities.entities.forEach(function(entity){

                if(carrierIDs[i].carrierID == entity.id){

                    entityName += entity.name;
                }
            });

        }


        var url = '<?php echo API_HOST; ?>';
        url += '/api/order_details?filter=orderID,eq,' + orderID + '&transform=1';

        $.ajax({
           url: url,
           type: "GET",
           contentType: "application/json",
           success: function(json){

                var order_details = json.order_details;
                var earliestPickup = order_details[0].pickupDate;
                var latestDelivery = order_details[0].deliveryDate;

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
                }


                var pickupDate = earliestPickup;
                var dropoffDate = latestDelivery;

                $('#pickupDate').val(pickupDate);
                $('#dropOffDate').val(dropoffDate);
           }
        });


        $('#trailerVIN').val(vinNumber);
        $('#index').val(index);
        $('#orderID').val(orderID);
        $('#customerID').val(customerID);
        $('#trailerCarrier').val(entityName);

        $('#unitNumber').val(unitNumber);
        $('#truckProNumber').val(truckProNumber);
        $('#year').val(trailerYear);
        $('#trailerNotes').val(trailerNotes);

        $("#editTrailerData").modal('show');
    } );

    function uploadPOD(){

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

        today = mm+'/'+dd+'/'+yyyy;
        today = yyyy+"-"+mm+"-"+dd+" "+hours+":"+min+":"+sec;

        var carrierID = $('#podCarrierID').val();
        var carrier = "";

        allEntities.entities.forEach(function(entity){

            if(carrierID == entity.id){

                carrier = entity.name;
            }
        });

        var formData = new FormData();
        var fileData = $('#filePOD')[0].files[0];
        formData.append('entityID', $("#customerID").val());
        formData.append('name', $("#vinNumber").val());
        formData.append('fileupload', fileData);

        var url = '<?php echo HTTP_HOST."/uploaddocument" ?>';
	var type = "POST";

        if(fileData != undefined){
            $.ajax({
                url : url,
                type : 'POST',
                data : formData,
                processData: false,  // tell jQuery not to process the data
                contentType: false,  // tell jQuery not to set contentType
                success : function(data) {
                    var files = $('#filePOD').prop("files");
                    var fileNames = $.map(files, function(val) { return val.name; }).join(',');
                    var podTable = $("#pod-list-table").DataTable();
                    var podList = podTable.ajax.json().orders[0].podList;
                    var index = $('#index').val();
                    var orderID = $('#orderID').val();

                    var pod = {vinNumber: $('#vinNumber').val(), notes: $('#podNotes').val(), deliveryDate: $('#deliveryDate').val(), fileName: fileNames, carrier: carrier};

                    podList.splice(index, 1, pod);

                    var orderData = {podList: podList};

                    $.ajax({
                        url: '<?php echo API_HOST."/api/orders/"; ?>' + orderID,
                        type: 'PUT',
                        data: JSON.stringify(orderData),
                        contentType: "application/json",
                        async: false,
                        success: function(){

                            alert("POD Successfully Uploaded.");
                            var podListTable = $('#pod-list-table').DataTable();
                            podListTable.ajax.reload();

                            // Clear Form
                            $('#orderID').val('');
                            $('#index').val('');
                            $('#customerID').val('');
                            $('#vinNumber').val('');
                            $('#deliveryDate').val('');
                            $('#podCarrierID').val('');
                            $('#podNotes').val('');
                            $("#uploadPOD").modal('hide');

                        },
                        error: function(error){
                            alert("Unable to Save POD List to Orders.");
                        }
                    });

                },
                error: function(error){
                    alert("Unable to Upload POD File.");
                }
            });
        }
        else{
            alert("You must select a file to upload.");
        }
    }

    function savePODInfo(){

        var carrierID = $('#podCarrierID').val();
        var carrier = "";

        allEntities.entities.forEach(function(entity){

            if(carrierID == entity.id){

                carrier = entity.name;
            }
        });

        var podTable = $("#pod-list-table").DataTable();
        var podList = podTable.ajax.json().orders[0].podList;
        var index = $('#index').val();
        var orderID = $('#orderID').val();
        var fileName = $('#fileName').val();

        var data = podList[index];
        var unitNumber = data.unitNumber;
        var truckProNumber = data.truckProNumber;
        var trailerYear = data.trailerYear;
        var trailerNotes = data.trailerNotes;

        var pod = {vinNumber: $('#vinNumber').val(), notes: $('#podNotes').val(), deliveryDate: $('#deliveryDate').val(), fileName: fileName, carrier: carrier,
        unitNumber: unitNumber, truckProNumber: truckProNumber, trailerYear: trailerYear, trailerNotes: trailerNotes};

        podList.splice(index, 1, pod);

        var orderData = {podList: podList};

        $.ajax({
            url: '<?php echo API_HOST."/api/orders/"; ?>' + orderID,
            type: 'PUT',
            data: JSON.stringify(orderData),
            contentType: "application/json",
            async: false,
            success: function(){

                alert("POD Info Successfully Saved.");
                var podListTable = $('#pod-list-table').DataTable();
                podListTable.ajax.reload();

                // Clear Form
                $('#orderID').val('');
                $('#index').val('');
                $('#customerID').val('');
                $('#vinNumber').val('');
                $('#deliveryDate').val('');
                $('#podCarrierID').val('');
                $('#podNotes').val('');
                $("#uploadPOD").modal('hide');

            },
            error: function(error){
                alert("Unable to Save POD List to Orders.");
            }
        });

    }

    $('#blnReplacePOD').change(function(){
        if($(this).is(":checked")){
            $('#sectionPOD').show();
        }
        else{
            $('#sectionPOD').hide();
        }
    });


    $("#btnUploadPOD").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates

            console.log($('#fileName').val());

        // fileName will tell us if we're in Upload Mode or View/Edit Mode
        if($('#fileName').val() == ""){
            // We are in Upload mode,
            // Lets upload POD
            uploadPOD();
        }
        else{
            // We are in View/Edit Mode
            // Now we need to know if we need to replace the POD
            if($('#blnReplacePOD').is(":checked")){
                // We need to replace the POD
                // So we upload
                uploadPOD();
            }
            else{
                // Otherwise,
                // We save the POD Info
                savePODInfo();
            }
        }
    });

    });
 </script>
