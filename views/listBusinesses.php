<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

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

                if (type == "PUT") {
                    var date = today;
                    var data = {contactID: $("#contactID").val(), entityRating: $("#entityRating").val(), rateType: $("input[name='rateType']:checked").val(), negotiatedRate: $("#negotiatedRate").val(), towAwayRateMin: $("#towAwayRateMin").val(), towAwayRateMax: $("#towAwayRateMax").val(), towAwayRateType: $("input[name='towAwayRateType']:checked").val(), loadOutRateMin: $("#loadOutRateMin").val(), loadOutRateMax: $("#loadOutRateMax").val(), loadOutRateType: $("input[name='loadOutRateType']:checked").val(), configuration_settings: configurationSettings, updatedAt: date};
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
                        $("#towAwayRateType").val('');
                        $("#loadOutRateMin").val('');
                        $("#loadOutRateMax").val('');
                        $("#loadOutRateType").val('');
                        passValidation = true;
                      } else {
                        alert("Updating Business Information Failed!");
                      }
                   },
                   error: function() {
                      alert("There Was An Error Adding Business!");
                   }
                });

                return passValidation;

          } else {

                return false;

          }

      }

      function loadTableAJAX() {
        var url = '<?php echo API_HOST_URL; ?>' + '/entities?columns=id,entityTypeID,name,entityRating,contactID,status,rateType,negotiatedRate,towAwayRateMin,towAwayRateMax,towAwayRateType,loadOutRateMin,loadOutRateMax,loadOutRateType&filter[]=id,gt,0&order=name&transform=1';
        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                dataSrc: 'entities'
            },
            columns: [
                { data: "id", visible: false },
                { data: "entityTypeID", visible: false },
                { data: "name" },
                { data: "entityRating", visible: false },
                { data: "contactID", visible: false },
                { data: "rateType", visible: false },
                { data: "negotiatedRate", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
                { data: "towAwayRateMin", render: $.fn.dataTable.render.number(',', '.', 2, '$')},
                { data: "towAwayRateMax", render: $.fn.dataTable.render.number(',', '.', 2, '$')},
                { data: "towAwayRateType", visible: false},
                { data: "loadOutRateMin", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
                { data: "loadOutRateMax", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
                { data: "loadOutRateType", visible: false},
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
                  alert("Changing Status of Entity Failed!");
                }
             },
             error: function() {
                alert("There Was An Error Changing Entity Status!");
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
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover" width="100%">
                 <thead>
                 <tr>
                     <th>ID</th>
                     <th>Type ID</th>
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
         <h5 class="modal-title" id="exampleModalLabel"><strong>Business</strong></h5>
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

 <script>

    //var contacts = <?php echo $contacts; ?>;

    loadTableAJAX();

    var table = $("#datatable-table").DataTable();

    $("#addBusiness").click(function(){
        $("#id").val('');
        $("#name").val('');
  		$("#myModal").modal('show');
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
          $('input:radio[name="towAwayRateType"]').val([data["towAwayRateType"]]);
          $("#towAwayRateMin").val(data["towAwayRateMin"]);
          $("#towAwayRateMax").val(data["towAwayRateMax"]);
          $('input:radio[name="loadOutRateType"]').val([data["loadOutRateType"]]);
          $("#loadOutRateMin").val(data["loadOutRateMin"]);
          $("#loadOutRateMax").val(data["loadOutRateMax"]);
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

          $.ajax({
               url: '<?php echo API_HOST_URL . "/entities"; ?>/' + $("#id").val(),
               type: 'GET',
               contentType: "application/json",
               async: false,
               success: function(response){
                 var entityTypeID = response.entityTypeID;
                 var cs = JSON.parse(response.configuration_settings);
                 var li = '';
                 var dpli = '';
                 for (var i = 0; i < cdpvList.length; i++) {
                     if ( entityTypeID == cdpvList[i].entityTypeID ) {
                            var selected = '';
                            var value = '';

                            $.each(cs, function(idx, obj) {
                              $.each(obj, function(key, val) {
                                if (cdpvList[i].columnName == key) {
                                    value = val; // Get the value from the JSON data in the record to use to set the selected option in the dropdown
                                }
                              })
                            });

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
                  alert('Failed Getting Configuration Settings!');
               }
          });

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
