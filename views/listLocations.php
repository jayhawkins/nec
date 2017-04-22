<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

$state = '';
$states = json_decode(file_get_contents(API_HOST.'/api/states?columns=abbreviation,name&order=name'));

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

// No longer needed. We don't load via PHP anymore. All handled in JS function.
//$getlocations = json_decode(file_get_contents(API_HOST.'/api/locations?include=location_types&columns=locations.name,location_types.name,locations.address1,locations.address2,locations.city,locations.state,locations.zip,locations.status&filter=entityID,eq,' . $_SESSION['entityid'] . '&order=locationTypeID'),true);
//$locations = php_crud_api_transform($getlocations);

 ?>

 <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API; ?>"></script>

 <script>

     var contacts = <?php echo json_encode($contacts); ?>;
     console.log(contacts);

     var locations_contacts = <?php echo json_encode($locations_contacts); ?>;
     console.log(locations_contacts);

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

      function verifyAndPost() {

          if ( $('#formLocation').parsley().validate() ) {

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
                var address = $("#address1").val() + ' ' + $("#city").val() + ' ' + $("#state").val() + ' ' + $("#zip").val();

                geocoder.geocode( { 'address': address}, function(results, status) {

                  if (status == google.maps.GeocoderStatus.OK) {
                      var lat = results[0].geometry.location.lat();
                      var lng = results[0].geometry.location.lng();

                      //var url = '<?php echo API_HOST."/api/locations" ?>';
                      if ($("#id").val() > '') {
                          var url = '<?php echo API_HOST."/api/locations" ?>/' + $("#id").val();
                          type = "PUT";
                      } else {
                          var url = '<?php echo API_HOST."/api/locations" ?>';
                          type = "POST";
                      }

                      if (type == "PUT") {
                          var date = today;
                          var data = {entityID: $("#entityID").val(), locationTypeID: $("#locationTypeID").val(), name: $("#name").val(), address1: $("#address1").val(), address2: $("#address2").val(), city: $("#city").val(), state: $("#state").val(), zip: $("#zip").val(), latitude: lat, longitude: lng, updatedAt: date};
                      } else {
                          var date = today;
                          var data = {entityID: $("#entityID").val(), locationTypeID: $("#locationTypeID").val(), name: $("#name").val(), address1: $("#address1").val(), address2: $("#address2").val(), city: $("#city").val(), state: $("#state").val(), zip: $("#zip").val(), latitude: lat, longitude: lng, createdAt: date};
                      }

                      $.ajax({
                         url: url,
                         type: type,
                         data: JSON.stringify(data),
                         contentType: "application/json",
                         async: false,
                         success: function(data){
                            if (data > 0) {
                              $("#myModal").modal('hide');
                              loadTableAJAX();
                              $("#id").val('');
                              $("#locationTypeID").val('');
                              $("#name").val('');
                              $("#address1").val('');
                              $("#address2").val('');
                              $("#city").val('');
                              $("#state").val('');
                              $("#zip").val('');
                              passValidation = true;
                            } else {
                              alert("Adding Location Failed!");
                            }
                         },
                         error: function() {
                            alert("There Was An Error Adding Location!");
                         }
                      });
                  } else {
                      alert("ERROR Geo-Coding Address!");
                  }
                });

                return passValidation;

          } else {

                return false;

          }

      }

      function loadTableAJAX() {
        myApp.showPleaseWait();
        //var url = '<?php echo API_HOST; ?>' + '/api/locations?include=location_types&columns=locations.id,locations.name,location_types.id,location_types.name,locations.address1,locations.address2,locations.city,locations.state,locations.zip,locations.status&filter=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&order=locationTypeID&transform=1';
        var url = '<?php echo API_HOST; ?>' + '/api/locations?include=location_types&columns=locations.id,locations.name,location_types.id,location_types.name,locations.address1,locations.address2,locations.city,locations.state,locations.zip,locations.status&filter[]=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&filter[]=locationTypeID,ne,1&satisfy=all&order=locationTypeID&transform=1';

        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                dataSrc: 'locations'
            },
            columns: [
                { data: "id", visible: false },
                { data: "name" },
                { data: "location_types[0].id", visible: false },
                { data: "location_types[0].name" },
                { data: "address1" },
                { data: "address2", visible: false },
                { data: "city" },
                { data: "state" },
                { data: "zip" },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var buttons = '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-edit text-info\"></i> <span class=\"text-info\">Edit</span></button>';

                        if (o.status == "Active") {
                                  buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-remove text-info\"></i> <span class=\"text-info\">Disable</span></button>";
                        } else {
                                  buttons += " &nbsp;<button class=\"btn btn-danger btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-exclamation-sign text-info\"></i> <span class=\"text-info\">Enable</span></button>";
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
          myApp.hidePleaseWait();

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
          var url = '<?php echo API_HOST."/api/locations" ?>/' + $("#id").val();
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
                  alert("Changing Status of Location Failed!");
                }
             },
             error: function() {
                alert("There Was An Error Changing Location Status!");
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

              $widget.css('cursor', 'pointer')
              $widget.append($checkbox);

              // Event Handlers
              $widget.on('click', function () {
                  $checkbox.prop('checked', !$checkbox.is(':checked'));
                  $checkbox.triggerHandler('change');
                  updateDisplay();
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

                  updateDisplay();

                  // Inject the icon if applicable
                  if ($widget.find('.state-icon').length == 0) {
                      $widget.prepend('<span class="state-icon ' + settings[$widget.data('state')].icon + '"></span>');
                  }
              }
              init();
          });

          $('#get-checked-data').on('click', function(event) {
              event.preventDefault();
              var checkedItems = {}, counter = 0;
              $("#check-list-box li.active").each(function(idx, li) {
                  checkedItems[counter] = $(li).text();
                  counter++;
              });
              $('#display-json').html(JSON.stringify(checkedItems, null, '\t'));
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
 </style>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Location Maintenance</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Location</span></h4>
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
         <button type="button" id="addLocation" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Location</button>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th>ID</th>
                     <th>Name</th>
                     <th>Type ID</th>
                     <th>Type</th>
                     <th class="hidden-sm-down">Address1</th>
                     <th class="hidden-sm-down">Address2</th>
                     <th class="hidden-sm-down">City</th>
                     <th class="no-sort">State</th>
                     <th class="no-sort">Zip</th>
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
 <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel"><strong>Location</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formLocation" class="register-form mt-lg">
                 <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
                 <input type="hidden" id="id" name="id" value="" />
                 <div class="row">
                     <div class="col-sm-6">
                         <div class="form-group">
                           <input type="text" id="name" name="name" class="form-control mb-sm" placeholder="Location Title"
                           required="required" />
                         </div>
                     </div>
                     <div class="col-sm-6">
                         <div class="form-group">
                           <select id="locationTypeID" name="locationTypeID" data-placeholder="Location Type" class="form-control chzn-select" data-ui-jq="select2" required="required">
                             <option value="">*Select Type...</option>
            <?php
                             foreach($locationTypes->location_types->records as $value) {
                                 if ($value[2] == "Active") {
                                   $selected = ($value[0] == $locationTypeID) ? 'selected=selected':'';
                                   echo "<option value=" . $value[0] . ">" . $value[1] . "</option>\n";
                                 }
                             }
            ?>
                           </select>
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-6">
                       <div class="form-group">
                         <input type="text" id="address1" name="address1" class="form-control mb-sm" placeholder="Company Address" required="required" />
                       </div>
                     </div>
                     <div class="col-sm-6">
                         <div class="form-group">
                           <input type="text" id="address2" name="address2" class="form-control mb-sm" placeholder="Bldg. Number/Suite" />
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-4">
                         <div class="form-group">
                           <input type="text" id="city" name="city" class="form-control" placeholder="*City" required="required" />
                         </div>
                     </div>
                     <div class="col-sm-4">
                         <div class="form-group">
                           <select id="state" name="state" data-placeholder="State" class="form-control chzn-select" data-ui-jq="select2" required="required">
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
                     <div class="col-sm-4">
                         <div class="form-group">
                           <input type="text" id="zip" name="zip" class="form-control mb-sm" placeholder="Zip" required="required" />
                         </div>
                     </div>
                 </div>
                 <hr />
                 <div class="container" style="margin-top:20px;">
                 <div class="row">
                   <div class="col-xs-6">
                        <h5 class="text-center"><strong>Associated Contacts</strong></h5>
                        <div class="well" style="max-height: 200px;overflow: auto;">
                            <ul id="contacts" class="list-group checked-list-box">

                            </ul>
                        </div>
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
                            <h5>Do you wish to disable this location?</h5>
                          </div>
                      </div>
                  </div>
                 </form>
        </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Disable');">Disable Location</button>
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
                             <h5>Do you wish to enable this location?</h5>
                           </div>
                       </div>
                   </div>
                  </form>
         </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Enable');">Enable Location</button>
          </div>
        </div>
      </div>
    </div>

 <script>



    $( "#state" ).select2();

    $( "#locationTypeID" ).select2();

    loadTableAJAX();

    var table = $("#datatable-table").DataTable();
    var tableContact = $("#datatable-table-contact").DataTable();

    $("#addLocation").click(function(){
      $("#id").val('');
      $("#locationTypeID").val('');
      $("#name").val('');
      $("#address1").val('');
      $("#address2").val('');
      $("#city").val('');
      $("#state").val('');
      $("#zip").val('');
  		$("#myModal").modal('show');
  	});

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();
        if (this.textContent.indexOf("Edit") > -1) {
          var li = '';
          var checked = '';
          $("#id").val(data["id"]);
          $("#locationTypeID").val(data["location_types"][0].id);
          $("#name").val(data["name"]);
          $("#address1").val(data["address1"]);
          $("#address2").val(data["address2"]);
          $("#city").val(data["city"]);
          $("#state").val(data["state"]);
          $("#zip").val(data["zip"]);
          console.log("contacts records: " + contacts.contacts.records.length);
          console.log("locations_contacts records: " + locations_contacts.locations_contacts.records.length);
          console.log('data id is ' + data["id"]);
          for (var i = 0; i < contacts.contacts.records.length; i++) {
              for (var l = 0; l < locations_contacts.locations_contacts.records.length; l++) {
                  //console.log(contacts.contacts.records[i]);
                  if ( locations_contacts.locations_contacts.records[l][0] == data["id"] ) {
                      if (locations_contacts.locations_contacts.records[l][1] == contacts.contacts.records[i][0]) {
                          checked = 'data-checked="true"';
                      } else {
                          checked = '';
                      }
                  }
              }
              console.log('checked is: ' + checked);
              li += '<li id=\"' + contacts.contacts.records[i][0] + '\" class=\"list-group-item\" ' + checked + '>' + contacts.contacts.records[i][1] + ' ' + contacts.contacts.records[i][2] + '</li>\n';
              console.log(li);
          }
          $("#contacts").html(li);
          formatListBox();
          $("#myModal").modal('show');
        } else {
            $("#id").val(data["id"]);
            if (this.textContent.indexOf("Disable") > -1) {
              $("#disableDialogLabel").html('Disable <strong>' + data['name'] + '</strong>');
              $("#myDisableDialog").modal('show');
            } else {
              if (this.textContent.indexOf("Enable") > -1) {
                $("#enableDialogLabel").html('Enable <strong>' + data['name'] + '</strong>');
                $("#myEnableDialog").modal('show');
              }
            }
        }

    } );



 </script>
