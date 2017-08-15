<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

$state = '';
$states = json_decode(file_get_contents(API_HOST.'/api/states?columns=abbreviation,name&order=name'));

$entity = '';
$entity = json_decode(file_get_contents(API_HOST.'/api/entities?columns=rateType,negotiatedRate&filter[]=id,eq,' . $_SESSION['entityid']));

$entities = '';
$entities = json_decode(file_get_contents(API_HOST.'/api/entities?columns=id,name&order=name&filter[]=id,gt,0&filter[]=entityTypeID,eq,2'));

$allEntities = '';
$allEntities = json_decode(file_get_contents(API_HOST.'/api/entities?columns=id,name&order=name&filter[]=id,gt,0&transform=1'));


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
    
    var allEntities = <?php echo json_encode($allEntities); ?>;
    console.log(allEntities);
    
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

      function post() {

          //var originationaddress = $("#originationAddress1").val() + ', ' + $("#originationCity").val() + ', ' + $("#originationState").val() + ', ' + $("#originationZip").val();
          //var destinationaddress = $("#destinationAddress1").val() + ', ' + $("#destinationCity").val() + ', ' + $("#destinationState").val() + ', ' + $("#destinationZip").val();
          var originationaddress = $("#originationCity").val() + ', ' + $("#originationState").val();
          var destinationaddress = $("#destinationCity").val() + ', ' + $("#destinationState").val();

          if (originationaddress != $("#originToMatch").val() && destinationaddress != $("#destToMatch").val()) {
              alert("The commitment for this Available request must be picked up or dropped off at the listed Origination or Destination. Please select a new Origination or Destination address.");
              //alert($("#originToMatch").val());
              //alert($("#destToMatch").val());
              return false;
          }


          if (confirm("You have selected to Commit to this Availability. A Nationwide Equipment Control team member will contact you within 4 buisness hours to start the order process. Do you wish to proceed with this commitment?") == true) {

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
                      alert(data);
                      $("#load").html("Commit");
                      $("#load").prop("disabled", false);
                    });
                    return true;
                } else {
                    return false;
                }

          } else {

                $("#myModalCommit").modal('hide');

          }
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
                                         $("#myModalCommit").modal('hide');
                                     },
                                     error: function() {
                                        alert('Failed creating a new Need from an existing.');
                                        $("#myModalCommit").modal('hide');
                                     }
                                  });
                              //}

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
                              passValidation = true;

                });

            } else {

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
                        var status = o.status;
                        
                        if(status == "Available"){                            
                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"fa fa-thumbs-up text-info\"></i> <span class=\"text-info\">View Commits</span></button>';

                        }
                        else{
                            buttons += "Order Completed!" ;
                        }
                        
                        return buttons;
                    }
                }
                
            ],
            scrollX: true
          });

          example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );
          
        //To Reload The Ajax
        //See DataTables.net for more information about the reload method
        example_table.ajax.reload();

      }

    function loadCustomerNeedsCommitAJAX (id){
                 
        var url = '<?php echo API_HOST; ?>' + '/api/customer_needs?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,availableDate,expirationDate,transportationMode,rate,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,customer_needs_commit.id,customer_needs_commit.entityID,customer_needs_commit.status,customer_needs_commit.pickupDate,customer_needs_commit.deliveryDate,customer_needs_commit.rate,customer_needs_commit.transporation_mode,entities.name,entities.rateType,entities.negotiatedRate&filter=rootCustomerNeedsID,eq,' + id + '&satisfy=all&order[]=entityID&order[]=rootCustomerNeedsID&order[]=availableDate,desc&transform=1';
        
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
                    }, visible: true
                },
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
                        var commitID = o.customer_needs_commit[0].id;
                        
                        if(status == "Available"){
                            input += "<input id=\"carrierRate-" + commitID + "\" type=\"text\" name=\"carrierRate\" class=\"form-control mb-sm\" placeholder=\"Carrier Rate\" value=\"" + carrierRate + "\"/>";
                        }
                        else{
                            input += "<input id=\"carrierRate-" + commitID + "\" type=\"text\" name=\"carrierRate\" class=\"form-control mb-sm\" placeholder=\"Carrier Rate\" value=\"" + carrierRate + "\" readonly/>";
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
                        var status = o.customer_needs_commit[0].status;                        
                        
                        if(status == "Available"){
                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"fa fa-check text-info\"></i> <span class=\"text-info\">Accept Commit</span></button>';
                        }
                        else{
                            buttons += "Already Approved!" ;
                        }

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
        $("#customer-needs").css("display", "none");
          
      }

    function loadSelectedCustomer(id){
        
        var baseUrl = '<?php echo API_HOST; ?>' + '/api/customer_needs?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,rate,availableDate,expirationDate,transportationMode,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,customer_needs_commit.id,customer_needs_commit.status,customer_needs_commit.rate,customer_needs_commit.transporation_mode,entities.name,entities.rateType,entities.negotiatedRate';
                  
        baseUrl = baseUrl + "&filter[]=id,eq," + id;
                
        var url = baseUrl + '&satisfy=any&order[]=entityID&order[]=rootCustomerNeedsID&order[]=availableDate,desc&transform=1';
        
        if ( ! $.fn.DataTable.isDataTable( '#selected-customer-need' ) ) {
            
            var example_table = $('#selected-customer-need').DataTable({
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
                    "defaultContent": '',
                    visible: false
                },
                { data: "entities[0].name", visible: true },
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
                        
                        buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"fa fa-thumbs-up text-info\"></i> <span class=\"text-info\">View Commits</span></button>';

                        return buttons;
                    }, visible: false
                }
                
            ]
          });

            example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );
            
            //To Reload The Ajax
            //See DataTables.net for more information about the reload method
            example_table.ajax.reload();
        }
        else{
          //The URL will change with each "View Commit" button click
          // Must load new Url each time.
            var reload_table = $('#selected-customer-need').DataTable();
            reload_table.ajax.url(url).load();
        }
    }
    
    function getCarrierTotal(json){
      
            var customer_needs = json.customer_needs;
            var customer_needs_commit = new Array();
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

        var url = '<?php echo API_HOST."/api/customer_needs_commit" ?>/' + commitID;
        type = "PUT";
        var date = today;
        var data = {rate: carrierRate, status: "Close", updatedAt: date};
        //var data = {rate: carrierRate, updatedAt: date};

        //console.log("CommitID:", commitID);

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
                          alert(notification);
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
         <!--<div class="widget-controls">
             <a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
             <a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>
             <a data-widgster="close" title="Close" href="#"><i class="glyphicon glyphicon-remove"></i></a>
         </div>-->
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
    <br>
    <br>
     <div class="widget-body">
         
        <div class="mt">
            <h5><span class="fw-semi-bold">Selected Customer Transport</span></h5>
            <table id="selected-customer-need" class="table table-striped table-hover">
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
        
    <br>
    
         <div id="dataTable-2" class="mt">
            <h5><span class="fw-semi-bold">Carrier Committed Transport</span></h5>
            <table id="customer-needs-commit-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th>Carrier Name</th>
                     <th>ID</th>
                     <th>Root Customer Needs ID</th>
                     <th>Entity ID</th>
                     <th>Qty</th>
                     <th>Pick Up</th>
                     <th>Delivery</th>
                     <th>Transport Mode</th>
                     <th>Carrier Rate</th>
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
            <div class="col-sm-4">
                <a data-widgster="addCommit" title="Add" href="Javascript:addNewCommit();"><i class="fa fa-plus-square-o"></i> Add Carrier Commitment</a>
            </div>
        </div>
                
    <br>
    
        <div class="row">
            <div class="col-sm-4">
                <label for="customerRate">Customer Rate</label>
                <div class="form-group">
                  <input type="hidden" id="customerNeedsID" name="customerNeedsID" />
                  <input type="hidden" id="entityID" name="entityID" />
                  <input type="text" id="customerRate" name="customerRate" class="form-control mb-sm" placeholder="Customer Rate" />
                </div>
            </div>         
            <div class="col-sm-4 col-sm-offset-4">
                <label for="filePurchaseOrder">Upload the Customer's purchase order</label>
                <div class="form-group">
                  <input type="file" id="filePurchaseOrder" name="filePurchaseOrder" class="form-control-file mb-sm"/>
                </div>
            </div>
        </div>
         
        <div class="row">
            <div class="col-sm-4">
                <label for="carrierTotalRate">Carrier Total Rate</label>
                <div class="form-group">
                  <input type="text" id="carrierTotalRate" name="carrierTotalRate" class="form-control mb-sm" placeholder="Customer Rate" readonly/>
                </div>
            </div>   
            <div class="col-sm-4 col-sm-offset-4">
                <div class="form-group">
                    <button id="completeOrder" class="btn btn-primary btn-block" role="button" onclick="completeOrder();"><i class="fa fa-check-square-o text-info"></i> <span class="text-info">Complete Order</span></button>
                </div>
            </div>
        </div>
         <div class="row"> 
            <div class="col-sm-4">
                <label for="totalRevenue">Total Revenue</label>
                <div class="form-group">
                  <input type="text" id="totalRevenue" name="totalRevenue" class="form-control mb-sm" placeholder="Total Revenue" readonly/>
                </div>
            </div>             
         </div>
        </div>
     </div>
    
 </section>
 
  <!-- Modal -->
  <div class="modal fade" id="myModalCommit" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Add Carrier Commmit</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
                <form id="formNeed" class="register-form mt-lg">
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

 
 <script>

    loadTableAJAX();

    $('.datepicker').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });

    function addNewCommit(){  
        
        var selectedTable = $("#selected-customer-need").DataTable();
        var json = selectedTable.ajax.json()
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
            } else if (data['transportationMode'] == "Both (Empty or Load Out)"){
                either = "selected=selected";
            }
            transportationmodeselect += '<option value="Empty" '+empty+'>Empty</option>\n';
            transportationmodeselect += '<option value="Load Out" '+loadout+'>Load Out</option>\n';
            transportationmodeselect += '<option value="Both (Empty or Load Out)" '+either+'>Both (Empty or Load Out)</option>\n';
        }

        transportationmodeselect += '</select>\n';
        $("#transportationModeDiv").html(transportationmodeselect);

        $("#entityID").prop('disabled', true);
        $("#myModalCommit").modal('show');
    }

    function completeOrder(){
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
        console.log(fileData);
        
        var selectedTable = $('#selected-customer-need').DataTable();
        var selectedCustomerNeed = selectedTable.ajax.json().customer_needs[0];
        
        console.log(selectedCustomerNeed);
        
        var commitTable = $('#customer-needs-commit-table').DataTable();
        var customer_needs = commitTable.ajax.json().customer_needs;
        var carrierIDs = new Array();
                    
        customer_needs.forEach(function(customer_need){

            if(customer_need.customer_needs_commit.length > 0 && customer_need.customer_needs_commit[0].status == "Close"){
                carrierIDs.push({carrierID: customer_need.customer_needs_commit[0].entityID});
            }
        });
                            
        if(fileData != undefined){
            $.ajax({
                url: url,
                type: type,
                data: formData,
                processData: false,  // tell jQuery not to process the data
                contentType: false,  // tell jQuery not to set contentType
                success: function(response){
                    //alert('Purchase Order Uploaded.');
                    var documentID = parseInt(response);    // Returned is the uploaded document's ID number.
                    
                    var url = '<?php echo API_HOST ?>' + '/api/orders/';
                    var orderData = {customerID: $("#entityID").val(), carrierIDs: carrierIDs, documentID: documentID, orderID: orderID,
                            originationAddress: selectedCustomerNeed.originationAddress1, originationCity: selectedCustomerNeed.originationCity,
                            originationState: selectedCustomerNeed.originationState, originationZip: selectedCustomerNeed.originationZip,
                            originationLng: selectedCustomerNeed.originationLng, originationLat: selectedCustomerNeed.originationLat,
                            destinationAddress: selectedCustomerNeed.destinationAddress1, destinationCity: selectedCustomerNeed.destinationCity,
                            destinationState: selectedCustomerNeed.destinationState, destinationZip: selectedCustomerNeed.destinationZip,
                            destinationLng: selectedCustomerNeed.destinationLng, destinationLat: selectedCustomerNeed.destinationLat,
                            distance: selectedCustomerNeed.distance, needsDataPoints: selectedCustomerNeed.needsDataPoints,
                            status: "Open", transportationMode: selectedCustomerNeed.transportationMode, qty: selectedCustomerNeed.qty,
                            rateType: selectedCustomerNeed.rateType, customerRate: $('#customerRate').val(), carrierTotalRate: $('#carrierTotalRate').val(),
                            totalRevenue: $('#totalRevenue').val(), createdAt: today, updatedAt: today};
                        
                    $.ajax({
                        url: url,
                        type: type,
                        data: JSON.stringify(orderData),
                        contentType: "application/json",
                        async: false,
                        success: function(){
                            alert("Purchase Order Completed.");
                            
                            $.ajax({
                                url: '<?php echo API_HOST ?>' + '/api/customer_needs/' + selectedCustomerNeed.id,
                                type: "PUT",
                                data: JSON.stringify({status: "Closed"}),
                                contentType: "application/json",
                                async: false,
                                success: function(){
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
            alert('You must Upload the Customer\'s Purchase Order to Complete the Order.');
        }
    }

    var table = $("#datatable-table").DataTable();
    $("#customer-needs-commit").css("display", "none");

    $('#customer-needs-commit-table tbody').on( 'click', 'button', function () {
        var commitTable = $("#customer-needs-commit-table").DataTable();
        
        var data = commitTable.row( $(this).parents('tr') ).data();

        var rootCustomerNeedsID = data["rootCustomerNeedsID"];
        var commitID = data.customer_needs_commit[0].id; 
        //var entityID = data.customer_needs_commit[0].entityID; 
        var carrierRate = $("#carrierRate-" + commitID).val();
        
        approveCommit(rootCustomerNeedsID, commitID, carrierRate);
    });

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();

        var id = data["id"];
        var rate = data["rate"];
        var entityID = data["entityID"];
        
        $("#customerNeedsID").val(id);
        $("#entityID").val(entityID);
        $("#customerRate").val(rate.toFixed(2));
            
        loadSelectedCustomer(id)
        loadCustomerNeedsCommitAJAX(id);
    });

    function closeCommitTransport(){
        
        $("#customer-needs-commit").css("display", "none");
        $("#customer-needs").css("display", "block");
        table.ajax.reload();
    }

    $('#customerRate').keyup(function () {
        
        $.ajax({
            url: '<?php echo API_HOST ?>' + '/api/customer_needs/' + $("#customerNeedsID").val(),
            type: "PUT",
            data: JSON.stringify({rate: $("#customerRate").val()}),
            contentType: "application/json",
            async: false,
            success: function(){
                getTotalRevenue();
            },
            error: function(){}
        });
        
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
