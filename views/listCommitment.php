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

$dataPoints = json_decode(file_get_contents(API_HOST_URL . "/object_type_data_points?include=object_type_data_point_values&transform=1&columns=id,columnName,title,status,object_type_data_point_values.value&filter[]=entityID,in,(0," . $_SESSION['entityid'] . ")&filter[]=status,eq,Active&order=sort_order" ));

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

    var entityid = <?php echo $_SESSION['entityid']; ?>;

    var allEntities = <?php echo json_encode($allEntities); ?>;

    var customerNeedsRootIDs = <?php echo json_encode($customer_needs_root)?>;

    var carrierIDs = new Array();

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

        var strMessage = "The following fields must be entered:<br>";

       if($('#pickupDate').val() == "") {
           strMessage += "-Pick-Up Date<br>";
           blnResult = false;
       }
       if($('#deliveryDate').val() == "") {
           strMessage += "-Delivery Date<br>";
           blnResult = false;
       }
       if($('#carrierID').val() == "") {
           strMessage += "-Carrier<br>";
           blnResult = false;
       }
       if($('#originationCity').val() == "") {
           strMessage += "-Origination City<br>";
           blnResult = false;
       }
       if($('#originationState').val() == "") {
           strMessage += "-Origination State<br>";
           blnResult = false;
       }
       if($('#destinationCity').val() == "") {
           strMessage += "-Destination City<br>";
           blnResult = false;
       }
       if($('#destinationState').val() == "") {
           strMessage += "-Destination State<br>";
           blnResult = false;
       }

       if(blnResult == false){
           //alert(strMessage);
            $("#errorAlertTitle").html("Error");
            $("#errorAlertBody").html(strMessage);
            $("#errorAlert").modal('show');
       }

       return blnResult;
    }

      function post() {

          //var originationaddress = $("#originationAddress1").val() + ', ' + $("#originationCity").val() + ', ' + $("#originationState").val() + ', ' + $("#originationZip").val();
          //var destinationaddress = $("#destinationAddress1").val() + ', ' + $("#destinationCity").val() + ', ' + $("#destinationState").val() + ', ' + $("#destinationZip").val();
          var originationaddress = $("#originationCity").val() + ', ' + $("#originationState").val();
          var destinationaddress = $("#destinationCity").val() + ', ' + $("#destinationState").val();

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

                $.ajax({
                   url: '<?php echo HTTP_HOST."/getlocationbycitystatezip" ?>',
                   type: 'POST',
                   data: JSON.stringify(params),
                   contentType: "application/json",
                   async: false,
                   success: function(response){

                      if (response == "success") {

                          var params = {
                                address1: $("#destinationAddress1").val(),
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
                                    if (response == "ZERO_RESULTS") {
                                        //alert("Destination Address does not exist!");
                                        $("#errorAlertTitle").html("Error");
                                        $("#errorAlertBody").html("Destination Address does not exist!");
                                        $("#errorAlert").modal('show');
                                    } else {
                                        //alert("Destination Address Error: " + JSON.stringify(response));
                                        $("#errorAlertTitle").html("Destination Address Error");
                                        $("#errorAlertBody").html(JSON.stringify(response));
                                        $("#errorAlert").modal('show');
                                    }
                                    result = false;
                                }
                             },
                             error: function(response) {
                                if (response == "ZERO_RESULTS") {
                                    //alert("Destination Address does not exist!");
                                    $("#errorAlertTitle").html("Error");
                                    $("#errorAlertBody").html("Destination Address does not exist!");
                                    $("#errorAlert").modal('show');
                                } else {
                                    //alert("Destination Address Error: " + JSON.stringify(response));
                                    $("#errorAlertTitle").html("Destination Address Error");
                                    $("#errorAlertBody").html(JSON.stringify(response));
                                    $("#errorAlert").modal('show');
                                }
                                result = false;
                             }
                          });
                      } else {
                          if (response == "ZERO_RESULTS") {
                              alert("Origination Address does not exist!");
                                $("#errorAlertTitle").html("Error");
                                $("#errorAlertBody").html("Origination Address does not exist!");
                                $("#errorAlert").modal('show');
                          } else {
                              //alert("Origination Address Error: " + JSON.stringify(response));
                                $("#errorAlertTitle").html("Origination Address Error");
                                $("#errorAlertBody").html(JSON.stringify(response));
                                $("#errorAlert").modal('show');
                          }
                          result = false;
                      }
                   },
                   error: function(response) {
                      //alert("Issue With Origination Address: " + JSON.stringify(response));
                        $("#errorAlertTitle").html("Issue With Origination Address");
                        $("#errorAlertBody").html(JSON.stringify(response));
                        $("#errorAlert").modal('show');
                      result = false;
                   }
                });

                if (result) {
                    verifyAndPost(function(data) {
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
                //alert("Unable to get billing Address");
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Unable to get billing Address");
                $("#errorAlert").modal('show');
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
                                        $("#load").html("Commit");
                                        $("#load").prop("disabled", false);
                                         $("#myModalCommit").modal('hide');
                                     },
                                     error: function() {
                                        //alert('Failed creating a new Need from an existing.');

                                        $("#errorAlertTitle").html("Error");
                                        $("#errorAlertBody").html("Failed creating a new Need from an existing.");
                                        $("#errorAlert").modal('show');

                                        $("#load").html("Commit");
                                        $("#load").prop("disabled", false);
                                        $("#myModalCommit").modal('hide');
                                     }
                                  });
                              //}

                            $("#load").html("Commit");
                            $("#load").prop("disabled", false);
                              $("#myModal").modal('hide');
                              //loadCustomerNeedsCommitAJAX ($("#id").val());
                              getCommitted();
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

        //var url = '<?php echo API_HOST_URL; ?>' + '/customer_needs?columns=id,rootCustomerNeedsID&filter[]=rootCustomerNeedsID,neq,0&filter[]=status,eq,Available&transform=1';
        var url = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=customer_needs_commit&filter[]=status,eq,Available&transform=1';

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
                //alert("There Was An Error Saving the Status");
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("There Was An Error Saving The Status");
                $("#errorAlert").modal('show');
           }
        });

    }

    function clearCommitForm(){

        $('#commitModalTitle').empty();
        $('#dp-check-list-box').empty();
            $('#customerID').val("");

            $('#pickupLocation').val("");
            $('#pickupContactPerson').val("");
            $('#pickupPhoneNumber').val("");
            $('#pickupHoursOfOperation').val("");
            $('#originationAddress1').val("");
            $('#originationAddress2').val("");
            $('#originationCity').val("");
            $('#originationState').val("");
            $('#originationZip').val("");
            $('#originationNotes').val("");

            $('#deliveryLocation').val("");
            $('#deliveryContactPerson').val("");
            $('#deliveryPhoneNumber').val("");
            $('#deliveryHoursOfOperation').val("");
            $('#destinationAddress1').val("");
            $('#destinationAddress2').val("");
            $('#destinationCity').val("");
            $('#destinationState').val("");
            $('#destinationZip').val("");
            $('#destinationNotes').val("");
            $('#unitDataBody').empty();

        for(var relayNumber = 1; relayNumber <=4 ; relayNumber ++){
            $('#relay_id' + relayNumber).val("");
            $('#commit_id' + relayNumber).val("");
            $('#entityID_relay' + relayNumber).val("");
            $('#address_relay' + relayNumber).val("");
            $('#city_relay' + relayNumber).val("");
            $('#state_relay' + relayNumber).val("");
            $('#zip_relay' + relayNumber).val("");
            $('#notes_relay' + relayNumber).val("");
            
            $('#pickupDate_relay' + relayNumber).val("");
            $('#deliveryDate_relay' + relayNumber).val("");
            $('#rate_relay' + relayNumber).val("");

            $('#originationLng_relay' + relayNumber).val("");
            $('#originationLat_relay' + relayNumber).val("");
            $('#destinationLng_relay' + relayNumber).val("");
            $('#destinationLat_relay' + relayNumber).val("");
            $('#distance_relay' + relayNumber).val("");

            $('#deliveryLocation_relay' + relayNumber).val("");
            $('#contactPerson_relay' + relayNumber).val("");
            $('#phoneNumber_relay' + relayNumber).val("");
            //$('#hoursOfOperation_relay' + relayNumber).val("");
            $('#hoursOfOperationOpen_relay' + relayNumber).val("");
            $('#hoursOfOperationClose_relay' + relayNumber).val("");
            $('#timeZone_relay' + relayNumber).val("");
            
        }
            
            var dpli = '<div class="form-group row">' +
                        '   <div class="col-sm-2">' +
                            '<label for="qty">Quantity</label>'+
                            '<input id="qty" name="qty" class="form-control" value="">'+
                        '   </div>';

            var itemIndex = 1;

            for (var i = 0; i < dataPoints.object_type_data_points.length; i++) {
                
                if(dataPoints.object_type_data_points[i].title == "Decals"){
                    dpli += '<div class="col-sm-2">' +
                            '<label for="decals">Decals</label>'+
                            '<input id="decals" name="decals" class="form-control" value="">'+
                            '</div>';
                  }
                  else{

                    dpli += '<div class="col-sm-2">' +
                            '<label for="' + dataPoints.object_type_data_points[i].columnName + '">' + dataPoints.object_type_data_points[i].title + '</label>' +
                            '<select class="form-control" id="' + dataPoints.object_type_data_points[i].columnName + '" name="' + dataPoints.object_type_data_points[i].columnName + '">' +
                            ' <option value="">-Select From List-</option>\n';

                    for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {

                        dpli += '<option>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</option>\n';

                    }

                    dpli += '</select>' +
                            '</div>';
                  }

                  itemIndex += 1;

                  if(itemIndex % 6 == 0) dpli += '</div><div class="form-group row">';
            }


            dpli += '<div class="col-sm-2">' +
                    '   <label for="rate">Rate</label>' +
                    '       <input id="rate" name="rate" class="form-control" value="">' +
                    '</div>';

            dpli += '<div class="col-sm-2">' +
                    '   <label for="rateType">Rate Type</label><br>' +
                    '       <input type="radio" id="rateType" name="rateType" value="Flat Rate"/> Flat Rate ' +
                    '       <input type="radio" id="rateType" name="rateType" value="Mileage"/> Mileage' +
                    '   </div>';

            dpli += '<div class="col-sm-2">' +
                    '   <label for="transportationMode">Transportation Mode</label>' +
                    '       <select class="form-control" id="transportationMode" name="transportationMode">' +
                    '           <option value="">*Select Mode...</option>' +
                    '           <option value="Empty">Empty</option>' +
                    '           <option value="Load Out">Load Out</option>' +
                    '           <option value="Either (Empty or Load Out)">Either (Empty or Load Out)</option>' +
                    '       </select>' +
                    '   </div>'+
                    '</div>';

            $("#dp-check-list-box").append(dpli);


    }

    function loadTableAJAX(committed) {

        var baseUrl = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=customer_needs_commit,entities&columns=entities.name,id,rootCustomerNeedsID,entityID,qty,rate,availableDate,expirationDate,transportationMode,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,createdAt,customer_needs_commit.id,customer_needs_commit.status,customer_needs_commit.rate,customer_needs_commit.transporation_mode,entities.rateType,entities.negotiatedRate&filter[0]=id,in,(0,' + committed + ')&filter[1]=status,eq,Available';

        var url = baseUrl + '&order=updatedAt,desc&transform=1';

        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            bsort: true,
            "pageLength": 50,
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
                },
                { data: "createdAt", visible: false}

            ],
            order: [[33, "desc"]]
            //scrollX: true
          });

          example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );

        //To Reload The Ajax
        //See DataTables.net for more information about the reload method
        example_table.ajax.reload();

      }

    function loadNewCustomerNeedsCommit(id){

        $('#commitModalTitle').empty();
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

        var baseUrl = '<?php echo API_HOST_URL; ?>' + '/customer_needs/' + id + '?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,rate,rateType,availableDate,expirationDate,deliveryInformation,pickupInformation,transportationMode,originationAddress1,originationAddress2,originationCity,originationState,originationZip,originationNotes,destinationNotes,originationLat,originationLng,destinationAddress1,destinationAddress2,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,unitData,status,customer_needs_commit.id,customer_needs_commit.entityID,customer_needs_commit.status,customer_needs_commit.rate,customer_needs_commit.transportation_mode,entities.name,entities.rateType,entities.negotiatedRate';

        var url = baseUrl + '&satisfy=any&order[]=entityID&order[]=rootCustomerNeedsID&order[]=availableDate,desc&transform=1';

        var relayURL = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,rate,availableDate,expirationDate,deliveryInformation,pickupInformation,transportationMode,rate,originationAddress1,originationCity,originationState,originationZip,originationNotes,destinationNotes,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,unitData,status,customer_needs_commit.id,customer_needs_commit.entityID,customer_needs_commit.status,customer_needs_commit.pickupDate,customer_needs_commit.deliveryDate,customer_needs_commit.rate,customer_needs_commit.transportation_mode,customer_needs_commit.originationLng,customer_needs_commit.originationLat,customer_needs_commit.destinationLng,customer_needs_commit.destinationLat,customer_needs_commit.distance,customer_needs_commit.rateType,entities.name,entities.rateType,customer_needs_commit.qty,entities.negotiatedRate&filter[]=rootCustomerNeedsID,eq,' + id + '&filter[]=status,eq,Available&satisfy=all&order[]=id&transform=1';

        $.get(url, function(data){

            var customer_needs = data;
            var needsDataPoints = customer_needs.needsDataPoints;

            //if(customer_needs.deliveryInformation == null) customer_needs.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
            //if(customer_needs.pickupInformation == null) customer_needs.pickupInformation = {pickupLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
            if(customer_needs.deliveryInformation == null) customer_needs.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", deliveryHoursOfOperationOpen: "", deliveryHoursOfOperationClose: "", deliveryTimeZone: ""};
            if(customer_needs.pickupInformation == null) customer_needs.pickupInformation = {pickupLocation: "", contactPerson: "", phoneNumber: "", pickupHoursOfOperationOpen: "", pickupHoursOfOperationClose: "", pickupTimeZone: ""};
            if(customer_needs.originationAddress1 == null) customer_needs.originationAddress1 = "";
            if(customer_needs.destinationAddress1 == null) customer_needs.destinationAddress1 = "";
            if(customer_needs.originationAddress2 == null) customer_needs.originationAddress2 = "";
            if(customer_needs.destinationAddress2 == null) customer_needs.destinationAddress2 = "";
            if(customer_needs.originationZip == null) customer_needs.originationZip = "";
            if(customer_needs.destinationZip == null) customer_needs.destinationZip = "";
            if(customer_needs.originationLng == null) customer_needs.originationLng = "";
            if(customer_needs.destinationLng == null) customer_needs.destinationLng = "";
            if(customer_needs.originationLat == null) customer_needs.originationLat = "";
            if(customer_needs.destinationLat == null) customer_needs.destinationLat = "";
            if(customer_needs.distance == null) customer_needs.distance = "";

            if(customer_needs.pickupInformation.pickupHoursOfOperationOpen == null) customer_needs.pickupInformation.pickupHoursOfOperationOpen = "";
            if(customer_needs.pickupInformation.pickupHoursOfOperationClose == null) customer_needs.pickupInformation.pickupHoursOfOperationClose = "";
            if(customer_needs.pickupInformation.pickupTimeZone == null) customer_needs.pickupInformation.pickupTimeZone = "";
            
            if(customer_needs.deliveryInformation.deliveryHoursOfOperationOpen == null) customer_needs.deliveryInformation.deliveryHoursOfOperationOpen = "";
            if(customer_needs.deliveryInformation.deliveryHoursOfOperationClose == null) customer_needs.deliveryInformation.deliveryHoursOfOperationClose = "";
            if(customer_needs.deliveryInformation.deliveryTimeZone == null) customer_needs.deliveryInformation.deliveryTimeZone = "";

            if(customer_needs.unitData == null) customer_needs.unitData = [];

            $('#customerID').val(customer_needs.entityID);

            // Populate Edit form
            $('#pickupLocation').val(customer_needs.pickupInformation.pickupLocation);
            $('#pickupContactPerson').val(customer_needs.pickupInformation.contactPerson);
            $('#pickupPhoneNumber').val(customer_needs.pickupInformation.phoneNumber);
            //$('#pickupHoursOfOperation').val(customer_needs.pickupInformation.hoursOfOperation);
            $('#pickupHoursOfOperationOpen').val(customer_needs.pickupInformation.pickupHoursOfOperationOpen);
            $('#pickupHoursOfOperationClose').val(customer_needs.pickupInformation.pickupHoursOfOperationClose);
            $('#pickupTimeZone').val(customer_needs.pickupInformation.pickupTimeZone);
            $('#originationAddress1').val(customer_needs.originationAddress1);
            $('#originationAddress2').val(customer_needs.originationAddress2);
            $('#originationCity').val(customer_needs.originationCity);
            $('#originationState').val(customer_needs.originationState);
            $('#originationZip').val(customer_needs.originationZip);
            $('#originationLng').val(customer_needs.originationLng);
            $('#originationLat').val(customer_needs.originationLat);
            $('#originationNotes').val(customer_needs.originationNotes);


            $('#deliveryLocation').val(customer_needs.deliveryInformation.deliveryLocation);
            $('#deliveryContactPerson').val(customer_needs.deliveryInformation.contactPerson);
            $('#deliveryPhoneNumber').val(customer_needs.deliveryInformation.phoneNumber);
            //$('#deliveryHoursOfOperation').val(customer_needs.deliveryInformation.hoursOfOperation);
            $('#deliveryHoursOfOperationOpen').val(customer_needs.deliveryInformation.deliveryHoursOfOperationOpen);
            $('#deliveryHoursOfOperationClose').val(customer_needs.deliveryInformation.deliveryHoursOfOperationClose);
            $('#deliveryTimeZone').val(customer_needs.deliveryInformation.deliveryTimeZone);
            $('#destinationAddress1').val(customer_needs.destinationAddress1);
            $('#destinationAddress2').val(customer_needs.destinationAddress2);
            $('#destinationCity').val(customer_needs.destinationCity);
            $('#destinationState').val(customer_needs.destinationState);
            $('#destinationZip').val(customer_needs.destinationZip);
            $('#destinationLng').val(customer_needs.destinationLng);
            $('#destinationLat').val(customer_needs.destinationLat);
            $('#destinationNotes').val(customer_needs.destinationNotes);

            $('#distance').val(customer_needs.distance);

            var pickupInformation = "";
            var deliveryInformation = "";

            // Populate view
            //if(customer_needs.pickupInformation.pickupLocation != "" && customer_needs.pickupInformation.contactPerson != "" && customer_needs.pickupInformation.phoneNumber != "" && customer_needs.pickupInformation.hoursOfOperation != "" ){
//            if(customer_needs.pickupInformation.pickupLocation != "" && customer_needs.pickupInformation.contactPerson != "" && customer_needs.pickupInformation.phoneNumber != "" && customer_needs.pickupInformation.hoursOfOperationOpen != "" && customer_needs.pickupInformation.hoursOfOperationClose != ""){
//
//                pickupInformation = customer_needs.pickupInformation.pickupLocation + "<br>"
//                        + customer_needs.pickupInformation.contactPerson + "<br>"
//                        + customer_needs.pickupInformation.phoneNumber + "<br>"
//                        //+ customer_needs.pickupInformation.hoursOfOperation + "<br><br>";
//                        + customer_needs.pickupInformation.pickupHoursOfOperationOpen + " to " + customer_needs.pickupInformation.pickupHoursOfOperationClose + " " + customer_needs.pickupInformation.pickupTimeZone + "<br><br>";
//            }

            var pickupAddress = ( customer_needs.pickupInformation.pickupLocation.trim() != "" ? customer_needs.pickupInformation.pickupLocation + "<br>" : "Unknown Location<br>")
                              + ( customer_needs.originationAddress1.trim() != "" ? customer_needs.originationAddress1 + "<br>" : "Unknown Address<br>")
                              + ( customer_needs.originationCity.trim() != "" ? customer_needs.originationCity : "Unknown City")
                              + ", " 
                              + ( customer_needs.originationState.trim() != "" ? customer_needs.originationState : "Unknown State") 
                              + " " 
                              + ( customer_needs.originationZip.trim() != "" ? customer_needs.originationZip : "Unknown Zip") + "<br>"
                              + ( customer_needs.pickupInformation.contactPerson.trim() != "" ? customer_needs.pickupInformation.contactPerson + "<br>" : "Unknown Contact<br>")
                              + ( customer_needs.pickupInformation.phoneNumber.trim() != "" ? customer_needs.pickupInformation.phoneNumber + "<br>" : "Unknown Phone Number<br>")
                              + ( customer_needs.pickupInformation.pickupHoursOfOperationOpen.trim() != "" ? customer_needs.pickupInformation.pickupHoursOfOperationOpen  : "N/A")
                              + " to "
                              + ( customer_needs.pickupInformation.pickupHoursOfOperationClose.trim() != "" ? customer_needs.pickupInformation.pickupHoursOfOperationClose  : "N/A")
                              + " "
                              + ( customer_needs.pickupInformation.pickupTimeZone.trim() != "" ? customer_needs.pickupInformation.pickupTimeZone  + "<br><br>" : "Unknown Time Zone<br><br>");




            var deliveryAddress = ( customer_needs.deliveryInformation.deliveryLocation.trim() != "" ? customer_needs.deliveryInformation.deliveryLocation + "<br>" : "Unknown Location<br>")
                              + ( customer_needs.destinationAddress1.trim() != "" ? customer_needs.destinationAddress1 + "<br>" : "Unknown Address<br>")
                              + ( customer_needs.destinationCity.trim() != "" ? customer_needs.destinationCity : "Unknown City")
                              + ", " 
                              + ( customer_needs.destinationState.trim() != "" ? customer_needs.destinationState : "Unknown State") 
                              + " " 
                              + ( customer_needs.destinationZip.trim() != "" ? customer_needs.destinationZip : "Unknown Zip") + "<br>"
                              + ( customer_needs.deliveryInformation.contactPerson.trim() != "" ? customer_needs.deliveryInformation.contactPerson + "<br>" : "Unknown Contact<br>")
                              + ( customer_needs.deliveryInformation.phoneNumber.trim() != "" ? customer_needs.deliveryInformation.phoneNumber + "<br>" : "Unknown Phone Number<br>")
                              + ( customer_needs.deliveryInformation.deliveryHoursOfOperationOpen.trim() != "" ? customer_needs.deliveryInformation.deliveryHoursOfOperationOpen  : "N/A")
                              + " to "
                              + ( customer_needs.deliveryInformation.deliveryHoursOfOperationClose.trim() != "" ? customer_needs.deliveryInformation.deliveryHoursOfOperationClose  : "N/A")
                              + " "
                              + ( customer_needs.deliveryInformation.deliveryTimeZone.trim() != "" ? customer_needs.deliveryInformation.deliveryTimeZone  + "<br><br>" : "Unknown Time Zone<br><br>");


//            if(customer_needs.deliveryInformation.deliveryLocation != "" && customer_needs.deliveryInformation.contactPerson != "" && customer_needs.deliveryInformation.phoneNumber != "" && customer_needs.deliveryInformation.hoursOfOperation != "" ){
//
//                deliveryInformation = customer_needs.deliveryInformation.deliveryLocation + "<br>"
//                        + customer_needs.deliveryInformation.contactPerson + "<br>"
//                        + customer_needs.deliveryInformation.phoneNumber + "<br>"
//                        //+ customer_needs.deliveryInformation.hoursOfOperation + "<br><br>";
//                        + customer_needs.deliveryInformation.deliveryHoursOfOperationOpen + " to " + customer_needs.deliveryInformation.deliveryHoursOfOperationClose + " " + customer_needs.deliveryInformation.deliveryTimeZone + "<br><br>";
//
//            }

//            var pickupAddress = pickupInformation + customer_needs.originationAddress1 + "<br>" +
//                    customer_needs.originationCity + ", " + customer_needs.originationState + " " + customer_needs.originationZip + "<br><br>";

//            var deliveryAddress = deliveryInformation + customer_needs.destinationAddress1 + "<br>" +
//                    customer_needs.destinationCity + ", " + customer_needs.destinationState + " " + customer_needs.destinationZip + "<br><br>";

            if(customer_needs.originationNotes != ""){
                    pickupAddress  += "Notes:<br>" +
                                customer_needs.originationNotes + "<br>";
            }

            if(customer_needs.destinationNotes != ""){
                    deliveryAddress  += "Notes:<br>" +
                                customer_needs.destinationNotes + "<br>";
            }

            var trailerData = "Quantity: <strong>" + customer_needs.qty + "</strong><br>"
                            + "Rate: <strong>" + customer_needs.rate + "</strong><br>"
                            + "Rate Type: <strong>" + customer_needs.rateType + "</strong><br>"
                            + "Transportation Mode: <strong>" + customer_needs.transportationMode + "</strong><br>";

            var unitData = "";
            var unitEdit = "";

            $.each(customer_needs.unitData, function(key, unit){

                if(unit.year == null) unit.year = "";
                if(unit.make == null) unit.make = "";
                if(unit.licenseNumber == null) unit.licenseNumber = "";
                if(unit.value == null) unit.value = "";

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

            var dpli = '<div class="form-group row">' +
                            '<label for="qty" class="col-sm-3 col-form-label">Quantity</label>'+
                            '<div class="col-sm-9">' +
                            '<input id="qty" name="qty" class="form-control" value="'+customer_needs.qty+'">'+
                            '</div>'+
                            '</div>';


            var dpli = '<div class="form-group row">' +
                        '   <div class="col-sm-2">' +
                            '<label for="qty">Quantity</label>'+
                            '<input id="qty" name="qty" class="form-control" value="'+customer_needs.qty+'">'+
                        '   </div>';

            var itemIndex = 1;

            for (var i = 0; i < dataPoints.object_type_data_points.length; i++) {
                var selected = '';
                var value = '';

                $.each(needsDataPoints, function(idx, obj) {
                  $.each(obj, function(key, val) {
                    if (dataPoints.object_type_data_points[i].columnName == key) {
                        value = val; // Get the value from the JSON data in the record to use to set the selected option in the dropdown

                        trailerData += dataPoints.object_type_data_points[i].title + ": <strong>" + val + "</strong><br>";
                    }
                  })
                });

                if(dataPoints.object_type_data_points[i].title == "Decals"){
                    dpli += '<div class="col-sm-2">' +
                            '<label for="decals">Decals</label>'+
                            '<input id="decals" name="decals" class="form-control" value="'+value+'">'+
                            '</div>';
                  }
                  else{

                    dpli += '<div class="col-sm-2">' +
                            '<label for="' + dataPoints.object_type_data_points[i].columnName + '">' + dataPoints.object_type_data_points[i].title + '</label>' +
                            '<select class="form-control" id="' + dataPoints.object_type_data_points[i].columnName + '" name="' + dataPoints.object_type_data_points[i].columnName + '">' +
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
                            '</div>';
                  }

                  itemIndex += 1;

                  if(itemIndex % 6 == 0) dpli += '</div><div class="form-group row">';
            }


            dpli += '<div class="col-sm-2">' +
                    '   <label for="rate">Rate</label>' +
                    '       <input id="rate" name="rate" class="form-control" value="' + customer_needs.rate + '">' +
                    '</div>';

            dpli += '<div class="col-sm-2">' +
                    '   <label for="rateType">Rate Type</label><br>' +
                    '       <input type="radio" id="rateType" name="rateType" value="Flat Rate" ' + (customer_needs.rateType == "Flat Rate" ? "checked" : "") + '/> Flat Rate ' +
                    '       <input type="radio" id="rateType" name="rateType" value="Mileage" ' + (customer_needs.rateType == "Mileage" ? "checked" : "") + '/> Mileage' +
                    '   </div>';

            dpli += '<div class="col-sm-2">' +
                    '   <label for="transportationMode">Transportation Mode</label>' +
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
                if (customer_needs.status == 'Available') {
                    //if(customer_needs.deliveryInformation == null) customer_needs.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", hoursOfOperation: ""};
                    if(customer_needs.deliveryInformation == null) customer_needs.deliveryInformation = {deliveryLocation: "", contactPerson: "", phoneNumber: "", deliveryHoursOfOperationOpen: "", deliveryHoursOfOperationClose: "", deliveryTimeZone: ""};
                    if(customer_needs.destinationAddress1 == null) customer_needs.destinationAddress1 = "";
                    if(customer_needs.destinationZip == null) customer_needs.destinationZip = "";

                    if(customer_needs.deliveryInformation.deliveryHoursOfOperationOpen == null) customer_needs.deliveryInformation.deliveryHoursOfOperationOpen = "";
                    if(customer_needs.deliveryInformation.deliveryHoursOfOperationClose == null) customer_needs.deliveryInformation.deliveryHoursOfOperationClose = "";
                    if(customer_needs.deliveryInformation.deliveryTimeZone == null) customer_needs.deliveryInformation.deliveryTimeZone = "";
                    
                    
//                    var deliveryInformation = "";
//
//                    //if(customer_needs.deliveryInformation.deliveryLocation != "" && customer_needs.deliveryInformation.contactPerson != "" && customer_needs.deliveryInformation.phoneNumber != "" && customer_needs.deliveryInformation.hoursOfOperation != "" ){
//                    if(customer_needs.deliveryInformation.deliveryLocation != "" && customer_needs.deliveryInformation.contactPerson != "" && customer_needs.deliveryInformation.phoneNumber != "" && customer_needs.deliveryInformation.deliveryHoursOfOperationOpen != "" && customer_needs.deliveryInformation.deliveryHoursOfOperationClose != ""){
//
//                        //deliveryInformation = customer_needs.deliveryInformation.deliveryLocation + "<br>"
//                        //        + customer_needs.deliveryInformation.contactPerson + "<br>"
//                        //        + customer_needs.deliveryInformation.phoneNumber + "<br>"
//                        //        + customer_needs.deliveryInformation.hoursOfOperation + "<br><br>";
//
//                        deliveryInformation = customer_needs.deliveryInformation.deliveryLocation + "<br>"
//                                + customer_needs.deliveryInformation.contactPerson + "<br>"
//                                + customer_needs.deliveryInformation.phoneNumber + "<br>"
//                                + customer_needs.deliveryInformation.deliveryHoursOfOperationOpen + " to " + customer_needs.deliveryInformation.deliveryHoursOfOperationClose + " " + customer_needs.deliveryInformation.deliveryTimeZone + "<br><br>";
//                    }
//
//                    var deliveryAddress = deliveryInformation + customer_needs.destinationAddress1 + "<br>" +
//                            customer_needs.destinationCity + ", " + customer_needs.destinationState + " " + customer_needs.destinationZip + "<br><br>";


            var deliveryAddress = ( customer_needs.deliveryInformation.deliveryLocation.trim() != "" ? customer_needs.deliveryInformation.deliveryLocation + "<br>" : "Unknown Location<br>")
                              + ( customer_needs.destinationAddress1.trim() != "" ? customer_needs.destinationAddress1 + "<br>" : "Unknown Address<br>")
                              + ( customer_needs.destinationCity.trim() != "" ? customer_needs.destinationCity : "Unknown City")
                              + ", " 
                              + ( customer_needs.destinationState.trim() != "" ? customer_needs.destinationState : "Unknown State") 
                              + " " 
                              + ( customer_needs.destinationZip.trim() != "" ? customer_needs.destinationZip : "Unknown Zip") + "<br>"
                              + ( customer_needs.deliveryInformation.contactPerson.trim() != "" ? customer_needs.deliveryInformation.contactPerson + "<br>" : "Unknown Contact<br>")
                              + ( customer_needs.deliveryInformation.phoneNumber.trim() != "" ? customer_needs.deliveryInformation.phoneNumber + "<br>" : "Unknown Phone Number<br>")
                              + ( customer_needs.deliveryInformation.deliveryHoursOfOperationOpen.trim() != "" ? customer_needs.deliveryInformation.deliveryHoursOfOperationOpen  : "N/A")
                              + " to "
                              + ( customer_needs.deliveryInformation.deliveryHoursOfOperationClose.trim() != "" ? customer_needs.deliveryInformation.deliveryHoursOfOperationClose  : "N/A")
                              + " "
                              + ( customer_needs.deliveryInformation.deliveryTimeZone.trim() != "" ? customer_needs.deliveryInformation.deliveryTimeZone  + "<br><br>" : "Unknown Time Zone<br><br>");



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
                    $('#commit_id' + relayNumber).val(customer_needs.customer_needs_commit[0].id);
                    
                    var currentCarrier = parseInt(customer_needs.customer_needs_commit[0].entities[0].id);
                    
                    $('#entityID_relay' + relayNumber).val(currentCarrier);
                    
                    if (currentCarrier > 0){
                        $('#relayOptions_' + relayNumber).css("display", "block");
                    }
                    else{
                        $('#relayOptions_' + relayNumber).css("display", "none");
                    }

                    
                    $('#address_relay' + relayNumber).val(customer_needs.destinationAddress1);
                    $('#city_relay' + relayNumber).val(customer_needs.destinationCity);
                    $('#state_relay' + relayNumber).val(customer_needs.destinationState);
                    $('#zip_relay' + relayNumber).val(customer_needs.destinationZip);
                    $('#notes_relay' + relayNumber).val(customer_needs.destinationNotes);
                    $('#pickupDate_relay' + relayNumber).val(customer_needs.customer_needs_commit[0].pickupDate);
                    $('#deliveryDate_relay' + relayNumber).val(customer_needs.customer_needs_commit[0].deliveryDate);
                    $('#rate_relay' + relayNumber).val();

                    $('#originationLng_relay' + relayNumber).val(customer_needs.customer_needs_commit[0].originationLng);
                    $('#originationLat_relay' + relayNumber).val(customer_needs.customer_needs_commit[0].originationLat);
                    $('#destinationLng_relay' + relayNumber).val(customer_needs.customer_needs_commit[0].destinationLng);
                    $('#destinationLat_relay' + relayNumber).val(customer_needs.customer_needs_commit[0].destinationLat);
                    $('#distance_relay' + relayNumber).val(customer_needs.customer_needs_commit[0].distance);

                    $('#deliveryLocation_relay' + relayNumber).val(customer_needs.deliveryInformation.deliveryLocation);
                    $('#contactPerson_relay' + relayNumber).val(customer_needs.deliveryInformation.contactPerson);
                    $('#phoneNumber_relay' + relayNumber).val(customer_needs.deliveryInformation.phoneNumber);

                    //$('#hoursOfOperation_relay' + relayNumber).val(customer_needs.deliveryInformation.hoursOfOperation);
                    $('#hoursOfOperationOpen_relay' + relayNumber).val(customer_needs.deliveryInformation.deliveryHoursOfOperationOpen);
                    $('#hoursOfOperationClose_relay' + relayNumber).val(customer_needs.deliveryInformation.deliveryHoursOfOperationClose);
                    $('#timeZone_relay' + relayNumber).val(customer_needs.deliveryInformation.deliveryTimeZone);


                    $.ajax({
                        url: '<?php echo API_HOST_URL . "/locations"; ?>' + '?filter=entityID,eq,' + currentCarrier + '&transform=1',
                        contentType: "application/json",
                        success: function (json) {

                            //console.log(json);
                            var locations = json.locations;
                            var locationdata = [];
                            $.each(locations, function(key, location){
                                var value = location.address1;
                                var label = location.address1 + ', ' + location.city + ', ' + location.state + ' ' + location.zip;
                                var id = location.id
                                var city = location.city;
                                var state = location.state;
                                var zip = location.zip;
                                var entry = {id: id, value: value, label: label, city: city, state: state, zip: zip};
                                locationdata.push(entry);
                            });

                            $('#address_relay' + relayNumber).autocomplete({
                                source: locationdata,
                                minLength: 0,
                                select: function (event, ui) {
                                    $('#city_relay' + relayNumber).val(ui.item.city);
                                    $('#state_relay' + relayNumber).val(ui.item.state);
                                    $('#zip_relay' + relayNumber).val(ui.item.zip);
                                }
                            });
                        }
                    });


                    if(customer_needs.customer_needs_commit[0].rate == 0){
                        var calcRate = 0.00;
                        if (customer_needs.customer_needs_commit[0].entities[0].rateType == "Mileage") {
                            calcRate = ( customer_needs.customer_needs_commit[0].distance * parseFloat(customer_needs.customer_needs_commit[0].entities[0].negotiatedRate).toFixed(2) ) * customer_needs.customer_needs_commit[0].qty;
                        } else {
                            calcRate = customer_needs.customer_needs_commit[0].entities[0].negotiatedRate;
                        }
                        //$('#rate_relay' + relayNumber).val('$' + parseFloat(calcRate).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,").toString());
                        $('#rate_relay' + relayNumber).val(parseFloat(calcRate).toFixed(2));
                    }
                    else{
                        $('#rate_relay' + relayNumber).val(parseFloat(customer_needs.customer_needs_commit[0].rate).toFixed(2));
                    }

                    var entid = parseInt(customer_needs.customer_needs_commit[0].entities[0].id);
                    var entname = customer_needs.customer_needs_commit[0].entities[0].name;
                    var carrier = {};
                    carrier[entid] = entname;

                    if (carrierIDs.indexOf(carrier) === -1) carrierIDs.push(carrier);

                    if(relayNumber == 4) return false;
                }
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

        $('#commitModalTitle').append("Edit Commitment");

        $("#customer-needs-commit").css("display", "block");
        $("#customer-needs").css("display", "none");
    }


    function showEditCommit(){
        $('#customer-needs-commit').css('display', 'none');
        $('#editCommitDetails').css('display', 'block');
    }

    function closeEditCommit(){
        
        
        if($('#commitModalTitle').text() == "Edit Commitment"){
            $('#customer-needs-commit').css('display', 'block');
            $('#editCommitDetails').css('display', 'none');
        }
        else{
            $('#customer-needs').css('display', 'block');
            $('#editCommitDetails').css('display', 'none');
        }

    }

    function showAddCommit(){
        $('#customer-needs').css('display', 'none');
        $('#editCommitDetails').css('display', 'block');
    }
    
    function closeAddCommit(){
        $('#customer-needs-commit').css('display', 'none');
        $('#editCommitDetails').css('display', 'block');
    }

//Part I removed



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

    ul.ui-autocomplete {
        z-index: 1100;
    }

    .col-md-1-7{
        width: 14.285714285%;
        float: left;
        position: relative;
        min-height: 1px;
        padding-left: 0.9375rem;
        padding-right: 0.9375rem;
    }

    .w-100{
        width: 100% !important;
    }

    .pickupAddress-border{
        padding-right:20px;
        border-right: 1px solid #ccc;
    }

 </style>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li onclick="ajaxFormCall('listCommitment');" onmouseover="" style="cursor: pointer;">View Commitments</li>
   <li class="active">View Carrier Committed Transport</li>
 </ol>
 <section id="customer-needs" class="widget">
     <header>
         <h4><span class="fw-semi-bold">Available Transport</span></h4>
         <div class="widget-controls">
           <button type="button" class="btn btn-primary btn-md" onclick="addNewCommitment();" id="addNewCommitment">Add New Commitment</button>
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
                     <th>Commitment Date</th>
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
             <!--<a data-widgster="save" title="Save" href="#" onclick="saveCommitAsOrder()"><i class="glyphicon glyphicon-floppy-disk" style="font-size: 20px;"></i></a>
             <a data-widgster="edit" title="Edit" href="#" onclick="editCommitTransport()"><i class="glyphicon glyphicon-pencil" style="font-size: 20px;"></i></a>
             <a data-widgster="close" title="Close" href="#" onclick="closeCommitTransport()"><i class="glyphicon glyphicon-remove" style="font-size: 20px;"></i></a>-->

           <button type="button" class="btn btn-primary btn-md" onclick="showEditCommit();" id="editCommitment">Edit</button>
           <button type="button" class="btn btn-primary btn-md" onclick="saveCommitAsOrder();" id="saveCommitAsOrder">Submit to Order</button>
           <button type="button" class="btn btn-primary btn-md" onclick="closeCommitTransport();" id="closeForm">Close</button>
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
                                    <th>Year</th>
                                    <th>Make</th>
                                    <th>License #</th>
                                    <th>Unit #</th>
                                    <th>VIN #</th>
                                    <th>Truck/Pro#</th>
                                    <th>P.O. #</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody id="unitDataBody">

                            </tbody>
                        </table>
                    </div>
             </div>
         </div>
     </div>
 </section>

<!-- New Edit Commit View -->
<section class="widget"  id="editCommitDetails" style="display: none;">
     <header>
         <h4><span class="fw-semi-bold"  id="commitModalTitle">Edit Commitment</span></h4>
     </header>
     <br />

            <div class="row">
                    <div class="col-md-6 pickupAddress-border">
                    <h2>Pickup Address</h2>
                    <form>
                        <input type="hidden" id="customerNeedsID" name="customerNeedsID" value="" />
                        <input class="form-control" id="originationLng" type="hidden">
                        <input class="form-control" id="originationLat" type="hidden">
                        <input class="form-control" id="destinationLng" type="hidden">
                        <input class="form-control" id="destinationLat" type="hidden">
                        <input class="form-control" id="distance" type="hidden">
                            <div class="form-group row">
                                    <label for="pickupLocation" class="col-sm-3 col-form-label">Location</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="pickupLocation" placeholder="" type="text">
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
                                    <div class="col-sm-3">
                                            <!--input class="form-control" id="pickupHoursOfOperation" placeholder="" type="text"-->
                                            <select class="form-control" id="pickupHoursOfOperationOpen">
                                                <option>01:00</option>
                                                <option>02:00</option>
                                                <option>03:00</option>
                                                <option>04:00</option>
                                                <option>05:00</option>
                                                <option>06:00</option>
                                                <option>07:00</option>
                                                <option>08:00</option>
                                                <option>09:00</option>
                                                <option>10:00</option>
                                                <option>11:00</option>
                                                <option>12:00</option>
                                                <option>13:00</option>
                                                <option>14:00</option>
                                                <option>15:00</option>
                                                <option>16:00</option>
                                                <option>17:00</option>
                                                <option>18:00</option>
                                                <option>19:00</option>
                                                <option>20:00</option>
                                                <option>21:00</option>
                                                <option>22:00</option>
                                                <option>23:00</option>
                                                <option>24:00</option>
                                            </select>
                                    </div>
                                    <div class="col-sm-3">
                                            <!--input class="form-control" id="pickupHoursOfOperation" placeholder="" type="text"-->
                                            <select class="form-control" id="pickupHoursOfOperationClose">
                                                <option>01:00</option>
                                                <option>02:00</option>
                                                <option>03:00</option>
                                                <option>04:00</option>
                                                <option>05:00</option>
                                                <option>06:00</option>
                                                <option>07:00</option>
                                                <option>08:00</option>
                                                <option>09:00</option>
                                                <option>10:00</option>
                                                <option>11:00</option>
                                                <option>12:00</option>
                                                <option>13:00</option>
                                                <option>14:00</option>
                                                <option>15:00</option>
                                                <option>16:00</option>
                                                <option>17:00</option>
                                                <option>18:00</option>
                                                <option>19:00</option>
                                                <option>20:00</option>
                                                <option>21:00</option>
                                                <option>22:00</option>
                                                <option>23:00</option>
                                                <option>24:00</option>
                                            </select>
                                    </div>
                                    <div class="col-sm-3">
                                            <select class="form-control" id="pickupTimeZone">
                                                <option>EST (Eastern)</option>
                                                <option>CST (Central)</option>
                                                <option>MPT (Mountain)</option>
                                                <option>PST (Pacific)</option>
                                            </select>
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

                <div class="col-md-6">
                    <h2>Delivery Address</h2>
                    <form>
                            <div class="form-group row">
                                    <label for="deliveryLocation" class="col-sm-3 col-form-label">Location</label>
                                    <div class="col-sm-9">
                                            <input class="form-control" id="deliveryLocation" placeholder="" type="text">
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
                                    <div class="col-sm-3">
                                            <!--input class="form-control" id="deliveryHoursOfOperation" placeholder="" type="text"-->
                                            <select class="form-control" id="deliveryHoursOfOperationOpen">
                                                <option>01:00</option>
                                                <option>02:00</option>
                                                <option>03:00</option>
                                                <option>04:00</option>
                                                <option>05:00</option>
                                                <option>06:00</option>
                                                <option>07:00</option>
                                                <option>08:00</option>
                                                <option>09:00</option>
                                                <option>10:00</option>
                                                <option>11:00</option>
                                                <option>12:00</option>
                                                <option>13:00</option>
                                                <option>14:00</option>
                                                <option>15:00</option>
                                                <option>16:00</option>
                                                <option>17:00</option>
                                                <option>18:00</option>
                                                <option>19:00</option>
                                                <option>20:00</option>
                                                <option>21:00</option>
                                                <option>22:00</option>
                                                <option>23:00</option>
                                                <option>24:00</option>
                                            </select>
                                    </div>
                                    <div class="col-sm-3">
                                            <!--input class="form-control" id="deliveryHoursOfOperation" placeholder="" type="text"-->
                                            <select class="form-control" id="deliveryHoursOfOperationClose">
                                                <option>01:00</option>
                                                <option>02:00</option>
                                                <option>03:00</option>
                                                <option>04:00</option>
                                                <option>05:00</option>
                                                <option>06:00</option>
                                                <option>07:00</option>
                                                <option>08:00</option>
                                                <option>09:00</option>
                                                <option>10:00</option>
                                                <option>11:00</option>
                                                <option>12:00</option>
                                                <option>13:00</option>
                                                <option>14:00</option>
                                                <option>15:00</option>
                                                <option>16:00</option>
                                                <option>17:00</option>
                                                <option>18:00</option>
                                                <option>19:00</option>
                                                <option>20:00</option>
                                                <option>21:00</option>
                                                <option>22:00</option>
                                                <option>23:00</option>
                                                <option>24:00</option>
                                            </select>
                                    </div>
                                    <div class="col-sm-3">
                                            <select class="form-control" id="deliveryTimeZone">
                                                <option>EST (Eastern)</option>
                                                <option>CST (Central)</option>
                                                <option>MPT (Mountain)</option>
                                                <option>PST (Pacific)</option>
                                            </select>
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
<!--
            <div class="row">
            </div>

            <hr>-->

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
                            <input class="form-control" id="originationLng_relay1" type="hidden">
                            <input class="form-control" id="originationLat_relay1" type="hidden">
                            <input class="form-control" id="destinationLng_relay1" type="hidden">
                            <input class="form-control" id="destinationLat_relay1" type="hidden">
                            <input class="form-control" id="distance_relay1" type="hidden">
                            <!--<input class="form-control" id="entityID_relay1" placeholder="" type="hidden">-->
                            <div class="form-group">
                                <label for="entityID_relay1">Carrier</label>
                                <select id="entityID_relay1" name="entityID_relay1" data-placeholder="Carrier" class="form-control chzn-select" required="required" onchange="populateAutocomplete(this, 1);">
                                    <option selected=selected value=""> -Select Carrier- </option>
                    <?php
                                     foreach($entities->entities->records as $value) {
                                         $selected = ($value[0] == $entity) ? 'selected=selected':'';
                                         echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                                     }
                    ?>
                                </select>
                            </div>
                            
                            
                            <div id="relayOptions_1" style="display: none;">
                                <div class="form-group">
                                        <label for="deliveryLocation_relay1">Location</label>
                                        <input class="form-control" id="deliveryLocation_relay1" placeholder="" type="text">
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
                                    <button type="button" class="btn btn-secondary w-100" onclick="saveRelayAddressToCarrier(1);">Save Address To Carrier</button>
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
                                            <option></option>
                                            <option>01:00</option>
                                            <option>02:00</option>
                                            <option>03:00</option>
                                            <option>04:00</option>
                                            <option>05:00</option>
                                            <option>06:00</option>
                                            <option>07:00</option>
                                            <option>08:00</option>
                                            <option>09:00</option>
                                            <option>10:00</option>
                                            <option>11:00</option>
                                            <option>12:00</option>
                                            <option>13:00</option>
                                            <option>14:00</option>
                                            <option>15:00</option>
                                            <option>16:00</option>
                                            <option>17:00</option>
                                            <option>18:00</option>
                                            <option>19:00</option>
                                            <option>20:00</option>
                                            <option>21:00</option>
                                            <option>22:00</option>
                                            <option>23:00</option>
                                            <option>24:00</option>
                                        </select>
                                </div>
                                <div class="form-group">
                                        <label for="hoursOfOperationClose_relay1">Closing Hour</label>
                                        <select class="form-control" id="hoursOfOperationClose_relay1">
                                            <option></option>
                                            <option>01:00</option>
                                            <option>02:00</option>
                                            <option>03:00</option>
                                            <option>04:00</option>
                                            <option>05:00</option>
                                            <option>06:00</option>
                                            <option>07:00</option>
                                            <option>08:00</option>
                                            <option>09:00</option>
                                            <option>10:00</option>
                                            <option>11:00</option>
                                            <option>12:00</option>
                                            <option>13:00</option>
                                            <option>14:00</option>
                                            <option>15:00</option>
                                            <option>16:00</option>
                                            <option>17:00</option>
                                            <option>18:00</option>
                                            <option>19:00</option>
                                            <option>20:00</option>
                                            <option>21:00</option>
                                            <option>22:00</option>
                                            <option>23:00</option>
                                            <option>24:00</option>
                                        </select>
                                </div>
                                <div class="form-group">
                                        <label for="timeZone_relay1">Time Zone</label>
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
                                        <label for="notes_relay1">Notes</label>
                                        <textarea class="form-control" id="notes_relay1" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                        <label for="rate_relay1">Negotiated Rate</label>
                                        <input class="form-control" id="rate_relay1" placeholder="" type="text" disabled>
                                </div>
                            </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <h4>Relay Address 2</h4>
                            <input class="form-control" id="relay_id2" placeholder="" type="hidden">
                            <input class="form-control" id="commit_id2" placeholder="" type="hidden">
                            <input class="form-control" id="originationLng_relay2" type="hidden">
                            <input class="form-control" id="originationLat_relay2" type="hidden">
                            <input class="form-control" id="destinationLng_relay2" type="hidden">
                            <input class="form-control" id="destinationLat_relay2" type="hidden">
                            <input class="form-control" id="distance_relay2" type="hidden">
                            <!--<input class="form-control" id="entityID_relay2" placeholder="" type="hidden">-->
                            <div class="form-group">
                            <label for="entityID_relay2">Carrier</label>
                                <select id="entityID_relay2" name="entityID_relay2" data-placeholder="Carrier" class="form-control chzn-select" required="required" onchange="populateAutocomplete(this, 2);">
                                    <option selected=selected value=""> -Select Carrier- </option>
                    <?php
                                     foreach($entities->entities->records as $value) {
                                         $selected = ($value[0] == $entity) ? 'selected=selected':'';
                                         echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                                     }
                    ?>
                                </select>
                            </div>
                            
                            <div id="relayOptions_2" style="display: none;">
                                <div class="form-group">
                                        <label for="deliveryLocation_relay2">Location</label>
                                        <input class="form-control" id="deliveryLocation_relay2" placeholder="" type="text">
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
                                    <button type="button" class="btn btn-secondary w-100" onclick="saveRelayAddressToCarrier(2);">Save Address To Carrier</button>
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
                                        <!--input class="form-control" id="hoursOfOperation_relay2" placeholder="" type="text"-->
                                        <select class="form-control" id="hoursOfOperationOpen_relay2">
                                            <option></option>
                                            <option>01:00</option>
                                            <option>02:00</option>
                                            <option>03:00</option>
                                            <option>04:00</option>
                                            <option>05:00</option>
                                            <option>06:00</option>
                                            <option>07:00</option>
                                            <option>08:00</option>
                                            <option>09:00</option>
                                            <option>10:00</option>
                                            <option>11:00</option>
                                            <option>12:00</option>
                                            <option>13:00</option>
                                            <option>14:00</option>
                                            <option>15:00</option>
                                            <option>16:00</option>
                                            <option>17:00</option>
                                            <option>18:00</option>
                                            <option>19:00</option>
                                            <option>20:00</option>
                                            <option>21:00</option>
                                            <option>22:00</option>
                                            <option>23:00</option>
                                            <option>24:00</option>
                                        </select>
                                </div>
                                <div class="form-group">
                                        <label for="hoursOfOperationClose_relay2">Closing Hour</label>
                                        <select class="form-control" id="hoursOfOperationClose_relay2">
                                            <option></option>
                                            <option>01:00</option>
                                            <option>02:00</option>
                                            <option>03:00</option>
                                            <option>04:00</option>
                                            <option>05:00</option>
                                            <option>06:00</option>
                                            <option>07:00</option>
                                            <option>08:00</option>
                                            <option>09:00</option>
                                            <option>10:00</option>
                                            <option>11:00</option>
                                            <option>12:00</option>
                                            <option>13:00</option>
                                            <option>14:00</option>
                                            <option>15:00</option>
                                            <option>16:00</option>
                                            <option>17:00</option>
                                            <option>18:00</option>
                                            <option>19:00</option>
                                            <option>20:00</option>
                                            <option>21:00</option>
                                            <option>22:00</option>
                                            <option>23:00</option>
                                            <option>24:00</option>
                                        </select>
                                </div>
                                <div class="form-group">
                                        <label for="timeZone_relay2">Time Zone</label>
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
                                        <label for="notes_relay2">Notes</label>
                                        <textarea class="form-control" id="notes_relay2" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                        <label for="rate_relay2">Negotiated Rate</label>
                                        <input class="form-control" id="rate_relay2" placeholder="" type="text" disabled>
                                </div>
                            </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <h4>Relay Address 3</h4>
                            <input class="form-control" id="relay_id3" placeholder="" type="hidden">
                            <input class="form-control" id="commit_id3" placeholder="" type="hidden">
                            <input class="form-control" id="originationLng_relay3" type="hidden">
                            <input class="form-control" id="originationLat_relay3" type="hidden">
                            <input class="form-control" id="destinationLng_relay3" type="hidden">
                            <input class="form-control" id="destinationLat_relay3" type="hidden">
                            <input class="form-control" id="distance_relay3" type="hidden">
                            <!--<input class="form-control" id="entityID_relay3" placeholder="" type="hidden">-->
                            <div class="form-group">
                            <label for="entityID_relay3">Carrier</label>
                                <select id="entityID_relay3" name="entityID_relay3" data-placeholder="Carrier" class="form-control chzn-select" required="required" onchange="populateAutocomplete(this, 3);">
                                    <option selected=selected value=""> -Select Carrier- </option>
                    <?php
                                     foreach($entities->entities->records as $value) {
                                         $selected = ($value[0] == $entity) ? 'selected=selected':'';
                                         echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                                     }
                    ?>
                                </select>
                            </div>
                            
                            <div id="relayOptions_3" style="display: none;">
                                <div class="form-group">
                                        <label for="deliveryLocation_relay3">Location</label>
                                        <input class="form-control" id="deliveryLocation_relay3" placeholder="" type="text">
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
                                    <button type="button" class="btn btn-secondary w-100" onclick="saveRelayAddressToCarrier(3);">Save Address To Carrier</button>
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
                                        <!--input class="form-control" id="hoursOfOperation_relay3" placeholder="" type="text"-->
                                        <select class="form-control" id="hoursOfOperationOpen_relay3">
                                            <option></option>
                                            <option>01:00</option>
                                            <option>02:00</option>
                                            <option>03:00</option>
                                            <option>04:00</option>
                                            <option>05:00</option>
                                            <option>06:00</option>
                                            <option>07:00</option>
                                            <option>08:00</option>
                                            <option>09:00</option>
                                            <option>10:00</option>
                                            <option>11:00</option>
                                            <option>12:00</option>
                                            <option>13:00</option>
                                            <option>14:00</option>
                                            <option>15:00</option>
                                            <option>16:00</option>
                                            <option>17:00</option>
                                            <option>18:00</option>
                                            <option>19:00</option>
                                            <option>20:00</option>
                                            <option>21:00</option>
                                            <option>22:00</option>
                                            <option>23:00</option>
                                            <option>24:00</option>
                                        </select>
                                </div>
                                <div class="form-group">
                                        <label for="hoursOfOperationClose_relay3">Closing Hour</label>
                                        <select class="form-control" id="hoursOfOperationClose_relay3">
                                            <option></option>
                                            <option>01:00</option>
                                            <option>02:00</option>
                                            <option>03:00</option>
                                            <option>04:00</option>
                                            <option>05:00</option>
                                            <option>06:00</option>
                                            <option>07:00</option>
                                            <option>08:00</option>
                                            <option>09:00</option>
                                            <option>10:00</option>
                                            <option>11:00</option>
                                            <option>12:00</option>
                                            <option>13:00</option>
                                            <option>14:00</option>
                                            <option>15:00</option>
                                            <option>16:00</option>
                                            <option>17:00</option>
                                            <option>18:00</option>
                                            <option>19:00</option>
                                            <option>20:00</option>
                                            <option>21:00</option>
                                            <option>22:00</option>
                                            <option>23:00</option>
                                            <option>24:00</option>
                                        </select>
                                </div>
                                <div class="form-group">
                                        <label for="timeZone_relay3">Time Zone</label>
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
                                        <label for="notes_relay3">Notes</label>
                                        <textarea class="form-control" id="notes_relay3" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                        <label for="rate_relay3">Negotiated Rate</label>
                                        <input class="form-control" id="rate_relay3" placeholder="" type="text" disabled>
                                </div>
                            </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <h4>Relay Address 4</h4>
                            <input class="form-control" id="relay_id4" placeholder="" type="hidden">
                            <input class="form-control" id="commit_id4" placeholder="" type="hidden">
                            <input class="form-control" id="originationLng_relay4" type="hidden">
                            <input class="form-control" id="originationLat_relay4" type="hidden">
                            <input class="form-control" id="destinationLng_relay4" type="hidden">
                            <input class="form-control" id="destinationLat_relay4" type="hidden">
                            <input class="form-control" id="distance_relay4" type="hidden">
                            <!--<input class="form-control" id="entityID_relay4" placeholder="" type="hidden">-->
                            <div class="form-group">
                            <label for="entityID_relay4">Carrier</label>
                                <select id="entityID_relay4" name="entityID_relay4" data-placeholder="Carrier" class="form-control chzn-select" required="required" onchange="populateAutocomplete(this, 4);">
                                    <option selected=selected value=""> -Select Carrier- </option>
                    <?php
                                     foreach($entities->entities->records as $value) {
                                         $selected = ($value[0] == $entity) ? 'selected=selected':'';
                                         echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                                     }
                    ?>
                                </select>
                            </div>
                            
                            <div id="relayOptions_4" style="display: none;">
                                <div class="form-group">
                                        <label for="deliveryLocation_relay4">Location</label>
                                        <input class="form-control" id="deliveryLocation_relay4" placeholder="" type="text">
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
                                    <button type="button" class="btn btn-secondary w-100" onclick="saveRelayAddressToCarrier(4);">Save Address To Carrier</button>
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
                                        <!--input class="form-control" id="hoursOfOperation_relay4" placeholder="" type="text"-->
                                        <select class="form-control" id="hoursOfOperationOpen_relay4">
                                            <option></option>
                                            <option>01:00</option>
                                            <option>02:00</option>
                                            <option>03:00</option>
                                            <option>04:00</option>
                                            <option>05:00</option>
                                            <option>06:00</option>
                                            <option>07:00</option>
                                            <option>08:00</option>
                                            <option>09:00</option>
                                            <option>10:00</option>
                                            <option>11:00</option>
                                            <option>12:00</option>
                                            <option>13:00</option>
                                            <option>14:00</option>
                                            <option>15:00</option>
                                            <option>16:00</option>
                                            <option>17:00</option>
                                            <option>18:00</option>
                                            <option>19:00</option>
                                            <option>20:00</option>
                                            <option>21:00</option>
                                            <option>22:00</option>
                                            <option>23:00</option>
                                            <option>24:00</option>
                                        </select>
                                </div>
                                <div class="form-group">
                                        <label for="hoursOfOperationClose_relay4">Closing Hour</label>
                                        <select class="form-control" id="hoursOfOperationClose_relay4">
                                            <option></option>
                                            <option>01:00</option>
                                            <option>02:00</option>
                                            <option>03:00</option>
                                            <option>04:00</option>
                                            <option>05:00</option>
                                            <option>06:00</option>
                                            <option>07:00</option>
                                            <option>08:00</option>
                                            <option>09:00</option>
                                            <option>10:00</option>
                                            <option>11:00</option>
                                            <option>12:00</option>
                                            <option>13:00</option>
                                            <option>14:00</option>
                                            <option>15:00</option>
                                            <option>16:00</option>
                                            <option>17:00</option>
                                            <option>18:00</option>
                                            <option>19:00</option>
                                            <option>20:00</option>
                                            <option>21:00</option>
                                            <option>22:00</option>
                                            <option>23:00</option>
                                            <option>24:00</option>
                                        </select>
                                </div>
                                <div class="form-group">
                                        <label for="timeZone_relay4">Time Zone</label>
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
                                        <label for="notes_relay4">Notes</label>
                                        <textarea class="form-control" id="notes_relay4" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                        <label for="rate_relay4">Negotiated Rate</label>
                                        <input class="form-control" id="rate_relay4" placeholder="" type="text" disabled>
                                </div>
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

        <div class="row row-grid">
            <div class="col-lg-4 col-md-4 col-sm-12 pull-right">
                <button type="button" class="btn btn-secondary btn-lg" onclick="closeEditCommit();">Close</button>
                <button type="button" class="btn btn-primary btn-lg" onclick="addTrailer();" id="addTrailer">AddTrailer</button>
                <button type="button" class="btn btn-primary btn-lg" onclick="saveCommit();" id="saveCommit">Save</button>
            </div>
        </div>


 </section>

 <script>

    getCommitted();


    $('.datepicker').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });



// Part II Removed



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
                            //alert("Could not close availability leg.");
                            $("#errorAlertTitle").html("Error");
                            $("#errorAlertBody").html("Could not close availability leg.");
                            $("#errorAlert").modal('show');
                        }
                    });
                });
            },
            error: function(){
                // alert("Could not Get customer needs Customer Needs.");
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Could not get customer needs");
                $("#errorAlert").modal('show');
            }
        });

    }

    function closeRootCustomerCommitLegs(id){

        $.ajax({
            url: '<?php echo API_HOST_URL ?>' + '/customer_needs/' + id,
            type: "PUT",
            data: JSON.stringify({status: "Closed"}),
            contentType: "application/json",
            async: false,
            success: function(data){
                if(data > 0){
                    console.log("Leg Closed.");
                }
                else{
                    console.log("Could not close root leg:", id);
                }
            },
            error: function(){
                //alert("Could not close availability leg.");
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Could not close availability root leg.");
                $("#errorAlert").modal('show');
            }
        });

    }



// Part III Removed




   function addTrailer(){

       var unitID = $('#addTrailer > div').length;

        var unitData = '<div class="row">\n\
                        <div class="col-md-1">\n\
                            <div class="form-group">\n\
                                    <label for="year' + (unitID + 1) + '">Year</label>\n\
                                    <input class="form-control" id="year' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\
                        </div>\n\
                        <div class="col-md-2">\n\
                            <div class="form-group">\n\
                                    <label for="make' + (unitID + 1) + '">Make</label>\n\
                                    <input class="form-control" id="make' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\
                        </div>\n\
                        <div class="col-md-1">\n\
                            <div class="form-group">\n\
                                    <label for="licenseNumber' + (unitID + 1) + '">License #</label>\n\
                                    <input class="form-control" id="licenseNumber' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\
                        </div>\n\
                        <div class="col-md-1">\n\
                            <div class="form-group">\n\
                                    <label for="unitNumber' + (unitID + 1) + '">Unit #</label>\n\
                                    <input class="form-control" id="unitNumber' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\
                        </div>\n\
                        <div class="col-md-2">\n\
                            <div class="form-group">\n\
                                    <label for="vinNumber' + (unitID + 1) + '">VIN #</label>\n\
                                    <input class="form-control" id="vinNumber' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\
                        </div>\n\
                        <div class="col-md-2">\n\
                            <div class="form-group">\n\
                                    <label for="truckProNumber' + (unitID + 1) + '">Truck/Pro #</label>\n\
                                    <input class="form-control" id="truckProNumber' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\
                        </div>\n\
                        <div class="col-md-2">\n\
                            <div class="form-group">\n\
                                    <label for="poNumber' + (unitID + 1) + '">P.O. #</label>\n\
                                    <input class="form-control" id="poNumber' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\
                        </div>\n\
                        <div class="col-md-1">\n\
                            <div class="form-group">\n\
                                    <label for="value' + (unitID + 1) + '">Value</label>\n\
                                    <input class="form-control" id="value' + (unitID + 1) + '" placeholder="" type="text">\n\
                            </div>\n\
                        </div>\n\
                    </div>';

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

                                    $("#errorAlertTitle").html("Error");
                                    $("#errorAlertBody").html("Could not create Quickbooks Vendor");
                                    $("#errorAlert").modal('show');

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

                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Could not create Quickbooks Customer");
                    $("#errorAlert").modal('show');

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
                        $("#errorAlertTitle").html("Success");
                        $("#errorAlertBody").html("Customer Availability Successfully Completed.");
                        $("#errorAlert").modal('show');
	            },
	            error: function(){
                        $("#errorAlertTitle").html("Error");
                        $("#errorAlertBody").html("Error with adding Order Details.");
                        $("#errorAlert").modal('show');
	            }

	        });

		}

    }



// Part IV Removed



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

    function editCommitment(){

        var unitDataList = [];

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

/*
            var pickupInformation = {pickupLocation: $('#pickupLocation').val().trim(), contactPerson: $('#pickupContactPerson').val().trim(),
                                    phoneNumber: $('#pickupPhoneNumber').val().trim(), hoursOfOperation: $('#pickupHoursOfOperation').val().trim()};

            var deliveryInformation = {deliveryLocation: $('#deliveryLocation').val().trim(), contactPerson: $('#deliveryContactPerson').val().trim(),
                                    phoneNumber: $('#deliveryPhoneNumber').val().trim(), hoursOfOperation: $('#deliveryHoursOfOperation').val().trim()};
*/

            var pickupInformation = {pickupLocation: $('#pickupLocation').val().trim(), contactPerson: $('#pickupContactPerson').val().trim(),
                                    phoneNumber: $('#pickupPhoneNumber').val().trim(), pickupHoursOfOperationOpen: $('#pickupHoursOfOperationOpen').val(),
                                    pickupHoursOfOperationClose: $('#pickupHoursOfOperationClose').val(), pickupTimeZone: $('#pickupTimeZone').val()};

            var deliveryInformation = {deliveryLocation: $('#deliveryLocation').val().trim(), contactPerson: $('#deliveryContactPerson').val().trim(),
                                    phoneNumber: $('#deliveryPhoneNumber').val().trim(), deliveryHoursOfOperationOpen: $('#deliveryHoursOfOperationOpen').val(),
                                    deliveryHoursOfOperationClose: $('#deliveryHoursOfOperationClose').val(), deliveryTimeZone: $('#deliveryTimeZone').val()};


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
            var rateType = $('input[name=rateType]:checked').val().trim();
            var transportationMode = $("#transportationMode").val().trim();

            var data = {pickupInformation: pickupInformation, originationAddress1: originationAddress1, originationAddress2: originationAddress2, originationCity: originationCity, originationState: originationState, originationZip: originationZip, originationNotes: originationNotes,
                        deliveryInformation: deliveryInformation, destinationAddress1: destinationAddress1, destinationAddress2: destinationAddress2, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, destinationNotes: destinationNotes,
                        qty: qty, updatedAt: today, needsDataPoints: needsdatapoints, unitData: unitDataList, rate: rate, rateType: rateType, transportationMode: transportationMode};

            var url = '<?php echo API_HOST_URL . "/customer_needs" ?>/' + id;

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
                            var commitID = $('#commit_id' + relayNumber).val().trim();

                            var carrierID = $('#entityID_relay' + relayNumber).val().trim();

                            var destinationAddress1 = $('#address_relay' + relayNumber).val().trim();
                            var destinationCity = $('#city_relay' + relayNumber).val().trim();
                            var destinationState = $('#state_relay' + relayNumber).val().trim();
                            var destinationZip = $('#zip_relay' + relayNumber).val().trim();
                            var destinationNotes = $('#notes_relay' + relayNumber).val().trim();
/*
                            var deliveryInformation = {deliveryLocation: $('#deliveryLocation_relay' + relayNumber).val().trim(), contactPerson: $('#contactPerson_relay' + relayNumber).val().trim(),
                                                    phoneNumber: $('#phoneNumber_relay' + relayNumber).val().trim(), hoursOfOperation: $('#hoursOfOperation_relay' + relayNumber).val().trim()};
*/

                            var deliveryInformation = {deliveryLocation: $('#deliveryLocation_relay' + relayNumber).val().trim(), contactPerson: $('#contactPerson_relay' + relayNumber).val().trim(),
                                                    phoneNumber: $('#phoneNumber_relay' + relayNumber).val().trim(), deliveryHoursOfOperationOpen: $('#hoursOfOperationOpen_relay' + relayNumber).val().trim(),
                                                    deliveryHoursOfOperationClose: $('#hoursOfOperationClose_relay' + relayNumber).val().trim(),deliveryTimeZone: $('#timeZone_relay' + relayNumber).val()};

                            if(carrierID != ""){

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
                                    /*
                                    relayData = {rootCustomerNeedsID: id, pickupInformation: pickupInformation,  originationAddress1: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip, originationNotes: originationNotes,
                                        deliveryInformation: deliveryInformation, destinationAddress1: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, destinationNotes: destinationNotes,
                                        qty: qty, updatedAt: today, needsDataPoints: needsdatapoints,  unitData: unitDataList, rate: rate, rateType: rateType, transportationMode: transportationMode};
                                    */
                                    relayData = {rootCustomerNeedsID: id, pickupInformation: pickupInformation,  originationAddress1: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip, originationNotes: originationNotes,
                                        deliveryInformation: deliveryInformation, destinationAddress1: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, destinationNotes: destinationNotes,
                                        qty: qty, updatedAt: today, needsDataPoints: needsdatapoints,  unitData: unitDataList};
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

                                            var entityID = $('#entityID_relay' + relayNumber).val();
                                            var pickupDate = $('#pickupDate_relay' + relayNumber).val();
                                            var deliveryDate = $('#deliveryDate_relay' + relayNumber).val();
                                            var rate = $('#rate_relay' + relayNumber).val();
/*
                                            var commitData = {customerNeedsID: relayID, originationAddress1: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                                                            destinationAddress1: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, status: "Available",
                                                            originationLng: "", originationLat: "", destinationLng: "", destinationLat: "", distance: 0, qty: qty, rate: rate, transportation_mode: "", transportation_type: "", updatedAt: today,
                                                            pickupDate: pickupDate, deliveryDate: deliveryDate };
*/
                                            var commitData = {customerNeedsID: relayID, entityID: entityID, originationAddress1: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                                                            destinationAddress1: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, updatedAt: today,
                                                            pickupDate: pickupDate, deliveryDate: deliveryDate };

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
                                                    $("#errorAlertTitle").html("Error");
                                                    $("#errorAlertBody").html("Unable to save to customer_needs_commit");
                                                    $("#errorAlert").modal('show');
                                                }
                                            });

                                        }
                                    },
                                    error: function(){

                                        $("#errorAlertTitle").html("Error");
                                        $("#errorAlertBody").html("unable to save relay.");
                                        $("#errorAlert").modal('show');
                                    }
                                });
                            }
                            else if(relayID != ""){

                                url = '<?php echo API_HOST_URL . "/customer_needs" ?>/' + relayID;
                                var statusChange = {status: "Close"};

                                $.ajax({
                                url: url,
                                type: "PUT",
                                data: JSON.stringify(statusChange),
                                success: function(data){

                                },
                                error: function(){
                                    $("#errorAlertTitle").html("Error");
                                    $("#errorAlertBody").html("Unable to save to customer_needs_commit");
                                    $("#errorAlert").modal('show');
                                }
                            });

                            }
                        }

                        $("#saveCommit").html("Save");
                        $("#saveCommit").prop("disabled", false);
                        closeEditCommit();
                        loadNewCustomerNeedsCommit(id);

                        $("#errorAlertTitle").html("Success");
                        $("#errorAlertBody").html("Commit Updated");
                        $("#errorAlert").modal('show');


                        var logParams = {logTypeName: "Customer Needs", logMessage: "Commitment has been edited edited by Admin.", referenceID: id};

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

                    }
                    else{
                        console.log(data);
                    }
                },
                error: function(data){
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("There Was An Error Updating Commit");
                    $("#errorAlert").modal('show');
                }
            });
        }
        else{
            $("#errorAlertTitle").html("Error");
            $("#errorAlertBody").html("You must enter at least ONE Trailer.");
            $("#errorAlert").modal('show');
        }
    }

    function addCommitment(){

        var unitDataList = [];

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
                var unitData = {unitNumber: unitNumber, vinNumber: vinNumber, truckProNumber: truckProNumber, poNumber: poNumber, year: year, make: make, value: cashValue};

                unitDataList.push(unitData);
            }
        });

        if(unitDataList.length > 0){
          $("#saveCommit").html("<i class='fa fa-spinner fa-spin'></i> Saving Commit");
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
/*
            var pickupInformation = {pickupLocation: $('#pickupLocation').val().trim(), contactPerson: $('#pickupContactPerson').val().trim(),
                                    phoneNumber: $('#pickupPhoneNumber').val().trim(), hoursOfOperation: $('#pickupHoursOfOperation').val().trim()};

            var deliveryInformation = {deliveryLocation: $('#deliveryLocation').val().trim(), contactPerson: $('#deliveryContactPerson').val().trim(),
                                    phoneNumber: $('#deliveryPhoneNumber').val().trim(), hoursOfOperation: $('#deliveryHoursOfOperation').val().trim()};
*/

            var pickupInformation = {pickupLocation: $('#pickupLocation').val().trim(), contactPerson: $('#pickupContactPerson').val().trim(),
                                    phoneNumber: $('#pickupPhoneNumber').val().trim(), pickupHoursOfOperationOpen: $('#pickupHoursOfOperationOpen').val(),
                                    pickupHoursOfOperationClose: $('#pickupHoursOfOperationClose').val(), pickupTimeZone: $('#pickupTimeZone').val()};

            var deliveryInformation = {deliveryLocation: $('#deliveryLocation').val().trim(), contactPerson: $('#deliveryContactPerson').val().trim(),
                                    phoneNumber: $('#deliveryPhoneNumber').val().trim(), deliveryHoursOfOperationOpen: $('#deliveryHoursOfOperationOpen').val(),
                                    deliveryHoursOfOperationClose: $('#deliveryHoursOfOperationClose').val(), deliveryTimeZone: $('#deliveryTimeZone').val()};

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
                        qty: qty, updatedAt: today, needsDataPoints: needsdatapoints, unitData: unitDataList, rate: rate, rateType: rateType, transportationMode: transportationMode, status: "Available"};

            var url = '<?php echo API_HOST_URL . "/customer_needs" ?>';

            $.ajax({
                url: url,
                type: 'POST',
                data: JSON.stringify(data),
                contentType: "application/json",
                async: false,
                success: function(data){
                    if(data > 0){

                        var id = data;

                        var relayNumber = 0;
                        for(relayNumber = 1; relayNumber < 5; relayNumber++){

                            var relayData = {};

                            var carrierID = $('#entityID_relay' + relayNumber).val();
                            var destinationAddress1 = $('#address_relay' + relayNumber).val().trim();
                            var destinationCity = $('#city_relay' + relayNumber).val().trim();
                            var destinationState = $('#state_relay' + relayNumber).val().trim();
                            var destinationZip = $('#zip_relay' + relayNumber).val().trim();
                            var destinationNotes = $('#notes_relay' + relayNumber).val().trim();
/*
                            var deliveryInformation = {deliveryLocation: $('#deliveryLocation_relay' + relayNumber).val().trim(), contactPerson: $('#contactPerson_relay' + relayNumber).val().trim(),
                                                    phoneNumber: $('#phoneNumber_relay' + relayNumber).val().trim(), hoursOfOperation: $('#hoursOfOperation_relay' + relayNumber).val().trim()};
*/

                            var deliveryInformation = {deliveryLocation: $('#deliveryLocation_relay' + relayNumber).val().trim(), contactPerson: $('#contactPerson_relay' + relayNumber).val().trim(),
                                                    phoneNumber: $('#phoneNumber_relay' + relayNumber).val().trim(), deliveryHoursOfOperationOpen: $('#hoursOfOperationOpen_relay' + relayNumber).val().trim(),
                                                    deliveryHoursOfOperationClose: $('#hoursOfOperationClose_relay' + relayNumber).val().trim(),deliveryTimeZone: $('#timeZone_relay' + relayNumber).val().trim()};

                            if(carrierID != ""){

                                relayData = {rootCustomerNeedsID: id, pickupInformation: pickupInformation, originationAddress1: originationAddress1, originationAddress2: originationAddress2, originationCity: originationCity, originationState: originationState, originationZip: originationZip, originationNotes: originationNotes,
                                    deliveryInformation: deliveryInformation, destinationAddress1: destinationAddress1, destinationAddress2: destinationAddress2, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, destinationNotes: destinationNotes,
                                    qty: qty, createdAt: today, updatedAt: today, needsDataPoints: needsdatapoints,  unitData: unitDataList, rate: rate, rateType: rateType, transportationMode: transportationMode, status: "Available"};

                                $.ajax({
                                    url: '<?php echo API_HOST_URL . "/customer_needs" ?>/',
                                    type: "POST",
                                    data: JSON.stringify(relayData),
                                    contentType: "application/json",
                                    async: false,
                                    success: function(data){
                                        if(data > 0){

                                            var relayID = data;

                                            var entityID = $('#entityID_relay' + relayNumber).val();
                                            var pickupDate = $('#pickupDate_relay' + relayNumber).val();
                                            var deliveryDate = $('#deliveryDate_relay' + relayNumber).val();

                                            var commitData = {customerNeedsID: relayID, entityID: entityID, originationAddress1: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                                                            destinationAddress1: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, status: "Available",
                                                            originationLng: "", originationLat: "", destinationLng: "", destinationLat: "", distance: 0, qty: qty, transportation_mode: transportationMode, transportation_type: "", createdAt: today, updatedAt: today,
                                                            pickupDate: pickupDate, deliveryDate: deliveryDate };

                                            $.ajax({
                                                url: '<?php echo API_HOST_URL . "/customer_needs_commit" ?>/',
                                                type: "POST",
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
                                                    $("#errorAlertTitle").html("Error");
                                                    $("#errorAlertBody").html("Unable to save to customer_needs_commit");
                                                    $("#errorAlert").modal('show');
                                                }
                                            });

                                        }
                                    },
                                    error: function(){
                                        $("#errorAlertTitle").html("Error");
                                        $("#errorAlertBody").html("unable to save relay.");
                                        $("#errorAlert").modal('show');
                                    }
                                });
                            }
                        }
                    }
                    else{
                        console.log(data);
                    }
                    $("#saveCommit").html("Save");
                    $("#saveCommit").prop("disabled", false);
                    closeEditCommit();
                    loadNewCustomerNeedsCommit(id);

                    $("#errorAlertTitle").html("Success");
                    $("#errorAlertBody").html("Commit Added");
                    $("#errorAlert").modal('show');
                },
                error: function(data){
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("There Was An Error Updating Commit");
                    $("#errorAlert").modal('show');
                }
            });

        }
        else{
            $("#errorAlertTitle").html("Error");
            $("#errorAlertBody").html("You must enter at least ONE Trailer.");
            $("#errorAlert").modal('show');
        }
    }

    function saveCommit(){

            if($('#commitModalTitle').text() == "Edit Commitment"){
                editCommitment();
            }
            else{
                addCommitment();
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
/*
            var pickupInformation = {pickupLocation: $('#pickupLocation').val().trim(), contactPerson: $('#pickupContactPerson').val().trim(),
                                    phoneNumber: $('#pickupPhoneNumber').val().trim(), hoursOfOperation: $('#pickupHoursOfOperation').val().trim()};

            var deliveryInformation = {deliveryLocation: $('#deliveryLocation').val().trim(), contactPerson: $('#deliveryContactPerson').val().trim(),
                                    phoneNumber: $('#deliveryPhoneNumber').val().trim(), hoursOfOperation: $('#deliveryHoursOfOperation').val().trim()};
*/

            var pickupInformation = {pickupLocation: $('#pickupLocation').val().trim(), contactPerson: $('#pickupContactPerson').val().trim(),
                                    phoneNumber: $('#pickupPhoneNumber').val().trim(), pickupHoursOfOperationOpen: $('#pickupHoursOfOperationOpen').val(),
                                    pickupHoursOfOperationClose: $('#pickupHoursOfOperationClose').val(), pickupTimeZone: $('#pickupTimeZone').val()};

            var deliveryInformation = {deliveryLocation: $('#deliveryLocation').val().trim(), contactPerson: $('#deliveryContactPerson').val().trim(),
                                    phoneNumber: $('#deliveryPhoneNumber').val().trim(), deliveryHoursOfOperationOpen: $('#deliveryHoursOfOperationOpen').val(),
                                    deliveryHoursOfOperationClose: $('#deliveryHoursOfOperationClose').val(), deliveryTimeZone: $('#deliveryTimeZone').val()};

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

            var transportationMode = $('#transportationMode').val().trim();

            var originationLng = $('#originationLng').val().trim();
            var originationLat = $('#originationLat').val().trim();
            var destinationLng = $('#destinationLng').val().trim();
            var destinationLat = $('#destinationLat').val().trim();
            var distance = $('#distance').val().trim();


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
            var customerID = $('#customerID').val();
/*
            var orderData = {customerID: customerID, carrierIDs: [{carrierID: 0}], orderID: orderID, deliveryInformation: deliveryInformation, pickupInformation: pickupInformation, originationAddress: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                        destinationAddress: destinationAddress1,  destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, originationLng: "", originationLat: "", destinationLng: "", destinationLat: "", distance: 0, needsDataPoints: needsdatapoints, podList: unitDataList,
                        comments: "", createdAt: today, updatedAt: today, qty: qty};
*/
            var orderData = {customerID: customerID, carrierIDs: carrierIDs, orderID: orderID, deliveryInformation: deliveryInformation, pickupInformation: pickupInformation, originationAddress: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                        destinationAddress: destinationAddress1,  destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, originationLng: originationLng, originationLat: originationLat, destinationLng: destinationLng, destinationLat: destinationLat, distance: distance, needsDataPoints: needsdatapoints, podList: unitDataList,
                        comments: "", createdAt: today, updatedAt: today, qty: qty, customerRate: rate, transportationMode: transportationMode};

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
                            var carrierID = $('#entityID_relay' + relayNumber).val();
                            var destinationAddress1 = $('#address_relay' + relayNumber).val().trim();
                            var destinationCity = $('#city_relay' + relayNumber).val().trim();
                            var destinationState = $('#state_relay' + relayNumber).val().trim();
                            var destinationZip = $('#zip_relay' + relayNumber).val().trim();
                            var destinationNotes = $('#notes_relay' + relayNumber).val().trim();

/*
                            var deliveryInformation = {deliveryLocation: $('#deliveryLocation_relay' + relayNumber).val().trim(), contactPerson: $('#contactPerson_relay' + relayNumber).val().trim(),
                                                    phoneNumber: $('#phoneNumber_relay' + relayNumber).val().trim(), hoursOfOperation: $('#hoursOfOperation_relay' + relayNumber).val().trim()};
*/

                            var deliveryInformation = {deliveryLocation: $('#deliveryLocation_relay' + relayNumber).val().trim(), contactPerson: $('#contactPerson_relay' + relayNumber).val().trim(),
                                                    phoneNumber: $('#phoneNumber_relay' + relayNumber).val().trim(), deliveryHoursOfOperationOpen: $('#hoursOfOperationOpen_relay' + relayNumber).val(),
                                                    deliveryHoursOfOperationClose: $('#hoursOfOperationClose_relay' + relayNumber).val(),timeZone: $('#timeZone_relay' + relayNumber).val()};

                            if(carrierID != ""){

                                    url = '<?php echo API_HOST_URL . "/order_details" ?>/';
                                    type = "POST";

                                    var pickupDate = $('#pickupDate_relay' + relayNumber).val();
                                    var deliveryDate = $('#deliveryDate_relay' + relayNumber).val();
                                    var entityID = $('#entityID_relay' + relayNumber).val();
                                    var carrierRate = $('#rate_relay' + relayNumber).val();

                                    var originationLng = $("#originationLng_relay"+relayNumber).val();
                                    var originationLat = $("#originationLat_relay"+relayNumber).val();
                                    var destinationLng = $("#destinationLng_relay"+relayNumber).val();
                                    var destinationLat = $("#destinationLat_relay"+relayNumber).val();
                                    var distance = $("#distance_relay"+relayNumber).val();
/*
                                    relayData = {carrierID: 0, orderID: data, pickupInformation: pickupInformation, originationAddress: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                                        deliveryInformation: deliveryInformation, destinationAddress: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, carrierRate: 0.00, transportationMode: "",
                                        qty: qty, createdAt: today, updatedAt: today, needsDataPoints: needsdatapoints, status: "Open", pickupDate: pickupDate, deliveryDate: deliveryDate};
*/
                                    relayData = {carrierID: entityID, orderID: data, pickupInformation: pickupInformation, originationAddress: originationAddress1, originationCity: originationCity, originationState: originationState, originationZip: originationZip,
                                        deliveryInformation: deliveryInformation, destinationAddress: destinationAddress1, destinationCity: destinationCity, destinationState: destinationState, destinationZip: destinationZip, carrierRate: carrierRate, transportationMode: transportationMode,
                                        originationLng: originationLng, originationLat: originationLat, destinationLng: destinationLng, destinationLat: destinationLat, distance: distance,
                                        qty: qty, createdAt: today, updatedAt: today, needsDataPoints: needsdatapoints, status: "Open", pickupDate: pickupDate, deliveryDate: deliveryDate};

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
                                        $("#errorAlertTitle").html("Error");
                                        $("#errorAlertBody").html("unable to save relay.");
                                        $("#errorAlert").modal('show');
                                    }
                                });
                            }
                        }

                        var customer_needs_to_orders = {customerNeedsID: id, orderID: data};
                        var orderID = data;

                        $.ajax({
                            url: '<?php echo API_HOST_URL ?>' + "/customer_needs_to_orders",
                            type: "POST",
                            data: JSON.stringify(customer_needs_to_orders),
                            contentType: "application/json",
                            async: false,
                            success: function(data){

                                var logParams = {logTypeName: "Orders", logMessage: "Commitments has been converted to order", referenceID: orderID};

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
                            error: function(data){

                                $("#errorAlertTitle").html("Error");
                                $("#errorAlertBody").html("There Was An Error Connecting Commitments With Orders");
                                $("#errorAlert").modal('show');
                            }
                        });



                        closeCustomerCommitLegs(id);
                        closeRootCustomerCommitLegs(id);
                        $(document.body).css("cursor", "default");

                        $("#errorAlertTitle").html("Success");
                        $("#errorAlertBody").html("Order Saved");
                        $("#errorAlert").modal('show');

                        getCommitted();
                        closeCommitTransport();
                    }
                    else{
                        $(document.body).css("cursor", "default");

                    }
                },
                error: function(data){

                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("There Was An Error Saving Order");
                    $("#errorAlert").modal('show');
                }
            });
        }
        else{
            $("#errorAlertTitle").html("Error");
            $("#errorAlertBody").html("You must enter at least ONE Trailer.");
            $("#errorAlert").modal('show');
        }

    }


    function addNewCommitment(){
        clearCommitForm();

        $('#commitModalTitle').append("Add Commitment");
        showAddCommit();
    }

    function populateAutocomplete(select, relayNumber){
        var currentCarrier = $(select).val();

        if (currentCarrier != ""){
            $('#relayOptions_' + relayNumber).css("display", "block");
        }
        else{
            $('#relayOptions_' + relayNumber).css("display", "none");
        }

        $.ajax({
            url: '<?php echo API_HOST_URL . "/locations"; ?>' + '?filter=entityID,eq,' + currentCarrier + '&transform=1',
            contentType: "application/json",
            success: function (json) {

                var locations = json.locations;
                var locationdata = [];
                $.each(locations, function(key, location){
                    var value = location.address1;
                    var label = location.address1 + ', ' + location.city + ', ' + location.state + ' ' + location.zip;
                    var id = location.id
                    var city = location.city;
                    var state = location.state;
                    var zip = location.zip;
                    var entry = {id: id, value: value, label: label, city: city, state: state, zip: zip};
                    locationdata.push(entry);
                });

                $('#address_relay' + relayNumber).autocomplete({
                    source: locationdata,
                    minLength: 0,
                    select: function (event, ui) {
                        $('#city_relay' + relayNumber).val(ui.item.city);
                        $('#state_relay' + relayNumber).val(ui.item.state);
                        $('#zip_relay' + relayNumber).val(ui.item.zip);
                    }
                });
            }
        });
    }

    function saveRelayAddressToCarrier(relayNumber){

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

        var locationName = $('#deliveryLocation_relay' + relayNumber).val();
        var entityID = $('#entityID_relay' + relayNumber).val();
        var address = $('#address_relay' + relayNumber).val();
        var city = $('#city_relay' + relayNumber).val();
        var state = $('#state_relay' + relayNumber).val();
        var zip = $('#zip_relay' + relayNumber).val();

        var locationData = {entityID: entityID, locationTypeID: 3, name: locationName, address1: address, address2: "",
        city: city, state: state, zip: zip, latitude: 0.00, longitude: 0.00, timeZone: "", status: "Active",
        createdAt: today, updatedAt: today};

        $.ajax({
            url: '<?php echo API_HOST_URL . "/locations"; ?>',
            type: 'POST',
            data: JSON.stringify(locationData),
            contentType: "application/json",
            async: false,
            success: function (data) {
                if(data > 0){
                    var entitySelect = $('#entityID_relay' + relayNumber);
                    populateAutocomplete(entitySelect, relayNumber);

                    $("#errorAlertTitle").html("Success");
                    $("#errorAlertBody").html("Address Saved");
                    $("#errorAlert").modal('show');

                }
                else{
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Unable to Save Address");
                    $("#errorAlert").modal('show');
                }
            },
            error: function(){
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Unable to Save Address");
                $("#errorAlert").modal('show');
            }
        });


    }

 </script>
