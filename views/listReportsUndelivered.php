<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

 ?>

 <script>

      function loadTableAJAX() {

        url = '<?php echo HTTP_HOST."/getundeliveredtrailers" ?>';

        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                type: 'POST',
                dataSrc: 'order_details'
            },
            columns: [
                { data: "orderID" },
                { data: "customerName"},
                { data: "carrierName"},
                { data: "unitNumber" },
                { data: "vinNumber" },
                { data: null,
                        "bSortable": true,
                        "mRender" : function(o) {
                            if (o.city) {
                                var location = o.city + ', ' + o.state;
                            } else {
                                var location = '';
                            }

                            return location;
                        }
                },
                { data: "statusesstatus"}
            ]
          });

          example_table.buttons().container().appendTo( $('.col-sm-6:eq(0)', example_table.table().container() ) );

          //To Reload The Ajax
          //See DataTables.net for more information about the reload method
          example_table.ajax.reload();

      }

 </script>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li>Reporting</li>
   <li class="active">Undelivered Relays</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Undelivered Trailer Relays</span></h4>
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
         <button type="button" id="downloadCSVButton" class="btn btn-primary pull-right">Download CSV</button>
         <a id="downloadCSV"></a>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover" width="100%">
                 <thead>
                 <tr>
                     <th class="hidden-sm-down">Order ID</th>
                     <th class="hidden-sm-down text-nowrap" width="20%">Customer</th>
                     <th class="hidden-sm-down text-nowrap" width="20%">Carrier</th>
                     <th class="hidden-sm-down text-nowrap">Unit Number</th>
                     <th class="hidden-sm-down text-nowrap">VIN</th>
                     <th class="hidden-sm-down text-nowrap">Last Location</th>
                     <th class="hidden-sm-down">Trailer Status</th>
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

    // We have to handle downloading the report as a .csv file
    $("#downloadCSVButton").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates

        downloadTemplateClick();

    });

    function downloadTemplateClick() {

            url = '<?php echo HTTP_HOST."/getundeliveredtrailerscsv" ?>';

            $.ajax({
                url: url,
                type: "POST",
                contentType: "application/json",
                responseType: "arraybuffer",
                success: function(data){
                    var element = document.createElement('a');
                    element.setAttribute('href', "data:application/octet-stream;charset=utf-8;base64,"+btoa(data.replace(/\n/g, '\r\n')));
                    element.setAttribute('download', "carrier_needs_bulk_import_template.csv");
                    //$("#downloadTemplate").attr("href","data:application/octet-stream;charset=utf-8;base64,"+btoa(data.replace(/\n/g, '\r\n')));
                    //$("#downloadTemplate").attr("download","carrier_needs_bulk_import_template.csv");
                    document.body.appendChild(element);
                    element.click();
                    document.body.removeChild(element);
                },
                error: function() {
                    alert('Failed to Download Undelivered Trailers Report');
                }
            });

    }


 </script>
