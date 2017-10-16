<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

$userTypeID = '';
//$userTypes = json_decode(file_get_contents(API_HOST_URL . '/user_types?columns=id,name&order=id'));

if ($_SESSION['entityid'] == 0) {
    $args = array();
} else {
    $args = array(
        "filter"=>"id,gt,0"
    );
}
$url = API_HOST_URL . "/user_types?".http_build_query($args);
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'GET'
    )
);
$context  = stream_context_create($options);
$userTypes = json_decode(file_get_contents($url,false,$context),false);

?>

 <script>

      function post() {

          if ( $('#formUser').parsley().validate() ) {

                  if ($("#emailAddress").val() > '') {

                        if ($("#id").val() == '') { // Only check if this is an add

                              var params = {
                                    username: $("#emailAddress").val()
                              };
                              $.ajax({
                                 url: '<?php echo HTTP_HOST."/checkforusername" ?>',
                                 type: 'POST',
                                 data: JSON.stringify(params),
                                 contentType: "application/json",
                                 async: false,
                                 success: function(response){
                                    if (response == "success") {
                                        result = true;
                                    } else {
                                        alert("Username Already Exists: " + response);
                                        result = false;
                                    }
                                 },
                                 error: function(response) {
                                    alert("Username Verification Failed: " + response);
                                    result = false;
                                 }
                              });

                              if (result) {
                                verifyAndPost();
                              } else {
                                return false;
                              }

                        } else {

                            verifyAndPost(); // If updating go ahead and post

                        }

                } else {

                   alert('You Must Assign a Username.');
                   return false;

                }

          } else {

              return false;

          }

      }

      function verifyAndPost() {

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
                type = "PUT";
                status = $("#status").val();
            } else {
                type = "POST";
                status = "Inactive";
            }

            var params = {
                  userID: $("#userID").val(),
                  member_id: $("#id").val(),
                  type: type,
                  entityID: $("#entityID").val(),
                  userTypeID: $("#userTypeID").val(),
                  firstName: $("#firstName").val(),
                  lastName: $("#lastName").val(),
                  username: $("#emailAddress").val(),
                  password: $("#password").val(),
                  uniqueID: $("#uniqueID").val(),
                  textNumber: $("#textNumber").val(),
                  status: status,
                  createdAt: today,
                  updatedAt: today
            };

            $.ajax({
               url: '<?php echo HTTP_HOST."/usermaintenance" ?>',
               type: type,
               data: JSON.stringify(params),
               contentType: "application/json",
               async: false,
               success: function(data){
                   if (data == "success") {
                    $("#myModal").modal('hide');
                    loadTableAJAX();
                    $("#id").val('');
                    $("#userID").val('');
                    $("#userTypeID").val('');
                    $("#firstName").val('');
                    $("#lastName").val('');
                    $("#emailAddress").val('');
                    $("#password").val('');
                    $("#passwordConfirm").val('');
                    $("#uniqueID").val('');
                    $("#textNumber").val('');
                    $("#status").val('');
                    passValidation = true;
                  } else {
                    alert("Adding User Failed!");
                  }
               },
               error: function() {
                  alert("There Was An Error Adding User!");
               }
            });

            return passValidation;

      }

      function loadTableAJAX() {
        var url = '<?php echo API_HOST_URL; ?>' + '/members?include=users,user_types&columns=members.id,members.entityID,members.userID,members.firstName,members.lastName,user_types.id,user_types.name,users.username,users.uniqueID,users.textNumber,users.status&filter[]=members.entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&order[]=lastName&order[]=firstName&transform=1';
        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                dataSrc: 'members'
            },
            columns: [
                { data: "id", visible: false },
                { data: "entityID", visible: false },
                { data: "userID", visible: false },
                { data: "firstName" },
                { data: "lastName" },
                { data: "users[0].user_types[0].id", visible: false },
                { data: "users[0].user_types[0].name" },
                { data: "users[0].username", visible: false },
                { data: "users[0].uniqueID", visible: false },
                { data: "users[0].textNumber", visible: false },
                { data: "users[0].status" },
                {
                    data: null,
                    "bSortable": false,
                    "mRender": function (o) {
                        var buttons = '<div class="pull-right text-nowrap">';
                        buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-edit text\"></i> <span class=\"text\">Edit</span></button>';

                        if (o.users[0].status == "Active") {
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
          var url = '<?php echo API_HOST_URL . "/users" ?>/' + $("#userID").val();
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
                  alert("Changing Status of User Failed!");
                }
             },
             error: function() {
                alert("There Was An Error Changing User Status!");
             }
          });

          //return passValidation;
      }

 </script>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">User Maintenance</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">User</span></h4>
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
         <button type="button" id="addUser" class="btn btn-primary pull-xs-right" data-target="#myModal">Add User</button>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th>Members ID</th>
                     <th>Entity ID</th>
                     <th>User ID</th>
                     <th class="hidden-sm-down">First Name</th>
                     <th class="hidden-sm-down">Last Name</th>
                     <th class="no-sort">User Type ID</th>
                     <th class="no-sort">Type</th>
                     <th class="no-sort">UserName</th>
                     <th class="no-sort">Unique ID</th>
                     <th class="no-sort">Text Number</th>
                     <th class="no-sort">Status</th>
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
         <h5 class="modal-title" id="exampleModalLabel"><strong>User</strong></h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formUser" class="register-form mt-lg">
                 <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
                 <input type="hidden" id="id" name="id" value="" />
                 <input type="hidden" id="userID" name="userID" value="" />
                 <input type="hidden" id="status" name="status" value="" />
                 <div class="row">
                     <div class="col-sm-4">
                         <label for="firstName">First Name</label>
                         <div class="form-group">
                           <input type="text" id="firstName" name="firstName" class="form-control mb-sm" placeholder="*First Name" required="required" />
                         </div>
                     </div>
                     <div class="col-sm-4">
                         <label for="lastName">Last Name</label>
                         <div class="form-group">
                           <input type="text" id="lastName" name="lastName" class="form-control mb-sm" placeholder="*Last Name" required="required" />
                         </div>
                     </div>
                     <div class="col-sm-4">
                         <label for="contactTypeID">User Type</label>
                         <div class="form-group">
                           <select id="userTypeID" name="userTypeID" data-placeholder="*User Type" class="form-control chzn-select" data-ui-jq="select2" required="required">
                             <option value="">*Select Type...</option>
            <?php
                             foreach($userTypes->user_types->records as $value) {
                                 $selected = ($value[0] == $userTypeID) ? 'selected=selected':'';
                                 echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                             }
            ?>
                           </select>
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-4">
                       <label for="title">Username <i>(Use an Email Address)</i></label>
                       <div class="form-group">
                         <input type="text" id="emailAddress" name="emailAddress" class="form-control mb-sm" placeholder="*Email Address" required="required" data-parsley-type="email" />
                       </div>
                     </div>
                     <div class="col-sm-4">
                         <label for="textNumber">Text Number</label>
                         <div class="form-group">
                           <input type="text" id="textNumber" name="textNumber" class="form-control" placeholder="Text Number" required="required" />
                         </div>
                     </div>
                     <div class="col-sm-4">
                         <div id="divUserTypeID">
                         <label for="uniqueID">Driver ID</label>
                         <div class="form-group">
                           <input type="text" id="uniqueID" name="uniqueID" class="form-control" placeholder="Unique ID" />
                         </div>
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-4">
                         <label for="password">Password</label>
                         <div class="form-group">
                           <input type="text" id="password" name="password" class="form-control" placeholder="*Password" />
                         </div>
                     </div>
                     <div class="col-sm-4">
                         <label for="uniqueID">Confirm Password</label>
                         <div class="form-group">
                           <input type="text" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="Confirm Password"
                            data-parsley-equalto="#password" />
                         </div>
                     </div>
                     <div class="col-sm-4">
                         &nbsp;
                     </div>
                  </div>
                </form>
       </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="return post();">Save changes</button>
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
                  <input type="hidden" id="userID" name="userID" value="" />
                  <div class="row">
                      <div class="col-sm-12">
                          <div class="form-group">
                            <h5>Do you wish to disable this user?</h5>
                          </div>
                      </div>

                  </div>
                 </form>
        </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Disable');">Disable User</button>
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
                   <input type="hidden" id="userID" name="userID" value="" />
                   <div class="row">
                       <div class="col-sm-12">
                           <div class="form-group">
                             <h5>Do you wish to enable this user?</h5>
                           </div>
                       </div>

                   </div>
                  </form>
         </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Enable');">Enable User</button>
          </div>
        </div>
      </div>
    </div>

 <script>

    loadTableAJAX();

    var table1 = $("#datatable-table").DataTable();

    $("#addUser").click(function(){
        $("#id").val('');
        $("#userID").val('');
        $("#userTypeID").val('');
        $("#firstName").val('');
        $("#lastName").val('');
        $("#emailAddress").val('');
        $("#password").val('');
        $("#passwordConfirm").val('');
        $("#uniqueID").val('');
        $("#status").val('');
        $("#divUserTypeID").hide();
  		$("#myModal").modal('show');
  	});

    $('#datatable-table tbody').on( 'click', 'button', function () {
        var data = table1.row( $(this).parents('tr') ).data();
        if (this.textContent.indexOf("Edit") > -1) {
          $("#id").val(data["id"]);
          $("#userID").val(data["userID"]);
          $("#firstName").val(data["firstName"]);
          $("#lastName").val(data["lastName"]);
          $("#emailAddress").val(data["users"][0]["username"]);
          $("#password").val('');
          $("#confirmPassword").val('');
          $("#uniqueID").val(data["users"][0]["uniqueID"]);
          $("#textNumber").val(data["users"][0]["textNumber"]);
          $("#userTypeID").val(data["users"][0]["user_types"][0]["id"]);
          $("#status").val(data['users'][0]['status']);
/*
          if ($("#userTypeID").val() == 5) {
              $("#divUserTypeID").show();
          } else {
              $("#divUserTypeID").hide();
          }
*/
          $("#divUserTypeID").hide();
          $("#myModal").modal('show');
        } else {
            $("#id").val(data["id"]);
            $("#userID").val(data["userID"]);
            if (this.textContent.indexOf("Disable") > -1) {
              $("#disableDialogLabel").html('Disable <strong>' + data['firstName'] + ' ' + data['lastName'] + '</strong>');
              $("#myDisableDialog").modal('show');
            } else {
              if (this.textContent.indexOf("Enable") > -1) {
                $("#enableDialogLabel").html('Enable <strong>' + data['firstName'] + ' ' + data['lastName'] + '</strong>');
                $("#myEnableDialog").modal('show');
              }
            }
        }

    } );

/*
    $('#userTypeID').on( 'change', function () {
        if ($("#userTypeID").val() == 5) {
            $("#divUserTypeID").show();
        } else {
            $("#divUserTypeID").hide();
        }

    });
*/

 </script>
