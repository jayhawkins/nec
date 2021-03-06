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

$allentities = '';
$allentities = json_decode(file_get_contents(API_HOST_URL . '/entities?columns=id,name,rateType,negotiatedRate&order=name&filter[]=id,gt,0&transform=1'));

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


// No longer needed. We don't load via PHP anymore. All handled in JS function.
//$getlocations = json_decode(file_get_contents(API_HOST_URL . '/locations?include=location_types&columns=locations.name,location_types.name,locations.address1,locations.address2,locations.city,locations.state,locations.zip,locations.status&filter=entityID,eq,' . $_SESSION['entityid'] . '&order=locationTypeID'),true);
//$locations = php_crud_api_transform($getlocations);

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

     var allentities = <?php echo json_encode($allentities); ?>;

     var entity = <?php echo json_encode($entity); ?>;
     //alert(JSON.stringify(entity));
     //console.log(JSON.stringify(entity.entities.records[0][1]));

     var entityid = <?php echo $_SESSION['entityid']; ?>;
     var entitytype = <?php echo $_SESSION['entitytype']; ?>;

     var admin = false;
     if (entityid == 0) {
         admin = true;
     } else {
         admin = false;
     }

     var commitOriginationCity = [];
     var commitDestinationCity = [];

     var myApp;
      myApp = myApp || (function () {
       var pleaseWaitDiv = $('<div class="modal hide" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false"><div class="modal-header"><h1>Processing...</h1></div><div class="modal-body"><div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div></div></div>');
       return {
           showPleaseWait: function() {
               pleaseWaitDiv.modal();
           },
           hidePleaseWait: function () {
               pleaseWaitDiv.modal('hide');
           },

       };
      })();

      function post() {

          // Verify date carrier selects is not before date available of the trailer
          var d1 = Date.parse($("#havailableDate").val());
          var d2 = Date.parse($("#pickupDate").val());
          var d3 = Date.parse($("#deliveryDate").val());
          if (d1 > d2) {
              //alert ("Your pickup date is prior to the availability date!");
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Your pickup date is prior to the availability date!");
                $("#errorAlert").modal('show');
              return false;
          }
          if (d1 > d3) {
              //alert ("Your delivery date is prior to the availability date!");
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Your delivery date is prior to the availability date!");
                $("#errorAlert").modal('show');
              return false;
          }
          if (d2 > d3) {
              //alert ("Your pickup date is after your delivery date!");
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Your pickup date is after your delivery date!");
                $("#errorAlert").modal('show');
              return false;
          }

          //var originationaddress = $("#originationAddress1").val() + ', ' + $("#originationCity").val() + ', ' + $("#originationState").val() + ', ' + $("#originationZip").val();
          //var destinationaddress = $("#destinationAddress1").val() + ', ' + $("#destinationCity").val() + ', ' + $("#destinationState").val() + ', ' + $("#destinationZip").val();
          var originationaddress = $("#originationCity").val() + ', ' + $("#originationState").val();
          var destinationaddress = $("#destinationCity").val() + ', ' + $("#destinationState").val();

          if (originationaddress != $("#originToMatch").val() && destinationaddress != $("#destToMatch").val()) {
              //alert("The commitment for this Availablility must be picked up at " + $("#originToMatch").val() + " or dropped off at " + $("#destToMatch").val() + ". Please make a valid selection.");

                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("The commitment for this Availablility must be picked up at " + $("#originToMatch").val() + " or dropped off at " + $("#destToMatch").val() + ". Please make a valid selection.");
                $("#errorAlert").modal('show');

              return false;
          }

            //console.log(commitOriginationCity);
            //console.log(commitDestinationCity);
            //console.log($("#ocity").val());
            //console.log($("#dcity").val());
            //console.log('origin city: ' + commitOriginationCity.indexOf($("#ocity").val()));
            //console.log('dest city: ' + commitDestinationCity.indexOf($("#dcity").val()));

          if (commitOriginationCity.length == 2 && !admin && ( (commitOriginationCity.indexOf($("#ocity").val()) == -1 && $("#originationCity").val() != $("#ocity").val()) || (commitDestinationCity.indexOf($("#dcity").val()) == -1 && $("#destinationCity").val() != $("#dcity").val()) ) ) {
              //$("#relayMessage").html("<strong>" + $("#ocity").val() + " or " + $("#dcity").val() + " must have be in at least one relay</strong>");
              //$("#relayDialog").modal('show');
              //alert("Check the current relays. " + $("#ocity").val() + " or " + $("#dcity").val() + " must be specified in at least one relay");

                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Check the current relays. " + $("#ocity").val() + " or " + $("#dcity").val() + " must be specified in at least one relay");
                $("#errorAlert").modal('show');
                return false;
          }

          //if (confirm("You have selected to Commit to this Availability. A Nationwide Equipment Control team member will contact you within 4 buisness hours to start the order process. Do you wish to proceed with this commitment?") == true) {
          msg = Messenger().post({
                message: "You have selected to Commit to this Availability. A Nationwide Equipment Control team member will contact you shortly to start the order process. Do you wish to proceed with this commitment?",
                actions: {
                    retry: {
                        label: 'Yes',
                        phrase: 'Committing to Availability',
                        auto: false,
                        //delay: 10,
                        action: function() {
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
                                                        // alert("Destination Address does not exist!");
                                                        $("#errorAlertTitle").html("Error");
                                                        $("#errorAlertBody").html("Destination Address does not exist!");
                                                        $("#errorAlert").modal('show');
                                                    } else {
                                                        // alert("Destination Address Error: " + JSON.stringify(response));
                                                        $("#errorAlertTitle").html("Destination Address Error");
                                                        $("#errorAlertBody").html(JSON.stringify(response));
                                                        $("#errorAlert").modal('show');
                                                    }
                                                    result = false;
                                                    //alert('Preparation Failed!');
                                                }
                                             },
                                             error: function(response) {
                                                if (response == "ZERO_RESULTS") {
                                                    // alert("Destination Address does not exist!");
                                                    $("#errorAlertTitle").html("Error");
                                                    $("#errorAlertBody").html("Destination Address does not exist!");
                                                    $("#errorAlert").modal('show');
                                                } else {
                                                    // alert("Destination Address Error: " + JSON.stringify(response));
                                                    $("#errorAlertTitle").html("Destination Address Error");
                                                    $("#errorAlertBody").html(JSON.stringify(response));
                                                    $("#errorAlert").modal('show');
                                                }
                                                result = false;
                                                //alert('Failed Searching for Destination Location! - Notify NEC of this failure.');
                                             }
                                          });
                                      } else {
                                          if (response == "ZERO_RESULTS") {
                                                //alert("Origination Address does not exist!");
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
                                          //alert('Preparation Failed!');
                                      }
                                   },
                                   error: function(response) {
                                        //alert("Issue With Origination Address: " + JSON.stringify(response));
                                        $("#errorAlertTitle").html("Issue With Origination Address");
                                        $("#errorAlertBody").html(JSON.stringify(response));
                                        $("#errorAlert").modal('show');
                                      result = false;
                                      //alert('Failed Searching for Origination Location! - Notify NEC of this failure.');
                                   }
                                });

                                if (result) {
                                    verifyAndPost(function(data) {
                                        //alert(data);
                                        $("#errorAlertTitle").html("Message");
                                        $("#errorAlertBody").html(JSON.stringify(data));
                                        $("#errorAlert").modal('show');
                                        $("#load").html("Commit");
                                        $("#load").prop("disabled", false);
                                    });
                                    return true;
                                } else {
                                    return false;
                                }
                        }
                    },
                    cancel: {
                        label: 'No',
                        delay: 10,
                        action: function() {
                            return msg.cancel();
                          }
                    }
                }
          });
          //} else {

          //      $("#myModalCommit").modal('hide');

          //}
      }

      function verifyAndPost() {

          if ( $('#formNeed').parsley().validate() ) {

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
                                  var carrierID = $("#entityID").val();

                                  var url = '<?php echo HTTP_HOST."/createcustomerneedsfromexisting" ?>';
                                  var date = today;
                                  var recStatus = 'Available';
                                  var data = {id: $("#id").val(), rootCustomerNeedsID: $("#rootCustomerNeedsID").val(), carrierID: carrierID, qty: $("#qty").val(), originationAddress1: newOriginationAddress1, originationCity: newOriginationCity, originationState: newOriginationState, originationZip: newOriginationZip, destinationAddress1: newDestinationAddress1, destinationCity: newDestinationCity, destinationState: newDestinationState, destinationZip: newDestinationZip, originationLat: originationlat, originationLng: originationlng, destinationLat: destinationlat, destinationLng: destinationlng, distance: distance,  transportationMode: $("#transportationMode").val(),transportation_mode: $("#transportationMode").val(), transportation_type: $('input[name="transportationType"]:checked').val(), pickupDate: $("#pickupDate").val(), deliveryDate: $("#deliveryDate").val(), rate: $("#rate").val(), rateType: $("#rateType").val()};
                                  $.ajax({
                                     url: url,
                                     type: 'POST',
                                     data: JSON.stringify(data),
                                     contentType: "application/json",
                                     async: false,
                                     success: function(notification){
                                         //alert("Create from existing: " + notification);
                                         var logParams = {logTypeName: "Customer Needs", logMessage: "A commitment has been made.", referenceID: $("#id").val()};


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

                                         $("#myModalCommit").modal('hide');
                                     },
                                     error: function() {
                                        //alert('Failed creating a new Need from an existing.');
                                        $("#myModalCommit").modal('hide');

                                        $("#errorAlertTitle").html("Error");
                                        $("#errorAlertBody").html("Failed creating a new Need from an existing.");
                                        $("#errorAlert").modal('show');
                                     }
                                  });
                              //}

                              $("#myModal").modal('hide');
                              loadTableAJAX();
                              $("#id").val('');
                              $("#rootCustomerNeedsID").val('');
                              $("#qty").val('');
                              $("#rate").val('');
                              $("#rateType").val('');
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
                              passValidation = true;

                });

            } else {

                return false;

            }

      }


      function loadTableAJAX() {

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

        if (<?php echo $_SESSION['entityid']; ?> > 0) {
            var url = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=entities&columns=id,rootCustomerNeedsID,entityID,qty,availableDate,expirationDate,transportationMode,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,entities.name,entities.rateType,entities.negotiatedRate&filter[0]=rootCustomerNeedsID,eq,0&filter[1]=expirationDate,ge,' + today + '&filter[2]=status,eq,Available&order[]=createdAt,desc&transform=1';
            var show = false;
        } else {
            var url = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=entities&columns=id,rootCustomerNeedsID,entityID,qty,availableDate,expirationDate,transportationMode,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,customer_needs_commit.id,customer_needs_commit.status,customer_needs_commit.rate,customer_needs_commit.transporation_mode,entities.name,entities.rateType,entities.negotiatedRate&satisfy=all&filter[0]=rootCustomerNeedsID,eq,0&filter[1]=status,eq,Available&filter[2]=expirationDate,ge,' + today + '&order[0]=entityID&order[1]=rootCustomerNeedsID&order[2]=createdAt,desc&transform=1';
            var show = true;
        }

        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            bSort: true,
            "pageLength": 50,
            ajax: {
                url: url,
                dataSrc: 'customer_needs'
            },
            columns: [
                {
                    data: function (o) {

                        var buttons = '<div class="pull-right text-nowrap">';
                        buttons += '</div>';
                        /* This causes way too much latency with larger datasets. Figure out a new way to handle this.
                        var checkurl = '<?php echo API_HOST_URL; ?>' + '/customer_needs_commit?include=customer_needs&filter[]=customer_needs.rootCustomerNeedsID,eq,' + o.id + '&filter[]=entityID,eq,'+ <?php echo $_SESSION['entityid']; ?> + '&transform=1';
                        var buttons = '';

                        $.ajax({
                             url: checkurl,
                             type: 'GET',
                             //data: JSON.stringify(data),
                             //contentType: "application/json",
                             async: false,
                             success: function(data){
                                 //console.log(data);
                                 //console.log(data.customer_needs_commit[0].customer_needs.length);

                                if (data.customer_needs_commit[0].customer_needs.length > 0) {
                                  console.log(o.id);
                                  console.log(data.customer_needs_commit[0].customer_needs.length);
                                  buttons += '<div class="pull-right text-nowrap">';
                                  buttons += '<i class=\"glyphicon glyphicon-star text\"></i> <span class=\"text\"></span>';
                                  buttons += '</div>';
                                } else {
                                  buttons += '<div class="pull-right text-nowrap">';
                                  buttons += '</div>';
                                }

                             },
                             error: function() {
                                buttons += '<div class="pull-right text-nowrap">';
                                buttons += '</div>';
                             }
                        });
                        */
                        return buttons;

                    }
                },
                {
                    "className":      'details-control-add',
                    "orderable":      false,
                    "data":           null,
                    "defaultContent": ''
                },
                //{ data: "entities[0].name", visible: show },
                {
                    data: null,
                    "bSortable": true,
                    "mRender": function (o) {

                        var entityName = '';
                        var entityID = o.entityID;

                        allentities.entities.forEach(function(entity){

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
                    "render": function(o) {
                      if (o.availableDate == "0000-00-00") {
                          return '';
                      } else {
                          return formatDate(new Date(o.availableDate));
                      }
                    }
                },
                {
                    data: null,
                    "bSortable": true,
                    "render": function(o) {
                      if (o.expirationDate == "0000-00-00") {
                          return '';
                      } else {
                          return formatDate(new Date(o.expirationDate));
                      }
                    }
                },
                { data: "transportationMode", visible: false },
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
                { data: "distance", render: $.fn.dataTable.render.number(',', '.', 0, ''), visible: false  },
                { data: "needsDataPoints", visible: false },
                { data: "entities[0].name", visible: false },
                { data: "entities[0].rateType", visible: false },
                { data: "entities[0].negotiatedRate", visible: false},
                { data: "status", visible: false},
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var buttons = '<div class="pull-right text-nowrap">';
                        buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-link text\"></i> <span class=\"text\">View Relays</span></button>';
                        buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-plus text\"></i> <span class=\"text\">Commit</span></button>";
                        buttons += '</div>';
                        return buttons;
                    }
                }
            ]
          });

          example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );

          //To Reload The Ajax
          //See DataTables.net for more information about the reload method
          example_table.ajax.reload();
          $("#entityID").prop('disabled', false);
          $("#load").html("Commit");
          $("#load").prop("disabled", false);

      }

      function loadCarrierTableAJAX() {

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

        if (<?php echo $_SESSION['entityid']; ?> > 0) {
            var url = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=entities,customer_needs_commit&columns=id,rootCustomerNeedsID,entityID,qty,availableDate,expirationDate,transportationMode,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,entities.name,entities.rateType,entities.negotiatedRate&filter[0]=rootCustomerNeedsID,eq,0&filter[1]=expirationDate,ge,' + today + '&filter[2]=status,eq,Available&order[]=createdAt,desc&transform=1';
            var show = false;
        } else {
            var url = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=entities&columns=id,rootCustomerNeedsID,entityID,qty,availableDate,expirationDate,transportationMode,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,customer_needs_commit.id,customer_needs_commit.status,customer_needs_commit.rate,customer_needs_commit.transporation_mode,entities.name,entities.rateType,entities.negotiatedRate&satisfy=all&filter[0]=rootCustomerNeedsID,eq,0&filter[]=status,eq,Available&filter[]=expirationDate,ge,' + today + '&order[]=entityID&order[]=rootCustomerNeedsID&order[]=createdAt,desc&transform=1';
            var show = true;
        }

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
                //{ data: "entities[0].name", visible: show },
                {
                    data: null,
                    "bSortable": true,
                    "mRender": function (o) {

                        var entityName = '';
                        var entityID = o.entityID;

                        allentities.entities.forEach(function(entity){

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
                { data: "availableDate" },
                {
                    data: null,
                    "bSortable": true,
                    "render": function(o) {
                      if (o.expirationDate == "0000-00-00") {
                          return '';
                      } else {
                          return o.expirationDate;
                      }
                    }
                },
                { data: "transportationMode", visible: false },
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
                { data: "entities[0].name", visible: false },
                { data: "entities[0].rateType", visible: false },
                { data: "entities[0].negotiatedRate", visible: false},
                { data: "status", visible: false},
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var buttons = '<div class="pull-right text-nowrap">';
                        buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-link text\"></i> <span class=\"text\">View Relays</span></button>';
                        buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-plus text\"></i> <span class=\"text\">Commit</span></button>";
                        buttons += '</div>';
                        return buttons;
                    }
                }
            ]
          });

          example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );

          //To Reload The Ajax
          //See DataTables.net for more information about the reload method
          example_table.ajax.reload();
          $("#entityID").prop('disabled', false);
          $("#load").html("Commit");
          $("#load").prop("disabled", false);

      }

      function recordEnableDisable(status) {
          var passValidation = false;

          if (status == "Disable") {
              var newStatus = 'Inactive';
              var myDialog = "#myDisableDialog";
          } else if (status == "Enable") {
              var myDialog = "#myEnableDialog";
              var newStatus = 'Active';
          } else {
              var myDialog = "#myEnableDialog";
              var newStatus = 'Active';
          }

          var data = {status: newStatus};
          var url = '<?php echo API_HOST_URL . "/customer_needs" ?>/' + $("#id").val();
          var type = "PUT";

          $.ajax({
             url: url,
             type: type,
             data: JSON.stringify(data),
             contentType: "application/json",
             async: false,
             success: function(data){
                if (data > 0) {
                  //$("#myModal").modal('hide');
                  $(myDialog).modal('hide');
                  loadTableAJAX();
                  passValidation = true;
                } else {
                  $(myDialog).modal('hide');
                  //alert("Changing Status of Need Failed!");
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Changing Status of Need Failed!");
                    $("#errorAlert").modal('show');
                }
             },
             error: function() {
                //alert("There Was An Error Changing Need Status!");
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("There Was An Error Changing Need Status!");
                $("#errorAlert").modal('show');
             }
          });

          //return passValidation;
      }

      function cancelCommit() {
          var passValidation = false;

          var data = {status: "Cancelled", cancellationReason: $("#cancellationReason").val(), explainOther: $("#explainOther").val()};
          var url = '<?php echo API_HOST_URL . "/customer_needs_commit" ?>/' + $("#commitid").val();
          var type = "PUT";

          $.ajax({
             url: url,
             type: type,
             data: JSON.stringify(data),
             contentType: "application/json",
             async: false,
             success: function(data){
                if (data > 0) {
                  //$("#myModal").modal('hide');
                  $(myCancelDialog).modal('hide');
                  loadTableAJAX();
                  passValidation = true;
                } else {
                    $(myCancelDialog).modal('hide');
                    //alert("Updating of Cancellation of Commit Failed!");
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Updating of Cancellation of Commit Failed!");
                    $("#errorAlert").modal('show');
                }
             },
             error: function() {
                //alert("There Was An Error Canceling Commit!");
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("There Was An Error Canceling Commit!");
                $("#errorAlert").modal('show');
             }
          });

          //return passValidation;
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
                      var url = '<?php echo HTTP_HOST."/deletelocationcontacts" ?>';
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
                                        //alert("There Was An Error Adding Need Contacts!");
                                        $("#errorAlertTitle").html("Error");
                                        $("#errorAlertBody").html("There Was An Error Adding Need Contacts!");
                                        $("#errorAlert").modal('show');
                                    }
                                 });
                            } else {
                                  //alert("There Was An Issue Clearing Need Contacts!");
                                $("#errorAlertTitle").html("Error");
                                $("#errorAlertBody").html("There Was An Issue Clearing Need Contacts!");
                                $("#errorAlert").modal('show');
                            }
                         },
                         error: function() {
                             // alert("There Was An Error Deleting Need Records!");
                            $("#errorAlertTitle").html("Error");
                            $("#errorAlertBody").html("There Was An Error Deleting Need Records!");
                            $("#errorAlert").modal('show');
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

          var url = '<?php echo API_HOST_URL . "/locations_contacts?columns=location_id,contact_id&filter=entityID,eq," . $_SESSION['entityid'] ?>';
          var type = "GET";

          $.ajax({
             url: url,
             type: type,
             async: false,
             success: function(data){
                  locations_contacts = data;
             },
             error: function() {
                //alert("There Was An Error Retrieving Location Contacts!");
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("There Was An Error Retrieving Location Contacts!");
                $("#errorAlert").modal('show');
             }
          });
      }

      function getLocations(city) {

          var url = '<?php echo API_HOST_URL . "/locations?columns=id,city,state,zip&filter[]=entityID,eq," . $_SESSION['entityid'] ?>';
          url += "&filter[]=city,sw," + city;
          var type = "GET";

          $.ajax({
             url: url,
             type: type,
             async: false,
             success: function(data){
                  //alert(JSON.stringify(data));
                $("#errorAlertTitle").html("Message");
                $("#errorAlertBody").html(JSON.stringify(data));
                $("#errorAlert").modal('show');
             },
             error: function() {
                //alert("There Was An Error Retrieving Location Contacts!");
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("There Was An Error Retrieving Location Contacts!");
                $("#errorAlert").modal('show');
             }
          });
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

 </style>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">My One Way Trailer Opportunities</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">One Way Trailer Opportunities Available to Transport</span></h4>
         <div class="widget-controls">
             <a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
             <a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>
             <a data-widgster="close" title="Close" href="#"><i class="glyphicon glyphicon-remove"></i></a>
         </div>
     </header>
     <div class="widget-body">
         <!--p>
             Column sorting, live search, pagination. Built with
             <a href="http://www.datatables.net/" target="_blank">jQuery DataTables</a>
         </p -->
         <!--button type="button" id="addNeed" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Need</button-->
         <div class="pull-right text-nowrap">
            <button type="button" id="myRelays" class="btn btn-primary">View My Relays</button>
            <button type="button" id="allRelays" class="btn btn-primary">View All Relays</button>
         </div>
         <br /><br />

         <!-- Remove this for now until we can determine a better way to handle displaying committed to relays -->
         <!--div><strong><i class="glyphicon glyphicon-star text"></i> <span class="text">indicates you have committed to relays on this availability</span></strong></div-->

         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th></th>
                     <th></th>
                     <th>Company</th>
                     <th>ID</th>
                     <th>Root Customer Needs ID</th>
                     <th>Entity ID</th>
                     <th>Qty</th>
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
                     <th>Name</th>
                     <th>Rate Type</th>
                     <th>Negotiated Rate</th>
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

 <!-- Modal -->
 <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true" aria-label="exampleModalLabel">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel"><strong>Availablity Details</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formView" class="register-form mt-lg">
                 <input type="hidden" id="id" name="id" value="" />
                 <div class="row">
                     <div class="col-sm-2">
                         <label for="dspqty">Trailers Available:</label>
                         <div id="dspqty" class="form-group form-control mb-sm">
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="dspavailableDate">Available Date</label>
                         <div id="dspavailableDate" class="form-group form-control mb-sm">
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="dspexpirationDate">Available Date</label>
                         <div id="dspexpirationDate" class="form-group form-control mb-sm">
                         </div>
                     </div>
                     <div class="col-sm-4">
                         <div class="form-group">
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-7">
                         <label for="dsporiginationCity">Origination City</label>
                         <div id="dsporiginationCity" class="form-group form-control mb-sm">
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="dsporiginationState">Origination State</label>
                         <div id="dsporiginationState" class="form-group form-control mb-sm">
                         </div>
                     </div>
                     <div class="col-sm-2">
                         <label for="dsporiginationZip">Origination Zip</label>
                         <div id="dsporiginationZip" class="form-group form-control mb-sm">
                         </div>
                     </div>
                 </div>
                 <div class="row">
                   <div class="col-sm-7">
                       <label for="dspdestinationCity">Destination City</label>
                       <div id="dspdestinationCity" class="form-group form-control mb-sm">
                       </div>
                   </div>
                   <div class="col-sm-3">
                       <label for="dspdestinationState">Destination State</label>
                       <div id="dspdestinationState" class="form-group form-control mb-sm">
                       </div>
                   </div>
                   <div class="col-sm-2">
                       <label for="dspdestinationZip">Destination Zip</label>
                       <div id="dspdestinationZip" class="form-group form-control mb-sm">
                       </div>
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
                        </div>
                     </div>
                 </div>
                </form>
       </div>
       <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <!--button type="button" class="btn btn-primary" id="btnCommit">Commit To Need</button-->
       </div>
     </div>
   </div>
 </div>

  <!-- Modal -->
  <div class="modal fade" id="myModalCommit" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Commit To Availablity</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
                <form id="formNeed" name="formNeed" class="register-form mt-lg">
                  <input type="hidden" id="id" name="id" value="" />
                  <input type="hidden" id="rootCustomerNeedsID" name="rootCustomerNeedsID" value="" />
                  <input type="hidden" id="originToMatch" name="originToMatch" value="" />
                  <input type="hidden" id="destToMatch" name="destToMatch" value="" />
                  <input type="hidden" id="oaddress1" name="oaddress1" value="" />
                  <input type="hidden" id="ocity" name="ocity" value="" />
                  <input type="hidden" id="ostate" name="ostate" value="" />
                  <input type="hidden" id="ozip" name="ozip" value="" />
                  <input type="hidden" id="daddress1" name="daddress1" value="" />
                  <input type="hidden" id="dcity" name="dcity" value="" />
                  <input type="hidden" id="dstate" name=""dstate value="" />
                  <input type="hidden" id="dzip" name="dzip" value="" />
                  <input type="hidden" id="havailableDate" name="havailableDate" value="" />
                  <input type="hidden" id="rate" name="rate" value="" />
                  <input type="hidden" id="rateType" name="rateType" value="" />
                  <div id="divMaxRelayMessage" style="display: none" class="row">
                      <div class="col-sm-12 center-block">
                          <strong>Max relays reached. You must select this route in its entirety.</strong>
                      </div>
                      <div class="col-sm-12">&nbsp;</div>
                  </div>
                  <div class="row">
                      <div class="col-sm-2">
                          <label for="qtyDiv"># of Trailers</label>
                          <div id="qtyDiv" class="form-group">
                          </div>
                      </div>
                      <div class="col-sm-3">
                          <label for="availableDate">Pick-Up Date</label>
                          <div class="form-group">
                            <div id="sandbox-container" class="input-group date  datepicker">
                               <input type="text" id="pickupDate" name="pickupDate" class="form-control" placeholder="Pickup Date" required="required"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                            </div>
                          </div>
                      </div>
                      <div class="col-sm-3">
                          <label for="expirationDate">Delivery Date</label>
                          <div class="form-group">
                            <div id="sandbox-container" class="input-group date  datepicker">
                               <input type="text" id="deliveryDate" name="deliveryDate" class="form-control" placeholder="Delivery Date" required="required"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                            </div>
                          </div>
                      </div>
                      <div class="col-sm-4">
                          <div class="form-group">
              <?php if ($_SESSION['entityid'] > 0) { ?>
                             <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
              <?php } else { ?>
                              <label for="entityID">Carrier</label>
                              <select id="entityID" name="entityID" data-placeholder="Carrier" class="form-control chzn-select" required="required">
                                <option selected=selected value=""> -Select Carrier- </option>
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
                  <div class="row">
                      <div class="col-sm-3">
                          <label for="originationCity">Origination City</label>
                          <div class="form-group">
                            <input type="hidden" id="originationLocationID" name="originationLocationID" />
                            <input type="text" id="originationCity" name="originationCity" class="form-control mb-sm" placeholder="Origin City"
                            required="required" />
                          </div>
                          <div id="org-suggesstion-box" class="frmSearch"></div>
                      </div>
                      <!--
                      <div class="col-sm-4">
                          <label for="originationAddress1">Origination Address</label>
                          <div class="form-group">
                            <input type="text" id="originationAddress1" name="originationAddress1" class="form-control mb-sm" placeholder="Origin Address"
                            required="required" />
                          </div>
                      </div>
                      -->
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
                      <!--
                      <div class="col-sm-2">
                          <label for="originationZip">Origination Zip</label>
                          <div class="form-group">
                            <input type="text" id="originationZip" name="originationZip" class="form-control mb-sm" placeholder="Origin Zip"
                            required="required" />
                          </div>
                      </div>
                      -->
                      <div class="col-sm-4">
                      </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-3">
                        <label for="DestinationCity">Destination City</label>
                        <div class="form-group">
                          <input type="text" id="destinationCity" name="destinationCity" class="form-control mb-sm" placeholder="Dest. City"
                          required="required" />
                        </div>
                        <div id="dest-suggesstion-box" class="frmSearch"></div>
                    </div>
                    <!--
                    <div class="col-sm-4">
                        <label for="destinationAddress1">Destination Address</label>
                        <div class="form-group">
                          <input type="text" id="destinationAddress1" name="destinationAddress1" class="form-control mb-sm" placeholder="Destination Address"
                          required="required" />
                        </div>
                    </div>
                    -->
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
                    <!--
                    <div class="col-sm-2">
                        <label for="destinationZip">Destination Zip</label>
                        <div class="form-group">
                          <input type="text" id="destinationZip" name="destinationZip" class="form-control mb-sm" placeholder="Dest. Zip"
                          required="required" />
                        </div>
                    </div>
                    -->
                    <div class="col-sm-4">
                    </div>
                  </div>
                  <hr/>
                  <div class="row">
                      <!--
                      <div class="col-sm-3">
                          <label for="rate">Rate to Transport</label>
                          <div class="form-group">
                            <input type="text" id="rate" name="rate" class="form-control mb-sm" placeholder="$ Rate to Transport"
                            required="required" />
                          </div>
                      </div>
                      <div class="col-sm-3">
                          <label for="rate">Rate Type</label>
                          <div class="form-group">
                            <div class="d-inline-block"><input type="radio" id="transportionType" name="transportationType" value="Flat Rate" /> Flat Rate
                            &nbsp;&nbsp;<input type="radio" id="transportionType" name="transportationType" value="Mileage" /> Mileage</div>
                          </div>
                      </div>
                      -->
                      <div class="col-sm-3">
                        <label for="transportationModeDiv">Transportation Mode</label>
                        <div id="transportationModeDiv" class="form-group">
                        </div>
                      </div>
                      <div class="col-sm-9">
                      </div>
                  </div>
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary btn-md" onclick="return post();" id="load">Commit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="myCancelDialog" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cancelDialogLabel"></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
                <form id="formCancel" class="register-form mt-lg">
                  <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
                  <input type="hidden" id="commitid" name="commitid" value="" />
                  <div class="row">
                      <div class="col-sm-12">
                        <label for="cancellationReadon">Reason for Cancellation</label>
                        <select id="cancellationReason" name="cancellationReason" data-placeholder="Cancellation Reason" class="form-control chzn-select" required="required">
                          <option value="">*Select Reason...</option>
                          <option value="Trailers No Longer Available">Trailers No Longer Available</option>
                          <option value="Need No Longer Available">Need No Longer Available</option>
                          <option value="Submitted In Error">Submitted In Error</option>
                          <option value="Other">Other</option>
                        </select>
                      </div>
                  </div>
                  <div class="row">
                      <div class="col-sm-12">
                        &nbsp;
                      </div>
                  </div>
                  <div class="row">
                      <div class="col-sm-12">
                        <label for="explainOther">If "Other" please explain</label>
                        <textarea id="explainOther" name="explainOther" class="form-control"></textarea>
                      </div>
                  </div>
                 </form>
        </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary" onclick="return cancelCommit();">Submit</button>
         </div>
       </div>
     </div>
   </div>

   <!-- Modal -->
  <div class="modal fade" id="relayDialog" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cancelDialogLabel"></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
              <div class="row">
                  <div class="col-sm-12" id="relayMessage">

                  </div>
              </div>
        </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
         </div>
       </div>
     </div>
   </div>


<!-- These modals are currently not used -->
  <!-- Modal -->
  <div class="modal fade" id="myDisableDialog" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="disableDialogLabel"></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
                <form id="formRegister" class="register-form mt-lg">
                  <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
                  <input type="hidden" id="id" name="id" value="" />
                  <div class="row">
                      <div class="col-sm-12">
                          <div class="form-group">
                            <h5>Do you wish to close this need?</h5>
                          </div>
                      </div>
                  </div>
                 </form>
        </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Close');">Close Need</button>
         </div>
       </div>
     </div>
   </div>

   <div class="modal fade" id="myEnableDialog" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-md" role="document">
       <div class="modal-content">
         <div class="modal-header">
           <h5 class="modal-title" id="enableDialogLabel"></h5>
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body">
                 <form id="formRegister" class="register-form mt-lg">
                   <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
                   <input type="hidden" id="id" name="id" value="" />
                   <div class="row">
                       <div class="col-sm-12">
                           <div class="form-group">
                             <h5>Do you wish to open this need?</h5>
                           </div>
                       </div>
                   </div>
                  </form>
         </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Enable');">Open Need</button>
          </div>
        </div>
      </div>
    </div>

 <script>

    //$( "#originationState" ).select2();
    //$( "#destinationState" ).select2();

    Messenger.options = {
        extraClasses: 'messenger-fixed messenger-on-top'
    }

    loadTableAJAX();

    var table = $("#datatable-table").DataTable();
    var tableContact = $("#datatable-table-contact").DataTable();
    var tableDataPoints = $("#datatable-table-datapoints").DataTable();

    $('.datepicker').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });

    $("#myRelays").unbind('click').on('click', function(){
        loadCarrierTableAJAX();
    });

    $("#allRelays").unbind('click').on('click', function(){
        loadTableAJAX();
    });

    $("#addNeed").click(function(){
      var li = '';
      var checked = '';
      var dpli = '';
      var dpchecked = '';
      $("#id").val('');
      $("#rootCustomerNeedsID").val('');
      $("#qty").val('');
      $("#availableDate").val('');
      $("#originationCity").val('');
      $("#originationState").val('');
      $("#originationZip").val('');
      $("#destinationCity").val('');
      $("#destinationState").val('');
      $("#destinationZip").val('');
      for (var i = 0; i < contacts.contacts.records.length; i++) {
          li += '<li id=\"' + contacts.contacts.records[i][0] + '\" class=\"list-group-item\" ' + checked + '>' + contacts.contacts.records[i][1] + ' ' + contacts.contacts.records[i][2] + '</li>\n';
      }
      $("#check-list-box").html(li);
      for (var i = 0; i < dataPoints.object_type_data_points.length; i++) {
          dpli += '<li>' + dataPoints.object_type_data_points[i].title +
                  ' <select class="form-control mb-sm" id="' + dataPoints.object_type_data_points[i].columnName + '" name="' + dataPoints.object_type_data_points[i].columnName + '">';
          for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {
              dpli += '<option>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</option>\n';
          }
          dpli += '</select>' +
                  '</li>\n';
      }
      $("#dp-check-list-box").html(dpli);
      formatListBox();
      formatListBoxDP();
      $("#entityID").prop('disabled', false);
  		$("#myModal").modal('show');
  	});

    $('#datatable-table tbody').unbind('click').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();

        if (this.textContent.indexOf("View Details") > -1) {
            var li = '';
            var checked = '';
            var dpli = '';
            var dpchecked = '';
            $("#id").val(data["id"]);
            $("#rootCustomerNeedsID").val(data["rootCustomerNeedsID"]);
            $("#entityID").val(data["entityID"]);
            $("#dspqty").html(data["qty"]);
            $("#dspavailableDate").html(data["availableDate"]);
            $("#dspexpirationDate").html(data["expirationDate"]);
            $("#dsporiginationCity").html(data["originationCity"]);
            $("#dsporiginationState").html(data["originationState"]);
            $("#dsporiginationZip").html(data["originationZip"]);
            $("#dspdestinationCity").html(data["destinationCity"]);
            $("#dspdestinationState").html(data["destinationState"]);
            $("#dspdestinationZip").html(data["destinationZip"]);
            var ndp = data["needsDataPoints"];
            var con = data["contactEmails"];

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

                dpli += '<li>' + dataPoints.object_type_data_points[i].title;
                for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {

                    if (dataPoints.object_type_data_points[i].object_type_data_point_values[v].value === value) {
                        selected = ' selected ';
                        //dpli += '<option' + selected + '>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</option>\n';
                        dpli += ' => <strong>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</strong>\n';
                    } else {
                        selected = '';
                    }
                }

                dpli += '</li>\n';
            }
            $("#dp-check-list-box").html(dpli);
            formatListBox();
            formatListBoxDP();
            $("#entityID").prop('disabled', true);
            $("#myModal").modal('show');
        } else if (this.textContent.indexOf("Commit") > -1) {
            // Enable just in case was disabled in last commit call
            $("#originationCity").prop("disabled", false);
            $("#originationState").prop("disabled", false);
            $("#destinationCity").prop("disabled", false);
            $("#destinationState").prop("disabled", false);
            // Hide the max relay message and show if response shows 2 existing relays
            $("#divMaxRelayMessage").hide();
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
            $("#havailableDate").val(data["availableDate"]);
            // Set up the matching addresses like we do up in the verifyAndPost() - makes it easy to do a compare
            //$("#originToMatch").val(data["originationAddress1"] + ', ' + data["originationCity"] + ', ' + data["originationState"] + ', ' + data["originationZip"]);
            //$("#destToMatch").val(data["destinationAddress1"] + ', ' + data["destinationCity"] + ', ' + data["destinationState"] + ', ' + data["destinationZip"]);
            $("#originToMatch").val(data["originationCity"] + ', ' + data["originationState"]);
            $("#destToMatch").val(data["destinationCity"] + ', ' + data["destinationState"]);
            //$("#rate").val(data["entities"][0].negotiatedRate.toFixed(2));
            //$("#rateType").val(data["entities"][0].rateType);

            for (var i = 1; i <= data['qty']; i++) {
                if (i == data['qty']) {
                    dpchecked = "selected=selected";
                }
                qtyselect += '<option ' + dpchecked + '>' + i + '</option>\n';
            }
            qtyselect += '</select>\n';
            $("#qtyDiv").html(qtyselect);

            //if (entity.entities.records[0][0] == "Flat Rate") {
            if (data.rateType == "Flat Rate") {
                $('input[name="transportationType"][value="Flat Rate"]').prop('checked', true);
            } else {
                $('input[name="transportationType"][value="Mileage"]').prop('checked', true);
            }
/*
            for (var i = 0; i < data['needsDataPoints'].length; i++) {
                for (var key in data['needsDataPoints'][i]) {
                    if (key == "transportation_type") {
                        if (data['needsDataPoints'][i][key] == "Tow Empty") {
                            transportationtypeselect += '<option>Tow Empty</option>\n';
                        } else {
                            transportationtypeselect += '<option>Tow Empty</option>\n';
                            transportationtypeselect += '<option>Load Out</option>\n';
                            transportationtypeselect += '<option>Either (Empty or Load Out)</option>\n';
                        }
                        i = data['needsDataPoints'].length; // Get out of the loop - we already did what we needed to do.
                    }
                }
            }
*/

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

            // Determine if this is the third relay. If so, lock down Origination and Destination so they have to select it. Limit relays to 3.
            var url = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,availableDate,expirationDate,customer_needs_commit.originationCity,customer_needs_commit.originationState,customer_needs_commit.destinationCity,customer_needs_commit.destinationState&filter[]=rootCustomerNeedsID,eq,' + data['id'] + '&order[]=createdAt,desc&transform=1';
            var params = {id: data['id']};
            $.ajax({
               url: url,
               type: 'GET',
               //data: JSON.stringify(params),
               contentType: "application/json",
               async: false,
               success: function(response){

                    commitOriginationCity = [];
                    commitDestinationCity = [];

                   // Load relay array so we can check relays in post function
                   var needs = response.customer_needs;
                   for (var x = 0; x < needs.length; x++) {
                       var commits = needs[x].customer_needs_commit;
                       for (var y = 0; y < commits.length; y++) {
                           //console.log('Commits ' + y + ': ' + commits[y].destinationCity);
                           commitOriginationCity.push(commits[y].originationCity);
                           commitDestinationCity.push(commits[y].destinationCity);
                       }

                   }

                   if (commitOriginationCity.length == 3 && !admin) { // Per Troy, carriers can only commit to 3 relays total. Only NEC Admin can add more relays....
                        //$("#divMaxRelayMessage").show();
                        //$("#originationCity").prop("disabled", true);
                        //$("#originationState").prop("disabled", true);
                        //$("#destinationCity").prop("disabled", true);
                        //$("#destinationState").prop("disabled", true);
                        ////$("#entityID").prop('disabled', true);
                        $("#relayMessage").html("<h5><strong>Relay limit has been reached. No more relays allowed.</strong></h5>");
                        $("#relayDialog").modal('show');
                    //} else if (commitOriginationCity.length == 2 && !admin && (commitOriginationCity.indexOf($("#originationCity").val()) == -1) ) {
                    //    $("#divMaxRelayMessage").show();
                    //    $("#originationCity").prop("disabled", true);
                    //    $("#originationState").prop("disabled", true);
                    //    $("#destinationCity").prop("disabled", true);
                    //    $("#destinationState").prop("disabled", true);
                        ////$("#entityID").prop('disabled', true);
                    //    $("#myModalCommit").modal('show');
                    } else {
                        $("#myModalCommit").modal('show');
                    }
               },
               error: function() {
                    //alert('Error Selecting Relays for ' + id + '!');
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html('Error Selecting Relays for ' + id + '!');
                    $("#errorAlert").modal('show');
               }
            });

            //$("#entityID").prop('disabled', true);
            //$("#myModalCommit").modal('show');
          } else if (this.textContent.indexOf("Cancel") > -1) {
              $("#commitid").val(data["customer_needs_commit"][0].id);
              $("#myCancelDialog").modal('show');
          } else if (this.textContent.indexOf("View Relays") > -1) {
              var tr = $(this).closest('tr');
              var row = table.row( tr );

              if ( row.child.isShown() ) {
                row.child.hide();
                tr.removeClass('shown');
              } else {
                row.child( formatRelays(row.data()) ).show();
                tr.addClass('shown');
              }
          } else {
            //Nothing - Somehow got in here???
          }

    });

    $('#entityID').on( 'change', function () {

        var entityID = $('#entityID').val();

        var negotiatedRate = '0.00';
        allentities.entities.forEach(function(entity){
            if( entityID == entity.id ){
                negotiatedRate = entity.negotiatedRate;
            }
        });
        $("#rate").val(parseFloat(negotiatedRate.toFixed(2)));

        var rateType = '';
        allentities.entities.forEach(function(entity){
            if( entityID == entity.id ){
                rateType = entity.rateType;
            }
        });
        $("#rateType").val(rateType);

        var params = {id: $("#entityID").val()};
        //alert(JSON.stringify(params));
        $.ajax({
           url: '<?php echo HTTP_HOST."/getcontactsbycustomer" ?>',
           type: 'POST',
           data: JSON.stringify(params),
           contentType: "application/json",
           async: false,
           success: function(response){
             response = JSON.parse(response);
             var li = '';
             for (var i = 0; i < response.contacts.length; i++) {
                 checked = '';
                 /*
                 for (var l = 0; l < con.length; l++) {
                     $.each(con, function(idx, obj) {
                       $.each(obj, function(key, val) {
                         if (contacts.contacts.records[i][0] == key) {
                             checked = 'data-checked="true"';
                         }
                       })
                     });
                 }
                 */
                 li += '<li id=\"' + response.contacts[i].id + '\" class=\"list-group-item\" ' + checked + '>' + response.contacts[i].firstName + ' ' + response.contacts[i].lastName + '</li>\n';
             }
             $("#check-list-box").html(li);
             formatListBox();
           },
           error: function() {
                // alert('Failed Getting Contacts! - Notify NEC of this failure.');
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html('Failed Getting Contacts! - Notify NEC of this failure.');
                $("#errorAlert").modal('show');
           }
        });
    });

    $("#originationCity_Inactive").keyup(function(){
        $("#originationCity").css("background","#FFF url(img/loaderIcon.gif) no-repeat 165px");

        var url = '<?php echo API_HOST_URL; ?>/locations?transform=1&columns=id,name,city&filter[]=entityID,eq,' + $("#entityID").val() + '&filter[]=city,sw,' + $(this).val();

    		$.ajax({
        		type: "GET",
        		url: url,
        		//data: data,
        		beforeSend: function(){
        			$("#originationCity").css("background","#FFF url(img/loaderIcon.gif) no-repeat 165px");
        		},
        		success: function(data){
              var li = '<ul id="origination-list" class="orgSearch">';
              for (var t=0;t<data.locations.length;t++) {
                  li += '<li onClick="selectOrgCity(\'' + data.locations[t].id + '\');" id=\"' + data.locations[t].id + '\">' + data.locations[t].city + ' [' + data.locations[t].name + ']</li>\n';
              }
              li += '</ul>';
              $("#org-suggesstion-box").html(li);
        			$("#org-suggesstion-box").show();
        			$("#originationCity").css("background","#FFF");
        		}
    		});
  	});

    function selectOrgCity(val) {
        var params = {id: val};
        $.ajax({
           url: '<?php echo HTTP_HOST."/getlocation" ?>',
           type: 'POST',
           data: JSON.stringify(params),
           contentType: "application/json",
           async: false,
           success: function(response){
             //response = JSON.stringify(JSON.parse(response));
             response = JSON.parse(response);
             $("#originationAddress1").val(response.address1);
             $("#originationCity").val(response.city);
             $("#originationState").val(response.state);
             $("#originationZip").val(response.zip);
             $("#org-suggesstion-box").html("");
             $("#org-suggesstion-box").hide();
           },
           error: function() {
                // alert('Error Selecting Origination City!');
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html('Error Selecting Origination City!');
                $("#errorAlert").modal('show');
           }
        });
    }

    $("#destinationCity_Inactive").keyup(function(){
        $("#destinationCity").css("background","#FFF url(img/loaderIcon.gif) no-repeat 165px");

        var url = '<?php echo API_HOST_URL; ?>/locations?transform=1&columns=id,name,city&filter[]=entityID,eq,' + $("#entityID").val() + '&filter[]=city,sw,' + $(this).val();

    		$.ajax({
        		type: "GET",
        		url: url,
        		//data: data,
        		beforeSend: function(){
        			$("#destinationCity").css("background","#FFF url(img/loaderIcon.gif) no-repeat 165px");
        		},
        		success: function(data){
              var li = '<ul id="destination-list" class="destSearch">';
              for (var t=0;t<data.locations.length;t++) {
                  li += '<li onClick="selectDestCity(\'' + data.locations[t].id + '\');" id=\"' + data.locations[t].id + '\">' + data.locations[t].city + ' [' + data.locations[t].name + ']</li>\n';
              }
              li += '</ul>';
              $("#dest-suggesstion-box").html(li);
        			$("#dest-suggesstion-box").show();
        			$("#destinationCity").css("background","#FFF");
        		}
    		});
  	});

    function selectDestCity(val) {
        var params = {id: val};
        $.ajax({
           url: '<?php echo HTTP_HOST."/getlocation" ?>',
           type: 'POST',
           data: JSON.stringify(params),
           contentType: "application/json",
           async: false,
           success: function(response){
             //response = JSON.stringify(JSON.parse(response));
             response = JSON.parse(response);
             $("#destinationAddress1").val(response.address1);
             $("#destinationCity").val(response.city);
             $("#destinationState").val(response.state);
             $("#destinationZip").val(response.zip);
             $("#dest-suggesstion-box").html("");
             $("#dest-suggesstion-box").hide();
           },
           error: function() {
                // alert('Error Selecting Destination City!');
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html('Error Selecting Destination City!');
                $("#errorAlert").modal('show');
           }
        });
    }

    $("#myModal").on("hidden.bs.modal", function () {
        $("#entityID").prop('disabled', false);
    });

    $('#btnCommit').click(function () {
        $("#myModalCommit").modal('show');
        /*
        $.ajax({
           url: '<?php echo HTTP_HOST."/getcontactsbycustomer" ?>',
           type: 'POST',
           data: JSON.stringify(params),
           contentType: "application/json",
           async: false,
           success: function(response){
             response = JSON.parse(response);
                 li += '<li id=\"' + response.contacts[i].id + '\" class=\"list-group-item\" ' + checked + '>' + response.contacts[i].firstName + ' ' + response.contacts[i].lastName + '</li>\n';
             }
             $("#check-list-box").html(li);
             formatListBox();
           },
           error: function() {
              alert('Failed Getting Contacts! - Notify NEC of this failure.');
           }
        });
        */
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

    /* Formatting function for row details - modify as you need */
    function formatRelays ( d ) {

        var div = $('<div/>')
            .addClass( 'loading' )
            .text( 'Loading...' );
        var url = '<?php echo API_HOST_URL; ?>' + '/customer_needs?include=customer_needs_commit,entities&filter[]=rootCustomerNeedsID,eq,'+d.id+'&filter[]=customer_needs_commit.status,eq,Available&transform=1';
        var params = {id: d.id};
        $.ajax({
           url: url,
           type: 'GET',
           //data: JSON.stringify(params),
           contentType: "application/json",
           async: false,
           success: function(response){
                response = response.customer_needs;
                var table = '<table  class="col-sm-12" cellpadding="5" cellspacing="0" border="0">';
                    table += '<tr><th>Carrier</th><th>Qty</th><th>Pickup Date</th><th>Delivery Date</th><th>Orig. City</th><th>Orig. State</th><th>Dest. City</th><th>Dest. State</th><th>Mileage</th><th></th></tr>';

                // `d` is the original data object for the row
                if (response.length > 0) {

                    for (var n = 0; n < response.length; n++) {

                        for (var i = 0; i < response[n]['customer_needs_commit'].length; i++) {
                            if (entitytype == 0 || response[n]['customer_needs_commit'][i].entityID == entityid) {
                                var name = response[n]['customer_needs_commit'][i].entities[0].name;
                            } else {
                                var name = '';
                            }
                            table += '</tr>\n';
                            table += '<td>' + name + '</td>';
                            table += '<td>' + response[n]['customer_needs_commit'][i].qty + '</td>';
                            table += '<td>' + response[n]['customer_needs_commit'][i].pickupDate + '</td>';
                            table += '<td>' + response[n]['customer_needs_commit'][i].deliveryDate + '</td>';
                            table += '<td>' + response[n]['customer_needs_commit'][i].originationCity + '</td>';
                            table += '<td>' + response[n]['customer_needs_commit'][i].originationState + '</td>';
                            table += '<td>' + response[n]['customer_needs_commit'][i].destinationCity + '</td>';
                            table += '<td>' + response[n]['customer_needs_commit'][i].destinationState + '</td>';
                            table += '<td>' + response[n]['customer_needs_commit'][i].distance + '</td>';

                            var buttons = '<div class="pull-right text-nowrap">';
                            if ( (response[n]['customer_needs_commit'][i].status == "Cancelled") ) {
                                      buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-plus text\"></i> <span class=\"text\">Commit</span></button>";
                            } else {
                                      buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-exclamation-sign text\"></i> <span class=\"text\">Cancel</span></button>";
                            }
                            buttons += '</div>';

                            // Don't show Cancel button now. Activate when we have time to implement
                            //table += '<td>' + buttons + '</td>';
                            table += '<td>&nbsp;</td>';

                            table += '</tr>\n';
                        }

                    }

                } else {

                    table += '<tr><td colspan="10" align="center" bgcolor="#444444"><font color="#FFFFFF"><b> ***** No Relays Found ***** </b></font></td></tr>';

                }

                table += '</table>\n';

                div
                .html( table )
                .removeClass( 'loading' );
            },
            error: function() {
                // alert('Failed Getting Relays!');
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html('Failed Getting Relays!');
                $("#errorAlert").modal('show');
            }
        } );

        return div;

    }

/*
    $('#datatable-table tbody').on('click', 'td.details-control', function () {

        var tr = $(this).closest('tr');
        var row = table.row( tr );

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        } else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    } );
*/
    $('#datatable-table tbody').on('click', 'td.details-control-add', function () {

        var tr = $(this).closest('tr');
        var row = table.row( tr );
        var td = $(this).closest('td');

        // Open this row
        row.child( format(row.data()) ).show();
        td.addClass('details-control-minus');
        td.removeClass('details-control-add');

    } );

    $('#datatable-table tbody').on('click', 'td.details-control-minus', function () {

        var tr = $(this).closest('tr');
        var row = table.row( tr );
        var td = $(this).closest('td');

        // This row is already open - close it
        row.child.hide();
        td.removeClass('details-control-minus');
        td.addClass('details-control-add');

    } );

 </script>
