<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';
require '../lib/quickbooksconfig.php';

define('OAUTH_REQUEST_URL', 'https://oauth.intuit.com/oauth/v1/get_request_token');
define('OAUTH_ACCESS_URL', 'https://oauth.intuit.com/oauth/v1/get_access_token');
define('OAUTH_AUTHORISE_URL', 'https://appcenter.intuit.com/Connect/Begin');
// The url to this page. it needs to be dynamic to handle runnable's dynamic urls
define('http://nec.dubtel.com/qb_api_status');
// cleans out the token variable if comming from
// connect to QuickBooks button
if ( isset($_GET['start'] ) ) {
  unset($_SESSION['token']);
}
 
try {
  $oauth = new OAuth( OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
  $oauth->enableDebug();
  $oauth->disableSSLChecks(); //To avoid the error: (Peer certificate cannot be authenticated with given CA certificates)
  if (!isset( $_GET['oauth_token'] ) && !isset($_SESSION['token']) ){
	// step 1: get request token from Intuit
    $request_token = $oauth->getRequestToken( OAUTH_REQUEST_URL, CALLBACK_URL );
		$_SESSION['secret'] = $request_token['oauth_token_secret'];
		// step 2: send user to intuit to authorize 
		header('Location: '. OAUTH_AUTHORISE_URL .'?oauth_token='.$request_token['oauth_token']);
	}
	
	if ( isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']) ){
		// step 3: request a access token from Intuit
    $oauth->setToken($_GET['oauth_token'], $_SESSION['secret']);
		$access_token = $oauth->getAccessToken( OAUTH_ACCESS_URL );
		
		$_SESSION['token'] = serialize( $access_token );
    $_SESSION['realmId'] = '193514475422329';  // realmId is legacy for customerId
    //$_SESSION['dataSource'] = $_REQUEST['dataSource'];
	
	 $token = $_SESSION['token'] ;
	 $realmId = $_SESSION['realmId'];
	 //$dataSource = $_SESSION['dataSource'];
	 $secret = $_SESSION['secret'] ;
	 
    // write JS to pup up to refresh parent and close popup
    echo '<script type="text/javascript">
            window.opener.location.href = window.opener.location.href;
            window.close();
          </script>';
  }
 
} catch(OAuthException $e) {
	echo "Got auth exception";
	echo '<pre>';
	print_r($e);
}
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
        
        var baseUrl = '<?php echo API_HOST; ?>' + '/api/customer_needs?include=customer_needs_commit,entities&columns=id,rootCustomerNeedsID,entityID,qty,rate,availableDate,expirationDate,transportationMode,originationAddress1,originationCity,originationState,originationZip,originationLat,originationLng,destinationAddress1,destinationCity,destinationState,destinationZip,destinationLat,destinationLng,distance,needsDataPoints,status,customer_needs_commit.id,customer_needs_commit.status,customer_needs_commit.rate,customer_needs_commit.transporation_mode,entities.name,entities.rateType,entities.negotiatedRate&filter[]=rootCustomerNeedsID,eq,0&filter[]=status,eq,Available';
             
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
                { data: "entities[0].name" },
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
                            buttons += "Commitment Complete!" ;
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
                //dataSrc: 'customer_needs',
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
   <li class="active">Quickbooks</li>
 </ol>
 <section id="customer-needs" class="widget">
     <header>
         <h4><span class="fw-semi-bold">Quickbooks Connection Status</span></h4>
         <!--<div class="widget-controls">
             <a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
             <a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>
             <a data-widgster="close" title="Close" href="#"><i class="glyphicon glyphicon-remove"></i></a>
         </div>-->
     </header>
     <div class="widget-body">
         <br /><br />
         
     </div>
 </section>
 

  <!-- Modal -->


