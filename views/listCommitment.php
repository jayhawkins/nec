<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

$state = '';
$states = json_decode(file_get_contents(API_HOST_URL . '/states?columns=abbreviation,name&order=name'));

$entity = '';
$entity = json_decode(file_get_contents(API_HOST_URL . '/entities?columns=rateType,negotiatedRate&filter[]=id,eq,' . $_SESSION['entityid']));

$entities = '';
$entities = json_decode(file_get_contents(API_HOST_URL . '/entities?columns=id,name&order=name&filter[]=id,gt,0&filter[]=entityTypeID,eq,2'));

$allEntities = '';
$allEntities = json_decode(file_get_contents(API_HOST_URL . '/entities?columns=id,name&order=name&filter[]=id,gt,0&transform=1'));


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
$customer_needs_root = json_decode(file_get_contents(API_HOST_URL . "/customer_needs?columns=rootCustomerNeedsID&filter=rootCustomerNeedsID,neq,0&transform=1"));


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

    function verifyAddCarrierCommit(){
        var blnResult = true;   // Assume Good Data

        var strMessage = "The following fields must be entered:\n";

       if($('#pickupDate').val() == "") {
           strMessage += "-Pick-Up Date\n";
           blnResult = false;
       }
       if($('#deliveryDate').val() == "") {
           strMessage += "-Delivery Date\n";
           blnResult = false;
       }
       if($('#carrierID').val() == "") {
           strMessage += "-Carrier\n";
           blnResult = false;
       }
       if($('#originationCity').val() == "") {
           strMessage += "-Origination City\n";
           blnResult = false;
       }
       if($('#originationState').val() == "") {
           strMessage += "-Origination State\n";
           blnResult = false;
       }
       if($('#destinationCity').val() == "") {
           strMessage += "-Destination City\n";
           blnResult = false;
       }
       if($('#destinationState').val() == "") {
           strMessage += "-Destination State\n";
           blnResult = false;
       }

       if(blnResult == false){
           alert(strMessage);
       }

       return blnResult;
    }

      function post() {

          //var originationaddress = $("#originationAddress1").val() + ', ' + $("#originationCity").val() + ', ' + $("#originationState").val() + ', ' + $("#originationZip").val();
          //var destinationaddress = $("#destinationAddress1").val() + ', ' + $("#destinationCity").val() + ', ' + $("#destinationState").val() + ', ' + $("#destinationZip").val();
          var originationaddress = $("#originationCity").val() + ', ' + $("#originationState").val();
          var destinationaddress = $("#destinationCity").val() + ', ' + $("#destinationState").val();

/*
          if (originationaddress != $("#originToMatch").val() && destinationaddress != $("#destToMatch").val()) {
              alert("The commitment for this Available request must be picked up or dropped off at the listed Origination or Destination. Please select a new Origination or Destination address.");
              //alert($("#originToMatch").val());
              //alert($("#destToMatch").val());
              return false;
          }
*/
            if(verifyAddCarrierCommit() == true){
                var result = true;

                var params = {
                      address1: $("#originationAddress1").val(),
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
                      //alert("Origination " + response);
                      if (response == "success") {

                          var params = {
                                address1: $("#destinationAddress1").val(),
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

                                //alert("Destination " + response);
                                if (response == "success") {
                                } else {
                                    if (response == "ZERO_RESULTS") {
                                        alert("Destination Address does not exist!");
                                    } else {
                                        alert("Destination Address Error: " + JSON.stringify(response));
                                    }
                                    result = false;
                                    //alert('Preparation Failed!');
                                }
                             },
                             error: function(response) {
                                if (response == "ZERO_RESULTS") {
                                    alert("Destination Address does not exist!");
                                } else {
                                    alert("Destination Address Error: " + JSON.stringify(response));
                                }
                                result = false;
                                //alert('Failed Searching for Destination Location! - Notify NEC of this failure.');
                             }
                          });
                      } else {
                          if (response == "ZERO_RESULTS") {
                              alert("Origination Address does not exist!");
                          } else {
                              alert("Origination Address Error: " + JSON.stringify(response));
                          }
                          result = false;
                          //alert('Preparation Failed!');
                      }
                   },
                   error: function(response) {
                      alert("Issue With Origination Address: " + JSON.stringify(response));
                      result = false;
                      //alert('Failed Searching for Origination Location! - Notify NEC of this failure.');
                   }
                });

                if (result) {
                    verifyAndPost(function(data) {
                      //alert(data);
                      $("#load").html("Commit");
                      $("#load").prop("disabled", false);
                    });
                    return true;
                } else {
                    return false;
                }
            }

      }

    function getBillingAddress(entityID){

        var billingAddress = {};

        $.ajax({
            url: '<?php echo API_HOST_URL . "/locations" ?>?columns=address1,city,state,zip&transform=1&filter[]=locationTypeID,eq,1&filter[]=status,eq,Active&filter[]=entityID,eq,' + entityID,
            type: 'GET',
            contentType: "application/json",
            async: false,
            success: function(data){

                billingAddress = data.locations[0];

            },
            error: function() {
                alert("Unable to get billing Address");
            }
         });

         return billingAddress;
    }

      function verifyAndPost() {

                $("#load").html("<i class='fa fa-spinner fa-spin'></i> Committing Now");
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

                //var originationaddress = $("#originationAddress1").val() + ', ' + $("#originationCity").val() + ', ' + $("#originationState").val() + ', ' + $("#originationZip").val();
                //var destinationaddress = $("#destinationAddress1").val() + ', ' + $("#destinationCity").val() + ', ' + $("#destinationState").val() + ', ' + $("#destinationZip").val();
                var originationaddress = $("#originationCity").val() + ', ' + $("#originationState").val();
                var destinationaddress = $("#destinationCity").val() + ', ' + $("#destinationState").val();

                // getMapDirectionFromGoogle is defined in common.js
                newGetMapDirectionFromGoogle( originationaddress, destinationaddress, function(response) {

                              var originationlat = response.originationlat;
                              var originationlng = response.originationlng;
                              var destinationlat = response.destinationlat;
                              var destinationlng = response.destinationlng;
                              var distance = response.distance;

                              var newOriginationAddress1 = "";
                              var newOriginationCity = "";
                              var newOriginationState = "";
                              var newOriginationZip = "";
                              var newDestinationAddress1 = "";
                              var newDestinationCity = "";
                              var newDestinationState = "";
                              var newDestinationZip = "";
                              //var newOriginationLat = "";
                              //var newOriginationLng = "";
                              //var newDestinationLat = "";
                              //var newDestinationLng = "";


                              // If new commit Origination or Destination is different than parent it came from, we need to create another customer_needs records
                              //if (originationaddress != $("#originToMatch").val() || destinationaddress != $("#destToMatch").val()) {

                                  var newOriginationAddress1 = $("#originationAddress1").val();
                                  var newOriginationCity = $("#originationCity").val();
                                  var newOriginationState = $("#originationState").val();
                                  var newOriginationZip = $("#originationZip").val();
                                  var newDestinationAddress1 = $("#destinationAddress1").val();
                                  var newDestinationCity = $("#destinationCity").val();
                                  var newDestinationState = $("#destinationState").val();
                                  var newDestinationZip = $("#destinationZip").val();
                                  var carrierID = $("#carrierID").val();

                                  var url = '<?php echo HTTP_HOST."/createcustomerneedsfromexisting" ?>';
                                  var date = today;
                                  var recStatus = 'Available';
                                  var data = {id: $("#id").val(), rootCustomerNeedsID: $("#rootCustomerNeedsID").val(), carrierID: carrierID, qty: $("#qty").val(), originationAddress1: newOriginationAddress1, originationCity: newOriginationCity, originationState: newOriginationState, originationZip: newOriginationZip, destinationAddress1: newDestinationAddress1, destinationCity: newDestinationCity, destinationState: newDestinationState, destinationZip: newDestinationZip, originationLat: originationlat, originationLng: originationlng, destinationLat: destinationlat, destinationLng: destinationlng, distance: distance,  transportationMode: $("#transportationMode").val(),transportation_mode: $("#transportationMode").val(), transportation_type: $('input[name="transportationType"]:checked').val(), pickupDate: $("#pickupDate").val(), deliveryDate: $("#deliveryDate").val()};
                                  $.ajax({
                                     url: url,
                                     type: 'POST',
                                     data: JSON.stringify(data),
                                     contentType: "application/json",
                                     async: false,
                                     success: function(notification){
                                         //alert("Create from existing: " + notification);
                                        $("#load").html("Commit");
                                        $("#load").prop("disabled", false);
                                         $("#myModalCommit").modal('hide');
                                     },
                                     error: function() {
                                        alert('Failed creating a new Need from an existing.');
                                        $("#load").html("Commit");
                                        $("#load").prop("disabled", false);
                                        $("#myModalCommit").modal('hide');
                                     }
                                  });
                              //}

                            $("#load").html("Commit");
                            $("#load").prop("disabled", false);
                              $("#myModal").modal('hide');
                              loadCustomerNeedsCommitAJAX ($("#id").val());
                              $("#id").val('');
                              $("#rootCustomerNeedsID").val('');
                              $("#qty").val('');
                              $("#rate").val('');
                              $("#availableDate").val('');
                              $("#expirationDate").val('');
                              $("#originationAddress1").val('');
                              $("#originationCity").val('');
                              $("#originationState").val('');
                              $("#originationZip").val('');
                              $("#destinationAddress1").val('');
                              $("#destinationCity").val('');
                              $("#destinationState").val('');
                              $("#destinationZip").val('');
                              $("#carrierID").val('');
                              passValidation = true;

                              return passValidation;

                });
      }

    function getCommitted(){

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

                loadTableAJAX(customer_needs_commit.toString());

           },
           error: function() {
              alert("There Was An Error Saving the Status");
           }
        });

    }


    function loadTableAJAX(committed) {

        var baseUrl = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=customer_needs_commit,entities&columns=entities.name,id,rootCustomerNeedsID,entityID,qty,rate,availableDate,expirationDate,transportationMode,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,customer_needs_commit.id,customer_needs_commit.status,customer_needs_commit.rate,customer_needs_commit.transporation_mode,entities.rateType,entities.negotiatedRate&filter[]=id,in,(0,' + committed + ')&filter[]=status,eq,Available';

        var url = baseUrl + '&order[]=entityID&order[]=rootCustomerNeedsID&order[]=availableDate,desc&transform=1';

        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                dataSrc: 'customer_needs'
            },
            columns: [
                {
                    "className":      'details-control-add',
                    "orderable":      false,
                    "data":           null,
                    "defaultContent": ''
                },
                //{ data: "entities[0].name" },
                {
                    data: null,
                    "bSortable": true,
                    "mRender": function (o) {

                        var entityName = '';
                        var entityID = o.entityID;

                        allEntities.entities.forEach(function(entity){

                            if(entityID == entity.id){

                                entityName = entity.name;
                            }
                        });

                        return entityName;
                    }
                },
                { data: "id", visible: false },
                { data: "rootCustomerNeedsID", visible: false},
                { data: "entityID", visible: false },
                { data: "qty" },
                { data: "rate", visible: false},
                {
                    data: null,
                    "bSortable": true,
                    "mRender": function (o) {
                        var theDate = o.availableDate;
                        return formatDate(new Date(theDate));
                    }
                },
                {
                    data: null,
                    "bSortable": true,
                    "mRender": function(o) {
                      if (o.expirationDate == "0000-00-00") {
                          return '';
                      } else {
                          return formatDate(new Date(o.expirationDate));
                      }
                    }
                },
                { data: "transportationMode" },
                { data: "originationAddress1", visible: false },
                { data: "originationCity" },
                { data: "originationState" },
                { data: "originationZip", visible: false },
                { data: "originationLat", visible: false },
                { data: "originationLng", visible: false },
                { data: "destinationAddress1", visible: false },
                { data: "destinationCity" },
                { data: "destinationState" },
                { data: "destinationZip", visible: false },
                { data: "destinationLat", visible: false },
                { data: "destinationLng", visible: false },
                { data: "distance", render: $.fn.dataTable.render.number(',', '.', 0, '')  },
                { data: "needsDataPoints", visible: false },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function(o) {

                      var newStatus = o.status;
                      if (o.length > 0) {
                          var showAmount = o.rate.toString().split(".");

                          showAmount[0] = showAmount[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                          if (showAmount.length > 1) {
                              if (showAmount[1].length < 2) {
                                  showAmount[1] = showAmount[1] + '0';
                              }
                              showAmount = "$" + showAmount[0] + "." + showAmount[1];
                          } else {
                              showAmount = "$" + showAmount[0] + ".00";
                          }
                          if (o.status == "Cancelled") {
                              newStatus = "<strong>Cancelled</strong>";
                          } else {
                              newStatus = "<strong>Committed</strong>";
                          }
                      }
                      return newStatus;
                    }
                },
                { data: "customer_needs_commit[0].id", visible: false },
                { data: "customer_needs_commit[0].status", visible: false },
                { data: "customer_needs_commit[0].rate", visible: false },
                { data: "customer_needs_commit[0].transportation_mode", visible: false },
                { data: "entities[0].name", visible: false },
                { data: "entities[0].rateType", visible: false },
                { data: "entities[0].negotiatedRate", visible: false},
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {

                        var status = o.status;

                        if(status == "Available"){
                            var buttons = '<div class="pull-right text-nowrap">';
                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"fa fa-thumbs-up text\"></i> <span class=\"text\">View Commits</span></button>';
                            buttons += '</div>';
                        } else {
                            var buttons = "Commitment Complete!" ;
                        }

                        return buttons;
                    }
                }

            ],
            //scrollX: true
          });

          example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );

        //To Reload The Ajax
        //See DataTables.net for more information about the reload method
        example_table.ajax.reload();

      }

    function loadNewCustomerNeedsCommit(id){
    
        $('#relayTabs > li > a').unbind('click');
        $('#unitDataBody').empty();
        $('#relayTabs').empty();
        $('#selectedRelayTabs').empty();
        $('#pickupAddress').empty();
        $('#deliveryAddress').empty();
        $('#trailerData').empty();
        
        $('#addTrailer').empty();
        $("#dp-check-list-box").empty();
        $('#customerNeedsID').val(id);
            
        var baseUrl = '<?php echo API_HOST_URL; ?>' + '/customer_needs/' + id + '?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,rate,rateType,availableDate,expirationDate,deliveryInformation,pickupInformation,transportationMode,originationAddress1,originationAddress2,originationCity,originationState,originationZip,originationNotes,destinationNotes,originationLat,originationLng,destinationAddress1,destinationAddress2,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,unitData,status,customer_needs_commit.id,customer_needs_commit.status,customer_needs_commit.rate,customer_needs_commit.transportation_mode,entities.name,entities.rateType,entities.negotiatedRate';

        var url = baseUrl + '&satisfy=any&order[]=entityID&order[]=rootCustomerNeedsID&order[]=availableDate,desc&transform=1';

        var relayURL = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,rate,availableDate,expirationDate,deliveryInformation,pickupInformation,transportationMode,rate,originationAddress1,originationCity,originationState,originationZip,originationNotes,destinationNotes,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,unitData,status,customer_needs_commit.id,customer_needs_commit.entityID,customer_needs_commit.status,customer_needs_commit.pickupDate,customer_needs_commit.deliveryDate,customer_needs_commit.rate,customer_needs_commit.transportation_mode,entities.name,entities.rateType,entities.negotiatedRate&filter[]=rootCustomerNeedsID,eq,' + id + '&filter[]=status,eq,Available&satisfy=all&order[]=id&transform=1';
        
        $.get(url, function(data){
            
            var customer_needs = data;
            var needsDataPoints = customer_needs.needsDataPoints;
            
            if(customer_needs.deliveryInformation == null) customer_needs.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
            if(customer_needs.pickupInformation == null) customer_needs.pickupInformation = {pickupLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
            if(customer_needs.originationAddress1 == null) customer_needs.originationAddress1 = "";
            if(customer_needs.destinationAddress1 == null) customer_needs.destinationAddress1 = "";
            if(customer_needs.originationAddress2 == null) customer_needs.originationAddress2 = "";
            if(customer_needs.destinationAddress2 == null) customer_needs.destinationAddress2 = "";
            if(customer_needs.originationZip == null) customer_needs.originationZip = "";
            if(customer_needs.destinationZip == null) customer_needs.destinationZip = "";
            if(customer_needs.unitData == null) customer_needs.unitData = [];
            
            $('#customerID').val(customer_needs.entityID);
            
            // Populate Edit form
            $('#pickupLocation').val(customer_needs.pickupInformation.pickupLocation);
            $('#pickupContactPerson').val(customer_needs.pickupInformation.contactPerson);
            $('#pickupPhoneNumber').val(customer_needs.pickupInformation.phoneNumber);
            $('#pickupHoursOfOperation').val(customer_needs.pickupInformation.hoursOfOperation);            
            $('#originationAddress1').val(customer_needs.originationAddress1);
            $('#originationAddress2').val(customer_needs.originationAddress2);
            $('#originationCity').val(customer_needs.originationCity);
            $('#originationState').val(customer_needs.originationState);
            $('#originationZip').val(customer_needs.originationZip);
            $('#originationNotes').val(customer_needs.originationNotes);
            
            
            $('#deliveryLocation').val(customer_needs.deliveryInformation.deliveryLocation);
            $('#deliveryContactPerson').val(customer_needs.deliveryInformation.contactPerson);
            $('#deliveryPhoneNumber').val(customer_needs.deliveryInformation.phoneNumber);
            $('#deliveryHoursOfOperation').val(customer_needs.deliveryInformation.hoursOfOperation);            
            $('#destinationAddress1').val(customer_needs.destinationAddress1);
            $('#destinationAddress2').val(customer_needs.destinationAddress2);
            $('#destinationCity').val(customer_needs.destinationCity);
            $('#destinationState').val(customer_needs.destinationState);
            $('#destinationZip').val(customer_needs.destinationZip);
            $('#destinationNotes').val(customer_needs.destinationNotes);
            
            var pickupInformation = "";
            var deliveryInformation = "";
            
            // Populate view
            if(customer_needs.pickupInformation.pickupLocation != "" && customer_needs.pickupInformation.contactPerson != "" && customer_needs.pickupInformation.phoneNumber != "" && customer_needs.pickupInformation.hoursOfOperation != "" ){
                
                pickupInformation = customer_needs.pickupInformation.pickupLocation + "<br>" 
                        + customer_needs.pickupInformation.contactPerson + "<br>"
                        + customer_needs.pickupInformation.phoneNumber + "<br>"
                        + customer_needs.pickupInformation.hoursOfOperation + "<br><br>";
            }
            
            if(customer_needs.deliveryInformation.deliveryLocation != "" && customer_needs.deliveryInformation.contactPerson != "" && customer_needs.deliveryInformation.phoneNumber != "" && customer_needs.deliveryInformation.hoursOfOperation != "" ){
                
                deliveryInformation = customer_needs.deliveryInformation.deliveryLocation + "<br>" 
                        + customer_needs.deliveryInformation.contactPerson + "<br>"
                        + customer_needs.deliveryInformation.phoneNumber + "<br>"
                        + customer_needs.deliveryInformation.hoursOfOperation + "<br><br>";
            }
            
            var pickupAddress = pickupInformation + customer_needs.originationAddress1 + "<br>" +
                    customer_needs.originationCity + ", " + customer_needs.originationState + " " + customer_needs.originationZip + "<br><br>";
                        
            var deliveryAddress = deliveryInformation + customer_needs.destinationAddress1 + "<br>" +
                    customer_needs.destinationCity + ", " + customer_needs.destinationState + " " + customer_needs.destinationZip + "<br><br>";
                        
            if(customer_needs.originationNotes != ""){
                    pickupAddress  += "Notes:<br>" + 
                                customer_needs.originationNotes + "<br>";
            }         
            
            if(customer_needs.destinationNotes != ""){
                    deliveryAddress  += "Notes:<br>" + 
                                customer_needs.destinationNotes + "<br>";
            }
                        
            var trailerData = "Quantity: " + customer_needs.qty + "<br>"
                            + "Rate: " + customer_needs.rate + "<br>"
                            + "Rate Type: " + customer_needs.rateType + "<br>"
                            + "Transportation Mode: " + customer_needs.transportationMode + "<br>";
            
            var unitData = "";
            var unitEdit = "";
            
            $.each(customer_needs.unitData, function(key, unit){
                unitData += "<tr>" + 
                        "<td>"+ unit.unitNumber +"</td>" + 
                        "<td>"+ unit.vinNumber +"</td>" + 
                        "<td>"+ unit.truckProNumber +"</td>" + 
                        "<td>"+ unit.poNumber +"</td>" + 
                        "</tr>";
                
                unitEdit += ' <div class="row"><div class="col-md-3">\n\
                            <div class="form-group">\n\
                                    <label for="unitNumber' + (key + 1) + '">Unit #</label>\n\
                                    <input class="form-control" id="unitNumber' + (key + 1) + '" placeholder="" type="text" value="'+unit.unitNumber+'">\n\
                            </div>\n\
                        </div>\n\
                <div class="col-md-3">\n\
                            <div class="form-group">\n\
                                    <label for="vinNumber' + (key + 1) + '">VIN #</label>\n\
                                    <input class="form-control" id="vinNumber' + (key + 1) + '" placeholder="" type="text" value="'+unit.vinNumber+'">\n\
                            </div>\n\</div>\n\
                <div class="col-md-3">\n\
                            <div class="form-group">\n\
                                    <label for="truckProNumber' + (key + 1) + '">Truck/Pro #</label>\n\
                                    <input class="form-control" id="truckProNumber' + (key + 1) + '" placeholder="" type="text" value="'+unit.truckProNumber+'">\n\
                            </div>\n\</div>\n\
                <div class="col-md-3">\n\
                            <div class="form-group">\n\
                                    <label for="poNumber' + (key + 1) + '">P.O. #</label>\n\
                                    <input class="form-control" id="poNumber' + (key + 1) + '" placeholder="" type="text" value="'+unit.poNumber+'">\n\
                            </div>\n\</div></div>';
            });
            
            var dpli = '<div class="form-group row">' +
                            '<label for="qty" class="col-sm-3 col-form-label">Quantity</label>'+
                            '<div class="col-sm-9">' +
                            '<input id="qty" name="qty" class="form-control" value="'+customer_needs.qty+'">'+
                            '</div>'+
                            '</div>';
                    
            for (var i = 0; i < dataPoints.object_type_data_points.length; i++) {
                var selected = '';
                var value = '';

                $.each(needsDataPoints, function(idx, obj) {
                  $.each(obj, function(key, val) {
                    if (dataPoints.object_type_data_points[i].columnName == key) {
                        value = val; // Get the value from the JSON data in the record to use to set the selected option in the dropdown
                        
                        trailerData += dataPoints.object_type_data_points[i].title + ": " + val + "<br>";
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
                    '       <input id="rate" name="rate" class="form-control" value="' + customer_needs.rate + '">' +
                    '   </div>' +
                    '</div>';
            
            dpli += '<div class="form-group row">' +
                    '   <label for="rateType" class="col-sm-3 col-form-label">Rate Type</label>' +
                    '   <div class="col-sm-9">' +
                    '       <input type="radio" id="rateType" name="rateType" value="Flat Rate" ' + (customer_needs.rateType == "Flat Rate" ? "checked" : "") + '/> Flat Rate ' + 
                    '       <input type="radio" id="rateType" name="rateType" value="Mileage" ' + (customer_needs.rateType == "Mileage" ? "checked" : "") + '/> Mileage</div>' +
                    '   </div>'+
                    '</div>';
            
            dpli += '<div class="form-group row">' +
                    '   <label for="transportationMode" class="col-sm-3 col-form-label">Transportation Mode</label>' +
                    '   <div class="col-sm-9">' +
                    '       <select class="form-control" id="transportationMode" name="transportationMode">' +
                    '           <option value="">*Select Mode...</option>' +
                    '           <option value="Empty" ' + (customer_needs.transportationMode == "Empty" ? "selected" : "") + '>Empty</option>' +
                    '           <option value="Load Out" ' + (customer_needs.transportationMode == "Load Out" ? "selected" : "") + '>Load Out</option>' +
                    '           <option value="Either (Empty or Load Out)" ' + (customer_needs.transportationMode == "Either (Empty or Load Out)" ? "selected" : "") + '>Either (Empty or Load Out)</option>' +
                    '       </select>' +
                    '   </div>'+
                    '</div>';
            
            $("#dp-check-list-box").append(dpli);
        
            
            
            $('#addTrailer').append(unitEdit);
            $('#unitDataBody').append(unitData);
            $('#pickupAddress').append(pickupAddress);
            $('#deliveryAddress').append(deliveryAddress);
            $('#trailerData').append(trailerData);
            
        });
        
        $.get(relayURL, function(data){
            var relays = data.customer_needs;
            var relayTabs = "";
            var selectedRelayTabs = "";
            
            $.each(relays, function(key, customer_needs){   
                if(customer_needs.deliveryInformation == null) customer_needs.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
                if(customer_needs.destinationAddress1 == null) customer_needs.destinationAddress1 = "";
                if(customer_needs.destinationZip == null) customer_needs.destinationZip = "";

                var deliveryInformation = "";

                if(customer_needs.deliveryInformation.deliveryLocation != "" && customer_needs.deliveryInformation.contactPerson != "" && customer_needs.deliveryInformation.phoneNumber != "" && customer_needs.deliveryInformation.hoursOfOperation != "" ){

                    deliveryInformation = customer_needs.deliveryInformation.deliveryLocation + "<br>" 
                            + customer_needs.deliveryInformation.contactPerson + "<br>"
                            + customer_needs.deliveryInformation.phoneNumber + "<br>"
                            + customer_needs.deliveryInformation.hoursOfOperation + "<br><br>";
                }

                var deliveryAddress = deliveryInformation + customer_needs.destinationAddress1 + "<br>" +
                        customer_needs.destinationCity + ", " + customer_needs.destinationState + " " + customer_needs.destinationZip + "<br><br>";
      
                if(customer_needs.destinationNotes != ""){
                        deliveryAddress  += "Notes:<br>" + 
                                    customer_needs.destinationNotes + "<br>";
                }
            
                var relayNumber = key + 1;
                if(key == 0){
                    relayTabs += "<li class=\"nav-link active\" aria-expanded=\"false\"><a href=\"#" + key + "\" data-toggle=\"tab\" class=\"active\" aria-expanded=\"true\">Relay Address " + relayNumber + "</a></li>";
                    
                    selectedRelayTabs += "<div class=\"tab-pane active\" id=\"" + key + "\" aria-expanded=\"false\">" + deliveryAddress + "</div>";
                }
                else{
                    relayTabs += "<li class=\"nav-link\" aria-expanded=\"false\"><a href=\"#" + key + "\" data-toggle=\"tab\" class=\"active\" aria-expanded=\"true\">Relay Address " + relayNumber + "</a></li>";
                    
                    selectedRelayTabs += "<div class=\"tab-pane\" id=\"" + key + "\" aria-expanded=\"false\">" + deliveryAddress + "</div>";
                }
                
                $('#relay_id' + relayNumber).val(customer_needs.id);
                $('#commit_id' + relayNumber).val(customer_needs.customer_needs_commit.id);
                $('#address_relay' + relayNumber).val(customer_needs.destinationAddress1);
                $('#city_relay' + relayNumber).val(customer_needs.destinationCity);
                $('#state_relay' + relayNumber).val(customer_needs.destinationState);
                $('#zip_relay' + relayNumber).val(customer_needs.destinationZip);     
                $('#notes_relay' + relayNumber).val(customer_needs.destinationNotes);  
                
                $('#deliveryLocation_relay' + relayNumber).val(customer_needs.deliveryInformation.deliveryLocation); 
                $('#contactPerson_relay' + relayNumber).val(customer_needs.deliveryInformation.contactPerson); 
                $('#phoneNumber_relay' + relayNumber).val(customer_needs.deliveryInformation.phoneNumber); 
                $('#hoursOfOperation_relay' + relayNumber).val(customer_needs.deliveryInformation.hoursOfOperation); 
                
                if(relayNumber == 4) return false;
                
            });
            
            $('#relayTabs').append(relayTabs);
            $('#selectedRelayTabs').append(selectedRelayTabs);
            
            $('#relayTabs > li > a').bind('click',function(){

                var relayToShow = $(this).attr('href');
                $('#relayTabs > li').removeClass('active');
                $(this).parent().addClass('active');
                $('#selectedRelayTabs > div').removeClass('active');
                $('#selectedRelayTabs > div' + relayToShow).addClass('active');

            });
    
        });
        
        $("#customer-needs-commit").css("display", "block");
        $("#customer-needs").css("display", "none");
    }
    
/*
    function loadCustomerNeedsCommitAJAX (id){

        var url = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,rate,availableDate,expirationDate,transportationMode,rate,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,customer_needs_commit.id,customer_needs_commit.entityID,customer_needs_commit.status,customer_needs_commit.pickupDate,customer_needs_commit.deliveryDate,customer_needs_commit.rate,customer_needs_commit.transportation_mode,entities.name,entities.rateType,entities.negotiatedRate&filter=rootCustomerNeedsID,eq,' + id + '&satisfy=all&order[]=entityID&order[]=rootCustomerNeedsID&order[]=availableDate,desc&transform=1';
        //alert(url);

        if ( ! $.fn.DataTable.isDataTable( '#customer-needs-commit-table' ) ) {

            var example_table_commit = $('#customer-needs-commit-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                //dataSrc: 'customer_needs',
                dataSrc: function ( json ) {

                    var customer_needs = json.customer_needs;
                    return customer_needs;
                }
            },
            columns: [
                //{ data: "entities[0].name", visible: true },
                {
                    data: null,
                    "bSortable": true,
                    "mRender": function (o) {

                        var entityName = '';
                        var entityID = o.customer_needs_commit[0].entityID;

                        allEntities.entities.forEach(function(entity){

                            if(entityID == entity.id){

                                entityName = entity.name;
                            }
                        });

                        return entityName;
                    }
                },
                { data: "id", visible: false },
                { data: "rootCustomerNeedsID", visible: false},
                { data: "entityID", visible: false },
                { data: "qty" },
                { data: "rate", visible: false },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var theDate = o.customer_needs_commit[0].pickupDate;
                        return formatDate(new Date(theDate));
                    }
                },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var theDate = o.customer_needs_commit[0].deliveryDate;
                        return formatDate(new Date(theDate));
                    }
                },
                { data: "transportationMode" },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {

                        var input = '';
                        var status = o.customer_needs_commit[0].status;
                        var carrierRate = o.customer_needs_commit[0].rate.toFixed(2);
                        var commitID = o.id;

                        if(status == "Available"){
                            input += "<input id=\"carrierRate-" + commitID + "\" type=\"text\" name=\"carrierRate\" class=\"form-control mb-sm\" placeholder=\"Carrier Rate\" value=\"" + carrierRate + "\"/>";
                        }
                        else{
                            input += "<input id=\"carrierRate-" + commitID + "\" type=\"text\" name=\"carrierRate\" class=\"form-control mb-sm\" placeholder=\"Carrier Rate\" value=\"" + carrierRate + "\" readonly/>";
                        }

                        return input;
                    }
                },
                { data: "originationCity" },
                { data: "originationState" },
                { data: "originationLat", visible: false },
                { data: "originationLng", visible: false },
                { data: "destinationCity" },
                { data: "destinationState" },
                { data: "destinationLat", visible: false },
                { data: "destinationLng", visible: false },
                { data: "distance", render: $.fn.dataTable.render.number(',', '.', 0, '')  },
                { data: "needsDataPoints", visible: false },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function(o) {

                      var newStatus = o.status;
                      if (o.length > 0) {
                          var showAmount = o.rate.toString().split(".");
                          showAmount[0] = showAmount[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                          if (showAmount.length > 1) {
                              if (showAmount[1].length < 2) {
                                  showAmount[1] = showAmount[1] + '0';
                              }
                              showAmount = "$" + showAmount[0] + "." + showAmount[1];
                          } else {
                              showAmount = "$" + showAmount[0] + ".00";
                          }
                          if (o.status == "Cancelled") {
                              newStatus = "<strong>Cancelled</strong>";
                          } else {
                              newStatus = "<strong>Committed</strong>";
                          }
                      }
                      return newStatus;
                    }
                },
                { data: "customer_needs_commit[0].id", visible: false },
                { data: "customer_needs_commit[0].status", visible: false },
                { data: "customer_needs_commit[0].rate", visible: false },
                { data: "customer_needs_commit[0].transportation_mode", visible: false },
                { data: "entities[0].name", visible: false },
                { data: "entities[0].rateType", visible: false },
                { data: "entities[0].negotiatedRate", visible: false},
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {

                        var buttons = '<div class="pull-right text-nowrap">';

                        var status = o.customer_needs_commit[0].status;

                        if(status == "Available"){
                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"fa fa-check text\"></i> <span class=\"text\">Accept Commit</span></button>';
                        }
                        else{
                            buttons += "<b>Approved!</b>" ;
                        }

                        buttons += '</div>';

                        return buttons;
                    }
                }
            ],
            //scrollX: true
          });

            example_table_commit.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table_commit.table().container() ) );

            //To Reload The Ajax
            //See DataTables.net for more information about the reload method
            example_table_commit.ajax.reload(function(json){
                getCarrierTotal(json);
            });
        }
        else{
          //The URL will change with each "View Commit" button click
          // Must load new Url each time.
            var reload_table_commit = $('#customer-needs-commit-table').DataTable();
            reload_table_commit.ajax.url(url).load(function(json){
                getCarrierTotal(json);
            });
        }

        $("#customer-needs-commit").css("display", "block");
        $("#customer-needs").css("display", "none");

      }

    function loadSelectedCustomer(id){

        var baseUrl = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,rate,availableDate,expirationDate,transportationMode,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,customer_needs_commit.id,customer_needs_commit.status,customer_needs_commit.rate,customer_needs_commit.transportation_mode,entities.name,entities.rateType,entities.negotiatedRate';

        baseUrl = baseUrl + "&filter[]=id,eq," + id;

        var url = baseUrl + '&satisfy=any&order[]=entityID&order[]=rootCustomerNeedsID&order[]=availableDate,desc&transform=1';

        if ( ! $.fn.DataTable.isDataTable( '#selected-customer-need' ) ) {

            var example_table_customer = $('#selected-customer-need').DataTable({
                retrieve: true,
                processing: true,
                ajax: {
                    url: url,
                    dataSrc: 'customer_needs'
                },
                columns: [
                    //{ data: "entities[0].name", visible: true },
                    {
                        data: null,
                        "bSortable": true,
                        "mRender": function (o) {

                            var entityName = '';
                            var entityID = o.entityID;

                            allEntities.entities.forEach(function(entity){

                                if(entityID == entity.id){

                                    entityName = entity.name;
                                }
                            });

                            return entityName;
                        }
                    },
                    { data: "id", visible: false },
                    { data: "rootCustomerNeedsID", visible: false},
                    { data: "entityID", visible: false },
                    { data: "qty" },
                    {
                        data: null,
                        "bSortable": true,
                        "mRender": function (o) {
                            var theDate = o.availableDate;
                            return formatDate(new Date(theDate));
                        }
                    },
                    {
                        data: null,
                        "bSortable": true,
                        "mRender": function(o) {
                          if (o.expirationDate == "0000-00-00") {
                              return '';
                          } else {
                              return formatDate(new Date(o.expirationDate));
                          }
                        }
                    },
                    { data: "transportationMode" },
                    { data: "originationCity" },
                    { data: "originationState" },
                    { data: "originationLat", visible: false },
                    { data: "originationLng", visible: false },
                    { data: "destinationCity" },
                    { data: "destinationState" },
                    { data: "destinationLat", visible: false },
                    { data: "destinationLng", visible: false },
                    { data: "distance", render: $.fn.dataTable.render.number(',', '.', 0, '')  },
                    { data: "needsDataPoints", visible: false },
                    {
                        data: null,
                        "bSortable": false,
                        "mRender": function(o) {

                          var newStatus = o.status;
                          if (o.length > 0) {
                              var showAmount = o.rate.toString().split(".");
                              showAmount[0] = showAmount[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                              if (showAmount.length > 1) {
                                  if (showAmount[1].length < 2) {
                                      showAmount[1] = showAmount[1] + '0';
                                  }
                                  showAmount = "$" + showAmount[0] + "." + showAmount[1];
                              } else {
                                  showAmount = "$" + showAmount[0] + ".00";
                              }
                              if (o.status == "Cancelled") {
                                  newStatus = "<strong>Cancelled</strong>";
                              } else {
                                  newStatus = "<strong>Committed</strong>";
                              }
                          }
                          return newStatus;
                        }
                    },
                    { data: "customer_needs_commit[0].id", visible: false },
                    { data: "customer_needs_commit[0].status", visible: false },
                    { data: "customer_needs_commit[0].rate", visible: false },
                    { data: "customer_needs_commit[0].transportation_mode", visible: false },
                    { data: "entities[0].rateType", visible: false },
                    { data: "entities[0].negotiatedRate", visible: false}

                ]
            });

            example_table_customer.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table_customer.table().container() ) );

            //To Reload The Ajax
            //See DataTables.net for more information about the reload method
            example_table_customer.ajax.reload();
        } else {
          //The URL will change with each "View Commit" button click
          // Must load new Url each time.
            var reload_table_customer = $('#selected-customer-need').DataTable();
            reload_table_customer.ajax.url(url).load();
        }
    }

    function loadCustomerNeedsNotesAJAX(id){

        var entityType = <?php echo $_SESSION['entitytype'];  ?>;
        var blnShow = false;

        if (entityType == 0) blnShow = true;

        var url = '<?php echo API_HOST_URL; ?>' + '/customer_needs_notes?columns=id,customerNeedsID,note,permission,createdAt&filter[]=customerNeedsID,eq,' + id + '&filter[]=permission,cs,' + entityType + '&transform=1';

        if ( ! $.fn.DataTable.isDataTable( '#customer-needs-note-table' ) ) {

            var example_table_notes = $('#customer-needs-note-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                dataSrc: 'customer_needs_notes'
            },
            columns: [
                {
                    data: null,
                    "bSortable": true,
                    "render": function(o) {
                        var permission = o.permission;
                        var viewAccess = "";

                        switch(permission){
                            case "0":
                                viewAccess = "NEC Admin Only";
                                break;
                            case "0,1":
                                viewAccess = "Customer and NEC Admin";
                                break;
                            case "0,2":
                                viewAccess = "Carrier and NEC Admin";
                                break;
                            case "0,1,2":
                                viewAccess = "All";
                                break;
                        }

                      return viewAccess;
                    },
                    visible: blnShow
                },
                { data: "createdAt" },
                { data: "note" }

            ]
          });

            example_table_notes.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table_notes.table().container() ) );

            //To Reload The Ajax
            //See DataTables.net for more information about the reload method
            example_table_notes.ajax.reload();
        }
        else{
          //The URL will change with each "View Commit" button click
          // Must load new Url each time.
            var reload_table_note = $('#customer-needs-note-table').DataTable();
            reload_table_note.ajax.url(url).load();
        }
    }

    function getCarrierTotal(json){

            var customer_needs = json.customer_needs;
            var carrierTotal = 0;

            customer_needs.forEach(function(customer_need){

                if(customer_need.customer_needs_commit.length > 0 && customer_need.customer_needs_commit[0].status !== "Available"){
                    carrierTotal += customer_need.customer_needs_commit[0].rate;
                }
            });

        $("#carrierTotalRate").val(carrierTotal.toFixed(2));
        getTotalRevenue();
      }

    function approveCommit(rootCustomerNeedsID, commitID, carrierRate){

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

        var url = '<?php echo API_HOST_URL . "/customer_needs_commit" ?>/' + commitID;
        type = "PUT";
        var date = today;
        var data = {rate: carrierRate, status: "Close", updatedAt: date};
        //var data = {rate: carrierRate, updatedAt: date};

        $.ajax({
           url: url,
           type: type,
           data: JSON.stringify(data),
           contentType: "application/json",
           async: false,
           success: function(data){
              if (data > 0) {
                if (type === 'PUT') {
                   var params = {id: commitID};
                   $.ajax({
                      url: '<?php echo HTTP_HOST."/commitacceptednotification" ?>',
                      type: 'POST',
                      data: JSON.stringify(params),
                      contentType: "application/json",
                      async: false,
                      success: function(notification){

                      },
                      error: function() {
                         alert('Failed Sending Notifications! - Notify NEC of this failure.');
                      }
                   });
                }

              } else {
                alert("Approving Commit Failed! Please Verify Your Data.");
              }

              loadCustomerNeedsCommitAJAX(rootCustomerNeedsID);
           },
           error: function() {
              alert("There Was An Error Approving Commit!");
           }
        });
    }
*/
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

 </style>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">View Carrier Committed Transport</li>
 </ol>
 <section id="customer-needs" class="widget">
     <header>
         <h4><span class="fw-semi-bold">Available Transport</span></h4>
         <div class="widget-controls">
             
         </div>
     </header>
     <div class="widget-body">
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th></th>
                     <th>Company</th>
                     <th>ID</th>
                     <th>Root Customer Needs ID</th>
                     <th>Entity ID</th>
                     <th>Qty</th>
                     <th>Rate</th>
                     <th>Available</th>
                     <th>Expires</th>
                     <th>Transport Mode</th>
                     <th class="hidden-sm-down">Orig. Address1</th>
                     <th class="hidden-sm-down">Orig. City</th>
                     <th class="hidden-sm-down">Orig. State</th>
                     <th class="hidden-sm-down">Orig. Zip</th>
                     <th class="hidden-sm-down">Orig. Lat.</th>
                     <th class="hidden-sm-down">Orig. Long.</th>
                     <th class="hidden-sm-down">Dest. Address1</th>
                     <th class="hidden-sm-down">Dest. City</th>
                     <th class="hidden-sm-down">Dest. State</th>
                     <th class="hidden-sm-down">Dest. Zip</th>
                     <th class="hidden-sm-down">Dest. Lat.</th>
                     <th class="hidden-sm-down">Dest. Long.</th>
                     <th class="hidden-sm-down">Mileage</th>
                     <th class="hidden-sm-down">Data Points</th>
                     <th>Status</th>
                     <th>Commit ID</th>
                     <th>Commit Status</th>
                     <th>Commit Rate</th>
                     <th>Transportation Mode</th>
                     <th>Name</th>
                     <th>Rate Type</th>
                     <th>Negotiated Rate</th>
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
 
<!-- New Commit View -->
<section class="widget"  id="customer-needs-commit" style="display: none;">
     <header>
         <h4><span class="fw-semi-bold">Committed Transport</span></h4>
         <div class="widget-controls">
             <!--<a data-widgster="save" title="Save" href="#" onclick="saveCommitAsOrder()"><i class="glyphicon glyphicon-floppy-disk" style="font-size: 20px;"></i></a>-->
             <a data-widgster="edit" title="Edit" href="#" onclick="editCommitTransport()"><i class="glyphicon glyphicon-pencil" style="font-size: 20px;"></i></a>
             <a data-widgster="close" title="Close" href="#" onclick="closeCommitTransport()"><i class="glyphicon glyphicon-remove" style="font-size: 20px;"></i></a>
         </div>
     </header>
     <br />
     <input type="hidden" id="customerID">
     <div class="row">
         <div class="col-md-8">
             <div class="row">
                <div class="col-sm-12 col-md-6">
                        <div class="panel-bkg">
                                <div class="panel-title">Pickup Address</div>
                                <div class="panel-content" id="pickupAddress">
                                    
                                </div>
                        </div>
                </div>

                <div class="col-sm-12 col-md-6">
                        <div class="panel-bkg">
                                <div class="panel-title">Delivery Address</div>
                                <div class="panel-content" id="deliveryAddress">
                                    
                                </div>
                        </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel-bkg">
                    <!-- tabs-left -- use util.js for functionality -->
                    <div class="tabbable tabs-left">
                            <ul class="nav nav-tabs" id="relayTabs">
                                    
                            </ul>
                            <div class="tab-content" id="selectedRelayTabs">
                                
                            </div>
                    </div>
                    <!-- /tabs -->

                    </div>
                </div>
            </div>
         </div>
         <div class="col-md-4">
             
		<div class="row">
			<div class="col-md-12">
				<div class="panel-bkg">
					<div class="panel-title">Trailer Data</div>
					<div class="panel-content" id="trailerData">
                                            
					</div>
				</div>
			</div>
		</div>
	
         </div>
     </div>
     <br>
     <div class="row">
         <div class="col-md-12">
             <div class="panel-bkg">
                <div class="panel-title">Unit Data</div>
                    <div class="panel-content">
                        <table id="new-unit-data-table" class="table table-striped table-hover" width="100%">
                            <thead>
                                <tr>
                                    <th>Unit #</th>
                                    <th>VIN #</th>
                                    <th>Truck/Pro#</th>
                                    <th>P.O. #</th>
                                </tr>
                            </thead>
                            <tbody id="unitDataBody">

                            </tbody>
                        </table>
                    </div>
             </div>
         </div>
     </div>
     <br>
     <div class="row">
         <div class="col-md-12">
             
           <button type="button" class="btn btn-primary btn-md" onclick="saveCommitAsOrder();" id="saveCommitAsOrder">Complete Order</button>
         </div>
     </div>
 </section>

<!-- Edit Commit Modal -->
  <div class="modal fade" id="editCommitModal" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h1 class="modal-title fw-semi-bold" id="exampleModalCommitLabel">Edit Commitment</h1>
        </div>
        <div class="modal-body">
            <div class="row">
                    <div class="col-md-12">
                    <h2>Pickup Address</h2>
                    <form>
                        <input type="hidden" id="customerNeedsID" name="customerNeedsID" value="" />
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
                                            <input class="form-control" id="pickupHoursOfOperation" placeholder="" type="text">
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
                            <div class="form-group row">
                                    <label for="originationNotes" class="col-sm-3 col-form-label">Notes</label>
                                    <div class="col-sm-9">
                                            <textarea class="form-control" id="originationNotes" rows="3"></textarea>
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
                                            <input class="form-control" id="deliveryHoursOfOperation" placeholder="" type="text">
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
                            <div class="form-group row">
                                    <label for="destinationNotes" class="col-sm-3 col-form-label">Notes</label>
                                    <div class="col-sm-9">
                                            <textarea class="form-control" id="destinationNotes" rows="3"></textarea>
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
                            <div class="form-group row">
                                    <label for="type" class="col-sm-3 col-form-label">Type</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="type" placeholder="" type="type">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="height" class="col-sm-3 col-form-label">Height</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="height" placeholder="Height of trailer door" type="height">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="door" class="col-sm-3 col-form-label">Door</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="door" placeholder="Trailer door type" type="door">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="test_data1" class="col-sm-3 col-form-label">Test Data</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="test_data1" placeholder="" type="test_data1">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="test_data2" class="col-sm-3 col-form-label">Test Data</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="test_data2" placeholder="" type="test_data2">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="test_data3" class="col-sm-3 col-form-label">Test Data</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="test_data3" placeholder="" type="test_data3">
                                    </div>
                            </div>
                            <div class="form-group row">
                                    <label for="test_data4" class="col-sm-3 col-form-label">Test Data</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="test_data4" placeholder="" type="test_data4">
                                    </div>
                            </div>
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
                                    <label for="hoursOfOperation_relay1">Hours of Operation</label>
                                    <input class="form-control" id="hoursOfOperation_relay1" placeholder="" type="text">
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
                            <div class="form-group">
                                    <label for="notes_relay1">Notes</label>
                                    <textarea class="form-control" id="notes_relay1" rows="3"></textarea>
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
                                    <label for="hoursOfOperation_relay2">Hours of Operation</label>
                                    <input class="form-control" id="hoursOfOperation_relay2" placeholder="" type="text">
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
                            <div class="form-group">
                                    <label for="notes_relay2">Notes</label>
                                    <textarea class="form-control" id="notes_relay2" rows="3"></textarea>
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
                                    <label for="hoursOfOperation_relay3">Hours of Operation</label>
                                    <input class="form-control" id="hoursOfOperation_relay3" placeholder="" type="text">
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
                            <div class="form-group">
                                    <label for="notes_relay3">Notes</label>
                                    <textarea class="form-control" id="notes_relay3" rows="3"></textarea>
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
                                    <label for="hoursOfOperation_relay4">Hours of Operation</label>
                                    <input class="form-control" id="hoursOfOperation_relay4" placeholder="" type="text">
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
                            <div class="form-group">
                                    <label for="notes_relay4">Notes</label>
                                    <textarea class="form-control" id="notes_relay4" rows="3"></textarea>
                             </div>
                    </div>
            </div>
            
            <hr>
            
            <div class="row">
                    <div class="col-md-12">
                            <h2>Unit Data</h2>
                    </div>
            </div>
            
            <br>
            
            <div class="row">
                <div id="addTrailer" class="col-md-12">
                    
                </div>
            </div>
            
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary btn-md" onclick="addTrailer();" id="addTrailer">AddTrailer</button>
           <button type="button" class="btn btn-primary btn-md" onclick="saveCommit();" id="saveCommit">Save</button>
        </div>
      </div>
    </div>
  </div>

 <script>

    getCommitted();


    $('.datepicker').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });

/*
    function addNewCommit(){

        var selectedTable = $("#selected-customer-need").DataTable();
        var json = selectedTable.ajax.json();
        var data = json.customer_needs[0];

        var li = '';
        var checked = '';
        var qtyselect = '<select id="qty" class="form-control mb-sm">\n';
        var transportationmodeselect = '<select id="transportationMode" name="transportationMode" class="form-control mb-sm" required="required">\n';
        var dpchecked = '';
        $("#id").val(data["id"]);
        $("#rootCustomerNeedsID").val(data["rootCustomerNeedsID"]);
        //$("#entityID").val(data["entityID"]); Use the session entity id of the logged in user, not from the customer_needs record
        $("#entityID").val(entityid);
        $("#qty").val(data["qty"]);
        $("#originationAddress1").val(data["originationAddress1"]);
        $("#originationCity").val(data["originationCity"]);
        $("#originationState").val(data["originationState"]);
        $("#originationZip").val(data["originationZip"]);
        $("#destinationAddress1").val(data["destinationAddress1"]);
        $("#destinationCity").val(data["destinationCity"]);
        $("#destinationState").val(data["destinationState"]);
        $("#destinationZip").val(data["destinationZip"]);
        $("#oaddress1").val(data["originationAddress1"]);
        $("#ocity").val(data["originationCity"]);
        $("#ostate").val(data["originationState"]);
        $("#ozip").val(data["originationZip"]);
        $("#daddress1").val(data["destinationAddress1"]);
        $("#dcity").val(data["destinationCity"]);
        $("#dstate").val(data["destinationState"]);
        $("#dzip").val(data["destinationZip"]);
        // Set up the matching addresses like we do up in the verifyAndPost() - makes it easy to do a compare
        //$("#originToMatch").val(data["originationAddress1"] + ', ' + data["originationCity"] + ', ' + data["originationState"] + ', ' + data["originationZip"]);
        //$("#destToMatch").val(data["destinationAddress1"] + ', ' + data["destinationCity"] + ', ' + data["destinationState"] + ', ' + data["destinationZip"]);
        $("#originToMatch").val(data["originationCity"] + ', ' + data["originationState"]);
        $("#destToMatch").val(data["destinationCity"] + ', ' + data["destinationState"]);
        //$("#rate").val(data["entities"][0].negotiatedRate.toFixed(2));
        $("#rate").val(entity.entities.records[0][1].toFixed(2));

        for (var i = 1; i <= data['qty']; i++) {
            if (i == data['qty']) {
                dpchecked = "selected=selected";
            }
            qtyselect += '<option ' + dpchecked + '>' + i + '</option>\n';
        }
        qtyselect += '</select>\n';
        $("#qtyDiv").html(qtyselect);

        if (entity.entities.records[0][0] == "Flat Rate") {
            $('input[name="transportationType"][value="Flat Rate"]').prop('checked', true);
        } else {
            $('input[name="transportationType"][value="Mileage"]').prop('checked', true);
        }

        var empty = "";
        var loadout = "";
        var either = "";
        if (data['transportationMode'] == "Empty") {
            transportationmodeselect += '<option value="Empty">Empty</option>\n';
        } else {
            if (data['transportationMode'] == "Empty") {
                empty = "selected=selected";
            } else if (data['transportationMode'] == "Load Out"){
                loadout = "selected=selected";
            } else if (data['transportationMode'] == "Either (Empty or Load Out)"){
                either = "selected=selected";
            }
            transportationmodeselect += '<option value="Empty" '+empty+'>Empty</option>\n';
            transportationmodeselect += '<option value="Load Out" '+loadout+'>Load Out</option>\n';
            transportationmodeselect += '<option value="Either (Empty or Load Out)" '+either+'>Either (Empty or Load Out)</option>\n';
        }

        transportationmodeselect += '</select>\n';
        $("#transportationModeDiv").html(transportationmodeselect);

        $("#entityID").prop('disabled', true);
        $("#myModalCommit").modal('show');
    }

    function addNewNote(){

        var selectedTable = $("#selected-customer-need").DataTable();
        var json = selectedTable.ajax.json();
        var data = json.customer_needs[0];

        $("#customerNeedsID").val(data["id"]);
        $("#viewAccess").val("0");
        $("#commitmentNote").val("");

        $("#addNote").modal('show');
    }
*/
    function closeCustomerCommitLegs(customerNeedID){

        $.ajax({
            url: '<?php echo API_HOST_URL ?>' + '/customer_needs?columns=id&filter=rootCustomerNeedsID,eq,' + customerNeedID + '&transform=1',
            type: "GET",
            contentType: "application/json",
            async: false,
            success: function(data){

                data.customer_needs.forEach(function(customerNeedID){

                    $.ajax({
                        url: '<?php echo API_HOST_URL ?>' + '/customer_needs/' + customerNeedID.id,
                        type: "PUT",
                        data: JSON.stringify({status: "Closed"}),
                        contentType: "application/json",
                        async: false,
                        success: function(data){
                            if(data > 0){
                                console.log("Leg Closed.");
                            }
                            else{
                                console.log("Could not close leg:", customerNeedID.id);
                            }
                        },
                        error: function(){
                            alert("Could not close availability leg.");
                        }
                    });
                });
            },
            error: function(){
                alert("Could not Get customer needs Customer Needs.");
            }
        });

    }
/*
    function createNewAvailability(customerNeedID, differenceQty, today){

        $.ajax({
            url: '<?php echo API_HOST_URL ?>' + '/customer_needs/' + customerNeedID + '?transform=1',
            type: "GET",
            contentType: "application/json",
            success: function(data){
                var url = '<?php echo API_HOST_URL . "/customer_needs" ?>';
                var type = "POST";

                var newCustomerNeed = {entityID: data.entityID, originationAddress1: data.originationAddress1, originationCity: data.originationCity, originationState: data.originationState, originationZip: data.originationZip,
                                    destinationAddress1: data.destinationAddress1, destinationCity: data.destinationCity, destinationState: data.destinationState, destinationZip: data.destinationZip,
                                    originationLng: data.originationLng, originationLat: data.originationLat, destinationLng: data.destinationLng, destinationLat: data.destinationLat, distance: data.distance,
                                    needsDataPoints: data.needsDataPoints, status: "Available", qty: differenceQty, rate: 0, rateType: data.rateType, transportationMode: data.transportationMode, contactsEmails: data.contactsEmails,
                                    availableDate: data.availableDate, expirationDate: data.expirationDate, createdAt: today, updatedAt: today};

                $.ajax({
                   url: url,
                   type: type,
                   data: JSON.stringify(newCustomerNeed),
                   contentType: "application/json",
                   async: false,
                   success: function(data){
                      if (data > 0) {
                           var params = {id: data};
                           $.ajax({
                              url: '<?php echo HTTP_HOST."/customerneedsnotification" ?>',
                              type: 'POST',
                              data: JSON.stringify(params),
                              contentType: "application/json",
                              async: false,
                              success: function(notification){
                                  //var updatedata = {rootCustomerNeedsID: data};
                                  var updatedata = {rootCustomerNeedsID: 0};
                                  $.ajax({
                                      url: '<?php echo API_HOST_URL . "/customer_needs" ?>/' + data,
                                      type: 'PUT',
                                      data: JSON.stringify(updatedata),
                                      contentType: "application/json",
                                      async: false,
                                      success: function(updateneeds){
                                          //alert(notification);
                                          countCommitments();
                                      },
                                      error: function() {
                                         alert('Failed Updating Root Customer Needs ID! - Notify NEC of this failure.');
                                      }
                                  });
                              },
                              error: function() {
                                 alert('Failed Sending Notifications! - Notify NEC of this failure.');
                              }
                           });
                      } else {
                        alert("Adding Need Failed! Please Verify Your Data.");
                      }
                   },
                   error: function() {
                      alert("There Was An Error Adding Availability!");
                   }
                });
            },
            error: function(error){
                console.log("Error: " + error);
            }

        });
    }
*/
   
   function addTrailer(){
       
       var unitID = $('#addTrailer > div').length;
       
       var unitData = ' <div class="row"><div class="col-md-3">\n\
                            <div class="form-group">\n\
                                    <label for="unitNumber' + (unitID + 1) + '">Unit #</label>\n\
                                    <input class="form-control" id="unitNumber' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\
                        </div>\n\
                <div class="col-md-3">\n\
                            <div class="form-group">\n\
                                    <label for="vinNumber' + (unitID + 1) + '">VIN #</label>\n\
                                    <input class="form-control" id="vinNumber' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\</div>\n\
                <div class="col-md-3">\n\
                            <div class="form-group">\n\
                                    <label for="truckProNumber' + (unitID + 1) + '">Truck/Pro #</label>\n\
                                    <input class="form-control" id="truckProNumber' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\</div>\n\
                <div class="col-md-3">\n\
                            <div class="form-group">\n\
                                    <label for="poNumber' + (unitID + 1) + '">P.O. #</label>\n\
                                    <input class="form-control" id="poNumber' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\</div></div>';
       
       $('#addTrailer').append(unitData);
   }
   
    function addVendorInfo(vendorName,vendorAddress,vendorCity,vendorState,vendorZip,vendorPrice,vendorNotes,customerID){
        <?php $quickbooks_host = "http://nec.dubtel.com";?>
                            $.ajax({
                                url: '<?php echo HTTP_HOST; ?>' + '/QBO/src/Pages/VendorCreate.php',
                                type: "POST",
                                data: jQuery.param({vendorName: vendorName,vendorPrice:vendorPrice,vendorNotes:vendorNotes,vendorAddress:vendorAddress,vendorCity:vendorCity,vendorState:vendorState,vendorZip:vendorZip,customerID:customerID}),
                                success: function(){
                                    console.log(vendorName + ' ' + vendorAddress + ' ' + vendorCity + ' ' + vendorPrice);
                                },
                                error: function(){
                                    console.log('Error:' + ' ' + vendorAddress + ' ' + vendorCity + ' ' + vendorPrice);
                                    alert("Could not Create Quickbooks Vendor");

                                }
                            });


    }

    //Yaw,
    // While I was investigating, I realized that the javascript was not waiting for a return from the addCustomerInfo function.
    // So, instead I thought it would be best to grab all of the Customer and Vendor information and push it into the addCustomerInfo function.
    // ONLY when we succeed, do we loop through our vendors and add them to the quickbooks as well.
    // I hope this doesn't make things complicated.
    // Dennis

    function addCustomerInfo(customerName,customerAddress,customerCity,customerState,customerZip,customerPrice,customerNotes, vendorDetails){
        //var result = '';
        <?php $quickbooks_host = "http://nec.dubtel.com";?>
            $.ajax({
                url: '<?php echo HTTP_HOST; ?>' + '/QBO/src/Pages/CustomerCreate.php',
                type: "POST",
                dataType: "json",
                data: jQuery.param({customerName: customerName,customerPrice:customerPrice,customerNotes:customerNotes,customerAddress:customerAddress,customerCity:customerCity,customerState:customerState,customerZip:customerZip}),
                success: function(data){

                    vendorDetails.forEach(function(carrier_detail){
                       addVendorInfo(carrier_detail.carrierName,carrier_detail.billingAddress,carrier_detail.billingCity,carrier_detail.billingState,carrier_detail.billingZip,carrier_detail.carrierRate,customerNotes,data.customer_id);

                    });

                    //console.log("Customer Created: " + JSON.stringify(data));
                    //console.log(data.customer_id);
                    //return data.customer_id;
                    //result = data;
                    //console.log('success result is:' + result);
                    //console.log(customerName + ' ' + customerAddress + ' ' + customerCity + ' ' + customerPrice);
                },
                error: function(){
                    console.log('Error:' + customerName + ' ' + customerAddress + ' ' + customerCity + ' ' + customerPrice);
                    alert("Could not Create Quickbooks Customer");

                }
            });
            //console.log('result is:' + result);
            //return result;

    }

    function saveOrderDetails(orderID){
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


        var table = $("#customer-needs-commit-table").DataTable();
        var url = '<?php echo API_HOST_URL ?>' + '/order_details/';
        var json = table.ajax.json();

        var customer_needs = json.customer_needs;
        var order_detail_list = new Array();

        customer_needs.forEach(function(customer_need){

            if(customer_need.customer_needs_commit.length > 0 &&
                    customer_need.customer_needs_commit[0].status == "Close"){

                var order_detail = {
                    carrierID: customer_need.customer_needs_commit[0].entityID,
                    orderID: orderID,
                    originationCity: customer_need.originationCity,
                    originationState: customer_need.originationState,
                    destinationCity: customer_need.destinationCity,
                    destinationState: customer_need.destinationState,
                    originationLng: customer_need.originationLng,
                    originationLat: customer_need.originationLat,
                    destinationLng: customer_need.destinationLng,
                    destinationLat: customer_need.destinationLat,
                    distance: customer_need.distance,
                    status: "Open",
                    transportationMode: customer_need.transportationMode,
                    qty: customer_need.qty,
                    carrierRate: customer_need.customer_needs_commit[0].rate,
                    pickupDate: customer_need.customer_needs_commit[0].pickupDate,
                    deliveryDate: customer_need.customer_needs_commit[0].deliveryDate,
                    createdAt: today,
                    updatedAt: today
                };

                order_detail_list.push(order_detail);
            }
        });

		if (order_detail_list.length > 0) {

	        $.ajax({
	            url: url,
	            type: "POST",
	            data: JSON.stringify(order_detail_list),
	            contentType: "application/json",
	            async: false,
	            success: function(){
	                alert("Customer Availability Successfully Completed.");
	            },
	            error: function(){
	                alert("Error with adding Order Details.");
	            }

	        });

		}

    }

    $('#completeOrder').unbind('click').bind('click', function(event){


        event.preventDefault();
        var today = new Date();
        var orderID = today.getTime().toString();
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

        var url = '<?php echo HTTP_HOST."/uploaddocument" ?>';
        var type = "POST";
        var formData = new FormData();
        var fileData = $('#filePurchaseOrder')[0].files[0];
        formData.append('entityID', $("#entityID").val());
        formData.append('name', "Purchase Order: " + today);
        formData.append('documentID', "purchaseOrder");
        formData.append('updatedAt', today);
        formData.append('fileupload', fileData);

        var selectedTable = $('#selected-customer-need').DataTable();
        var selectedCustomerNeed = selectedTable.ajax.json().customer_needs[0];

        var commitTable = $('#customer-needs-commit-table').DataTable();
        var customer_needs = commitTable.ajax.json().customer_needs;
        var carrierIDs = new Array();
        var carrierQty = 0;
        customer_needs.forEach(function(customer_need){

            if(customer_need.customer_needs_commit.length > 0 && customer_need.customer_needs_commit[0].status == "Close"){
                var carrier = {carrierID: customer_need.customer_needs_commit[0].entityID};

                if (carrierIDs.indexOf(carrier) === -1) carrierIDs.push(carrier);

                if (carrierQty === 0) carrierQty = customer_need.qty;
            }
        });
        
        if(fileData != undefined){
            
            var fileName = fileData.name;
            var fileExtension = fileName.split('.').pop();
            var acceptableExtension = ["pdf", "doc", "docx"];
            
            if(acceptableExtension.indexOf(fileExtension) > 0){
                $.ajax({
                    url: url,
                    type: type,
                    data: formData,
                    processData: false,  // tell jQuery not to process the data
                    contentType: false,  // tell jQuery not to set contentType
                    success: function(response){
                        //alert('Purchase Order Uploaded.');
                        var documentID = parseInt(response);    // Returned is the uploaded document's ID number.

                        var orderQty = 0;
                        var differenceQty = 0;

                        if(selectedCustomerNeed.qty > carrierQty) {
                            orderQty = carrierQty;
                            differenceQty = selectedCustomerNeed.qty - carrierQty;
                            createNewAvailability(selectedCustomerNeed.id, differenceQty, today);
                        }
                        else {
                            orderQty = selectedCustomerNeed.qty;
                        }

                        var url = '<?php echo API_HOST_URL ?>' + '/orders/';
                        var orderData = {customerID: $("#entityID").val(), carrierIDs: carrierIDs, documentID: documentID, orderID: orderID,
                                originationAddress: selectedCustomerNeed.originationAddress1, originationCity: selectedCustomerNeed.originationCity,
                                originationState: selectedCustomerNeed.originationState, originationZip: selectedCustomerNeed.originationZip,
                                originationLng: selectedCustomerNeed.originationLng, originationLat: selectedCustomerNeed.originationLat,
                                destinationAddress: selectedCustomerNeed.destinationAddress1, destinationCity: selectedCustomerNeed.destinationCity,
                                destinationState: selectedCustomerNeed.destinationState, destinationZip: selectedCustomerNeed.destinationZip,
                                destinationLng: selectedCustomerNeed.destinationLng, destinationLat: selectedCustomerNeed.destinationLat,
                                distance: selectedCustomerNeed.distance, needsDataPoints: selectedCustomerNeed.needsDataPoints,
                                status: "Open", transportationMode: selectedCustomerNeed.transportationMode, qty: orderQty,
                                rateType: selectedCustomerNeed.rateType, customerRate: $('#customerRate').val(), carrierTotalRate: $('#carrierTotalRate').val(),
                                totalRevenue: $('#totalRevenue').val(), createdAt: today, updatedAt: today};

                            var customerName = "";
                            allEntities.entities.forEach(function(entity){
                                if(selectedCustomerNeed.entityID == entity.id){
                                            customerName = entity.name;
                                }
                            });

                           var originationCity = selectedCustomerNeed.originationCity;
                           var originationState = selectedCustomerNeed.originationState;
                           var destinationCity = selectedCustomerNeed.destinationCity;
                           var destinationState = selectedCustomerNeed.destinationState;
                           var customerRate = $('#customerRate').val();
                           var customerID = $("#entityID").val();
                           var customerAddress = '';
                           var customerCity = '';
                           var customerState = '';
                           var customerZip = '';
                           var customerNotes = originationCity + ', ' + originationState + ' to ' + destinationCity + ', ' + destinationState;

                           var customerBillingAddress = getBillingAddress(customerID);

                           // Here is empty data for Customer Billing Address
                           customerAddress = customerBillingAddress.address1;
                           customerCity = customerBillingAddress.city;
                           customerState = customerBillingAddress.state;
                           customerZip = customerBillingAddress.zip;

                           var customerData = customerBillingAddress;
                           customerData.customerName = customerName;

                           // We are not calling this yet.
                           // We will wait until we have the carrier info as well.
                           //var retCustomerID = addCustomerInfo(customerName,customerAddress,customerCity,customerState,customerZip,customerRate,customerNotes);
                           //alert(retCustomerID);

                            var needsCommitTable = $("#customer-needs-commit-table").DataTable();
                            var needsCommitJSON = needsCommitTable.ajax.json();

                            var customer_needs_commit = needsCommitJSON.customer_needs;
                            var carrier_detail_list = new Array();
                            var carrier = "";

                            customer_needs_commit.forEach(function(customer_need){

                                if(customer_need.customer_needs_commit.length > 0 &&
                                        customer_need.customer_needs_commit[0].status == "Close"){

                                    var entityName = "";
                                    var entityID = customer_need.customer_needs_commit[0].entityID;


                                    allEntities.entities.forEach(function(entity){

                                        if(entityID == entity.id){

                                            entityName = entity.name;
                                        }
                                    });

                                    var carrierBillingAddress = getBillingAddress(entityID);

                                    var carrier_detail = {
                                        carrierName: entityName,                // This is the carrier's Name
                                        carrierRate: customer_need.customer_needs_commit[0].rate,    // This is that carrier's rate.
                                        billingAddress: carrierBillingAddress.address1,
                                        billingCity: carrierBillingAddress.city,
                                        billingState: carrierBillingAddress.state,
                                        billingZip: carrierBillingAddress.zip
                                    };

                                    carrier = carrier_detail.carrierName;
                                    var carrierNotes = customerNotes;

                                    // We will not be calling addVendorInfo yet. This will be nested inside addCustomerInfo
                                    //addVendorInfo(carrier_detail.carrierName,carrier_detail.billingAddress,carrier_detail.billingCity,carrier_detail.billingState,carrier_detail.billingZip,carrier_detail.carrierRate,carrierNotes,retCustomerID);
                                    carrier_detail_list.push(carrier_detail);
                                }
                            });

                            // This is a list of all the carriers accepted and associated with the commit.
                            //console.log(JSON.stringify(carrier_detail_list));

                           // You need the total Carrier...
                           var carrierTotalRate = $('#carrierTotalRate').val();

                           // Now that we have all of the carriers.
                           // We will now call addCustomerInfo.
                           // We do not need to wait for a return.
                           addCustomerInfo(customerName,customerAddress,customerCity,customerState,customerZip,customerRate,customerNotes, carrier_detail_list);

                           var notes = 'From ' + originationCity + ',' + originationState + ' to ' + destinationCity + ',' + destinationState;



                        $.ajax({
                            url: url,
                            type: type,
                            data: JSON.stringify(orderData),
                            contentType: "application/json",
                            async: false,
                            success: function(response){
                                console.log(JSON.stringify(response));
                                saveOrderDetails(response);
                                closeCustomerCommitLegs(selectedCustomerNeed.id);
                                if (differenceQty > 0) createNewAvailability(selectedCustomerNeed.id, differenceQty, today);

                                $.ajax({
                                    url: '<?php echo API_HOST_URL ?>' + '/customer_needs/' + selectedCustomerNeed.id,
                                    type: "PUT",
                                    data: JSON.stringify({status: "Closed"}),
                                    contentType: "application/json",
                                    async: false,
                                    success: function(){

                                        countUserOrders();
                                        countCommitments();
                                        closeCommitTransport();
                                    },
                                    error: function(){
                                        alert("Could not Close Customer Needs.");
                                        closeCommitTransport();
                                    }
                                });
                            },
                            error: function(){
                                alert("Purchase Order Uploaded. Unable to Complete the Order. ");
                            }

                        });

                   },
                   error: function(error) {
                      alert("Could not upload file. Purchase order not completed.");
                   }
                });
            }
            else{
                alert("This file type is not able to be uploaded. You may only upload PDF or Word Documents.");
                var input = $('#filePurchaseOrder');
                input.val('');
            }
        }
        else{
            var documentID = 0;

            var orderQty = 0;
            var differenceQty = 0;

            if(selectedCustomerNeed.qty > carrierQty) {
                orderQty = carrierQty;
                differenceQty = selectedCustomerNeed.qty - carrierQty;
                createNewAvailability(selectedCustomerNeed.id, differenceQty, today);
            }
            else {
                orderQty = selectedCustomerNeed.qty;
            }

            var url = '<?php echo API_HOST_URL ?>' + '/orders/';
            var orderData = {customerID: $("#entityID").val(), carrierIDs: carrierIDs, documentID: documentID, orderID: orderID,
                    originationAddress: selectedCustomerNeed.originationAddress1, originationCity: selectedCustomerNeed.originationCity,
                    originationState: selectedCustomerNeed.originationState, originationZip: selectedCustomerNeed.originationZip,
                    originationLng: selectedCustomerNeed.originationLng, originationLat: selectedCustomerNeed.originationLat,
                    destinationAddress: selectedCustomerNeed.destinationAddress1, destinationCity: selectedCustomerNeed.destinationCity,
                    destinationState: selectedCustomerNeed.destinationState, destinationZip: selectedCustomerNeed.destinationZip,
                    destinationLng: selectedCustomerNeed.destinationLng, destinationLat: selectedCustomerNeed.destinationLat,
                    distance: selectedCustomerNeed.distance, needsDataPoints: selectedCustomerNeed.needsDataPoints,
                    status: "Open", transportationMode: selectedCustomerNeed.transportationMode, qty: orderQty,
                    rateType: selectedCustomerNeed.rateType, customerRate: $('#customerRate').val(), carrierTotalRate: $('#carrierTotalRate').val(),
                    totalRevenue: $('#totalRevenue').val(), createdAt: today, updatedAt: today};

                var customerName = "";
                allEntities.entities.forEach(function(entity){
                    if(selectedCustomerNeed.entityID == entity.id){
                                customerName = entity.name;
                    }
                });

               var originationCity = selectedCustomerNeed.originationCity;
               var originationState = selectedCustomerNeed.originationState;
               var destinationCity = selectedCustomerNeed.destinationCity;
               var destinationState = selectedCustomerNeed.destinationState;
               var customerRate = $('#customerRate').val();
               var customerID = $("#entityID").val();
               var customerAddress = '';
               var customerCity = '';
               var customerState = '';
               var customerZip = '';
               var customerNotes = originationCity + ', ' + originationState + ' to ' + destinationCity + ', ' + destinationState;

               var customerBillingAddress = getBillingAddress(customerID);

               // Here is empty data for Customer Billing Address
               customerAddress = customerBillingAddress.address1;
               customerCity = customerBillingAddress.city;
               customerState = customerBillingAddress.state;
               customerZip = customerBillingAddress.zip;

               var customerData = customerBillingAddress;
               customerData.customerName = customerName;

               // We are not calling this yet.
               // We will wait until we have the carrier info as well.
               //var retCustomerID = addCustomerInfo(customerName,customerAddress,customerCity,customerState,customerZip,customerRate,customerNotes);
               //alert(retCustomerID);

                var needsCommitTable = $("#customer-needs-commit-table").DataTable();
                var needsCommitJSON = needsCommitTable.ajax.json();

                var customer_needs_commit = needsCommitJSON.customer_needs;
                var carrier_detail_list = new Array();
                var carrier = "";

                customer_needs_commit.forEach(function(customer_need){

                    if(customer_need.customer_needs_commit.length > 0 &&
                            customer_need.customer_needs_commit[0].status == "Close"){

                        var entityName = "";
                        var entityID = customer_need.customer_needs_commit[0].entityID;


                        allEntities.entities.forEach(function(entity){

                            if(entityID == entity.id){

                                entityName = entity.name;
                            }
                        });

                        var carrierBillingAddress = getBillingAddress(entityID);

                        var carrier_detail = {
                            carrierName: entityName,                // This is the carrier's Name
                            carrierRate: customer_need.customer_needs_commit[0].rate,    // This is that carrier's rate.
                            billingAddress: carrierBillingAddress.address1,
                            billingCity: carrierBillingAddress.city,
                            billingState: carrierBillingAddress.state,
                            billingZip: carrierBillingAddress.zip
                        };

                        carrier = carrier_detail.carrierName;
                        var carrierNotes = customerNotes;

                        // We will not be calling addVendorInfo yet. This will be nested inside addCustomerInfo
                        //addVendorInfo(carrier_detail.carrierName,carrier_detail.billingAddress,carrier_detail.billingCity,carrier_detail.billingState,carrier_detail.billingZip,carrier_detail.carrierRate,carrierNotes,retCustomerID);
                        carrier_detail_list.push(carrier_detail);
                    }
                });

                // This is a list of all the carriers accepted and associated with the commit.
                //console.log(JSON.stringify(carrier_detail_list));

               // You need the total Carrier...
               var carrierTotalRate = $('#carrierTotalRate').val();

               // Now that we have all of the carriers.
               // We will now call addCustomerInfo.
               // We do not need to wait for a return.
               addCustomerInfo(customerName,customerAddress,customerCity,customerState,customerZip,customerRate,customerNotes, carrier_detail_list);

               var notes = 'From ' + originationCity + ',' + originationState + ' to ' + destinationCity + ',' + destinationState;



            $.ajax({
                url: url,
                type: type,
                data: JSON.stringify(orderData),
                contentType: "application/json",
                async: false,
                success: function(response){
                    console.log(JSON.stringify(response));
                    saveOrderDetails(response);
                    closeCustomerCommitLegs(selectedCustomerNeed.id);
                    if (differenceQty > 0) createNewAvailability(selectedCustomerNeed.id, differenceQty, today);

                    $.ajax({
                        url: '<?php echo API_HOST_URL ?>' + '/customer_needs/' + selectedCustomerNeed.id,
                        type: "PUT",
                        data: JSON.stringify({status: "Closed"}),
                        contentType: "application/json",
                        async: false,
                        success: function(){

                            countUserOrders();
                            countCommitments();
                            closeCommitTransport();
                        },
                        error: function(){
                            alert("Could not Close Customer Needs.");
                            closeCommitTransport();
                        }
                    });
                },
                error: function(){
                    alert("Purchase Order Uploaded. Unable to Complete the Order. ");
                }

            });
        }

        return false;
    });

    $("#customer-needs-commit").css("display", "none");

    $('#datatable-table tbody').unbind('click').on( 'click', 'button', function () {

        var table = $("#datatable-table").DataTable();

        var data = table.row( $(this).parents('tr') ).data();

        if (this.textContent.indexOf("View Commits") > -1) {
            var id = data["id"];
            var rate = data["rate"];
            var entityID = data["entityID"];

            $("#customerNeedsID").val(id);
            $("#entityID").val(entityID);
            $("#customerRate").val(rate.toFixed(2));

            loadNewCustomerNeedsCommit(id);
        
        }
    });

    function closeCommitTransport(){

        $('#relayTabs > li > a').unbind('click');
        $('#unitDataBody').empty();
        $('#relayTabs').empty();
        $('#selectedRelayTabs').empty();
        $('#pickupAddress').empty();
        $('#deliveryAddress').empty();
        $('#trailerData').empty();
        $("#customer-needs-commit").css("display", "none");
        $("#customer-needs").css("display", "block");
        var table = $("#datatable-table").DataTable();
        table.ajax.reload();
    }

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

    $('#datatable-table tbody').on('click', 'td.details-control-add', function () {

    var table = $("#datatable-table").DataTable();
        var tr = $(this).closest('tr');
        var row = table.row( tr );
        var td = $(this).closest('td');

        // Open this row
        row.child( format(row.data()) ).show();
        td.addClass('details-control-minus');
        td.removeClass('details-control-add');

    } );

    $('#datatable-table tbody').on('click', 'td.details-control-minus', function () {

    var table = $("#datatable-table").DataTable();
        var tr = $(this).closest('tr');
        var row = table.row( tr );
        var td = $(this).closest('td');

        // This row is already open - close it
        row.child.hide();
        td.removeClass('details-control-minus');
        td.addClass('details-control-add');

    } );

    function editCommitTransport(){
        
        $("#editCommitModal").modal('show');
    }
    
    function saveCommit(){
        
        var unitDataList = [];
        
        $('#addTrailer > div').each(function(index, value){
            var unitID = index + 1;
            var unitNumber = $('#unitNumber' + unitID).val().trim();
            var vinNumber = $('#vinNumber' + unitID).val().trim();
            var truckProNumber = $('#truckProNumber' + unitID).val().trim();
            var poNumber = $('#poNumber' + unitID).val().trim();
           
            if(vinNumber != "" || unitNumber != "" || truckProNumber != "" || poNumber != ""){
                var unitData = {unitNumber: unitNumber, vinNumber: vinNumber, truckProNumber: truckProNumber, poNumber: poNumber}; 
                
                unitDataList.push(unitData);
            }
        });
        
        if(unitDataList.length > 0){
          $("#saveCommit").html("<i class='fa fa-spinner fa-spin'></i> Updating Commit");
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

        var id = $('#customerNeedsID').val();
        
        var pickupInformation = {pickupLocation: $('#pickupLocation').val().trim(), contactPerson: $('#pickupContactPerson').val().trim(),
                                phoneNumber: $('#pickupPhoneNumber').val().trim(), hoursOfOperation: $('#pickupHoursOfOperation').val().trim()};
        
        var deliveryInformation = {deliveryLocation: $('#deliveryLocation').val().trim(), contactPerson: $('#deliveryContactPerson').val().trim(),
                                phoneNumber: $('#deliveryPhoneNumber').val().trim(), hoursOfOperation: $('#deliveryHoursOfOperation').val().trim()};
        
        var originationAddress1 = $('#originationAddress1').val().trim();
        var originationAddress2 = $('#originationAddress2').val().trim();
        var originationCity = $('#originationCity').val().trim();
        var originationState = $('#originationState').val().trim();
        var originationZip = $('#originationZip').val().trim();
        var originationNotes = $('#originationNotes').val().trim();
        
        var destinationAddress1 = $('#destinationAddress1').val().trim();
        var destinationAddress2 = $('#destinationAddress2').val().trim();
        var destinationCity = $('#destinationCity').val().trim();
        var destinationState = $('#destinationState').val().trim();
        var destinationZip = $('#destinationZip').val().trim();
        var destinationNotes = $('#destinationNotes').val().trim();
        
        
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
        
        var data = {pickupInformation: pickupInformation, originationAddress1: originationAddress1, originationAddress2: originationAddress2, originationCity: originationCity, originationState: originationState, originationZip: originationZip, originationNotes: originationNotes,
                    deliveryInformation: deliveryInformation, destinationAddress1: destinationAddress1, destinationAddress2: destinationAddress2, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, destinationNotes: destinationNotes,
                    qty: qty, updatedAt: today, needsDataPoints: needsdatapoints, unitData: unitDataList, rate: rate, rateType: rateType, transportationMode: transportationMode};
        
        
        var url = '<?php echo API_HOST_URL . "/customer_needs" ?>/' + id;
        
        $.ajax({
            url: url,
            type: "PUT",
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
                        var commitID = $('#commit_id' + relayNumber).val().trim();
                        var destinationAddress1 = $('#address_relay' + relayNumber).val().trim();
                        var destinationCity = $('#city_relay' + relayNumber).val().trim();
                        var destinationState = $('#state_relay' + relayNumber).val().trim();
                        var destinationZip = $('#zip_relay' + relayNumber).val().trim();
                        var destinationNotes = $('#notes_relay' + relayNumber).val().trim();

                        var deliveryInformation = {deliveryLocation: $('#deliveryLocation_relay' + relayNumber).val().trim(), contactPerson: $('#contactPerson_relay' + relayNumber).val().trim(),
                                                phoneNumber: $('#phoneNumber_relay' + relayNumber).val().trim(), hoursOfOperation: $('#hoursOfOperation_relay' + relayNumber).val().trim()};

                        if(destinationCity != "" && destinationState != ""){
                            
                            if(relayID == ""){
                                url = '<?php echo API_HOST_URL . "/customer_needs" ?>/';
                                type = "POST";
                                relayData = {rootCustomerNeedsID: id, pickupInformation: pickupInformation, originationAddress1: originationAddress1, originationAddress2: originationAddress2, originationCity: originationCity, originationState: originationState, originationZip: originationZip, originationNotes: originationNotes,
                                    deliveryInformation: deliveryInformation, destinationAddress1: destinationAddress1, destinationAddress2: destinationAddress2, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, destinationNotes: destinationNotes,
                                    qty: qty, createdAt: today, updatedAt: today, needsDataPoints: needsdatapoints,  unitData: unitDataList, rate: rate, rateType: rateType, transportationMode: transportationMode};
                            }
                            else{ 
                                url = '<?php echo API_HOST_URL . "/customer_needs" ?>/' + relayID;
                                type = "PUT";
                                relayData = {rootCustomerNeedsID: id, pickupInformation: pickupInformation,  originationAddress1: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip, originationNotes: originationNotes,
                                    deliveryInformation: deliveryInformation, destinationAddress1: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, destinationNotes: destinationNotes,
                                    qty: qty, updatedAt: today, needsDataPoints: needsdatapoints,  unitData: unitDataList, rate: rate, rateType: rateType, transportationMode: transportationMode};
                            }
                            
                            $.ajax({
                                url: url,
                                type: type,
                                data: JSON.stringify(relayData),
                                contentType: "application/json",
                                async: false,
                                success: function(data){
                                    if(data > 0){
                                        
                                        url = '<?php echo API_HOST_URL . "/customer_needs_commit" ?>/';
                                        
                                        if(type == "POST") relayID = data;
                                        else url += commitID;
                                        
                                        var commitData = {customerNeedsID: relayID, originationAddress1: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                                                        destinationAddress1: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, status: "Available",
                                                        originationLng: "", originationLat: "", destinationLng: "", destinationLat: "", distance: 0, qty: qty, transportation_mode: "", transportation_type: "", updatedAt: today};
                                        
                                        $.ajax({
                                            url: url,
                                            type: type,
                                            data: JSON.stringify(commitData),
                                            success: function(data){
                                                if(data > 0){                                                    
                                                    originationAddress1 = destinationAddress1;
                                                    originationCity = destinationCity;
                                                    originationState = destinationState;
                                                    originationZip = destinationZip;
                                                    originationNotes = destinationNotes;
                                                    pickupInformation = deliveryInformation;
                                                }
                                            },
                                            error: function(){
                                                alert("Unable to save to customer_needs_commit");
                                            }
                                        });
                                        
                                    }
                                },
                                error: function(){
                                    alert("unable to save relay.");
                                }
                            });
                        }
                        else if(relayID != ""){
                        
                            console.log(relayID);

                            url = '<?php echo API_HOST_URL . "/customer_needs" ?>/' + relayID;
                            var statusChange = {status: "Close"};
                            
                            $.ajax({
                            url: url,
                            type: "PUT",
                            data: JSON.stringify(statusChange),
                            success: function(data){
                                if(data > 0){                           
                                    alert("customer need closed.");
                                }
                                else{
                                    
                                }
                            },
                            error: function(){
                                alert("Unable to save to customer_needs_commit");
                            }
                        });
                            
                        }
                    }    
                    
                    $("#saveCommit").html("Save");
                    $("#saveCommit").prop("disabled", false);
                    $("#editCommitModal").modal('hide');
                    loadNewCustomerNeedsCommit(id);
                    alert("Commit Updated");
                }
                else{
                    console.log(data);
                }
            },
            error: function(data){
                alert("There Was An Error Updating Commit");
            }
        });
        }
        else{
            alert("You must enter at least ONE Trailer.");
        }
    }
    
    
    function saveCommitAsOrder(){
        
        $(document.body).css("cursor", "wait");
        
        var unitDataList = [];
        
        $('#addTrailer > div').each(function(index, value){
            var unitID = index + 1;
            var unitNumber = $('#unitNumber' + unitID).val().trim();
            var vinNumber = $('#vinNumber' + unitID).val().trim();
            var truckProNumber = $('#truckProNumber' + unitID).val().trim();
            var poNumber = $('#poNumber' + unitID).val().trim();
           
            if(vinNumber != "" || unitNumber != "" || truckProNumber != "" || poNumber != ""){
                var unitData = {unitNumber: unitNumber, vinNumber: vinNumber, truckProNumber: truckProNumber, poNumber: poNumber}; 
                
                unitDataList.push(unitData);
            }
        });
        
        if(unitDataList.length > 0){
            var today = new Date();
            var orderID = today.getTime().toString();
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

            var id = $('#customerNeedsID').val();

            var pickupInformation = {pickupLocation: $('#pickupLocation').val().trim(), contactPerson: $('#pickupContactPerson').val().trim(),
                                    phoneNumber: $('#pickupPhoneNumber').val().trim(), hoursOfOperation: $('#pickupHoursOfOperation').val().trim()};

            var deliveryInformation = {deliveryLocation: $('#deliveryLocation').val().trim(), contactPerson: $('#deliveryContactPerson').val().trim(),
                                    phoneNumber: $('#deliveryPhoneNumber').val().trim(), hoursOfOperation: $('#deliveryHoursOfOperation').val().trim()};

            var originationAddress1 = $('#originationAddress1').val().trim();
            var originationAddress2 = $('#originationAddress2').val().trim();
            var originationCity = $('#originationCity').val().trim();
            var originationState = $('#originationState').val().trim();
            var originationZip = $('#originationZip').val().trim();
            var originationNotes = $('#originationNotes').val().trim();

            var destinationAddress1 = $('#destinationAddress1').val().trim();
            var destinationAddress2 = $('#destinationAddress2').val().trim();
            var destinationCity = $('#destinationCity').val().trim();
            var destinationState = $('#destinationState').val().trim();
            var destinationZip = $('#destinationZip').val().trim();
            var destinationNotes = $('#destinationNotes').val().trim();


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
            var customerID = $('#customerID').val();

            var orderData = {customerID: customerID, carrierIDs: [{carrierID: 0}], orderID: orderID, deliveryInformation: deliveryInformation, pickupInformation: pickupInformation, originationAddress: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                        destinationAddress: destinationAddress1,  destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, originationLng: "", originationLat: "", destinationLng: "", destinationLat: "", distance: 0, needsDataPoints: needsdatapoints, podList: unitDataList, 
                        comments: "", createdAt: today, updatedAt: today, qty: qty};

            var url = '<?php echo API_HOST_URL . "/orders" ?>';

            $.ajax({
                url: url,
                type: "POST",
                data: JSON.stringify(orderData),
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
                            var commitID = $('#commit_id' + relayNumber).val().trim();
                            var destinationAddress1 = $('#address_relay' + relayNumber).val().trim();
                            var destinationCity = $('#city_relay' + relayNumber).val().trim();
                            var destinationState = $('#state_relay' + relayNumber).val().trim();
                            var destinationZip = $('#zip_relay' + relayNumber).val().trim();
                            var destinationNotes = $('#notes_relay' + relayNumber).val().trim();

                            var deliveryInformation = {deliveryLocation: $('#deliveryLocation_relay' + relayNumber).val().trim(), contactPerson: $('#contactPerson_relay' + relayNumber).val().trim(),
                                                    phoneNumber: $('#phoneNumber_relay' + relayNumber).val().trim(), hoursOfOperation: $('#hoursOfOperation_relay' + relayNumber).val().trim()};

                            if(destinationCity != "" && destinationState != ""){

                                    url = '<?php echo API_HOST_URL . "/order_details" ?>/';
                                    type = "POST";
                                    relayData = {carrierID: 0, orderID: data, pickupInformation: pickupInformation, originationAddress: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                                        deliveryInformation: deliveryInformation, destinationAddress: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, carrierRate: 0.00, transportationMode: "",
                                        qty: qty, createdAt: today, updatedAt: today, needsDataPoints: needsdatapoints, status: "Open"};

                                $.ajax({
                                    url: url,
                                    type: type,
                                    data: JSON.stringify(relayData),
                                    contentType: "application/json",
                                    async: false,
                                    success: function(data){
                                        if(data > 0){
                                            originationAddress1 = destinationAddress1;
                                            originationCity = destinationCity;
                                            originationState = destinationState;
                                            originationZip = destinationZip;
                                            pickupInformation = deliveryInformation;
                                        }
                                    },
                                    error: function(){
                                        alert("unable to save relay.");
                                    }
                                });
                            }
                        }    

                        closeCustomerCommitLegs(id);
                        $(document.body).css("cursor", "default");
                        alert("Order Saved.");
                        closeCommitTransport();
                    }
                    else{
                        $(document.body).css("cursor", "default");

                    }
                },
                error: function(data){

                    alert("There Was An Error Saving Order");
                }
            });
        }
        else{
            alert("You must enter at least ONE Trailer.");
        }
    }
 </script>
