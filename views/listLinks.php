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

 ?>

 <script>

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

        if ( $('#formLink').parsley().validate() ) {
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
                    var url = '<?php echo API_HOST_URL . "/links" ?>/' + $("#id").val();
                    type = "PUT";
                } else {
                    var url = '<?php echo API_HOST_URL ."/links" ?>';
                    type = "POST";
                }

                if (type == "PUT") {
                    var date = today;
                    var data = {entityID: $("#entityID").val(), name: $("#name").val(), link: $("#link").val(), updatedAt: date};
                } else {
                    var date = today;
                    var data = {entityID: $("#entityID").val(), name: $("#name").val(), link: $("#link").val(), createdAt: date};
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
                        $("#name").val('');
                        $("#link").val('');
                        passValidation = true;
                      } else {
                        //alert("Adding Link Failed!");
                        Messenger().post({
                            message: "Adding Link Failed!",
                            type: 'error',
                            showCloseButton: true
                        });
                      }
                   },
                   error: function() {
                      //alert("There Was An Error Adding Link!");
                      Messenger().post({
                            message: "There Was An Error Adding Link!",
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
        myApp.showPleaseWait();
        var url = '<?php echo API_HOST_URL; ?>' + '/links?columns=id,name,link,status&filter=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&order=name&transform=1';
        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            bsort: true,
            "pageLength": 50,
            ajax: {
                url: url,
                dataSrc: 'links'
            },
            columns: [
                { data: "id", visible: false },
                { data: "name" },
                { data: "link" },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var buttons = '<div class="pull-right text-nowrap">';
                        buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-edit text\"></i> <span class=\"text\">Edit</span></button>';

                        if (o.status == "Active") {
                                  buttons += " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-remove text\"></i> <span class=\"text\">Disable</span></button>";
                        } else {
                                  buttons += " &nbsp;<button class=\"btn btn-danger btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-exclamation-sign text\"></i> <span class=\"text\">Enable</span></button>";
                        }
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
          var url = '<?php echo API_HOST_URL . "/links" ?>/' + $("#id").val();
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
                  //alert("Changing Status of Link Failed!");
                  Messenger().post({
                        message: "Changing Status of Link Failed!",
                        type: 'error',
                        showCloseButton: true
                  });
                }
             },
             error: function() {
                //alert("There Was An Error Changing Link Status!");
                Messenger().post({
                        message: "There Was An Error Changing Link Status!",
                        type: 'error',
                        showCloseButton: true
                  });
             }
          });

          //return passValidation;
      }

 </script>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Link Maintenance</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Link</span></h4>
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
         <button type="button" id="addLink" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Link</button>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover" width="100%">
                 <thead>
                 <tr>
                     <th>ID</th>
                     <th class="hidden-sm-down">Name</th>
                     <th class="hidden-sm-down">Link</th>
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
         <h5 class="modal-title" id="exampleModalLabel"><strong>Link</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formLink" class="register-form mt-lg">
                 <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
                 <input type="hidden" id="id" name="id" value="" />
                 <div class="row">
                     <div class="col-sm-6">
                         <label for="name">Link Name</label>
                         <div class="form-group">
                           <input type="text" id="name" name="name" class="form-control mb-sm" placeholder="*Name" required="required" />
                         </div>
                     </div>
                     <div class="col-sm-6">
                         <label for="link">Link</label>
                         <div class="form-group">
                           <input type="text" id="link" name="link" class="form-control mb-sm" placeholder="Link" required="required" />
                         </div>
                     </div>
                 </div>
                </form>
       </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="return verifyAndPost();">Save changes</button>
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
                            <h5>Do you wish to disable this link?</h5>
                          </div>
                      </div>

                  </div>
                 </form>
        </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Disable');">Disable Link</button>
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
                             <h5>Do you wish to enable this link?</h5>
                           </div>
                       </div>

                   </div>
                  </form>
         </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Enable');">Enable Link</button>
          </div>
        </div>
      </div>
    </div>

 <script>

    Messenger.options = {
        extraClasses: 'messenger-fixed messenger-on-top'
    }

    loadTableAJAX();

    var table = $("#datatable-table").DataTable();

    $("#addLink").click(function(){
      $("#id").val('');
      $("#name").val('');
      $("#link").val('');
  		$("#myModal").modal('show');
  	});

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();
        if (this.textContent.indexOf("Edit") > -1) {
          $("#id").val(data["id"]);
          $("#name").val(data["name"]);
          $("#link").val(data["link"]);
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
