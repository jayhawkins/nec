<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

$state = '';
$states = json_decode(file_get_contents(API_HOST.'/api/states?columns=abbreviation,name&order=name'));

$entities = '';
$entities = json_decode(file_get_contents(API_HOST.'/api/entities?columns=id,name&order=name&filter[]=id,gt,0&filter[]=entityTypeID,eq,1'));


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

$dataPoints = json_decode(file_get_contents(API_HOST."/api/object_type_data_points?include=object_type_data_point_values&transform=1&columns=id,columnName,title,status,object_type_data_point_values.value&filter[]=entityID,in,(0," . $_SESSION['entityid'] . ")&filter[]=status,eq,Active&order[]=sort_order" ));


// No longer needed. We don't load via PHP anymore. All handled in JS function.
//$getlocations = json_decode(file_get_contents(API_HOST.'/api/locations?include=location_types&columns=locations.name,location_types.name,locations.address1,locations.address2,locations.city,locations.state,locations.zip,locations.status&filter=entityID,eq,' . $_SESSION['entityid'] . '&order=locationTypeID'),true);
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

          if (result) { verifyAndPost(); } else { return false; }
      }

      function verifyAndPost() {

          if ( $('#formNeed').parsley().validate() ) {

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
                                  var url = '<?php echo API_HOST."/api/customer_needs" ?>/' + $("#id").val();
                                  type = "PUT";
                              } else {
                                  var url = '<?php echo API_HOST."/api/customer_needs" ?>';
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
                                  var data = {qty: $("#qty").val(), payout: $("#payout").val(), originationAddress1: $("#originationAddress1").val(), originationCity: $("#originationCity").val(), originationState: $("#originationState").val(), originationZip: $("#originationZip").val(), destinationAddress1: $("#destinationAddress1").val(), destinationCity: $("#destinationCity").val(), destinationState: $("#destinationState").val(), destinationZip: $("#destinationZip").val(), originationLat: originationlat, originationLng: originationlng, destinationLat: destinationlat, destinationLng: destinationlng, needsDataPoints: needsdatapoints, contactEmails: $contacts, availableDate: $("#availableDate").val(), expirationDate: $("#expirationDate").val(), updatedAt: date};
                              } else {
                                  var date = today;
                                  var recStatus = 'Available';
                                  var data = {entityID: $("#entityID").val(), qty: $("#qty").val(), payout: $("#payout").val(), originationAddress1: $("#originationAddress1").val(), originationCity: $("#originationCity").val(), originationState: $("#originationState").val(), originationZip: $("#originationZip").val(), destinationAddress1: $("#destinationAddress1").val(), destinationCity: $("#destinationCity").val(), destinationState: $("#destinationState").val(), destinationZip: $("#destinationZip").val(), originationLat: originationlat, originationLng: originationlng, destinationLat: destinationlat, destinationLng: destinationlng, needsDataPoints: needsdatapoints, status: recStatus, contactEmails: $contacts, availableDate: $("#availableDate").val(), expirationDate: $("#expirationDate").val(), createdAt: date};
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
                                           url: '<?php echo HTTP_HOST."/customerneedsnotification" ?>',
                                           type: 'POST',
                                           data: JSON.stringify(params),
                                           contentType: "application/json",
                                           async: false,
                                           success: function(notification){
                                              alert(notification);
                                           },
                                           error: function() {
                                              alert('Failed Sending Notifications! - Notify NEC of this failure.');
                                           }
                                        });
                                      }
                                      $("#myModal").modal('hide');
                                      loadTableAJAX();
                                      $("#id").val('');
                                      $("#qty").val('');
                                      $("#payout").val('');
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
                                      alert("Adding Need Failed! Please Verify Your Data.");
                                    }
                                 },
                                 error: function() {
                                    alert("There Was An Error Adding Location!");
                                 }
                              });

                          } else {
                              alert("ERROR Geo-Coding Destination Address!");
                          }
                      });
                  } else {
                      alert("ERROR Geo-Coding Origination Address!");
                  }
                });

                return passValidation;

            } else {

                return false;

            }

      }

      function loadTableAJAX() {
        myApp.showPleaseWait();
        if (<?php echo $_SESSION['entityid']; ?> > 0) {
            var url = '<?php echo API_HOST; ?>' + '/api/customer_needs?include=entities&columns=entities.name,id,entityID,qty,payout,availableDate,expirationDate,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,needsDataPoints,status,contactEmails&filter[]=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&satisfy=all&order[]=availableDate,desc&transform=1';
            var show = false;
        } else {
            var url = '<?php echo API_HOST; ?>' + '/api/customer_needs?include=entities&columns=entities.name,id,entityID,qty,payout,availableDate,expirationDate,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,needsDataPoints,status,contactEmails&satisfy=all&order[]=entityID&order[]=availableDate,desc&transform=1';
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
                { data: "entities[0].name", visible: show },
                { data: "id", visible: false },
                { data: "entityID", visible: false },
                { data: "qty" },
                { data: "payout", render: $.fn.dataTable.render.number(',', '.', 2, '$')  },
                { data: "availableDate", visible: false },
                { data: "expirationDate", visible: false },
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
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var buttons = '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-edit text-info\"></i> <span class=\"text-info\">Edit</span></button>';

                        if (o.status == "Available") {
                                  buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-remove text-info\"></i> <span class=\"text-info\">Available</span></button>";
                        } else {
                                  buttons += " &nbsp;<button class=\"btn btn-danger btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-exclamation-sign text-info\"></i> <span class=\"text-info\">Unavailable</span></button>";
                        }

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
          myApp.hidePleaseWait();

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
          var url = '<?php echo API_HOST."/api/customer_needs" ?>/' + $("#id").val();
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
                  alert("Changing Status of Need Failed!");
                }
             },
             error: function() {
                alert("There Was An Error Changing Need Status!");
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

          var url = '<?php echo API_HOST."/api/locations_contacts?columns=location_id,contact_id&filter=entityID,eq," . $_SESSION['entityid'] ?>';
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

          var url = '<?php echo API_HOST."/api/locations?columns=id,city,state,zip&filter[]=entityID,eq," . $_SESSION['entityid'] ?>';
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

 </style>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Customer Trailer Availability Maintenance</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">My Availability</span></h4>
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
         <button type="button" id="addNeed" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Availability</button>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th>Company</th>
                     <th>ID</th>
                     <th>Entity ID</th>
                     <th>Quantity</th>
                     <th>Payout</th>
                     <th>Available Date</th>
                     <th>Expiration Date</th>
                     <th class="hidden-sm-down">Orig. Address</th>
                     <th class="hidden-sm-down">Orig. City</th>
                     <th class="hidden-sm-down">Orig. State</th>
                     <th class="hidden-sm-down">Orig. Zip</th>
                     <th class="hidden-sm-down">Orig. Lat.</th>
                     <th class="hidden-sm-down">Orig. Long.</th>
                     <th class="hidden-sm-down">Dest. Address</th>
                     <th class="hidden-sm-down">Dest. City</th>
                     <th class="hidden-sm-down">Dest. State</th>
                     <th class="hidden-sm-down">Dest. Zip</th>
                     <th class="hidden-sm-down">Dest. Lat.</th>
                     <th class="hidden-sm-down">Dest. Long.</th>
                     <th class="hidden-sm-down">Data Points</th>
                     <th class="hidden-sm-down">Contact</th>
                     <th class="no-sort pull-right">&nbsp;</th>
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
         <h5 class="modal-title" id="exampleModalLabel"><strong>Availability</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formNeed" class="register-form mt-lg">
                 <input type="hidden" id="id" name="id" value="" />
                 <div class="row">
                     <div class="col-sm-2">
                         <label for="qty">Trailers Available:</label>
                         <div class="form-group">
                           <input type="text" id="qty" name="qty" class="form-control mb-sm" placeholder="# Available"
                           required="required" />
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="availableDate">Available Date</label>
                         <div class="form-group">
                           <!--input type="text" id="policyExpirationDate" name="policyExpirationDate" class="form-control mb-sm" placeholder="Policy Expiration Date (YYYY-MM-DD)" required="required" /-->
                           <div id="sandbox-container" class="input-group date  datepicker">
                              <input type="text" id="availableDate" name="availableDate" class="form-control" placeholder="Availability Date"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                           </div>
                         </div>
                     </div>
                     <div class="col-sm-3">
                         <label for="expirationDate">Expiration Date</label>
                         <div class="form-group">
                           <!--input type="text" id="policyExpirationDate" name="policyExpirationDate" class="form-control mb-sm" placeholder="Policy Expiration Date (YYYY-MM-DD)" required="required" /-->
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
                             <label for="entityID">Customer:</label>
                             <select id="entityID" name="entityID" data-placeholder="Carrier" class="form-control chzn-select" required="required">
                               <option value="">*Select Customer...</option>
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
                         <div id="suggesstion-box" class="frmSearch"></div>
                     </div>
                     <div class="col-sm-4">
                         <label for="originationAddress1">Origination Address</label>
                         <div class="form-group">
                           <input type="text" id="originationAddress1" name="originationAddress1" class="form-control mb-sm" placeholder="Origin Address"
                           required="required" />
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
                           <input type="text" id="originationZip" name="originationZip" class="form-control mb-sm" placeholder="Origin Zip"
                           required="required" />
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
                   </div>
                   <div class="col-sm-4">
                       <label for="destinationAddress1">Destination Address</label>
                       <div class="form-group">
                         <input type="text" id="destinationAddress1" name="destinationAddress1" class="form-control mb-sm" placeholder="Destination Address"
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
                         <input type="text" id="destinationZip" name="destinationZip" class="form-control mb-sm" placeholder="Dest. Zip"
                         required="required" />
                       </div>
                   </div>
                 </div>
                 <hr />
                 <div class="row">
                     <div class="col-sm-3">
                         <label for="payout">Payout to Transport:</label>
                         <div class="form-group">
                           <input type="text" id="payout" name="payout" class="form-control mb-sm" placeholder="Payout $"
                           required="required" data-parsley-type="number" />
                         </div>
                     </div>
                     <div class="col-sm-9">
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
                             <h5 class="text-center"><strong>Contacts For This Availability:</strong></h5>
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
          <button type="button" class="btn btn-primary" onclick="return post();">Save Changes</button>
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
                             <h5>Do you wish to make this need Available?</h5>
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

 <script>

    //$( "#originationState" ).select2();
    //$( "#destinationState" ).select2();

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
      $("#payout").val('0.00');
      $("#payout").prop("disabled", false);
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

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();

        if (this.textContent.indexOf("Edit") > -1) {
            var li = '';
            var checked = '';
            var dpli = '';
            var dpchecked = '';
            $("#id").val(data["id"]);
            $("#entityID").val(data["entityID"]);
            $("#qty").val(data["qty"]);
            $("#payout").val(data["payout"].toFixed(2));
<?php if ($_SESSION['entityid'] > 0) { ?>
            $("#payout").prop("disabled", true);
<?php } ?>
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
                  alert('Failed Getting Contacts! - Notify NEC of this failure.');
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
                        ' <select class="form-control mb-sm" id="' + dataPoints.object_type_data_points[i].columnName + '" name="' + dataPoints.object_type_data_points[i].columnName + '">';
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
            formatListBox();
            formatListBoxDP();
            $("#entityID").prop('disabled', true);
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

    $('#entityID').on( 'change', function () {
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
              alert('Failed Getting Contacts! - Notify NEC of this failure.');
           }
        });
    });

    $("#originationCity").keyup(function(){
        $("#originationCity").css("background","#FFF url(img/loaderIcon.gif) no-repeat 165px");

        var url = '<?php echo API_HOST; ?>/api/locations?transform=1&columns=id,name,city&filter[]=entityID,eq,' + $("#entityID").val() + '&filter[]=city,sw,' + $(this).val();

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
             $("#originationLocationID").val(response.id);
             $("#originationAddress1").val(response.address1);
             $("#originationCity").val(response.city);
             $("#originationState").val(response.state);
             $("#originationZip").val(response.zip);
             var li = setContactsOnLocationSelected();
             $("#check-list-box").html(li);
             formatListBox();
             $("#suggesstion-box").hide();
           },
           error: function() {
                alert('Error Selecting Origination City!');
           }
        });
    }

    $("#destinationCity").keyup(function(){
        $("#destinationCity").css("background","#FFF url(img/loaderIcon.gif) no-repeat 165px");

        var url = '<?php echo API_HOST; ?>/api/locations?transform=1&columns=id,name,city&filter[]=entityID,eq,' + $("#entityID").val() + '&filter[]=city,sw,' + $(this).val();

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
             $("#destinationAddress1").val(response.address1);
             $("#destinationCity").val(response.city);
             $("#destinationState").val(response.state);
             $("#destinationZip").val(response.zip);
             $("#suggesstion-box").hide();
           },
           error: function() {
                alert('Error Selecting Destination City!');
           }
        });
    }

    $("#myModal").on("hidden.bs.modal", function () {
        $("#entityID").prop('disabled', false);
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


 </script>