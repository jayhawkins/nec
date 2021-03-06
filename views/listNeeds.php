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


// No longer needed. We don't load via PHP anymore. All handled in JS function.
//$getlocations = json_decode(file_get_contents(API_HOST_URL . '/locations?include=location_types&columns=locations.name,location_types.name,locations.address1,locations.address2,locations.city,locations.state,locations.zip,locations.status&filter=entityID,eq,' . $_SESSION['entityid'] . '&order=locationTypeID'),true);
//$locations = php_crud_api_transform($getlocations);

 ?>

 <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

 <script type="text/javascript" src="https://maps.google.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API; ?>"></script>

 <script>

     var contacts = <?php echo json_encode($contacts); ?>;
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

      function post() {

          if ( $('#formNeed').parsley().validate() ) {

                var result = true;

                var params = {
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
                                    $("#errorAlertTitle").html("Error");
                                    $("#errorAlertBody").html("1: " + response);
                                    $("#errorAlert").modal('show');
                                    result = false;
                                    //alert('Preparation Failed!');
                                }
                             },
                             error: function(response) {
                                //alert("2: " + response);
                                $("#errorAlertTitle").html("Error");
                                $("#errorAlertBody").html("2: " + response);
                                $("#errorAlert").modal('show');
                                result = false;
                                //alert('Failed Searching for Destination Location! - Notify NEC of this failure.');
                             }
                          });
                      } else {
                            //alert("3: " + response);
                            $("#errorAlertTitle").html("Error");
                            $("#errorAlertBody").html("3: " + response);
                            $("#errorAlert").modal('show');
                          result = false;
                          //alert('Preparation Failed!');
                      }
                   },
                   error: function(response) {
                        // alert("4: " + JSON.stringify(response));
                        $("#errorAlertTitle").html("Error");
                        $("#errorAlertBody").html("4: " + response);
                        $("#errorAlert").modal('show');
                      result = false;
                      //alert('Failed Searching for Origination Location! - Notify NEC of this failure.');
                   }
                });

                if (result) {
                  verifyAndPost();
                } else {
                  return false;
                }

          } else {

              return false;

          }

      }

      function verifyAndPost() {

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

                              if (type == "PUT") {
                                  var date = today;
                                  var data = {qty: $("#qty").val(), originationAddress1: $("#originationAddress1").val(), originationCity: $("#originationCity").val(), originationState: $("#originationState").val(), originationZip: $("#originationZip").val(), destinationAddress1: $("#destinationAddress1").val(), destinationCity: $("#destinationCity").val(), destinationState: $("#destinationState").val(), destinationZip: $("#destinationZip").val(), originationLat: originationlat, originationLng: originationlng, destinationLat: destinationlat, destinationLng: destinationlng, needsDataPoints: needsdatapoints, contactEmails: $contacts, availableDate: $("#availableDate").val(), expirationDate: $("#expirationDate").val(), updatedAt: date};
                              } else {
                                  var date = today;
                                  var data = {entityID: $("#entityID").val(), qty: $("#qty").val(), originationAddress1: $("#originationAddress1").val(), originationCity: $("#originationCity").val(), originationState: $("#originationState").val(), originationZip: $("#originationZip").val(), destinationAddress1: $("#destinationAddress1").val(), destinationCity: $("#destinationCity").val(), destinationState: $("#destinationState").val(), destinationZip: $("#destinationZip").val(), originationLat: originationlat, originationLng: originationlng, destinationLat: destinationlat, destinationLng: destinationlng, needsDataPoints: needsdatapoints, contactEmails: $contacts, availableDate: $("#availableDate").val(), expirationDate: $("#expirationDate").val(), createdAt: date, updatedAt: date};
                              }

                              $.ajax({
                                 url: url,
                                 type: type,
                                 data: JSON.stringify(data),
                                 contentType: "application/json",
                                 async: false,
                                 success: function(data){
                                    if (data > 0) {
                                      if (type == 'POST') {
                                        var params = {id: data};
                                        $.ajax({
                                           url: '<?php echo HTTP_HOST."/carrierneedsnotification" ?>',
                                           type: 'POST',
                                           data: JSON.stringify(params),
                                           contentType: "application/json",
                                           async: false,
                                           success: function(notification){
                                              //alert(notification);
                                                $("#errorAlertTitle").html("Success");
                                                $("#errorAlertBody").html(notification);
                                                $("#errorAlert").modal('show');
                                           },
                                           error: function() {
                                                //alert('Failed Sending Notifications! - Notify NEC of this failure.');
                                                $("#errorAlertTitle").html("Failed Sending Notifications!");
                                                $("#errorAlertBody").html("Notify NEC of this failure.");
                                                $("#errorAlert").modal('show');
                                           }
                                        });
                                      }
                                      $("#myModal").modal('hide');
                                      loadTableAJAX();
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
                                      passValidation = true;
                                    } else {
                                        //alert("Adding Need Failed!");
                                        $("#errorAlertTitle").html("Error");
                                        $("#errorAlertBody").html("Adding Need Failed");
                                        $("#errorAlert").modal('show');
                                    }
                                 },
                                 error: function() {
                                    //alert("There Was An Error Adding Location!");
                                    $("#errorAlertTitle").html("Error");
                                    $("#errorAlertBody").html("There Was An Error Adding Location!");
                                    $("#errorAlert").modal('show');
                                 }
                              });

                          } else {
                                //alert("ERROR Geo-Coding Address!");
                                $("#errorAlertTitle").html("Error");
                                $("#errorAlertBody").html("ERROR Geo-Coding Address!");
                                $("#errorAlert").modal('show');
                          }
                      });
                  } else {
                        //alert("ERROR Geo-Coding Address!");
                        $("#errorAlertTitle").html("Error");
                        $("#errorAlertBody").html("ERROR Geo-Coding Address!");
                        $("#errorAlert").modal('show');
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
                var url = '<?php echo API_HOST_URL; ?>' + '/carrier_needs?include=entities&columns=entities.name,id,entityID,qty,availableDate,expirationDate,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,needsDataPoints,status,contactEmails&filter[]=expirationDate,ge,' + today + '&filter[]=status,eq,Available&order[]=availableDate,desc&transform=1';
                var show = false;
            } else {
                var url = '<?php echo API_HOST_URL; ?>' + '/carrier_needs?include=entities&columns=entities.name,id,entityID,qty,availableDate,expirationDate,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,needsDataPoints,status,contactEmails&filter[]=expirationDate,ge,' + today + '&filter[]=status,eq,Available&satisfy=all&order[]=entityID&order[]=availableDate,desc&transform=1';
                var show = true;
            }

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
                    {
                        "className":      'details-control-add',
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    { data: "entities[0].name", visible: show },
                    { data: "id", visible: false },
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
                    { data: "needsDataPoints", visible: false },
                    { data: "contactEmails", visible: false },
                    { data: "status" },
                    {
                        data: null,
                        "bSortable": false,
                        "mRender": function (o) {
                            var buttons = '<div class="pull-right text-nowrap">';
                            //buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-edit text\"></i> <span class=\"text\">Request Carrier</span></button>';
/*
                            if (o.status == "Open") {
                                      buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-remove text\"></i> <span class=\"text\">Close</span></button>";
                            } else {
                                      buttons += " &nbsp;<button class=\"btn btn-danger btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-exclamation-sign text\"></i> <span class=\"text\">Open</span></button>";
                            }
*/
                            buttons += "</div>";

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
              //myApp.hidePleaseWait();

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
                              //alert("There Was An Error Deleting Need Records!");
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
                    $("#errorAlertTitle").html("Success");
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

    #destination-list{float:left;list-style:none;margin-top:-3px;padding:0;width:250px;position: relative;}

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
   <li class="active">View Carrier Needs</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Current Carrier Needs</span></h4>
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
         <!--button type="button" id="addNeed" class="btn btn-primary pull-xs-right" data-target="#myModal">Add New Need</button-->
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th></th>
                     <th>Company</th>
                     <th>ID</th>
                     <th>Entity ID</th>
                     <th>Quantity</th>
                     <th>Available Date</th>
                     <th>Expiration Date</th>
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
                     <th class="hidden-sm-down">Data Points</th>
                     <th class="hidden-sm-down">Contact</th>
                     <th>Status</th>
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
         <h5 class="modal-title" id="exampleModalLabel"><strong>Need</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formNeed" class="register-form mt-lg">
                 <input type="hidden" id="id" name="id" value="" />
                 <div class="row">
                     <div class="col-sm-2">
                         <label for="qty"># Looking For:</label>
                         <div id="qty" class="form-group form-control mb-sm">
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="availableDate">Need Date</label>
                         <div id="availableDate" class="form-group form-control mb-sm">
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="expirationDate">Expiration Date</label>
                         <div id="expirationDate" class="form-group form-control mb-sm">
                         </div>
                     </div>
                     <div class="col-sm-4">
                         <div class="form-group">
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-3">
                         <label for="originationCity">Origination City</label>
                         <div id="originationCity" class="form-group form-control mb-sm">
                         </div>
                         <div id="suggesstion-box" class="frmSearch"></div>
                     </div>
                     <div class="col-sm-4">
                         <label for="originationAddress1">Origination Address</label>
                         <div id="originationAddress1" class="form-group form-control mb-sm">
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="originationState">Origination State</label>
                         <div id="originationState" class="form-group form-control mb-sm">
                         </div>
                     </div>
                     <div class="col-sm-2">
                         <label for="originationZip">Origination Zip</label>
                         <div id="originationZip" class="form-group form-control mb-sm">
                         </div>
                     </div>
                 </div>
                 <div class="row">
                   <div class="col-sm-3">
                       <label for="DestinationCity">Destination City</label>
                       <div id="destinationCity" class="form-group form-control mb-sm">
                       </div>
                   </div>
                   <div class="col-sm-4">
                       <label for="destinationAddress1">Destination Address</label>
                       <div id="destinationAddress1" class="form-group form-control mb-sm">
                       </div>
                   </div>
                   <div class="col-sm-3">
                       <label for="destinationState">Destination State</label>
                       <div id="destinationState" class="form-group form-control mb-sm">
                       </div>
                   </div>
                   <div class="col-sm-2">
                       <label for="destinationZip">Destination Zip</label>
                       <div id="destinationZip" class="form-group form-control mb-sm">
                       </div>
                   </div>
                 </div>
                 <!--
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
                 -->
               </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="return post();">Request Carrier</button>
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

    $("#addNeed").click(function(){
      var li = '';
      var checked = '';
      var dpli = '';
      var dpchecked = '';
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
      for (var i = 0; i < contacts.contacts.records.length; i++) {
          li += '<li id=\"' + contacts.contacts.records[i][0] + '\" class=\"list-group-item\" ' + checked + '>' + contacts.contacts.records[i][1] + ' ' + contacts.contacts.records[i][2] + '</li>\n';
      }
      $("#check-list-box").html(li);
      for (var i = 0; i < dataPoints.object_type_data_points.length; i++) {
          dpli += '<li>' + dataPoints.object_type_data_points[i].title +
                  ' <select class="form-control mb-sm" id="' + dataPoints.object_type_data_points[i].columnName + '" name="' + dataPoints.object_type_data_points[i].columnName + '">\n' +
                  ' <option value="">-Select One-</option>\n';
          for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {
              dpli += '<option>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</option>\n';
          }
          dpli += '</select>\n' +
                  '</li>\n';
      }
      //$("#dp-check-list-box").html(dpli);
      formatListBox();
      formatListBoxDP();
      $("#entityID").prop('disabled', false);
      $("#exampleModalLabel").html('Add New Need');
      $("#suggesstion-box").hide();
  		$("#myModal").modal('show');
  	});

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();

        if (this.textContent.indexOf("Request Carrier") > -1) {
            var li = '';
            var checked = '';
            var dpli = '';
            var dpchecked = '';
            $("#id").html(data["id"]);
            //$("#entityID").html(data["entityID"]);
            $("#qty").html(data["qty"]);
            $("#availableDate").html(data["availableDate"]);
            $("#expirationDate").html(data["expirationDate"]);
            $("#originationAddress1").html(data["originationAddress1"]);
            $("#originationCity").html(data["originationCity"]);
            $("#originationState").html(data["originationState"]);
            $("#originationZip").html(data["originationZip"]);
            $("#destinationAddress1").html(data["destinationAddress1"]);
            $("#destinationCity").html(data["destinationCity"]);
            $("#destinationState").html(data["destinationState"]);
            $("#destinationZip").html(data["destinationZip"]);
            var ndp = data["needsDataPoints"];
            var con = data["contactEmails"];

            var params = {id: $("#entityID").val()};
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
                 // alert('Failed Getting Contacts! - Notify NEC of this failure.');
                $("#errorAlertTitle").html("Failed Getting Contacts!");
                $("#errorAlertBody").html("Notify NEC of this failure.");
                $("#errorAlert").modal('show');
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

                dpli += '<li>' + dataPoints.object_type_data_points[i].title;
                for (var v = 0; v < dataPoints.object_type_data_points[i].object_type_data_point_values.length; v++) {

                    if (dataPoints.object_type_data_points[i].object_type_data_point_values[v].value === value) {
                        selected = ' selected ';
                        dpli += ' => <strong>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</strong>\n';
                    } else {
                        selected = '';
                    }

                    //dpli += '<option' + selected + '>' + dataPoints.object_type_data_points[i].object_type_data_point_values[v].value + '</option>\n';

                }

                dpli += '</li>\n';
            }
            //$("#dp-check-list-box").html(dpli);
            formatListBox();
            formatListBoxDP();
            $("#entityID").prop('disabled', true);
            $("#exampleModalLabel").html('Carrier Need');
            $("#suggesstion-box").hide();
            $("#myModal").modal('show');
        } else {
            $("#id").val(data["id"]);
            if (this.textContent.indexOf("Close") > -1) {
              $("#disableDialogLabel").html('Close <strong>' + data['name'] + '</strong>');
              $("#myDisableDialog").modal('show');
            } else {
              if (this.textContent.indexOf("Open") > -1) {
                $("#enableDialogLabel").html('Open <strong>' + data['name'] + '</strong>');
                $("#myEnableDialog").modal('show');
              }
            }
        }

    });

    $('#entityID').on( 'change', function () {
        var params = {id: $("#entityID").val()};
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
                $("#errorAlertTitle").html("Failed Getting Contacts!");
                $("#errorAlertBody").html("Notify NEC of this failure.");
                $("#errorAlert").modal('show');
           }
        });
    });

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
              $("#suggesstion-box").html(li);
        			$("#suggesstion-box").show();
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
             $("#originationCity").val(response.city);
             $("#originationState").val(response.state);
             $("#originationZip").val(response.zip);
             $("#suggesstion-box").hide();
           },
           error: function() {
                //alert('Error Selecting Origination City!');
                $("#errorAlertTitle").html("Failed Getting Contacts!");
                $("#errorAlertBody").html("Error Selecting Origination City!");
                $("#errorAlert").modal('show');
           }
        });
    }

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
              $("#suggesstion-box").html(li);
        			$("#suggesstion-box").show();
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
             $("#destinationCity").val(response.city);
             $("#destinationState").val(response.state);
             $("#destinationZip").val(response.zip);
             $("#suggesstion-box").hide();
           },
           error: function() {
                // alert('Error Selecting Destination City!');
                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Error Selecting Destination City!");
                $("#errorAlert").modal('show');
           }
        });
    }

    $("#myModal").on("hidden.bs.modal", function () {
        $("#entityID").prop('disabled', false);
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
