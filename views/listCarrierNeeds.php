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

$entities = '';
$entities = json_decode(file_get_contents(API_HOST_URL . '/entities?columns=id,name&order=name&filter[]=id,gt,0&filter[]=entityTypeID,eq,2'));

$entity = '';
$entity = json_decode(file_get_contents(API_HOST_URL . '/entities?filter[]=id,eq,' . $_SESSION['entityid'] . '&transform=1'));

$locationTypeID = '';
$locationTypes = json_decode(file_get_contents(API_HOST_URL . "/location_types?columns=id,name,status&filter[]=entityID,eq," . $_SESSION['entityid'] . "&filter[]=id,gt,0&satisfy=all&order=name"));

$contacts = '';
$contacts = json_decode(file_get_contents(API_HOST_URL . "/contacts?columns=id,firstName,lastName&order=lastName&filter=entityID,eq," . $_SESSION['entityid'] ));

$locations_contacts = '';
$locations_contacts = json_decode(file_get_contents(API_HOST_URL . "/locations_contacts?columns=location_id,contact_id&filter=entityID,eq," . $_SESSION['entityid'] ));

$contactTypes = json_decode(file_get_contents(API_HOST_URL . '/contact_types?columns=id,name&order=id'));

$loccon = array();
for ($lc=0;$lc<count($locations_contacts->locations_contacts->records);$lc++) {
    $loccon[$locations_contacts->locations_contacts->records[$lc][0]] = $locations_contacts->locations_contacts->records[$lc][1];
}

//Get entity configuration_settings
$configuration_settings = $entity->entities[0]->configuration_settings;

$need_expire_days = 30; // This is the default for how many days until expired if entity has not set one
for ($cs=0;$cs<count($configuration_settings);$cs++) {
    while (list($key, $val) = each($configuration_settings[$cs])) {
        //echo "$key => $val\n";
        if ($key == "need_expire_days") {
            $need_expire_days = $val;
        }
    }
}

$dataPoints = json_decode(file_get_contents(API_HOST_URL . "/object_type_data_points?include=object_type_data_point_values&transform=1&columns=id,columnName,title,status,object_type_data_point_values.value&filter[]=entityID,in,(0," . $_SESSION['entityid'] . ")&filter[]=status,eq,Active&order=sort_order" ));


// No longer needed. We don't load via PHP anymore. All handled in JS function.
//$getlocations = json_decode(file_get_contents(API_HOST_URL . '/locations?include=location_types&columns=locations.name,location_types.name,locations.address1,locations.address2,locations.city,locations.state,locations.zip,locations.status&filter=entityID,eq,' . $_SESSION['entityid'] . '&order=locationTypeID'),true);
//$locations = php_crud_api_transform($getlocations);

 ?>

 <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

 <script type="text/javascript" src="https://maps.google.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API; ?>"></script>

 <script>

     var need_expire_days = <?php echo $need_expire_days; ?>;

     var contacts = <?php echo json_encode($contacts); ?>;
     var entities = <?php echo json_encode($entities); ?>;
     var entityID = <?php echo $_SESSION['entityid']; ?>;
     //console.log(contacts);

     var locations_contacts = <?php echo json_encode($locations_contacts); ?>;
     //console.log(locations_contacts);

     var dataPoints = <?php echo json_encode($dataPoints); ?>;
     //console.log(dataPoints);

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

        function reloadContacts(){

            var url = '<?php echo API_HOST_URL . "/contacts?columns=id,firstName,lastName&order=lastName&filter=entityID,eq," . $_SESSION['entityid'] ?>';
            var type = "GET";

            $.ajax({
               url: url,
               type: type,
               async: false,
               success: function(data){
                    contacts = data;
               },
               error: function() {
                  //alert("There Was An Error Retrieving Contacts!");
                  Messenger().post({
                        message: "There Was An Error Retrieving Contacts!",
                        type: 'error',
                        showCloseButton: true
                    });
               }
            });
        }

        function getCarrierContactTitle(entityID){
            var carrierContactTitle = "";
            entities.entities.records.forEach(function(value){
               if(entityID == value[0]){
                   carrierContactTitle = value[1] + " Needs Contacts";

               }
            });

            if(carrierContactTitle == ""){

                   $("#carrierContactsTitle").html("<strong>Carrier Needs Contacts</strong>");
            }
            else{

                   $("#carrierContactsTitle").html("<strong>" + carrierContactTitle + "</strong>");
            }
        }

        function parseDate(input) {
          var parts = input.match(/(\d+)/g);
          // new Date(year, month [, date [, hours[, minutes[, seconds[, ms]]]]])
          return new Date(parts[0], parts[1]-1, parts[2]); // months are 0-based
        }

        function formatFormDates(date) {
            var d = date,
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }

      function post() {
         if ( $('#formNeed').parsley().validate() ) {

                // Build the contacts to verify one was chosen
                var contactsarray = [];
                var obj = $("#check-list-box li.active");
                for (var i = 0; i < obj.length; i++) {
                    item = {};
                    item[obj[i].id] = obj[i].innerText;
                    contactsarray.push(item);
                }
                if (contactsarray.length <= 0) {
                    //alert('You must select a point of contact for this need.');
                    Messenger().post({
                        message: 'You must select a point of contact for this need.',
                        type: 'error',
                        showCloseButton: true
                    });
                    return false;
                }

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
                                if (response == "success") {
                                } else {
                                    //alert("1: " + response);
                                    result = false;
                                    //alert('Preparation Failed!');
                                    Messenger().post({
                                        message: 'Preparation Failed!',
                                        type: 'error',
                                        showCloseButton: true
                                    });
                                }
                             },
                             error: function(response) {
                                //alert("2: " + response);
                                result = false;
                                //alert('Failed Searching for Destination Location! - Notify NEC of this failure.');
                                Messenger().post({
                                        message: 'Failed Searching for Destination Location! - Notify NEC of this failure.',
                                        type: 'error',
                                        showCloseButton: true
                                    });
                             }
                          });
                      } else {
                          //alert("3: " + response);
                          result = false;
                          //alert('Preparation Failed!');
                          Messenger().post({
                                message: '3 - Preparation Failed!',
                                type: 'error',
                                showCloseButton: true
                            });
                      }
                   },
                   error: function(response) {
                      //alert("4: " + JSON.stringify(response));
                      result = false;
                      //alert('Failed Searching for Origination Location! - Notify NEC of this failure.');
                      Messenger().post({
                            message: 'Failed Searching for Origination Location! - Notify NEC of this failure.',
                            type: 'error',
                            showCloseButton: true
                        });
                   }
                });

                if (result) {
                    verifyAndPost(function(data) {
                        $("#load").html("Save Changes");
                        $("#load").prop("disabled", false);
                    });
                } else {
                  return false;
                }

          } else {

              return false;

          }

      }

      function verifyAndPost() {

          $("#load").html("<i class='fa fa-spinner fa-spin'></i> Adding Needs");
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

                var geocoder = new google.maps.Geocoder();
                var originationaddress = $("#originationAddress1").val() + ' ' + $("#originationCity").val() + ' ' + $("#originationState").val() + ' ' + $("#originationZip").val();

                geocoder.geocode( { 'address': originationaddress}, function(originationresults, status) {

                  if (status == google.maps.GeocoderStatus.OK) {

                    var originationlat = originationresults[0].geometry.location.lat();
                    var originationlng = originationresults[0].geometry.location.lng();

                      var destinationaddress = $("#destinationAddress1").val() + ' ' + $("#destinationCity").val() + ' ' + $("#destinationState").val() + ' ' + $("#destinationZip").val();
                      geocoder.geocode( { 'address': destinationaddress}, function(destinationresults, status) {

                          if (status == google.maps.GeocoderStatus.OK) {
                              var destinationlat = destinationresults[0].geometry.location.lat();
                              var destinationlng = destinationresults[0].geometry.location.lng();

                              if ($("#id").val() > '') {
                                  var url = '<?php echo API_HOST_URL . "/carrier_needs" ?>/' + $("#id").val();
                                  type = "PUT";
                              } else {
                                  var url = '<?php echo API_HOST_URL . "/carrier_needs" ?>';
                                  type = "POST";
                              }

                              // Build the contacts
                              var contactsarray = [];
                              var obj = $("#check-list-box li.active");
                              for (var i = 0; i < obj.length; i++) {
                                  item = {};
                                  item[obj[i].id] = obj[i].innerText;
                                  contactsarray.push(item);
                              }
                              var $contacts = contactsarray;

                              // Build the needsDataPoints
                              var needsarray = [];
                              var obj = $("#dp-check-list-box li select");
                              for (var i = 0; i < obj.length; i++) {
                                  item = {};
                                  item[obj[i].id] = obj[i].value;
                                  needsarray.push(item);
                              }
                              var needsdatapoints = needsarray;

                              var availableDateString = $("#availableDate").val();
                              var expirationDateString = $("#expirationDate").val();

                              var availableDate = new Date(parseDate(availableDateString));

                              if (expirationDateString == ""){
                                  var expirationDate = new Date(parseDate(availableDateString));
                                  //expirationDate.setDate(availableDate.getDate() + 30); - No longer using a hardcoded 30 days. Comes from entities->configuration_settings
                                  expirationDate.setDate(availableDate.getDate() + parseInt(need_expire_days));
                                  expirationDateString = formatFormDates(expirationDate);
                              } else{
                                  var expirationDate = new Date(parseDate(expirationDateString));
                                  expirationDateString = formatFormDates(expirationDate);
                              }

                              if (type == "PUT") {
                                  var date = today;
                                  var data = {qty: $("#qty").val(), transportationMode: $("#transportationMode").val(), originationAddress1: $("#originationAddress1").val(), originationCity: $("#originationCity").val(), originationState: $("#originationState").val(), originationZip: $("#originationZip").val(), destinationAddress1: $("#destinationAddress1").val(), destinationCity: $("#destinationCity").val(), destinationState: $("#destinationState").val(), destinationZip: $("#destinationZip").val(), originationLat: originationlat, originationLng: originationlng, destinationLat: destinationlat, destinationLng: destinationlng, needsDataPoints: needsdatapoints, contactEmails: $contacts, availableDate: $("#availableDate").val(), expirationDate: expirationDateString, updatedAt: date};
                              } else {
                                  var date = today;
                                  var recStatus = 'Available';
                                  var data = {entityID: $("#entityID").val(), qty: $("#qty").val(), transportationMode: $("#transportationMode").val(), originationAddress1: $("#originationAddress1").val(), originationCity: $("#originationCity").val(), originationState: $("#originationState").val(), originationZip: $("#originationZip").val(), destinationAddress1: $("#destinationAddress1").val(), destinationCity: $("#destinationCity").val(), destinationState: $("#destinationState").val(), destinationZip: $("#destinationZip").val(), originationLat: originationlat, originationLng: originationlng, destinationLat: destinationlat, destinationLng: destinationlng, needsDataPoints: needsdatapoints, status: recStatus, contactEmails: $contacts, availableDate: $("#availableDate").val(), expirationDate: expirationDateString, createdAt: date, updatedAt: date};
                              }

                              $.ajax({
                                 url: url,
                                 type: type,
                                 data: JSON.stringify(data),
                                 contentType: "application/json",
                                 async: false,
                                 success: function(data){
                                    if (data > 0) {

                                      /* Don't send out notifications now. Do it during the Needs matching cron job
                                      if (type == 'POST') {
                                        var params = {id: data};
                                        $.ajax({
                                           url: '<?php echo HTTP_HOST."/carrierneedsnotification" ?>',
                                           type: 'POST',
                                           data: JSON.stringify(params),
                                           contentType: "application/json",
                                           async: false,
                                           success: function(notification){
                                               $("#load").html("Save Changes");
                                               $("#load").prop("disabled", false);
	                                        	   alert(notification);
                                           },
                                           error: function() {
                                               $("#load").html("Save Changes");
                                               $("#load").prop("disabled", false);
	                                        	   alert('Failed Sending Notifications! - Notify NEC of this failure.');
                                           }
                                        });
                                      }
                                      */

                                      $("#myModal").modal('hide');
                                      loadTableAJAX();
                                      $("#id").val('');
                                      $("#qty").val('');
                                      $("#transportationMode").val('');
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

                                      $("#load").html("Save Changes");
                                      $("#load").prop("disabled", false);

                                    } else {
                                        $("#load").html("Save Changes");
                                        $("#load").prop("disabled", false);
                                        //alert("Adding Need Failed! Invalid Data...");
                                        Messenger().post({
                                            message: "Adding Need Failed! Invalid Data...",
                                            type: 'error',
                                            showCloseButton: true
                                        });
                                    }
                                 },
                                 error: function() {
                                     $("#load").html("Save Changes");
                                     $("#load").prop("disabled", false);
                                     //alert("There Was An Error Adding Location!");
                                     Messenger().post({
                                        message: "There Was An Error Adding Location!",
                                        type: 'error',
                                        showCloseButton: true
                                    });
                                 }
                              });

                          } else {
                              $("#load").html("Save Changes");
                              $("#load").prop("disabled", false);
	                          //alert("ERROR Geo-Coding Destination Address!");
                              Messenger().post({
                                    message: "ERROR Geo-Coding Destination Address!",
                                    type: 'error',
                                    showCloseButton: true
                                });
                          }
                      });
                  } else {
                      $("#load").html("Save Changes");
                      $("#load").prop("disabled", false);
                      //alert("ERROR Geo-Coding Origination Address!");
                      Messenger().post({
                            message: "ERROR Geo-Coding Origination Address!",
                            type: 'error',
                            showCloseButton: true
                        });
                  }
                });

                return passValidation;

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

        //myApp.showPleaseWait();
        if (<?php echo $_SESSION['entityid']; ?> > 0) {

            var pastNeedsDate = new Date();
            //var pastNeedsDateString = new Date(pastNeedsDate.getFullYear(), pastNeedsDate.getMonth()+1, pastNeedsDate.getDate()-need_expire_days);
            var pastNeedsDateString = new Date(pastNeedsDate.getFullYear(), pastNeedsDate.getMonth()+1, pastNeedsDate.getDate()-180); // Only go back 6 months to get history
            pastNeedsDateString = pastNeedsDateString.getFullYear() + '-' + pastNeedsDateString.getMonth() + '-' + pastNeedsDateString.getDate();

            var url = '<?php echo API_HOST_URL; ?>' + '/carrier_needs?include=entities&columns=entities.name,id,entityID,qty,transportationMode,availableDate,expirationDate,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,needsDataPoints,status,contactEmails&filter[0]=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&filter[1]=expirationDate,ge,' + pastNeedsDateString + '&satisfy=all&order[]=expirationDate,desc&transform=1';
            var example_table = $('#datatable-table').DataTable({
                retrieve: true,
                processing: true,
                bSort: true,
                "pageLength": 50,
                ajax: {
                    url: url,
                    dataSrc: 'carrier_needs'
                },
                columns: [
                    { data: "entities[0].name", visible: false },
                    { data: "id", visible: false },
                    { data: "entityID", visible: false },
                    { data: "qty" },
                    { data: "transportationMode", visible: false },
                    { data: "availableDate", visible: false },
                    { data: "expirationDate", visible: false },
                    { data: "originationCity" },
                    { data: "originationState" },
                    { data: "originationZip", visible: false },
                    { data: "originationLat", visible: false },
                    { data: "originationLng", visible: false },
                    { data: "destinationCity" },
                    { data: "destinationState" },
                    { data: "destinationZip", visible: false },
                    { data: "destinationLat", visible: false },
                    { data: "destinationLng", visible: false },
                    { data: "needsDataPoints", visible: false },
                    { data: "contactEmails", visible: false },
                    {
                        data: null,
                        "bSortable": false,
                        "mRender": function (o) {
                            var buttons = '<div class="pull-right text-nowrap">';
                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-edit text\"></i> <span class=\"text\">Edit</span></button>';

                            if (o.status == "Available") {
                                      buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-remove text\"></i> <span class=\"text\">Available</span></button>";
                            } else {
                                      buttons += " &nbsp;<button class=\"btn btn-danger btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-exclamation-sign text\"></i> <span class=\"text\">Unavailable</span></button>";
                            }
                            buttons += "</div>";

                            return buttons;
                        }
                    }
                ]
              });

        } else {

            var pastNeedsDate = new Date();
            var pastNeedsDateString = new Date(pastNeedsDate.getFullYear(), pastNeedsDate.getMonth()+1, pastNeedsDate.getDate()-180);
            pastNeedsDateString = pastNeedsDateString.getFullYear() + '-' + pastNeedsDateString.getMonth() + '-' + pastNeedsDateString.getDate();

            var url = '<?php echo API_HOST_URL; ?>' + '/carrier_needs?include=entities&columns=entities.name,id,entityID,qty,transportationMode,availableDate,expirationDate,originationCity,originationState,originationZip,originationLat,originationLng,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,needsDataPoints,status,contactEmails&filter[0]=expirationDate,ge,' + pastNeedsDateString + '&satisfy=all&order[]=entityID&order[]=expirationDate,desc&transform=1';
            var example_table = $('#datatable-table').DataTable({
                retrieve: true,
                processing: true,
                bSort: false,
                "pageLength": 50,
                ajax: {
                    url: url,
                    dataSrc: 'carrier_needs'
                },
                columns: [
                    { data: "entities[0].name" },
                    { data: "id", visible: false },
                    { data: "entityID", visible: false },
                    { data: "qty" },
                    { data: "transportationMode", visible: false },
                    { data: "availableDate", visible: false },
                    { data: "expirationDate", visible: false },
                    { data: "originationCity" },
                    { data: "originationState" },
                    { data: "originationZip", visible: false },
                    { data: "originationLat", visible: false },
                    { data: "originationLng", visible: false },
                    { data: "destinationCity" },
                    { data: "destinationState" },
                    { data: "destinationZip", visible: false },
                    { data: "destinationLat", visible: false },
                    { data: "destinationLng", visible: false },
                    { data: "needsDataPoints", visible: false },
                    { data: "contactEmails", visible: false },
                    {
                        data: null,
                        "bSortable": false,
                        "mRender": function (o) {
                            var buttons = '<div class="pull-right text-nowrap">';
                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-edit text\"></i> <span class=\"text\">Edit</span></button>';

                            if (o.status == "Available") {
                                      buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-remove text\"></i> <span class=\"text\">Available</span></button>";
                            } else {
                                      buttons += " &nbsp;<button class=\"btn btn-danger btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-exclamation-sign text\"></i> <span class=\"text\">Unavailable</span></button>";
                            }
                            buttons += '</div>';

                            return buttons;
                        }
                    }
                ]
              });
        }



          example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );

          //To Reload The Ajax
          //See DataTables.net for more information about the reload method
          example_table.ajax.reload();
          $("#entityID").prop('disabled', false);
          $("#btnUpload").html("Upload");
          $("#btnUpload").prop("disabled", false);

      }

      function loadModal() { // load form with selection data for building download file ONLY - do not show modal form

			var li = '';
			var checked = '';
			var dpli = '';
			var dpchecked = '';
			var emptyMode = '';
			var loadMode = '';
			var eitherMode = '';
			$("#id").val('');
			$("#qty").val('');
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

			transMode = '<select id="transportationMode" name="transportationMode" class="form-control chzn-select" required="required">' + '<option value="" selected=selected>*Select Mode...</option>';

			transMode += '<option value="Empty" ' + emptyMode + '>Empty</option>';
			transMode += '<option value="Load Out" ' + loadMode + '>Load Out</option>';
			transMode += '<option value="Either (Empty or Load Out)" ' + eitherMode + '>Either (Empty or Load Out)</option>';
			transMode += '</select>';
			$("#divTransportationMode").html(transMode);

			for (var i = 0; i < contacts.contacts.records.length; i++) {
				li += '<li id=\"' + contacts.contacts.records[i][0] + '\" class=\"list-group-item\" ' + checked + '>' + contacts.contacts.records[i][1] + ' ' + contacts.contacts.records[i][2] + '</li>\n';
			}
			$("#check-list-box").html(li);
			for (var i = 0; i < dataPoints.object_type_data_points.length; i++) {
				dpli += '<li>' + dataPoints.object_type_data_points[i].title +
				' <select class="form-control mb-sm" id="' + dataPoints.object_type_data_points[i].columnName + '" name="' + dataPoints.object_type_data_points[i].columnName + '">\n' +
				' <option value="" selected=selected>-Select From List-</option>\n';
				for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {
					dpli += '<option>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</option>\n';
				}
				dpli += '</select>\n' +
				'</li>\n';
			}
			$("#dp-check-list-box").html(dpli);
			formatListBox();
			formatListBoxDP();
			$("#entityID").prop('disabled', false);
			$("#exampleModalLabel").html('Add New Need');

      }

      function replaceDocument() {

            $("#btnUpload").html("<i class='fa fa-spinner fa-spin'></i> Uploading Now");
            $("#btnUpload").prop("disabled", true);

            var data,date;
            var passValidation = false;
            var type = "";
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; //January is 0!
            var yyyy = today.getFullYear();
            var hours = today.getHours();
            var min = today.getMinutes();
            var sec = today.getSeconds();
            var url = "";
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
            var date = today;

            url = '<?php echo HTTP_HOST."/carrierbulkupload" ?>';
            type = "POST";
            var formData = new FormData();
            formData.append('fileupload', $('#updatePolicyFile')[0].files[0]);
            formData.append('entityID', $("#entityID").val());
            $.ajax({
                url : url,
                type : 'POST',
                data : formData,
                processData: false,  // tell jQuery not to process the data
                contentType: false,  // tell jQuery not to set contentType
                success : function(data) {
/*
                    var alertString="";
                    var obj = $.parseJSON(data);
                    for (var x=1;x<Object.keys(obj).length;x++) {
                        alertString+="\n"+obj[x];
                    }
                    //alert("Success:"+ alertString);

                    //console.log(JSON.stringify(data));
                    //console.log('Carrier Needs updated');
*/
                    if (data == "success") {
                        $(myModal2).modal('hide');
                        loadTableAJAX();
                    } else {
                        //console.log("Error During Success: " + data);
                        Messenger().post({
                            message: "Error During Success: " + data,
                            type: 'error',
                            showCloseButton: true
                        });
                    }
                },
                error: function(data) {
                    console.log("Error During Error: "+ data);
                    Messenger().post({
                        message: "Error During Error: "+ data,
                        type: 'error',
                        showCloseButton: true
                    });
                }
            });
	  }

	  function downloadTemplateClick() {

            loadModal(); // Just load the modal so we can parse through and get the data to build the download file
            var labels="";
            var secondRow="";
            $("#formNeed label").each(function(e){
                if (e==0) {
                    labels+=$(this).html();
                } else {
                    labels+=","+$(this).html();
                    if ($(this).html() == "Need Date" || $(this).html() == "Expiration Date") {
                        labels+=" (YYYY-MM-DD)";
                    } else if ($(this).html() == "Transportation Mode") {
                        labels+=" (Empty/Load Out/Either (Empty or Load Out))";
                    }
                    secondRow+=",";
                }
            });
            // Build the needsDataPoints
            var obj = $("#dp-check-list-box li select");
            for (var i = 0; i < obj.length; i++) {
                labels+=",Trailer "+obj[i].id +" "+obj[i].innerText.replace(/\n -Select From List-\n/g, '(').replace(/\n/g, '/').slice(0, -1)+")";
                secondRow+=",";
            }
            // Build the contacts
            var obj = $("#check-list-box li");
            if (obj.length > 0) {
                for (var i = 0; i < 1; i++) { // Only grab the first contact if there is one
                    //labels+=",Contact ("+obj[i].id+") "+obj[i].innerText;
                    labels+=",Contact";
                    //secondRow+=",no";
                    secondRow+=",{\"" + obj[i].id + "\": \"" + obj[i].innerText + "\"}"
                }
                //labels+="\r\n"+secondRow;
                labels+="\n"+secondRow;
            }
            var element = document.createElement('a');
            element.setAttribute('href', "data:application/octet-stream;charset=utf-8;base64,"+btoa(labels.replace(/\n/g, '\r\n')));
            element.setAttribute('download', "carrier_needs_bulk_import_template.csv");
            //$("#downloadTemplate").attr("href","data:application/octet-stream;charset=utf-8;base64,"+btoa(labels.replace(/\n/g, '\r\n')));
            //$("#downloadTemplate").attr("download","carrier_needs_bulk_import_template.csv");
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);

      }


      function recordEnableDisable(status) {
          var passValidation = false;

          if (status == "Make Unavailable") {
              var newStatus = 'Unavailable';
              var myDialog = "#myDisableDialog";
          } else if (status == "Make Available") {
              var myDialog = "#myEnableDialog";
              var newStatus = 'Available';
          } else {
              var myDialog = "#myEnableDialog";
              var newStatus = 'Available';
          }

          var data = {status: newStatus};
          var url = '<?php echo API_HOST_URL . "/carrier_needs" ?>/' + $("#id").val();
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
                  Messenger().post({
                        message: "Changing Status of Need Failed!",
                        type: 'error',
                        showCloseButton: true
                    });
                }
             },
             error: function() {
                //alert("There Was An Error Changing Need Status!");
                Messenger().post({
                    message: "There Was An Error Changing Need Status!",
                    type: 'error',
                    showCloseButton: true
                });
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
                  $widget.find('.state-icon').removeClass().addClass('state-icon ' + settings[$widget.data('state')].icon);
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
                                       Messenger().post({
                                            message: "There Was An Error Adding Need Contacts!",
                                            type: 'error',
                                            showCloseButton: true
                                        });
                                    }
                                 });
                            } else {
                                  //alert("There Was An Issue Clearing Need Contacts!");
                                  Messenger().post({
                                        message: "There Was An Issue Clearing Need Contacts!",
                                        type: 'error',
                                        showCloseButton: true
                                    });
                            }
                         },
                         error: function() {
                              //alert("There Was An Error Deleting Need Records!");
                              Messenger().post({
                                    message: "There Was An Error Deleting Need Records!",
                                    type: 'error',
                                    showCloseButton: true
                                });
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
                Messenger().post({
                    message: "There Was An Error Retrieving Location Contacts!",
                    type: 'error',
                    showCloseButton: true
                });
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
                  //$("#errorAlertTitle").html("Error");
                  //$("#errorAlertBody").html(data);
                  //$("#errorAlert").modal('show');
             },
             error: function() {
                //alert("There Was An Error Retrieving Location Contacts!");
                Messenger().post({
                    message: "There Was An Error Retrieving Location Contacts!",
                    type: 'error',
                    showCloseButton: true
                });
             }
          });
      }

      function addCarrierContact() {

          $("#load2").html("<i class='fa fa-spinner fa-spin'></i> Adding Contact");
          $("#load2").prop("disabled", true);

        if ( $('#formContact').parsley().validate() ) {

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

                var url = '<?php echo API_HOST_URL . "/contacts" ?>';
                type = "POST";

                var phone = $("#primaryPhone").val().replace(/(\d{3})\-?(\d{3})\-?(\d{4})/, '$1-$2-$3');
                var ext = $("#primaryPhoneExt").val();
                if (ext != "") {
                        phone = phone + " x" + ext;
                }

                var date = today;
                var data = {
                        entityID: $("#carrierID").val(),
                        contactTypeID: $("#contactTypeID").val(),
                        firstName: $("#firstName").val(),
                        lastName: $("#lastName").val(),
                        title: $("#title").val(),
                        emailAddress: $("#emailAddress").val(),
                        primaryPhone: phone,
                        secondaryPhone: $("#secondaryPhone").val(),
                        fax: $("#fax").val(),
                        contactRating: $("#contactRating").val(),
                        createdAt: date
                  };

                $.ajax({
                   url: url,
                   type: type,
                   data: JSON.stringify(data),
                   contentType: "application/json",
                   async: false,
                   success: function(data){
                      if (data > 0) {
                        loadTableAJAX();
                        reloadContacts();
                        $("#contactTypeID").val('');
                        $("#firstName").val('');
                        $("#lastName").val('');
                        $("#title").val('');
                        $("#emailAddress").val('');
                        $("#primaryPhone").val('');
                        $("#primaryPhoneExt").val('');
                        $("#secondaryPhone").val('');
                        $("#fax").val('');
                        $("#contactRating").val('');
                        $("#carrierID").val('');
                        $("#addCarrierContact").modal('hide');

                        $("#load2").html("Add Contact");
                        $("#load2").prop("disabled", false);
                        passValidation = true;
                      } else {
                          $("#load2").html("Add Contact");
                          $("#load2").prop("disabled", false);
                        //alert("Adding Contact Failed!");
                          Messenger().post({
                                message: "Adding Contact Failed!",
                                type: 'error',
                                showCloseButton: true
                            });
                      }
                   },
                   error: function() {
                       $("#load2").html("Add Contact");
                       $("#load2").prop("disabled", false);
                      //alert("There Was An Error Adding Contact!");
                       Messenger().post({
                            message: "There Was An Error Adding Contact!",
                            type: 'error',
                            showCloseButton: true
                        });
                   }
                });

                $("#load2").html("Add Contact");
                $("#load2").prop("disabled", false);
                return passValidation;

          } else {

              $("#load2").html("Add Contact");
              $("#load2").prop("disabled", false);
                return false;
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

    #destination-list{float:left;list-style:none;margin-top:-3px;padding:0;width:250px;position: relative;}

    #destination-list li{padding: 10px; background: #f0f0f0; border-bottom: #bbb9b9 1px solid;}

    #destination-list li:hover{background:#ece3d2;cursor: pointer;}

    .btn-contact{
        display: inline-block;
        position: relative;
        left:20%;
        margin-bottom: 10px;
    }

 </style>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Manage My Needs</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">My Needs</span></h4>
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
         <div class="pull-right text-nowrap">
            <button type="button" id="addNeed" class="btn btn-primary" data-target="#myModal">Add New Need</button>
            <button type="button" id="uploadTemplate" class="btn btn-primary" data-target="#myModal2" >Upload Bulk Template</button>
         </div>
         <button type="button" id="downloadTemplateButton" class="btn btn-primary">Download Bulk Template</button>
         <a id="downloadTemplate"></a>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover" style="width: 100%;">
                 <thead>
                 <tr>
                     <th>Company</th>
                     <th>ID</th>
                     <th>Entity ID</th>
                     <th>Quantity</th>
                     <th>Transportation Mode</th>
                     <th>Available Date</th>
                     <th>Expiration Date</th>
                     <th class="hidden-sm-down">Orig. City</th>
                     <th class="hidden-sm-down">Orig. State</th>
                     <th class="hidden-sm-down">Orig. Zip</th>
                     <th class="hidden-sm-down">Orig. Lat.</th>
                     <th class="hidden-sm-down">Orig. Long.</th>
                     <th class="hidden-sm-down">Dest. City</th>
                     <th class="hidden-sm-down">Dest. State</th>
                     <th class="hidden-sm-down">Dest. Zip</th>
                     <th class="hidden-sm-down">Dest. Lat.</th>
                     <th class="hidden-sm-down">Dest. Long.</th>
                     <th class="hidden-sm-down">Data Points</th>
                     <th class="hidden-sm-down">Contact</th>
                     <th>&nbsp;</th>
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
 <div class="modal fade" id="myModal" z-index="1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel" style="position:absolute;"><strong>Need</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formNeed" class="register-form mt-lg">
                 <input type="hidden" id="id" name="id" value="" />
                 <div class="row">
                     <div class="col-sm-2">
                         <label for="qty"># Looking For</label>
                         <div class="form-group">
                           <input type="text" id="qty" name="qty" class="form-control mb-sm" placeholder="# Looking For"
                           required="required" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="availableDate">Need Date</label>
                         <div class="form-group">
                           <div id="sandbox-container" class="input-group date  datepicker">
                              <input type="text" id="availableDate" name="availableDate" class="form-control" placeholder="Need Date" required="required"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                           </div>
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="expirationDate">Expiration Date</label>
                         <div class="form-group">
                           <div id="sandbox-container" class="input-group date  datepicker">
                              <input type="text" id="expirationDate" name="expirationDate" class="form-control" placeholder="Expiration Date"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                           </div>
                         </div>
                     </div>
                     <div class="col-sm-4">
                         <div class="form-group">
             <?php if ($_SESSION['entityid'] > 0) { ?>
                            <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
             <?php } else { ?>
                             <label for="entityID">Carrier:</label>
                             <select id="entityID" name="entityID" data-placeholder="Carrier" class="form-control chzn-select" required="required">
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
                 <div class="row">
                     <div class="col-sm-3">
                         <label for="originationCity">Origination City</label>
                         <div class="form-group">
                           <input type="hidden" id="originationLocationID" name="originationLocationID" />
                           <input type="text" id="originationCity" name="originationCity" class="form-control mb-sm" placeholder="Origin City"
                           required="required" />
                         </div>
                         <div id="suggesstion-box-origin" class="frmSearch"></div>
                     </div>
                     <div class="col-sm-4">
                         <label for="originationAddress1">Origination Address</label>
                         <div class="form-group">
                           <input type="text" id="originationAddress1" name="originationAddress1" class="form-control mb-sm" placeholder="Origin Address" />
                         </div>
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
                 <div class="row">
                   <div class="col-sm-3">
                       <label for="DestinationCity">Destination City</label>
                       <div class="form-group">
                         <input type="text" id="destinationCity" name="destinationCity" class="form-control mb-sm" placeholder="Dest. City"
                         required="required" />
                       </div>
                       <div id="suggesstion-box-dest" class="frmSearch"></div>
                   </div>
                   <div class="col-sm-4">
                       <label for="destinationAddress1">Destination Address</label>
                       <div class="form-group">
                         <input type="text" id="destinationAddress1" name="destinationAddress1" class="form-control mb-sm" placeholder="Destination Address" />
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
                 <div class="row">
                    <div class="col-sm-4">
                         <label for="divTransportationMode">Transportation Mode</label>
                         <div id="divTransportationMode" class="form-group">

                         </div>
                    </div>
                    <div class="col-sm-8">
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
                             <h5 class="text-center" id="carrierContactsTitle" style="display:inline-block"><strong>Company Contacts</strong></h5>
                            <button type="button" class="btn btn-primary btn-contact" onclick="openAddContact()">Add Contact</button>
                             <div class="well" style="max-height: 200px;overflow: auto;">
                                 <ul id="check-list-box" class="list-group checked-list-box">

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

<!-- Modal -->
<div class="modal fade" id="myModal-insurance" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel"><strong>Insurance</strong></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formInsurance" class="register-form mt-lg" action="<?php echo HTTP_HOST."/uploaddocument" ?>" method="POST" enctype="multipart/form-data">
					<input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
					<input type="hidden" id="id" name="id" value="" />
					<div class="row">
						<div class="col-sm-6">
							<label for="name">Insurer</label>
							<div class="form-group">
								<input type="text" id="name" name="name" class="form-control mb-sm" placeholder="*Name" required="required" />
							</div>
						</div>
						<div class="col-sm-6">
							<label for="contactName">Contact Name</label>
							<div class="form-group">
								<input type="text" id="contactName" name="contactName" class="form-control mb-sm" placeholder="*Contact Name" required="required" />
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-6">
							<label for="contactEmail">Contact Email</label>
							<div class="form-group">
								<input type="email" id="contactEmail" name="contactEmail" class="form-control mb-sm" placeholder="*Contact Email" required="required" />
							</div>
						</div>
						<div class="col-sm-6">
							<label for="contactPhone">Contact Phone</label>
							<div class="form-group">
								<input type="text" id="contactPhone" name="contactPhone" class="form-control mb-sm" placeholder="Contact Phone" required="required" />
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-6">
							<label for="policyNumber">Policy Number</label>
							<div class="form-group">
								<input type="text" id="policyNumber" name="policyNumber" class="form-control mb-sm" placeholder="*Policy Number" required="required" />
							</div>
						</div>
						<div class="col-sm-6">
							<label for="policyExpirationDate">Expiration Date</label>
							<div class="form-group">
								<!--input type="text" id="policyExpirationDate" name="policyExpirationDate" class="form-control mb-sm" placeholder="Policy Expiration Date (YYYY-MM-DD)" required="required" /-->
								<div id="sandbox-container" class="input-group date datepicker">
									<input type="text" id="policyExpirationDate" name="policyExpirationDate" class="form-control" placeholder="Policy Expiration Date"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<label for="fileupload">Policy File Upload</label>
							<div class="form-group">
								<input type="file" id="fileupload" name="fileupload" class="form-control mb-sm" placeholder="*Policy Number" required="required" data-filesize="20000000"
								data-filetype="image/bmp,image/gif,image/jpeg,application/zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/rtf"
								onchange="validateFile(this);" />
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="return verifyAndPost();">Save Changes</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="enableDialogLabel">Upload Bulk Carrier Needs</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
                <div class="row">
                <form>
                    <input type="hidden" id="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
                    <input type="hidden" id="replaceID" name="replaceID" value="" />
                    <input type="hidden" id="docToView" value="" />
                    <div id="divUploadPolicyFile" class="col-md-7">
                        <label for="updatePolicyFile"><strong>Select Carrier Needs File To Upload</strong></label>
                        <div class="form-group">
                            <input type="file" id="updatePolicyFile" name="updatePolicyFile" class="form-control mb-sm" placeholder="Update Policy" data-filesize="20000000"
                            data-filetype="text/csv"
                            onchange="validateFile(this);" />
                        </div>
                    </div>
                    <!--
                    <div class="col-md-5 pull-right">
                        <label for="btnView">&nbsp;</label>
                        <div class="form-group">
                            <button type="button" class="btn btn-primary" id="btnReplace">Upload/View Policy</button>
                            <button type="button" class="btn btn-primary" id="btnView">View Policy</button>
                        </div>
                    </div>
                    -->
                </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnUpload">Upload</button>
            </div>
		</div>
	</div>
</div>
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
                            <h5>Do you wish to make this need UNAVAILABLE?</h5>
                          </div>
                      </div>
                  </div>
                 </form>
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Make Unavailable');">Make Unavailable</button>
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
                             <h5>Do you wish to make this need AVAILABLE?</h5>
                           </div>
                       </div>
                   </div>
                  </form>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Make Available');">Make Available</button>
         </div>
       </div>
     </div>
   </div>



 <!-- Modal -->
 <div class="modal fade" id="addCarrierContact" z-index="1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel"><strong>Contact</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formContact" class="register-form mt-lg">
                 <div class="row">
                     <div class="col-sm-6">
                         <label for="firstName">First Name</label>
                         <div class="form-group">
                           <input type="text" id="firstName" name="firstName" class="form-control mb-sm" placeholder="*First Name" required="required" />
                         </div>
                     </div>
                     <div class="col-sm-6">
                         <label for="lastName">Last Name</label>
                         <div class="form-group">
                           <input type="text" id="lastName" name="lastName" class="form-control mb-sm" placeholder="Last Name" required="required" />
                         </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-sm-4">
                         <label for="contactTypeID">Contact Type</label>
                         <div class="form-group">
                           <select id="contactTypeID" name="contactTypeID" data-placeholder="*Contact Type" class="form-control chzn-select" data-ui-jq="select2" required="required">
                             <option value="">*Select Type...</option>
            <?php
                             foreach($contactTypes->contact_types->records as $value) {
                                 $selected = ($value[0] == $contactTypeID) ? 'selected=selected':'';
                                 echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                             }
            ?>
                           </select>
                         </div>
                     </div>
                     <div class="col-sm-4">
                       <div class="form-group">
             <?php if ($_SESSION['entityid'] > 0) { ?>
                            <input type="hidden" id="carrierID" name="carrierID" value="<?php echo $_SESSION['entityid']; ?>" />
             <?php } else { ?>
                             <label for="entityID">Carrier:</label>
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
                      <div class="col-sm-4">
                          <div class="form-group">
                              &nbsp;
                          </div>
                      </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-6">
                       <label for="title">Title</label>
                       <div class="form-group">
                         <input type="text" id="title" name="title" class="form-control mb-sm" placeholder="*Title" required="required" />
                       </div>
                     </div>
                     <div class="col-sm-6">
                         <label for="emailAddress">Email Address</label>
                         <div class="form-group">
                           <input type="email" data-parsley-type="email" id="emailAddress" name="emailAddress" class="form-control mb-sm" placeholder="*Email Address" required="required"/>
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-6">
                         <label for="primaryPhone">Primary Phone</label>
                         <div class="form-group">
                           <div class="col-sm-9" style="padding-left: 0; padding-right: 0">
 	                          <input type="text" id="primaryPhone" name="primaryPhone" class="form-control" placeholder="*Primary Phone" required="required" />
                           </div>
                           <div class="col-sm-3" style="padding-right: 0;">
                              <input type="text" maxlength="15" data-parsley-maxlength="15" id="primaryPhoneExt" name="primaryPhoneExt" class="form-control" placeholder="Ext" />
                           </div>
                         </div>
                     </div>
                     <div class="col-sm-6">
                         <label for="secondaryPhone">Secondary Phone</label>
                         <div class="form-group">
                           <input type="text" id="secondaryPhone" name="secondaryPhone" class="form-control" placeholder="Secondary Phone" />
                         </div>
                     </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-6">
                        <label for="fax">Fax</label>
                        <div class="form-group">
                          <input type="text" id="fax" name="fax" class="form-control mb-sm" placeholder="Fax" />
                        </div>
                    </div>
                     <div class="col-sm-6">
                         <label for="contactRating">Rating</label>
                         <div class="form-group">
                           <select id="contactRating" name="contactRating" data-placeholder="Rating" class="form-control chzn-select" data-ui-jq="select2">
                              <option value="">*Rating...</option>
             <?php
                              for($s = 1; $s < 6; $s++) {
                                  $selected = ($s == $contactRating) ? 'selected=selected':'';
                                  echo "<option value=" .$s . " " . $selected . ">" . $s . " Star(s)</option>\n";
                              }
             ?>
                           </select>
                         </div>
                     </div>

                 </div>
                </form>
       </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button id="load2" type="button" class="btn btn-primary" onclick="return addCarrierContact();">Add Contact</button>
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
    getCarrierContactTitle(entityID);
    var table = $("#datatable-table").DataTable();
    var tableContact = $("#datatable-table-contact").DataTable();
    var tableDataPoints = $("#datatable-table-datapoints").DataTable();

	function validateFile(file) {
		var ext = $(file).val().split(".");
		ext = ext[ext.length-1].toLowerCase();
		var arrayExtensions = ["csv"];
		if (arrayExtensions.lastIndexOf(ext) == -1) {
			//alert("File must be one of the following valid types; csv.");
			$("#errorAlertTitle").html("Error");
            $("#errorAlertBody").html("File must be one of the following valid types; csv.");
            $("#errorAlert").modal('show');
			$(file).val("");
			$(file).focus();
			return false;
		} else {
			return true;
		}
	}

    $('.datepicker').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });


	$("#uploadTemplate").click(function(){
		$("#myModal2").modal('show');
	});

    $("#addNeed").click(function(){
      var li = '';
      var checked = '';
      var dpli = '';
      var dpchecked = '';
      var emptyMode = '';
      var loadMode = '';
      var eitherMode = '';
      $("#id").val('');
      $("#qty").val('');
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
      //alert(JSON.stringify(contacts));

    getCarrierContactTitle(entityID);

    if(entityID == 0){
        $("#entityID").val('');
    }
      transMode = '<select id="transportationMode" name="transportationMode" class="form-control chzn-select" required="required">' +
                             '<option value="" selected=selected>*Select Mode...</option>';

      transMode += '<option value="Empty" ' + emptyMode + '>Empty</option>';
      transMode += '<option value="Load Out" ' + loadMode + '>Load Out</option>';
      transMode += '<option value="Either (Empty or Load Out)" ' + eitherMode + '>Either (Empty or Load Out)</option>';
      transMode += '</select>';
      $("#divTransportationMode").html(transMode);

      for (var i = 0; i < contacts.contacts.records.length; i++) {
          li += '<li id=\"' + contacts.contacts.records[i][0] + '\" class=\"list-group-item\" ' + checked + '>' + contacts.contacts.records[i][1] + ' ' + contacts.contacts.records[i][2] + '</li>\n';
      }
      $("#check-list-box").html(li);
      for (var i = 0; i < dataPoints.object_type_data_points.length; i++) {
          dpli += '<li>' + dataPoints.object_type_data_points[i].title +
                  ' <select class="form-control mb-sm" id="' + dataPoints.object_type_data_points[i].columnName + '" name="' + dataPoints.object_type_data_points[i].columnName + '">\n' +
                  ' <option value="" selected=selected>-Select From List-</option>\n';
          for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {
              dpli += '<option>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</option>\n';
          }
          dpli += '</select>\n' +
                  '</li>\n';
      }
      $("#dp-check-list-box").html(dpli);
      formatListBox();
      formatListBoxDP();
      $("#entityID").prop('disabled', false);
      $("#exampleModalLabel").html('Add New Need');
      $("#suggesstion-box").hide();
      $("#myModal").modal('show');

      $('#myModal').on('shown.bs.modal', function () {
          $('#qty').focus();
      });

  	});

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();

        if (this.textContent.indexOf("Edit") > -1) {
            var li = '';
            var checked = '';
            var dpli = '';
            var dpchecked = '';
            var transMode = '';
            var emptyMode = '';
            var loadMode = '';
            var eitherMode = '';
            $("#id").val(data["id"]);
            $("#entityID").val(data["entityID"]);
            $("#qty").val(data["qty"]);
            $("#availableDate").val(data["availableDate"]);
            $("#expirationDate").val(data["expirationDate"]);
            $("#originationAddress1").val(data["originationAddress1"]);
            $("#originationCity").val(data["originationCity"]);
            $("#originationState").val(data["originationState"]);
            $("#originationZip").val(data["originationZip"]);
            $("#destinationAddress1").val(data["destinationAddress1"]);
            $("#destinationCity").val(data["destinationCity"]);
            $("#destinationState").val(data["destinationState"]);
            $("#destinationZip").val(data["destinationZip"]);
            var ndp = data["needsDataPoints"];
            var con = data["contactEmails"];

            transMode = '<select id="transportationMode" name="transportationMode" class="form-control chzn-select" required="required">' +
                             '<option value="">*Select Mode...</option>';
            switch (data["transportationMode"]) {
                case "Empty": emptyMode = 'selected=selected';
                break;
                case "Load Out": loadMode = 'selected=selected';
                break;
                case "Either (Empty or Load Out)": eitherMode = 'selected=selected';
                break;
            }

            transMode += '<option value="Empty" ' + emptyMode + '>Empty</option>';
            transMode += '<option value="Load Out" ' + loadMode + '>Load Out</option>';
            transMode += '<option value="Either (Empty or Load Out)" ' + eitherMode + '>Either (Empty or Load Out)</option>';
            transMode += '</select>';
            $("#divTransportationMode").html(transMode);

            // Get configuration_settings for data manipulation
            $.ajax({
               url: '<?php echo API_HOST_URL . "/entities"; ?>/' + $("#entityID").val(),
               type: 'GET',
               contentType: "application/json",
               async: false,
               success: function(response){
                 var entityTypeID = response.entityTypeID;
                 var cs = response.configuration_settings;
                 if (cs) {
                    $.each(cs, function(idx, obj) {
                      $.each(obj, function(key, val) {
                        switch (key) {
                            case 'need_expire_days':
                                   need_expire_days = val; // Get the value from the JSON data in the record to use to set the selected option in the dropdown
                                   break;
                            default:
                        }
                      })
                    });
                 }
               },
               error: function() {
                  //alert('Failed Getting Configuration Settings! - Notify NEC of this failure.');
                  Messenger().post({
                        message: 'Failed Getting Configuration Settings! - Notify NEC of this failure.',
                        type: 'error',
                        showCloseButton: true
                    });
               }
            });

            var params = {id: $("#entityID").val()};
            getCarrierContactTitle($("#entityID").val());
            $.ajax({
               url: '<?php echo HTTP_HOST."/getcontactsbycarrier" ?>',
               type: 'POST',
               data: JSON.stringify(params),
               contentType: "application/json",
               async: false,
               success: function(response){
                 response = JSON.parse(response);
                 var li = '';
                 for (var i = 0; i < response.contacts.length; i++) {
                     checked = '';
                     for (var l = 0; l < con.length; l++) {
                         $.each(con, function(idx, obj) {
                           $.each(obj, function(key, val) {
                             if (response.contacts[i].id == key) {
                                 checked = 'data-checked="true"';
                             }
                           })
                         });
                     }
                     li += '<li id=\"' + response.contacts[i].id + '\" class=\"list-group-item\" ' + checked + '>' + response.contacts[i].firstName + ' ' + response.contacts[i].lastName + '</li>\n';
                 }
                 $("#check-list-box").html(li);
                 formatListBox();
               },
               error: function() {
                  //alert('Failed Getting Contacts! - Notify NEC of this failure.');
                  Messenger().post({
                        message: 'Failed Getting Contacts! - Notify NEC of this failure.',
                        type: 'error',
                        showCloseButton: true
                    });
               }
            });

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
                        ' <select class="form-control mb-sm" id="' + dataPoints.object_type_data_points[i].columnName + '" name="' + dataPoints.object_type_data_points[i].columnName + '">\n' +
                        ' <option value="">-Select From List-</option>\n';
                for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {
                    if (dataPoints.object_type_data_points[i].object_type_data_point_values[v].value === value) {
                        selected = ' selected=selected ';
                        console.log(dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + ' and ' + value);
                    } else {
                        selected = '';
                    }

                    dpli += '<option' + selected + '>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</option>\n';

                }

                dpli += '</select>\n' +
                        '</li>\n';
            }
            $("#dp-check-list-box").html(dpli);
            formatListBox();
            formatListBoxDP();
            $("#entityID").prop('disabled', true);
            $("#exampleModalLabel").html('Edit Need');
            $("#suggesstion-box").hide();
            $("#myModal").modal('show');
        } else {
            $("#id").val(data["id"]);
            if (this.textContent.indexOf("Unavailable") > -1) {
              $("#enableDialogLabel").html('Make <strong>Need</strong> Available?');
              $("#myEnableDialog").modal('show');
            } else {
              if (this.textContent.indexOf("Available") > -1) {
                $("#disableDialogLabel").html('Make <strong>Need</strong> Unavailable?');
                $("#myDisableDialog").modal('show');
              }
            }
        }

    });

    $('#entityID').off('change').on( 'change', function () {
        var params = {id: $("#entityID").val()};

    getCarrierContactTitle($("#entityID").val());
        //alert(JSON.stringify(params));
        $.ajax({
           url: '<?php echo HTTP_HOST."/getcontactsbycarrier" ?>',
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
              //alert('Failed Getting Contacts! - Notify NEC of this failure.');
              Messenger().post({
                    message: 'Failed Getting Contacts! - Notify NEC of this failure.',
                    type: 'error',
                    showCloseButton: true
                });
           }
        });

        // Get configuration_settings for data manipulation
        $.ajax({
           url: '<?php echo API_HOST_URL . "/entities"; ?>/' + $("#entityID").val(),
           type: 'GET',
           contentType: "application/json",
           async: false,
           success: function(response){
             var entityTypeID = response.entityTypeID;
             var cs = response.configuration_settings;
             if (cs) {
                $.each(cs, function(idx, obj) {
                  $.each(obj, function(key, val) {
                    switch (key) {
                        case 'need_expire_days':
                               need_expire_days = val; // Get the value from the JSON data in the record to use to set the selected option in the dropdown
                               break;
                        default:
                    }
                  })
                });
             }
           },
           error: function() {
              //alert('Failed Getting Configuration Settings! - Notify NEC of this failure.');
              Messenger().post({
                    message: 'Failed Getting Configuration Settings! - Notify NEC of this failure.',
                    type: 'error',
                    showCloseButton: true
                });
           }
        });
    });

/* Taken out per FS257
    $("#originationCity").keyup(function(){
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
              $("#suggesstion-box-origin").html(li);
        			$("#suggesstion-box-origin").show();
        			$("#originationCity").css("background","#FFF");
        		}
    		});
  	});
*/

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
             $("#originationLocationID").val(response.id);
             $("#originationAddress1").val(response.address1);
             $("#originationCity").val(response.city);
             $("#originationState").val(response.state);
             $("#originationZip").val(response.zip);
             var li = setContactsOnLocationSelected();
             $("#check-list-box").html(li);
             formatListBox();
             $("#suggesstion-box-origin").hide();
           },
           error: function() {
                //alert('Error Selecting Origination City!');
                Messenger().post({
                    message: 'Error Selecting Origination City!',
                    type: 'error',
                    showCloseButton: true
                });
           }
        });
    }

/* Taken out per FS257
    $("#destinationCity").keyup(function(){
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
              $("#suggesstion-box-dest").html(li);
        			$("#suggesstion-box-dest").show();
        			$("#destinationCity").css("background","#FFF");
        		}
    		});
  	});
*/

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
             $("#suggesstion-box-dest").hide();
           },
           error: function() {
                //alert('Error Selecting Destination City!');
                Messenger().post({
                    message: 'Error Selecting Destination City!',
                    type: 'error',
                    showCloseButton: true
                });
           }
        });
    }

    var blnFromAddForm = false;

    function openAddContact(){

        blnFromAddForm = true;

        $("#myModal").modal('hide');
    }

    $("#addCarrierContact").on("hidden.bs.modal", function () {

        $("#addNeed").trigger("click");
    });

    $("#myModal").on("hidden.bs.modal", function () {
        $("#entityID").prop('disabled', false);

        if(blnFromAddForm){

            blnFromAddForm = false;
            $("#addCarrierContact").modal('show');
        }

    });

    function setContactsOnLocationSelected() {
        var location_id = $("#originationLocationID").val();
        var li = '';

        //var con = contacts.contacts;
        var loc_con = locations_contacts.locations_contacts;
        for (var i = 0; i < contacts.contacts.records.length; i++) {
            checked = '';
            $.each(loc_con.records, function(idx, obj) {
                //alert(contacts.contacts.records[i][0] + ' and ' + location_id + ' and ' + obj);
                if (location_id == obj[0] && contacts.contacts.records[i][0] == obj[1]) {
                    checked = 'data-checked="true"';
                }
            });
            //alert(checked);
            li += '<li id=\"' + contacts.contacts.records[i][0] + '\" class=\"list-group-item\" ' + checked + '>' + contacts.contacts.records[i][1] + ' ' + contacts.contacts.records[i][2] + '</li>\n';
        }
        return li;
    }

	$("#btnUpload").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates
	    replaceDocument();
	    return false;
	});

	document.getElementById('downloadTemplateButton').addEventListener('click', downloadTemplateClick);

 </script>
