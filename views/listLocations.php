<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

$state = '';
$states = json_decode(file_get_contents(API_HOST.'/api/states?columns=abbreviation,name&order=name'));

$locationTypeID = '';
$locationTypes = json_decode(file_get_contents(API_HOST.'/api/location_types?columns=id,name&order=id'));

$getlocations = json_decode(file_get_contents(API_HOST.'/api/locations?include=location_types&columns=locations.name,location_types.name,locations.address1,locations.address2,locations.city,locations.state,locations.zip,locations.status&filter=entityID,eq,' . $_SESSION['entityid'] . '&order=locationTypeID'),true);
$locations = php_crud_api_transform($getlocations);
//print_r($locations['locations'][0]['name']);
//print_r($locations['locations'][0]['location_types'][0]['name']);

 ?>

 <script>

      function verifyAndPost() {
          var passValidation = false;

          var data = {entityID: $("#entityID").val(), locationTypeID: $("#locationTypeID").val(), name: $("#name").val(), address1: $("#address1").val(), address2: $("#address2").val(), city: $("#city").val(), state: $("#state").val(), zip: $("#zip").val()};
          var url = '<?php echo API_HOST."/api/locations" ?>';
          $.ajax({
             url: url,
             type: "POST",
             data: JSON.stringify(data),
             contentType: "application/json",
             async: false,
             success: function(data){
                if (data > 0) {
                  $("#myModal").modal('hide');
                  var htmlTable = loadTable();
                  //$("#dataTable").html(htmlTable);
                  //$("#datatable-table").DataTable();
                  passValidation = true;
                } else {
                  alert("Adding Location Failed!");
                }
             },
             error: function() {
                alert("There Was An Error Adding Location!");
             }
          });
          return passValidation;
      }

      function loadTableAJAX() {
        var url = '<?php echo API_HOST; ?>' + '/api/locations?include=location_types&columns=locations.name,location_types.name,locations.address1,locations.address2,locations.city,locations.state,locations.zip&filter=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&order=locationTypeID&transform=1';
        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                dataSrc: 'locations'
            },
            columns: [
                { data: "name" },
                { data: "location_types[0].name" },
                { data: "address1" },
                { data: "address2" },
                { data: "city" },
                { data: "state" },
                { data: "zip" }
            ]
          });

          //To Reload The Ajax
          //See DataTables.net for more information about the reload method
          example_table.ajax.reload()

      }

      function loadTable() {
        var url = '<?php echo API_HOST; ?>' + '/api/locations?include=location_types&columns=locations.name,location_types.name,locations.address1,locations.address2,locations.city,locations.state,locations.zip&filter=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&order=locationTypeID&transform=1';
        $.ajax({
           url: url,
           type: "GET",
           contentType: "application/json",
           async: false,
           success: function(data){
             //data = (JSON.stringify(data['locations']['records']));
             data = php_crud_api_transform(data);
              if (data) {
                var example_table = $('#datatable-table').DataTable({
                    'retrieve': true,
                    'ajax':  url,
                    'columns': [
                        { "locations": "name" },
                        { "locations_types": "locationTypeID" },
                        { "locations": "address1" },
                        { "locations": "address2" },
                        { "locations": "city" },
                        { "locations": "state" },
                        { "locations": "zip" }
                    ]
                  });

                  //To Reload The Ajax
                  //See DataTables.net for more information about the reload method
                  example_table.ajax.reload()

/*
                var table = '';
                var table = '<table id="datatable-table" class="table table-striped table-hover">' +
                    '<thead>' +
                    '<tr>' +
                        '<th>Name</th>' +
                        '<th>Type</th>' +
                        '<th class="hidden-sm-down">Address1</th>' +
                        '<th class="hidden-sm-down">Address2</th>' +
                        '<th class="hidden-sm-down">City</th>' +
                        '<th class="no-sort">State</th>' +
                        '<th class="no-sort">Zip</th>' +
                        '<th class="no-sort">&nbsp;</th>' +
                    '</tr>' +
                    '</thead>' +
                    '<tbody>';

                       for(var key in data.locations) {
                         if (data.locations.hasOwnProperty(key)) {
                            table += '<tr>' +
                                      '<td><span class="fw-semi-bold">' + data.locations[key].name + '</span></td>' +
                                      '<td><span class="fw-semi-bold">' + data.locations[key].location_types[0]['name'] + '</span></td>' +
                                      '<td class="hidden-sm-down">' + data.locations[key].address1 + '</td>' +
                                      '<td class="hidden-sm-down">' + data.locations[key].address2 + '</td>' +
                                      '<td class="hidden-sm-down">' + data.locations[key].city + '</td>' +
                                      '<td class="hidden-sm-down">' + data.locations[key].state + '</td>' +
                                      '<td class="hidden-sm-down">' + data.locations[key].zip + '</td>' +
                                      '<td class="hidden-sm-down"><i class="fa fa-edit"></i></td>' +
                                  '</tr>';
                          }
                       }

                    table += '</tbody>' +
                          '</table>';
                return table;
*/
              } else {
                return "Adding Location Failed!";
              }
           },
           error: function() {
              return "There Was An Error Adding Location!";
           }
        });

      }

 </script>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Location Maintenance</li>
 </ol>
 <h1 class="page-title">Profile - <span class="fw-semi-bold">Maintenance</span></h1>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Locations</span></h4>
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
         <button type="button" id="addLocation" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Location</button>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover">
                 <thead>
                 <tr>
                     <th>Name</th>
                     <th>Type</th>
                     <th class="hidden-sm-down">Address1</th>
                     <th class="hidden-sm-down">Address2</th>
                     <th class="hidden-sm-down">City</th>
                     <th class="no-sort">State</th>
                     <th class="no-sort">Zip</th>
                 </tr>
                 </thead>
                 <tbody>

<?php
/*
                    foreach($locations['locations'] as $key) {

                     echo "<tr>
                               <td><span class=\"fw-semi-bold\">" . $key['name'] ."</span></td>
                               <td class=\"hidden-sm-down\">" . $key['location_types'][0]['name'] ."</td>
                               <td class=\"hidden-sm-down\">" . $key['address1'] ."</td>
                               <td class=\"hidden-sm-down\">" . $key['address2'] ."</td>
                               <td class=\"hidden-sm-down\">" . $key['city'] ."</td>
                               <td class=\"hidden-sm-down\">" . $key['state'] ."</td>
                               <td class=\"hidden-sm-down\">" . $key['zip'] ."</td>
                               <td class=\"hidden-sm-down pull-right\"><button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-edit text-info\"></i> <span class=\"text-info\">Edit</span></button>";

                     if ($key['status'] == "Active") {
                               echo " &nbsp;<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-remove text-info\"></i> <span class=\"text-info\">Disable</span></button>";
                     } else {
                               echo " &nbsp;<button class=\"btn btn-danger btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-exclamation-sign text-info\"></i> <span class=\"text-info\">Enable</span></button>";
                     }

                     echo "
                              </td>
                           </tr>\n";

                }
*/
?>

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
         <h5 class="modal-title" id="exampleModalLabel">Add Location</h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
               <form id="formRegister" class="register-form mt-lg">
                 <input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
                 <div class="row">
                     <div class="col-sm-6">
                         <div class="form-group">
                           <input type="text" id="name" name="name" class="form-control mb-sm" placeholder="Location Title" />
                         </div>
                     </div>
                     <div class="col-sm-6">
                         <div class="form-group">
                           <select id="locationTypeID" name="locationTypeID" data-placeholder="Location Type" class="form-control chzn-select" data-ui-jq="select2" required="required">
                             <option value="">*Select Type...</option>
            <?php
                             foreach($locationTypes->location_types->records as $value) {
                                 $selected = ($value[0] == $locationTypeID) ? 'selected=selected':'';
                                 echo "<option value=" .$value[0] . " " . $selected . ">" . $value[1] . "</option>\n";
                             }
            ?>
                           </select>
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-6">
                         <div class="form-group">
                           <input type="text" id="address1" name="address1" class="form-control mb-sm" placeholder="Company Address" />
                         </div>
                     </div>
                     <div class="col-sm-6">
                         <div class="form-group">
                           <input type="text" id="address2" name="address2" class="form-control mb-sm" placeholder="Bldg. Number/Suite" />
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-sm-4">
                         <div class="form-group">
                           <input type="text" id="city" name="city" class="form-control" placeholder="*City"
                                  required="required" />
                         </div>
                     </div>
                     <div class="col-sm-4">
                         <div class="form-group">
                           <select id="state" name="state" data-placeholder="State" class="form-control chzn-select" data-ui-jq="select2" required="required">
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
                         <div class="form-group">
                           <input type="text" id="zip" name="zip" class="form-control mb-sm" placeholder="Zip" />
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

 <script>

    loadTableAJAX();

    //$("#datatable-table").DataTable();

    $("#addLocation").click(function(){
  		$("#myModal").modal('show');
  	});

 </script>
