<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

$contactTypeID = '';
$contactTypes = json_decode(file_get_contents(API_HOST_URL . '/contact_types?columns=id,name&order=id'));

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

          $("#load").html("<i class='fa fa-spinner fa-spin'></i> Edit Contact");
          $("#load").prop("disabled", true);

        if ( $('#formContact').parsley().validate() ) {

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
                    var url = '<?php echo API_HOST_URL . "/contacts" ?>/' + $("#id").val();
                    type = "PUT";
                } else {
                    var url = '<?php echo API_HOST_URL . "/contacts" ?>';
                    type = "POST";
                }

				var phone = $("#primaryPhone").val().replace(/(\d{3})\-?(\d{3})\-?(\d{4})/, '$1-$2-$3');
				var ext = $("#primaryPhoneExt").val();
				if (ext != "") {
					phone = phone + " x" + ext;
				}

                if (type == "PUT") {
                    var date = today;
                    var data = {
                            entityID: $("#entityID").val(),
                            contactTypeID: $("#contactTypeID").val(),
                            firstName: $("#firstName").val(),
                            lastName: $("#lastName").val(),
                            title: $("#title").val(),
                            emailAddress: $("#emailAddress").val(),
                            primaryPhone: phone,
                            secondaryPhone: $("#secondaryPhone").val(),
                            fax: $("#fax").val(),
                            contactRating: $("#contactRating").val(),
                            updatedAt: date
                       };

                 } else {

                    var date = today;
                    var data = {
                            entityID: $("#entityID").val(),
                            contactTypeID: $("#contactTypeID").val(),
                            firstName: $("#firstName").val(),
                            lastName: $("#lastName").val(),
                            title: $("#title").val(),
                            emailAddress: $("#emailAddress").val(),
                            primaryPhone: phone,
                            secondaryPhone: $("#secondaryPhone").val(),
                            fax: $("#fax").val(),
                            contactRating: $("#contactRating").val(),
                            createdAt: date
                      };
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
                        $("#contactTypeID").val('');
                        $("#firstName").val('');
                        $("#lastName").val('');
                        $("#title").val('');
                        $("#emailAddress").val('');
                        $("#primaryPhone").val('');
                        $("#primaryPhoneExt").val('');
                        $("#secondaryPhone").val('');
                        $("#fax").val('');
                        $("#contactRating").val('');

                        $("#load").html("Save Changes");
                        $("#load").prop("disabled", false);
                        passValidation = true;
                      } else {
                          $("#load").html("Save Changes");
                          $("#load").prop("disabled", false);
                        alert("Adding Contact Failed!");
                      }
                   },
                   error: function() {
                       $("#load").html("Save Changes");
                       $("#load").prop("disabled", false);
                      alert("There Was An Error Adding Contact!");
                   }
                });

                $("#load").html("Save Changes");
                $("#load").prop("disabled", false);
                return passValidation;

          } else {

              $("#load").html("Save Changes");
              $("#load").prop("disabled", false);
                return false;

          }

      }

      function loadTableAJAX() {
        myApp.showPleaseWait();
        var url = '<?php echo API_HOST_URL; ?>' + '/contacts?include=contact_types&columns=contacts.id,contacts.firstName,contacts.lastName,contact_types.id,contact_types.name,contacts.title,contacts.emailAddress,contacts.primaryPhone,contacts.secondaryPhone,contacts.fax,contacts.contactRating,contacts.status&filter=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&order=contactTypeID&transform=1';
        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                dataSrc: 'contacts'
            },
            columns: [
                { data: "id", visible: false },
                { data: "contact_types[0].id", visible: false },
                { data: "contact_types[0].name" },
                { data: "firstName" },
                { data: "lastName" },
                { data: "title" },
                { data: "emailAddress" },
                { data: "primaryPhone" },
                { data: "secondaryPhone", visible: false },
                { data: "fax" },
                { data: "contactRating", visible: false },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {

                        myApp.hidePleaseWait();

						//console.log(o);

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
          var url = '<?php echo API_HOST_URL . "/contacts" ?>/' + $("#id").val();
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
                  alert("Changing Status of Contact Failed!");
                }
             },
             error: function() {
                alert("There Was An Error Changing Contact Status!");
             }
          });

          //return passValidation;
      }

      function loadBusinessTableAJAX(){
          myApp.showPleaseWait();
        var url = '<?php echo API_HOST_URL; ?>' + '/entities?include=locations&filter[0]=status,eq,Active&filter[1]=locations.status,eq,Active&filter[2]=locations.name,eq,Headquarters&transform=1';
        var example_table = $('#business-datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                //dataSrc: 'entities',
                dataSrc: function ( json ) {

                    var entities = json.entities;
                    var businesses = new Array();

                        entities.forEach(function(entity){

                            if(entity.locations.length == 0){
                                var location = {id: 0, address1: "", address2: "",
                                    city: "", state: "", zip: ""};

                                entity.locations.push(location);
                            }

                            var business = {
                                id: entity.id,
                                name: entity.name,
                                addressid: entity.locations[0].id,
                                address1: entity.locations[0].address1,
                                address2: entity.locations[0].address2,
                                city: entity.locations[0].city,
                                state: entity.locations[0].state,
                                zip: entity.locations[0].zip
                            };

                            businesses.push(business);
                        });

                    return businesses;
                }
            },
            columns: [
                { data: "id", visible: false },
                { data: "name" },
                { data: "addressid", visible: false },
                { data: "address1" },
                { data: "address2" },
                { data: "city" },
                { data: "state" },
                { data: "zip" },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {

                        myApp.hidePleaseWait();

                        var buttons = '<div class="pull-right text-nowrap">';
                        buttons += '<button class=\"btn btn-primary btn-xs view-contacts\" role=\"button\"><i class=\"glyphicon glyphicon-eye-open text\"></i> <span class=\"text\">View Contacts</span></button>';

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


      function loadBusinessContacts(entityID) {
        myApp.showPleaseWait();
        var url = '<?php echo API_HOST_URL; ?>' + '/contacts?include=contact_types&columns=contacts.id,contacts.entityID,contacts.firstName,contacts.lastName,contact_types.id,contact_types.name,contacts.title,contacts.emailAddress,contacts.primaryPhone,contacts.secondaryPhone,contacts.fax,contacts.contactRating,contacts.status&filter=entityID,eq,' + entityID + '&order=contactTypeID&transform=1';
        if ( ! $.fn.DataTable.isDataTable( '#datatable-table' ) ) {
            var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                dataSrc: 'contacts'
            },
            columns: [
                { data: "id", visible: false },
                { data: "entityID", visible: false },
                { data: "contact_types[0].id", visible: false },
                { data: "contact_types[0].name" },
                { data: "firstName" },
                { data: "lastName" },
                { data: "title" },
                { data: "emailAddress" },
                { data: "primaryPhone" },
                { data: "secondaryPhone", visible: false },
                { data: "fax" },
                { data: "contactRating", visible: false },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {

                    		myApp.hidePleaseWait();

						//console.log(o);

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
        }
        else{

            //The URL will change with each "View Commit" button click
            // Must load new Url each time.
            var reload_table = $('#datatable-table').DataTable();
            reload_table.ajax.url(url).load();
        }

      }

 </script>

 <?php
 if($_SESSION['entitytype'] != 0){
 ?>
 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Contact Maintenance</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Contacts</span></h4>
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
         <button type="button" id="addContact" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Contact</button>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover" width="100%">
                 <thead>
                 <tr>
                     <th>ID</th>
                     <th>Entity ID</th>
                     <th>Type ID</th>
                     <th class="hidden-sm-down">Type</th>
                     <th class="hidden-sm-down">First Name</th>
                     <th class="hidden-sm-down">Last Name</th>
                     <th class="hidden-sm-down">Title</th>
                     <th class="hidden-sm-down">Email Address</th>
                     <th class="hidden-sm-down">Primary Phone</th>
                     <th class="no-sort">Secondary Phone</th>
                     <th class="no-sort">Fax</th>
                     <th class="no-sort">Contact Rating</th>
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

 <?php
 }
 else{
     ?>
 <div id="business-list">
 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Contact Maintenance</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Businesses</span></h4>
         <div class="widget-controls">
             <!--<a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
             <a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>
             <a data-widgster="close" title="Close" href="#"><i class="glyphicon glyphicon-remove"></i></a>-->
         </div>
     </header>

     <div class="widget-body">
         <!--p>
             Column sorting, live search, pagination. Built with
             <a href="http://www.datatables.net/" target="_blank">jQuery DataTables</a>
         </p
         <button type="button" id="addContact" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Contact</button>-->
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="business-datatable-table" class="table table-striped table-hover" width="100%">
                 <thead>
                 <tr>
                     <th>ID</th>
                     <th class="hidden-sm-down">Name</th>
                     <th class="hidden-sm-down">Location ID</th>
                     <th class="hidden-sm-down">Address 1</th>
                     <th class="hidden-sm-down">Address 2</th>
                     <th class="hidden-sm-down">City</th>
                     <th class="hidden-sm-down">State</th>
                     <th class="hidden-sm-down">Zip Code</th>
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
 </div>

 <div id="business-contacts" style="display: none;">
 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Contact Maintenance</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Contacts</span></h4>
         <div class="widget-controls">
             <!--<a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
             <a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>-->
             <a data-widgster="close" title="Close" href="Javascript:closeContacts()"><i class="glyphicon glyphicon-remove"></i></a>
         </div>
     </header>
     <div class="widget-body">
         <!--p>
             Column sorting, live search, pagination. Built with
             <a href="http://www.datatables.net/" target="_blank">jQuery DataTables</a>
         </p -->
         <button type="button" id="addContact" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Contact</button>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover" width="100%">
                 <thead>
                 <tr>
                     <th>ID</th>
                     <th>Entity ID</th>
                     <th>Type ID</th>
                     <th class="hidden-sm-down">Type</th>
                     <th class="hidden-sm-down">First Name</th>
                     <th class="hidden-sm-down">Last Name</th>
                     <th class="hidden-sm-down">Title</th>
                     <th class="hidden-sm-down">Email Address</th>
                     <th class="hidden-sm-down">Primary Phone</th>
                     <th class="no-sort">Secondary Phone</th>
                     <th class="no-sort">Fax</th>
                     <th class="no-sort">Contact Rating</th>
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
 </div>
 <?php
 }
 ?>

 <!-- Modal -->
 <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel"><strong>Contact</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formContact" class="register-form mt-lg">
                <?php if ($_SESSION['entityid'] > 0) { ?>
                        <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
                <?php } else { ?>
                        <input type="hidden" id="entityID" name="entityID" />
              <?php } ?>
                 <input type="hidden" id="id" name="id" value="" />
                 <div class="row">
                     <div class="col-sm-6">
                         <label for="firstName">First Name</label>
                         <div class="form-group">
                           <input type="text" id="firstName" name="firstName" class="form-control mb-sm" placeholder="*First Name" required="required" />
                         </div>
                     </div>
                     <div class="col-sm-6">
                         <label for="lastName">Last Name</label>
                         <div class="form-group">
                           <input type="text" id="lastName" name="lastName" class="form-control mb-sm" placeholder="Last Name" required="required" />
                         </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-sm-4">
                         <label for="contactTypeID">Contact Type</label>
                         <div class="form-group">
                           <select id="contactTypeID" name="contactTypeID" data-placeholder="*Contact Type" class="form-control chzn-select" data-ui-jq="select2" required="required">
                             <option value="">*Select Type...</option>
            <?php
                             foreach($contactTypes->contact_types->records as $value) {
                                 $selected = ($value[0] == $contactTypeID) ? 'selected=selected':'';
                                 echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                             }
            ?>
                           </select>
                         </div>
                     </div>
                     <div class="col-sm-8">
                       <div class="form-group">
                         &nbsp;
                       </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-6">
                       <label for="title">Title</label>
                       <div class="form-group">
                         <input type="text" id="title" name="title" class="form-control mb-sm" placeholder="*Title" required="required" />
                       </div>
                     </div>
                     <div class="col-sm-6">
                         <label for="emailAddress">Email Address</label>
                         <div class="form-group">
                           <input type="email" data-parsley-type="email" id="emailAddress" name="emailAddress" class="form-control mb-sm" placeholder="*Email Address" required="required"/>
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-6">
                         <label for="primaryPhone">Primary Phone</label>
                         <div class="form-group">
                           <div class="col-sm-9" style="padding-left: 0; padding-right: 0">
 	                          <input type="text" id="primaryPhone" name="primaryPhone" class="form-control" placeholder="*Primary Phone" required="required" />
                           </div>
                           <div class="col-sm-3" style="padding-right: 0;">
                              <input type="text" maxlength="15" data-parsley-maxlength="15" id="primaryPhoneExt" name="primaryPhoneExt" class="form-control" placeholder="Ext" />
                           </div>
                         </div>
                     </div>
                     <div class="col-sm-6">
                         <label for="secondaryPhone">Secondary Phone</label>
                         <div class="form-group">
                           <input type="text" id="secondaryPhone" name="secondaryPhone" class="form-control" placeholder="Secondary Phone" />
                         </div>
                     </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-6">
                        <label for="fax">Fax</label>
                        <div class="form-group">
                          <input type="text" id="fax" name="fax" class="form-control mb-sm" placeholder="Fax" />
                        </div>
                    </div>
                     <div class="col-sm-6">
                         <label for="contactRating">Rating</label>
                         <div class="form-group">
                           <select id="contactRating" name="contactRating" data-placeholder="Rating" class="form-control chzn-select" data-ui-jq="select2">
                              <option value="">*Rating...</option>
             <?php
                              for($s = 1; $s < 6; $s++) {
                                  $selected = ($s == $contactRating) ? 'selected=selected':'';
                                  echo "<option value=" .$s . " " . $selected . ">" . $s . " Star(s)</option>\n";
                              }
             ?>
                           </select>
                         </div>
                     </div>

                 </div>
                </form>
       </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button id="load" type="button" class="btn btn-primary" onclick="return verifyAndPost();">Save changes</button>
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
                            <h5>Do you wish to disable this contact?</h5>
                          </div>
                      </div>

                  </div>
                 </form>
        </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Disable');">Disable Contact</button>
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
                             <h5>Do you wish to enable this contact?</h5>
                           </div>
                       </div>

                   </div>
                  </form>
         </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Enable');">Enable Contact</button>
          </div>
        </div>
      </div>
    </div>

 <?php
 if($_SESSION['entitytype'] != 0){
 ?>
 <script>

    loadTableAJAX();

    var table = $("#datatable-table").DataTable();

    $("#addContact").click(function(){
      $("#id").val('');
      $("#contactTypeID").val('');
      $("#firstName").val('');
      $("#lastName").val('');
      $("#title").val('');
      $("#emailAddress").val('');
      $("#primaryPhone").val('');
      $("#primaryPhoneExt").val('');
      $("#secondaryPhone").val('');
      $("#fax").val('');
      $("#contactRating").val('');
  		$("#myModal").modal('show');
  	});

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table.row( $(this).parents('tr') ).data();
        if (this.textContent.indexOf("Edit") > -1) {


          $("#id").val(data["id"]);
          $("#contactTypeID").val(data["contact_types"][0].id);
          $("#firstName").val(data["firstName"]);
          $("#lastName").val(data["lastName"]);
          $("#title").val(data["title"]);
          $("#emailAddress").val(data["emailAddress"]);

        		var phoneindex = data["primaryPhone"].indexOf(' x');
            if (phoneindex != -1) {
           	 	var phone  = data["primaryPhone"].substring(0, phoneindex);
           	 	var ext = data["primaryPhone"].substring(phoneindex + 2, data["primaryPhone"].length);
                $("#primaryPhone").val(phone);
                $("#primaryPhoneExt").val(ext);
            } else {
                $("#primaryPhone").val(data["primaryPhone"]);
                $("#primaryPhoneExt").val('');
            }

          $("#secondaryPhone").val(data["secondaryPhone"]);
          $("#fax").val(data["fax"]);
          $("#contactRating").val(data["contactRating"]);
          $("#myModal").modal('show');
        } else {
            $("#id").val(data["id"]);
            if (this.textContent.indexOf("Disable") > -1) {
              $("#disableDialogLabel").html('Disable <strong>' + data['firstName'] + ' ' + data['lastName'] + '</strong>');
              $("#myDisableDialog").modal('show');
            } else {
              if (this.textContent.indexOf("Enable") > -1) {
                $("#enableDialogLabel").html('Enable <strong>' + data['name'] + ' ' + data['lastName'] + '</strong>');
                $("#myEnableDialog").modal('show');
              }
            }
        }

    } );

 </script>
 <?php
 }
 else{
 ?>
<script>

        function openContacts(){
            $("#business-list").css("display", "none");
            $("#business-contacts").css("display", "block");
        }


        function closeContacts(){
            var table = $("#business-datatable-table").DataTable();
            $("#business-contacts").css("display", "none");
            $("#business-list").css("display", "block");
            table.ajax.reload();
        }

        loadBusinessTableAJAX();


        $('#business-datatable-table tbody').off('click').on( 'click', 'button', function () {
            var businesstable = $("#business-datatable-table").DataTable();
            var data = businesstable.row( $(this).parents('tr') ).data();

            var entityID = data["id"];

            loadBusinessContacts(entityID);
            openContacts();
        });


        $("#addContact").click(function(){
          $("#id").val('');
          $("#contactTypeID").val('');
          $("#firstName").val('');
          $("#lastName").val('');
          $("#title").val('');
          $("#emailAddress").val('');
          $("#primaryPhone").val('');
          $("#primaryPhoneExt").val('');
          $("#secondaryPhone").val('');
          $("#fax").val('');
          $("#contactRating").val('');
                    $("#myModal").modal('show');
            });

        $('#datatable-table tbody').on( 'click', 'button', function () {

            var table = $("#datatable-table").DataTable();
            var data = table.row( $(this).parents('tr') ).data();
            if (this.textContent.indexOf("Edit") > -1) {


              $("#id").val(data["id"]);
              $("#contactTypeID").val(data["contact_types"][0].id);
              $("#firstName").val(data["firstName"]);
              $("#lastName").val(data["lastName"]);
              $("#title").val(data["title"]);
              $("#emailAddress").val(data["emailAddress"]);
                            var phoneindex = data["primaryPhone"].indexOf(' x');
                if (phoneindex != -1) {
                            var phone  = data["primaryPhone"].substring(0, phoneindex);
                            var ext = data["primaryPhone"].substring(phoneindex + 2, data["primaryPhone"].length);
                    $("#primaryPhone").val(phone);
                    $("#primaryPhoneExt").val(ext);
                } else {
                    $("#primaryPhone").val(data["primaryPhone"]);
                    $("#primaryPhoneExt").val('');
                }

              $("#secondaryPhone").val(data["secondaryPhone"]);
              $("#fax").val(data["fax"]);
              $("#contactRating").val(data["contactRating"]);
              $("#entityID").val(data["entityID"]);
              $("#myModal").modal('show');
            } else {
                $("#id").val(data["id"]);
                if (this.textContent.indexOf("Disable") > -1) {
                  $("#disableDialogLabel").html('Disable <strong>' + data['firstName'] + ' ' + data['lastName'] + '</strong>');
                  $("#myDisableDialog").modal('show');
                } else {
                  if (this.textContent.indexOf("Enable") > -1) {
                    $("#enableDialogLabel").html('Enable <strong>' + data['name'] + ' ' + data['lastName'] + '</strong>');
                    $("#myEnableDialog").modal('show');
                  }
                }
            }

        } );

 </script>
<?php
 }

 ?>
