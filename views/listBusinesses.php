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

$contacts = file_get_contents(API_HOST_URL . '/contacts?columns=id,firstName,lastName,title&filter=entityID,eq,0&order=lastName&transform=1');

// Get Configuration Settings for Availability or Needs
$cdpvargs = array(
    "include"=>"configuration_data_point_values",
    "filter[]"=>"configuration_data_point_values.status,eq,Active",
    "transform"=>1
);

$cdpvurl = API_HOST_URL . "/configuration_data_points?".http_build_query($cdpvargs);
$cdpvoptions = array(
  'http' => array(
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
      'method'  => 'GET'
  )
);
$cdpvcontext  = stream_context_create($cdpvoptions);
$cdpvresult = json_decode(file_get_contents($cdpvurl,false,$cdpvcontext),true);

$cdpvList = $cdpvresult["configuration_data_points"];

 ?>

 <script>

     var contacts = <?php echo $contacts; ?>;
     var cdpvList = <?php echo json_encode($cdpvList); ?>;

/*
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
*/
      function verifyAndPost() {

        if ( $('#formBusiness').parsley().validate() ) {
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

                if ($("#id").val() > '') {
                    var url = '<?php echo API_HOST_URL . "/entities" ?>/' + $("#id").val();
                    type = "PUT";
                } else {
                    var url = '<?php echo API_HOST_URL . "/entities" ?>';
                    type = "POST";
                }

                // Build the configuration_settings
                var configurationSettings = [];
                var obj = $("#dp-check-list-box li select");
                for (var i = 0; i < obj.length; i++) {
                    item = {};
                    item[obj[i].id] = obj[i].value;
                    configurationSettings.push(item);
                }

                 var towAwayRateType = "";
                if ($('input[name="towAwayRateType"]:checked').val() == "Flat Rate") {
                    towAwayRateType = "Flat Rate";
                } else {
                    towAwayRateType = "Mileage";
                }

                var loadOutRateType = "";
                if ($('input[name="loadOutRateType"]:checked').val() == "Flat Rate") {
                    loadOutRateType = "Flat Rate";
                } else {
                    loadOutRateType = "Mileage";
                }

                if (type == "PUT") {
                    var date = today;
                    var data = {contactID: $("#contactID").val(), entityRating: $("#entityRating").val(), rateType: $("input[name='rateType']:checked").val(), negotiatedRate: $("#negotiatedRate").val(), towAwayRateMin: $("#towAwayRateMin").val(), towAwayRateMax: $("#towAwayRateMax").val(), towAwayRateType: towAwayRateType, loadOutRateMin: $("#loadOutRateMin").val(), loadOutRateMax: $("#loadOutRateMax").val(), loadOutRateType: loadOutRateType, configuration_settings: configurationSettings, updatedAt: date};
                } else {
                    // Should never do this at this point
                    //var date = today;
                    //var data = {entityID: $("#entityID").val(), name: $("#name").val(), contactName: $("#contactName").val(), contactPhone: $("#contactPhone").val(), policyNumber: $("#policyNumber").val(), policyExpirationDate: $("#policyExpirationDate").val(), createdAt: date};
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
                        $("#entityTypeID").val();
                        $("#name").val('');
                        $("#contactID").val('');
                        $("#entityRating").val('');
                        $("#rateType").val('');
                        $("#negotiatedRate").val('');
                        $("#towAwayRateMin").val('');
                        $("#towAwayRateMax").val('');
                        //$("#towAwayRateType").val('');
                        $("#loadOutRateMin").val('');
                        $("#loadOutRateMax").val('');
                        //$("#loadOutRateType").val('');
                        passValidation = true;
                      } else {
                        //alert("Updating Business Information Failed!");
                        Messenger().post({
                            message: "Updating Business Information Failed!",
                            type: 'error',
                            showCloseButton: true
                        });
                      }
                   },
                   error: function() {
                      //alert("There Was An Error Adding Business!");
                      Messenger().post({
                            message: "There Was An Error Adding Business!",
                            type: 'error',
                            showCloseButton: true
                        });
                   }
                });

                return passValidation;

          } else {

                return false;

          }

      }

      function verifyAndAdd() {

        if ( $('#formAddBusiness').parsley().validate() ) {
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

                var url = '<?php echo HTTP_HOST . "/addentity" ?>';
                type = "POST";

                // Build the configuration_settings
                var configurationSettings = [];
                var obj = $("#dp-check-list-box-add li select");
                for (var i = 0; i < obj.length; i++) {
                    item = {};
                    item[obj[i].id] = obj[i].value;
                    configurationSettings.push(item);
                }

                var date = today;
                var data = {entityTypeID: $('input[name="addBusinessTypeID"]:checked').val(),
                            firstName: $("#addFirstName").val(),
                            lastName: $("#addLastName").val(),
                            city: $("#addCity").val(),
                            address1: $("#addAddress1").val(),
                            address2: $("#addAddress2").val(),
                            state: $("#addState").val(),
                            zip: $("#addZip").val(),
                            phone: $("#addPhone").val(),
                            phoneExt: $("#addPhoneExt").val(),
                            email: $("#addEmail").val(),
                            title: $("#addTitle").val(),
                            entityName: $("#addEntityName").val(),
                            fax: $("#addFax").val(),
                            contactID: $("#addContactID").val(),
                            negotiatedRate: $("#addNegotiatedRate").val(),
                            rateType: $("input[name='addRateType']:checked").val(),
                            towAwayRateMin: $("#addTowAwayRateMin").val(),
                            towAwayRateMax: $("#addTowAwayRateMax").val(),
                            towAwayRateType: $('input[name="addTowAwayRateType"]:checked').val(),
                            loadOutRateMin: $("#addLoadOutRateMin").val(),
                            loadOutRateMax: $("#addLoadOutRateMax").val(),
                            loadOutRateType: $('input[name="addLoadOutRateType"]:checked').val(),
                            configuration_settings: configurationSettings,
                            createdAt: date,
                            updatedAt: date};

                $.ajax({
                   url: url,
                   type: type,
                   data: JSON.stringify(data),
                   contentType: "application/json",
                   async: false,
                   success: function(data){
                      if (data > 0) {
                        $("#myAddModal").modal('hide');
                        loadTableAJAX();
                        $("#id").val('');
                        $("#entityTypeID").val('');
                        $("#addFirstName").val(''),
                        $("#addLastName").val(''),
                        $("#addCity").val(''),
                        $("#addAddress1").val(''),
                        $("#addAddress2").val(''),
                        $("#addState").val(''),
                        $("#addZip").val(''),
                        $("#addPhone").val(''),
                        $("#addPhoneExt").val(''),
                        $("#addEmail").val(''),
                        $("#addTitle").val(''),
                        $("#addEntityName").val(''),
                        $("#addFax").val(''),
                        $("#addContactID").val('');
                        $("#addEntityRating").val('');
                        $("#addRateType").val('');
                        $("#addNegotiatedRate").val('');
                        $("#addTowAwayRateMin").val('');
                        $("#addTowAwayRateMax").val('');
                        $("#addLoadOutRateMin").val('');
                        $("#addLoadOutRateMax").val('');
                        passValidation = true;
                      } else {
                        //alert("Posting Business Information Failed! \n\n" + data);
                        Messenger().post({
                            message: "Posting Business Information Failed! \n\n" + JSON.parse(data),
                            type: 'error',
                            showCloseButton: true
                        });
                      }
                   },
                   error: function(error) {
                      //alert("There Was An Error Adding Business! \n\n" + error);
                      Messenger().post({
                            message: "There Was An Error Adding Business! \n\n" + error,
                            type: 'error',
                            showCloseButton: true
                        });
                   }
                });

                return passValidation;

          } else {

                return false;

          }

      }

      function loadTableAJAX() {
        var url = '<?php echo API_HOST_URL; ?>' + '/entities?include=locations&columns=id,entityTypeID,name,entityRating,contactID,status,rateType,negotiatedRate,towAwayRateMin,towAwayRateMax,towAwayRateType,loadOutRateMin,loadOutRateMax,loadOutRateType,locations.name,locations.city,locations.state,locations.zip&filter[]=id,gt,0&filter[]=locations.locationTypeID,eq,1&order=name&transform=1';
        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            bSort: true,
            "pageLength": 50,
            ajax: {
                url: url,
                dataSrc: 'entities'
            },
            columns: [
                { data: "id", visible: false },
                //{ data: "entityTypeID", visible: false },
                {
                  data: null,
                  "mRender": function(o) {
                      if (o.entityTypeID == 1) {
                          return "Customer";
                      } else {
                          return "Carrier";
                      }
                  }
                },
                { data: "name" },
                { data: "entityRating", visible: false },
                { data: "contactID", visible: false },
                { data: "rateType", visible: false },
                { data: "negotiatedRate", render: $.fn.dataTable.render.number(',', '.', 2, '$'), visible: false },
                { data: "towAwayRateMin", render: $.fn.dataTable.render.number(',', '.', 2, '$'), visible: false},
                { data: "towAwayRateMax", render: $.fn.dataTable.render.number(',', '.', 2, '$'), visible: false},
                { data: "towAwayRateType", visible: false},
                { data: "loadOutRateMin", render: $.fn.dataTable.render.number(',', '.', 2, '$'), visible: false },
                { data: "loadOutRateMax", render: $.fn.dataTable.render.number(',', '.', 2, '$'), visible: false },
                { data: "loadOutRateType", visible: false},
                { data: "locations[0].name", visible: false },
                { data: "locations[0].city" },
                { data: "locations[0].state" },
                { data: "locations[0].zip" },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var buttons = '<div class="pull-right text-nowrap">';
                        buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-edit text\"></i> <span class=\"text\">Edit</span></button>';
/*
                        if (o.status == "Active") {
                                  buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-remove text\"></i> <span class=\"text\">Disable</span></button>";
                        } else {
                                  buttons += " &nbsp;<button class=\"btn btn-danger btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-exclamation-sign text\"></i> <span class=\"text\">Enable</span></button>";
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
          var url = '<?php echo API_HOST_URL . "/entities" ?>/' + $("#id").val();
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
                  //alert("Changing Status of Entity Failed!");
                  Messenger().post({
                        message: "Changing Status of Entity Failed!",
                        type: 'error',
                        showCloseButton: true
                    });
                }
             },
             error: function() {
                //alert("There Was An Error Changing Entity Status!");
                Messenger().post({
                    message: "There Was An Error Changing Entity Status!",
                    type: 'error',
                    showCloseButton: true
                });
             }
          });

          //return passValidation;
      }

 </script>

 <style>

    .text-nowrap {
        white-space: nowrap;
    }

</style>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Business Maintenance</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Business</span></h4>
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
         <!--button type="button" id="addBusiness" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Business</button-->
         <div class="pull-right text-nowrap">
            <button type="button" id="addBusiness" class="btn btn-primary" data-target="#myModal">Add New Business</button>
         </div>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover" width="100%">
                 <thead>
                 <tr>
                     <th>ID</th>
                     <th>Business Type</th>
                     <th class="hidden-sm-down text-nowrap">Business</th>
                     <th class="no-sort hidden-sm-down">Rating</th>
                     <th class="no-sort hidden-sm-down">Contact</th>
                     <th class="no-sort hidden-sm-down">Rate Type</th>
                     <th class="no-sort hidden-sm-down">Negotiated Rate</th>
                     <th class="no-sort hidden-sm-down">Tow Away Min</th>
                     <th class="no-sort hidden-sm-down">Tow Away Max</th>
                     <th class="no-sort hidden-sm-down">Tow Away Rate Type</th>
                     <th class="no-sort hidden-sm-down">Load Out Min</th>
                     <th class="no-sort hidden-sm-down">Load Out Rate Max</th>
                     <th class="no-sort hidden-sm-down">Load Out Rate Type</th>
                     <th>Location Name</th>
                     <th class="hidden-sm-down text-nowrap">City</th>
                     <th class="hidden-sm-down text-nowrap">State</th>
                     <th class="hidden-sm-down text-nowrap">Zip</th>
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
 <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="formTitle"><strong>Business</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
         <form id="formBusiness" class="register-form mt-lg">
           <input type="hidden" id="id" name="id" value="" />
           <div class="row">
               <div class="col-sm-4">
                   <label for="firstName">Name</label>
                   <div class="form-group form-control" id="name">
                   </div>
               </div>
               <div class="col-sm-4">
                   <label for="state">NEC Rep Contact</label>
                   <div class="form-group" id="contact-list-box">

                   </div>
               </div>
               <div class="col-sm-4">
                   <label for="zip">Rating</label>
                   <div class="form-group">
                     <input type="text" id="entityRating" name="entityRating" class="form-control mb-sm" placeholder="Rating" />
                   </div>
               </div>
           </div>
           <div class="row">
               <div class="col-sm-4">
                   <label for="negotiatedRate">NEC Negotiated Rate</label>
                   <div class="form-group">
                     <input type="text" id="negotiatedRate" name="negotiatedRate" class="form-control mb-sm" placeholder="Negotiated Rate" />
                   </div>
               </div>
               <div class="col-sm-4">
                   <label for="negotiatedRate">NEC Negotiated Rate Type</label>
                   <div class="form-group" style="align: middle">
                     <input type="radio" id="rateType" name="rateType" value="Flat Rate"> Flat Rate
                     &nbsp;&nbsp;
                     <input type="radio" id="rateType" name="rateType" value="Mileage"> Mileage
                   </div>
               </div>
               <div class="col-sm-4">
               </div>
           </div>
           <div class="row">
               <div class="col-sm-4">
                   <label for="negotiatedRate">Tow Away Rate Min</label>
                   <div class="form-group">
                     <input type="text" id="towAwayRateMin" name="towAwayRateMin" class="form-control mb-sm" placeholder="Tow Away Rate Min" />
                   </div>
               </div>
               <div class="col-sm-4">
                   <label for="negotiatedRate">Tow Away Rate Max</label>
                   <div class="form-group">
                     <input type="text" id="towAwayRateMax" name="towAwayRateMax" class="form-control mb-sm" placeholder="Tow Away Rate Max" />
                   </div>
               </div>
               <div class="col-sm-4">
                   <label for="negotiatedRate">Tow Away Rate Type</label>
                   <div class="form-group" style="align: middle">
                     <input type="radio" id="towAwayRateType" name="towAwayRateType" value="Flat Rate"> Flat Rate
                     &nbsp;&nbsp;
                     <input type="radio" id="towAwayRateType" name="towAwayRateType" value="Mileage"> Mileage
                   </div>
               </div>
           </div>
           <div class="row">
               <div class="col-sm-4">
                   <label for="negotiatedRate">Load Out Rate Min</label>
                   <div class="form-group">
                     <input type="text" id="loadOutRateMin" name="loadOutRateMin" class="form-control mb-sm" placeholder="Load Out Rate Min" />
                   </div>
               </div>
                <div class="col-sm-4">
                   <label for="negotiatedRate">Load Out Rate Max</label>
                   <div class="form-group">
                     <input type="text" id="loadOutRateMax" name="loadOutRateMax" class="form-control mb-sm" placeholder="Load Out Rate Max" />
                   </div>
               </div>
               <div class="col-sm-4">
                   <label for="negotiatedRate">Load Out Rate Type</label>
                   <div class="form-group" style="align: middle">
                     <input type="radio" id="loadOutRateType" name="loadOutRateType" value="Flat Rate"> Flat Rate
                     &nbsp;&nbsp;
                     <input type="radio" id="loadOutRateType" name="loadOutRateType" value="Mileage"> Mileage
                   </div>
               </div>
           </div>
           <hr />
           <div class="row">
                 <div class="container" style="margin-top:20px;">
                     <div class="row">
                       <div class="col-xs-6">
                            <h5 class="text-center"><strong>Configuration Settings</strong></h5>
                            <div class="well" style="max-height: 200px;overflow: auto;">
                                <ul id="dp-check-list-box" class="list-group">

                                </ul>
                            </div>
                        </div>
                        <div class="col-xs-6">
                             <h5 class="text-center" id="customerContactTitle"><strong></strong></h5>
                             <div class="well" style="max-height: 200px;overflow: auto;">
                                 <ul id="check-list-box" class="list-group checked-list-box">

                                 </ul>
                             </div>
                         </div>
                     </div>
                 </div>
           </div>
           <div class="modal-footer">
             <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
             <button type="button" class="btn btn-primary" onclick="return verifyAndPost();">Save Changes</button>
           </div>
         </form>
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
                            <h5>Do you wish to disable this business?</h5>
                          </div>
                      </div>

                  </div>
                 </form>
        </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Disable');">Disable Business</button>
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
                             <h5>Do you wish to enable this business?</h5>
                           </div>
                       </div>

                   </div>
                  </form>
         </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Enable');">Enable Business</button>
          </div>
        </div>
      </div>
    </div>

<!-- Modal -->
 <div class="modal fade" id="myAddModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="formTitle"><strong>Business</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
         <form id="formAddBusiness" class="register-form mt-lg">
           <input type="hidden" id="id" name="id" value="" />
           <input type="hidden" id="configuration_settings" name="configuration_settings" />
           <div class="row">
               <div class="col-sm-4">
                   <label for="negotiatedRate">Business Type</label>
                   <div class="form-group" style="align: middle">
                     <input type="radio" id="addBusinessTypeID" name="addBusinessTypeID" value="1"> Customer
                     &nbsp;&nbsp;
                     <input type="radio" id="addBusinessTypeID" name="addBusinessTypeID" value="2"> Carrier
                   </div>
               </div>
               <div class="col-sm-4">
                   <label for="rep">NEC Rep Contact</label>
                   <div class="form-group" id="contact-list-box-add">

                   </div>
               </div>
           </div>
           <div class="row">
                <div class="col-sm-4">
                    <label for="firstName">First Name</label>
                    <div class="form-group">
                      <input type="text" class="form-control" id="addFirstName" name="addFirstName" placeholder="*First Name" value=""
                              required="required" />
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="lastName">Last Name</label>
                    <div class="form-group">
                      <input type="text" id="addLastName" name="addLastName" class="form-control" placeholder="*Last Name" value=""
                             required="required" />
                    </div>
                </div>
                <div class="col-sm-4">
                  <label for="title">Title</label>
                  <div class="form-group">
                    <input type="text" id="addTitle" name="addTitle" class="form-control" placeholder="Title" value="" />
                  </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <label for="entityName">Company Name</label>
                    <div class="form-group">
                      <input type="text" id="addEntityName" name="addEntityName" class="form-control" placeholder="*Company Name" value=""
                             required="required" />
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="address1">Address 1</label>
                    <div class="form-group">
                      <input type="text" id="addAddress1" name="addAddress1" class="form-control mb-sm" placeholder="Company Address" value="" />
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="address2">Suite # / Apt #</label>
                    <div class="form-group">
                      <input type="text" id="addAddress2" name="addAddress2" class="form-control mb-sm" placeholder="Bldg. Number/Suite" value="" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <label for="city">City</label>
                    <div class="form-group">
                      <input type="text" id="addCity" name="addCity" class="form-control" placeholder="*City" value=""
                             required="required" />
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="state">State</label>
                    <div class="form-group">
                      <select id="addState" name="addState" data-placeholder="State" class="form-control chzn-select" data-ui-jq="select2" required="required">
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
                    <label for="zip">Zip</label>
                    <div class="form-group">
                      <input type="text" id="addZip" name="addZip" class="form-control mb-sm" placeholder="Zip" value="" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <label for="phone">Phone</label>
                    <div class="form-group">
                       <div class="col-sm-7" style="padding-left: 0; padding-right: 0">
                        <input type="text" id="addPhone" name="addPhone" class="form-control" placeholder="*Phone" value="" required="required" />
                       </div>
                       <div class="col-sm-5" style="padding-right: 0;">
                          <input type="text" maxlength="15" id="addPhoneExt" name="addPhoneExt" class="form-control" placeholder="Ext" value="" />
                       </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="fax">Fax</label>
                    <div class="form-group">
                        <input type="text" id="addFax" name="addFax" class="form-control" placeholder="Fax" value="" />
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="email">Email Address</label>
                    <div class="form-group">
                      <input type="addEmail" id="addEmail" name="addEmail" class="form-control" placeholder="*Email Address" value=""
                             data-parsley-trigger="change"
                             required="required" />
                    </div>
                </div>
           </div>
           <div class="row">
               <div class="col-sm-4">
                   <label for="negotiatedRate">NEC Negotiated Rate</label>
                   <div class="form-group">
                     <input type="text" id="addNegotiatedRate" name="addNegotiatedRate" class="form-control mb-sm" placeholder="Negotiated Rate" />
                   </div>
               </div>
               <div class="col-sm-4">
                   <label for="negotiatedRate">NEC Negotiated Rate Type</label>
                   <div class="form-group" style="align: middle">
                     <input type="radio" id="addRateType" name="addRateType" value="Flat Rate" checked> Flat Rate
                     &nbsp;&nbsp;
                     <input type="radio" id="addRateType" name="addRateType" value="Mileage"> Mileage
                   </div>
               </div>
               <div class="col-sm-4">
               </div>
           </div>
           <div class="row">
               <div class="col-sm-4">
                   <label for="negotiatedRate">Tow Away Rate Min</label>
                   <div class="form-group">
                     <input type="text" id="addTowAwayRateMin" name="addTowAwayRateMin" class="form-control mb-sm" placeholder="Tow Away Rate Min" />
                   </div>
               </div>
               <div class="col-sm-4">
                   <label for="negotiatedRate">Tow Away Rate Max</label>
                   <div class="form-group">
                     <input type="text" id="addTowAwayRateMax" name="addTowAwayRateMax" class="form-control mb-sm" placeholder="Tow Away Rate Max" />
                   </div>
               </div>
               <div class="col-sm-4">
                   <label for="negotiatedRate">Tow Away Rate Type</label>
                   <div class="form-group" style="align: middle">
                     <input type="radio" id="addTowAwayRateType" name="addTowAwayRateType" value="Flat Rate" checked> Flat Rate
                     &nbsp;&nbsp;
                     <input type="radio" id="addTowAwayRateType" name="addTowAwayRateType" value="Mileage"> Mileage
                   </div>
               </div>
           </div>
           <div class="row">
               <div class="col-sm-4">
                   <label for="negotiatedRate">Load Out Rate Min</label>
                   <div class="form-group">
                     <input type="text" id="addLoadOutRateMin" name="addLoadOutRateMin" class="form-control mb-sm" placeholder="Load Out Rate Min" />
                   </div>
               </div>
                <div class="col-sm-4">
                   <label for="negotiatedRate">Load Out Rate Max</label>
                   <div class="form-group">
                     <input type="text" id="addLoadOutRateMax" name="addLoadOutRateMax" class="form-control mb-sm" placeholder="Load Out Rate Max" />
                   </div>
               </div>
               <div class="col-sm-4">
                   <label for="negotiatedRate">Load Out Rate Type</label>
                   <div class="form-group" style="align: middle">
                     <input type="radio" id="addLoadOutRateType" name="addLoadOutRateType" value="Flat Rate" checked> Flat Rate
                     &nbsp;&nbsp;
                     <input type="radio" id="addLoadOutRateType" name="addLoadOutRateType" value="Mileage"> Mileage
                   </div>
               </div>
           </div>
           <hr />
           <div class="row">
                 <div class="container" style="margin-top:20px;">
                     <div class="row">
                       <div class="col-xs-6">
                            <h5 class="text-center"><strong>Configuration Settings</strong></h5>
                            <div class="well" style="max-height: 200px;overflow: auto;">
                                <ul id="dp-check-list-box-add" class="list-group">

                                </ul>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            &nbsp;
                        </div>
                     </div>
                 </div>
           </div>
           <div class="modal-footer">
             <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
             <button type="button" class="btn btn-primary" onclick="return verifyAndAdd();">Save Changes</button>
           </div>
         </form>
        </div>
      </div>
    </div>
  </div>

 <script>

    //var contacts = <?php echo $contacts; ?>;

    Messenger.options = {
        extraClasses: 'messenger-fixed messenger-on-top'
    }

    loadTableAJAX();

    var table = $("#datatable-table").DataTable();

    $("#addBusiness").click(function(){

        var contactdropdown = '<select id="addContactID" name="addContactID" data-placeholder="NEC Rep" class="form-control chzn-select" data-ui-jq="select2" required="required">';
        for (var i = 0; i < contacts.contacts.length; i++) {
             contactdropdown += '<option value="'+ contacts.contacts[i].id + '">' + contacts.contacts[i].firstName + ' ' + contacts.contacts[i].lastName + '</option>\n';
        }
        contactdropdown += '</select>\n';
        $("#contact-list-box-add").html(contactdropdown);

  		$("#myAddModal").modal('show');
  	});

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();

        var contactdropdown = '';
        var selected = '';
        if (this.textContent.indexOf("Edit") > -1) {
          $("#id").val(data["id"]);
          $("#name").html(data["name"]);
          $("#entityRating").val(data["entityRating"]);
          $('input[id="rateType"]').attr('checked', false);
          $('input:radio[name="rateType"]').val([data["rateType"]]);
          $("#negotiatedRate").val(data["negotiatedRate"]);

          $("#towAwayRateMin").val(data["towAwayRateMin"]);
          $("#towAwayRateMax").val(data["towAwayRateMax"]);

          $("#loadOutRateMin").val(data["loadOutRateMin"]);
          $("#loadOutRateMax").val(data["loadOutRateMax"]);

          //$('input:radio[name=towAwayRateType]').val([data["towAwayRateType"]]);
          if (data["towAwayRateType"] == "Flat Rate") {
              $('input:radio[name=towAwayRateType]').filter('[value="Flat Rate"]').prop('checked', true);
          } else {
              $('input:radio[name=towAwayRateType]').filter('[value="Mileage"]').prop('checked', true);
          }

          //$('input:radio[name=loadOutRateType]').val([data["loadOutRateType"]]);
          if (data["loadOutRateType"] == "Flat Rate") {
              $('input:radio[name=loadOutRateType]').filter('[value="Flat Rate"]').prop('checked', true);
          } else {
              $('input:radio[name=loadOutRateType]').filter('[value="Mileage"]').prop('checked', true);
          }

          contactdropdown += '<select id="contactID" name="contactID" data-placeholder="NEC Rep" class="form-control chzn-select" data-ui-jq="select2" required="required">';
          for (var i = 0; i < contacts.contacts.length; i++) {
              selected = (contacts.contacts[i].id == data["contactID"]) ? 'selected=selected':'';
              contactdropdown += '<option value="'+ contacts.contacts[i].id + '"' + selected + '>' + contacts.contacts[i].firstName + ' ' + contacts.contacts[i].lastName + '</option>\n';
          }
          contactdropdown += '</select>\n';
          $("#contact-list-box").html(contactdropdown);

          var params = {
              entityID: $("#id").val()
          };

          var entityTypeID = 0;

          $.ajax({
               url: '<?php echo API_HOST_URL . "/entities"; ?>/' + $("#id").val(),
               type: 'GET',
               contentType: "application/json",
               async: false,
               success: function(response){
                 entityTypeID = response.entityTypeID;
                 var cs = response.configuration_settings;
                 //console.log('length: ' + cs);
                 var li = '';
                 var dpli = '';
                 for (var i = 0; i < cdpvList.length; i++) {
                     if ( entityTypeID == cdpvList[i].entityTypeID ) {
                            var selected = '';
                            var value = '';
                            if (cs) {
                                $.each(cs, function(idx, obj) {
                                  $.each(obj, function(key, val) {
                                    if (cdpvList[i].columnName == key) {
                                        value = val; // Get the value from the JSON data in the record to use to set the selected option in the dropdown
                                    }
                                  })
                                });
                            }

                            dpli += '<li>' + cdpvList[i].title +
                                    ' <select class="form-control mb-sm" id="' + cdpvList[i].columnName + '" name="' + cdpvList[i].columnName + '">' +
                                    ' <option value="">-Select From List-</option>\n';

                            for (var v = 0; v < cdpvList[i].configuration_data_point_values.length; v++) {

                                if (cdpvList[i].configuration_data_point_values[v].value === value) {
                                    selected = ' selected ';
                                } else {
                                    selected = '';
                                }

                                dpli += '<option value="' + cdpvList[i].configuration_data_point_values[v].value + '" ' + selected + '>' + cdpvList[i].configuration_data_point_values[v].title + '</option>\n';

                            }

                            dpli += '</select>' +
                                  '</li>\n';
                     }

                 }
                 $("#dp-check-list-box").html(dpli);
               },
               error: function() {
                  //alert('Failed Getting Configuration Settings!');
                  Messenger().post({
                        message: 'Failed Getting Configuration Settings!',
                        type: 'error',
                        showCloseButton: true
                    });
               }
          });

          if (entityTypeID == 1) {
              $("#formTitle").html("<strong>Customer Maintenance</strong>");
          } else {
              $("#formTitle").html("<strong>Carrier Maintenance</strong>");
          }

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

    $('input[name="addBusinessTypeID"]').click(function() {
        $("#dp-check-list-box-add").html('');
        var li = '';
        var dpli = '';
        var entityTypeID = $('input[name="addBusinessTypeID"]:checked').val();
        for (var i = 0; i < cdpvList.length; i++) {
             if ( entityTypeID == cdpvList[i].entityTypeID ) {
                    var selected = '';
                    var value = '';

                    dpli += '<li>' + cdpvList[i].title +
                            ' <select class="form-control mb-sm" id="' + cdpvList[i].columnName + '" name="' + cdpvList[i].columnName + '">' +
                            ' <option value="">-Select From List-</option>\n';

                    for (var v = 0; v < cdpvList[i].configuration_data_point_values.length; v++) {
                        dpli += '<option value="' + cdpvList[i].configuration_data_point_values[v].value + '" ' + selected + '>' + cdpvList[i].configuration_data_point_values[v].title + '</option>\n';
                    }

                    dpli += '</select>' +
                          '</li>\n';
             }

         }
         $("#dp-check-list-box-add").html(dpli);
    });

 </script>
