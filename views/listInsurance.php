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
			// file upload
			if ($("#id").val() > '') {
				console.log("editing");
				url = '<?php echo API_HOST."/api/insurance_carriers" ?>/' + $("#id").val();
				type = "PUT";
				var files = $('#fileupload').prop("files");
				var fileNames = $.map(files, function(val) { return val.name; }).join(',');
				date = today;
				data = {id: $("#id").val(), entityID: $("#entityID").val(), name: $("#name").val(), contactName: $("#contactName").val(), contactEmail: $("#contactEmail").val(), contactPhone: $("#contactPhone").val(), policyNumber: $("#policyNumber").val(), policyExpirationDate: $("#policyExpirationDate").val(), updatedAt: date};
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
							$("#fileupload").val('');
							passValidation = true;
						} else {
							alert("Adding Insurance Failed!");
						}
					},
					error: function() {
						alert("There Was An Error Adding Insurance!");
					}
				});
			} else {
				console.log("creating");
				url = '<?php echo HTTP_HOST."/uploaddocument" ?>';
				type = "POST";
				var formData = new FormData();
				formData.append('entityID', $("#entityID").val());
				formData.append('name', $("#name").val());
				formData.append('documentID', "insurance");
				formData.append('updatedAt', date);
				formData.append('fileupload', $('#fileupload')[0].files[0]);
				$.ajax({
					url : url,
					type : 'POST',
					data : formData,
					processData: false,  // tell jQuery not to process the data
					contentType: false,  // tell jQuery not to set contentType
					success : function(data) {
						console.log('file uploaded');
						// update listInsurance
						url = '<?php echo API_HOST."/api/insurance_carriers" ?>';
						type = "POST";
						var files = $('#fileupload').prop("files");
						var fileNames = $.map(files, function(val) { return val.name; }).join(',');
						date = today;
						data = {fileupload: fileNames, entityID: $("#entityID").val(), name: $("#name").val(), contactName: $("#contactName").val(), contactEmail: $("#contactEmail").val(), contactPhone: $("#contactPhone").val(), policyNumber: $("#policyNumber").val(), policyExpirationDate: $("#policyExpirationDate").val(), createdAt: date};
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
									$("#fileupload").val('');
									passValidation = true;
								} else {
									alert("Adding Insurance Failed!");
								}
							},
							error: function() {
								alert("There Was An Error Adding Insurance!");
							}
						});
						console.log('listInsurance updated');
					}
				});
			}
			return passValidation;
		} else {
			return false;
		}
	}
	function loadTableAJAX() {
		myApp.showPleaseWait();
		var url = '<?php echo API_HOST; ?>' + '/api/insurance_carriers?columns=id,name,link,contactName,contactEmail,contactPhone,policyNumber,policyExpirationDate,fileupload,status&filter=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&order=name&transform=1';
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
				{ data: "contactEmail" },
				{ data: "contactPhone" },
				{ data: "policyNumber" },
				{ data: "policyExpirationDate", visible: false },
				{ data: "fileupload" },
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
					<th class="hidden-sm-down">Contact Email</th>
					<th class="hidden-sm-down">Contact Phone</th>
					<th class="hidden-sm-down">Policy Number</th>
					<th class="hidden-sm-down">Policy Expiration Date</th>
					<th class="hidden-sm-down">Policy File</th>
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
				<form id="formInsurance" class="register-form mt-lg" action="<?php echo HTTP_HOST."/uploaddocument" ?>" method="POST" enctype="multipart/form-data">
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
						<div class="col-sm-6">
							<label for="fileupload">Policy File Upload</label>
							<div class="form-group">
								<input type="file" id="fileupload" name="fileupload" class="form-control mb-sm" placeholder="*Policy Number" required="required" />
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
		$("#contactEmail").val('');
		$("#contactPhone").val('');
		$("#policyNumber").val('');
		$("#policyExpirationDate").val('');
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
			$("#name").val(data["name"]);
			$("#contactName").val(data["contactName"]);
			$("#contactEmail").val(data["contactEmail"]);
			$("#contactPhone").val(data["contactPhone"]);
			$("#policyNumber").val(data["policyNumber"]);
			$("#fileupload").prop("disabled", true);
			$("#fileupload").prop("required", false);
			$("#fileupload").parent().parent().attr("hidden", true);
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
	});
</script>