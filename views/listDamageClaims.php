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

	function verifyAndPost() {
		//if ( $('#formDamageClaim').parsley().validate() ) {
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
				data = {id: $("#id").val(), entityID: $("#entityID").val(), vinNumber: $("#vinNumber").val(), estimatedRepairCost: $("#estimatedRepairCost").val(), negotiatedRepairCost: $("#negotiatedRepairCost").val(), damage: $("#damage").val(), updatedAt: date};
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
							$("#entityID").val('');
							$("#vinNumber").val('');
							$("#estimatedRepairCost").val('');
							$("#nogotiatedRepairCost").val('');
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

			} else {

				url = '<?php echo HTTP_HOST."/uploaddocument" ?>';
				type = "POST";
				var formData = new FormData();
				formData.append('fileupload', $('#fileupload')[0].files[0]);
				formData.append('entityID', $("#entityID").val());
				formData.append('name', 'Damage Claim for: ' + $("#vinNumber").val() + ' - ' + $('#fileupload')[0].files[0].name);
				$.ajax({
					url : url,
					type : 'POST',
					data : formData,
					processData: false,  // tell jQuery not to process the data
					contentType: false,  // tell jQuery not to set contentType
					success : function(data) {
						// update listDamageClaims
						url = '<?php echo API_HOST_URL . "/damage_claims" ?>';
						type = "POST";
						var files = $('#fileupload').prop("files");
						var fileNames = $.map(files, function(val) { return val.name; }).join(',');
						date = today;
						data = {entityID: $("#entityID").val(), vinNumber: $("#vinNumber").val(), estimatedRepairCost: $("#estimatedRepairCost").val(), negotiatedRepairCost: $("#negotiatedRepairCost").val(), damage: $("#damage").val(), status: "Active", createdAt: date, updatedAt: date};
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
									$("#entityID").val('');
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
						//console.log('listDamageClaim updated');
					}
				});
			}
			return passValidation;
		//} else {
		//	return false;
		//}
	}

	function replaceDocument() {

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

	    url = '<?php echo HTTP_HOST."/uploaddocument" ?>';
        type = "POST";
        var formData = new FormData();
        formData.append('fileupload', $('#updatePolicyFile')[0].files[0]);
        formData.append('entityID', $("#entityID").val());
        formData.append('name', $("#vinNumber").val());
        $.ajax({
            url : url,
            type : 'POST',
            data : formData,
            processData: false,  // tell jQuery not to process the data
            contentType: false,  // tell jQuery not to set contentType
            success : function(data) {
                // update listInsurance
                url = '<?php echo API_HOST_URL . "/damage_claims/" ?>' + $("#replaceID").val();
                type = "PUT";
                var files = $('#updatePolicyFile').prop("files");
                var fileNames = $.map(files, function(val) { return val.name; }).join(',');
                data = {id: $("#replaceID").val(), fileupload: fileNames, entityID: $("#entityID").val(), updatedAt: date};
                console.log(data);
                $.ajax({
                    url: url,
                    type: type,
                    data: JSON.stringify(data),
                    contentType: "application/json",
                    async: false,
                    success: function(data){
                        if (data > 0) {
                            $("#viewPolicy").modal('hide');
                            loadTableAJAX();
                            passValidation = true;
                        } else {
                            alert("Updating Damage Claim Document Failed!");
                        }
                    },
                    error: function() {
                        alert("There Was An Error Updating Damage Claim Document!");
                    }
                });
                //console.log('listDamageClaim updated');
            },
            error: function() {
                alert("Failed");
            }
        });
	}

	function loadTableAJAX() {
		var url = '<?php echo API_HOST_URL; ?>' + '/damage_claims?&filter=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&transform=1';
		var example_table = $('#datatable-table').DataTable({
			retrieve: true,
			processing: true,
			ajax: {
				url: url,
				dataSrc: 'damage_claims'
			},
			columns: [
                { data: "id", visible: false },
                { data: "entityID", visible: false },
				{ data: "entityIDAtFault", visible: false },
				{ data: "vinNumber" },
				{ data: "damage" },
				{ data: "estimatedRepairCost", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
				{ data: "negotiatedRepairCost", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
				{ data: "documentIDs", visible: false },
				{ data: "status" },
				{ data: null,
                    "bSortable": false,
					"mRender": function (o) {
					    var buttons = '';
                        if (o.fileupload > '') {
                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-eye-open text\"></i> <span class=\"text\">Upload/View Claim</span></button> &nbsp;';
                        }
                        buttons += "</div>";
                        return buttons;
                    }
				},
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
						buttons += '</div>';
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
					loadTableAJAX();
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
          myApp.showPleaseWait();
        var url = '<?php echo API_HOST_URL; ?>' + '/entities?include=entity_types,locations&filter[0]=status,eq,Active&filter[1]=locations.status,eq,Active&filter[2]=locations.name,eq,Headquarters&transform=1';
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
                        buttons += '<button class=\"btn btn-primary btn-xs view-contacts\" role=\"button\"><i class=\"glyphicon glyphicon-eye-open text\"></i> <span class=\"text\">View Claims</span></button>';

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

    function loadBusinessInsurance(entityID) {
        var url = '<?php echo API_HOST_URL; ?>' + '/damage_claims?columns=id,name,link,contactName,contactEmail,contactPhone,policyNumber,policyExpirationDate,fileupload,status&filter=entityID,eq,' + entityID + '&order=name&transform=1';
		if ( ! $.fn.DataTable.isDataTable( '#datatable-table' ) ) {
            var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                dataSrc: 'damage_claims'
            },
            columns: [
                { data: "id", visible: false },
				{ data: "name" },
				{ data: "contactName" },
				{ data: "contactEmail" },
				{ data: "contactPhone" },
				{ data: "policyNumber", visible: false },
				{ data: "policyExpirationDate", visible: false },
				{ data: null,
                    "bSortable": false,
					"mRender": function (o) {
					    var buttons = '';
                        if (o.fileupload > '') {
                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-eye-open text\"></i> <span class=\"text\">Upload/View Claim Documents</span></button> &nbsp;';
                        }
                        buttons += "</div>";
                        return buttons;
                    }
				},
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
						buttons += '</div>';
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

	function viewDocumentHold() {
		window.open($("#docToView").val(), '_blank', 'location=yes,height=570,width=520,scrollbars=yes,status=yes');
	}

</script>

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

} else {

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

 <div id="business-contacts" style="display: none;">
 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Damage Claim Maintenance</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Damage Claim</span></h4>
         <div class="widget-controls">
             <!--<a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
             <a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>>
             <a data-widgster="close" title="Close" href="Javascript:closeClaim()"><i class="glyphicon glyphicon-remove"></i></a-->
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
             <table id="datatable-table" class="table table-striped table-hover" width="100%">
                 <thead>
					<tr>
					<th>ID</th>
					<th class="hidden-sm-down">Name</th>
					<th class="hidden-sm-down">Contact Name</th>
					<th class="hidden-sm-down">Contact Email</th>
					<th class="hidden-sm-down">Contact Phone</th>
					<th class="hidden-sm-down">Policy Number</th>
					<th class="hidden-sm-down">Policy Expiration Date</th>
					<th class="hidden-sm-down">Policy File</th>
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
				<h5 class="modal-title" id="exampleModalLabel"><strong>Damage Claim</strong></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formDamageClaim" class="register-form mt-lg" action="<?php echo HTTP_HOST."/uploaddocument" ?>" method="POST" enctype="multipart/form-data">
					<input type="hidden" id="entityID" name="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
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
								<input type="text" id="estimatedRepairCost" name="estimatedRepairCost" class="form-control mb-sm" placeholder="* $$$$.&cent;&cent;" />
							</div>
						</div>
						<div class="col-sm-3">
							<label for="negotiatedRepairCost">Negotiated Repair Cost</label>
							<div class="form-group">
								<input type="text" id="negotiatedRepairCost" name="negotiatedRepairCost" class="form-control mb-sm" placeholder="* $$$$.&cent;&cent;" />
							</div>
						</div>
						<div class="col-sm-3">
							<label for=""></label>
							<div class="form-group">

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
					<div class="row">
						<div class="col-sm-6">
							<label for="policyNumber">Claims Documents</label>
							<div class="form-group">

							</div>
						</div>
					</div>
					<hr />
					<div class="row">
						<div class="col-sm-12">
							<label for="fileupload">Claim File Upload</label>
							<div class="form-group">
								<input type="file" id="fileupload" name="fileupload" class="form-control mb-sm" data-filesize="20000000"
                                data-filetype="image/bmp,image/gif,image/jpeg,application/zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/rtf"
								onchange="validateFile(this);" />
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
				<h5 class="modal-title" id="enableDialogLabel">View Claim</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
                <div class="row">
                <form>
                    <input type="hidden" id="entityID" value="<?php echo $_SESSION['entityid']; ?>" />
                    <input type="hidden" id="replaceID" name="replaceID" value="" />
                    <input type="hidden" id="docToView" value="" />
                    <div id="divUploadPolicyFile" class="col-md-7">
                        <label for="updatePolicyFile">Update Claim File</label>
                        <div class="form-group">
                            <input type="file" id="updateClaimFile" name="updateClaimFile" class="form-control mb-sm" placeholder="Update Claim" data-filesize="20000000"
                            data-filetype="image/bmp,image/gif,image/jpeg,application/zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/rtf"
                            onchange="validateFile(this);" />
                        </div>
                    </div>
                    <div class="col-md-5 pull-right">
                        <label for="btnView">&nbsp;</label>
                        <div class="form-group">
                            <button type="button" class="btn btn-primary" id="btnReplace">Upload/View Claim</button>
                            <button type="button" class="btn btn-primary" id="btnView">View Claim</button>
                        </div>
                    </div>
                </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnSave">Save</button>
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

	var table = $("#datatable-table").DataTable();
	$('.datepicker').datepicker({
		autoclose: true,
		todayHighlight: true,
		format: "yyyy-mm-dd"
	});

	$("#addClaim").click(function(){
		$("#id").val('');
		$("#vinNumber").val('');
		$("#estimatedRepairCost").val('');
		$("#negotiatedRepairCost").val('');
		$("#damage").val('');
		$("#fileupload").val('');
		$("#fileupload").prop("disabled", false);
		$("#fileupload").prop("required", true);
		$("#fileupload").parent().parent().attr("hidden", false);
		$("#myModal").modal('show');
	});
	$('#datatable-table tbody').on( 'click', 'button', function () {
		var data = table.row( $(this).parents('tr') ).data();
		if (this.textContent.indexOf("Edit") > -1) {
			$("#id").val(data["id"]);
			$("#entityID").val(data["entityID"]);
			$("#vinNumber").val(data["vinNumber"]);
			$("#estimatedRepairCost").val(data["estimatedRepairCost"]);
			$("#negotiatedRepairCost").val(data["negotiatedRepairCost"]);
			$("#damage").val(data["damage"]);
			$("#fileupload").prop("disabled", true);
			$("#fileupload").prop("required", false);
			$("#fileupload").parent().parent().attr("hidden", true);
			$("#docToView").val(data["fileupload"]);
			$("#myModal").modal('show');
        } else if (this.textContent.indexOf("Upload/View Claim") > -1) {
            $("#name").val(data['name']);
            $("#replaceID").val(data['id']);
            $("#docToView").val(data['fileupload']);
            $("#divUploadPolicyFile").hide();
            $("#btnSave").hide();
            $("#viewPolicy").modal('show');
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
	});

	$("#btnReplace").unbind('click').bind('click',function(){
	    $("#divUploadClaimFile").show();
        $("#btnSave").show();
	});

	$("#btnView").unbind('click').bind('click',function(){
	    window.open( '<?php echo HTTP_HOST."/viewdocument" ?>?filename=' + $("#docToView").val() + '&entityID=' + $("#entityID").val(), '_blank');
	    /*
	    url = '<?php echo HTTP_HOST."/viewclaim" ?>';
        type = "POST";
        data = {'filename': $('#docToView').val(), 'entityID': $("#entityID").val()};
        $.ajax({
            url : url,
            type : type,
            data : JSON.stringify(data),
            contentType: "application/json",
            success : function(data) {

            }
        });
        */
	});

	$("#btnSave").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates
	    replaceDocument();
	    return false;
	});


</script>

<?php

} else {

?>

<script>

        function openPolicies(){
            $("#business-list").css("display", "none");
            $("#business-contacts").css("display", "block");
        }


        function closePolicies(){
            var businesstable = $("#business-datatable-table").DataTable();
            $("#business-contacts").css("display", "none");
            $("#business-list").css("display", "block");
            businesstable.ajax.reload();
        }

        loadBusinessTableAJAX();

        $('.datepicker').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "yyyy-mm-dd"
        });


        $('#business-datatable-table tbody').off('click').on( 'click', 'button', function () {
            var businesstable = $("#business-datatable-table").DataTable();
            var data = businesstable.row( $(this).parents('tr') ).data();

            var entityID = data["id"];
            $("#entityID").val(data["id"]);

            loadBusinessInsurance(entityID);
            openPolicies();
        });


        $("#addClaim").click(function(){
              $("#id").val('');
              $("#entityID").val($("#entityID").val());
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
                $("#entityID").val($("#entityID").val());
                $("#name").val(data["name"]);
                $("#contactName").val(data["contactName"]);
                $("#contactEmail").val(data["contactEmail"]);
                $("#contactPhone").val(data["contactPhone"]);
                $("#policyNumber").val(data["policyNumber"]);
                $("#fileupload").prop("disabled", true);
                $("#fileupload").prop("required", false);
                $("#fileupload").parent().parent().attr("hidden", true);
                $("#policyExpirationDate").val(data["policyExpirationDate"]);
                $("#docToView").val(data["fileupload"]);
                $("#myModal").modal('show');
            } else if (this.textContent.indexOf("Upload/View Claim") > -1) {
                $("#replaceID").val(data['id']);
                $("#docToView").val(data['fileupload']);
                $("#divUploadPolicyFile").hide();
                $("#btnSave").hide();
                $("#viewPolicy").modal('show');
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

        $("#btnReplace").unbind('click').bind('click',function(){
            $("#divUploadPolicyFile").show();
            $("#btnSave").show();
        });

        $("#btnView").unbind('click').bind('click',function(){
            window.open( '<?php echo HTTP_HOST."/viewdocument" ?>?filename=' + $("#docToView").val() + '&entityID=' + $("#entityID").val(), '_blank');
            /*
            url = '<?php echo HTTP_HOST."/viewclaim" ?>';
            type = "POST";
            data = {'filename': $('#docToView').val(), 'entityID': $("#entityID").val()};
            $.ajax({
                url : url,
                type : type,
                data : JSON.stringify(data),
                contentType: "application/json",
                success : function(data) {

                }
            });
            */
        });

        $("#btnSave").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates
            replaceDocument();
            return false;
        });

 </script>

<?php
}
?>
