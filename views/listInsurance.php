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
		var ext = $(file).val().split(".");
		ext = ext[ext.length-1].toLowerCase();
		var arrayExtensions = ["jpg" , "jpeg", "png", "bmp", "gif", "pdf", "zip", "doc", "docx"];
		if (arrayExtensions.lastIndexOf(ext) == -1) {
			alert("File must be one of the following valid types; jpg, jpeg, png, bmp, gif, pdf, zip, doc or docx.");
			$(file).val("");
			$(file).focus();
			return false;
		} else {
			return true;
		}
	}

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
			var date = today;
			// file upload
			if ($("#id").val() > '') {
				url = '<?php echo API_HOST_URL . "/insurance_carriers" ?>/' + $("#id").val();
				type = "PUT";
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

				url = '<?php echo HTTP_HOST."/uploaddocument" ?>';
				type = "POST";
				var formData = new FormData();
				formData.append('fileupload', $('#fileupload')[0].files[0]);
				formData.append('entityID', $("#entityID").val());
				formData.append('name', $("#name").val());
				$.ajax({
					url : url,
					type : 'POST',
					data : formData,
					processData: false,  // tell jQuery not to process the data
					contentType: false,  // tell jQuery not to set contentType
					success : function(data) {
						// update listInsurance
						url = '<?php echo API_HOST_URL . "/insurance_carriers" ?>';
						type = "POST";
						var files = $('#fileupload').prop("files");
						var fileNames = $.map(files, function(val) { return val.name; }).join(',');
						date = today;
						data = {fileupload: fileNames, entityID: $("#entityID").val(), name: $("#name").val(), contactName: $("#contactName").val(), contactEmail: $("#contactEmail").val(), contactPhone: $("#contactPhone").val(), policyNumber: $("#policyNumber").val(), policyExpirationDate: $("#policyExpirationDate").val(), createdAt: date, updatedAt: date};
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
        $.ajax({
            url : url,
            type : 'POST',
            data : formData,
            processData: false,  // tell jQuery not to process the data
            contentType: false,  // tell jQuery not to set contentType
            success : function(data) {
                // update listInsurance
                url = '<?php echo API_HOST_URL . "/insurance_carriers/" ?>' + $("#replaceID").val();
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
                            alert("Updating Insurance Policy Document Failed!");
                        }
                    },
                    error: function() {
                        alert("There Was An Error Updating Insurance Document!");
                    }
                });
                console.log('listInsurance updated');
            },
            error: function() {
                alert("Failed");
            }
        });
	}

	function loadTableAJAX() {
		var url = '<?php echo API_HOST_URL; ?>' + '/insurance_carriers?columns=id,name,link,contactName,contactEmail,contactPhone,policyNumber,policyExpirationDate,fileupload,status&filter=entityID,eq,' + <?php echo $_SESSION['entityid']; ?> + '&order=name&transform=1';
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
				{ data: "policyNumber", visible: false },
				{ data: "policyExpirationDate", visible: false },
				{ data: null,
                    "bSortable": false,
					"mRender": function (o) {
					    var buttons = '';
                        if (o.fileupload > '') {
                            buttons += '<button class=\"btn btn-primary btn-xs\" role=\"button\"><i class=\"glyphicon glyphicon-eye-open text\"></i> <span class=\"text\">Upload/View Policy</span></button> &nbsp;';
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
		var url = '<?php echo API_HOST_URL . "/insurance_carriers" ?>/' + $("#id").val();
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

	function viewDocumentHold() {
		window.open($("#docToView").val(), '_blank', 'location=yes,height=570,width=520,scrollbars=yes,status=yes');
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
								<div id="sandbox-container" class="input-group date datepicker">
									<input type="text" id="policyExpirationDate" name="policyExpirationDate" class="form-control" placeholder="Policy Expiration Date"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<label for="fileupload">Policy File Upload</label>
							<div class="form-group">
								<input type="file" id="fileupload" name="fileupload" class="form-control mb-sm" placeholder="*Policy Number" required="required" data-filesize="20000000"
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
<div class="modal fade" id="viewPolicy" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="enableDialogLabel">View Policy</h5>
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
                        <label for="updatePolicyFile">Update Policy File</label>
                        <div class="form-group">
                            <input type="file" id="updatePolicyFile" name="updatePolicyFile" class="form-control mb-sm" placeholder="Update Policy" data-filesize="20000000"
                            data-filetype="image/bmp,image/gif,image/jpeg,application/zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/rtf"
                            onchange="validateFile(this);" />
                        </div>
                    </div>
                    <div class="col-md-5 pull-right">
                        <label for="btnView">&nbsp;</label>
                        <div class="form-group">
                            <button type="button" class="btn btn-primary" id="btnReplace">Upload/View Policy</button>
                            <button type="button" class="btn btn-primary" id="btnView">View Policy</button>
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
			$("#docToView").val(data["fileupload"]);
			$("#myModal").modal('show');
        } else if (this.textContent.indexOf("Upload/View Policy") > -1) {
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
	    $("#divUploadPolicyFile").show();
        $("#btnSave").show();
	});

	$("#btnView").unbind('click').bind('click',function(){
	    window.open( '<?php echo HTTP_HOST."/viewdocument" ?>?filename=' + $("#docToView").val() + '&entityID=' + $("#entityID").val(), '_blank');
	    /*
	    url = '<?php echo HTTP_HOST."/viewpolicy" ?>';
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
