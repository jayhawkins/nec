<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

 ?>

 <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

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

        if ( $('#formInsurance').parsley().validate() ) {
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
                    var url = '<?php echo API_HOST."/api/insurance_carriers" ?>/' + $("#id").val();
                    type = "PUT";
                } else {
                    var url = '<?php echo API_HOST."/api/insurance_carriers" ?>';
                    type = "POST";
                }

                if (type == "PUT") {
                    var date = today;
                    var data = {entityID: $("#entityID").val(), name: $("#name").val(), contactName: $("#contactName").val(), contactEmail: $("#contactEmail").val(), contactPhone: $("#contactPhone").val(), policyNumber: $("#policyNumber").val(), policyExpirationDate: $("#policyExpirationDate").val(), updatedAt: date};
                } else {
                    var date = today;
                    var data = {entityID: $("#entityID").val(), name: $("#name").val(), contactName: $("#contactName").val(), contactEmail: $("#contactEmail").val(), contactPhone: $("#contactPhone").val(), policyNumber: $("#policyNumber").val(), policyExpirationDate: $("#policyExpirationDate").val(), createdAt: date};
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
                        $("#contactName").val('');
                        $("#contactEmail").val('');
                        $("#contactPhone").val('');
                        $("#policyNumber").val('');
                        $("#policyExpirationDate").val('');
                        passValidation = true;
                      } else {
                        alert("Adding Insurance Failed!");
                      }
                   },
                   error: function() {
                      alert("There Was An Error Adding Insurance!");
                   }
                });

                return passValidation;

          } else {

                return false;

          }

      }

      function loadTableAJAX() {
        myApp.showPleaseWait();
        var url = '<?php echo API_HOST; ?>' + '/api/insurance_carriers?columns=id,name,link,contactName,contactPhone,policyNumber,policyExpirationDate,status&filter=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&order=name&transform=1';
        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                dataSrc: 'insurance_carriers'
            },
            columns: [
                { data: "id", visible: false },
                { data: "name" },
                { data: "contactName" },
                { data: "contactPhone" },
                { data: "policyNumber" },
                { data: "policyExpirationDate", visible: false },
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
          var url = '<?php echo API_HOST."/api/insurance_carriers" ?>/' + $("#id").val();
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
                  alert("Changing Status of Insurance Failed!");
                }
             },
             error: function() {
                alert("There Was An Error Changing Insurance Status!");
             }
          });

          //return passValidation;
      }

 </script>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Insurance Maintenance</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Insurance</span></h4>
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
         <button type="button" id="addInsurance" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Insurance</button>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th>ID</th>
                     <th class="hidden-sm-down">Name</th>
                     <th class="hidden-sm-down">Contact Name</th>
                     <th class="hidden-sm-down">Contact Phone</th>
                     <th class="hidden-sm-down">Policy Number</th>
                     <th class="hidden-sm-down">Policy Expiration Date</th>
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
         <h5 class="modal-title" id="exampleModalLabel"><strong>Insurance</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formInsurance" class="register-form mt-lg">
                 <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
                 <input type="hidden" id="id" name="id" value="" />
                 <div class="row">
                     <div class="col-sm-6">
                         <label for="name">Insurer</label>
                         <div class="form-group">
                           <input type="text" id="name" name="name" class="form-control mb-sm" placeholder="*Name" required="required" />
                         </div>
                     </div>
                     <div class="col-sm-6">
                         <label for="contactName">Contact Name</label>
                         <div class="form-group">
                           <input type="text" id="contactName" name="contactName" class="form-control mb-sm" placeholder="*Contact Name" required="required" />
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-6">
                         <label for="contactEmail">Contact Email</label>
                         <div class="form-group">
                           <input type="email" id="contactEmail" name="contactEmail" class="form-control mb-sm" placeholder="*Contact Email" required="required" />
                         </div>
                     </div>
                     <div class="col-sm-6">
                         <label for="contactPhone">Contact Phone</label>
                         <div class="form-group">
                           <input type="text" id="contactPhone" name="contactPhone" class="form-control mb-sm" placeholder="Contact Phone" required="required" />
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-6">
                         <label for="policyNumber">Policy Number</label>
                         <div class="form-group">
                           <input type="text" id="policyNumber" name="policyNumber" class="form-control mb-sm" placeholder="*Policy Number" required="required" />
                         </div>
                     </div>
                     <div class="col-sm-6">
                         <label for="policyExpirationDate">Expiration Date</label>
                         <div class="form-group">
                           <!--input type="text" id="policyExpirationDate" name="policyExpirationDate" class="form-control mb-sm" placeholder="Policy Expiration Date (YYYY-MM-DD)" required="required" /-->
                           <div id="sandbox-container" class="input-group date  datepicker">
                              <input type="text" id="policyExpirationDate" name="policyExpirationDate" class="form-control" placeholder="Policy Expiration Date"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                           </div>
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-12">
                        <label for="fileupload">Policy File Upload</label>
                        <div class="form-group">
                        	<!--<input type="file" id="policyFile" name="files[]" class="form-control mb-sm" placeholder="*Policy File" required="required" multiple />-->
							<span class="btn btn-success fileinput-button">
								<i class="glyphicon glyphicon-plus"></i>
								<span>Add files...</span>
								<!-- The file input field used as target for the file upload widget -->
								<input id="fileupload" type="file" name="files[]" class="form-control mb-sm" placeholder="*Policy File" required="required" multiple />
								<!-- The global progress bar -->
								<div id="progress" class="progress">
									<div class="progress-bar progress-bar-success"></div>
								</div>
								<!-- The container for the uploaded files -->
								<div id="files" class="files"></div>
							</span>
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
                            <h5>Do you wish to disable this insurance policy?</h5>
                          </div>
                      </div>

                  </div>
                 </form>
        </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Disable');">Disable Policy</button>
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
                             <h5>Do you wish to enable this insurance policy?</h5>
                           </div>
                       </div>

                   </div>
                  </form>
         </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Enable');">Enable Policy</button>
          </div>
        </div>
      </div>
    </div>


<!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>-->
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="jQuery-File-Upload-9.18.0/js/vendor/jquery.ui.widget.js"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>
<!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="jQuery-File-Upload-9.18.0/js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="jQuery-File-Upload-9.18.0/js/jquery.fileupload.js"></script>
<!-- The File Upload processing plugin -->
<script src="jQuery-File-Upload-9.18.0/js/jquery.fileupload-process.js"></script>
<!-- The File Upload image preview & resize plugin -->
<script src="jQuery-File-Upload-9.18.0/js/jquery.fileupload-image.js"></script>
<!-- The File Upload audio preview plugin -->
<script src="jQuery-File-Upload-9.18.0/js/jquery.fileupload-audio.js"></script>
<!-- The File Upload video preview plugin -->
<script src="jQuery-File-Upload-9.18.0/js/jquery.fileupload-video.js"></script>
<!-- The File Upload validation plugin -->
<script src="jQuery-File-Upload-9.18.0/js/jquery.fileupload-validate.js"></script>
<script>
/*jslint unparam: true, regexp: true */

    loadTableAJAX();

    var table = $("#datatable-table").DataTable();

    $('.datepicker').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });

    $("#addInsurance").click(function(){
      $("#id").val('');
      $("#name").val('');
      $("#contactName").val('');
      $("#contactPhone").val('');
      $("#policyNumber").val('');
      $("#policyExpirationDate").val('');
  		$("#myModal").modal('show');
  	});

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();
        if (this.textContent.indexOf("Edit") > -1) {
          $("#id").val(data["id"]);
          $("#name").val(data["name"]);
          $("#contactName").val(data["contactName"]);
          $("#contactPhone").val(data["contactPhone"]);
          $("#policyNumber").val(data["policyNumber"]);
          $("#policyExpirationDate").val(data["policyExpirationDate"]);
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

/*global window, $ */
$(function () {
    'use strict';
    // Change this to the location of your server-side upload handler:
    var url = window.location.hostname === 'blueimp.github.io' ? '//jquery-file-upload.appspot.com/' : 'server/php/',
        uploadButton = $('<button/>')
            .addClass('btn btn-primary')
            .prop('disabled', true)
            .text('Processing...')
            .on('click', function () {
                var $this = $(this),
                    data = $this.data();
                $this
                    .off('click')
                    .text('Abort')
                    .on('click', function () {
                        $this.remove();
                        data.abort();
                    });
                data.submit().always(function () {
                    $this.remove();
                });
            });

            //---- file upload ---//
    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',
        autoUpload: false,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        maxFileSize: 999000,
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator.userAgent),
        previewMaxWidth: 100,
        previewMaxHeight: 100,
        previewCrop: true
    }).on('fileuploadadd', function (e, data) {
        data.context = $('<div/>').appendTo('#files');
        $.each(data.files, function (index, file) {
            var node = $('<p/>')
                    .append($('<span/>').text(file.name));
            if (!index) {
                node
                    .append('<br>')
                    .append(uploadButton.clone(true).data(data));
            }
            node.appendTo(data.context);
        });
    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index,
            file = data.files[index],
            node = $(data.context.children()[index]);
        if (file.preview) {
            node
                .prepend('<br>')
                .prepend(file.preview);
        }
        if (file.error) {
            node
                .append('<br>')
                .append($('<span class="text-danger"/>').text(file.error));
        }
        if (index + 1 === data.files.length) {
            data.context.find('button')
                .text('Upload')
                .prop('disabled', !!data.files.error);
        }
    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#progress .progress-bar').css(
            'width',
            progress + '%'
        );
    }).on('fileuploaddone', function (e, data) {
        $.each(data.result.files, function (index, file) {
            if (file.url) {
                var link = $('<a>')
                    .attr('target', '_blank')
                    .prop('href', file.url);
                $(data.context.children()[index])
                    .wrap(link);
            } else if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
        });
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});

 </script>
