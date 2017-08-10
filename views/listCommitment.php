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
                        if (type == 'PUT') {
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

                        $("#myModalCommit").modal('hide');
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

            } 
            else {

                return false;

            }

      }


    function getCustomerNeedRoot(){        
                
        var customerNeedsRootIDs = new Array();
        
        var url = '<?php echo API_HOST ?>' + '/api/customer_needs?columns=rootCustomerNeedsID&transform=1';
        var type = "GET";
        $.ajax({
            url: '<?php echo API_HOST ?>' + '/api/customer_needs?columns=rootCustomerNeedsID&transform=1',
            type: "GET",
            contentType: "application/json",
            success: function(data){
                
                var customerNeeds = data.customer_needs;
                
                customerNeeds.forEach(function(customerNeed){
                    
                    var id = customerNeed.rootCustomerNeedsID;
                    
                    if(customerNeedsRootIDs.indexOf(id) === -1){
                        customerNeedsRootIDs.push(id);
                    }
                });
                
                                             
            },
            error: function(error){
                console.log("Error: " + error);
            }
            
        });
          
          return customerNeedsRootIDs;
    }
      


      function loadTableAJAX() {        
        
        var baseUrl = '<?php echo API_HOST; ?>' + '/api/customer_needs?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,rate,availableDate,expirationDate,transportationMode,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,customer_needs_commit.id,customer_needs_commit.status,customer_needs_commit.rate,customer_needs_commit.transporation_mode,entities.name,entities.rateType,entities.negotiatedRate';
                            
        customerNeedsRootIDs.customer_needs.forEach(function(customer_need){
           
           baseUrl = baseUrl + "&filter[]=id,eq," + customer_need.rootCustomerNeedsID;
        });
        
        
        if (<?php echo $_SESSION['entityid']; ?> > 0) {
            var url = baseUrl + '&satisfy=any&order[]=rootCustomerNeedsID&order[]=availableDate,desc&transform=1';
            var show = false;
        } else {
            var url = baseUrl + '&satisfy=any&order[]=entityID&order[]=rootCustomerNeedsID&order[]=availableDate,desc&transform=1';
            var show = true;
        }

/*      if (<?php echo $_SESSION['entityid']; ?> > 0) {
            var url = '<?php echo API_HOST; ?>' + '/api/customer_needs_commit?include=customer_needs,entities&columns=id,customerNeedsID,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,status,qty,rate,transportation_mode,transportation_type,pickupDate,deliveryDate,customer_needs.needsDataPoints,distance,customer_needs.expirationDate,customer_needs.availableDate,entities.name,entities.rateType,entities.negotiatedRate&order[]=pickupDate,desc&transform=1';
            var show = false;
        } else {
            var url = '<?php echo API_HOST; ?>' + '/api/customer_needs_commit?include=customer_needs,entities&columns=id,customerNeedsID,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,status,qty,rate,transportation_mode,transportation_type,pickupDate,deliveryDate,customer_needs.needsDataPoints,distance,customer_needs.expirationDate,customer_needs.availableDate,entities.name,entities.rateType,entities.negotiatedRate&satify=all&order[]=id&order[]=pickupDate,desc&transform=1';
            var show = true;
        }*/

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
                { data: "entities[0].name", visible: show },
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
                    "render": function(o) {
                      var newStatus = o.status;
                      if (o.customer_needs_commit.length > 0) {
                          var showAmount = o.customer_needs_commit[0].rate.toString().split(".");
                          showAmount[0] = showAmount[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                          if (showAmount.length > 1) {
                              if (showAmount[1].length < 2) {
                                  showAmount[1] = showAmount[1] + '0';
                              }
                              showAmount = "$" + showAmount[0] + "." + showAmount[1];
                          } else {
                              showAmount = "$" + showAmount[0] + ".00";
                          }
                          if (o.customer_needs_commit[0].status == "Cancelled") {
                              newStatus = "<strong>Cancelled</strong>";
                          } else {
                              newStatus = "<strong>Committed</strong>";
                          }
                      }
                      return newStatus;
                    }, visible: false
                },
                { data: "customer_needs_commit[0].id", visible: false },
                { data: "customer_needs_commit[0].status", visible: false },
                { data: "customer_needs_commit[0].rate", visible: false },
                { data: "customer_needs_commit[0].transportation_mode", visible: false },
                { data: "entities[0].name", visible: false },
                { data: "entities[0].rateType", visible: false },
                { data: "rate", visible: false},
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var buttons = '';
                        
                        buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"fa fa-check text-info\"></i> <span class=\"text-info\">View Commits</span></button>';

                        return buttons;
                    }
                }
                
            ]
          });

          example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );
          
          
/*
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
                {                     
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var input = '';
                        var status = o.status;
                        var customerRate = o.customer_needs[0].entities[0].negotiatedRate.toFixed(2);
                        
                        if(status == "Open"){
                            input += "<input type=\"text\" name=\"customerRate\" class=\"form-control mb-sm\" placeholder=\"Customer Rate\" value=\"" + customerRate + "\"/>";
                        }
                        else{
                            input += "<input type=\"text\" name=\"customerRate\" class=\"form-control mb-sm\" placeholder=\"Customer Rate\" value=\"" + customerRate + "\" readonly/>" ;
                        }
                        
                        return input;
                    }, visible: true
                },
                {                     
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var input = '';
                        var status = o.status;
                        var carrierRate = o.rate.toFixed(2);
                        
                        if(status == "Open"){
                            input += "<input type=\"text\" name=\"carrierRate\" class=\"form-control mb-sm\" placeholder=\"Carrier Rate\" value=\"" + carrierRate + "\"/>";
                        }
                        else{
                            input += "<input type=\"text\" name=\"carrierRate\" class=\"form-control mb-sm\" placeholder=\"Carrier Rate\" value=\"" + carrierRate + "\" readonly/>";
                        }
                                                
                        return input;
                    }, visible: true
                },
                { data: "id", visible: false },
                { data: "customer_needs[0].entityID", visible: false },
                { data: "qty" },
                { data: "customer_needs[0].availableDate", visible:false },
                { data: "customer_needs[0].expirationDate", visible: false },
                { data: "pickupDate" },
                { data: "deliveryDate" },
                { data: "transportation_mode" },
                { data: "originationAddress1", visible: true },
                { data: "originationCity" },
                { data: "originationState" },
                { data: "originationZip", visible: false },
                { data: "originationLat", visible: false },
                { data: "originationLng", visible: false },
                { data: "destinationAddress1", visible: true },
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
                            buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\" disabled><i class=\"fa fa-thumbs-up text-info\"></i> <span class=\"text\">Accept Commitment</span></button>";
                        }
                        else{
                            buttons += "Already Approved!" ;
                        }

                        return buttons;
                    }, visible: true
                }
            ],
            scrollX: true
          });
*/

        //To Reload The Ajax
        //See DataTables.net for more information about the reload method
        example_table.ajax.reload();
        $("#entityID").prop('disabled', false);
        $("#load").html("Commit");
        $("#load").prop("disabled", false);

      }

      function loadCustomerNeedsCommitAJAX (id){
                 
        var url = '<?php echo API_HOST; ?>' + '/api/customer_needs?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,availableDate,expirationDate,transportationMode,rate,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,customer_needs_commit.id,customer_needs_commit.status,customer_needs_commit.pickupDate,customer_needs_commit.deliveryDate,customer_needs_commit.rate,customer_needs_commit.transporation_mode,entities.name,entities.rateType,entities.negotiatedRate&filter=rootCustomerNeedsID,eq,' + id + '&satisfy=all&order[]=entityID&order[]=rootCustomerNeedsID&order[]=availableDate,desc&transform=1';
        
        if ( ! $.fn.DataTable.isDataTable( '#customer-needs-commit-table' ) ) {
            
            var example_table = $('#customer-needs-commit-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                //dataSrc: 'customer_needs'
                dataSrc: function ( json ) {
                    
                    var customer_needs = json.customer_needs;
                    var customer_needs_commit = new Array();
                    
                    customer_needs.forEach(function(customer_need){
                        
                        if(customer_need.customer_needs_commit.length > 0){
                            customer_needs_commit.push(customer_need);
                        }
                    });
                    //console.log(customer_needs_commit);
                    return customer_needs_commit;
                }
            },
            columns: [
                { data: "entities[0].name", visible: true },
                { data: "id", visible: false },
                { data: "rootCustomerNeedsID", visible: false},
                { data: "entityID", visible: false },
                { data: "qty" },
                { data: "customer_needs_commit[0].pickupDate" },
                { data: "customer_needs_commit[0].deliveryDate" },
                { data: "transportationMode" },
                {                     
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var input = '';
                        var status = o.customer_needs_commit[0].status;
                        var carrierRate = o.customer_needs_commit[0].rate.toFixed(2);
                        
                        if(status == "Available"){
                            input += "<input type=\"text\" name=\"carrierRate\" class=\"form-control mb-sm\" placeholder=\"Carrier Rate\" value=\"" + carrierRate + "\"/>";
                        }
                        else{
                            input += "<input type=\"text\" name=\"carrierRate\" class=\"form-control mb-sm\" placeholder=\"Carrier Rate\" value=\"" + carrierRate + "\" readonly/>";
                        }
                                                
                        return input;
                    }, visible: true
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
                { data: "distance", render: $.fn.dataTable.render.number(',', '.', 0, '')  },
                { data: "needsDataPoints", visible: false },
                {
                    data: null,
                    "bSortable": false,
                    "render": function(o) {
                      var newStatus = o.status;
                      if (o.customer_needs_commit.length > 0) {
                          var showAmount = o.customer_needs_commit[0].rate.toString().split(".");
                          showAmount[0] = showAmount[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                          if (showAmount.length > 1) {
                              if (showAmount[1].length < 2) {
                                  showAmount[1] = showAmount[1] + '0';
                              }
                              showAmount = "$" + showAmount[0] + "." + showAmount[1];
                          } else {
                              showAmount = "$" + showAmount[0] + ".00";
                          }
                          if (o.customer_needs_commit[0].status == "Cancelled") {
                              newStatus = "<strong>Cancelled</strong>";
                          } else {
                              newStatus = "<strong>Committed</strong>";
                          }
                      }
                      return newStatus;
                    }, visible: false
                },
                { data: "customer_needs_commit[0].id", visible: false },
                { data: "customer_needs_commit[0].status", visible: false },
                //{ data: "customer_needs_commit[0].rate", visible: false },
                
                { data: "customer_needs_commit[0].transportation_mode", visible: false },
                { data: "entities[0].name", visible: false },
                { data: "entities[0].rateType", visible: false },
                { data: "entities[0].negotiatedRate", visible: false},
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var buttons = '';
                        
                        buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"fa fa-check text-info\"></i> <span class=\"text-info\">Accept Commit</span></button>';

                        return buttons;
                    }
                }
            ],
            scrollX: true
          });

            //To Reload The Ajax
            //See DataTables.net for more information about the reload method
            example_table.ajax.reload(function(json){
                getCarrierTotal(json);
            });
        }
        else{
          //The URL will change with each "View Commit" button click
          // Must load new Url each time.
            var reload_table = $('#customer-needs-commit-table').DataTable();
            reload_table.ajax.url(url).load(function(json){
                getCarrierTotal(json);
            });
        }
        
        $("#customer-needs-commit").css("display", "block");
          
      }

      function getCarrierTotal(json){
      
            var customer_needs = json.customer_needs;
            var customer_needs_commit = new Array();
            var carrierTotal = 0;

            customer_needs.forEach(function(customer_need){

                if(customer_need.customer_needs_commit.length > 0){
                    carrierTotal += customer_need.customer_needs_commit[0].rate;
                    //customer_needs_commit.push(customer_need);
                }
            });
            
        $("#carrierTotalRate").val(carrierTotal.toFixed(2));        
        getTotalRevenue();             
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
         <h4><span class="fw-semi-bold">Available Transport</span></h4>
         <div class="widget-controls">
             <a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
             <a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>
             <a data-widgster="close" title="Close" href="#"><i class="glyphicon glyphicon-remove"></i></a>
         </div>
     </header>
     <div class="widget-body">
         <br /><br />
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
                     <th>Transporation Mode</th>
                     <th>Name</th>
                     <th>Rate Type</th>
                     <th>Negotiated Rate</th>
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
 
<section class="widget"  id="customer-needs-commit" style="display: none;">
     <header>
         <h4><span class="fw-semi-bold">Committed Transport</span></h4>  
         <div class="widget-controls">
             <a data-widgster="close" title="Close" href="Javascript:closeCommitTransport()"><i class="glyphicon glyphicon-remove"></i></a>
         </div>
     </header>
     <div class="widget-body">
         
        <div class="row">
            
        </div>
         <div id="dataTable-2" class="mt">
             <table id="customer-needs-commit-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th>Company</th>
                     <th>ID</th>
                     <th>Root Customer Needs ID</th>
                     <th>Entity ID</th>
                     <th>Qty</th>
                     <th>Pick Up</th>
                     <th>Delivery</th>
                     <th>Transport Mode</th>
                     <th>Commit Rate</th>
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
                     <th>Transportation Mode</th>
                     <th>Name</th>
                     <th>Rate Type</th>
                     <th>Negotiated Rate</th>
                     <th class="no-sort pull-right"></th>
                 </tr>
                 </thead>
                 <tbody>
                      <!-- loadTableAJAX() is what populates this area -->
                 </tbody>
             </table>
         </div>
                
        <div class="row">
            <div class="col-sm-3">
                <label for="customerRate">Customer Rate</label>
                <div class="form-group">
                  <input type="hidden" id="customerNeedsID" name="customerNeedsID" />
                  <input type="text" id="customerRate" name="customerRate" class="form-control mb-sm" placeholder="Customer Rate" />
                </div>
            </div>
            <div class="col-sm-3">
                <label for="carrierTotalRate">Carrier Total Rate</label>
                <div class="form-group">
                  <input type="text" id="carrierTotalRate" name="carrierTotalRate" class="form-control mb-sm" placeholder="Customer Rate" readonly/>
                </div>
            </div>
            <div class="col-sm-3">
                <label for="totalRevenue">Total Revenue</label>
                <div class="form-group">
                  <input type="text" id="totalRevenue" name="totalRevenue" class="form-control mb-sm" placeholder="Total Revenue" readonly/>
                </div>
            </div>
        </div>
     </div>
    
 </section>

 <script>

    loadTableAJAX();

    var table = $("#datatable-table").DataTable();
    $("#customer-needs-commit").css("display", "none");

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();

        var id = data["id"];
        var rate = data["rate"];
        
        $("#customerNeedsID").val(id);
        $("#customerRate").val(rate.toFixed(2));
            
        loadCustomerNeedsCommitAJAX(id);
               
    });

    function closeCommitTransport(){
        
        $("#customer-needs-commit").css("display", "none");
    }

    $('#customerRate').keyup(function () {
        getTotalRevenue();
    });

    $('#carrierTotalRate').on( 'change', function () {
        getTotalRevenue();        
    });
    
    function getTotalRevenue(){
        var customerRate = $("#customerRate").val();
        var carrierTotalRate = $("#carrierTotalRate").val();
        var totalRevenue = customerRate - carrierTotalRate;        
        $("#totalRevenue").val(totalRevenue.toFixed(2));
    }
    
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
