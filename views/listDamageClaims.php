<?php
	session_start();
	require '../../nec_config.php';
	require '../lib/common.php';


$allEntities = '';
$allEntities = json_decode(file_get_contents(API_HOST_URL . '/entities?columns=id,name&order=name&filter[]=id,gt,0'));

?>

<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script>

    var entityid = <?php echo $_SESSION['entityid']; ?>;

    var userid = <?php echo $_SESSION['userid']; ?>;

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

        function loadDamageClaimNotes(damageClaimID){

            var url = '<?php echo API_HOST_URL; ?>';

            url += '/damage_claim_notes?include=members,entities&columns=id,userID,note,createdAt,updatedAt,members.firstName,members.lastName,entities.name&filter=damageClaimID,eq,' + damageClaimID + '&order=createdAt,desc&transform=1';

            var blnShow = false;

            if(entityid == 0) blnShow = true;

            if ( ! $.fn.DataTable.isDataTable( '#damage-note-table' ) ) {

                var orders_table = $('#damage-note-table').DataTable({
                retrieve: true,
                processing: true,
                "pageLength": 50,
                ajax: {
                    url: url,
                    dataSrc: 'damage_claim_notes'
                },
                columns:  [
                        { data: "id", visible: false },
                        { data: "userID", visible: false },
                        { data: null,
                            "bSortable": true,
                            "mRender": function (o) {
                                var userFullName = o.members[0].firstName + ' ' + o.members[0].lastName;

                                var nameWithCompany = userFullName + ' {' + o.members[0].entities[0].name + '}';

                                return nameWithCompany;
                            }
                        },
                        { data: "note" },
                        { data: "createdAt" }
                    ],
                order: [[4, "desc"]]
              });

                //To Reload The Ajax
                //See DataTables.net for more information about the reload method
                orders_table.ajax.reload();
            }
            else{

                //The URL will change with each "View Commit" button click
              // Must load new Url each time.
                var reload_table = $('#damage-note-table').DataTable();
                reload_table.ajax.url(url).load();
            }

        }

	function validateFile(file) {
	    if (file) {
            var ext = $(file).val().split(".");
            ext = ext[ext.length-1].toLowerCase();
            var arrayExtensions = ["jpg", "JPG", "jpeg", "JPEG", "png", "PNG", "bmp", "BMP", "gif", "GIF", "pdf", "PDF", "zip", "ZIP", "doc", "DOC", "docx", "DOCX"];
            if (arrayExtensions.lastIndexOf(ext) == -1) {
                alert("File must be one of the following valid types; jpg, jpeg, png, bmp, gif, pdf, zip, doc or docx.");
                $(file).val("");
                $(file).focus();
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
	}

        function viewPOD(documentID){

            var documentURL = '<?php echo API_HOST_URL; ?>' + '/documents/' + documentID;

            $.get(documentURL, function(data){

                window.open( data.documentURL, '_blank');
            });

        }

        function loadFileUploadDiv(){
            if($('#documentIDs').val() != "null"){

                var jsnDocuments = JSON.parse($('#documentIDs').val());
                var viewDocumentsButtons = "";
                $.each(jsnDocuments, function(key, document){
                    viewDocumentsButtons += '<a class=\"btn btn-primary mg-top-5\" href=\"#\" onclick=\"viewPOD(' + document.documentID + ')\">' + document.documentType + ': ' + document.documentName + '</a> &nbsp;';

                });

                $('#uploadedFiles').html(viewDocumentsButtons);
            }
        }

	function verifyAndPost() {
		if ( $('#formDamageClaim').parsley().validate() ) {
			var data,date;
			var passValidation = false;
			var type = "";
			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth()+1; //January is 0!
			var yyyy = today.getFullYear();
			var hours = today.getHours();
			var min = today.getMinutes();
			var sec = today.getSeconds();
			var url = "";
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
			var date = today;
			// file upload
			if ($("#id").val() > '') {
				url = '<?php echo API_HOST_URL . "/damage_claims" ?>/' + $("#id").val();
				type = "PUT";
				date = today;
				data = {id: $("#id").val(), entityID: $("#entityID").val(), entityAtFaultID: $("#entityAtFaultID").val(), vinNumber: $("#vinNumber").val(), estimatedRepairCost: $("#estimatedRepairCost").val(), negotiatedRepairCost: $("#negotiatedRepairCost").val(), damage: $("#damage").val(), updatedAt: date};
				$.ajax({
					url: url,
					type: type,
					data: JSON.stringify(data),
					contentType: "application/json",
					async: false,
					success: function(data){
						if (data > 0) {
							$("#claimsModal").modal('hide');

                                                        if(entityid > 0) loadTableAJAX();
                                                        else loadBusinessClaims($("#entityID").val());

							$("#id").val('');
                                                        $("#entityAtFaultID").val('');
							$("#vinNumber").val('');
							$("#estimatedRepairCost").val('');
							$("#nogotiatedRepairCost").val('');
							$("#damage").val('');
							passValidation = true;
						} else {
							alert("Adding Damage Claim Failed!");
						}
					},
					error: function() {
						alert("There Was An Error Adding Damage Claim!");
					}
				});

			}
                        else {

                                // update listDamageClaims
                                url = '<?php echo API_HOST_URL . "/damage_claims" ?>';
                                type = "POST";

                                date = today;
                                data = {entityID: $("#entityID").val(), entityAtFaultID: $("#entityAtFaultID").val(), vinNumber: $("#vinNumber").val(), estimatedRepairCost: $("#estimatedRepairCost").val(), negotiatedRepairCost: $("#negotiatedRepairCost").val(), damage: $("#damage").val(), status: "Active", createdAt: date, updatedAt: date};
                                $.ajax({
                                        url: url,
                                        type: type,
                                        data: JSON.stringify(data),
                                        contentType: "application/json",
                                        async: false,
                                        success: function(data){
                                                if (data > 0) {
                                                        $("#claimsModal").modal('hide');

                                                        if(entityid > 0) loadTableAJAX();
                                                        else loadBusinessClaims($("#entityID").val());

                                                        $("#id").val('');
                                                        $("#entityAtFaultID").val('');
                                                        $("#vinNumber").val('');
                                                        $("#estimatedRepairCost").val('');
                                                        $("#negotiatedRepairCost").val('');
                                                        $("#damage").val('');
                                                        $("#fileupload").val('');
                                                        passValidation = true;
                                                } else {
                                                        alert("Adding Damage Claim Failed!");
                                                }
                                        },
                                        error: function() {
                                                alert("There Was An Error Adding Damage Claim!");
                                        }
                                });

			}
			return passValidation;
		} else {
			return false;
		}
	}

	function loadTableAJAX(blnShowAtFault = false) {
        
            var url = '';
            
            if(blnShowAtFault){
                url = '<?php echo API_HOST_URL; ?>' + '/damage_claims?filter=entityAtFaultID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&transform=1';
            }
            else{
                url = '<?php echo API_HOST_URL; ?>' + '/damage_claims?filter=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&transform=1';
            }
            
            if ( ! $.fn.DataTable.isDataTable( '#datatable-table' ) ) {
                var example_table = $('#datatable-table').DataTable({
                    retrieve: true,
                    processing: true,
                    "pageLength": 50,
                    ajax: {
                            url: url,
                            dataSrc: 'damage_claims'
                    },
                    columns: [
                        { data: "id", visible: false },
                        { data: "entityID", visible: false },
                        { data: "entityAtFaultID", visible: false },
                        { data: "vinNumber" },
                        { data: "damage" },
                        { data: "estimatedRepairCost", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
                        { data: "negotiatedRepairCost", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
                        { data: "documentIDs", visible: false },
                        { data: "status", visible: false },
                        { data: null,
                            "bSortable": false,
                            "mRender": function (o) {
                                var buttons = '<div class="pull-right text-nowrap">';

                                buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-eye-open text\"></i> <span class=\"text\">Upload/View Claim Files</span></button> &nbsp;';
                                buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-eye-open text\"></i> <span class=\"text\">View Claim Details</span></button> &nbsp;';
                                buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-eye-open text\"></i> <span class=\"text\">View Notes</span></button> &nbsp;';

                                buttons += '</div>';
                                return buttons;
                            }
                        }
                    ],
                    order: [[1, "desc"]]
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
		var url = '<?php echo API_HOST_URL . "/damage_claims" ?>/' + $("#id").val();
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
                                        if(entityid > 0) loadTableAJAX();
                                        else loadBusinessClaims($("#entityID").val());

					passValidation = true;
				} else {
					$(myDialog).modal('hide');
					alert("Changing Status of Claim Failed!");
				}
			},
			error: function() {
				alert("There Was An Error Changing Claim Status!");
			}
		});
		//return passValidation;
	}

	function loadBusinessTableAJAX(){
        var url = '<?php echo API_HOST_URL; ?>' + '/entities?include=entity_types,locations&filter[]=status,eq,Active&filter[]=locations.status,eq,Active&filter[]=locations.name,eq,Headquarters&filter[]=id,gt,0&order[]=name&transform=1';
        var example_table = $('#business-datatable-table').DataTable({
            retrieve: true,
            processing: true,
            "pageLength": 50,
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
                                typeID: entity.entityTypeID,
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
                { data: null,
                    "bSortable": true,
                    "mRender": function(p) {
                        if (p.typeID == 1) {
                            return "Customer";
                        } else if (p.typeID == 2){
                            return "Carrier";
                        } else {
                            return "NEC Admin";
                        }
                    }
                },
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
                        buttons += '<button class=\"btn btn-primary btn-xs view-claims\" role=\"button\"><i class=\"glyphicon glyphicon-eye-open text\"></i> <span class=\"text\">View Claims</span></button>';

                        buttons += "</div>";
                        return buttons;
                    }
                }
            ],
            order: [[2]]
          });

          example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );

          //To Reload The Ajax
          //See DataTables.net for more information about the reload method
          example_table.ajax.reload();

    }

        function loadBusinessClaims(entityID, blnShowAtFault = false) {

            var url = '';
            
            if(blnShowAtFault){
                url = '<?php echo API_HOST_URL; ?>' + '/damage_claims?filter=entityAtFaultID,eq,' + entityID + '&transform=1';
            }
            else{
                 url = '<?php echo API_HOST_URL; ?>' + '/damage_claims?filter=entityID,eq,' + entityID + '&transform=1';
            }
            
            if ( ! $.fn.DataTable.isDataTable( '#datatable-table' ) ) {
                var example_table = $('#datatable-table').DataTable({
                    retrieve: true,
                    processing: true,
                    "pageLength": 50,
                    ajax: {
                            url: url,
                            dataSrc: 'damage_claims'
                    },
                    columns: [
                        { data: "id", visible: false },
                        { data: "entityID", visible: false },
                        { data: "entityAtFaultID", visible: false },
                        { data: "vinNumber" },
                        { data: "damage" },
                        { data: "estimatedRepairCost", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
                        { data: "negotiatedRepairCost", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
                        { data: "documentIDs", visible: false },
                        { data: "status" },
                        { data: null,
                            "bSortable": false,
                            "mRender": function (o) {
                                var buttons = '<div class="pull-right text-nowrap">';

                                buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-eye-open text\"></i> <span class=\"text\">Upload/View Claim</span></button> &nbsp;';
                                buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-eye-open text\"></i> <span class=\"text\">View Notes</span></button> &nbsp;';
                                
                                buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-edit text\"></i> <span class=\"text\">Edit</span></button> &nbsp;';

                                if (o.status == "Active") {
                                    buttons += '<button class=\"btn btn-success btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-ok text\"></i> <span class=\"text\">Approve</span></button> &nbsp;';
                                    buttons += "<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-remove text\"></i> <span class=\"text\">Disable</span></button>";
                                }
                                else if (o.status == "Inactive") {
                                    buttons += '<button class=\"btn btn-success btn-xs\" role=\"button\" disabled><i class=\"glyphicon glyphicon-ok text\"></i> <span class=\"text\">Approve</span></button> &nbsp;';
                                    buttons += "<button class=\"btn btn-danger btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-exclamation-sign text\"></i> <span class=\"text\">Enable</span></button>";
                                }
                                else if (o.status == "Invoiced") {
                                    buttons += '<button class=\"btn btn-success btn-xs\" role=\"button\" disabled><i class=\"glyphicon glyphicon-ok text\"></i> <span class=\"text\">Approve</span></button> &nbsp;';
                                    buttons += "<button class=\"btn btn-primary btn-xs\" role=\"button\" disabled><i class=\"glyphicon glyphicon-remove text\"></i> <span class=\"text\">Disable</span></button>";
                                }

                                buttons += '</div>';
                                return buttons;
                            }
                        }
                    ],
                    order: [[1, "desc"]]
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

<style>
    .mg-top-5{
        margin-top: 10px;
    }

</style>

<?php
if($_SESSION['entitytype'] != 0){
?>
<ol class="breadcrumb">
	<li>ADMIN</li>
	<li class="active">Damage Claims</li>
</ol>
<section class="widget">
	<header>
		<h4><span class="fw-semi-bold">Damage Claims</span></h4>
		<div class="widget-controls">
			<!--a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
			<a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>
			<a data-widgster="close" title="Close" href="#"><i class="glyphicon glyphicon-remove"></i></a-->
		</div>
	</header>
	<div class="widget-body">
		<!--p>
		Column sorting, live search, pagination. Built with
		<a href="http://www.datatables.net/" target="_blank">jQuery DataTables</a>
		</p -->

                <button type="button" id="addClaim" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Claim</button>
                <button type="button" id="btnViewClaims" class="btn btn-primary pull-xs-right" style="margin-right: 10px;">View Claims Against You</button>
		<br /><br />
		<div id="dataTable" class="mt">
			<table id="datatable-table" class="table table-striped table-hover">
				<thead>
					<tr>
					<th>ID</th>
					<th class="hidden-sm-down">EntityID</th>
					<th class="hidden-sm-down">Entity At Fault</th>
					<th class="hidden-sm-down">VIN Number</th>
					<th class="hidden-sm-down">Damage</th>
					<th class="hidden-sm-down">Estimated Cost</th>
					<th class="hidden-sm-down">Negotiated Cost</th>
					<th class="hidden-sm-down">Document IDs</th>
					<th class="hidden-sm-down">Status</th>
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
else {

?>

 <div id="business-list">
 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Damage Claim Maintenance</li>
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
         <br />
         <div id="dataTable" class="mt">
             <table id="business-datatable-table" class="table table-striped table-hover" width="100%">
                 <thead>
                 <tr>
                     <th>ID</th>
                     <th class="hidden-sm-down">Name</th>
                     <th class="hidden-sm-down">Type</th>
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

 <div id="business-claims" style="display: none;">
 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Damage Claim Maintenance</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Damage Claim</span></h4>
         <div class="widget-controls">
             <!--<a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
             <a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>-->
             <a data-widgster="close" title="Close" href="Javascript:closeClaims()"><i class="glyphicon glyphicon-remove"></i></a>
         </div>
     </header>
     <div class="widget-body">
         <!--p>
             Column sorting, live search, pagination. Built with
             <a href="http://www.datatables.net/" target="_blank">jQuery DataTables</a>
         </p -->
            <button type="button" id="addClaim" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Claim</button>
            <button type="button" id="btnViewClaims" class="btn btn-primary pull-xs-right" style="margin-right: 10px;">View Claims Against You</button>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th class="hidden-sm-down">EntityID</th>
                            <th class="hidden-sm-down">Entity At Fault</th>
                            <th class="hidden-sm-down">VIN Number</th>
                            <th class="hidden-sm-down">Damage</th>
                            <th class="hidden-sm-down">Estimated Cost</th>
                            <th class="hidden-sm-down">Negotiated Cost</th>
                            <th class="hidden-sm-down">Document IDs</th>
                            <th class="hidden-sm-down">Status</th>
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

<!-- Claim Modal -->
<div class="modal fade" id="claimsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel"><strong>Damage Claim</strong></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formDamageClaim" class="register-form mt-lg" action="" method="POST" enctype="multipart/form-data">
					<input type="hidden" id="entityID" name="entityID" />
					<input type="hidden" id="id" name="id" value="" />
					<div class="row">
						<div class="col-sm-3">
							<label for="vinNumber">VIN Number</label>
							<div class="form-group">
								<input type="text" id="vinNumber" name="vinNumber" class="form-control mb-sm" placeholder="* VIN Number" required />
							</div>
						</div>
						<div class="col-sm-3">
							<label for="estimatedRepairCost">Estimated Repair Cost</label>
							<div class="form-group">
								<input type="text" id="estimatedRepairCost" name="estimatedRepairCost" class="form-control mb-sm" placeholder="* ex: 2000.00" />
							</div>
						</div>
						<div class="col-sm-3">
							<label for="negotiatedRepairCost">Negotiated Repair Cost</label>
							<div class="form-group">
								<input type="text" id="negotiatedRepairCost" name="negotiatedRepairCost" class="form-control mb-sm" placeholder="* ex: 2000.00" />
							</div>
						</div>
						<div class="col-sm-3">
                                                    <div class="form-group">
                                        <?php if ($_SESSION['entityid'] > 0) { ?>
                                                       <input type="hidden" id="entityAtFaultID" name="entityAtFaultID" value="" />
                                        <?php } else { ?>
                                                        <label for="entityAtFaultID">At Fault</label>
                                                        <select id="entityAtFaultID" name="entityAtFaultID" data-placeholder="Carrier" class="form-control chzn-select" required="required">
                                                          <option selected=selected value=""> -Select Business- </option>
                                         <?php
                                                          foreach($allEntities->entities->records as $value) {
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
						<div class="col-sm-12">
							<label for="contactEmail">Damage</label>
							<div class="form-group">
								<textarea id="damage" name="damage" class="form-control mb-sm" placeholder="* Please Describe Damage" required></textarea>
							</div>
						</div>
					</div>
<!--					<div class="row">
						<div class="col-sm-12">
							<label for="fileupload">Claim File Upload</label>
							<div class="form-group">
								<input type="file" id="fileupload" name="fileupload" class="form-control mb-sm" data-filesize="20000000"
                                data-filetype="image/bmp,image/gif,image/jpeg,application/zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/rtf"
								onchange="validateFile(this);" />
							</div>
						</div>
					</div>-->
				</form>
			</div>
			<div class="modal-footer" id="btnClaimModalFooter">
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
								<h5>Do you wish to disable this claim?</h5>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Disable');">Disable Claim</button>
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
								<h5>Do you wish to enable this claim?</h5>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="return recordEnableDisable('Enable');">Enable Claim</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="viewPolicy" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enableDialogLabel">View Claim Files</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" id="claimID" name="claimID" value="" />
                    <input type="hidden" id="documentIDs" value="" />
                    <div class="row">
                        <div class="col-md-5">
                            <label for="filClaimFile">Claim File To Upload</label>
                            <div class="form-group">
                                <input type="file" id="filClaimFile" name="filClaimFile" class="form-control mb-sm" placeholder="Update Claim" data-filesize="20000000"
                                data-filetype="image/bmp,image/gif,image/jpeg,application/zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/rtf"
                                onchange="validateFile(this);" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="fileType">File Type</label>
                            <div class="form-group">
                                <select id="fileType" name="fileType" class="form-control mb-sm">
                                    <option value="">Select an option</option>
                                    <option value="Delivery Receipt">Delivery Receipt</option>
                                    <option value="Proof Of Estimate">Proof Of Estimate</option>
                                    <option value="Repair Bill">Repair Bill</option>
                                    <option value="Photo Of Damage">Photo Of Damage</option>
                                    <option value="Photo Of Repair">Photo Of Repair</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="btnView">&nbsp;</label>
                            <div class="form-group">
                                <button type="button" class="btn btn-primary pull-right" id="btnUploadFile">Upload File</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="updatePolicyFile">Files Uploaded</label>
                            <div id="uploadedFiles" class="form-group">

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewNotesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                </button>
                <br>
                <br>
                <h5 class="modal-title" id="viewNotesLabel" style="display: inline-block;"><strong>Damage Claim Notes</strong></h5>
                    <button type="button" class="btn btn-primary btn-md pull-right" onclick="" id="addDamageNote">Add Note</button>
            </div>
            <div class="modal-body">
                <form id="formRegister" class="register-form mt-lg">
                    <input type="hidden" id="damageClaimID" name="damageClaimID" />
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="damage-note-table" class="table table-striped table-hover" width="100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>UserID</th>
                                        <th>User</th>
                                        <th>Note</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                             </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true" aria-label="exampleModalCommitLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalCommitLabel"><strong>Add Note</strong></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <textarea class="form-control" id="txtAdminNote" rows="3"></textarea>
        </div>
        <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
           <button type="button" class="btn btn-primary"  id="btnSaveNote">Save</button>
        </div>
      </div>
    </div>
  </div>

<!-- confirmClaimApproval -->

<div class="modal fade" id="confirmClaimApproval" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Approve Damage Claim</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="frmClaimApproval" class="register-form mt-lg">
					<input type="hidden" id="approvedClaimID" name="approvedClaimID" />
					<input type="hidden" id="approvedEntityID" name="approvedEntityID" />
					<input type="hidden" id="approvedVinNumber" name="approvedVinNumber" />
					<input type="hidden" id="approvedDamage" name="approvedDamage" />
					<input type="hidden" id="approvedRepairCost" name="approvedRepairCost" />
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<h5>Do you wish to approve this claim and invoice the party at fault?</h5>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
				<button type="button" class="btn btn-primary" id="btnConfirmClaimApproval">Yes</button>
			</div>
		</div>
	</div>
</div>
<?php
if($_SESSION['entitytype'] != 0){
?>

<script>

	/*jslint unparam: true, regexp: true */

	loadTableAJAX();

        $('#btnViewClaims').off('click').on('click', function(){
            
            var btnViewClaims = $('#btnViewClaims').text();
            
            if(btnViewClaims == "View Claims Against You")
            {                
                loadTableAJAX(true);
                $('#btnViewClaims').text('View Your Claims');
            }
            else
            {
                loadTableAJAX(false);
                $('#btnViewClaims').text('View Claims Against You');
            }
                        
        });

	var table = $("#datatable-table").DataTable();
	$('.datepicker').datepicker({
		autoclose: true,
		todayHighlight: true,
		format: "yyyy-mm-dd"
	});

        $('#addDamageNote').off('click').on('click', function(){
            $("#txtAdminNote").val("");
            $("#addNoteModal").modal('show');
        });

        $('#btnSaveNote').off('click').on('click', function(){

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

            if(sec<10) {
                sec='0'+sec;
            }

            today = mm+'/'+dd+'/'+yyyy;
            today = yyyy+"-"+mm+"-"+dd+" "+hours+":"+min+":"+sec;

            var damageClaimID = $('#damageClaimID').val();

            var note = {damageClaimID: damageClaimID, userID: userid, note: $("#txtAdminNote").val(), createdAt: today, updatedAt: today};

            $.ajax({
                url: '<?php echo API_HOST_URL . "/damage_claim_notes/"; ?>',
                type: "POST",
                data: JSON.stringify(note),
                contentType: "application/json",
                async: false,
                success: function(data){

                    console.log(data);

                    loadDamageClaimNotes(damageClaimID);
                    $("#addNoteModal").modal('hide');
                },
                error: function(error){
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Unable to Save Note.");
                    $("#errorAlert").modal('show');
                }
            });

        });

        $("#addClaim").click(function(){
            $("#id").val('');
            $("#entityID").val(entityid);
            $("#claimsModal").modal('show');
        });

        $('#datatable-table tbody').off('click', 'button').on( 'click', 'button', function () {
            var table = $("#datatable-table").DataTable();
            var data = table.row( $(this).parents('tr') ).data();
            if (this.textContent.indexOf("Edit") > -1) {
                $("#id").val(data["id"]);
                $("#entityID").val(entityid);
                $("#entityAtFaultID").val(data["entityAtFaultID"]);
                $("#vinNumber").val(data["vinNumber"]);
                $("#damage").val(data["damage"]);
                $("#estimatedRepairCost").val(data["estimatedRepairCost"]);
                $("#negotiatedRepairCost").val(data["negotiatedRepairCost"]);
                $("#claimsModal").modal('show');
            } else if (this.textContent.indexOf("Upload/View Claim") > -1) {
                $("#claimID").val(data['id']);
                $("#documentIDs").val(JSON.stringify(data['documentIDs']));
                $('#fileType').val("");
                $('#filClaimFile').val("");
                loadFileUploadDiv();

                $("#viewPolicy").modal('show');
            }  else if (this.textContent.indexOf("View Notes") > -1) {
                $("#damageClaimID").val(data['id']);
                loadDamageClaimNotes(data['id']);

                $("#viewNotesModal").modal('show');
            }  else if (this.textContent.indexOf("View Claim Details") > -1){
                $("#id").val(data["id"]);
                $("#entityID").val(entityid);
                $("#entityAtFaultID").val(data["entityAtFaultID"]);
                $("#vinNumber").val(data["vinNumber"]);
                $("#damage").val(data["damage"]);
                $("#estimatedRepairCost").val(data["estimatedRepairCost"]);
                $("#negotiatedRepairCost").val(data["negotiatedRepairCost"]);
                $("#btnClaimModalFooter").css("display", "none");
                $("#formDamageClaim :input").prop("disabled", true);
                
                $("#claimsModal").modal('show');
            }else {
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

        $('#btnUploadFile').off('click').on('click', function(){
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; //January is 0!
            var yyyy = today.getFullYear();
            var hours = today.getHours();
            var min = today.getMinutes();
            var sec = today.getSeconds();
            var url = "";
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

            var file = $('#filClaimFile')[0].files[0];
            var fileType = $('#fileType').val();
            if(fileType == "" || file == undefined || file == ""){

                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Please Make sure you are selecting a file and a file type.");
                $("#errorAlert").modal('show');
            }
            else{

                var url = '<?php echo HTTP_HOST."/uploaddocument" ?>';
                var formData = new FormData();
                formData.append('fileupload', $('#filClaimFile')[0].files[0]);
                formData.append('entityID', $("#entityID").val());
                formData.append('name', $('#fileType').val() + ' for: ' + $("#vinNumber").val() + ' - ' + $('#filClaimFile')[0].files[0].name);

                $.ajax({
                        url : url,
                        type : 'POST',
                        data : formData,
                        processData: false,  // tell jQuery not to process the data
                        contentType: false,  // tell jQuery not to set contentType
                        success : function(data) {
                            console.log("Document Uploaded");
                            var documentIDs = [];

                            if($('#documentIDs').val() != "null") documentIDs = JSON.parse($('#documentIDs').val());

                            var newDocument = {documentID: data, documentType: fileType, documentName: $('#filClaimFile')[0].files[0].name};

                            documentIDs.push(newDocument);

                            url = '<?php echo API_HOST_URL . "/damage_claims" ?>/' + $("#claimID").val();
                            type = "PUT";
                            var claimData = {documentIDs: documentIDs, updatedAt: today};

                            $.ajax({
                                url: url,
                                type: type,
                                data: JSON.stringify(claimData),
                                contentType: "application/json",
                                async: false,
                                success: function(data){
                                    if (data > 0) {
                                        $('#documentIDs').val(JSON.stringify(documentIDs));
                                        $('#fileType').val("");
                                        $('#filClaimFile').val("");

                                        loadFileUploadDiv();
                                    } else {
                                        alert("Adding Damage Claim Failed!");
                                    }
                                },
                                error: function() {
                                    alert("There Was An Error Adding Damage Claim!");
                                }
                            });
                        },
                        error: function(error){
                            $("#errorAlertTitle").html("Error");
                            $("#errorAlertBody").html(error.responseText);
                            $("#errorAlert").modal('show');
                        }
                });

            }

        });

</script>

<?php

}
else {

?>

<script>

        $('#btnViewClaims').off('click').on('click', function(){
            
            var btnViewClaims = $('#btnViewClaims').text();
            
            if(btnViewClaims == "View Claims Against You")
            {                
                loadBusinessClaims($("#entityID").val(), true);
                $('#btnViewClaims').text('View Your Claims');
            }
            else
            {
                loadBusinessClaims($("#entityID").val(), false);
                $('#btnViewClaims').text('View Claims Against You');
            }
               
        });


        function openClaims(){
            $("#business-list").css("display", "none");
            $("#business-claims").css("display", "block");
        }


        function closeClaims(){
            var businesstable = $("#business-datatable-table").DataTable();
            $("#business-claims").css("display", "none");
            $("#business-list").css("display", "block");
            businesstable.ajax.reload();
        }

        loadBusinessTableAJAX();

        $('.datepicker').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "yyyy-mm-dd"
        });

        $('#btnConfirmClaimApproval').off('click').on('click', function(){

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

            if(sec<10) {
                sec='0'+sec;
            }

            today = mm+'/'+dd+'/'+yyyy;
            today = yyyy+"-"+mm+"-"+dd+" "+hours+":"+min+":"+sec;

            var damageClaimID = $('#approvedClaimID').val();
            var atFaultEntityID = $("#approvedEntityID").val();
            var vinNumber = $("#approvedVinNumber").val();
            var damage_description = $("#approvedDamage").val();
            var cost = $("#approvedRepairCost").val();

            var approved_damage_claim = {damageClaimID: damageClaimID, atFaultEntityID: atFaultEntityID, vinNumber: vinNumber, damage_description: damage_description, cost: cost, userID: userid, note: $("#txtAdminNote").val(), createdAt: today, updatedAt: today};

            $.ajax({
                url: '<?php echo API_HOST_URL . "/approved_damage_claims/"; ?>',
                type: "POST",
                data: JSON.stringify(approved_damage_claim),
                contentType: "application/json",
                async: false,
                success: function(data){

                    if(data > 0){
                        var newStatus = {status: "Invoiced"};

                        $.ajax({
                            url: '<?php echo API_HOST_URL . "/damage_claims/"; ?>' + damageClaimID,
                            type: "PUT",
                            data: JSON.stringify(newStatus),
                            contentType: "application/json",
                            async: false,
                            success: function(data){
                                loadBusinessClaims($("#entityID").val());
                                $("#confirmClaimApproval").modal('hide');
                                $("#errorAlertTitle").html("Success!");
                                $("#errorAlertBody").html("Claim Approved.");
                                $("#errorAlert").modal('show');
                            }
                        });
                    }
                },
                error: function(error){
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Unable to Approve Claim.");
                    $("#errorAlert").modal('show');
                }
            });

        });

        $('#addDamageNote').off('click').on('click', function(){
            $("#txtAdminNote").val("");
            $("#addNoteModal").modal('show');
        });

        $('#btnSaveNote').off('click').on('click', function(){

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

            if(sec<10) {
                sec='0'+sec;
            }

            today = mm+'/'+dd+'/'+yyyy;
            today = yyyy+"-"+mm+"-"+dd+" "+hours+":"+min+":"+sec;

            var damageClaimID = $('#damageClaimID').val();

            var note = {damageClaimID: damageClaimID, userID: userid, note: $("#txtAdminNote").val(), createdAt: today, updatedAt: today};

            console.log(note);

            $.ajax({
                url: '<?php echo API_HOST_URL . "/damage_claim_notes/"; ?>',
                type: "POST",
                data: JSON.stringify(note),
                contentType: "application/json",
                async: false,
                success: function(data){
                    console.log(data);

                    loadDamageClaimNotes(damageClaimID);
                    $("#addNoteModal").modal('hide');
                },
                error: function(error){
                    $("#errorAlertTitle").html("Error");
                    $("#errorAlertBody").html("Unable to Save Note.");
                    $("#errorAlert").modal('show');
                }
            });

        });


        $('#business-datatable-table tbody').off('click').on( 'click', 'button', function () {
            var businesstable = $("#business-datatable-table").DataTable();
            var data = businesstable.row( $(this).parents('tr') ).data();

            var entityID = data["id"];
            $("#entityID").val(data["id"]);

            loadBusinessClaims(entityID);
            openClaims();
        });


        $("#addClaim").click(function(){
              $("#id").val('');
              $("#claimsModal").modal('show');
        });

        $('#datatable-table tbody').off('click', 'button').on( 'click', 'button', function () {
            var table = $("#datatable-table").DataTable();
            var data = table.row( $(this).parents('tr') ).data();
            if (this.textContent.indexOf("Edit") > -1) {
                $("#id").val(data["id"]);
                $("#entityAtFaultID").val(data["entityAtFaultID"]);
                $("#vinNumber").val(data["vinNumber"]);
                $("#damage").val(data["damage"]);
                $("#estimatedRepairCost").val(data["estimatedRepairCost"]);
                $("#negotiatedRepairCost").val(data["negotiatedRepairCost"]);
                $("#claimsModal").modal('show');
            } else if (this.textContent.indexOf("Upload/View Claim") > -1) {
                $("#claimID").val(data['id']);
                $("#documentIDs").val(JSON.stringify(data['documentIDs']));
                $('#fileType').val("");
                $('#filClaimFile').val("");
                loadFileUploadDiv();

                $("#viewPolicy").modal('show');
            } else if (this.textContent.indexOf("View Notes") > -1) {
                $("#damageClaimID").val(data['id']);
                loadDamageClaimNotes(data['id']);

                $("#viewNotesModal").modal('show');
            } else if (this.textContent.indexOf("Approve") > -1) {
                $("#approvedClaimID").val(data['id']);
                $("#approvedEntityID").val(data['entityAtFaultID']);
                $("#approvedVinNumber").val(data["vinNumber"]);
                $("#approvedDamage").val(data["damage"]);
                $("#approvedRepairCost").val(data["negotiatedRepairCost"]);

                $("#confirmClaimApproval").modal('show');
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

        $('#btnUploadFile').off('click').on('click', function(){
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; //January is 0!
            var yyyy = today.getFullYear();
            var hours = today.getHours();
            var min = today.getMinutes();
            var sec = today.getSeconds();
            var url = "";
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

            var file = $('#filClaimFile')[0].files[0];
            var fileType = $('#fileType').val();
            if(fileType == "" || file == undefined || file == ""){

                $("#errorAlertTitle").html("Error");
                $("#errorAlertBody").html("Please Make sure you are selecting a file and a file type.");
                $("#errorAlert").modal('show');
            }
            else{

                var url = '<?php echo HTTP_HOST."/uploaddocument" ?>';
                var formData = new FormData();
                formData.append('fileupload', $('#filClaimFile')[0].files[0]);
                formData.append('entityID', $("#entityID").val());
                formData.append('name', $('#fileType').val() + ' for: ' + $("#vinNumber").val() + ' - ' + $('#filClaimFile')[0].files[0].name);

                $.ajax({
                        url : url,
                        type : 'POST',
                        data : formData,
                        processData: false,  // tell jQuery not to process the data
                        contentType: false,  // tell jQuery not to set contentType
                        success : function(data) {
                            console.log("Document Uploaded");
                            var documentIDs = [];

                            if($('#documentIDs').val() != "null") documentIDs = JSON.parse($('#documentIDs').val());

                            var newDocument = {documentID: data, documentType: fileType, documentName: $('#filClaimFile')[0].files[0].name};

                            documentIDs.push(newDocument);

                            var url = '<?php echo API_HOST_URL . "/damage_claims" ?>/' + $("#claimID").val();
                            var type = "PUT";
                            var claimData = {documentIDs: documentIDs, updatedAt: today};

                            $.ajax({
                                url: url,
                                type: type,
                                data: JSON.stringify(claimData),
                                contentType: "application/json",
                                async: false,
                                success: function(data){
                                    if (data > 0) {
                                        $('#documentIDs').val(JSON.stringify(documentIDs));
                                        $('#fileType').val("");
                                        $('#filClaimFile').val("");

                                        loadFileUploadDiv();
                                    } else {
                                        alert("Adding Damage Claim Failed!");
                                    }
                                },
                                error: function() {
                                    alert("There Was An Error Adding Damage Claim!");
                                }
                            });
                        },
                        error: function(error){
                            $("#errorAlertTitle").html("Error");
                            $("#errorAlertBody").html(error.responseText);
                            $("#errorAlert").modal('show');
                        }
                });

            }

        });

 </script>

<?php
}
?>
