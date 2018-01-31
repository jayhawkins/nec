<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

 ?>

 <script>

      function loadTableAJAX() {

        if ($("#startDate").val() > '') {
            // Do nothing - use the dates selected
        } else {
            var today = new Date();
            var yyyy = today.getFullYear();
            var dd = today.getDate();
            var mm = today.getMonth()+1; //January is 0!

            var month = today.getMonth();
            var enddate = new Date(yyyy, month + 1, 0);
            var enddd = enddate.getDate();

            if(dd<10) {
                dd='0'+dd;
            }

            if(mm<10) {
                mm='0'+mm;
            }

            if(enddd<10) {
                enddd='0'+enddd;
            }

            //today = mm+'/'+dd+'/'+yyyy;
            //endday = mm+'/'+enddd+'/'+yyyy;
            today = yyyy+'-'+mm+'-'+dd;
            endday = yyyy+'-'+mm+'-'+enddd;
            $("#startDate").val(today);
            $("#endDate").val(endday);
            console.log('in here');
        }
console.log($("#startDate").val());
        url = '<?php echo HTTP_HOST."/getardetail" ?>';
        var params = {
                      startDate: $("#startDate").val(),
                      endDate: $("#endDate").val()
                };

        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                type: 'POST',
                data: params,
                dataSrc: 'approved_pod'
            },
            columns: [
                { data: "orderID" },
                { data: "customerName" },
                { data: "carrierName" },
                { data: "costToCustomer" },
                { data: "costToCarrier" },
                { data: "qbInvoiceNumber" },
                { data: "qbStatus" }
            ]
          });

          example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );

          //To Reload The Ajax
          //See DataTables.net for more information about the reload method
          example_table.ajax.reload();

      }

 </script>

 <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li>Reporting</li>
   <li class="active">A/R Summary Detail</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">A/R Summary Detail</span></h4>
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
         <div class="col-sm-12">
            <div id="sandbox-container" class="input-group col-sm-3 date datepicker">
               <input type="text" id="startDate" name="startDate" class="form-control" placeholder="Start Date" required="required"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
            </div>
            <div id="sandbox-container" class="input-group col-sm-3 date datepicker">
               <input type="text" id="endDate" name="endDate" class="form-control" placeholder="End Date" required="required"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
            </div>
            <div class="form-group col-sm-6">
                <button type="button" id="downloadCSVButton" class="btn btn-primary pull-right">Download CSV</button>
            </div>
         </div>
         <a id="downloadCSV"></a>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover" width="100%">
                 <thead>
                 <tr>
                     <th class="hidden-sm-down">Order ID</th>
                     <th class="hidden-sm-down">Customer Name</th>
                     <th class="hidden-sm-down text-nowrap">Carrier Name</th>
                     <th class="hidden-sm-down">Cost to Customer</th>
                     <th class="hidden-sm-down">Cost to Carrier</th>
                     <th class="hidden-sm-down text-nowrap">QB Invoice #</th>
                     <th class="hidden-sm-down text-nowrap">QB Status</th>
                 </tr>
                 </thead>
                 <tbody>
                      <!-- loadTableAJAX() is what populates this area -->
                 </tbody>
             </table>
         </div>
     </div>
 </section>

 <script>

    loadTableAJAX();

    $('.datepicker').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });

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

    // We have to reload when dates change
    $("#startDate").unbind('change').bind('change',function(){ // Doing it like this because it was double posting document giving me duplicates

        loadTableAJAX();

    });

    // We have to reload when dates change
    $("#endDate").unbind('change').bind('change',function(){ // Doing it like this because it was double posting document giving me duplicates

        loadTableAJAX();

    });

    // We have to handle downloading the report as a .csv file
    $("#downloadCSVButton").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates

        downloadTemplateClick();

    });

    function downloadTemplateClick() {

            url = '<?php echo HTTP_HOST."/getardetailcsv" ?>';
            var params = {
                      startDate: $("#startDate").val(),
                      endDate: $("#endDate").val()
            };

            $.ajax({
                url: url,
                type: "POST",
                contentType: "application/json",
                responseType: "arraybuffer",
                data: JSON.stringify(params),
                success: function(data){
                    var element = document.createElement('a');
                    element.setAttribute('href', "data:application/octet-stream;charset=utf-8;base64,"+btoa(data.replace(/\n/g, '\r\n')));
                    element.setAttribute('download', "ar_summary_detail_report.csv");
                    //$("#downloadTemplate").attr("href","data:application/octet-stream;charset=utf-8;base64,"+btoa(data.replace(/\n/g, '\r\n')));
                    //$("#downloadTemplate").attr("download","ar_summary_detail_report.csv");
                    document.body.appendChild(element);
                    element.click();
                    document.body.removeChild(element);
                },
                error: function() {
                    alert('Failed to Download A/R Summary Detail Report');
                }
            });

    }


 </script>
