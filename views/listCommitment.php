<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

$state = '';
$states = json_decode(file_get_contents(API_HOST.'/api/states?columns=abbreviation,name&order=name'));

$entity = '';
$entity = json_decode(file_get_contents(API_HOST.'/api/entities?columns=rateType,negotiatedRate&filter[]=id,eq,' . $_SESSION['entityid']));

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

$dataPoints = json_decode(file_get_contents(API_HOST."/api/object_type_data_points?include=object_type_data_point_values&transform=1&columns=id,columnName,title,status,object_type_data_point_values.value&filter[]=entityID,in,(0," . $_SESSION['entityid'] . ")&filter[]=status,eq,Active" ));


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

          if (confirm("You have selected to Approve this Commit. Do you wish to proceed?") == true) {

                    verifyAndPost(function(data) {
                      alert(data);
                      $("#load").html("Commit");
                      $("#load").prop("disabled", false);
                    });
                    return true;
                
          } else {

                $("#myModalCommit").modal('hide');

          }
      }

      function verifyAndPost() {

          if ( $('#formNeed').parsley().validate() ) {

                $("#load").html("<i class='fa fa-spinner fa-spin'></i> Approving Commit");
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

                var url = '<?php echo API_HOST."/api/customer_needs_commit" ?>/' + $("#id").val();
                type = "PUT";
                var date = today;
                var data = {status: "Close", updatedAt: date};

                $.ajax({
                   url: url,
                   type: type,
                   data: JSON.stringify(data),
                   contentType: "application/json",
                   async: false,
                   success: function(data){
                      if (data > 0) {
                        if (type == 'POST') {
                           var params = {id: $("#id").val()};
                           $.ajax({
                              url: '<?php echo HTTP_HOST."/commitacceptednotification" ?>',
                              type: 'POST',
                              data: JSON.stringify(params),
                              contentType: "application/json",
                              async: false,
                              success: function(notification){
                                  alert(notification);
                                  $("#myModalCommit").modal('hide');
                              },
                              error: function() {
                                 alert('Failed Sending Notifications! - Notify NEC of this failure.');
                                 $("#myModalCommit").modal('hide');
                              }
                           });
                        }

                        $("#myModal").modal('hide');
                        loadTableAJAX();
                        $("#id").val('');
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
                        $("#pickupDate").val('');
                        $("#deliveryDate").val('');
                        $("#rate").val('');
                        passValidation = true;
                      } else {
                        alert("Adding Need Failed! Please Verify Your Data.");
                      }
                   },
                   error: function() {
                      alert("There Was An Error Adding Availability!");
                   }
                });

                return passValidation;
              
            } else {

                return false;

            }

      }

      function loadTableAJAX() {

        if (<?php echo $_SESSION['entityid']; ?> > 0) {
            var url = '<?php echo API_HOST; ?>' + '/api/customer_needs_commit?include=customer_needs,entities&columns=id,customerNeedsID,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,status,qty,rate,transportation_mode,transportation_type,pickupDate,deliveryDate,customer_needs.needsDataPoints,distance,customer_needs.expirationDate,customer_needs.availableDate,entities.name,entities.rateType,entities.negotiatedRate&order[]=pickupDate,desc&transform=1';
            var show = false;
        } else {
            var url = '<?php echo API_HOST; ?>' + '/api/customer_needs_commit?include=customer_needs,entities&columns=id,customerNeedsID,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,status,qty,rate,transportation_mode,transportation_type,pickupDate,deliveryDate,customer_needs.needsDataPoints,distance,customer_needs.expirationDate,customer_needs.availableDate,entities.name,entities.rateType,entities.negotiatedRate&satify=all&order[]=id&order[]=pickupDate,desc&transform=1';
            var show = true;
        }

        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                dataSrc: "customer_needs_commit"
            },
            columns: [
                {
                    className:      'details-control-add',
                    orderable:      false,
                    data:           null,
                    defaultContent: ''
                },
                { data: "customer_needs[0].entities[0].name", visible: true },
                { data: "customer_needs[0].entities[0].negotiatedRate", visible: true},
                { data: "rate", visible: true },
                { data: "id", visible: false },
                { data: "customer_needs[0].entityID", visible: false },
                { data: "qty" },
                { data: "customer_needs[0].availableDate", visible:false },
                { data: "customer_needs[0].expirationDate" },
                { data: "pickupDate" },
                { data: "deliveryDate" },
                { data: "transportation_mode" },
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
                { data: "customer_needs[0].needsDataPoints", visible: false },
                { data: "status", visible: false },
                { data: "customer_needs[0].entities[0].rateType", visible: false },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var buttons = '';
                        var status = o.status;
                        
                        if(status == "Open"){
                            buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"fa fa-thumbs-up text-info\"></i> <span class=\"text\">Accept Commitment</span></button>";
                        }
                        else{
                            buttons += "All ready Approved!" ;
                        }
                        
                        return buttons;
                    }, visible: true
                }
            ],
            scrollX: true
          });
          

        //example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );

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
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Committed Transport</span></h4>
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
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th></th>
                     <th>Company</th>
                     <th>Negotiated Rate</th>
                     <th>Commit Rate</th>
                     <th>ID</th>
                     <th>Entity ID</th>
                     <th>Qty</th>
                     <th>Available</th>
                     <th>Expires</th>
                     <th>Pickup</th>
                     <th>Delivery</th>
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
                     <th>Commit Status</th>
                     <th>Rate Type</th>
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

  <!-- Modal myModalCommit -->
  <div class="modal fade" id="myModalCommit" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Accept Commitment</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
                <form id="formNeed" class="register-form mt-lg">
                  <input type="hidden" id="id" name="id" value="" />
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
                  <div class="row">
                      <div class="col-sm-2">
                          <label for="qtyDiv"># of Trailers</label>
                          <div id="qtyDiv" class="form-group">
                          </div>
                      </div>
                      <div class="col-sm-3">
                          <label for="availableDate">Pick-Up Date</label>
                          <div class="form-group">
                            <div id="sandbox-container" class="input-group date ">
                               <input type="text" id="pickupDate" name="pickupDate" class="form-control" placeholder="Pickup Date" required="required" readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                            </div>
                          </div>
                      </div>
                      <div class="col-sm-3">
                          <label for="expirationDate">Delivery Date</label>
                          <div class="form-group">
                            <div id="sandbox-container" class="input-group date ">
                               <input type="text" id="deliveryDate" name="deliveryDate" class="form-control" placeholder="Delivery Date" required="required" readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                            </div>
                          </div>
                      </div>
                      <!--<div class="col-sm-4">
                          <div class="form-group">
              <?php if ($_SESSION['entityid'] > 0) { ?>
                             <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
              <?php } else { ?>
                              <label for="entityID">Customer</label>
                              <select id="entityID" name="entityID" data-placeholder="Carrier" class="form-control chzn-select" required="required">
                                <option value="">*Select Customer...</option>
               <?php
                                foreach($entities->entities->records as $value) {
                                    $selected = ($value[0] == $entity) ? 'selected=selected':'';
                                    //echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                                }
               ?>
                              </select>
               <?php } ?>
                          </div>
                      </div>-->
                  </div>
                  <div class="row">
                      <div class="col-sm-3">
                          <label for="originationCity">Origination City</label>
                          <div class="form-group">
                            <input type="hidden" id="originationLocationID" name="originationLocationID" />
                            <input type="text" id="originationCity" name="originationCity" class="form-control mb-sm" placeholder="Origin City"
                            required="required" readonly/>
                          </div>
                          <div id="org-suggesstion-box" class="frmSearch"></div>
                      </div>
                      <div class="col-sm-4">
                          <label for="originationAddress1">Origination Address</label>
                          <div class="form-group">
                            <input type="text" id="originationAddress1" name="originationAddress1" class="form-control mb-sm" placeholder="Origin Address"
                            required="required" readonly/>
                          </div>
                      </div>
                      <div class="col-sm-3">
                          <label for="originationState">Origination State</label>
                          <div class="form-group">
                            <select id="originationState" name="origitnaionState" data-placeholder="Origin State" class="form-control chzn-select" data-ui-jq="select2" required="required" disabled>
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
                            required="required" readonly/>
                          </div>
                      </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-3">
                        <label for="DestinationCity">Destination City</label>
                        <div class="form-group">
                          <input type="text" id="destinationCity" name="destinationCity" class="form-control mb-sm" placeholder="Dest. City"
                          required="required" readonly/>
                        </div>
                        <div id="dest-suggesstion-box" class="frmSearch"></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="destinationAddress1">Destination Address</label>
                        <div class="form-group">
                          <input type="text" id="destinationAddress1" name="destinationAddress1" class="form-control mb-sm" placeholder="Destination Address"
                          required="required" readonly/>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label for="destinationState">Destination State</label>
                        <div class="form-group">
                          <select id="destinationState" name="destinationState" data-placeholder="Dest. State" class="form-control chzn-select" data-ui-jq="select2" required="required" disabled>
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
                          required="required" readonly/>
                        </div>
                    </div>
                  </div>
                  <hr/>
                  <div class="row">
                      <div class="col-sm-3">
                          <label for="rate">Rate to Transport</label>
                          <div class="form-group">
                            <input type="text" id="rate" name="rate" class="form-control mb-sm" placeholder="$ Rate to Transport"
                            required="required" readonly/>
                          </div>
                      </div>
                      <div class="col-sm-3">
                          <label for="rate">Rate Type</label>
                          <div class="form-group">
                            <div class="d-inline-block"><input type="radio" id="transportionType" name="transportationType" value="Flat Rate" disabled/> Flat Rate
                            &nbsp;&nbsp;<input type="radio" id="transportionType" name="transportationType" value="Mileage" disabled/> Mileage</div>
                          </div>
                      </div>
                      <div class="col-sm-3">
                        <label for="transportationModeDiv">Transportation Mode</label>
                        <div id="transportationModeDiv" class="form-group">
                        </div>
                      </div>
                      <div class="col-sm-3">
                      </div>
                  </div>
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary btn-md" onclick="return post();" id="load">Accept Commit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal myCancelDialog -->
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
                  <input type="hidden" id="id" name="id" value="" />
                  <div class="row">
                      <div class="col-sm-12">
                        <label for="rate">Reason for Cancellation</label>
                        <select id="cancellationReason" name="cancellationReason" data-placeholder="Cancellation Reason" class="form-control chzn-select" required="required">
                          <option value="">*Select Reason...</option>
                          <option>Trailers No Longer Available</option>
                          <option>Need No Longer Available</option>
                          <option>Submitted In Error</option>
                          <option>Other</option>
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
                        <label for="rate">If "Other" please explain</label>
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

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();

        if (this.textContent.indexOf("Accept Commitment") > -1) {
            var li = '';
            var checked = '';
            var qtyselect = '<select id="qty" class="form-control mb-sm" disabled>\n';
            var transportationmodeselect = '<select id="transportationMode" name="transportationMode" class="form-control mb-sm" required="required" disabled>\n';
            var dpchecked = '';
            $("#id").val(data["id"]);
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
            $("#pickupDate").val(data["pickupDate"]);
            $("#deliveryDate").val(data["deliveryDate"]);
            // Set up the matching addresses like we do up in the verifyAndPost() - makes it easy to do a compare
            $("#originToMatch").val(data["originationAddress1"] + ', ' + data["originationCity"] + ', ' + data["originationState"] + ', ' + data["originationZip"]);
            $("#destToMatch").val(data["destinationAddress1"] + ', ' + data["destinationCity"] + ', ' + data["destinationState"] + ', ' + data["destinationZip"]);
            $("#rate").val(data["rate"].toFixed(2));

            for (var i = 1; i <= data['qty']; i++) {
                if (i == data['qty']) {
                    dpchecked = "selected=selected";
                }
                qtyselect += '<option ' + dpchecked + '>' + i + '</option>\n';
            }
            qtyselect += '</select>\n';
            $("#qtyDiv").html(qtyselect);

            if (data["transportation_type"] == "Flat Rate") {
                $('input[name="transportationType"][value="Flat Rate"]').prop('checked', true);
            } else {
                $('input[name="transportationType"][value="Mileage"]').prop('checked', true);
            }

            var empty = "";
            var loadout = "";
            var either = "";
            if (data['transportation_mode'] == "Empty") {
                transportationmodeselect += '<option value="Empty">Empty</option>\n';
            } 
            else {
                if (data['transportation_mode'] == "Empty") {
                    empty = "selected=selected";
                } 
                else if (data['transportation_mode'] == "Load Out"){
                    loadout = "selected=selected";
                } 
                else if (data['transportation_mode'] == "Both (Empty or Load Out)"){
                    either = "selected=selected";
                }
                transportationmodeselect += '<option value="Empty" '+empty+'>Empty</option>\n';
                transportationmodeselect += '<option value="Load Out" '+loadout+'>Load Out</option>\n';
                transportationmodeselect += '<option value="Both (Empty or Load Out)" '+either+'>Both (Empty or Load Out)</option>\n';
            }

            //transportationmodeselect += '<option value="Empty">Empty</option>\n';
            //transportationmodeselect += '<option value="Load Out">Load Out</option>\n';
            //transportationmodeselect += '<option value="Both (Empty or Load Out)">Both (Empty or Load Out)</option>\n';
            transportationmodeselect += '</select>\n';
            $("#transportationModeDiv").html(transportationmodeselect);

            $("#entityID").prop('disabled', true);
            $("#myModalCommit").modal('show');
          } 
        else if (this.textContent.indexOf("Cancel") > -1) {
            $("#myCancelDialog").modal('show');
        } 
        else {
          //Nothing - Somehow got in here???
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

    $("#myModal").on("hidden.bs.modal", function () {
        $("#entityID").prop('disabled', false);
    });

    $('#btnCommit').click(function () {
        $("#myModalCommit").modal('show');
        
    });

    /* Formatting function for row details - modify as you need */
    function format ( d ) {

        var table = '<table  class="col-sm-12" cellpadding="5" cellspacing="0" border="0"><tr>';

        // `d` is the original data object for the row
        var ndp = d.customer_needs[0].needsDataPoints;

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
