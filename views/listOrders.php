<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

if ($_SESSION['userid'] <= 0 || $_SESSION['userid'] == "") {
    header("Location: " . HTTP_HOST . "/logout");
}

/*
if(auto_logout("login_time")) {
    session_unset();
    session_destroy();
    header("Location: ".HTTP_HOST."/logout");
    exit();
}
*/

$state = '';
$states = json_decode(file_get_contents(API_HOST_URL . '/states?columns=abbreviation,name&order=name'));

$entity = '';
$entity = json_decode(file_get_contents(API_HOST_URL . '/entities?columns=rateType,negotiatedRate&filter[]=id,eq,' . $_SESSION['entityid']));

$entities = '';
$entities = json_decode(file_get_contents(API_HOST_URL . '/entities?columns=id,name&order=name&filter[]=id,gt,0&filter[]=entityTypeID,eq,2'));

$carrierEntities = '';
$carrierEntities = json_decode(file_get_contents(API_HOST_URL . '/entities?columns=id,name&order=name&filter[]=id,gt,0&filter[]=entityTypeID,eq,2&transform=1'));

$allEntities = '';
$allEntities = json_decode(file_get_contents(API_HOST_URL . '/entities?columns=id,name&order=name&transform=1'));


$locationTypeID = '';
$locationTypes = json_decode(file_get_contents(API_HOST_URL . "/location_types?columns=id,name,status&filter[]=entityID,eq," . $_SESSION['entityid'] . "&filter[]=id,gt,0&satisfy=all&order=name"));

$contacts = '';
$contacts = json_decode(file_get_contents(API_HOST_URL . "/contacts?columns=id,firstName,lastName&order=lastName&filter=entityID,eq," . $_SESSION['entityid'] ));

$locations_contacts = '';
$locations_contacts = json_decode(file_get_contents(API_HOST_URL . "/locations_contacts?columns=location_id,contact_id&filter=entityID,eq," . $_SESSION['entityid'] ));

$loccon = array();
for ($lc=0;$lc<count($locations_contacts->locations_contacts->records);$lc++) {
    $loccon[$locations_contacts->locations_contacts->records[$lc][0]] = $locations_contacts->locations_contacts->records[$lc][1];
}

$dataPoints = json_decode(file_get_contents(API_HOST_URL . "/object_type_data_points?include=object_type_data_point_values&transform=1&columns=id,columnName,title,status,object_type_data_point_values.value&filter[]=entityID,in,(0," . $_SESSION['entityid'] . ")&filter[]=status,eq,Active" ));

$customer_needs_root = '';
$customer_needs_root = json_decode(file_get_contents(API_HOST_URL . "/customer_needs?columns=rootCustomerNeedsID&transform=1"));


 ?>

 <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

 <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API; ?>"></script>
 <!--script type="text/javascript" src="https://maps.google.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API; ?>"></script-->

 <script>
(function($) {
    $.fn.blink = function(options) {
        var defaults = {
            delay: 500
        };
        var options = $.extend(defaults, options);

        return this.each(function() {
            var obj = $(this);
            setInterval(function() {
                if ($(obj).css("visibility") == "visible") {
                    $(obj).css('visibility', 'hidden');
                }
                else {
                    $(obj).css('visibility', 'visible');
                }
            }, options.delay);
        });
    }
}(jQuery))

    var contacts = <?php echo json_encode($contacts); ?>;

    var locations_contacts = <?php echo json_encode($locations_contacts); ?>;

    var dataPoints = <?php echo json_encode($dataPoints); ?>;

    var entity = <?php echo json_encode($entity); ?>;

    var userid = <?php echo $_SESSION['userid']; ?>;

    var entityid = <?php echo $_SESSION['entityid']; ?>;

    var allEntities = <?php echo json_encode($allEntities); ?>;

    var customerNeedsRootIDs = <?php echo json_encode($customer_needs_root)?>;

    var entityType = <?php echo $_SESSION['entitytype'];  ?>;

    var carrierEntities = <?php echo json_encode($carrierEntities); ?>;
    var vinNumbersList = [];
    var existingPODList = [];

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

    Date.daysBetween = function( date1, date2 ) {
      //Get 1 day in milliseconds
      var one_day=1000*60*60*24;

      // Convert both dates to milliseconds
      var date1_ms = date1.getTime();
      var date2_ms = date2.getTime();

      // Calculate the difference in milliseconds
      var difference_ms = date2_ms - date1_ms;

      // Convert back to days and return
      return Math.round(difference_ms/one_day);
    }

    function loadVinNumberList(){
        var option = '';

        $('#orderStatusVinNumber').empty();

        for(var i=0; i< vinNumbersList.length; i++){
            option += '<option value="' + vinNumbersList[i] + '">' + vinNumbersList[i] + '</option>';
        }

        $('#orderStatusVinNumber').append(option);
    }

    function displayOrderStatuses(orderID, carrierID, vinNumber){

        var orderStatusURL = '';
        if(carrierID == "") orderStatusURL = '<?php echo API_HOST_URL; ?>' + '/order_statuses?filter[0]=orderID,eq,' + orderID + '&filter[1]=vinNumber,eq,' + vinNumber + '&order=updatedAt,desc&transform=1';
        else orderStatusURL = '<?php echo API_HOST_URL; ?>' + '/order_statuses?filter[0]=orderID,eq,' + orderID + '&filter[1]=vinNumber,eq,' + vinNumber + '&filter[2]=carrierID,eq,' + carrierID + '&order=updatedAt,desc&transform=1';

        $.get(orderStatusURL, function(data){
            var statuses = data.order_statuses;

            var statusesList = "<div class=\"row\">";

            if (statuses.length == 0){
                statusesList += "<div class=\"col-md-12\"><h3>There are no statuses available.</<h3></div>";
            }
            else{
                $.each(statuses, function(key, status){
                    var index = key + 1;
                    var carrierName = "";

                    allEntities.entities.forEach(function(entity){

                        if(status.carrierID == entity.id){

                            carrierName += entity.name;
                        }
                    });

                    var dimmed = "";

                    statusesList += "<div class=\"col-md-4\">" +
                                    "   <div class=\"carrier-tracking__panel " + dimmed + "\">" +
                                    "       <div class=\"row\">" +
                                    "           <div class=\"col-md-3\">" +
                                    "               <img src=\"img/logo-truck-warrior.png\" width=\"53\" height=\"44\" alt=\"\"/>" +
                                    "           </div>" +
                                    "           <div class=\"col-md-9\">" +
                                    "               <h5 class=\"text-bright-blue\">" + carrierName + "</h5>" +
                                    "           </div>" +
                                    "       </div>" +
                                    "       <hr>" +
                                    "       <div class=\"row\">" +
                                    "           <div class=\"col-md-4\">" +
                                    "               <div class=\"row\">" +
                                    "                   <div class=\"col-md-12\">" +
                                    "                       <span class=\"text-blue\">Trailer Status:</span><br>" +
                                    "                   </div>" +
                                    "               </div>" +
                                    "               <div class=\"row\">" +
                                    "                   <div class=\"col-md-12\">" +
                                    "                       <span class=\"text-blue\">Loading Status:</span><br>" +
                                    "                   </div>" +
                                    "               </div>" +
                                    "               <div class=\"row\">" +
                                    "                   <div class=\"col-md-12\">" +
                                    "                       <span class=\"text-blue\">Last Location:</span><br>" +
                                    "                   </div>" +
                                    "               </div>" +
                                    "               <div class=\"row\">" +
                                    "                   <div class=\"col-md-12\">" +
                                    "                       <span class=\"text-blue\">Date:</span><br>" +
                                    "                   </div>" +
                                    "               </div>" +
                                    "               <div class=\"row\">" +
                                    "                   <div class=\"col-md-12\">" +
                                    "                       <span class=\"text-blue\">Arrival Eta:</span><br>" +
                                    "                   </div>" +
                                    "               </div>" +
                                    "           </div>" +
                                    "           <div class=\"col-md-8\">" +
                                    "               <div class=\"row\">" +
                                    "                   <div class=\"col-md-12\">" +
                                    "                       " + status.status + "<br>" +
                                    "                   </div>" +
                                    "               </div>" +
                                    "               <div class=\"row\">" +
                                    "                   <div class=\"col-md-12\">" +
                                    "                       " + status.loadingStatus + "<br>" +
                                    "                   </div>" +
                                    "               </div>" +
                                    "               <div class=\"row\">" +
                                    "                   <div class=\"col-md-12\">" +
                                    "                       "+status.city+", " + status.state + "<br>" +
                                    "                   </div>" +
                                    "               </div>" +
                                    "               <div class=\"row\">" +
                                    "                   <div class=\"col-md-12\">" +
                                    "                       " + status.updatedAt + "<br>" +
                                    "                   </div>" +
                                    "               </div>" +
                                    "               <div class=\"row\">" +
                                    "                   <div class=\"col-md-12\">" +
                                    "                       " + status.arrivalEta + " Hrs<br>" +
                                    "                   </div>" +
                                    "               </div>" +
                                    "           </div>" +
                                    "       </div>" +
                                    "       <hr>";
                    if(status.documentID > 0){
                        statusesList += "       <div class=\"row\">" +
                                        "           <div class=\"col-md-6\">" +
                                        "               <button type=\"button\" id=\"viewPOD\" class=\"btn btn-primary w-100\" onclick=\"viewPOD(" + status.documentID + ");\">View POD</button>" +
                                        "           </div>";
                                
                                
            <?php
                if($_SESSION['entitytype'] == 0){
            ?>
    
                        if(status.hasBeenApproved == 0){
                            statusesList += "       <div class=\"col-md-6\">" +
                                            "           <button type=\"button\" id=\"approvePOD\" class=\"btn btn-primary w-100\" onclick=\"confirmApprovePOD(" + status.carrierID + ", '" + vinNumber + "'," + status.documentID + ", " + status.orderDetailID + ", " + orderID + ", " + status.id + ", '" + status.unitNumber + "');\">Approve POD</button>" +
                                            "       </div>";
                        }
                        else{
                            statusesList += "       <div class=\"col-md-6\">" +
                                            "           <button type=\"button\" id=\"approvePOD\" class=\"btn btn-primary w-100 disabled\" disabled>Approved</button>" +
                                            "       </div>";
                        }
                <?php }?>

                        statusesList += "       </div>" +
                                        "       <hr>";
                    }
                    statusesList += "       <ul class=\"list-inline\">" +
                                    "           <li class=\"list-inline-item\">Add a Note</li>" +
                                    "           <li class=\"list-inline-item pad-left-25\"><span class=\"fa fa-pencil text-bright-blue\"></span></li>" +
                                    "       </ul>" +
                                    "       <p>Note: " + status.note + "</p>" +
                                    "   </div>" +
                                    "</div>";

                    if(index % 3 == 0){
                        statusesList +="</div><br><div class=\"row\">";
                    }
                });
            }

            statusesList += "</div>";
            $("#statusesList").empty().html(statusesList);

        });
    }

    function confirmApprovePOD(carrierID, vinNumber, documentID, orderDetailID, orderID, statusID, unitNumber){

        var orderURL = '<?php echo API_HOST_URL; ?>' + '/orders/' + orderID;

        $.get(orderURL, function(order){

            var customerID = order.customerID;
            $('#approveCustomerID').val(customerID);

            var customerRate = order.customerRate;
            var qty = order.qty;
            var cost = customerRate / qty;
            $('#approveCost').val(Math.round(cost));

        });

        $('#approveOrderID').val(orderID);
        $('#approveOrderDetailID').val(orderDetailID);
        $('#approveCarrierID').val(carrierID);
        $('#approveDocumentID').val(documentID);
        $('#approveVinNumber').val(vinNumber);
        $('#approveUnitNumber').val(unitNumber);
        $('#approveStatusID').val(statusID);

        $('#approvePODModal').modal('show');
    }

    function addVINNumber(){

		var count = $('#input-list-box > li').length;
		var id = (count !== 0 ) ? count - 1 : 1;
        var li = '';
        li += '<li id="list-box-' + id + '" class="list-group-item"><input type="text" class="form-control" value=""></li>\n';
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
                  $.ajax({
                     url: '<?php echo HTTP_HOST."/getlocationbycitystatezip" ?>',
                     type: 'POST',
                     data: JSON.stringify(params),
                     contentType: "application/json",
                     async: false,
                     success: function(response){
                        if (response == "success") {
                        } else {
                            result = false;
                              $("#errorAlertTitle").html("Error");
                              $("#errorAlertBody").html("Preparation Failed!");
                              $("#errorAlert").modal('show');
                        }
                     },
                     error: function(response) {
                        result = false;
                          $("#errorAlertTitle").html("Failed Searching for Destination Location!");
                          $("#errorAlertBody").html("Notify NEC of this failure.");
                          $("#errorAlert").modal('show');
                     }
                  });
              } else {
                  result = false;
                  $("#errorAlertTitle").html("Error");
                  $("#errorAlertBody").html("Preparation Failed!");
                  $("#errorAlert").modal('show');
              }
           },
           error: function(response) {
              result = false;
              $("#errorAlertTitle").html("Failed Searching for Origination Location!");
              $("#errorAlertBody").html("Notify NEC of this failure.");
              $("#errorAlert").modal('show');
           }
        });

        if (result) {
          verifyAndPost(function(data) {
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

                              var url = '<?php echo API_HOST_URL . "/orders/"; ?>' + $("#id").val();
                              type = "PUT";


                            // Build the podList
                            var podArray = [];
                            var obj = $("#input-list-box li");
                            var item = {};
                            for (var i = 0; i < obj.length; i++) {
                                var blnMatch = false;
                                var newVinNumber = obj[i].firstChild.value.trim();

                                if (newVinNumber != ""){

                                    for(var j=0; j < existingPODList.length; j++){
                                        if(existingPODList[j].vinNumber == newVinNumber){
                                            blnMatch = true;
                                            podArray.push(existingPODList[j]);
                                            break;
                                        }
                                    }

                                    if(!blnMatch){
                                      item = {vinNumber: newVinNumber, deliveryDate: "", notes: "", fileName: "", carrier: ""};
                                      podArray.push(item);
                                    }

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

                              var decal = {};
                              decal['decals'] = $("#decals").val();
                              needsarray.push(decal);

                            var needsdatapoints = needsarray;

                            var pickupInformation = {
                                pickupLocation: $("#pickupLocation").val(),
                                contactPerson: $("#pickupContactPerson").val(),
                                phoneNumber: $("#pickupPhoneNumber").val(),
                                //hoursOfOperation: $("#pickupHoursOfOperation").val()
                                pickupHoursOfOperationOpen: $("#pickupHoursOfOperationOpen").val(),
                                pickupHoursOfOperationClose: $("#pickupHoursOfOperationClose").val(),
                                pickupTimeZone: $("#pickupTimeZone").val()
                            };

                            var deliveryInformation  = {
                                deliveryLocation: $("#deliveryLocation").val(),
                                contactPerson: $("#deliveryContactPerson").val(),
                                phoneNumber: $("#deliveryPhoneNumber").val(),
                                //hoursOfOperation: $("#deliveryHoursOfOperation").val()
                                deliveryHoursOfOperationOpen: $("#deliveryHoursOfOperationOpen").val(),
                                deliveryHoursOfOperationClose: $("#deliveryHoursOfOperationClose").val(),
                                deliveryTimeZone: $("#deliveryTimeZone").val()
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

                                              if(entityid > 0){
                                                  $("#errorAlertTitle").html("Error");
                                                  $("#errorAlertBody").html(data);
                                                  $("#errorAlert").modal('show');
                                              }

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
                                              $("#load").html("Save Changes");
                                              $("#load").prop("disabled", false);

                                              $("#errorAlertTitle").html("Notification Error");
                                              $("#errorAlertBody").html(JSON.stringify(data));
                                              $("#errorAlert").modal('show');
                                        }
                                    });

                                  } else {
                                      $("#load").html("Save Changes");
                                      $("#load").prop("disabled", false);

                                      $("#errorAlertTitle").html("Error");
                                      $("#errorAlertBody").html("Editing Order Failed! Please Verify Your Data.");
                                      $("#errorAlert").modal('show');
                                  }
                               },
                               error: function() {
                                  $("#load").html("Save Changes");
                                  $("#load").prop("disabled", false);

                                  $("#errorAlertTitle").html("Error");
                                  $("#errorAlertBody").html("There Was An Error Editing The Order!");
                                  $("#errorAlert").modal('show');
                               }
                            });

                            return passValidation;
            });
    }

    function loadOrderNotes(orderID){

        var url = '<?php echo API_HOST_URL; ?>';

        url += '/order_notes?include=members&columns=id,userID,note,createdAt,updatedAt,members.firstName,members.lastName&filter=orderID,eq,' + orderID + '&order=id,desc&transform=1';

        if ( ! $.fn.DataTable.isDataTable( '#admin-note-table' ) ) {

            var orders_table = $('#admin-note-table').DataTable({
            retrieve: true,
            processing: true,
            "pageLength": 50,
            ajax: {
                url: url,
                dataSrc: 'order_notes'
            },
            columns:  [
                    { data: "id", visible: false },
                    { data: "userID", visible: false },
                    { data: null,
                        "bSortable": true,
                        "mRender": function (o) {
                            var userFullName = o.members[0].firstName + ' ' + o.members[0].lastName;

                            return userFullName;
                        }
                    },
                    { data: "note" },
                    { data: "createdAt" }
                ],
                order: [[4, "desc"]]
          });

            orders_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', orders_table.table().container() ) );
            //To Reload The Ajax
            //See DataTables.net for more information about the reload method
            orders_table.ajax.reload();
        }
        else{

            //The URL will change with each "View Commit" button click
          // Must load new Url each time.
            var reload_table = $('#admin-note-table').DataTable();
            reload_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', reload_table.table().container() ) );
            reload_table.ajax.url(url).load();
        }

    }

    function loadOrderLedger(orderID){

        var url = '<?php echo HTTP_HOST; ?>/orders_log/' + orderID;

        if ( ! $.fn.DataTable.isDataTable( '#order-ledger-table' ) ) {

            var orders_table = $('#order-ledger-table').DataTable({
                retrieve: true,
                processing: true,
                "pageLength": 50,
                ajax: {
                    url: url,
                    //dataSrc: 'logs',
                    dataSrc: function ( json ) {

                        var logs = json.logs;

                        return logs;
                    }
                },
                columns:  [
                        { data: "id", visible: false },
                        { data: "user_id", visible: false },
                        { data: null,
                            "bSortable": true,
                            "mRender": function (o) {
                                var userFullName = o.members[0].firstName + ' ' + o.members[0].lastName;

                                return userFullName;
                            }
                        },
                        { data: "log_type_id", visible: false },
                        { data: "log_descr" },
                        { data: "ref_id", visible: false },
                        { data: "createdAt" }
                    ],
                    order: [[6, "desc"]]
              });

            //To Reload The Ajax
            //See DataTables.net for more information about the reload method
            orders_table.ajax.reload();
        }
        else{

            //The URL will change with each "View Commit" button click
            // Must load new Url each time.
            var reload_table = $('#order-ledger-table').DataTable();
            reload_table.ajax.url(url).load();
        }
    }

    function loadTableAJAX(status) {

        var url = '<?php echo API_HOST_URL; ?>';
        var blnShow = false;

        switch(entityType){
            case 0:     // URL for the Admin. The admin can see ALL Orders.
                url += '/orders?includes=documents,entities&columns=id,customerID,carrierIDs,documentID,orderID,originationAddress,originationCity,originationState,originationZip,destinationAddress,destinationCity,destinationState,destinationZip,distance,needsDataPoints,status,qty,rateType,transportationMode,entities.id,entities.name,documents.id,documents.documentURL&filter=status,eq,' + status + '&satisfy=all&transform=1';
                blnShow = true;
                break;
            case 1:    // URL for Customer. The Customer can only see their orders.
                url += '/orders?includes=documents,entities&columns=id,customerID,carrierIDs,documentID,orderID,originationAddress,originationCity,originationState,originationZip,destinationAddress,destinationCity,destinationState,destinationZip,distance,needsDataPoints,status,qty,rateType,transportationMode,entities.id,entities.name,documents.id,documents.documentURL&filter[0]=customerID,eq,' + entityid + '&filter[1]=status,eq,' + status + '&satisfy=all&transform=1';
                break;
            case 2:     // URL for the Carrier. Same as the admin but will be filtered below.
                url += '/orders?includes=documents,entities&columns=id,customerID,carrierIDs,documentID,orderID,originationAddress,originationCity,originationState,originationZip,destinationAddress,destinationCity,destinationState,destinationZip,distance,needsDataPoints,status,qty,rateType,transportationMode,entities.id,entities.name,documents.id,documents.documentURL&filter=status,eq,' + status + '&satisfy=all&transform=1';
                break;
        }

        if ( ! $.fn.DataTable.isDataTable( '#orders-table' ) ) {

            var orders_table = $('#orders-table').DataTable({
            retrieve: true,
            processing: true,
            "pageLength": 50,
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

                                if(Object.keys(carrierIDs[i]) == entityid){
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
                {
                    data: null,
                    "bSortable": true,
                    "mRender": function (o) {
                        var entityName = '';
                        var customerID = o.customerID;

                        allEntities.entities.forEach(function(entity){

                            if(customerID == entity.id){

                                entityName += entity.name;
                            }
                        });

                        return entityName;
                    },
                    visible: blnShow
                },
                {
                    data: null,
                    "bSortable": true,
                    "mRender": function (o) {
                        var entityName = '';
                        var carrierIDs = o.carrierIDs;

                        if(carrierIDs[0].carrierID != undefined){
                            for(var i = 0; i < carrierIDs.length; i++){

                                if(i > 0) entityName += ", ";

                                    allEntities.entities.forEach(function(entity){

                                        if(carrierIDs[i].carrierID == entity.id){

                                            entityName += entity.name;
                                        }

                                    });
                            }
                        }
                        else{
                            carrierIDs.forEach(function(value){

                                    allEntities.entities.forEach(function(entity){

                                            if(value[entity.id] != undefined){
                                                entityName += value[entity.id];
                                                entityName += ", ";
                                            }

                                    });
                            });
                            entityName = entityName.trimRight();
                            entityName = entityName.replace(/(^,)|(,$)/g, "");
                        }

                        return entityName;
                    },
                    visible: blnShow
                },
                { data: "qty" },
                { data: "transportationMode" },
                { data: "originationAddress", visible: false },
                { data: "originationCity" },
                { data: "originationState" },
                { data: "originationZip", visible: false },
                { data: "destinationAddress", visible: false },
                { data: "destinationCity" },
                { data: "destinationState" },
                { data: "destinationZip", visible: false },
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
            scrollX: true,
            order: [[1, "desc"]]
          });

            orders_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', orders_table.table().container() ) );
            //To Reload The Ajax
            //See DataTables.net for more information about the reload method
            orders_table.ajax.reload();
        }
        else{

            //The URL will change with each "View Commit" button click
          // Must load new Url each time.
            var reload_table = $('#orders-table').DataTable();
            reload_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', reload_table.table().container() ) );
            reload_table.ajax.url(url).load();
        }

        if(status == "Open"){
            $('#viewOpenOrders').css('display', 'none');
            $('#viewClosedOrders').css('display', 'block');
        }
        else{
            $('#viewOpenOrders').css('display', 'block');
            $('#viewClosedOrders').css('display', 'none');
        }

      }

    function loadNewOrderDetailsAJAX(orderID){

        var url = '<?php echo API_HOST_URL; ?>';
        var blnShow = false;
        var blnCarrierRate = false;
        var statusCarrierName = '';

        switch(entityType){
            case 0:     // URL for the Admin.
                url += '/order_details?include=orders,entities&columns=id,carrierID,orderID,pickupInformation,deliveryInformation,originationAddress,originationCity,originationState,originationZip,destinationAddress,destinationCity,destinationState,destinationZip,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,orders.customerRate,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID,orders.qty,orders.customerID,orders.pickupInformation,orders.deliveryInformation,orders.podList,orders.documentID,entities.name&filter=orderID,eq,' + orderID + '&transform=1';
                blnShow = true;
                blnCarrierRate = true;
                break;
            case 1:    // URL for Customer.
                url += '/order_details?include=orders,entities&columns=id,carrierID,orderID,pickupInformation,deliveryInformation,originationAddress,originationCity,originationState,originationZip,destinationAddress,destinationCity,destinationState,destinationZip,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,orders.customerRate,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID,orders.qty,orders.customerID,orders.pickupInformation,orders.deliveryInformation,orders.podList,entities.name&filter=orderID,eq,' + orderID + '&transform=1';
                blnShow = true;
                break;
            case 2:     // URL for the Carrier. The Customer can only see order details of their route.
                url += '/order_details?include=orders,entities&columns=id,carrierID,orderID,pickupInformation,deliveryInformation,originationAddress,originationCity,originationState,originationZip,destinationAddress,destinationCity,destinationState,destinationZip,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,orders.customerRate,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID,orders.qty,orders.customerID,orders.pickupInformation,orders.deliveryInformation,orders.podList,entities.name&filter[]=orderID,eq,' + orderID + '&filter[]=carrierID,eq,' + entityid + '&transform=1';
                blnCarrierRate = true;
                break;
        }

        $("#orderID").val(orderID);

        $.get(url, function(data){

            var order_details = data.order_details;

            var originationCity = order_details[0].orders[0].originationCity;
            var originationState = order_details[0].orders[0].originationState;

            var destinationCity = order_details[0].orders[0].destinationCity;
            var destinationState = order_details[0].orders[0].destinationState;

            var fromAddress = originationCity + ", " + originationState;
            var toAddress = destinationCity + ", " + destinationState;

            var carriers = [];
            var trailers = order_details[0].orders[0].podList;

            // Get the Carrier Rate
            var displayCustomerRate = order_details[0].orders[0].customerRate;
            var displayCarrierTotal = 0;

            var relayList = "<div class=\"row carrier-row__border-bot carrier-row__notselected\"><div class=\"col-md-12\"><h4>Carriers</h4><div class=\"fa fa-lg fa-refresh text-blue\" style=\"float: right; position: relative; top: -25px;\"></div><br></div></div>";
            var trailerList = "<div class=\"row trailer-row__border-bot trailer-row__notselected\"><div class=\"col-md-12\"><h4>Trailer List</h4><div class=\"fa fa-lg fa-refresh text-blue\" style=\"float: right; position: relative; top: -25px;\"></div><br></div></div>";
            var activeCarriers = "<option value=\"\">*Select Carrier...</option>";

            var currentCarrier = '';
            for(var i = 0; i < order_details.length; i++){
                currentCarrier = order_details[i].carrierID;
                var entityName = "";

                if(carriers.indexOf(currentCarrier) == -1) carriers.push(currentCarrier);

                allEntities.entities.forEach(function(entity){

                    if(currentCarrier == entity.id){

                        entityName += entity.name;
                        statusCarrierName += entity.name; // Store this so we can put it on the Tracking History tab
                    }
                });

                // Add all of the carrier rates together
                displayCarrierTotal += order_details[i].carrierRate;

                activeCarriers += "<option value=\"" + order_details[i].carrierID + "\">" + entityName + "</option>";

                if(i == 0){
                    relayList += "<div class=\"row carrier-row carrier-row__border-top carrier-row__selected\" onclick=\"displayRelay(this, " + order_details[i].id + ")\">" +
                                "       <div class=\"col-md-3\">" +
                                "           <div class=\"carrier-logo carrier-logo__buds\"></div>" +
                                "       </div>" +
                                "       <div class=\"col-md-9\">" +
                                "           <h4>" + entityName + "</h4>" +
                                "           QTY <span class=\"pad-left-25\">" + order_details[i].qty + "</span>" +
                                "       </div>" +
                                " </div>";



                    if(order_details[i].pickupInformation == null){
                        //order_details[i].pickupInformation = {pickupLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
                        order_details[i].pickupInformation = {pickupLocation: "", contactPerson: "", phoneNumber: "", pickupHoursOfOperationOpen: "", pickupHoursOfOperationClose: "", pickupTimeZone: ""};
                    }

                    if(order_details[i].deliveryInformation == null){
                        //order_details[i].deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
                        order_details[i].deliveryInformation = {pickupLocation: "", contactPerson: "", phoneNumber: "", deliveryHoursOfOperationOpen: "", deliveryHoursOfOperationClose: "", deliveryTimeZone: ""};
                    }

                    var carrierDistance = " <h5>" + entityName + "</h5> <small class=\"text-blue\">Distance: " + order_details[i].distance + " miles</small>";

                    $("#carrierDistance").empty().html(carrierDistance);

                    //if(order_details[i].pickupInformation.hoursOfOperation == "") order_details[i].pickupInformation.hoursOfOperation = "N/A";
                    if(order_details[i].pickupInformation.pickupHoursOfOperationOpen == "") order_details[i].pickupInformation.pickupHoursOfOperationOpen = "01:00";
                    if(order_details[i].pickupInformation.pickupHoursOfOperationClose == "") order_details[i].pickupInformation.pickupHoursOfOperationClose = "01:00";

                    //if(order_details[i].deliveryInformation.hoursOfOperation == "") order_details[i].deliveryInformation.hoursOfOperation = "N/A";
                    if(order_details[i].deliveryInformation.deliveryHoursOfOperationOpen == "") order_details[i].deliveryInformation.deliveryHoursOfOperationOpen = "01:00";
                    if(order_details[i].deliveryInformation.deliveryHoursOfOperationClose == "") order_details[i].deliveryInformation.deliveryHoursOfOperationClose = "01:00";

                    if(entityType == 0){
                        $("#pickupName").val(order_details[i].pickupInformation.pickupLocation);
                        $("#pickupAddress").val(order_details[i].originationAddress);
                        $("#pickupCity").val(order_details[i].originationCity);
                        $("#pickupState").val(order_details[i].originationState);
                        $("#pickupZip").val(order_details[i].originationZip);
                        $("#pickupPhone").val(order_details[i].pickupInformation.phoneNumber);
                        $("#pickupContact").val(order_details[i].pickupInformation.contactPerson);
                        //$("#pickupHours").val(order_details[i].pickupInformation.hoursOfOperation);
                        $("#pickupHoursOfOperationOpen").val(order_details[i].pickupInformation.pickupHoursOfOperationOpen);
                        $("#pickupHoursOfOperationClose").val(order_details[i].pickupInformation.pickupHoursOfOperationClose);
                        $("#pickupTimeZone").val(order_details[i].pickupInformation.pickupTimeZone);
                        $("#pickupDate").val(order_details[i].pickupDate);

                        $("#deliveryName").val(order_details[i].deliveryInformation.deliveryLocation);
                        $("#deliveryAddress").val(order_details[i].destinationAddress);
                        $("#deliveryCity").val(order_details[i].destinationCity);
                        $("#deliveryState").val(order_details[i].destinationState);
                        $("#deliveryZip").val(order_details[i].destinationZip);
                        $("#deliveryPhone").val(order_details[i].deliveryInformation.phoneNumber);
                        $("#deliveryContact").val(order_details[i].deliveryInformation.contactPerson);
                        //$("#deliveryHours").val(order_details[i].deliveryInformation.hoursOfOperation);
                        $("#deliveryHoursOfOperationOpen").val(order_details[i].pickupInformation.deliveryHoursOfOperationOpen);
                        $("#deliveryHoursOfOperationClose").val(order_details[i].pickupInformation.deliveryHoursOfOperationClose);
                        $("#deliveryTimeZone").val(order_details[i].pickupInformation.deliveryTimeZone);
                        $("#deliveryDate").val(order_details[i].deliveryDate);

                        $("#transportMode").val(order_details[i].transportationMode);
                        $("#carrierRate").val(order_details[i].carrierRate);
                        $("#carrierQty").val(order_details[i].qty);

                        $("#orderDetailID").val(order_details[i].id);
                        $("#documentID").val(order_details[i].orders[0].documentID);

                        $.ajax({
                            url: '<?php echo API_HOST_URL . "/locations"; ?>' + '?filter=entityID,eq,' + currentCarrier + '&transform=1',
                            contentType: "application/json",
                            success: function (json) {
                                var locations = json.locations;
                                var data = [];
                                $.each(locations, function(key, location){
                                    var value = location.address1;
                                    var label = location.address1 + ', ' + location.city + ', ' + location.state + ' ' + location.zip;
                                    var id = location.id
                                    var city = location.city;
                                    var state = location.state;
                                    var zip = location.zip;
                                    var entry = {id: id, value: value, label: label, city: city, state: state, zip: zip};
                                    data.push(entry);
                                });

                                $("#pickupAddress").autocomplete({
                                    source: data,
                                    minLength: 0,
                                    select: function (event, ui) {
                                        $("#pickupCity").val(ui.item.city);
                                        $("#pickupState").val(ui.item.state);
                                        $("#pickupZip").val(ui.item.zip);
                                    }
                                });


                                $("#deliveryAddress").autocomplete({
                                    source: data,
                                    minLength: 0,
                                    select: function (event, ui) {
                                        $("#deliveryCity").val(ui.item.city);
                                        $("#deliveryState").val(ui.item.state);
                                        $("#deliveryZip").val(ui.item.zip);
                                    }
                                });
                            }
                        });
                    }
                    else{
                        var carrierPickupInformation = "<h5 class=\"text-blue\">Pick Up From</h5>" +
                                order_details[i].pickupInformation.pickupLocation + "<br>" +
                                order_details[i].originationAddress + "<br>" +
                                order_details[i].originationCity + ", " + order_details[i].originationState + " " + order_details[i].originationZip + "<br>" +
                                "<br>" +
                                order_details[i].pickupInformation.phoneNumber + "<br>" +
                                "<br>" +
                                order_details[i].pickupInformation.contactPerson + "<br>" +
                                "<br>" +
                                //order_details[i].pickupInformation.hoursOfOperation + "<br>" +
                                order_details[i].pickupInformation.pickupHoursOfOperationOpen + " to " + order_details[i].pickupInformation.pickupHoursOfOperationClose + " " + order_details[i].pickupInformation.pickupTimeZone +
                                "<br>" +
                                "<strong>Pick Up Date</strong><br>" +
                                "<div style=\"margin-left: 30px;\">" + order_details[i].pickupDate + "</div>" +
                                "<div class=\"fa fa-lg fa-calendar text-blue\" style=\"position: relative; left: 0; top: -22px;\"></div>";

                        var carrierDeliveryInformation = "<h5 class=\"text-blue\">Deliver To</h5>" +
                                order_details[i].deliveryInformation.deliveryLocation + "<br>" +
                                order_details[i].destinationAddress + "<br>" +
                                order_details[i].destinationCity + ", " + order_details[i].destinationState + " " + order_details[i].destinationZip + "<br>" +
                                "<br>" +
                                order_details[i].deliveryInformation.phoneNumber + "<br>" +
                                "<br>" +
                                order_details[i].deliveryInformation.contactPerson + "<br>" +
                                "<br>" +
                                //order_details[i].deliveryInformation.hoursOfOperation + "<br>" +
                                order_details[i].deliveryInformation.deliveryHoursOfOperationOpen + " to " + order_details[i].deliveryInformation.deliveryHoursOfOperationClose + " " + order_details[i].deliveryInformation.deliveryTimeZone +
                                "<br>" +
                                "<strong>Pick Up Date</strong><br>" +
                                "<div style=\"margin-left: 30px;\">" + order_details[i].deliveryDate + "</div>" +
                                "<div class=\"fa fa-lg fa-calendar text-blue\" style=\"position: relative; left: 0; top: -22px;\"></div>";

                        $('#carrierPickupInformation').empty().html(carrierPickupInformation);
                        $('#carrierDeliveryInformation').empty().html(carrierDeliveryInformation);

                        $('#deliveryDeadline').html(order_details[i].deliveryDate);

                        var deliveryDeadline = new Date(order_details[i].deliveryDate);
                        var today = new Date();
                        var difference = Date.daysBetween(today, deliveryDeadline);

                        if (difference <= 2 && difference >= 0){

                            $('#deliveryDeadline').addClass("deadline-warning");
                            $('#deliveryDeadline').blink({delay:1000});
                        }
                        else if(difference < 0){

                            $('#deliveryDeadline').addClass("deadline-danger");
                            $('#deliveryDeadline').blink({delay:1000});
                        }

                        $("#transportMode").html(order_details[i].transportationMode);
                        $("#carrierQty").html(order_details[i].qty);

                    }
                }
                else{
                    relayList += "<div class=\"row carrier-row carrier-row__border-bot carrier-row__notselected\" onclick=\"displayRelay(this, " + order_details[i].id + ")\">" +
                                "       <div class=\"col-md-3\">" +
                                "           <div class=\"carrier-logo carrier-logo__buds\"></div>" +
                                "       </div>" +
                                "       <div class=\"col-md-9\">" +
                                "           <h4>" + entityName + "</h4>" +
                                "           QTY <span class=\"pad-left-25\">" + order_details[i].qty + "</span>" +
                                "       </div>" +
                                " </div>";
                }
            }

            // Calculate total Revenue
            var displayTotalRevenue = displayCustomerRate - displayCarrierTotal;

            if(entityType == 0){
                // Display
                $('#displayCustomerRate').html(displayCustomerRate);
                $('#displayCarrierTotal').html(displayCarrierTotal);
                $('#displayTotalRevenue').html(displayTotalRevenue);
            }

            if(trailers == null){
                trailers = [];
                if(entityType == 0){
                    var statusesList = "<div class=\"row\"></div>";
                    $("#statusesList").empty().html(statusesList);
                }
            }

            $.each(trailers, function(key, trailer){

                if(trailer.vinNumber == null || trailer.vinNumber == "") trailer.vinNumber = "N/A";
                if(trailer.unitNumber == null || trailer.unitNumber == "") trailer.unitNumber = "N/A";

                if(key == 0){
                    trailerList += "<div class=\"row trailer-row trailer-row__border-top trailer-row__selected\" onclick=\"displayTrailer(this, '" + trailer.vinNumber + "', '" + currentCarrier + "', '" + trailer.unitNumber + "')\">" +
                                "       <div class=\"col-md-12\">" +
                                "           <h4>Unit #: " + trailer.unitNumber + "</h4>" +
                                "           <div class=\"text-blue\">VIN #:" +
                                "               <span class=\"pad-left-25\">" + trailer.vinNumber + "</span>" +
                                "           </div>" +
                                "       </div>" +
                                " </div>";

                        $("#displayVinNumber").html(trailer.vinNumber);
                        $("#displayUnitNumber").html(trailer.unitNumber);
                        $("#activeCarrier").val('');

                        displayOrderStatuses(orderID, '', trailer.vinNumber);
                }
                else{
                    trailerList += "<div class=\"row trailer-row trailer-row__border-bot trailer-row__notselected\" onclick=\"displayTrailer(this, '" + trailer.vinNumber + "', '" + currentCarrier + "', '" + trailer.unitNumber + "')\">" +
                                "       <div class=\"col-md-12\">" +
                                "           <h4>Unit #: " + trailer.unitNumber + "</h4>" +
                                "           <div class=\"text-blue\">VIN #:" +
                                "               <span class=\"pad-left-25\">" + trailer.vinNumber + "</span>" +
                                "           </div>" +
                                "       </div>" +
                                " </div>";
                }

                var url = '<?php echo API_HOST_URL . "/order_statuses?filter[]=vinNumber,eq," ?>' + trailer.vinNumber + '&filter[]=carrierID,eq,' + currentCarrier + '&order=createdAt,desc&transform=1';
                var type = "GET";

                $.ajax({
                     url: url,
                     type: type,
                     async: false,
                     success: function(data){
                         
                         if (data.order_statuses.length > 0) {
                             $("#statusCarrierName").html(statusCarrierName);
                             $("#statusID").val(data.order_statuses[0].id);
                             $("#statusAddANote").val(data.order_statuses[0].note);
                             $("#statusTrailerStatus").val(data.order_statuses[0].status);
                             $("#statusCurrentLocation").val(data.order_statuses[0].city + ', ' + data.order_statuses[0].state);
                             $("#statusLoadingStatus").val(data.order_statuses[0].loadingStatus);
                             $("#statusArrivalEta").val(data.order_statuses[0].arrivalEta);
                             $("#statusOrderID").val(data.order_statuses[0].orderID);
                             $("#statusOrderDetailID").val(order_details[0].id);
                             $("#statusCarrierID").val(data.order_statuses[0].carrierID);
                             $("#statusDocumentID").val(data.order_statuses[0].documentID);
                             $("#statusVinNumber").val(data.order_statuses[0].vinNumber);
                             $("#statusUnitNumber").val(data.order_statuses[0].unitNumber);

                             var url = '<?php echo API_HOST_URL ?>/documents?filter[]=id,eq,' + data.order_statuses[0].documentID + '&transform=1';
                             var type = "GET";

                             $.ajax({
                                 url: url,
                                 type: type,
                                 async: false,
                                 success: function(data){
                                     $("#statusFileName").val(data.documents[0].documentID);
                                 },
                                 error: function() {
                                    $("#errorAlertTitle").html("Error");
                                    $("#errorAlertBody").html("There Was An Error Retrieving Document Data!");
                                    $("#errorAlert").modal('show');
                                 }
                             });

                             $("#statusRecordButtons").css("display", "block");
                             $("#noStatusRecordsExist").css("display", "none");
                         } else {
                             $("#statusAddANote").val('');
                             $("#statusTrailerStatus").val('In Transit');
                             $("#statusCurrentLocation").val('');
                             $("#statusLoadingStatus").val('');
                             $("#statusArrivalEta").val('');
                             $("#statusOrderID").val(orderID);
                             $("#statusOrderDetailID").val(order_details[0].id);
                             $("#statusCarrierID").val(<?php echo $_SESSION['entityid']; ?>);
                             $("#statusDocumentID").val('');
                             $("#statusVinNumber").val(trailer.vinNumber);
                             $("#statusUnitNumber").val(trailer.unitNumber);
                             $("#noStatusRecordsExist").css("display", "block");
                             $("#statusRecordButtons").css("display", "none");
                         }
                     },
                     error: function() {
                        $("#errorAlertTitle").html("Error");
                        $("#errorAlertBody").html("There Was An Error Retrieving Order Status Data!");
                        $("#errorAlert").modal('show');
                     }
                });

            });

            $("#fromAddress").html(fromAddress);
            $("#toAddress").html(toAddress);
            $("#relayCount").html(order_details.length);
            $("#carriersCount").html(carriers.length);
            $("#displayQty").html(order_details[0].orders[0].qty);

            $("#activeCarrier").empty().html(activeCarriers);
            $("#relayList").empty().html(relayList);
            $("#trailerList").empty().html(trailerList);

        });

        $("#order-details").css("display", "block");
        $("#orders").css("display", "none");

        loadOrderNotes(orderID);
        loadOrderLedger(orderID);
    }

    function loadOrderDetailsAJAX(orderID){

        var url = '<?php echo API_HOST_URL; ?>';
        var blnShow = false;
        var blnCarrierRate = false;

        switch(entityType){
            case 0:     // URL for the Admin.
                url += '/order_details?include=orders,entities&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID,orders.customerID,orders.pickupInformation,orders.deliveryInformation,entities.name&filter=orderID,eq,' + orderID + '&transform=1';
                blnShow = true;
                blnCarrierRate = true;
                break;
            case 1:    // URL for Customer.
                url += '/order_details?include=orders,entities&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID,orders.customerID,orders.pickupInformation,orders.deliveryInformation,entities.name&filter=orderID,eq,' + orderID + '&transform=1';
                blnShow = true;
                break;
            case 2:     // URL for the Carrier. The Customer can only see order details of their route.
                url += '/order_details?include=orders,entities&columns=id,carrierID,orderID,originationCity,originationState,destinationCity,destinationState,orders.originationCity,orders.originationState,orders.destinationCity,orders.destinationState,orders.distance,orders.status,distance,status,transportationMode,qty,carrierRate,pickupDate,deliveryDate,orders.id,orders.orderID,orders.customerID,orders.pickupInformation,orders.deliveryInformation,entities.name&filter[]=orderID,eq,' + orderID + '&filter[]=carrierID,eq,' + entityid + '&transform=1';
                blnCarrierRate = true;
                break;
        }

        if ( ! $.fn.DataTable.isDataTable( '#order-details-table' ) ) {

            var order_details_table = $('#order-details-table').DataTable({
                retrieve: true,
                processing: true,
                "pageLength": 50,
                ajax: {
                    url: url,
                    //dataSrc: 'order_details',
                    dataSrc: function ( json ) {

                        var order_details = json.order_details;

                        if(entityType == 2 || entityType == 0){

                        var originationCity = order_details[0].orders[0].originationCity;
                        var originationState = order_details[0].orders[0].originationState;

                        var destinationCity = order_details[0].orders[0].destinationCity;
                        var destinationState = order_details[0].orders[0].destinationState;

                        var fromAddress = originationCity + ", " + originationState;
                        var toAddress = destinationCity + ", " + destinationState;

                        $("#origination").val(fromAddress);
                        $("#destination").val(toAddress);


                            return order_details;   // Carrier and Admin is already set
                        }

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

        //var url = '<?php echo API_HOST_URL; ?>/order_statuses?include=entities&columns=id,orderID,carrierID,city,state,status,note,createdAt,entities.id,entities.name&filter=orderID,eq,' + orderID + '&transform=1';
        var url = '<?php echo API_HOST_URL; ?>/orders?columns=id,carrierIDs,deliveryInformation,pickupInformation,podList&filter=id,eq,' + orderID + '&transform=1';
        var blnShow = false;

        if(entityType != 1) blnShow = true;

        if ( ! $.fn.DataTable.isDataTable( '#order-history-table' ) ) {

            var order_history_table = $('#order-history-table').DataTable({
                retrieve: true,
                processing: true,
                "pageLength": 50,
                ajax: {
                    url: url,
                    //dataSrc: 'order_statuses'
                    dataSrc: function(json){

                        var data = [];

                        var podList = json.orders[0].podList;

                        if (podList === null){
                            data = [];
                        }
                        else{
                            podList.forEach(function(pod){

                                if(pod.order_statuses != null){
                                    var order_statuses = pod.order_statuses;

                                    if(order_statuses.length > 0){
                                        order_statuses.forEach(function(order_status){
                                            order_status.vinNumber = pod.vinNumber;

                                            data.push(order_status);
                                        });
                                    }
                                }
                            });
                        }

                        return data;
                    }
                },
                columns: [
                    { data: "vinNumber" },
                    { data: "carrier" },
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

        var url = '<?php echo API_HOST_URL; ?>/orders?columns=id,carrierIDs,deliveryInformation,pickupInformation,podList&filter=id,eq,' + orderID + '&transform=1';
        var blnShow = false;

        if(entityType == 0) blnShow = true;

        if ( ! $.fn.DataTable.isDataTable( '#pod-list-table' ) ) {

            var order_history_table = $('#pod-list-table').DataTable({
                retrieve: true,
                processing: true,
                "pageLength": 50,
                ajax: {
                    url: url,
                    //dataSrc: 'orders[0].podList',
                    dataSrc: function(json){

                        var data = [];
                        vinNumbersList = [];

                        var carrierIDs = json.orders[0].carrierIDs;
                        var podList = json.orders[0].podList;
                        var deliveryInformation = json.orders[0].deliveryInformation;
                        var pickupInformation = json.orders[0].pickupInformation;

                        if (podList == null){
                            data = [];
                        }
                        else{
                            existingPODList = podList;
                            podList.forEach(function(pod){

                                if (deliveryInformation === null){

                                    pod.deliveryInformation = {};
                                }
                                else{
                                    pod.deliveryInformation = deliveryInformation;
                                }

                                if (pickupInformation === null){
                                    pod.pickupInformation = {};
                                }
                                else{
                                    pod.pickupInformation = pickupInformation;
                                }
                                data.push(pod);
                                vinNumbersList.push(pod.vinNumber);
                            });
                        }

                        return data;
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

                            if(podDataJSON.unitNumber == "" || podDataJSON.unitNumber == null){
                                errorCount++;
                            }
                            if(podDataJSON.trailerYear == "" || podDataJSON.trailerYear == null){
                                errorCount++;
                            }
                            if(podDataJSON.deliveryInformation == {} || podDataJSON.deliveryInformation == null){
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
                                //if(podDataJSON.deliveryInformation.hoursOfOperation == ""){
                                //    errorCount++;
                                //}
                                if(podDataJSON.deliveryInformation.deliveryHoursOfOperationOpen == ""){
                                    errorCount++;
                                }
                                if(podDataJSON.deliveryInformation.deliveryHoursOfOperationClose == ""){
                                    errorCount++;
                                }
                            }
                            if(podDataJSON.pickupInformation == {} || podDataJSON.pickupInformation == null){
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
                                //if(podDataJSON.pickupInformation.hoursOfOperation == ""){
                                //    errorCount++;
                                //}
                                if(podDataJSON.pickupInformation.pickupHoursOfOperationOpen == ""){
                                    errorCount++;
                                }
                                if(podDataJSON.pickupInformation.pickupHoursOfOperationClose == ""){
                                    errorCount++;
                                }
                            }

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

        var url = '<?php echo API_HOST_URL; ?>/orders?columns=comments&filter=id,eq,' + orderID + '&transform=1';

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
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Unable to retrieve order comments");
                $("#errorAlert").modal('show');
            }
        });
    }

    function getOrderIDAndCustomerName(orderID){

        var url = '<?php echo API_HOST_URL; ?>';

        switch(entityType) {
        case 0:

            url += '/orders?columns=id,customerID,orderID&filter=id,eq,' + orderID + '&transform=1';

            $.ajax({
                url: url,
                type: "GET",
                async: false,
                success: function(data){
                     var customerID = data.orders[0].customerID;

                     $("#customerID").val(customerID);

                     var orderID = data.orders[0].orderID;

                     $("#orderNumber").html(orderID);

                   var url = '<?php echo API_HOST_URL; ?>';
                   url += '/entities?columns=id,name&filter=id,eq,' + customerID + '&transform=1';

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

        		break;
        case 1:

			$('#editOrderDetails').click(function(){
	            $("#editOrder").modal('show');
			});

			$('#closeOrderDetails').click(function(){
				closeOrderDetails();
			});

            url += '/orders?filter=id,eq,' + orderID + '&transform=1';

            $.ajax({
                url: url,
                type: "GET",
                async: false,
                success: function(data){
                     var customerID = data.orders[0].customerID;
                     var orderID = data.orders[0].orderID;
                     
                     var podList = data.orders[0].podList;
                     
                     for(var i = 0; i < podList.length; i++){
                         if(podList[i].deliveryDate == null) podList[i].deliveryDate = "";
                         if(podList[i].notes == null) podList[i].notes = "";
                    }

                     $("#orderNumber").html(orderID);

             		var customer_podlist_table;

					if (data.orders[0].pickupInformation !== null) {
	             		$('#customer_pickupContactPerson').text(data.orders[0].pickupInformation.contactPerson);
	             		$('#customer_pickupLocation').text(data.orders[0].pickupInformation.pickupLocation);
	             		$('#customer_pickupPhoneNumber').text(data.orders[0].pickupInformation.phoneNumber);
	             		//$('#customer_pickupHoursOfOperation').text(data.orders[0].pickupInformation.hoursOfOperation);
	             		$('#customer_pickupHoursOfOperationOpen').text(data.orders[0].pickupInformation.pickupHoursOfOperationOpen);
	             		$('#customer_pickupHoursOfOperationClose').text(data.orders[0].pickupInformation.pickupHoursOfOperationClose);
	             		$('#customer_pickupTimeZone').text(data.orders[0].pickupInformation.pickupTimeZone);

	             		$('#pickupContactPerson').val(data.orders[0].pickupInformation.contactPerson);
	             		$('#pickupLocation').val(data.orders[0].pickupInformation.pickupLocation);
	             		$('#pickupPhoneNumber').val(data.orders[0].pickupInformation.phoneNumber);
	             		//$('#pickupHoursOfOperation').val(data.orders[0].pickupInformation.hoursOfOperation);
	             		$('#pickupHoursOfOperationOpen').val(data.orders[0].pickupInformation.pickupHoursOfOperationOpen);
	             		$('#pickupHoursOfOperationClose').val(data.orders[0].pickupInformation.pickupHoursOfOperationClose);
	             		$('#pickupTimeZone').val(data.orders[0].pickupInformation.pickupTimeZone);

					} else {
	             		$('#customer_pickupContactPerson').text('');
	             		$('#customer_pickupLocation').text('');
	             		$('#customer_pickupPhoneNumber').text('');
	             		//$('#customer_pickupHoursOfOperation').text('');
	             		$('#customer_pickupHoursOfOperationOpen').text('');
	             		$('#customer_pickupHoursOfOperationClose').text('');
	             		$('#customer_pickupTimeZone').text('');

	             		$('#pickupContactPerson').val('');
	             		$('#pickupLocation').val('');
	             		$('#pickupPhoneNumber').val('');
	             		//$('#pickupHoursOfOperation').val('');
	             		$('#pickupHoursOfOperationOpen').val('0.00');
	             		$('#pickupHoursOfOperationClose').val('0.00');
	             		$('#pickupTimeZone').val('');
					}

					if (data.orders[0].deliveryInformation !== null) {

	             		$('#customer_deliveryContactPerson').text(data.orders[0].deliveryInformation.contactPerson);
	             		$('#customer_deliveryLocation').text(data.orders[0].deliveryInformation.deliveryLocation);
	             		$('#customer_deliveryLocation').text(data.orders[0].deliveryInformation.deliveryLocation);
	             		$('#customer_deliveryPhoneNumber').text(data.orders[0].deliveryInformation.phoneNumber);
	             		//$('#customer_deliveryHoursOfOperation').text(data.orders[0].deliveryInformation.hoursOfOperation);
	             		$('#customer_deliveryHoursOfOperationOpen').text(data.orders[0].deliveryInformation.deliveryHoursOfOperationOpen);
	             		$('#customer_deliveryHoursOfOperationClose').text(data.orders[0].deliveryInformation.deliveryHoursOfOperationClose);
	             		$('#customer_deliveryTimeZone').text(data.orders[0].deliveryInformation.deliveryTimeZone);

	             		$('#deliveryContactPerson').val(data.orders[0].deliveryInformation.contactPerson);
	             		$('#deliveryLocation').val(data.orders[0].deliveryInformation.deliveryLocation);
	             		$('#deliveryLocation').val(data.orders[0].deliveryInformation.deliveryLocation);
	             		$('#deliveryPhoneNumber').val(data.orders[0].deliveryInformation.phoneNumber);
	             		//$('#deliveryHoursOfOperation').val(data.orders[0].deliveryInformation.hoursOfOperation);
	             		$('#deliveryHoursOfOperationOpen').val(data.orders[0].deliveryInformation.deliveryHoursOfOperationOpen);
	             		$('#deliveryHoursOfOperationClose').val(data.orders[0].deliveryInformation.deliveryHoursOfOperationClose);
	             		$('#deliveryTimeZone').val(data.orders[0].deliveryInformation.deliveryTimeZone);

					} else {

	             		$('#customer_deliveryContactPerson').text('');
	             		$('#customer_deliveryLocation').text('');
	             		$('#customer_deliveryLocation').text('');
	             		$('#customer_deliveryPhoneNumber').text('');
	             		//$('#customer_deliveryHoursOfOperation').text('');
	             		$('#customer_deliveryHoursOfOperationOpen').text('0.00');
	             		$('#customer_deliveryHoursOfOperationClose').text('0.00');
	             		$('#customer_deliveryTimeZone').text('');

	             		$('#deliveryContactPerson').val('');
	             		$('#deliveryLocation').val('');
	             		$('#deliveryLocation').val('');
	             		$('#deliveryPhoneNumber').val('');
	             		//$('#deliveryHoursOfOperation').val('');
	             		$('#deliveryHoursOfOperationOpen').val('0.00');
	             		$('#deliveryHoursOfOperationClose').val('0.00');
	             		$('#deliveryTimeZone').val('');

					}

					if (data.orders[0].originationAddress !== "" && data.orders[0].originationAddress !== null) {
						$('#customer_originationAddress').css({'display':'block'}).text(data.orders[0].originationAddress);
						$('#originationAddress').val(data.orders[0].originationAddress);
					} else {
						$('#customer_originationAddress').css({'display':'inline'}).text('');
						$('#originationAddress').val('');
					}

             		if (data.orders[0].destinationAddress !== "" && data.orders[0].destinationAddress !== null) {
             			$('#customer_destinationAddress').css({'display':'block'}).text(data.orders[0].destinationAddress);
             			$('#destinationAddress').val(data.orders[0].destinationAddress);
             		} else {
             			$('#customer_destinationAddress').css({'display':'inline'}).text('');
             			$('#destinationAddress').val('');
             		}

             		var hasorigincity = false;
             		if (data.orders[0].originationCity !== "" && data.orders[0].originationCity !== null) {
             			hasorigincity = true;
             			$('#customer_originationCity').text(data.orders[0].originationCity);
             			$('#originationCity').val(data.orders[0].originationCity);
             		} else {
             			$('#customer_originationCity').text('');
             			$('#originationCity').val('');
             		}

             		var hasdestcity = false;
             		if (data.orders[0].destinationCity !== "" && data.orders[0].destinationCity !== null) {
             			hasdestcity = true;
             			$('#customer_destinationCity').text(data.orders[0].destinationCity);
             			$('#destinationCity').val(data.orders[0].destinationCity);
             		} else {
             			$('#customer_destinationCity').text('');
             			$('#destinationCity').val('');
             		}

             		if (data.orders[0].originationState !== "" && data.orders[0].originationState !== null) {
             			$('#customer_originationState').text((hasorigincity) ? ", " +  data.orders[0].originationState : data.orders[0].originationState);
             			$('#originationState').val(data.orders[0].originationState);
             		} else {
             			$('#customer_originationState').text('');
             			$('#originationState').text('');
             		}

             		if (data.orders[0].destinationState !== "" && data.orders[0].destinationState !== null) {
             			$('#customer_destinationState').text((hasdestcity) ? ", " +  data.orders[0].destinationState : data.orders[0].destinationState);
             			$('#destinationState').val(data.orders[0].destinationState);
             		}

             		if (data.orders[0].originationZip !== "" && data.orders[0].originationZip !== null) {
             			$('#customer_originationZip').text(data.orders[0].originationZip);
             			$('#originationZip').val(data.orders[0].originationZip);
             		} else {
             			$('#customer_originationZip').text('');
             			$('#originationZip').val('');
             		}

             		if (data.orders[0].destinationZip !== "" && data.orders[0].destinationZip !== null) {
             			$('#customer_destinationZip').text(data.orders[0].destinationZip);
             			$('#destinationZip').val(data.orders[0].destinationZip);
             		} else {
             			$('#customer_destinationZip').text('');
             			$('#destinationZip').val('');
             		}

             		if (data.orders[0].transportationMode !== "" && data.orders[0].transportationMode !== null) {
	                		$("#transportationMode").val(data.orders[0].transportationMode);
             		} else {
                			$("#transportationMode").val('');
             		}

                    if (data.orders[0].rateType == "Flat Rate") {
                        $('#editOrder input[name="rateType"][value="Flat Rate"]').prop('checked', true);
                        $('#editOrder input[name="rateType"][value="Mileage"]').prop('checked', false);
                    } else {
                        $('#editOrder input[name="rateType"][value="Flat Rate"]').prop('checked', false);
                        $('#editOrder input[name="rateType"][value="Mileage"]').prop('checked', true);
                    }

					$('#customer-pod-list-table').DataTable().destroy();

					var dataSet = [['','','','']];
					if (data.orders[0].podList !== null) {

		                var items = '';
		                for (var i = 0; i < data.orders[0].podList.length; i++) {
			                	items += '<li class="list-group-item"><input type="text" class="form-control" value="' + data.orders[0].podList[i].vinNumber + '"></li>\n';
		                }
		                $("#input-list-box").html(items);

		                items = "";
		                for (var i = 0; i < dataPoints.object_type_data_points.length; i++) {
		                    var selected = '';
		                    var value = '';

		                    $.each(data.orders[0].needsDataPoints, function(idx, obj) {
		                      $.each(obj, function(key, val) {
		                        if (dataPoints.object_type_data_points[i].columnName == key) {
		                            value = val;
		                        }
		                      })
		                    });

		                    items += '<li>' + dataPoints.object_type_data_points[i].title +
		                            ' <select style="width:90%" class="form-control mb-sm" id="' + dataPoints.object_type_data_points[i].columnName + '" name="' + dataPoints.object_type_data_points[i].columnName + '" disabled>';
		                    for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {
		                        if (dataPoints.object_type_data_points[i].object_type_data_point_values[v].value === value) {
		                            selected = ' selected ';
		                        } else {
		                            selected = '';
		                        }
		                        items += '<option' + selected + '>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</option>\n';
		                    }

		                    items += '</select>' +
		                            '</li>\n';
		                }

		                $("#dp-check-list-box").html(items);

						customer_podlist_table = $('#customer-pod-list-table').DataTable({
                    	    		retrieve: true,
                	            processing: true,
                	            responsive: true,
                	            data: data.orders[0].podList,
                	            columns: [
                	                { data: "vinNumber" },
                	                { data: "deliveryDate" },
                	                { data: "notes" },
                	            ]
						});

					} else {

		                $("#input-list-box > li").remove();

						customer_podlist_table = $('#customer-pod-list-table').DataTable({
                    	    		retrieve: true,
                	            processing: true,
                	            responsive: true,
                	            data: [],
						});
					}

                   var url = '<?php echo API_HOST_URL; ?>';
                   url += '/entities?columns=id,name&filter=id,eq,' + customerID + '&transform=1';

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

            break;
        case 2:

            url += '/orders?columns=id,customerID,orderID&filter=id,eq,' + orderID + '&transform=1';

            $.ajax({
                url: url,
                type: "GET",
                async: false,
                success: function(data){
                     var customerID = data.orders[0].customerID;
                     var orderID = data.orders[0].orderID;

                     $("#orderNumber").html(orderID);

                   var url = '<?php echo API_HOST_URL; ?>';
                   url += '/entities?columns=id,name&filter=id,eq,' + customerID + '&transform=1';

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

            break;
   		}

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
                                      $("#errorAlertTitle").html("Error");
                                      $("#errorAlertBody").html("There Was An Error Adding Need Contacts!");
                                      $("#errorAlert").modal('show');
                                  }
                               });
                          } else {
                              $("#errorAlertTitle").html("Error");
                              $("#errorAlertBody").html("There Was An Issue Clearing Need Contacts!");
                              $("#errorAlert").modal('show');
                          }
                       },
                       error: function() {
                          $("#errorAlertTitle").html("Error");
                          $("#errorAlertBody").html("There Was An Error Deleting Need Records!");
                          $("#errorAlert").modal('show');
                       }
                    });
                }
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

        var url = '<?php echo API_HOST_URL . "/locations_contacts?columns=location_id,contact_id&filter=entityID,eq," . $_SESSION['entityid']; ?>';
        var type = "GET";

        $.ajax({
           url: url,
           type: type,
           async: false,
           success: function(data){
                locations_contacts = data;
           },
           error: function() {
              $("#errorAlertTitle").html("Error");
              $("#errorAlertBody").html("There Was An Error Retrieving Location Contacts!");
              $("#errorAlert").modal('show');
           }
        });
    }

    function getLocations(city) {

        var url = '<?php echo API_HOST_URL . "/locations?columns=id,city,state,zip&filter[]=entityID,eq," . $_SESSION['entityid']; ?>';
        url += "&filter[]=city,sw," + city;
        var type = "GET";

        $.ajax({
           url: url,
           type: type,
           async: false,
           success: function(data){
              $("#errorAlertTitle").html("Success");
              $("#errorAlertBody").html(JSON.stringify(data));
              $("#errorAlert").modal('show');
           },
           error: function() {
              $("#errorAlertTitle").html("Error");
              $("#errorAlertBody").html("There Was An Error Retrieving Location Contacts!");
              $("#errorAlert").modal('show');
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
           url: '<?php echo API_HOST_URL . "/orders/"; ?>' + id,
           type: "PUT",
           data: JSON.stringify(orderComments),
           contentType: "application/json",
           async: false,
           success: function(){
                $("#errorAlertTitle").html("Success");
                $("#errorAlertBody").html("Comment Saved");
                $("#errorAlert").modal('show');
           },
           error: function() {
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Comment could not save.");
                $("#errorAlert").modal('show');
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
        var tzName = today.toLocaleString('en', {timeZoneName:'short'}).split(' ').pop();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0!
        var yyyy = today.getFullYear();
        var hours = today.getHours();
        var min = today.getMinutes();
        var sec = today.getSeconds();
        var tod = "";

        if(dd<10) {
            dd='0'+dd;
        }

        if(mm<10) {
            mm='0'+mm;
        }

        if(hours < 12){
            tod = "a.m.";

            if(hours == 0){
                hours = "12";
            }
            else if(hours<10) {

                hours='0'+hours;
            }
        }
        else{
            hours = hours - 12;
            tod = "p.m.";

            if(hours == 0){
                hours = "12";
            }
            else if(hours<10) {

                hours='0'+hours;
            }
        }

        if(min<10) {
            min='0'+min;
        }

        if(sec<10) {
            sec='0'+sec;
        }

        today = mm+"-"+dd+"-"+yyyy+" "+hours+":"+min+" "+tod+" "+tzName;

        var podTable = $("#pod-list-table").DataTable();
        var podList = podTable.ajax.json().orders[0].podList;

        var orderHistoryTable = $('#order-history-table').DataTable();
        var orderDetailTable = $('#order-details-table').DataTable();
        var orderDetailJSON = orderDetailTable.ajax.json();

        var orderNumber = orderDetailJSON.order_details[0].orders[0].orderID;
        var customerID = orderDetailJSON.order_details[0].orders[0].customerID;

        var id = $("#id").val();
        var vinNumber = $("#orderStatusVinNumber").val();
        var city = $("#city").val();
        var state = $("#state").val();
        var status = $("#orderStatus").val();
        var notes = $("#statusNotes").val();
        var carrierID = $("#carrierID").val();

        var carrier = "";

        allEntities.entities.forEach(function(entity){

            if(carrierID == entity.id){

                carrier = entity.name;
            }
        });

        var podIndex = -1;

        podList.forEach(function(pod, index){
            if(pod.vinNumber == vinNumber){
                podIndex = index;
            }
        });

        var orderStatus = {carrier:carrier, city: city, state: state, status: status, note: notes, createdAt: today, updatedAt: today};

        var data = podList[podIndex];
        var unitNumber = data.unitNumber;
        var truckProNumber = data.truckProNumber;
        var trailerYear = data.trailerYear;
        var trailerNotes = data.trailerNotes;
        var podNotes = data.notes;
        var deliveryDate = data.deliveryDate;
        var fileName = data.fileName;
        var podCarrier = data.carrier;

        var order_statuses = [];

        if(data.order_statuses != null) order_statuses = data.order_statuses;

        order_statuses.push(orderStatus);

        var pod = {vinNumber: vinNumber, notes: podNotes, deliveryDate: deliveryDate, fileName: fileName, carrier: podCarrier,
        unitNumber: unitNumber, truckProNumber: truckProNumber, trailerYear: trailerYear, trailerNotes: trailerNotes, order_statuses: order_statuses};

        var emailData = {carrierID: carrierID, customerID: customerID, orderNumber: orderNumber};

            podList.splice(podIndex, 1, pod);

            var orderData = {podList: podList};

            $.ajax({
                url: '<?php echo API_HOST_URL . "/orders/"; ?>' + id,
                type: 'PUT',
                data: JSON.stringify(orderData),
                contentType: "application/json",
                async: false,
                success: function(){

                    $.ajax({
                        url: '<?php echo HTTP_HOST; ?>' + '/sendorderstatusnotification',
                        type: "POST",
                        data: JSON.stringify(emailData),
                        contentType: "application/json",
                        async:false,
                        success: function(data){
                            $("#errorAlertTitle").html("Success");
                            $("#errorAlertBody").html(data);
                            $("#errorAlert").modal('show');

                            // Clear Form
                            $("#id").val('');
                            $("#orderStatusVinNumber").val('');
                            $("#city").val('');
                            $("#state").val('');
                            $("#orderStatus").val('');
                            $("#statusNotes").val('');
                            $("#carrierID").val('');
                            $("#saveOrderStatus").html("Save");
                            $("#saveOrderStatus").prop("disabled", false);
                            orderHistoryTable.ajax.reload();
                            podTable.ajax.reload();
                            $("#addOrderStatus").modal('hide');
                        },
                        error: function(error){
                            $("#errorAlertTitle").html("Error");
                            $("#errorAlertBody").html("Unable to send notification about status change.");
                            $("#errorAlert").modal('show');

                            $("#saveOrderStatus").html("Save");
                            $("#saveOrderStatus").prop("disabled", false);
                        }
                    });
                },
                error: function() {
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("There Was An Error Saving the Status");
                    $("#errorAlert").modal('show');

                     $("#saveOrderStatus").html("Save");
                     $("#saveOrderStatus").prop("disabled", false);
                }
            });




    }

    function addDeliveryStatus() {
        var orderDetailsTable = $("#order-details-table").DataTable();
        var json = orderDetailsTable.ajax.json();
        var data = json.order_details[0];

            var orderStatusSelect = '<select id="orderStatus" name="orderStatus" class="form-control mb-sm" required="required">\n';

            $("#id").val(data.orders[0].id);

            var inTransit = "";
            var inCarriersYard = "";
            var atShipper = "";
            var trailerLoaded = "";
            var atConsignee = "";
            var trailerDelivered = "";

            loadVinNumberList();

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
            url: '<?php echo API_HOST_URL . "/orders/"; ?>' + orderID,
            type: 'PUT',
            data: JSON.stringify(orderData),
            contentType: "application/json",
            async: false,
            success: function(){
                $("#errorAlertTitle").html("Success");
                $("#errorAlertBody").html("Trailer Data Successfully Saved.");
                $("#errorAlert").modal('show');

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
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Unable to Save Trailer Data to Orders");
                $("#errorAlert").modal('show');
            }
        });

    }

    function viewPOD(documentID){

        var documentURL = '<?php echo API_HOST_URL; ?>' + '/documents/' + documentID;

        $.get(documentURL, function(data){
	    window.open( data.documentURL, '_blank');
        });
    }

    function showOrderSummary(){
        $('#orderSummary').css('display', 'block');
        $('#trackingHistory').css('display', 'none');
        $('#editOrderDetails').css('display', 'none');
        $('#adminNotes').css('display', 'none');
        $('#orderLedger').css('display', 'none');
        closeAddStatus();
    }

    function showTrackingHistory(){
        $('#orderSummary').css('display', 'none');
        $('#trackingHistory').css('display', 'block');
        $('#editOrderDetails').css('display', 'none');
        $('#adminNotes').css('display', 'none');
        $('#orderLedger').css('display', 'none');
        closeAddStatus();
    }

    function showAdminNotes(){
        $('#orderSummary').css('display', 'none');
        $('#trackingHistory').css('display', 'none');
        $('#editOrderDetails').css('display', 'none');
        $('#adminNotes').css('display', 'block');
        $('#orderLedger').css('display', 'none');
        closeAddStatus();
    }

    function showOrderLedger(){
        $('#orderSummary').css('display', 'none');
        $('#trackingHistory').css('display', 'none');
        $('#editOrderDetails').css('display', 'none');
        $('#adminNotes').css('display', 'none');
        $('#orderLedger').css('display', 'block');
        closeAddStatus();
    }

    function populateEditForm(orderID){

        var url = '<?php echo API_HOST_URL; ?>' + '/orders?include=order_details,entities&filter=id,eq,' + orderID + '&transform=1';

        $.get(url, function(data){
            var order = data.orders[0];
            var needsDataPoints = order.needsDataPoints;

            //if(order.deliveryInformation == null) order.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
            if(order.deliveryInformation == null) order.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", deliveryHoursOfOperationOpen: "", deliveryHoursOfOperationClose: "", deliveryTimeZone: ""};

            //if(order.pickupInformation == null) order.pickupInformation = {pickupLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
            if(order.pickupInformation == null) order.pickupInformation = {pickupLocation: "", contactPerson: "", phoneNumber: "", pickupHoursOfOperationOpen: "", pickupHoursOfOperationClose: "", pickupTimeZone: ""};

            if(order.originationAddress1 == null) order.originationAddress1 = "";
            if(order.destinationAddress1 == null) order.destinationAddress1 = "";
            if(order.originationAddress2 == null) order.originationAddress2 = "";
            if(order.destinationAddress2 == null) order.destinationAddress2 = "";
            if(order.originationZip == null) order.originationZip = "";
            if(order.destinationZip == null) order.destinationZip = "";
            if(order.podList == null) order.podList = [];
            
            // Populate Edit form
            $('#pickupLocation').val(order.pickupInformation.pickupLocation);
            $('#pickupContactPerson').val(order.pickupInformation.contactPerson);
            $('#pickupPhoneNumber').val(order.pickupInformation.phoneNumber);
            //$('#pickupHoursOfOperation').val(order.pickupInformation.hoursOfOperation);
            $('#pickupHoursOfOperationOpen').val(order.pickupInformation.pickupHoursOfOperationOpen);
            $('#pickupHoursOfOperationClose').val(order.pickupInformation.pickupHoursOfOperationClose);
            $('#pickupTimeZone').val(order.pickupInformation.pickupTimeZone);
            $('#originationAddress1').val(order.originationAddress);
            $('#originationCity').val(order.originationCity);
            $('#originationState').val(order.originationState);
            $('#originationZip').val(order.originationZip);


            $('#deliveryLocation').val(order.deliveryInformation.deliveryLocation);
            $('#deliveryContactPerson').val(order.deliveryInformation.contactPerson);
            $('#deliveryPhoneNumber').val(order.deliveryInformation.phoneNumber);
            //$('#deliveryHoursOfOperation').val(order.deliveryInformation.hoursOfOperation);
            $('#deliveryHoursOfOperationOpen').val(order.deliveryInformation.deliveryHoursOfOperationOpen);
            $('#deliveryHoursOfOperationClose').val(order.deliveryInformation.deliveryHoursOfOperationClose);
            $('#deliveryTimeZone').val(order.deliveryInformation.deliveryTimeZone);
            $('#destinationAddress1').val(order.destinationAddress);
            $('#destinationCity').val(order.destinationCity);
            $('#destinationState').val(order.destinationState);
            $('#destinationZip').val(order.destinationZip);

            var dpli = '<div class="form-group row">' +
                            '<label for="qty" class="col-sm-3 col-form-label">Quantity</label>'+
                            '<div class="col-sm-9">' +
                            '<input id="qty" name="qty" class="form-control" value="'+order.qty+'">'+
                            '</div>'+
                            '</div>';

            for (var i = 0; i < dataPoints.object_type_data_points.length; i++) {
                var selected = '';
                var value = '';

                $.each(needsDataPoints, function(idx, obj) {
                  $.each(obj, function(key, val) {
                    if (dataPoints.object_type_data_points[i].columnName == key) {
                        value = val; // Get the value from the JSON data in the record to use to set the selected option in the dropdown

                    }
                  })
                });

                if(dataPoints.object_type_data_points[i].title == "Decals"){
                    dpli += '<div class="form-group row">' +
                            '<label for="decals" class="col-sm-3 col-form-label">Decals</label>'+
                            '<div class="col-sm-9">' +
                            '<input id="decals" name="decals" class="form-control" value="'+value+'">'+
                            '</div>'+
                            '</div>';
                  }
                  else{

                    dpli += '<div class="form-group row">' +
                            '<label for="' + dataPoints.object_type_data_points[i].columnName + '" class="col-sm-3 col-form-label">' + dataPoints.object_type_data_points[i].title + '</label>' +
                            '<div class="col-sm-9"> <select class="form-control" id="' + dataPoints.object_type_data_points[i].columnName + '" name="' + dataPoints.object_type_data_points[i].columnName + '">' +
                            ' <option value="">-Select From List-</option>\n';

                    for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {

                        if (dataPoints.object_type_data_points[i].object_type_data_point_values[v].value === value) {
                            selected = ' selected ';
                        } else {
                            selected = '';
                        }

                        dpli += '<option' + selected + '>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</option>\n';

                    }

                    dpli += '</select>' +
                            '</div></div>';
                  }
            }


            dpli += '<div class="form-group row">' +
                    '   <label for="rate" class="col-sm-3 col-form-label">Rate</label>' +
                    '   <div class="col-sm-9">' +
                    '       <input id="rate" name="rate" class="form-control" value="' + order.customerRate + '">' +
                    '   </div>' +
                    '</div>';

            dpli += '<div class="form-group row">' +
                    '   <label for="rateType" class="col-sm-3 col-form-label">Rate Type</label>' +
                    '   <div class="col-sm-9">' +
                    '       <input type="radio" id="rateType" name="rateType" value="Flat Rate" ' + (order.rateType == "Flat Rate" ? "checked" : "") + '/> Flat Rate ' +
                    '       <input type="radio" id="rateType" name="rateType" value="Mileage" ' + (order.rateType == "Mileage" ? "checked" : "") + '/> Mileage</div>' +
                    '   </div>'+
                    '</div>';

            dpli += '<div class="form-group row">' +
                    '   <label for="transportationMode" class="col-sm-3 col-form-label">Transportation Mode</label>' +
                    '   <div class="col-sm-9">' +
                    '       <select class="form-control" id="transportationMode" name="transportationMode">' +
                    '           <option value="">*Select Mode...</option>' +
                    '           <option value="Empty" ' + (order.transportationMode == "Empty" ? "selected" : "") + '>Empty</option>' +
                    '           <option value="Load Out" ' + (order.transportationMode == "Load Out" ? "selected" : "") + '>Load Out</option>' +
                    '           <option value="Either (Empty or Load Out)" ' + (order.transportationMode == "Either (Empty or Load Out)" ? "selected" : "") + '>Either (Empty or Load Out)</option>' +
                    '       </select>' +
                    '   </div>'+
                    '</div>';

            $("#dp-check-list-box").empty().html(dpli);

            var order_details = order.order_details;

            $.each(order_details, function(key, detail){
                //if(detail.deliveryInformation == null) detail.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
                if(detail.deliveryInformation == null) detail.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", deliveryHoursOfOperationOpen: "", deliveryHoursOfOperationClose: "", deliveryTimeZone: ""};
                if(detail.destinationAddress == null) detail.destinationAddress = "";
                if(detail.destinationZip == null) detail.destinationZip = "";

                var relayNumber = key + 1;

                $('#relay_id' + relayNumber).val(detail.id);
                $('#address_relay' + relayNumber).val(detail.destinationAddress);
                $('#city_relay' + relayNumber).val(detail.destinationCity);
                $('#state_relay' + relayNumber).val(detail.destinationState);
                $('#zip_relay' + relayNumber).val(detail.destinationZip);
                $('#pickupDate_relay' + relayNumber).val(detail.pickupDate);
                $('#deliveryDate_relay' + relayNumber).val(detail.deliveryDate);

                $('#deliveryLocation_relay' + relayNumber).val(detail.deliveryInformation.deliveryLocation);
                $('#contactPerson_relay' + relayNumber).val(detail.deliveryInformation.contactPerson);
                $('#phoneNumber_relay' + relayNumber).val(detail.deliveryInformation.phoneNumber);
                //$('#hoursOfOperation_relay' + relayNumber).val(detail.deliveryInformation.hoursOfOperation);
                $('#hoursOfOperationOpen_relay' + relayNumber).val(detail.deliveryInformation.deliveryHoursOfOperationOpen);
                $('#hoursOfOperationClose_relay' + relayNumber).val(detail.deliveryInformation.deliveryHoursOfOperationClose);
                $('#timeZone_relay' + relayNumber).val(detail.deliveryInformation.deliveryTimeZone);

                if(relayNumber == 4) return false;

            });



            var unitData = "";
            var unitEdit = "";
            $('#addTrailer').html('');
            $('#unitDataBody').html('');

            $.each(order.podList, function(key, unit){

                if(unit.year == null) unit.year = "";
                if(unit.make == null) unit.make = "";
                if(unit.licenseNumber == null) unit.licenseNumber = "";
                if(unit.value == null) unit.value = "0.00";

                unitData += "<tr>" +
                        "<td>"+ unit.year +"</td>" +
                        "<td>"+ unit.make +"</td>" +
                        "<td>"+ unit.licenseNumber +"</td>" +
                        "<td>"+ unit.unitNumber +"</td>" +
                        "<td>"+ unit.vinNumber +"</td>" +
                        "<td>"+ unit.truckProNumber +"</td>" +
                        "<td>"+ unit.poNumber +"</td>" +
                        "<td>$"+ parseFloat(unit.value).toFixed(2) +"</td>" +
                        "</tr>";

                unitEdit += '<div class="row">\n\
                                <div class="col-md-1">\n\
                                    <div class="form-group">\n\
                                            <label for="year' + (key + 1) + '">Year</label>\n\
                                            <input class="form-control" id="year' + (key + 1) + '" placeholder="" type="text" value="'+unit.year+'">\n\
                                    </div>\n\
                                </div>\n\
                                <div class="col-md-2">\n\
                                    <div class="form-group">\n\
                                            <label for="make' + (key + 1) + '">Make</label>\n\
                                            <input class="form-control" id="make' + (key + 1) + '" placeholder="" type="text" value="'+unit.make+'">\n\
                                    </div>\n\
                                </div>\n\
                                <div class="col-md-1">\n\
                                    <div class="form-group">\n\
                                            <label for="make' + (key + 1) + '">License #</label>\n\
                                            <input class="form-control" id="licenseNumber' + (key + 1) + '" placeholder="" type="text" value="'+unit.licenseNumber+'">\n\
                                    </div>\n\
                                </div>\n\
                                <div class="col-md-1">\n\
                                    <div class="form-group">\n\
                                            <label for="unitNumber' + (key + 1) + '">Unit #</label>\n\
                                            <input class="form-control" id="unitNumber' + (key + 1) + '" placeholder="" type="text" value="'+unit.unitNumber+'">\n\
                                    </div>\n\
                                </div>\n\
                                <div class="col-md-2">\n\
                                    <div class="form-group">\n\
                                            <label for="vinNumber' + (key + 1) + '">VIN #</label>\n\
                                            <input class="form-control" id="vinNumber' + (key + 1) + '" placeholder="" type="text" value="'+unit.vinNumber+'">\n\
                                    </div>\n\
                                </div>\n\
                                <div class="col-md-2">\n\
                                    <div class="form-group">\n\
                                            <label for="truckProNumber' + (key + 1) + '">Truck/Pro #</label>\n\
                                            <input class="form-control" id="truckProNumber' + (key + 1) + '" placeholder="" type="text" value="'+unit.truckProNumber+'">\n\
                                    </div>\n\
                                </div>\n\
                                <div class="col-md-2">\n\
                                    <div class="form-group">\n\
                                            <label for="poNumber' + (key + 1) + '">P.O. #</label>\n\
                                            <input class="form-control" id="poNumber' + (key + 1) + '" placeholder="" type="text" value="'+unit.poNumber+'">\n\
                                    </div>\n\
                                </div>\n\
                                <div class="col-md-1">\n\
                                    <div class="form-group">\n\
                                            <label for="value' + (key + 1) + '">Value</label>\n\
                                            <input class="form-control" id="value' + (key + 1) + '" placeholder="" type="text" value="'+parseFloat(unit.value).toFixed(2)+'">\n\
                                    </div>\n\
                                </div>\n\
                            </div>';
            });

            $('#addTrailer').append(unitEdit);
            $('#unitDataBody').append(unitData);



        });
    }

    function showEditOrder(){
        var orderID = $("#orderID").val();
        populateEditForm(orderID);

        $('#order-details').css('display', 'none');
        $('#editOrderDetails').css('display', 'block');
    }

    function closeEditOrder(){
        $('#order-details').css('display', 'block');
        $('#editOrderDetails').css('display', 'none');
    }

    function openAddStatus(){
        $('#addStatus').css('display', 'block');
        $('#statusesList').css('display', 'none');
        $('#btnAddStatus').css('display', 'none');
        $('#btnCloseAddStatus').css('display', 'block');
    }

    function closeAddStatus(){
        $('#addStatus').css('display', 'none');
        $('#statusesList').css('display', 'block');
        $('#btnAddStatus').css('display', 'block');
        $('#btnCloseAddStatus').css('display', 'none');
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

    .text-summary{
       color: inherit;
    }

    .text-summary li{
       width: 250px;
    }

    .deadline-warning{
        color: orange !important;
        font-weight: bold !important;
        font-size: large;
    }

    .deadline-danger{
        color: red !important;
        font-weight: bold !important;
        font-size: large;
    }

    .w-100{
        width: 100% !important;
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
         <div class="widget-controls">
             <!--<a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
             <a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>-->
             <button type="button" class="btn btn-primary btn-md" onclick="loadTableAJAX('Open');" id="viewOpenOrders">View Open Orders</button>
             <button type="button" class="btn btn-primary btn-md" onclick="loadTableAJAX('Closed');" id="viewClosedOrders">View Closed Orders</button>
         </div>
     </header>
     <div class="widget-body">
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="orders-table" class="table table-striped table-hover" width="100%">
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
                     <th></th>
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
        <li onclick="ajaxFormCall('listOrders');" onmouseover="" style="cursor: pointer;">View Orders</li>
        <li class="active">View Order Details</li>
    </ol>

    <section class="widget">
        <header>
            <!--<h4><span class="fw-semi-bold">Order Details</span></h4>-->
            <div class="widget-controls">
                <a data-widgster="close" title="Close" href="Javascript:closeOrderDetails()"><i class="glyphicon glyphicon-remove"></i></a>
            </div>
        </header>

            <?php
                if($_SESSION['entitytype'] == 0){
            ?>
        <div class="text-summary">Order # <p id="orderNumber" style="display: inline;"></p></div>
        <ul class="text-summary">
            <li>Customer: <p id="customerName" style="display: inline;"></p></li>
            <li>From: <p id="fromAddress" style="display: inline;"></p></li>
            <li>Number of Carriers: <p id="carriersCount" style="display: inline;"></p></li>
        </ul>
        <br>
        <ul class="text-summary">
            <li>Trailer QTY: <p id="displayQty" style="display: inline;"></p></li>
            <li>To: <p id="toAddress" style="display: inline;"></p></li>
            <li>Number of Relays: <p id="relayCount" style="display: inline;"></p></li>
        </ul>
        <br>
        <ul class="text-summary">
            <li>Customer Rate: $<p id="displayCustomerRate" style="display: inline;"></p></li>
            <li>Carrier Payout Rate: $<p id="displayCarrierTotal" style="display: inline;"></p></li>
            <li>Total Revenue: $<p id="displayTotalRevenue" style="display: inline;"></p></li>
        </ul>
        <br>

        <input type="hidden" id="customerID" name="customerID" value="" />

        <?php
            }
            else{
                ?>
        <ul class="text-summary">
            <li>Order # <p id="orderNumber" style="display: inline;"></p></li>
            <li>From: <p id="fromAddress" style="display: inline;"></p></li>
        </ul>
        <br>
        <ul class="text-summary">
            <li>Trailer QTY: <p id="displayQty" style="display: inline;"></p></li>
            <li>To: <p id="toAddress" style="display: inline;"></p></li>
        </ul>
        <?php
            }
        ?>
        <br>
        <br>
        <ul id="ordersTabs" class="tablist">
            <li id="current">
                <a href="#" onclick="showOrderSummary();">Order Summary</a>
            </li>
            <li>
                <a href="#" onclick="showTrackingHistory();">Tracking History</a>
            </li>
            <li>
                <a href="#" onclick="showOrderLedger();">Order Ledger</a>
            </li>

            <?php
                if($_SESSION['entitytype'] == 0){
            ?>
            <li>
                <a href="#" onclick="showAdminNotes();">Admin Notes</a>
            </li>
        <?php
            }
        ?>
        </ul>

        <div id="orderSummary" class="row">
            <!-- start left column content -->
            <div class="col-md-3">
                <div id="relayList" class="carrier-container">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Carriers</h4>
                            <div class="fa fa-lg fa-refresh text-blue" style="float: right; position: relative; top: -25px;"></div>
                            <br>
                        </div>
                    </div>

                    <div class="row carrier-row carrier-row__border-top carrier-row__selected">
                        <div class="col-md-3">
                            <div class="carrier-logo carrier-logo__buds"></div>
                        </div>
                        <div class="col-md-9">
                            <h4>Buds Enterprise</h4>
                            QTY <span class="pad-left-25">5</span>
                        </div>
                    </div>

                    <br>
                    <br>
                </div>
            </div>

            <!-- start right column -->
            <?php
                if($_SESSION['entitytype'] == 0){
            ?>
            <div class="col-md-9">
                <div class="carrier-summary__top-container">
                    <div class="row">
                        <div class="col-md-1">
                            <div class="carrier-logo carrier-logo__buds"></div>
                        </div>
                        <div id="carrierDistance" class="col-md-3">
                            <h5>Buds Enterprise</h5>
                            <small class="text-blue">Distance: 1663 miles</small>
                        </div>
                        <div class="col-md-4">
                            <ul class="list-inline">
                                <li class="list-inline-item" style="width: 150px;">In Transit</li>
                                <li class="list-inline-item">
                                        <span class="carrier-summary__quantity bkg-qty-yellow border-green">5</span>
                                </li>
                            </ul>
                            <ul class="list-inline">
                              <li class="list-inline-item" style="width: 150px;">Pending Pick Up</li>
                              <li class="list-inline-item"><span class="carrier-summary__quantity bkg-qty-green border-green">2</span></li>
                            </ul>
                            <ul class="list-inline">
                              <li class="list-inline-item" style="width: 150px;">At Destination</li>
                              <li class="list-inline-item"><span class="carrier-summary__quantity bkg-md-gray border-green">0</span></li>
                            </ul>
                            <ul class="list-inline">
                              <li class="list-inline-item" style="width: 150px;">Relay</li>
                              <li class="list-inline-item"><span class="carrier-summary__quantity bkg-md-gray border-green">0</span></li>
                            </ul>
                            <ul class="list-inline">
                              <li class="list-inline-item" style="width: 150px;">Other</li>
                              <li class="list-inline-item"><span class="carrier-summary__quantity bkg-md-gray border-green">0</span></li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <ul class="list-inline text-right">
                              <li class="list-inline-item"><a href="#" class="btn btn-primary" onclick="saveCurrentOrderDetail();">Save Changes</a></li>
                              <li class="list-inline-item"><a href="#" id="showEditOrder" class="btn btn-secondary" onclick="showEditOrder();"><span class="fa fa-pencil"></span> Edit</a></li>
                            </ul>
                            <p>&nbsp;</p>
                            <br />
                            <ul class="list-inline text-right">
                              <li class="list-inline-item"><button type="button" class="btn btn-primary" id="btnUploadPO"><span class="fa fa-upload"></span> Upload PO</i></button></li>
                              <li class="list-inline-item"><button type="button" class="btn btn-secondary" id="btnViewPO" onclick='viewPOD($("#documentID").val());'><span class="fa fa-eye"></span> View</button></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="carrier-summary__bottom-container">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="hidden" class="form-control" id="orderDetailID">
                            <input type="hidden" class="form-control" id="documentID">
                            <h5 class="text-blue">Pick Up Information</h5>
                            <input type="text" class="form-control" id="pickupName" placeholder="Business Name"><br>
                            <input type="text" class="form-control" id="pickupAddress" placeholder="Business Address"><br>
                            <input type="text" class="form-control" id="pickupCity" placeholder="Business City"><br>

                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-control" id="pickupState">
                                            <option value="">*Select State...</option>
                                              <?php
                                                    foreach($states->states->records as $value) {
                                                        $selected = ($value[0] == $state) ? 'selected=selected':'';
                                                        echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                                                    }
                                              ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="pickupZip" placeholder="Business Zip Code">
                                </div>
                            </div>
                            <br>

                                <input type="text" class="form-control" id="pickupPhone" placeholder="Contact Phone Number"><br>
                                <input type="text" class="form-control" id="pickupContact" placeholder="Contact - First and Last Name"><br>
                                    <!--input type="text" class="form-control" id="pickupHours" placeholder="Hours Of Operations"><br-->

                                <div class="row">
                                    <div class="col-sm-3">
                                            <select class="form-control" id="pickupHoursOfOperationOpen">
                                                <option>00:30</option>
                                                <option>01:00</option>
                                                <option>01:30</option>
                                                <option>02:00</option>
                                                <option>02:30</option>
                                                <option>03:00</option>
                                                <option>03:30</option>
                                                <option>04:00</option>
                                                <option>04:30</option>
                                                <option>05:00</option>
                                                <option>05:30</option>
                                                <option>06:00</option>
                                                <option>06:30</option>
                                                <option>07:00</option>
                                                <option>07:30</option>
                                                <option selected>08:00</option>
                                                <option>08:30</option>
                                                <option>09:00</option>
                                                <option>09:30</option>
                                                <option>10:00</option>
                                                <option>10:30</option>
                                                <option>11:00</option>
                                                <option>11:30</option>
                                                <option>12:00</option>
                                                <option>12:30</option>
                                                <option>13:00</option>
                                                <option>13:30</option>
                                                <option>14:00</option>
                                                <option>14:30</option>
                                                <option>15:00</option>
                                                <option>15:30</option>
                                                <option>16:00</option>
                                                <option>16:30</option>
                                                <option>17:00</option>
                                                <option>17:30</option>
                                                <option>18:00</option>
                                                <option>18:30</option>
                                                <option>19:00</option>
                                                <option>19:30</option>
                                                <option>20:00</option>
                                                <option>20:30</option>
                                                <option>21:00</option>
                                                <option>21:30</option>
                                                <option>22:00</option>
                                                <option>22:30</option>
                                                <option>23:00</option>
                                                <option>23:30</option>
                                                <option>24:00</option>
                                            </select>
                                    </div>
                                    <div class="col-sm-3">
                                            <select class="form-control" id="pickupHoursOfOperationClose">
                                                <option>00:30</option>
                                                <option>01:00</option>
                                                <option>01:30</option>
                                                <option>02:00</option>
                                                <option>02:30</option>
                                                <option>03:00</option>
                                                <option>03:30</option>
                                                <option>04:00</option>
                                                <option>04:30</option>
                                                <option>05:00</option>
                                                <option>05:30</option>
                                                <option>06:00</option>
                                                <option>06:30</option>
                                                <option>07:00</option>
                                                <option>07:30</option>
                                                <option>08:00</option>
                                                <option>08:30</option>
                                                <option>09:00</option>
                                                <option>09:30</option>
                                                <option>10:00</option>
                                                <option>10:30</option>
                                                <option>11:00</option>
                                                <option>11:30</option>
                                                <option>12:00</option>
                                                <option>12:30</option>
                                                <option>13:00</option>
                                                <option>13:30</option>
                                                <option>14:00</option>
                                                <option>14:30</option>
                                                <option>15:00</option>
                                                <option>15:30</option>
                                                <option>16:00</option>
                                                <option>16:30</option>
                                                <option selected>17:00</option>
                                                <option>17:30</option>
                                                <option>18:00</option>
                                                <option>18:30</option>
                                                <option>19:00</option>
                                                <option>19:30</option>
                                                <option>20:00</option>
                                                <option>20:30</option>
                                                <option>21:00</option>
                                                <option>21:30</option>
                                                <option>22:00</option>
                                                <option>22:30</option>
                                                <option>23:00</option>
                                                <option>23:30</option>
                                                <option>24:00</option>
                                            </select>
                                    </div>
                                    <div class="col-sm-3">
                                            <select class="form-control" id="pickupTimeZone">
                                                <option>EST (Eastern)</option>
                                                <option>CST (Central)</option>
                                                <option selected>MPT (Mountain)</option>
                                                <option>PST (Pacific)</option>
                                            </select>
                                    </div>
                                </div>

                                <label for="pick-up-date" class="text-blue">Pick Up Date</label>
                                <input type="date" class="form-control" style="padding-left: 40px;" id="pickupDate" placeholder="MM/DD/YYYY">
                                <div class="fa fa-lg fa-calendar text-blue" style="position: relative; left: 10px; top: -28px;"></div>
                        </div>
                            <div class="col-md-4">
                                <h5 class="text-blue">Delivery Information</h5>
                                <input type="text" class="form-control" id="deliveryName" placeholder="Business Name"><br>
                                <input type="text" class="form-control" id="deliveryAddress" placeholder="Business Address"><br>
                                <input type="text" class="form-control" id="deliveryCity" placeholder="Business City"><br>

                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-control" id="deliveryState">
                                            <option value="">*Select State...</option>
                                              <?php
                                                    foreach($states->states->records as $value) {
                                                        $selected = ($value[0] == $state) ? 'selected=selected':'';
                                                        echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                                                    }
                                              ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="deliveryZip" placeholder="Business Zip Code">
                                </div>
                            </div>
                            <br>

                                <input type="text" class="form-control" id="deliveryPhone" placeholder="Contact Phone Number"><br>
                                <input type="text" class="form-control" id="deliveryContact" placeholder="Contact - First and Last Name"><br>
                                <!--input type="text" class="form-control" id="deliveryHours" placeholder="Hours Of Operations"><br-->

                                <div class="row">
                                    <div class="col-sm-3">
                                            <select class="form-control" id="deliveryHoursOfOperationOpen">
                                                <option>00:30</option>
                                                <option>01:00</option>
                                                <option>01:30</option>
                                                <option>02:00</option>
                                                <option>02:30</option>
                                                <option>03:00</option>
                                                <option>03:30</option>
                                                <option>04:00</option>
                                                <option>04:30</option>
                                                <option>05:00</option>
                                                <option>05:30</option>
                                                <option>06:00</option>
                                                <option>06:30</option>
                                                <option>07:00</option>
                                                <option>07:30</option>
                                                <option selected>08:00</option>
                                                <option>08:30</option>
                                                <option>09:00</option>
                                                <option>09:30</option>
                                                <option>10:00</option>
                                                <option>10:30</option>
                                                <option>11:00</option>
                                                <option>11:30</option>
                                                <option>12:00</option>
                                                <option>12:30</option>
                                                <option>13:00</option>
                                                <option>13:30</option>
                                                <option>14:00</option>
                                                <option>14:30</option>
                                                <option>15:00</option>
                                                <option>15:30</option>
                                                <option>16:00</option>
                                                <option>16:30</option>
                                                <option>17:00</option>
                                                <option>17:30</option>
                                                <option>18:00</option>
                                                <option>18:30</option>
                                                <option>19:00</option>
                                                <option>19:30</option>
                                                <option>20:00</option>
                                                <option>20:30</option>
                                                <option>21:00</option>
                                                <option>21:30</option>
                                                <option>22:00</option>
                                                <option>22:30</option>
                                                <option>23:00</option>
                                                <option>23:30</option>
                                                <option>24:00</option>
                                            </select>
                                    </div>
                                    <div class="col-sm-3">
                                            <select class="form-control" id="deliveryHoursOfOperationClose">
                                                <option>00:30</option>
                                                <option>01:00</option>
                                                <option>01:30</option>
                                                <option>02:00</option>
                                                <option>02:30</option>
                                                <option>03:00</option>
                                                <option>03:30</option>
                                                <option>04:00</option>
                                                <option>04:30</option>
                                                <option>05:00</option>
                                                <option>05:30</option>
                                                <option>06:00</option>
                                                <option>06:30</option>
                                                <option>07:00</option>
                                                <option>07:30</option>
                                                <option>08:00</option>
                                                <option>08:30</option>
                                                <option>09:00</option>
                                                <option>09:30</option>
                                                <option>10:00</option>
                                                <option>10:30</option>
                                                <option>11:00</option>
                                                <option>11:30</option>
                                                <option>12:00</option>
                                                <option>12:30</option>
                                                <option>13:00</option>
                                                <option>13:30</option>
                                                <option>14:00</option>
                                                <option>14:30</option>
                                                <option>15:00</option>
                                                <option>15:30</option>
                                                <option>16:00</option>
                                                <option>16:30</option>
                                                <option selected>17:00</option>
                                                <option>17:30</option>
                                                <option>18:00</option>
                                                <option>18:30</option>
                                                <option>19:00</option>
                                                <option>19:30</option>
                                                <option>20:00</option>
                                                <option>20:30</option>
                                                <option>21:00</option>
                                                <option>21:30</option>
                                                <option>22:00</option>
                                                <option>22:30</option>
                                                <option>23:00</option>
                                                <option>23:30</option>
                                                <option>24:00</option>
                                            </select>
                                    </div>
                                    <div class="col-sm-3">
                                            <select class="form-control" id="deliveryTimeZone">
                                                <option>EST (Eastern)</option>
                                                <option>CST (Central)</option>
                                                <option selected>MPT (Mountain)</option>
                                                <option>PST (Pacific)</option>
                                            </select>
                                    </div>
                                </div>

                                <label for="pick-up-date" class="text-blue">Drop Off Date</label>
                            <input type="date" class="form-control" style="padding-left: 40px;" id="deliveryDate" placeholder="MM/DD/YYYY">
                            <div class="fa fa-lg fa-calendar text-blue" style="position: relative; left: 10px; top: -28px;"></div>
                            </div>
                            <div class="col-md-2">
                                <label for="transport-mode">Transport Mode</label>
                                <select class="form-control" id="transportMode">
                                    <option value="">*Select Mode...</option>
                                    <option value="Empty">Empty</option>
                                    <option value="Load Out">Load Out</option>
                                    <option value="Either (Empty or Load Out)">Either (Empty or Load Out)</option>
                                </select><br><br>
                                <label for="transport-rate">Carrier Rate</label>
                                <input type="text" class="form-control" id="carrierRate" placeholder="i.e. - $250">
                            </div>
                            <div class="col-md-2">
                                    <label for="transport-rate">Quantity</label>
                            <input type="text" class="form-control" id="carrierQty" placeholder="Number of Items">
                            </div>
                    </div>
                </div>
            </div>
            <?php
                }
                else{
                    ?>

            <div class="col-md-9">
                <div class="carrier-summary__top-container">
                    <div class="row">
                        <div class="col-md-1">
                            <div class="carrier-logo carrier-logo__buds"></div>
                        </div>
                        <div id="carrierDistance" class="col-md-3">
                            <h5>Buds Enterprise</h5>
                            <small class="text-blue">Distance: 1663 miles</small>
                        </div>
                        <div class="col-md-4">
                            <ul class="list-inline">
                                    <li class="list-inline-item" style="width: 150px;">In Transit</li>
                                    <li class="list-inline-item">
                                            <span class="carrier-summary__quantity bkg-qty-yellow border-green">5</span>
                                    </li>
                            </ul>
                            <ul class="list-inline">
                              <li class="list-inline-item" style="width: 150px;">Pending Pick Up</li>
                              <li class="list-inline-item"><span class="carrier-summary__quantity bkg-qty-green border-green">2</span></li>
                            </ul>
                            <ul class="list-inline">
                              <li class="list-inline-item" style="width: 150px;">At Destination</li>
                              <li class="list-inline-item"><span class="carrier-summary__quantity bkg-md-gray border-green">0</span></li>
                            </ul>
                            <ul class="list-inline">
                              <li class="list-inline-item" style="width: 150px;">Relay</li>
                              <li class="list-inline-item"><span class="carrier-summary__quantity bkg-md-gray border-green">0</span></li>
                            </ul>
                            <ul class="list-inline">
                              <li class="list-inline-item" style="width: 150px;">Other</li>
                              <li class="list-inline-item"><span class="carrier-summary__quantity bkg-md-gray border-green">0</span></li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            Deadline to Deliver: <p id="deliveryDeadline" style="display: inline;"></p><br>
                            <br>
                            Notes from prior carrier:<br>
                            This part is still static.
                        </div>
                    </div>
                </div>

                <div class="carrier-summary__bottom-container">
                    <div class="row">
                        <div id="carrierPickupInformation" class="col-md-4">
                            <h5 class="text-blue">Pick Up From</h5>
                            Wabash National<br>
                            3700 E. Veterans Memorial Pkwy<br>
                            Lafayette, Indiana 47909<br>
                            <br>
                            708-758-7430<br>
                            <br>
                            David Rupp<br>
                            <br>
                            M-F, 8am to 5pm ET<br>
                            <br>
                            <strong>Pick Up Date</strong><br>
                            <div style="margin-left: 30px;">12/20/2017</div>
                            <div class="fa fa-lg fa-calendar text-blue" style="position: relative; left: 0; top: -22px;"></div>
                        </div>
                        <div id="carrierDeliveryInformation"  class="col-md-4">
                            <h5 class="text-blue">Deliver To</h5>
                            Federal Express Freight<br>
                            470 E Joe Orr Rd<br>
                            Chicago Heights, IL 60411<br>
                            <br>
                            817-740-9980<br>
                            <br>
                            John Burgess<br>
                            <br>
                            M-F, 9am to 2pm CT<br>
                            <br>
                            <strong>Delivery Date</strong><br>
                            <div style="margin-left: 30px;">01/06/2018</div>
                            <div class="fa fa-lg fa-calendar text-blue" style="position: relative; left: 0; top: -22px;"></div>
                        </div>
                        <div class="col-md-2">
                            <br>
                            <strong>Transport Mode</strong><br>
                            <!-- transportMode -->
                            <p id="transportMode" style="display: inline;"></p><br><br>
                        </div>
                        <div class="col-md-2">
                            <br>
                            <strong>Quantity</strong>
                            <br>
                            <!-- carrierQty -->
                           <p id="carrierQty" style="display: inline;"></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            ?>
        </div>

        <div id="trackingHistory" class="row" style="display: none;">
            <!-- start left column content -->
            <div class="col-md-3">
                <div id="trailerList" class="carrier-container">
                    <div class="row trailer-row__border-bot trailer-row__notselected">
                        <div class="col-md-12">
                            <h4>Trailer List</h4>
                            <div class="fa fa-lg fa-refresh text-blue" style="float: right; position: relative; top: -25px;"></div>
                            <br>
                        </div>
                    </div>

                    <div class="row trailer-row trailer-row__border-top trailer-row__selected" onclick="displayTrailer(this)">
                        <div class="col-md-12">
                            <h4>Vin#: 1JJV532D0JL041440</h4>
                            <div class="text-blue">Unit #: <span class="pad-left-25">JBHZ 675140</span></div>
                        </div>
                    </div>

                    <div class="row trailer-row trailer-row__border-bot trailer-row__notselected" onclick="displayTrailer(this)">
                        <div class="col-md-12">
                            <h4>Vin#: 1JJV532D0JL041443</h4>
                            <div class="text-blue">Unit #: <span class="pad-left-25">JBHZ 675143</span></div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- start right column -->
            <?php
                if($_SESSION['entitytype'] < 2){
            ?>
            <!-- start right column content -->
            <div class="col-md-9">
                <div class="carrier-summary__top-container">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 id="displayVinNumber" style="display: none"></h4>
                            <h4 id="displayUnitNumber"></h4>
                            <ul class="list-inline">
                                <li class="list-inline-item">Active Carrier:</li>
                                <li class="list-inline-item">
                                    <select class="form-control" id="activeCarrier">

                                    </select>
                                </li>
                                <li <?php if($_SESSION['entitytype'] == 1) echo 'style="display: none;"'; ?> class="list-inline-item pull-right"><button id="btnAddStatus" class="btn btn-primary" onclick="openAddStatus()">Add Status</button></li>
                                <li <?php if($_SESSION['entitytype'] == 1) echo 'style="display: none;"'; ?> class="list-inline-item pull-right"><button id="btnCloseAddStatus" class="btn btn-primary" onclick="closeAddStatus()">Close Add Status</button></li>
                            </ul>
                        </div>

                    </div>
                </div>

                <div id="statusesList" class="carrier-summary__bottom-container">
                    <div class="row">
                        <div class="col-md-4">
                            <!-- start carrier 1 panel -->
                            <div class="carrier-tracking__panel">
                                <div class="row">
                                    <div class="col-md-3">
                                        <img src="img/logo-truck-warrior.png" width="53" height="44" alt=""/>
                                    </div>
                                    <div class="col-md-9">
                                        <h5 class="text-bright-blue">Buds Enterprise</h5>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4">
                                        <span class="text-blue">Last Location:</span><br>
                                        <span class="text-blue">Date</span><br>
                                    </div>
                                    <div class="col-md-8">
                                        Lafayette, GA<br>
                                        12/01/2017<br>
                                    </div>
                                </div>
                                <hr>
                                <ul class="list-inline">
                                    <li class="list-inline-item">Add a Note</li>
                                    <li class="list-inline-item pad-left-25"><span class="fa fa-pencil text-bright-blue"></span></li>
                                </ul>
                                <p>Schedule to arrive at 10pm on 12/22</p>
                            </div>
                            <!-- end carrier 1 panel -->
                        </div>
                        <div class="col-md-4">
                            <!-- start carrier 2 panel -->
                            <div class="carrier-tracking__panel dimmed">
                                <div class="row">
                                    <div class="col-md-3">
                                        <img src="img/logo-truck-warrior.png" width="53" height="44" alt=""/>
                                    </div>
                                    <div class="col-md-9">
                                        <h5 class="text-bright-blue">Buds Enterprise</h5>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4">
                                        <span class="text-blue">Last Location:</span><br>
                                        <span class="text-blue">Date</span><br>
                                    </div>
                                    <div class="col-md-8">
                                        Lafayette, GA<br>
                                        12/01/2017<br>
                                    </div>
                                </div>
                                <hr>
                                <ul class="list-inline">
                                    <li class="list-inline-item">Add a Note</li>
                                    <li class="list-inline-item pad-left-25"><span class="fa fa-pencil text-bright-blue"></span></li>
                                </ul>
                                <p>Schedule to arrive at 10pm on 12/22</p>
                            </div>
                            <!-- end carrier 2 panel -->
                        </div>
                        <div class="col-md-4">
                            <!-- start carrier 3 panel -->
                            <div class="carrier-tracking__panel dimmed">
                                <div class="row">
                                    <div class="col-md-4">
                                        <img src="img/logo-truck-warrior.png" width="53" height="44" alt=""/>
                                    </div>
                                    <div class="col-md-8">
                                        <h5 class="text-bright-blue">Buds Enterprise</h5>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4">
                                        <span class="text-blue">Last Location:</span><br>
                                        <span class="text-blue">Date</span><br>
                                    </div>
                                    <div class="col-md-8">
                                        Lafayette, GA<br>
                                        12/01/2017<br>
                                    </div>
                                </div>
                                <hr>
                                <ul class="list-inline">
                                    <li class="list-inline-item">Add a Note</li>
                                    <li class="list-inline-item pad-left-25"><span class="fa fa-pencil text-bright-blue"></span></li>
                                </ul>
                                <p>Schedule to arrive at 10pm on 12/22</p>
                            </div>
                            <!-- end carrier 3 panel -->
                        </div>
                    </div>
                </div>

                <div id="addStatus" class="carrier-summary__bottom-container" style="display: none;">

                    <input type="hidden" id="statusID" value="" />
                    <input type="hidden" id="statusOrderID" value="" />
                    <input type="hidden" id="statusOrderDetailID" value="" />
                    <input type="hidden" id="statusCarrierID" value="" />
                    <input type="hidden" id="statusDocumentID" value="" />
                    <input type="hidden" id="statusFileName" value="" />
                    <input type="hidden" id="statusVinNumber" value="" />
                    <input type="hidden" id="statusUnitNumber" value="" />
                    <div class="row">
                        <div class="col-md-1">
                            <div class="carrier-logo carrier-logo__buds"></div>
                        </div>
                        <div class="col-md-3">&nbsp;</div>
                        <div class="col-md-3">
                            <div class="text-blue">Update Trailer Status:</div>
                        </div>
                        <div class="col-md-4">
                            <select class="custom-select bkg-qty-yellow" id="statusTrailerStatus">
                                <option selected>In Transit</option>
                                <option>In Carrier Yard</option>
                                <option>At Shipper To Be Loaded</option>
                                <option>Trailer Loaded In Route</option>
                                <option>At Consignee To Be Unloaded</option>
                                <option>Trailer Delivered</option>
                            </select>
                        </div>
                        <div class="col-md-1">&nbsp;</div>
                    </div>
                    <div class="row">
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-3">&nbsp;</div>
                        <div class="col-md-3">
                            <div class="text-blue">Update Current Location:</div>
                        </div>
                        <div class="col-md-4">
                            <input type="current-location" class="form-control" id="statusCurrentLocation" placeholder="City, State" value="Augusta GA"><br>
                        </div>
                        <div class="col-md-1">&nbsp;</div>
                    </div>
                    <div class="row">
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-3">&nbsp;</div>
                        <div class="col-md-3">
                            <div class="text-blue">Loading Status:</div>
                        </div>
                        <div class="col-md-4">
                            <input type="loading-status" class="form-control" id="statusLoadingStatus" placeholder="Loaded or Unloaded?" value="Loaded"><br>
                        </div>
                        <div class="col-md-1">&nbsp;</div>
                    </div>
                    <div class="row">
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-3">&nbsp;</div>
                        <div class="col-md-3">
                            <div class="text-blue">Arrival ETA</div>
                        </div>
                        <div class="col-md-4">
                            <input type="arrival-eta" class="form-control" id="statusArrivalEta" placeholder="ETA in hours" value="72"><br>
                        </div>
                        <div class="col-md-1">&nbsp;</div>
                    </div>

                    <!-- Show this if status record exist -->
                    <div id="statusRecordButtons" style="display: none">
                        <div class="row">
                            <div class="col-md-1">&nbsp;</div>
                            <div class="col-md-3">&nbsp;</div>
                            <div class="col-md-3">
                                <button type="button" id="btnDownloadPOD" class="btn btn-primary">Download POD &nbsp; <span class="fa fa-download"></span></button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-light" id="btnPaperClip"><span class="fa fa-lg fa-paperclip"></span></button>
                                &nbsp;
                                <button type="button" class="btn btn-primary" id="btnUploadPOD">Upload POD &nbsp; <span class="fa fa-upload"></span></button>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="list-inline-item btn btn-primary pull-right" id="saveTrailerStatusExisting">Update</li>
                            </div>
                        </div>
                    </div>

                    <!-- Show this if NO status record exist -->
                    <div id="noStatusRecordsExist" style="display: none">
                        <div class="row">
                            <div class="col-md-1">&nbsp;</div>
                            <div class="col-md-3">&nbsp;</div>
                            <div class="col-md-3">
                                <button type="button" id="btnDownloadPODNotExisting" class="btn btn-primary">Download POD &nbsp; <span class="fa fa-download"></span></button>
                            </div>
                            <div class="col-md-3">

                            </div>
                            <div class="col-md-1">
                                <button type="button" class="list-inline-item btn btn-primary pull-right" id="saveTrailerStatusNotExisting">Save Changes</li>
                            </div>
                        </div>
                    </div>


                    <hr>
                    <div class="widget border-radius-5 border-light-blue">
                        <label for="statusAddANote" class="text-blue">Add a Note</label>
                        <textarea class="form-control" id="statusAddANote" rows="3"></textarea><br>
                        <label for="blnShowCustomer" class="text-blue"><input type="checkbox" id="blnShowCustomer" value="true">Share with Customer</label><br>
                        <button type="button" id="addNote" class="btn btn-primary">Add Note</button>
                    </div>
                </div>
            </div>

            <?php
                }
                else if($_SESSION['entitytype'] == 2){
                    ?>
                    <!-- start right column content -->
                    <div class="col-md-9">
                        <div class="carrier-summary__top-container">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 id="displayVinNumber" style="display: none">1JJV532D0JL041440</h4>
                                    <h4 id="displayUnitNumber">1234567890</h4>
                                    <ul class="list-inline">
                                        <li class="list-inline-item">Notes from prior Carrier:</li>
                                        <li class="list-inline-item" id="statusNotes">N/A</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="carrier-summary__bottom-container">
                            <input type="hidden" id="statusID" value="" />
                            <input type="hidden" id="statusOrderID" value="" />
                            <input type="hidden" id="statusOrderDetailID" value="" />
                            <input type="hidden" id="statusCarrierID" value="" />
                            <input type="hidden" id="statusDocumentID" value="" />
                            <input type="hidden" id="statusFileName" value="" />
                            <input type="hidden" id="statusVinNumber" value="" />
                            <input type="hidden" id="statusUnitNumber" value="" />
                            <div class="row">
                                <div class="col-md-1">
                                    <div class="carrier-logo carrier-logo__buds"></div>
                                </div>
                                <div class="col-md-3" id="statusCarrierName">Buds Enterprise</div>
                                <div class="col-md-3">
                                    <div class="text-blue">Update Trailer Status:</div>
                                </div>
                                <div class="col-md-4">
                                    <select class="custom-select bkg-qty-yellow" id="statusTrailerStatus">
                                        <option selected>In Transit</option>
                                        <option>In Carrier Yard</option>
                                        <option>At Shipper To Be Loaded</option>
                                        <option>Trailer Loaded In Route</option>
                                        <option>At Consignee To Be Unloaded</option>
                                        <option>Trailer Delivered</option>
                                    </select>
                                </div>
                                <div class="col-md-1">&nbsp;</div>
                            </div>
                            <div class="row">
                                <div class="col-md-1">&nbsp;</div>
                                <div class="col-md-3">&nbsp;</div>
                                <div class="col-md-3">
                                    <div class="text-blue">Update Current Location:</div>
                                </div>
                                <div class="col-md-4">
                                    <input type="current-location" class="form-control" id="statusCurrentLocation" placeholder="City, State" value="Augusta GA"><br>
                                </div>
                                <div class="col-md-1">&nbsp;</div>
                            </div>
                            <div class="row">
                                <div class="col-md-1">&nbsp;</div>
                                <div class="col-md-3">&nbsp;</div>
                                <div class="col-md-3">
                                    <div class="text-blue">Loading Status:</div>
                                </div>
                                <div class="col-md-4">
                                    <input type="loading-status" class="form-control" id="statusLoadingStatus" placeholder="Loaded or Unloaded?" value="Loaded"><br>
                                </div>
                                <div class="col-md-1">&nbsp;</div>
                            </div>
                            <div class="row">
                                <div class="col-md-1">&nbsp;</div>
                                <div class="col-md-3">&nbsp;</div>
                                <div class="col-md-3">
                                    <div class="text-blue">Arrival ETA</div>
                                </div>
                                <div class="col-md-4">
                                    <input type="arrival-eta" class="form-control" id="statusArrivalEta" placeholder="ETA in hours" value="72"><br>
                                </div>
                                <div class="col-md-1">&nbsp;</div>
                            </div>

                            <!-- Show this if status record exist -->
                            <div id="statusRecordButtons" style="display: none">
                                <div class="row">
                                    <div class="col-md-1">&nbsp;</div>
                                    <div class="col-md-3">&nbsp;</div>
                                    <div class="col-md-3">
                                        <button type="button" id="btnDownloadPOD" class="btn btn-primary">Download POD &nbsp; <span class="fa fa-download"></span></button>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-outline-light" id="btnPaperClip"><span class="fa fa-lg fa-paperclip"></span></button>
                                        &nbsp;
                                        <button type="button" class="btn btn-primary" id="btnUploadPOD">Upload POD &nbsp; <span class="fa fa-upload"></span></button>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="list-inline-item btn btn-primary pull-right" id="saveTrailerStatusExisting">Update</li>
                                    </div>
                                </div>
                            </div>

                            <!-- Show this if NO status record exist -->
                            <div id="noStatusRecordsExist" style="display: none">
                                <div class="row">
                                    <div class="col-md-1">&nbsp;</div>
                                    <div class="col-md-3">&nbsp;</div>
                                    <div class="col-md-3">
                                        <button type="button" id="btnDownloadPODNotExisting" class="btn btn-primary">Download POD &nbsp; <span class="fa fa-download"></span></button>
                                    </div>
                                    <div class="col-md-3">

                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="list-inline-item btn btn-primary pull-right" id="saveTrailerStatusNotExisting">Save Changes</li>
                                    </div>
                                </div>
                            </div>


                            <hr>
                            <div class="widget border-radius-5 border-light-blue">
                                <label for="statusAddANote" class="text-blue">Add a Note</label>
                                <textarea class="form-control" id="statusAddANote" rows="3"></textarea><br>
                                <label for="blnShowCustomer" class="text-blue"><input type="checkbox" id="blnShowCustomer" value="true">Share with Customer</label><br>
                                <button type="button" id="addNote" class="btn btn-primary">Add Note</button>
                            </div>
                        </div>
                    </div>
            <?php
                }
            ?>

        </div>

        <div id="orderLedger" class="row" style="display: none;">

            <div id="dataTable-9000" class="col-md-12">
                <h5><span class="fw-semi-bold">Order Ledger</span></h5>

                <br>
                <br>
                <table id="order-ledger-table" class="table table-striped table-hover" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>User</th>
                        <th>Log Type ID</th>
                        <th>Ledger Description</th>
                        <th>Reference ID</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                 </table>
            </div>
        </div>
        <div id="adminNotes" class="row" style="display: none;">

            <div id="dataTable-9000" class="col-md-12">
                <h5><span class="fw-semi-bold">Admin Notes</span></h5>
                <div class="widget-controls">
                    <button type="button" class="btn btn-primary btn-md" onclick="" id="addAdminNote">Add Note</button>
                </div>
                <br>
                <br>
                <table id="admin-note-table" class="table table-striped table-hover" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>UserID</th>
                        <th>User</th>
                        <th>Note</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                 </table>
            </div>
        </div>

    </section>
</div>

 <div id="editOrderDetails" style="display: none">

    <ol class="breadcrumb">
        <li>ADMIN</li>
        <li>View Orders</li>
        <li>View Order Details</li>
        <li class="active">Edit Order Details</li>
    </ol>

     <section>

        <header>
                <h1 class="fw-semi-bold">Edit Order</h1>
        </header>
            <div class="row">
                    <div class="col-md-12">
                    <h2>Pickup Address</h2>
                    <form>
                        <input type="hidden" id="orderID" name="orderID" value="" />
                            <div class="form-group row">
                                    <label for="pickupLocation" class="col-sm-3 col-form-label">Location</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="pickupLocation" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="pickupContactPerson" class="col-sm-3 col-form-label">Contact Person</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="pickupContactPerson" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="pickupPhoneNumber" class="col-sm-3 col-form-label">Phone Number</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="pickupPhoneNumber" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="pickupHoursOfOperation" class="col-sm-3 col-form-label">Hours of Operation</label>
                                    <div class="col-sm-9">
                                            <!--input class="form-control" id="pickupHoursOfOperation" placeholder="" type="text"-->

                                            <div class="col-sm-3">
                                                    <select class="form-control" id="pickupHoursOfOperationOpen">
                                                        <option>00:30</option>
                                                        <option>01:00</option>
                                                        <option>01:30</option>
                                                        <option>02:00</option>
                                                        <option>02:30</option>
                                                        <option>03:00</option>
                                                        <option>03:30</option>
                                                        <option>04:00</option>
                                                        <option>04:30</option>
                                                        <option>05:00</option>
                                                        <option>05:30</option>
                                                        <option>06:00</option>
                                                        <option>06:30</option>
                                                        <option>07:00</option>
                                                        <option>07:30</option>
                                                        <option selected>08:00</option>
                                                        <option>08:30</option>
                                                        <option>09:00</option>
                                                        <option>09:30</option>
                                                        <option>10:00</option>
                                                        <option>10:30</option>
                                                        <option>11:00</option>
                                                        <option>11:30</option>
                                                        <option>12:00</option>
                                                        <option>12:30</option>
                                                        <option>13:00</option>
                                                        <option>13:30</option>
                                                        <option>14:00</option>
                                                        <option>14:30</option>
                                                        <option>15:00</option>
                                                        <option>15:30</option>
                                                        <option>16:00</option>
                                                        <option>16:30</option>
                                                        <option>17:00</option>
                                                        <option>17:30</option>
                                                        <option>18:00</option>
                                                        <option>18:30</option>
                                                        <option>19:00</option>
                                                        <option>19:30</option>
                                                        <option>20:00</option>
                                                        <option>20:30</option>
                                                        <option>21:00</option>
                                                        <option>21:30</option>
                                                        <option>22:00</option>
                                                        <option>22:30</option>
                                                        <option>23:00</option>
                                                        <option>23:30</option>
                                                        <option>24:00</option>
                                                    </select>
                                            </div>
                                            <div class="col-sm-3">
                                                    <select class="form-control" id="pickupHoursOfOperationClose">
                                                        <option>00:30</option>
                                                        <option>01:00</option>
                                                        <option>01:30</option>
                                                        <option>02:00</option>
                                                        <option>02:30</option>
                                                        <option>03:00</option>
                                                        <option>03:30</option>
                                                        <option>04:00</option>
                                                        <option>04:30</option>
                                                        <option>05:00</option>
                                                        <option>05:30</option>
                                                        <option>06:00</option>
                                                        <option>06:30</option>
                                                        <option>07:00</option>
                                                        <option>07:30</option>
                                                        <option>08:00</option>
                                                        <option>08:30</option>
                                                        <option>09:00</option>
                                                        <option>09:30</option>
                                                        <option>10:00</option>
                                                        <option>10:30</option>
                                                        <option>11:00</option>
                                                        <option>11:30</option>
                                                        <option>12:00</option>
                                                        <option>12:30</option>
                                                        <option>13:00</option>
                                                        <option>13:30</option>
                                                        <option>14:00</option>
                                                        <option>14:30</option>
                                                        <option>15:00</option>
                                                        <option>15:30</option>
                                                        <option>16:00</option>
                                                        <option>16:30</option>
                                                        <option selected>17:00</option>
                                                        <option>17:30</option>
                                                        <option>18:00</option>
                                                        <option>18:30</option>
                                                        <option>19:00</option>
                                                        <option>19:30</option>
                                                        <option>20:00</option>
                                                        <option>20:30</option>
                                                        <option>21:00</option>
                                                        <option>21:30</option>
                                                        <option>22:00</option>
                                                        <option>22:30</option>
                                                        <option>23:00</option>
                                                        <option>23:30</option>
                                                        <option>24:00</option>
                                                    </select>
                                            </div>
                                            <div class="col-sm-3">
                                                    <select class="form-control" id="pickupTimeZone">
                                                        <option>EST (Eastern)</option>
                                                        <option>CST (Central)</option>
                                                        <option selected>MPT (Mountain)</option>
                                                        <option>PST (Pacific)</option>
                                                    </select>
                                            </div>

                                    </div>
                            </div>

                            <div class="form-group row">
                                    <label for="originationAddress1" class="col-sm-3 col-form-label">Address 1</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="originationAddress1" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="originationAddress2" class="col-sm-3 col-form-label">Address 2</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="originationAddress2" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="originationCity" class="col-sm-3 col-form-label">City</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="originationCity" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="originationState" class="col-sm-3 col-form-label">State</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="originationState" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="originationZip" class="col-sm-3 col-form-label">Zip</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="originationZip" placeholder="" type="text">
                                    </div>
                            </div>
                    </form>
                    </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-12">
                    <h2>Delivery Address</h2>
                    <form>
                            <div class="form-group row">
                                    <label for="deliveryLocation" class="col-sm-3 col-form-label">Location</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="deliveryLocation" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="deliveryContactPerson" class="col-sm-3 col-form-label">Contact Person</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="deliveryContactPerson" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="deliveryPhoneNumber" class="col-sm-3 col-form-label">Phone Number</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="deliveryPhoneNumber" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="deliveryHoursOfOperation" class="col-sm-3 col-form-label">Hours of Operation</label>
                                    <div class="col-sm-9">
                                            <!--input class="form-control" id="deliveryHoursOfOperation" placeholder="" type="text"-->

                                            <div class="col-sm-3">
                                                    <select class="form-control" id="deliveryHoursOfOperationOpen">
                                                        <option>00:30</option>
                                                        <option>01:00</option>
                                                        <option>01:30</option>
                                                        <option>02:00</option>
                                                        <option>02:30</option>
                                                        <option>03:00</option>
                                                        <option>03:30</option>
                                                        <option>04:00</option>
                                                        <option>04:30</option>
                                                        <option>05:00</option>
                                                        <option>05:30</option>
                                                        <option>06:00</option>
                                                        <option>06:30</option>
                                                        <option>07:00</option>
                                                        <option>07:30</option>
                                                        <option selected>08:00</option>
                                                        <option>08:30</option>
                                                        <option>09:00</option>
                                                        <option>09:30</option>
                                                        <option>10:00</option>
                                                        <option>10:30</option>
                                                        <option>11:00</option>
                                                        <option>11:30</option>
                                                        <option>12:00</option>
                                                        <option>12:30</option>
                                                        <option>13:00</option>
                                                        <option>13:30</option>
                                                        <option>14:00</option>
                                                        <option>14:30</option>
                                                        <option>15:00</option>
                                                        <option>15:30</option>
                                                        <option>16:00</option>
                                                        <option>16:30</option>
                                                        <option>17:00</option>
                                                        <option>17:30</option>
                                                        <option>18:00</option>
                                                        <option>18:30</option>
                                                        <option>19:00</option>
                                                        <option>19:30</option>
                                                        <option>20:00</option>
                                                        <option>20:30</option>
                                                        <option>21:00</option>
                                                        <option>21:30</option>
                                                        <option>22:00</option>
                                                        <option>22:30</option>
                                                        <option>23:00</option>
                                                        <option>23:30</option>
                                                        <option>24:00</option>
                                                    </select>
                                            </div>
                                            <div class="col-sm-3">
                                                    <select class="form-control" id="deliveryHoursOfOperationClose">
                                                        <option>00:30</option>
                                                        <option>01:00</option>
                                                        <option>01:30</option>
                                                        <option>02:00</option>
                                                        <option>02:30</option>
                                                        <option>03:00</option>
                                                        <option>03:30</option>
                                                        <option>04:00</option>
                                                        <option>04:30</option>
                                                        <option>05:00</option>
                                                        <option>05:30</option>
                                                        <option>06:00</option>
                                                        <option>06:30</option>
                                                        <option>07:00</option>
                                                        <option>07:30</option>
                                                        <option>08:00</option>
                                                        <option>08:30</option>
                                                        <option>09:00</option>
                                                        <option>09:30</option>
                                                        <option>10:00</option>
                                                        <option>10:30</option>
                                                        <option>11:00</option>
                                                        <option>11:30</option>
                                                        <option>12:00</option>
                                                        <option>12:30</option>
                                                        <option>13:00</option>
                                                        <option>13:30</option>
                                                        <option>14:00</option>
                                                        <option>14:30</option>
                                                        <option>15:00</option>
                                                        <option>15:30</option>
                                                        <option>16:00</option>
                                                        <option>16:30</option>
                                                        <option selected>17:00</option>
                                                        <option>17:30</option>
                                                        <option>18:00</option>
                                                        <option>18:30</option>
                                                        <option>19:00</option>
                                                        <option>19:30</option>
                                                        <option>20:00</option>
                                                        <option>20:30</option>
                                                        <option>21:00</option>
                                                        <option>21:30</option>
                                                        <option>22:00</option>
                                                        <option>22:30</option>
                                                        <option>23:00</option>
                                                        <option>23:30</option>
                                                        <option>24:00</option>
                                                    </select>
                                            </div>
                                            <div class="col-sm-3">
                                                    <select class="form-control" id="deliveryTimeZone">
                                                        <option>EST (Eastern)</option>
                                                        <option>CST (Central)</option>
                                                        <option selected>MPT (Mountain)</option>
                                                        <option>PST (Pacific)</option>
                                                    </select>
                                            </div>

                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="destinationAddress1" class="col-sm-3 col-form-label">Address 1</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="destinationAddress1" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="destinationAddress2" class="col-sm-3 col-form-label">Address 2</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="destinationAddress2" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="destinationCity" class="col-sm-3 col-form-label">City</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="destinationCity" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="destinationState" class="col-sm-3 col-form-label">State</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="destinationState" placeholder="" type="text">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="destinationZip" class="col-sm-3 col-form-label">Zip</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="destinationZip" placeholder="" type="text">
                                    </div>
                            </div>
                    </form>
                </div>
            </div>

            <hr>

            <div class="row">
                    <div class="col-md-12">
                    <h2>Trailer Data</h2>
                    <form id="dp-check-list-box">

                    </form>
                    </div>
            </div>

            <hr>

            <div class="row">
                    <div class="col-md-12">
                            <h2>Relay Addresses</h2>
                    </div>
            </div>
            <br>
            <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <h4>Relay Address 1</h4>
                            <input class="form-control" id="relay_id1" placeholder="" type="hidden">
                            <input class="form-control" id="commit_id1" placeholder="" type="hidden">
                            <div class="form-group">
                                    <label for="deliveryLocation_relay1">Location</label>
                                    <input class="form-control" id="deliveryLocation_relay1" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="contactPerson_relay1">Contact Person</label>
                                    <input class="form-control" id="contactPerson_relay1" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="phoneNumber_relay1">Phone Number</label>
                                    <input class="form-control" id="phoneNumber_relay1" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="hoursOfOperationOpen_relay1">Opening Hour</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="hoursOfOperationOpen_relay1">
                                        <option>00:30</option>
                                        <option>01:00</option>
                                        <option>01:30</option>
                                        <option>02:00</option>
                                        <option>02:30</option>
                                        <option>03:00</option>
                                        <option>03:30</option>
                                        <option>04:00</option>
                                        <option>04:30</option>
                                        <option>05:00</option>
                                        <option>05:30</option>
                                        <option>06:00</option>
                                        <option>06:30</option>
                                        <option>07:00</option>
                                        <option>07:30</option>
                                        <option selected>08:00</option>
                                        <option>08:30</option>
                                        <option>09:00</option>
                                        <option>09:30</option>
                                        <option>10:00</option>
                                        <option>10:30</option>
                                        <option>11:00</option>
                                        <option>11:30</option>
                                        <option>12:00</option>
                                        <option>12:30</option>
                                        <option>13:00</option>
                                        <option>13:30</option>
                                        <option>14:00</option>
                                        <option>14:30</option>
                                        <option>15:00</option>
                                        <option>15:30</option>
                                        <option>16:00</option>
                                        <option>16:30</option>
                                        <option>17:00</option>
                                        <option>17:30</option>
                                        <option>18:00</option>
                                        <option>18:30</option>
                                        <option>19:00</option>
                                        <option>19:30</option>
                                        <option>20:00</option>
                                        <option>20:30</option>
                                        <option>21:00</option>
                                        <option>21:30</option>
                                        <option>22:00</option>
                                        <option>22:30</option>
                                        <option>23:00</option>
                                        <option>23:30</option>
                                        <option>24:00</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="hoursOfOperationClose_relay1">Closing Hour</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="hoursOfOperationClose_relay1">
                                        <option>00:30</option>
                                        <option>01:00</option>
                                        <option>01:30</option>
                                        <option>02:00</option>
                                        <option>02:30</option>
                                        <option>03:00</option>
                                        <option>03:30</option>
                                        <option>04:00</option>
                                        <option>04:30</option>
                                        <option>05:00</option>
                                        <option>05:30</option>
                                        <option>06:00</option>
                                        <option>06:30</option>
                                        <option>07:00</option>
                                        <option>07:30</option>
                                        <option>08:00</option>
                                        <option>08:30</option>
                                        <option>09:00</option>
                                        <option>09:30</option>
                                        <option>10:00</option>
                                        <option>10:30</option>
                                        <option>11:00</option>
                                        <option>11:30</option>
                                        <option>12:00</option>
                                        <option>12:30</option>
                                        <option>13:00</option>
                                        <option>13:30</option>
                                        <option>14:00</option>
                                        <option>14:30</option>
                                        <option>15:00</option>
                                        <option>15:30</option>
                                        <option>16:00</option>
                                        <option>16:30</option>
                                        <option selected>17:00</option>
                                        <option>17:30</option>
                                        <option>18:00</option>
                                        <option>18:30</option>
                                        <option>19:00</option>
                                        <option>19:30</option>
                                        <option>20:00</option>
                                        <option>20:30</option>
                                        <option>21:00</option>
                                        <option>21:30</option>
                                        <option>22:00</option>
                                        <option>22:30</option>
                                        <option>23:00</option>
                                        <option>23:30</option>
                                        <option>24:00</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="timeZone_relay1">Time Zone</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="timeZone_relay1">
                                        <option>EST (Eastern)</option>
                                        <option>CST (Central)</option>
                                        <option>MPT (Mountain)</option>
                                        <option>PST (Pacific)</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="pickupDate_relay1">Pickup Date</label>
                                    <input class="form-control" id="pickupDate_relay1" placeholder="" type="date">
                            </div>
                            <div class="form-group">
                                    <label for="deliveryDate_relay1">Delivery Date</label>
                                    <input class="form-control" id="deliveryDate_relay1" placeholder="" type="date">
                            </div>
                            <div class="form-group">
                                    <label for="address_relay1">Address</label>
                                    <input class="form-control" id="address_relay1" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="city_relay1">City</label>
                                    <input class="form-control" id="city_relay1" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="state_relay1">State</label>
                                    <input class="form-control" id="state_relay1" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="zip_relay1">Zip</label>
                                    <input class="form-control" id="zip_relay1" placeholder="" type="text">
                            </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <h4>Relay Address 2</h4>
                            <input class="form-control" id="relay_id2" placeholder="" type="hidden">
                            <input class="form-control" id="commit_id2" placeholder="" type="hidden">
                            <div class="form-group">
                                    <label for="deliveryLocation_relay2">Location</label>
                                    <input class="form-control" id="deliveryLocation_relay2" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="contactPerson_relay2">Contact Person</label>
                                    <input class="form-control" id="contactPerson_relay2" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="phoneNumber_relay2">Phone Number</label>
                                    <input class="form-control" id="phoneNumber_relay2" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="hoursOfOperationOpen_relay2">Opening Hour</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="hoursOfOperationOpen_relay2">
                                        <option>00:30</option>
                                        <option>01:00</option>
                                        <option>01:30</option>
                                        <option>02:00</option>
                                        <option>02:30</option>
                                        <option>03:00</option>
                                        <option>03:30</option>
                                        <option>04:00</option>
                                        <option>04:30</option>
                                        <option>05:00</option>
                                        <option>05:30</option>
                                        <option>06:00</option>
                                        <option>06:30</option>
                                        <option>07:00</option>
                                        <option>07:30</option>
                                        <option selected>08:00</option>
                                        <option>08:30</option>
                                        <option>09:00</option>
                                        <option>09:30</option>
                                        <option>10:00</option>
                                        <option>10:30</option>
                                        <option>11:00</option>
                                        <option>11:30</option>
                                        <option>12:00</option>
                                        <option>12:30</option>
                                        <option>13:00</option>
                                        <option>13:30</option>
                                        <option>14:00</option>
                                        <option>14:30</option>
                                        <option>15:00</option>
                                        <option>15:30</option>
                                        <option>16:00</option>
                                        <option>16:30</option>
                                        <option>17:00</option>
                                        <option>17:30</option>
                                        <option>18:00</option>
                                        <option>18:30</option>
                                        <option>19:00</option>
                                        <option>19:30</option>
                                        <option>20:00</option>
                                        <option>20:30</option>
                                        <option>21:00</option>
                                        <option>21:30</option>
                                        <option>22:00</option>
                                        <option>22:30</option>
                                        <option>23:00</option>
                                        <option>23:30</option>
                                        <option>24:00</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="hoursOfOperationClose_relay2">Closing Hour</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="hoursOfOperationClose_relay2">
                                        <option>00:30</option>
                                        <option>01:00</option>
                                        <option>01:30</option>
                                        <option>02:00</option>
                                        <option>02:30</option>
                                        <option>03:00</option>
                                        <option>03:30</option>
                                        <option>04:00</option>
                                        <option>04:30</option>
                                        <option>05:00</option>
                                        <option>05:30</option>
                                        <option>06:00</option>
                                        <option>06:30</option>
                                        <option>07:00</option>
                                        <option>07:30</option>
                                        <option>08:00</option>
                                        <option>08:30</option>
                                        <option>09:00</option>
                                        <option>09:30</option>
                                        <option>10:00</option>
                                        <option>10:30</option>
                                        <option>11:00</option>
                                        <option>11:30</option>
                                        <option>12:00</option>
                                        <option>12:30</option>
                                        <option>13:00</option>
                                        <option>13:30</option>
                                        <option>14:00</option>
                                        <option>14:30</option>
                                        <option>15:00</option>
                                        <option>15:30</option>
                                        <option>16:00</option>
                                        <option>16:30</option>
                                        <option selected>17:00</option>
                                        <option>17:30</option>
                                        <option>18:00</option>
                                        <option>18:30</option>
                                        <option>19:00</option>
                                        <option>19:30</option>
                                        <option>20:00</option>
                                        <option>20:30</option>
                                        <option>21:00</option>
                                        <option>21:30</option>
                                        <option>22:00</option>
                                        <option>22:30</option>
                                        <option>23:00</option>
                                        <option>23:30</option>
                                        <option>24:00</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="timeZone_relay2">Time Zone</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="timeZone_relay2">
                                        <option>EST (Eastern)</option>
                                        <option>CST (Central)</option>
                                        <option>MPT (Mountain)</option>
                                        <option>PST (Pacific)</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="pickupDate_relay2">Pickup Date</label>
                                    <input class="form-control" id="pickupDate_relay2" placeholder="" type="date">
                            </div>
                            <div class="form-group">
                                    <label for="deliveryDate_relay2">Delivery Date</label>
                                    <input class="form-control" id="deliveryDate_relay2" placeholder="" type="date">
                            </div>
                            <div class="form-group">
                                    <label for="address_relay2">Address</label>
                                    <input class="form-control" id="address_relay2" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="city_relay2">City</label>
                                    <input class="form-control" id="city_relay2" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="state_relay2">State</label>
                                    <input class="form-control" id="state_relay2" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="zip_relay2">Zip</label>
                                    <input class="form-control" id="zip_relay2" placeholder="" type="text">
                            </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <h4>Relay Address 3</h4>
                            <input class="form-control" id="relay_id3" placeholder="" type="hidden">
                            <input class="form-control" id="commit_id3" placeholder="" type="hidden">
                            <div class="form-group">
                                    <label for="deliveryLocation_relay3">Location</label>
                                    <input class="form-control" id="deliveryLocation_relay3" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="contactPerson_relay3">Contact Person</label>
                                    <input class="form-control" id="contactPerson_relay3" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="phoneNumber_relay3">Phone Number</label>
                                    <input class="form-control" id="phoneNumber_relay3" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="hoursOfOperationOpen_relay3">Opening Hour</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="hoursOfOperationOpen_relay3">
                                        <option>00:30</option>
                                        <option>01:00</option>
                                        <option>01:30</option>
                                        <option>02:00</option>
                                        <option>02:30</option>
                                        <option>03:00</option>
                                        <option>03:30</option>
                                        <option>04:00</option>
                                        <option>04:30</option>
                                        <option>05:00</option>
                                        <option>05:30</option>
                                        <option>06:00</option>
                                        <option>06:30</option>
                                        <option>07:00</option>
                                        <option>07:30</option>
                                        <option selected>08:00</option>
                                        <option>08:30</option>
                                        <option>09:00</option>
                                        <option>09:30</option>
                                        <option>10:00</option>
                                        <option>10:30</option>
                                        <option>11:00</option>
                                        <option>11:30</option>
                                        <option>12:00</option>
                                        <option>12:30</option>
                                        <option>13:00</option>
                                        <option>13:30</option>
                                        <option>14:00</option>
                                        <option>14:30</option>
                                        <option>15:00</option>
                                        <option>15:30</option>
                                        <option>16:00</option>
                                        <option>16:30</option>
                                        <option>17:00</option>
                                        <option>17:30</option>
                                        <option>18:00</option>
                                        <option>18:30</option>
                                        <option>19:00</option>
                                        <option>19:30</option>
                                        <option>20:00</option>
                                        <option>20:30</option>
                                        <option>21:00</option>
                                        <option>21:30</option>
                                        <option>22:00</option>
                                        <option>22:30</option>
                                        <option>23:00</option>
                                        <option>23:30</option>
                                        <option>24:00</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="hoursOfOperationClose_relay3">Closing Hour</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="hoursOfOperationClose_relay3">
                                        <option>00:30</option>
                                        <option>01:00</option>
                                        <option>01:30</option>
                                        <option>02:00</option>
                                        <option>02:30</option>
                                        <option>03:00</option>
                                        <option>03:30</option>
                                        <option>04:00</option>
                                        <option>04:30</option>
                                        <option>05:00</option>
                                        <option>05:30</option>
                                        <option>06:00</option>
                                        <option>06:30</option>
                                        <option>07:00</option>
                                        <option>07:30</option>
                                        <option>08:00</option>
                                        <option>08:30</option>
                                        <option>09:00</option>
                                        <option>09:30</option>
                                        <option>10:00</option>
                                        <option>10:30</option>
                                        <option>11:00</option>
                                        <option>11:30</option>
                                        <option>12:00</option>
                                        <option>12:30</option>
                                        <option>13:00</option>
                                        <option>13:30</option>
                                        <option>14:00</option>
                                        <option>14:30</option>
                                        <option>15:00</option>
                                        <option>15:30</option>
                                        <option>16:00</option>
                                        <option>16:30</option>
                                        <option selected>17:00</option>
                                        <option>17:30</option>
                                        <option>18:00</option>
                                        <option>18:30</option>
                                        <option>19:00</option>
                                        <option>19:30</option>
                                        <option>20:00</option>
                                        <option>20:30</option>
                                        <option>21:00</option>
                                        <option>21:30</option>
                                        <option>22:00</option>
                                        <option>22:30</option>
                                        <option>23:00</option>
                                        <option>23:30</option>
                                        <option>24:00</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="timeZone_relay3">Time Zone</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="timeZone_relay3">
                                        <option>EST (Eastern)</option>
                                        <option>CST (Central)</option>
                                        <option>MPT (Mountain)</option>
                                        <option>PST (Pacific)</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="pickupDate_relay3">Pickup Date</label>
                                    <input class="form-control" id="pickupDate_relay3" placeholder="" type="date">
                            </div>
                            <div class="form-group">
                                    <label for="deliveryDate_relay3">Delivery Date</label>
                                    <input class="form-control" id="deliveryDate_relay3" placeholder="" type="date">
                            </div>
                            <div class="form-group">
                                    <label for="address_relay3">Address</label>
                                    <input class="form-control" id="address_relay3" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="city_relay3">City</label>
                                    <input class="form-control" id="city_relay3" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="state_relay3">State</label>
                                    <input class="form-control" id="state_relay3" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="zip_relay3">Zip</label>
                                    <input class="form-control" id="zip_relay3" placeholder="" type="text">
                            </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <h4>Relay Address 4</h4>
                            <input class="form-control" id="relay_id4" placeholder="" type="hidden">
                            <input class="form-control" id="commit_id4" placeholder="" type="hidden">
                            <div class="form-group">
                                    <label for="deliveryLocation_relay4">Location</label>
                                    <input class="form-control" id="deliveryLocation_relay4" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="contactPerson_relay4">Contact Person</label>
                                    <input class="form-control" id="contactPerson_relay4" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="phoneNumber_relay4">Phone Number</label>
                                    <input class="form-control" id="phoneNumber_relay4" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="hoursOfOperationOpen_relay4">Opening Hour</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="hoursOfOperationOpen_relay4">
                                        <option>00:30</option>
                                        <option>01:00</option>
                                        <option>01:30</option>
                                        <option>02:00</option>
                                        <option>02:30</option>
                                        <option>03:00</option>
                                        <option>03:30</option>
                                        <option>04:00</option>
                                        <option>04:30</option>
                                        <option>05:00</option>
                                        <option>05:30</option>
                                        <option>06:00</option>
                                        <option>06:30</option>
                                        <option>07:00</option>
                                        <option>07:30</option>
                                        <option selected>08:00</option>
                                        <option>08:30</option>
                                        <option>09:00</option>
                                        <option>09:30</option>
                                        <option>10:00</option>
                                        <option>10:30</option>
                                        <option>11:00</option>
                                        <option>11:30</option>
                                        <option>12:00</option>
                                        <option>12:30</option>
                                        <option>13:00</option>
                                        <option>13:30</option>
                                        <option>14:00</option>
                                        <option>14:30</option>
                                        <option>15:00</option>
                                        <option>15:30</option>
                                        <option>16:00</option>
                                        <option>16:30</option>
                                        <option>17:00</option>
                                        <option>17:30</option>
                                        <option>18:00</option>
                                        <option>18:30</option>
                                        <option>19:00</option>
                                        <option>19:30</option>
                                        <option>20:00</option>
                                        <option>20:30</option>
                                        <option>21:00</option>
                                        <option>21:30</option>
                                        <option>22:00</option>
                                        <option>22:30</option>
                                        <option>23:00</option>
                                        <option>23:30</option>
                                        <option>24:00</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="hoursOfOperationClose_relay4">Closing Hour</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="hoursOfOperationClose_relay4">
                                        <option>00:30</option>
                                        <option>01:00</option>
                                        <option>01:30</option>
                                        <option>02:00</option>
                                        <option>02:30</option>
                                        <option>03:00</option>
                                        <option>03:30</option>
                                        <option>04:00</option>
                                        <option>04:30</option>
                                        <option>05:00</option>
                                        <option>05:30</option>
                                        <option>06:00</option>
                                        <option>06:30</option>
                                        <option>07:00</option>
                                        <option>07:30</option>
                                        <option>08:00</option>
                                        <option>08:30</option>
                                        <option>09:00</option>
                                        <option>09:30</option>
                                        <option>10:00</option>
                                        <option>10:30</option>
                                        <option>11:00</option>
                                        <option>11:30</option>
                                        <option>12:00</option>
                                        <option>12:30</option>
                                        <option>13:00</option>
                                        <option>13:30</option>
                                        <option>14:00</option>
                                        <option>14:30</option>
                                        <option>15:00</option>
                                        <option>15:30</option>
                                        <option>16:00</option>
                                        <option>16:30</option>
                                        <option selected>17:00</option>
                                        <option>17:30</option>
                                        <option>18:00</option>
                                        <option>18:30</option>
                                        <option>19:00</option>
                                        <option>19:30</option>
                                        <option>20:00</option>
                                        <option>20:30</option>
                                        <option>21:00</option>
                                        <option>21:30</option>
                                        <option>22:00</option>
                                        <option>22:30</option>
                                        <option>23:00</option>
                                        <option>23:30</option>
                                        <option>24:00</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="timeZone_relay4">Time Zone</label>
                                    <!--input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text"-->
                                    <select class="form-control" id="timeZone_relay4">
                                        <option>EST (Eastern)</option>
                                        <option>CST (Central)</option>
                                        <option>MPT (Mountain)</option>
                                        <option>PST (Pacific)</option>
                                    </select>
                            </div>
                            <div class="form-group">
                                    <label for="pickupDate_relay4">Pickup Date</label>
                                    <input class="form-control" id="pickupDate_relay4" placeholder="" type="date">
                            </div>
                            <div class="form-group">
                                    <label for="deliveryDate_relay4">Delivery Date</label>
                                    <input class="form-control" id="deliveryDate_relay4" placeholder="" type="date">
                            </div>
                            <div class="form-group">
                                    <label for="address_relay4">Address</label>
                                    <input class="form-control" id="address_relay4" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="city_relay4">City</label>
                                    <input class="form-control" id="city_relay4" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="state_relay4">State</label>
                                    <input class="form-control" id="state_relay4" placeholder="" type="text">
                            </div>
                            <div class="form-group">
                                    <label for="zip_relay4">Zip</label>
                                    <input class="form-control" id="zip_relay4" placeholder="" type="text">
                            </div>
                    </div>
            </div>

            <hr>

            <div class="row">
                    <div class="col-md-12">
                            <h2>Unit Data</h2>
                            <tbody id="unitDataBody">

                            </tbody>
                    </div>
            </div>

            <br>

            <div class="row">
                <div id="addTrailer" class="col-md-12">

                </div>
            </div>

            <br>

        <div class="row row-grid">
                <div class="col-lg-3 col-md-5 col-sm-12">
                        <a class="btn btn-secondary btn-block" href="#" role="button" onclick="closeEditOrder();">Cancel</a>
                </div>
                <div class="col-lg-3 col-md-5 col-sm-12">
                    <a id="saveOrder" class="btn btn-primary btn-block" href="#" role="button">Save</a>
                </div>
        </div>

     </section>

 </div>

 <div id="order-details-old" style="display: none;">
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

                  switch($_SESSION['entitytype']) {
                      case 1:
                          ?>
        <div class="widget-body">
                 <div class="row">
                     <div class="col-sm-2">
                        <h5><span class="fw-semi-bold">Customer Name:</span></h5>
                     </div>
                     <div class="col-sm-10">
                        <h5><span id="customerName"></span></h5>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-2">
                        <h5><span class="fw-semi-bold">Order ID:</span></h5>
                     </div>
                     <div class="col-sm-10">
                        <h5><span id="orderNumber"></span></h5>
                     </div>
                 </div>
                 <div class="container">
                     <div class="row" style="padding:20px 0px;">
                       <div class="col-xs-6">
                            <h5 class="text-center"><strong>Origination Address</strong></h5>
                        </div>
                        <div class="col-xs-6">
                             <h5 class="text-center"><strong>Destination Address</strong></h5>
                         </div>
                     </div>
                     <div class="row" style="padding:15px 15px 5px 15px;background-color: #f3f3f3;">
                         <div class="col-xs-6">
                         	<div class="row"><label for="customer_pickupContactPerson">Contact</label></div>
                         	<div class="row"><span id="customer_pickupContactPerson"></span></div>
                         </div>
                         <div class="col-xs-6">
                         	<div class="row"><label for="customer_deliveryContactPerson">Contact</label></div>
                         	<div class="row"><span id="customer_deliveryContactPerson"></span></div>
                         </div>
                     </div>
                     <div class="row" style="padding:5px 15px;background-color:#f3f3f3;">
                        <div class="col-xs-6">
                         	<div class="row"><label for="customer_pickupLocation">Lot Name (Yard)</label></div>
                         	<div class="row"><span id="customer_pickupLocation"></span></div>
                         </div>
                         <div class="col-xs-6">
                         	<div class="row"><label for="customer_deliveryLocation">Lot Name (Yard)</label></div>
                         	<div class="row"><span id="customer_deliveryLocation"></span></div>
                         </div>
                      </div>
                     <div class="row" style="padding:5px 15px;background-color:#f3f3f3;">
                        <div class="col-xs-6">
                         	<div class="row"><label for="customer_originationAddress">Address</label></div>
                         	<div class="row">
                         		<span id="customer_originationAddress"></span>
                         		<span id="customer_originationCity"></span>
                         		<span id="customer_originationState"></span>
                         		<span id="customer_originationZip"></span>
                         	</div>
                         </div>
                         <div class="col-xs-6">
                         	<div class="row"><label for="customer_destinationAddress">Address</label></div>
                         	<div class="row">
                         		<span id="customer_destinationAddress"></span>
                         		<span id="customer_destinationCity"></span>
                         		<span id="customer_destinationState"></span>
                         		<span id="customer_destinationZip"></span>
                         	</div>
                         </div>
                     </div>
                     <div class="row" style="padding:5px 15px;background-color:#f3f3f3;">
                        <div class="col-xs-6">
                         	<div class="row"><label for="customer_pickupPhoneNumber">Phone Number</label></div>
                         	<div class="row"><span id="customer_pickupPhoneNumber"></span></div>
                         </div>
                         <div class="col-xs-6">
                         	<div class="row"><label for="customer_deliveryPhoneNumber">Phone Number</label></div>
                         	<div class="row"><span id="customer_deliveryPhoneNumber"></span></div>
                         </div>
                     </div>
                     <div class="row" style="padding:5px 15px 15px 15px;background-color:#f3f3f3;">
                        <div class="col-xs-6">
                         	<div class="row"><label for="customer_pickupHoursOfOperation">Hours of Operation</label></div>
                         	<div class="row"><span id="customer_pickupHoursOfOperation"></span></div>
                         </div>
                         <div class="col-xs-6">
                         	<div class="row"><label for="customer_deliveryHoursOfOperation">Hours of Operation</label></div>
                         	<div class="row"><span id="customer_deliveryHoursOfOperation"></span></div>
                         </div>
                     </div>
                 </div>

            <div id="dataTable-3" class="mt">
                <h5><span class="fw-semi-bold">VIN List</span></h5>
                <table id="customer-pod-list-table" class="table table-striped table-hover" width="100%">
                    <thead>
                    <tr>
                        <th>VIN Number</th>
                        <th>Date</th>
                        <th>Comments</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                 </table>
            </div>

          <div class="modal-footer">
          	<button id="closeOrderDetails" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          	<button id="editOrderDetails" type="button" class="btn btn-primary"><i class="glyphicon glyphicon-edit text"></i> <span class="text">Edit</span></button>
        	  </div>
        </div>
			<?php

                          break;
                      default:
         ?>
        <div class="widget-body">

            <div id="dataTable-1" class="mt">
                <table id="order-details-table" class="table table-striped table-hover" width="100%">
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
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
            <br>
            <div id="dataTable-2" class="mt">
                <h5><span class="fw-semi-bold">Order Tracking History</span></h5>
                <table id="order-history-table" class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Trailer VIN</th>
                        <th>Carrier</th>
                        <th>Date</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                    </thead>
                    <tbody>

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
                <table id="pod-list-table" class="table table-striped table-hover" width="100%">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Trailer VIN</th>
                        <th>Carrier</th>
                        <th>Delivery Date</th>
                        <th>Notes</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>

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
			<?php

                          break;
                  }

            ?>

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
                    <input type="hidden" id="id" name="id" />
                    <div class="row">
                        <div class="col-sm-3">
                            <label for="orderStatusVinNumber">VIN Number</label>
                          <div class="form-group">
                              <select id="orderStatusVinNumber" name="orderStatusVinNumber" data-placeholder="orderStatusVinNumber" class="form-control chzn-select" data-ui-jq="select2">

                                </select>
                            </div>
                        </div>
                    </div>
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

<!-- NEW Upload POD Modal -->
  <div class="modal fade" id="newUploadPOD" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
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
                  <input type="hidden" id="statusID" name="statusID" value="" />
                  <input type="hidden" id="orderID" name="orderID" value="" />
                  <input type="hidden" id="index" name="index" value="" />
                  <input type="hidden" id="customerID" name="customerID" value="" />
                  <div class="row">
                        <div class="col-sm-6" id="sectionPOD">
                            <label for="filePOD">Select POD File to Upload</label>
                            <div class="form-group">
                                <input type="hidden" id="fileName" name="fileName" value="" />
                                <input type="file" id="filePOD" name="filePOD" class="form-control-file mb-sm"/>
                            </div>
                        </div>
                  </div>
                  <!--<hr/>
                  <div class="row" id="replacePOD">
                      <div class="col-sm-12">
                        <label for="blnReplacePOD"><input type="checkbox" id="blnReplacePOD">Replace POD</label>
                      </div>
                  </div>-->
                </form>
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <!--button type="button" class="btn btn-primary btn-md" id="viewPOD" onclick="viewPOD();">View POD</button-->
           <button type="button" class="btn btn-primary btn-md" id="btnUpload">Upload</button>
        </div>
      </div>
    </div>
  </div>

<!-- NEW Upload PO Modal -->
  <div class="modal fade" id="newUploadPO" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Upload Purchase Order</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
                <form id="formUploadPO" class="register-form mt-lg">
                  <input type="hidden" id="statusID" name="statusID" value="" />
                  <input type="hidden" id="orderID" name="orderID" value="" />
                  <input type="hidden" id="index" name="index" value="" />
                  <input type="hidden" id="customerID" name="customerID" value="" />
                  <div class="row">
                        <div class="col-sm-6" id="sectionPO">
                            <label for="filePO">Select Purchase Order File to Upload</label>
                            <div class="form-group">
                                <input type="hidden" id="fileName" name="fileName" value="" />
                                <input type="file" id="filePO" name="filePO" class="form-control-file mb-sm"/>
                            </div>
                        </div>
                  </div>
                  <hr/>
                  <div class="row" id="replacePO">
                      <div class="col-sm-12">
                        <label for="blnReplacePO"><input type="checkbox" id="blnReplacePO">Replace POD</label>
                      </div>
                  </div>
                </form>
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <!--button type="button" class="btn btn-primary btn-md" id="viewPOD" onclick="viewPOD();">View POD</button-->
           <button type="button" class="btn btn-primary btn-md" id="btnPOUpload">Upload</button>
        </div>
      </div>
    </div>
  </div>

<!-- Original Upload POD Modal -->
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

 <!-- Edit Order Modal
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
                             <input type="text" id="pickupLocation" name="pickupLocation" class="form-control mb-sm" placeholder="Pickup Location" maxlength="20"/>
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="pickupContactPerson">Contact Person</label>
                         <div class="form-group">
                           <input type="text" id="pickupContactPerson" name="pickupContactPerson" class="form-control mb-sm" placeholder="Contact Person" maxlength="20" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="pickupPhoneNumber">Phone Number</label>
                         <div class="form-group">
                           <input type="text" id="pickupPhoneNumber" name="pickupPhoneNumber" class="form-control mb-sm" placeholder="Phone Number" maxlength="20" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="pickupHoursOfOperation">Hours of Operation</label>
                         <div class="form-group">
                           <input type="text" id="pickupHoursOfOperation" name="pickupHoursOfOperation" class="form-control mb-sm" placeholder="Hours of Operation" maxlength="20" />
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
                           <input type="text" id="originationAddress" name="originationAddress" class="form-control mb-sm" placeholder="Origin Address" maxlength="20" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="originationCity">Origination City</label>
                         <div class="form-group">
                           <input type="hidden" id="originationLocationID" name="originationLocationID" />
                           <input type="text" id="originationCity" name="originationCity" class="form-control mb-sm" placeholder="Origin City" required="required"  maxlength="20" />
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
                           <input type="text" id="originationZip" name="originationZip" class="form-control mb-sm" placeholder="Origin Zip" maxlength="12" />
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
                             <input type="text" id="deliveryLocation" name="deliveryLocation" class="form-control mb-sm" placeholder="Delivery Location" maxlength="20"/>
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="deliveryContactPerson">Contact Person</label>
                         <div class="form-group">
                           <input type="text" id="deliveryContactPerson" name="deliveryContactPerson" class="form-control mb-sm" placeholder="Contact Person" maxlength="20" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="deliveryPhoneNumber">Phone Number</label>
                         <div class="form-group">
                           <input type="text" id="deliveryPhoneNumber" name="deliveryPhoneNumber" class="form-control mb-sm" placeholder="Phone Number" maxlength="20" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="deliveryHoursOfOperation">Hours of Operation</label>
                         <div class="form-group">
                           <input type="text" id="deliveryHoursOfOperation" name="deliveryHoursOfOperation" class="form-control mb-sm" placeholder="Hours of Operation" maxlength="20" />
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
                         <input type="text" id="destinationAddress" name="destinationAddress" class="form-control mb-sm" placeholder="Destination Address" maxlength="20" />
                       </div>
                   </div>
                   <div class="col-sm-3">
                       <label for="DestinationCity">Destination City</label>
                       <div class="form-group">
                         <input type="text" id="destinationCity" name="destinationCity" class="form-control mb-sm" placeholder="Dest. City" required="required" maxlength="20" />
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
                         <input type="text" id="destinationZip" name="destinationZip" class="form-control mb-sm" placeholder="Dest. Zip" maxlength="12" />
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
                       <div class="col-xs-6" style="max-height: 90%; overflow: auto; display: inline-block;">
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
-->

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
                                 <input type="text" id="unitNumber" name="unitNumber" class="form-control" placeholder="Unit #" maxlength="20">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label for="truckProNumber">Truck-Pro #</label>
                            <div class="form-group">
                                 <input type="text" id="truckProNumber" name="truckProNumber" class="form-control" placeholder="Truck-Pro #" maxlength="20">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label for="year">Year</label>
                            <div class="form-group">
                                 <input type="text" id="year" name="year" class="form-control" placeholder="Year" maxlength="20">
                            </div>
                        </div>
                        <div class="col-sm-4">
                        <label for="trailerNotes">Notes</label>
                            <div class="form-group">
                                 <input type="text" id="trailerNotes" name="trailerNotes" class="form-control" placeholder="Notes" maxlength="20">
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

<!-- Approve POD -->
<div class="modal fade" id="approvePODModal" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Approve POD</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body"><form id="formUploadPOD" class="register-form mt-lg">
            <input id="approveOrderID" name="approveOrderID" type="hidden">
            <input id="approveOrderDetailID" name="approveOrderDetailID" type="hidden">
            <input id="approveCarrierID" name="approveCarrierID" type="hidden">
            <input id="approveCustomerID" name="approveCustomerID" type="hidden">
            <input id="approveStatusID" name="approveStatusID" type="hidden">
            <input id="approveDocumentID" name="approveDocumentID" type="hidden">
            <input id="approveVinNumber" name="approveVinNumber" type="hidden">
            <input id="approveUnitNumber" name="approveUnitNumber" type="hidden">
                  <div class="row">
                        <div class="col-sm-3">
                            <label for="approveCost">Customer will be invoiced:</label>
                        </div>
                        <div class="col-sm-9">
                            <input type="text" id="approveCost" name="approveCost" class="form-control mb-sm" placeholder="Cost" />
                        </div>
                  </div>
                </form>
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
           <button type="button" class="btn btn-primary"  id="btnApprovePOD">Ok</button>
        </div>
      </div>
    </div>
  </div>

<!-- Add Admin Note -->
<div class="modal fade" id="adminNoteModal" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Add Note</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <textarea class="form-control" id="txtAdminNote" rows="3"></textarea>
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
           <button type="button" class="btn btn-primary"  id="btnSaveAdminNote">Save</button>
        </div>
      </div>
    </div>
  </div>

<!-- Missing Trailer Data -->
<div class="modal fade" id="trailer-data-missing" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Missing Information</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            There is information missing. <br>
            Please be sure to enter <i>Unit Number</i> and <i>Year</i> inside the <strong>Edit Trailer Data</strong> Form. <br>
            Also make sure the following is entered under <strong>Edit</strong>.
            <ul>
                <li>Pickup Location</li>
                <li>Pickup Contact Person</li>
                <li>Pickup Phone Number</li>
                <li>Pickup Hours of Operation</li>
                <li>Delivery Location</li>
                <li>Delivery Contact Person</li>
                <li>Delivery Phone Number</li>
                <li>Delivery Hours of Operation</li>
            </ul>

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

        loadTableAJAX("Open");

        $('.datepicker').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "yyyy-mm-dd"
        });

        var table = $("#orders-table").DataTable();

        $("#order-details").css("display", "none");

        $("#ordersTabs li").off('click').on('click', function(){
            $("#ordersTabs li").removeAttr('id');
            $(this).attr('id', 'current');
        });

        $("#btnApprovePOD").off('click').on('click', function(){
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

            var customerID = $('#approveCustomerID').val();
            var cost = $('#approveCost').val();
            var orderID = $('#approveOrderID').val();
            var orderDetailID = $('#approveOrderDetailID').val();
            var carrierID = $('#approveCarrierID').val();
            var documentID = $('#approveDocumentID').val();
            var vinNumber = $('#approveVinNumber').val();
            var unitNumber = $('#approveUnitNumber').val();
            var statusID = $('#approveStatusID').val();

            var approvedPOD = {orderID: orderID, orderDetailID: orderDetailID, carrierID: carrierID, customerID: customerID, userID: userid, documentID: documentID,
                vinNumber: vinNumber, unitNumber: unitNumber, cost: cost, createdAt: today, updatedAt: today};

            $.ajax({
                url: '<?php echo API_HOST_URL . "/approved_pod/"; ?>',
                type: "POST",
                data: JSON.stringify(approvedPOD),
                contentType: "application/json",
                async: false,
                success: function(data){
                    if(data > 0){

                        var orderStatus = {hasBeenApproved: 1, updatedAt: today};
                        $.ajax({
                            url: '<?php echo API_HOST_URL . "/order_statuses/"; ?>'+statusID,
                            type: "PUT",
                            data: JSON.stringify(orderStatus),
                            contentType: "application/json",
                            async: false,
                            success: function(data){
                                if(data > 0){

                                    var activeCarrier = $("#activeCarrier").val();
                                    displayOrderStatuses(orderID, activeCarrier, vinNumber);

                                    var logParams = {logTypeName: "Orders", logMessage: "POD has been approved for VIN#: " + vinNumber, referenceID: orderID};

                                    // This is will enter into the log
                                    $.ajax({
                                        url: '<?php echo HTTP_HOST."/save_to_log" ?>',
                                         type: 'POST',
                                         data: JSON.stringify(logParams),
                                         contentType: "application/json",
                                         async: false,
                                         success: function(logResult){

                                             console.log(logResult);
                                         },
                                         error: function(error){

                                             $("#errorAlertTitle").html("Error");
                                             $("#errorAlertBody").html(error);
                                             $("#errorAlert").modal('show');
                                         }
                                    });

                                    $('#approveCustomerID').val("");
                                    $('#approveCost').val("");
                                    $('#approveOrderID').val("");
                                    $('#approveOrderDetailID').val("");
                                    $('#approveCarrierID').val("");
                                    $('#approveDocumentID').val("");
                                    $('#approveVinNumber').val("");
                                    $('#approveUnitNumber').val("");
                                    $('#approveStatusID').val("");
                                    $('#approvePODModal').modal('hide');
                                }
                            },
                            error: function(){
                                $('#approveCustomerID').val("");
                                $('#approveCost').val("");
                                $('#approveOrderID').val("");
                                $('#approveOrderDetailID').val("");
                                $('#approveCarrierID').val("");
                                $('#approveDocumentID').val("");
                                $('#approveVinNumber').val("");
                                $('#approveUnitNumber').val("");
                                $('#approveStatusID').val("");
                                $('#approvePODModal').modal('hide');

                                $("#errorAlertTitle").html("Error");
                                $("#errorAlertBody").html("Unable to change the order status");
                                $("#errorAlert").modal('show');
                            }

                        });

                    }
                },
                error: function(){
                    $('#approveCustomerID').val("");
                    $('#approveCost').val("");
                    $('#approveOrderID').val("");
                    $('#approveOrderDetailID').val("");
                    $('#approveCarrierID').val("");
                    $('#approveDocumentID').val("");
                    $('#approveVinNumber').val("");
                    $('#approveUnitNumber').val("");
                    $('#approveStatusID').val("");
                    $('#approvePODModal').modal('hide');

                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Unable to Approve POD");
                    $("#errorAlert").modal('show');
                }
            });

        });

        $('#addAdminNote').off('click').on('click', function(){
            $("#txtAdminNote").val("");
            $("#adminNoteModal").modal('show');
        });

        $('#btnSaveAdminNote').off('click').on('click', function(){

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

            var orderID = $('#orderID').val();

            var note = {orderID: orderID, userID: userid, note: $("#txtAdminNote").val(), createdAt: today, updatedAt: today};

            $.ajax({
                url: '<?php echo API_HOST_URL . "/order_notes/"; ?>',
                type: "POST",
                data: JSON.stringify(note),
                contentType: "application/json",
                async: false,
                success: function(){
                    loadOrderNotes(orderID);
                    $("#adminNoteModal").modal('hide');
                },
                error: function(error){
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Unable to Save Note.");
                    $("#errorAlert").modal('show');
                }
            });

        });

        $('#order-details-table tbody').off('click').on( 'click', 'button', function () {

            var table = $("#order-details-table").DataTable();
            var data = table.row( $(this).parents('tr') ).data();

            var orderID = data.orders[0].id;

            $("#id").val(orderID);

            var url = '<?php echo API_HOST_URL . '/orders/' ?>' + orderID;

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
                        //$("#pickupHoursOfOperation").val('');
                        $("#pickupHoursOfOperationOpen").val('');
                        $("#pickupHoursOfOperationClose").val('');
                        $("#pickupTimeZone").val('');
                    }
                    else{
                        $("#pickupLocation").val(data["pickupInformation"].pickupLocation);
                        $("#pickupContactPerson").val(data["pickupInformation"].contactPerson);
                        $("#pickupPhoneNumber").val(data["pickupInformation"].phoneNumber);
                        //$("#pickupHoursOfOperation").val(data["pickupInformation"].hoursOfOperation);
                        $("#pickupHoursOfOperationOpen").val(data["pickupInformation"].pickupHoursOfOperationOpen);
                        $("#pickupHoursOfOperationClose").val(data["pickupInformation"].pickupHoursOfOperationClose);
                        $("#pickupTimeZone").val(data["pickupInformation"].pickupTimeZone);
                    }

                    if(data["deliveryInformation"] == null){
                        $("#deliveryLocation").val('');
                        $("#deliveryContactPerson").val('');
                        $("#deliveryPhoneNumber").val('');
                        //$("#deliveryHoursOfOperation").val('');
                        $("#deliveryHoursOfOperationOpen").val('');
                        $("#deliveryHoursOfOperationClose").val('');
                        $("#deliveryTimeZone").val('');
                    }
                    else{
                        $("#deliveryLocation").val(data["deliveryInformation"].deliveryLocation);
                        $("#deliveryContactPerson").val(data["deliveryInformation"].contactPerson);
                        $("#deliveryPhoneNumber").val(data["deliveryInformation"].phoneNumber);
                        //$("#deliveryHoursOfOperation").val(data["deliveryInformation"].hoursOfOperation);
                        $("#deliveryHoursOfOperationOpen").val(data["deliveryInformation"].deliveryHoursOfOperationOpen);
                        $("#deliveryHoursOfOperationClose").val(data["deliveryInformation"].deliveryHoursOfOperationClose);
                        $("#deliveryTimeZone").val(data["deliveryInformation"].deliveryTimeZone);
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


                        if(dataPoints.object_type_data_points[i].title == "Decals"){
                            dpli += '<li>' +
                                    'Decals'+
                                    '<input id="decals" name="decals" class="form-control mb-sm" value="'+value+'" disabled>\n'+
                                    '</li>\n';
                          }
                          else{
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

                    }
                    $("#dp-check-list-box").html(dpli);
                    //formatListBox();
                    formatListBoxDP();
                    $("#entityID").prop('disabled', true);
                    $("#editOrder").modal('show');
                }
            });
        });

        $('#orders-table tbody').off('click').on( 'click', 'td.order-details-link', function () {
            var data = table.row( $(this).parents('tr') ).data();

            var orderID = data["id"];

            getOrderIDAndCustomerName(orderID);
            loadNewOrderDetailsAJAX(orderID);
            showOrderSummary();
            $("#ordersTabs li").removeAttr('id');
            $("#ordersTabs li").first().attr('id', 'current');
//            loadOrderDetailsAJAX(orderID);
//            loadOrderStatusesAJAX(orderID);
//            loadPODListAJAX(orderID);
//            loadOrderComments(orderID);

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
                if (dataPoints.object_type_data_points[i].title == "Decals") {
                    table += ' <br/> <strong>' + value + '</strong>';
                } else {
                    for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {
                        if (dataPoints.object_type_data_points[i].object_type_data_point_values[v].value === value) {
                            table += ' <br/> <strong>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</strong>';
                        }
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

            var ele = $(this);
            var podTable = $("#pod-list-table").DataTable();
            var pod = podTable.row( $(this).parents('tr') ).data();
            var podList = podTable.ajax.json().orders[0].podList;

            var orderDetailsTable = $("#order-details-table").DataTable();
            var orderDetails = orderDetailsTable.ajax.json();
            var orderID = orderDetails.order_details[0].orderID;

            var url = '<?php echo API_HOST_URL . '/orders/' ?>' + orderID;

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
/*
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
*/

                    var podDataJSON = {
                        podFormType: customerName,
                        unitNumber: pod.unitNumber, vinNumber: pod.vinNumber, trailerProNumber: pod.truckProNumber, year: pod.trailerYear,
                        size: size, type: data.needsDataPoints[5].type, door: data.needsDataPoints[1].door, decals: data.needsDataPoints[13].decals,
                        originationAddress: data.originationAddress, originationCity: data.originationCity, originationState: data.originationState, originationZipcode: data.originationZip,
                        destinationAddress: data.destinationAddress, destinationCity: data.destinationCity, destinationState: data.destinationState, destinationZipcode: data.destinationZip,
                        pickupLocation: data.pickupInformation.pickupLocation, pickupContact: data.pickupInformation.contactPerson,
                        pickupPhoneNumber: data.pickupInformation.phoneNumber, pickupHours: data.pickupInformation.pickupHoursOfOperationOpen + " to " + data.pickupInformation.pickupHoursOfOperationClose + " " + data.pickupInformation.pickupTimeZone,
                        deliveryLocation: data.deliveryInformation.deliveryLocation, deliveryContact: data.deliveryInformation.contactPerson,
                        deliveryPhoneNumber: data.deliveryInformation.phoneNumber, deliveryHours: data.deliveryInformation.deliveryHoursOfOperationOpen + " to " + data.deliveryInformation.deliveryHoursOfOperationClose + " " + data.deliveryInformation.deliveryTimeZone
                    };

                    var podURL = '<?php echo HTTP_HOST . '/pod_form_api'; ?>';

                    $.ajax({
                        url: podURL,
                        type: "POST",
                        contentType: "application/json",
                        responseType: "arraybuffer",
                        data: JSON.stringify(podDataJSON),
                        success: function(data){

                            var iframe = $('#download-pdf-container');
                            if (iframe.length == 0) {
                                iframe = $('<iframe id="download=pdf-container" style="visibility:hidden;"></iframe>').appendTo('body');
                            }
                            iframe.attr('src', '<?php echo HTTP_HOST; ?>/download-pdf/' + data);

                        },
                        error: function(data){
                            console.log(JSON.stringify(data));
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


            var url = '<?php echo API_HOST_URL; ?>';
            url += '/order_details?filter=orderID,eq,' + orderID + '&transform=1';

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
                            url: '<?php echo API_HOST_URL . "/orders/"; ?>' + orderID,
                            type: 'PUT',
                            data: JSON.stringify(orderData),
                            contentType: "application/json",
                            async: false,
                            success: function(){
                                $("#errorAlertTitle").html("Success");
                                $("#errorAlertBody").html("POD Successfully Uploaded");
                                $("#errorAlert").modal('show');

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
                                $("#errorAlertTitle").html("Error");
                                $("#errorAlertBody").html("Unable to save POD list to Orders");
                                $("#errorAlert").modal('show');
                            }
                        });

                    },
                    error: function(error){
                        $("#errorAlertTitle").html("Error");
                        $("#errorAlertBody").html("Unable to Upload POD File.");
                        $("#errorAlert").modal('show');
                    }
                });
            }
            else{
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("You must select a file to upload.");
                $("#errorAlert").modal('show');
            }
        }

        // This is the upload for Proof of Delivery (POD)
        function PODUpload(){

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
            formData.append('entityID', $("#statusCarrierID").val());
            formData.append('name', 'Identifier: ' + $("#statusUnitNumber").val() + ' - ' + $("#statusVinNumber").val());
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

                        var statusID = $('#statusID').val();

                        var statusData = {documentID: data};

                        $.ajax({
                            url: '<?php echo API_HOST_URL . "/order_statuses/"; ?>' + statusID,
                            type: 'PUT',
                            data: JSON.stringify(statusData),
                            contentType: "application/json",
                            async: false,
                            success: function(){
                                $("#errorAlertTitle").html("Success");
                                $("#errorAlertBody").html("POD Successfully Uploaded.");
                                $("#errorAlert").modal('show');

                                var logParams = {logTypeName: "Orders", logMessage: "POD has been uploaded.", referenceID: $('#orderID').val()};

                                // This is will enter into the log
                                $.ajax({
                                    url: '<?php echo HTTP_HOST."/save_to_log" ?>',
                                     type: 'POST',
                                     data: JSON.stringify(logParams),
                                     contentType: "application/json",
                                     async: false,
                                     success: function(logResult){

                                         console.log(logResult);
                                     },
                                     error: function(error){

                                         $("#errorAlertTitle").html("Error");
                                         $("#errorAlertBody").html(error);
                                         $("#errorAlert").modal('show');
                                     }
                                });

                                // Clear Form
                                $('#statusID').val('');
                                //$("#uploadPOD").modal('hide');
                                $("#newUploadPOD").modal('hide');

                            },
                            error: function(error){
                                $("#errorAlertTitle").html("Error");
                                $("#errorAlertBody").html("Unable to Save POD List to Orders.");
                                $("#errorAlert").modal('show');
                            }
                        });

                    },
                    error: function(error){
                        $("#errorAlertTitle").html("Error");
                        $("#errorAlertBody").html("Unable to Upload POD File.");
                        $("#errorAlert").modal('show');
                    }
                });
            }
            else{
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("You must select a file to upload.");
                $("#errorAlert").modal('show');
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

            var orderHistoryTable = $('#order-history-table').DataTable();
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

            var order_statuses = [];

            if(data.order_statuses != null) order_statuses = data.order_statuses;

            var pod = {vinNumber: $('#vinNumber').val(), notes: $('#podNotes').val(), deliveryDate: $('#deliveryDate').val(), fileName: fileName, carrier: carrier,
            unitNumber: unitNumber, truckProNumber: truckProNumber, trailerYear: trailerYear, trailerNotes: trailerNotes, order_statuses: order_statuses};

            podList.splice(index, 1, pod);

            var orderData = {podList: podList};

            $.ajax({
                url: '<?php echo API_HOST_URL . "/orders/"; ?>' + orderID,
                type: 'PUT',
                data: JSON.stringify(orderData),
                contentType: "application/json",
                async: false,
                success: function(){
                    $("#errorAlertTitle").html("Success");
                    $("#errorAlertBody").html("POD Info Successfully Saved.");
                    $("#errorAlert").modal('show');

                    var podListTable = $('#pod-list-table').DataTable();
                    orderHistoryTable.ajax.reload();
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
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Unable to Save POD List to Orders.");
                    $("#errorAlert").modal('show');
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

                $("#newUploadPOD").modal("show");

        });

        $("#btnUpload").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates

            // fileName will tell us if we're in Upload Mode or View/Edit Mode
            if($('#filePOD').val() != ""){
                // We are in Upload mode,
                // Lets upload POD
                //uploadPOD();
                PODUpload();
            }
            else{
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("You must select a file.");
                $("#errorAlert").modal('show');
            }
        });

        // We have to handle downloading POD's a little differently depending on if the status record exists or not - This is if it does exist
        $("#btnDownloadPOD").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates

            var orderID = $("#statusOrderID").val();

            var url = '<?php echo API_HOST_URL . '/orders/' ?>' + orderID;

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

                    var pod = data.podList[0];
/*
                    var podDataJSON = {
                        podFormType: customerName,
                        unitNumber: pod.unitNumber, vinNumber: pod.vinNumber, trailerProNumber: pod.truckProNumber, year: pod.trailerYear,
                        size: size, type: data.needsDataPoints[5].type, door: data.needsDataPoints[1].door, decals: data.needsDataPoints[14].decals,
                        originationAddress: data.originationAddress, originationCity: data.originationCity, originationState: data.originationState, originationZipcode: data.originationZip,
                        destinationAddress: data.destinationAddress, destinationCity: data.destinationCity, destinationState: data.destinationState, destinationZipcode: data.destinationZip,
                        pickupLocation: data.pickupInformation.pickupLocation, pickupContact: data.pickupInformation.contactPerson,
                        pickupPhoneNumber: data.pickupInformation.phoneNumber, pickupHours: data.pickupInformation.hoursOfOperation,
                        deliveryLocation: data.deliveryInformation.deliveryLocation, deliveryContact: data.deliveryInformation.contactPerson,
                        deliveryPhoneNumber: data.deliveryInformation.phoneNumber, deliveryHours: data.deliveryInformation.hoursOfOperation
                    };
*/

                    var podDataJSON = {
                        podFormType: customerName,
                        unitNumber: pod.unitNumber, vinNumber: pod.vinNumber, trailerProNumber: pod.truckProNumber, year: pod.trailerYear,
                        size: size, type: data.needsDataPoints[5].type, door: data.needsDataPoints[1].door, decals: data.needsDataPoints[14].decals,
                        originationAddress: data.originationAddress, originationCity: data.originationCity, originationState: data.originationState, originationZipcode: data.originationZip,
                        destinationAddress: data.destinationAddress, destinationCity: data.destinationCity, destinationState: data.destinationState, destinationZipcode: data.destinationZip,
                        pickupLocation: data.pickupInformation.pickupLocation, pickupContact: data.pickupInformation.contactPerson,
                        pickupPhoneNumber: data.pickupInformation.phoneNumber, pickupHours: data.pickupInformation.pickupHoursOfOperationOpen + " to " + data.pickupInformation.pickupHoursOfOperationClose + " " + data.pickupInformation.pickupTimeZone,
                        deliveryLocation: data.deliveryInformation.deliveryLocation, deliveryContact: data.deliveryInformation.contactPerson,
                        deliveryPhoneNumber: data.deliveryInformation.phoneNumber, deliveryHours: data.deliveryInformation.deliveryHoursOfOperationOpen + " to " + data.deliveryInformation.deliveryHoursOfOperationClose + " " + data.deliveryInformation.deliveryTimeZone
                    };

                    var podURL = '<?php echo HTTP_HOST . '/pod_form_api'; ?>';

                    $.ajax({
                        url: podURL,
                        type: "POST",
                        contentType: "application/json",
                        responseType: "arraybuffer",
                        data: JSON.stringify(podDataJSON),
                        success: function(data){

                            var iframe = $('#download-pdf-container');
                            if (iframe.length == 0) {
                                iframe = $('<iframe id="download=pdf-container" style="visibility:hidden;"></iframe>').appendTo('body');
                            }
                            iframe.attr('src', '<?php echo HTTP_HOST; ?>/download-pdf/' + data);

                        },
                        error: function(data){
                            console.log(JSON.stringify(data));
                        }
                    });
                },
                error: function(data){
                    console.log("Could not get Order Information.");
                }
            });

        });

        // We have to handle downloading POD's a little differently depending on if the status record exists or not - This is if it does NOT exist
        $("#btnDownloadPODNotExisting").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates

            var orderID = $("#orderID").val();

            var url = '<?php echo API_HOST_URL . '/orders/' ?>' + orderID;

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

                    var pod = data.podList[0];
/*
                    var podDataJSON = {
                        podFormType: customerName,
                        unitNumber: pod.unitNumber, vinNumber: pod.vinNumber, trailerProNumber: pod.truckProNumber, year: pod.trailerYear,
                        size: size, type: data.needsDataPoints[5].type, door: data.needsDataPoints[1].door, decals: data.needsDataPoints[14].decals,
                        originationAddress: data.originationAddress, originationCity: data.originationCity, originationState: data.originationState, originationZipcode: data.originationZip,
                        destinationAddress: data.destinationAddress, destinationCity: data.destinationCity, destinationState: data.destinationState, destinationZipcode: data.destinationZip,
                        pickupLocation: data.pickupInformation.pickupLocation, pickupContact: data.pickupInformation.contactPerson,
                        pickupPhoneNumber: data.pickupInformation.phoneNumber, pickupHours: data.pickupInformation.hoursOfOperation,
                        deliveryLocation: data.deliveryInformation.deliveryLocation, deliveryContact: data.deliveryInformation.contactPerson,
                        deliveryPhoneNumber: data.deliveryInformation.phoneNumber, deliveryHours: data.deliveryInformation.hoursOfOperation
                    };
*/

                    var podDataJSON = {
                        podFormType: customerName,
                        unitNumber: pod.unitNumber, vinNumber: pod.vinNumber, trailerProNumber: pod.truckProNumber, year: pod.trailerYear,
                        size: size, type: data.needsDataPoints[5].type, door: data.needsDataPoints[1].door, decals: data.needsDataPoints[14].decals,
                        originationAddress: data.originationAddress, originationCity: data.originationCity, originationState: data.originationState, originationZipcode: data.originationZip,
                        destinationAddress: data.destinationAddress, destinationCity: data.destinationCity, destinationState: data.destinationState, destinationZipcode: data.destinationZip,
                        pickupLocation: data.pickupInformation.pickupLocation, pickupContact: data.pickupInformation.contactPerson,
                        pickupPhoneNumber: data.pickupInformation.phoneNumber, pickupHours: data.pickupInformation.pickupHoursOfOperationOpen + " to " + data.pickupInformation.pickupHoursOfOperationClose + " " + data.pickupInformation.pickupTimeZone,
                        deliveryLocation: data.deliveryInformation.deliveryLocation, deliveryContact: data.deliveryInformation.contactPerson,
                        deliveryPhoneNumber: data.deliveryInformation.phoneNumber, deliveryHours: data.deliveryInformation.deliveryHoursOfOperationOpen + " to " + data.deliveryInformation.deliveryHoursOfOperationClose + " " + data.deliveryInformation.deliveryTimeZone
                    };

                    var podURL = '<?php echo HTTP_HOST . '/pod_form_api'; ?>';

                    $.ajax({
                        url: podURL,
                        type: "POST",
                        contentType: "application/json",
                        responseType: "arraybuffer",
                        data: JSON.stringify(podDataJSON),
                        success: function(data){

                            var iframe = $('#download-pdf-container');
                            if (iframe.length == 0) {
                                iframe = $('<iframe id="download=pdf-container" style="visibility:hidden;"></iframe>').appendTo('body');
                            }
                            iframe.attr('src', '<?php echo HTTP_HOST; ?>/download-pdf/' + data);

                        },
                        error: function(data){
                            console.log(JSON.stringify(data));
                            $("#errorAlertTitle").html("Error");
                            $("#errorAlertBody").html(JSON.stringify(data));
                            $("#errorAlert").modal('show');
                        }
                    });
                },
                error: function(data){
                    console.log("Could not get Order Information.");
                }
            });

        });

        // This is the upload function for the Purchase Order (PO)
        function POUpload(){

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

            var customerID = $('#customerID').val();
            var customer = "";

            allEntities.entities.forEach(function(entity){

                if(customerID == entity.id){

                    customer = entity.name;
                }
            });

            var formData = new FormData();
            var fileData = $('#filePO')[0].files[0];
            formData.append('entityID', customerID);
            formData.append('name', 'Purchase Order for Order #: ' + $("#orderID").val());
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

                        var orderID = $('#orderID').val();

                        var orderData = {documentID: data};

                        $.ajax({
                            url: '<?php echo API_HOST_URL . "/orders/"; ?>' + orderID,
                            type: 'PUT',
                            data: JSON.stringify(orderData),
                            contentType: "application/json",
                            async: false,
                            success: function(){

                                $("#errorAlertTitle").html("Success");
                                $("#errorAlertBody").html("Purchase Order Successfully Uploaded");
                                $("#errorAlert").modal('show');

                                var logParams = {logTypeName: "Orders", logMessage: "Purchase Order has been uploaded.", referenceID: $('#orderID').val()};

                                // This is will enter into the log
                                $.ajax({
                                    url: '<?php echo HTTP_HOST."/save_to_log" ?>',
                                     type: 'POST',
                                     data: JSON.stringify(logParams),
                                     contentType: "application/json",
                                     async: false,
                                     success: function(logResult){

                                         console.log(logResult);
                                     },
                                     error: function(error){

                                         $("#errorAlertTitle").html("Error");
                                         $("#errorAlertBody").html(error);
                                         $("#errorAlert").modal('show');
                                     }
                                });
                                // Clear Form
                                $('#orderID').val('');
                                $("#newUploadPO").modal('hide');

                            },
                            error: function(error){
                                $("#errorAlertTitle").html("Error");
                                $("#errorAlertBody").html("Unable to Save Purchase Order List to Orders.");
                                $("#errorAlert").modal('show');
                            }
                        });

                    },
                    error: function(error){
                        $("#errorAlertTitle").html("Error");
                        $("#errorAlertBody").html("Unable to Upload Purchase Order File.");
                        $("#errorAlert").modal('show');
                    }
                });
            }
            else{
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("You must select a file to upload.");
                $("#errorAlert").modal('show');
            }
        }

        $("#btnUploadPO").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates

                $("#newUploadPO").modal("show");

        });

        $("#btnPOUpload").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates

            // fileName will tell us if we're in Upload Mode or View/Edit Mode
            if($('#filePO').val() != ""){
                // We are in Upload mode,
                // Lets upload PO
                POUpload();
            }
            else{
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("You must select a file.");
                $("#errorAlert").modal('show');
            }
        });

        $("#activeCarrier").unbind('change').bind('change',function(){ // Doing it like this because it was double posting document giving me duplicates

            var vinNumber = $("#displayVinNumber").html();
            var unitNumber = $("#displayUnitNumber").html();

            var activeCarrier = $("#activeCarrier").val();
            var orderID = $("#orderID").val();

            $('#statusCarrierID').val(activeCarrier);

            displayOrderStatuses(orderID, activeCarrier, vinNumber);
        });

        // We have to handle saving Trailer data a little differently depending on if the status record exists or not - This is if it does exist
        $("#saveTrailerStatusExisting").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates

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

                var statusOrderID = $("#statusOrderID").val();
                var statusOrderDetailID = $("#statusOrderDetailID").val();
                var statusCarrierID = $("#statusCarrierID").val();
                var statusVinNumber = $("#statusVinNumber").val();
                var statusUnitNumber = $("#statusUnitNumber").val();
                var type = 'POST';
                var url = '';

                $.ajax({
                url: '<?php echo API_HOST_URL . "/order_statuses?filter[]=vinNumber,eq," ?>' + statusVinNumber + '&filter[]=carrierID,eq,' + statusCarrierID + '&transform=1',
                type: 'GET',
                contentType: "application/json",
                async: false,
                success: function(data){

                    var citystate = $("#statusCurrentLocation").val().split(',');

                    var params = {orderID: statusOrderID,
                                  orderDetailID: statusOrderDetailID,
                                  carrierID: statusCarrierID,
                                  vinNumber: statusVinNumber,
                                  unitNumber: statusUnitNumber,
                                  status: $("#statusTrailerStatus").val(),
                                  city: citystate[0],
                                  state: citystate[1],
                                  loadingStatus: $("#statusLoadingStatus").val(),
                                  arrivalEta: $("#statusArrivalEta").val()
                    };
/* We always create a new status record so we have a history of status changes
                    if (data.order_statuses.length > 0) {
                        type = 'PUT';
                        url = '<?php echo API_HOST_URL . "/order_statuses/" ?>' + data.order_statuses[0].id;
                        params.updatedAt = today;
                    } else {
                        type = 'POST';
                        url = '<?php echo API_HOST_URL . "/order_statuses" ?>';
                        params.createdAt = today;
                        params.updatedAt = today;
                    }
*/
                    type = 'POST';
                    url = '<?php echo API_HOST_URL . "/order_statuses" ?>';
                    params.createdAt = today;
                    params.updatedAt = today;

                    $.ajax({
                        url: url,
                        type: type,
                        data: JSON.stringify(params),
                        contentType: "application/json",
                        async: false,
                        success: function(data){

                            var logParams = {logTypeName: "Orders", logMessage: "Trailer status has been updated.", referenceID: $('#orderID').val()};

                            // This is will enter into the log
                            $.ajax({
                                url: '<?php echo HTTP_HOST."/save_to_log" ?>',
                                 type: 'POST',
                                 data: JSON.stringify(logParams),
                                 contentType: "application/json",
                                 async: false,
                                 success: function(logResult){

                                     console.log(logResult);
                                 },
                                 error: function(error){

                                     $("#errorAlertTitle").html("Error");
                                     $("#errorAlertBody").html(error);
                                     $("#errorAlert").modal('show');
                                 }
                            });

                            $("#statusID").val(data);
                            $("#statusAddANote").val('');
                            $("#noStatusRecordsExist").css("display", "none");
                            $("#statusRecordButtons").css("display", "block");
                            $("#errorAlertTitle").html("Success");
                            $("#errorAlertBody").html("Order Trailer Status Saved!");
                            $("#errorAlert").modal('show');
                            displayOrderStatuses(statusOrderID, statusCarrierID, statusVinNumber);
                        },
                        error: function(error){
                            $("#errorAlertTitle").html("Error");
                            $("#errorAlertBody").html("Unable to Save Order Status Data.");
                            $("#errorAlert").modal('show');
                        }
                    });


                },
                error: function(error){
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Unable to locate Order Status Data");
                    $("#errorAlert").modal('show');
                }
            });

        });

        // We have to handle saving Trailer data a little differently depending on if the status record exists or not - This is if it does NOT exist
        $("#saveTrailerStatusNotExisting").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates

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

                var statusOrderID = $("#statusOrderID").val();
                var statusOrderDetailID = $("#statusOrderDetailID").val();
                var statusCarrierID = $("#statusCarrierID").val();
                var statusVinNumber = $("#statusVinNumber").val();
                var statusUnitNumber = $("#statusUnitNumber").val();
                var type = 'POST';
                var url = '';

                $.ajax({
                url: '<?php echo API_HOST_URL . "/order_statuses?filter[]=vinNumber,eq," ?>' + statusVinNumber + '&filter[]=carrierID,eq,' + statusCarrierID + '&transform=1',
                type: 'GET',
                contentType: "application/json",
                async: false,
                success: function(data){

                    var citystate = $("#statusCurrentLocation").val().split(',');

                    var params = {orderID: statusOrderID,
                                  orderDetailID: statusOrderDetailID,
                                  carrierID: statusCarrierID,
                                  vinNumber: statusVinNumber,
                                  unitNumber: statusUnitNumber,
                                  status: $("#statusTrailerStatus").val(),
                                  city: citystate[0],
                                  state: citystate[1],
                                  loadingStatus: $("#statusLoadingStatus").val(),
                                  arrivalEta: $("#statusArrivalEta").val()
                    };
/* We always create a new status record so we have a history of status changes
                    if (data.order_statuses.length > 0) {
                        type = 'PUT';
                        url = '<?php echo API_HOST_URL . "/order_statuses/" ?>' + data.order_statuses[0].id;
                        params.updatedAt = today;
                    } else {
                        type = 'POST';
                        url = '<?php echo API_HOST_URL . "/order_statuses" ?>';
                        params.createdAt = today;
                        params.updatedAt = today;
                    }
*/
                    type = 'POST';
                    url = '<?php echo API_HOST_URL . "/order_statuses" ?>';
                    params.createdAt = today;
                    params.updatedAt = today;

                    $.ajax({
                        url: url,
                        type: type,
                        data: JSON.stringify(params),
                        contentType: "application/json",
                        async: false,
                        success: function(data){

                            var logParams = {logTypeName: "Orders", logMessage: "Trailer status has been added.", referenceID: $('#orderID').val()};

                            // This is will enter into the log
                            $.ajax({
                                url: '<?php echo HTTP_HOST."/save_to_log" ?>',
                                 type: 'POST',
                                 data: JSON.stringify(logParams),
                                 contentType: "application/json",
                                 async: false,
                                 success: function(logResult){

                                     console.log(logResult);
                                 },
                                 error: function(error){

                                     $("#errorAlertTitle").html("Error");
                                     $("#errorAlertBody").html(error);
                                     $("#errorAlert").modal('show');
                                 }
                            });

                            $("#statusID").val(data);
                            $("#statusAddANote").val('');
                            $("#noStatusRecordsExist").css("display", "none");
                            $("#statusRecordButtons").css("display", "block");
                            $("#errorAlertTitle").html("Success");
                            $("#errorAlertBody").html("Order Trailer Status Saved!");
                            $("#errorAlert").modal('show');
                            displayOrderStatuses(statusOrderID, statusCarrierID, statusVinNumber);
                        },
                        error: function(error){
                            $("#errorAlertTitle").html("Error");
                            $("#errorAlertBody").html("Unable to Save Order Status Data.");
                            $("#errorAlert").modal('show');
                        }
                    });


                },
                error: function(error){
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Unable to locate Order Status Data.");
                    $("#errorAlert").modal('show');
                }
            });

        });

        $("#addNote").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates

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

                var statusID = $("#statusID").val();
                var statusOrderID = $("#statusOrderID").val();
                var statusCarrierID = $("#statusCarrierID").val();
                var statusVinNumber = $("#statusVinNumber").val();
                var type = 'POST';
                var url = '';

                $.ajax({
                url: '<?php echo API_HOST_URL . "/order_statuses?filter[]=vinNumber,eq," ?>' + statusVinNumber + '&filter[]=carrierID,eq,' + statusCarrierID + '&filter[]=id,eq' + statusID + '&transform=1',
                type: 'GET',
                contentType: "application/json",
                async: false,
                success: function(data){

                    var citystate = $("#statusCurrentLocation").val().split(',');
                    var params = {showToCustomer: $("#blnShowCustomer").is(':checked'), note: $("#statusAddANote").val()};

                    if (data.order_statuses.length > 0) {
                        type = 'PUT';
                        url = '<?php echo API_HOST_URL . "/order_statuses/" ?>' + statusID;
                        params.updatedAt = today;
                    } else {
                        type = 'POST';
                        url = '<?php echo API_HOST_URL . "/order_statuses" ?>';
                        params.createdAt = today;
                        params.updatedAt = today;
                    }

                    $.ajax({
                        url: url,
                        type: type,
                        data: JSON.stringify(params),
                        contentType: "application/json",
                        async: false,
                        success: function(){

                            var logParams = {logTypeName: "Orders", logMessage: "Note has been added to the Trailer Status.", referenceID: statusOrderID};

                            // This is will enter into the log
                            $.ajax({
                                url: '<?php echo HTTP_HOST."/save_to_log" ?>',
                                 type: 'POST',
                                 data: JSON.stringify(logParams),
                                 contentType: "application/json",
                                 async: false,
                                 success: function(logResult){

                                     console.log(logResult);
                                 },
                                 error: function(error){

                                     $("#errorAlertTitle").html("Error");
                                     $("#errorAlertBody").html(error);
                                     $("#errorAlert").modal('show');
                                 }
                            });

                            displayOrderStatuses(statusOrderID, statusCarrierID, statusVinNumber);
                            $("#errorAlertTitle").html("Success");
                            $("#errorAlertBody").html("Trailer Note Saved!");
                            $("#errorAlert").modal('show');
                        },
                        error: function(error){
                            $("#errorAlertTitle").html("Error");
                            $("#errorAlertBody").html("Unable to Save Trailer Status Note Data.");
                            $("#errorAlert").modal('show');
                        }
                    });


                },
                error: function(error){
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Unable to locate Trailer Status Note.");
                    $("#errorAlert").modal('show');
                }
            });

        });

        $('#saveOrder').off('click').on('click', function(){


            var unitDataList = []; // This information is stored in podList in the orders table vs. unitData in the customer_needs table

            $('#addTrailer > div').each(function(index, value){
                var unitID = index + 1;
                var year = $('#year' + unitID).val().trim();
                var make = $('#make' + unitID).val().trim();
                var licenseNumber = $('#licenseNumber' + unitID).val().trim();
                var cashValue = $('#value' + unitID).val().trim();
                var unitNumber = $('#unitNumber' + unitID).val().trim();
                var vinNumber = $('#vinNumber' + unitID).val().trim();
                var truckProNumber = $('#truckProNumber' + unitID).val().trim();
                var poNumber = $('#poNumber' + unitID).val().trim();

                if(vinNumber != "" || unitNumber != "" || truckProNumber != "" || poNumber != ""){
                    var unitData = {unitNumber: unitNumber, vinNumber: vinNumber, truckProNumber: truckProNumber, poNumber: poNumber, year: year, make: make, licenseNumber: licenseNumber, value: cashValue};

                    unitDataList.push(unitData);
                }
            });

            if(unitDataList.length > 0){
              $("#saveCommit").html("<i class='fa fa-spinner fa-spin'></i> Updating Order");
              $("#saveCommit").prop("disabled", true);

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

                var id = $('#orderID').val();
/*
                var pickupInformation = {pickupLocation: $('#pickupLocation').val().trim(), contactPerson: $('#pickupContactPerson').val().trim(),
                                        phoneNumber: $('#pickupPhoneNumber').val().trim(), hoursOfOperation: $('#pickupHoursOfOperation').val().trim()};

                var deliveryInformation = {deliveryLocation: $('#deliveryLocation').val().trim(), contactPerson: $('#deliveryContactPerson').val().trim(),
                                        phoneNumber: $('#deliveryPhoneNumber').val().trim(), hoursOfOperation: $('#deliveryHoursOfOperation').val().trim()};
*/

                var pickupInformation = {pickupLocation: $('#pickupLocation').val().trim(), contactPerson: $('#pickupContactPerson').val().trim(),
                                        phoneNumber: $('#pickupPhoneNumber').val().trim(), pickupHoursOfOperationOpen: $('#pickupHoursOfOperationOpen').val().trim(),
                                        pickupHoursOfOperationClose: $('#pickupHoursOfOperationClose').val().trim(),pickupTimeZone: $('#pickupTimeZone').val().trim()};

                var deliveryInformation = {deliveryLocation: $('#deliveryLocation').val().trim(), contactPerson: $('#deliveryContactPerson').val().trim(),
                                        phoneNumber: $('#deliveryPhoneNumber').val().trim(), deliveryHoursOfOperationOpen: $('#deliveryHoursOfOperationOpen').val().trim(),
                                        deliveryHoursOfOperationClose: $('#deliveryHoursOfOperationClose').val().trim(),deliveryTimeZone: $('#deliveryTimeZone').val().trim()};

                var originationAddress1 = $('#originationAddress1').val().trim();
                var originationAddress2 = $('#originationAddress2').val().trim();
                var originationCity = $('#originationCity').val().trim();
                var originationState = $('#originationState').val().trim();
                var originationZip = $('#originationZip').val().trim();

                var destinationAddress1 = $('#destinationAddress1').val().trim();
                var destinationAddress2 = $('#destinationAddress2').val().trim();
                var destinationCity = $('#destinationCity').val().trim();
                var destinationState = $('#destinationState').val().trim();
                var destinationZip = $('#destinationZip').val().trim();

                var originationaddress = originationAddress1 + ', ' + originationCity + ', ' + originationState + ', ' + originationZip;
                var destinationaddress = destinationAddress1 + ', ' + destinationCity + ', ' + destinationState + ', ' + destinationZip;

                // getMapDirectionFromGoogle is defined in common.js
                newGetMapDirectionFromGoogle( originationaddress, destinationaddress, function(response) {

                    var originationlat = response.originationlat;
                    var originationlng = response.originationlng;
                    var destinationlat = response.destinationlat;
                    var destinationlng = response.destinationlng;
                    var distance = response.distance;

                    // Build the needsDataPoints
                    var needsarray = [];
                    var obj = $("#dp-check-list-box div div select");

                    for (var i = 0; i < obj.length; i++) {
                        var item = {};
                        item[obj[i].id] = obj[i].value;
                        needsarray.push(item);
                    }

                    var decal = {};
                    decal['decals'] = $("#decals").val().trim();
                    needsarray.push(decal);

                    var needsdatapoints = needsarray;

                    var qty = $("#qty").val().trim();

                    var rate = $("#rate").val().trim();
                    var rateType = $("#rateType").val().trim();
                    var transportationMode = $("#transportationMode").val().trim();

                    var data = {pickupInformation: pickupInformation, originationAddress: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                                deliveryInformation: deliveryInformation, destinationAddress: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip,
                                originationLng: originationlng, originationLat: originationlat, destinationLng: destinationlng, destinationLat: destinationlat, distance: distance,
                                qty: qty, updatedAt: today, needsDataPoints: needsdatapoints, podList: unitDataList, customerRate: rate, rateType: rateType, transportationMode: transportationMode};

                    var url = '<?php echo API_HOST_URL . "/orders" ?>/' + id;

                    $.ajax({
                        url: url,
                        type: 'PUT',
                        data: JSON.stringify(data),
                        contentType: "application/json",
                        async: false,
                        success: function(data){
                            if(data > 0){

                                var relayNumber = 0;
                                for(relayNumber = 1; relayNumber < 5; relayNumber++){
                                    var relayData = {};
                                    var url = "";
                                    var type = "";

                                    var relayID = $('#relay_id' + relayNumber).val().trim();
                                    var destinationAddress1 = $('#address_relay' + relayNumber).val().trim();
                                    var destinationCity = $('#city_relay' + relayNumber).val().trim();
                                    var destinationState = $('#state_relay' + relayNumber).val().trim();
                                    var destinationZip = $('#zip_relay' + relayNumber).val().trim();
/*
                                    var deliveryInformation = {deliveryLocation: $('#deliveryLocation_relay' + relayNumber).val().trim(), contactPerson: $('#contactPerson_relay' + relayNumber).val().trim(),
                                                            phoneNumber: $('#phoneNumber_relay' + relayNumber).val().trim(), hoursOfOperation: $('#hoursOfOperation_relay' + relayNumber).val().trim()};
*/

                                    var deliveryInformation = {deliveryLocation: $('#deliveryLocation_relay' + relayNumber).val().trim(), contactPerson: $('#contactPerson_relay' + relayNumber).val().trim(),
                                                            phoneNumber: $('#phoneNumber_relay' + relayNumber).val().trim(), deliveryHoursOfOperationOpen: $('#hoursOfOperationOpen_relay' + relayNumber).val().trim(),
                                                            deliveryHoursOfOperationClose: $('#hoursOfOperationClose_relay' + relayNumber).val().trim(),deliveryTimeZone: $('#timeZone_relay' + relayNumber).val().trim()};

                                    if(destinationCity != "" && destinationState != ""){

                                            if(relayID == ""){
                                                url = '<?php echo API_HOST_URL . "/order_details" ?>/';
                                                type = "POST";
                                                relayData = {orderID: id, pickupInformation: pickupInformation, originationAddress: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                                                    deliveryInformation: deliveryInformation, destinationAddress: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip,
//                                                    originationLng: originationlng, originationLat: originationlat, destinationLng: destinationlng, destinationLat: destinationlat, distance: distance,
                                                    qty: qty, createdAt: today, updatedAt: today, needsDataPoints: needsdatapoints, transportationMode: transportationMode};
                                            }
                                            else{
                                                url = '<?php echo API_HOST_URL . "/order_details" ?>/' + relayID;
                                                type = "PUT";
                                                relayData = {pickupInformation: pickupInformation,  originationAddress: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                                                    deliveryInformation: deliveryInformation, destinationAddress: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip,
//                                                    originationLng: originationlng, originationLat: originationlat, destinationLng: destinationlng, destinationLat: destinationlat, distance: distance,
                                                    qty: qty, updatedAt: today, needsDataPoints: needsdatapoints, transportationMode: transportationMode};
                                            }

                                            $.ajax({
                                                url: url,
                                                type: type,
                                                data: JSON.stringify(relayData),
                                                success: function(data){

                                                    originationAddress1 = destinationAddress1;
                                                    originationCity = destinationCity;
                                                    originationState = destinationState;
                                                    originationZip = destinationZip;
                                                    pickupInformation = deliveryInformation;
                                                },
                                                error: function(){
                                                    $("#errorAlertTitle").html("Error");
                                                    $("#errorAlertBody").html("Unable to save to order detail");
                                                    $("#errorAlert").modal('show');
                                                }
                                            });

                                    }
                                    else if(relayID != ""){

                                        url = '<?php echo API_HOST_URL . "/order_details" ?>/' + relayID;

                                        $.ajax({
                                            url: url,
                                            type: "DELETE",
                                            success: function(data){
                                                if(data > 0){
                                                    $("#errorAlertTitle").html("Message");
                                                    $("#errorAlertBody").html("Order Detail Closed");
                                                    $("#errorAlert").modal('show');
                                                }
                                            },
                                            error: function(){
                                                $("#errorAlertTitle").html("Error");
                                                $("#errorAlertBody").html("Unable to save to order detail");
                                                $("#errorAlert").modal('show');
                                            }
                                        });

                                    }

                                }

                                var logParams = {logTypeName: "Orders", logMessage: "Order has been updated.", referenceID: id};

                                // This is will enter into the log
                                $.ajax({
                                    url: '<?php echo HTTP_HOST."/save_to_log" ?>',
                                     type: 'POST',
                                     data: JSON.stringify(logParams),
                                     contentType: "application/json",
                                     async: false,
                                     success: function(logResult){

                                         console.log(logResult);
                                     },
                                     error: function(error){

                                         $("#errorAlertTitle").html("Error");
                                         $("#errorAlertBody").html(error);
                                         $("#errorAlert").modal('show');
                                     }
                                });

                                $("#saveCommit").html("Save");
                                $("#saveCommit").prop("disabled", false);
                                loadNewOrderDetailsAJAX(id);
                                closeEditOrder();

                                $("#errorAlertTitle").html("Success");
                                $("#errorAlertBody").html("Order Updated");
                                $("#errorAlert").modal('show');
                            }
                            else{
                                console.log(data);
                            }
                        },
                        error: function(data){
                            $("#errorAlertTitle").html("Error");
                            $("#errorAlertBody").html("There was an error updating the order");
                            $("#errorAlert").modal('show');
                        }
                    });

                });
            }
            else{
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("You must enter at least ONE Trailer.");
                $("#errorAlert").modal('show');
            }
        });

    });

    function switchRelaySelect(element){

        $(".carrier-row").removeClass('carrier-row__border-top');
        $(".carrier-row").removeClass('carrier-row__selected');
        $(".carrier-row").removeClass('carrier-row__border-bot');
        $(".carrier-row").removeClass('carrier-row__notselected');
        $(".carrier-row").addClass('carrier-row__border-bot');
        $(".carrier-row").addClass('carrier-row__notselected');

        $(element).removeClass('carrier-row__border-bot');
        $(element).removeClass('carrier-row__notselected');
        $(element).addClass('carrier-row__border-top');
        $(element).addClass('carrier-row__selected');
    }

    function displayRelay(element, orderDetailID){
        switchRelaySelect(element);
        var url = '<?php echo API_HOST_URL; ?>/order_details/' + orderDetailID;

        $.get(url, function(data){

            var currentCarrier = data.carrierID;
            var entityName = "";

            allEntities.entities.forEach(function(entity){

                if(currentCarrier == entity.id){

                    entityName += entity.name;
                }
            });


            if(data.pickupInformation == null){
                //data.pickupInformation = {pickupLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
                data.pickupInformation = {pickupLocation: "", contactPerson: "", phoneNumber: "", pickupHoursOfOperationOpen: "", pickupHoursOfOperationClose: "", pickupTimeZone: ""};
            }

            if(data.deliveryInformation == null){
                //data.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
                data.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", deliveryHoursOfOperationOpen: "", deliveryHoursOfOperationClose: "", deliveryTimeZone: ""};
            }

            //if(data.pickupInformation.hoursOfOperation == "") data.pickupInformation.hoursOfOperation = "N/A";
            if(data.pickupInformation.pickupHoursOfOperationOpen == "") data.pickupInformation.pickupHoursOfOperationOpen = "01:00";
            if(data.pickupInformation.pickupHoursOfOperationClose == "") data.pickupInformation.pickupHoursOfOperationClose = "01:00";
            if(data.pickupInformation.pickupTimeZone == "") data.pickupInformation.pickupTimeZone = "EST (Eastern)";

            //if(data.deliveryInformation.hoursOfOperation == "") data.deliveryInformation.hoursOfOperation = "N/A";
            if(data.deliveryInformation.deliveryHoursOfOperationOpen == "") data.deliveryInformation.deliveryHoursOfOperationOpen = "01:00";
            if(data.deliveryInformation.deliveryHoursOfOperationClose == "") data.deliveryInformation.deliveryHoursOfOperationClose = "01:00";
            if(data.deliveryInformation.deliveryTimeZone == "") data.deliveryInformation.deliveryTimeZone = "EST (Eastern)";

            $("#pickupName").val(data.pickupInformation.pickupLocation);
            $("#pickupAddress").val(data.originationAddress);
            $("#pickupCity").val(data.originationCity);
            $("#pickupState").val(data.originationState);
            $("#pickupZip").val(data.originationZip);
            $("#pickupPhone").val(data.pickupInformation.phoneNumber);
            $("#pickupContact").val(data.pickupInformation.contactPerson);
            //$("#pickupHours").val(data.pickupInformation.hoursOfOperation);
            $("#pickupHoursOfOperationOpen").val(data.pickupInformation.pickupHoursOfOperationOpen);
            $("#pickupHoursOfOperationClose").val(data.pickupInformation.pickupHoursOfOperationClose);
            $("#pickupTimeZone").val(data.pickupInformation.pickupTimeZone);
            $("#pickupDate").val(data.pickupDate);

            $("#deliveryName").val(data.deliveryInformation.deliveryLocation);
            $("#deliveryAddress").val(data.destinationAddress);
            $("#deliveryCity").val(data.destinationCity);
            $("#deliveryState").val(data.destinationState);
            $("#deliveryZip").val(data.destinationZip);
            $("#deliveryPhone").val(data.deliveryInformation.phoneNumber);
            $("#deliveryContact").val(data.deliveryInformation.contactPerson);
            //$("#deliveryHours").val(data.deliveryInformation.hoursOfOperation);
            $("#deliveryHoursOfOperationOpen").val(data.deliveryInformation.deliveryHoursOfOperationOpen);
            $("#deliveryHoursOfOperationClose").val(data.deliveryInformation.deliveryHoursOfOperationClose);
            $("#deliveryTimeZone").val(data.deliveryInformation.deliveryTimeZone);
            $("#deliveryDate").val(data.deliveryDate);

            $("#transportMode").val(data.transportationMode);
            $("#carrierRate").val(data.carrierRate);
            $("#carrierQty").val(data.qty);

            var carrierDistance = " <h5>" + entityName + "</h5> <small class=\"text-blue\">Distance: " + data.distance + " miles</small>";

            $("#carrierDistance").empty().html(carrierDistance);
            $("#orderDetailID").val(data.id);

            $.ajax({
                url: '<?php echo API_HOST_URL . "/locations"; ?>' + '?filter=entityID,eq,' + currentCarrier + '&transform=1',
                contentType: "application/json",
                success: function (json) {
                    var locations = json.locations;
                    var data = [];
                    $.each(locations, function(key, location){
                        var value = location.address1;
                        var label = location.address1 + ', ' + location.city + ', ' + location.state + ' ' + location.zip;
                        var id = location.id
                        var city = location.city;
                        var state = location.state;
                        var zip = location.zip;
                        var entry = {id: id, value: value, label: label, city: city, state: state, zip: zip};
                        data.push(entry);
                    });

                    $("#pickupAddress").autocomplete({
                        source: data,
                        minLength: 0,
                        select: function (event, ui) {
                            $("#pickupCity").val(ui.item.city);
                            $("#pickupState").val(ui.item.state);
                            $("#pickupZip").val(ui.item.zip);
                        }
                    });


                    $("#deliveryAddress").autocomplete({
                        source: data,
                        minLength: 0,
                        select: function (event, ui) {
                            $("#deliveryCity").val(ui.item.city);
                            $("#deliveryState").val(ui.item.state);
                            $("#deliveryZip").val(ui.item.zip);
                        }
                    });
                }
            });
        });

    }

    function switchTrailerSelect(element){

        $(".trailer-row").removeClass('trailer-row__border-top');
        $(".trailer-row").removeClass('trailer-row__selected');
        $(".trailer-row").removeClass('trailer-row__border-bot');
        $(".trailer-row").removeClass('trailer-row__notselected');
        $(".trailer-row").addClass('trailer-row__border-bot');
        $(".trailer-row").addClass('trailer-row__notselected');

        $(element).removeClass('trailer-row__border-bot');
        $(element).removeClass('trailer-row__notselected');
        $(element).addClass('trailer-row__border-top');
        $(element).addClass('trailer-row__selected');
    }

    function displayTrailer(element, vinNumber, carrierID, unitNumber){
        switchTrailerSelect(element);

        $("#displayVinNumber").html(vinNumber);
        $("#displayUnitNumber").html(unitNumber);
        $("#statusVinNumber").val(vinNumber);
        $("#statusUnitNumber").val(unitNumber);
        $("#statusID").val('');

        var activeCarrier = $("#activeCarrier").val();
        var orderID = $("#orderID").val();

        displayOrderStatuses(orderID, activeCarrier, vinNumber);
    }

    function saveCurrentOrderDetail(){

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

        today = yyyy+"-"+mm+"-"+dd+" "+hours+":"+min+":"+sec;

        var orderDetailID = $("#orderDetailID").val();

        var pickupName = $("#pickupName").val();
        var pickupAddress = $("#pickupAddress").val();
        var pickupCity = $("#pickupCity").val();
        var pickupState = $("#pickupState").val();
        var pickupZip = $("#pickupZip").val();
        var pickupPhone = $("#pickupPhone").val();
        var pickupContact = $("#pickupContact").val();
        //var pickupHours = $("#pickupHours").val();
        var pickupHoursOfOperationOpen = $("#pickupHoursOfOperationOpen").val();
        var pickupHoursOfOperationClose = $("#pickupHoursOfOperationClose").val();
        var pickupTimeZone = $("#pickupTimeZone").val();
        var pickupDate = $("#pickupDate").val();

        var deliveryName = $("#deliveryName").val();
        var deliveryAddress = $("#deliveryAddress").val();
        var deliveryCity = $("#deliveryCity").val();
        var deliveryState = $("#deliveryState").val();
        var deliveryZip = $("#deliveryZip").val();
        var deliveryPhone = $("#deliveryPhone").val();
        var deliveryContact = $("#deliveryContact").val();
        //var deliveryHours = $("#deliveryHours").val();
        var deliveryHoursOfOperationOpen = $("#deliveryHoursOfOperationOpen").val();
        var deliveryHoursOfOperationClose = $("#deliveryHoursOfOperationClose").val();
        var deliveryTimeZone = $("#deliveryTimeZone").val();
        var deliveryDate = $("#deliveryDate").val();

        var transportationMode = $("#transportMode").val();
        var carrierRate = $("#carrierRate").val();
        var carrierQty = $("#carrierQty").val();

        //var pickupInformation = {pickupLocation: pickupName, phoneNumber: pickupPhone, contactPerson: pickupContact, hoursOfOperation: pickupHours};
        var pickupInformation = {pickupLocation: pickupName, phoneNumber: pickupPhone, contactPerson: pickupContact, pickupHoursOfOperationOpen: pickupHoursOfOperationOpen,
                                pickupHoursOfOperationClose: pickupHoursOfOperationClose, pickupTimeZone: pickupTimeZone};

        //var deliveryInformation = {deliveryLocation: deliveryName, phoneNumber: deliveryPhone, contactPerson: deliveryContact, hoursOfOperation: deliveryHours};
        var deliveryInformation = {deliveryLocation: deliveryName, phoneNumber: deliveryPhone, contactPerson: deliveryContact, deliveryHoursOfOperationOpen: deliveryHoursOfOperationOpen,
                                  deliveryHoursOfOperationClose: deliveryHoursOfOperationClose, deliveryTimeZone: deliveryTimeZone};

        var originationaddress = pickupAddress + ', ' + pickupCity + ', ' + pickupState + ', ' + pickupZip;
        var destinationaddress = deliveryAddress + ', ' + deliveryCity + ', ' + deliveryState + ', ' + deliveryZip;

        // getMapDirectionFromGoogle is defined in common.js
        newGetMapDirectionFromGoogle( originationaddress, destinationaddress, function(response) {

            var originationlat = response.originationlat;
            var originationlng = response.originationlng;
            var destinationlat = response.destinationlat;
            var destinationlng = response.destinationlng;
            var distance = response.distance;

            var order_detail = {pickupInformation: pickupInformation, originationAddress: pickupAddress, originationCity: pickupCity, originationState: pickupState, originationZip: pickupZip,
                deliveryInformation: deliveryInformation, destinationAddress: deliveryAddress, destinationCity: deliveryCity, destinationState: deliveryState, destinationZip: deliveryZip,
                orginationLng: originationlng, originationLat: originationlat, destinationLng: destinationlng, destinationLat: destinationlat, distance: distance, transportationMode: transportationMode,
                qty: carrierQty, carrierRate: carrierRate, pickupDate: pickupDate, deliveryDate: deliveryDate, updatedAt: today};

            $.ajax({
                url: '<?php echo API_HOST_URL . "/order_details/"; ?>' + orderDetailID,
                type: 'PUT',
                data: JSON.stringify(order_detail),
                contentType: "application/json",
                async: false,
                success: function(){
                    $("#errorAlertTitle").html("Success");
                    $("#errorAlertBody").html("Order Detail Saved");
                    $("#errorAlert").modal('show');

                    var orderID = $("#orderID").val();
                    loadNewOrderDetailsAJAX(orderID);

                    var logParams = {logTypeName: "Orders", logMessage: "Order relay has been updated.", referenceID: orderID};

                    // This is will enter into the log
                    $.ajax({
                        url: '<?php echo HTTP_HOST."/save_to_log" ?>',
                         type: 'POST',
                         data: JSON.stringify(logParams),
                         contentType: "application/json",
                         async: false,
                         success: function(logResult){

                             console.log(logResult);
                         },
                         error: function(error){

                             $("#errorAlertTitle").html("Error");
                             $("#errorAlertBody").html(error);
                             $("#errorAlert").modal('show');
                         }
                    });

                },
                error: function(error){
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Unable to save Order Detail");
                    $("#errorAlert").modal('show');
                }
            });

        });
    }

 </script>
