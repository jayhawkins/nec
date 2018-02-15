<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

 ?>

 <script>

      function loadTableAJAX() {

        var url = '<?php echo HTTP_HOST."/gettrends" ?>';
        var params = {entitytype: <?php echo $_SESSION['entitytype'] ?>,
                      entityid: <?php echo $_SESSION['entityid'] ?>};

        var example_table = $('#datatable-table').DataTable({
            retrieve: true,
            processing: true,
            ajax: {
                url: url,
                type: 'POST',
                data: function(d) {
                    d.entitytype = <?php echo $_SESSION['entitytype'] ?>;
                    d.entityid = <?php echo $_SESSION['entityid'] ?>;
                    d.trendEntityType = $('input[name="trendEntityType"]:checked').val();
                    d.timeFrame = $('input[name="timeFrame"]:checked').val();
                    return;
                },
                dataSrc: "customer_needs",
            },
            columns: [
                { data: "originationCity" },
                { data: "originationState" },
                /*
                {
                    data: null,
                    "bSortable": true,
                    "mRender": function(o) {
                      if (o.availableDate == "0000-00-00") {
                          return '';
                      } else {
                          return formatDate(new Date(o.availableDate)); // Use the formatDate from common.js to display Month, Day Year on listing
                      }
                    }
                }
                */
                { data: "qty" }
            ]
          })
          .on('xhr.dt', function ( e, settings, json, xhr ) {
            $("#recordCount").html('Total Outstanding Availability: ' + example_table.rows().length);
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
   <li class="active">Availability/Needs Trends</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Availability/Needs Trends</span></h4>
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
         <div class="btn btn-danger" id="recordCount"></div>
         <button type="button" id="downloadCSVButton" class="btn btn-primary pull-right">Download CSV</button>
         <div class="pull-right">
             <span class="fw-semi-bold">Trends For: &nbsp;</span>
             <input type="radio" id="trendEntityType" name="trendEntityType" value="Customers" checked> Customers
             &nbsp;&nbsp;
             <input type="radio" id="trendEntityType" name="trendEntityType" value="Carriers"> Carriers
             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
             <span class="fw-semi-bold">Timeframe: &nbsp;</span>
             <input type="radio" id="timeFrame" name="timeFrame" value="Yearly"> Yearly
             &nbsp;&nbsp;
             <input type="radio" id="timeFrame" name="timeFrame" value="Quarterly"> Quarterly
             &nbsp;&nbsp;
             <input type="radio" id="timeFrame" name="timeFrame" value="Monthly" checked> Monthly
             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         </div>
         <a id="downloadCSV"></a>
         <br /><br />
         <div id="dataTable" class="mt">
             <table id="datatable-table" class="table table-striped table-hover" width="100%">
                 <thead>
                 <tr>
                     <th class="hidden-sm-down text-nowrap">Origin City</th>
                     <th class="hidden-sm-down text-nowrap">Origin State</th>
                     <th class="hidden-sm-down">Quantity</th>
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

    // We have to handle downloading the report as a .csv file
    $("#downloadCSVButton").unbind('click').bind('click',function(){ // Doing it like this because it was double posting document giving me duplicates
        downloadTemplateClick();
    });

    $("input[type=radio][name=timeFrame]").unbind('change').bind('change',function(){ // Doing it like this because it was double posting document giving me duplicates
        loadTableAJAX();
    });

    $("input[type=radio][name=trendEntityType]").unbind('change').bind('change',function(){ // Doing it like this because it was double posting document giving me duplicates
        loadTableAJAX();
    });

    function downloadTemplateClick() {

            url = '<?php echo HTTP_HOST."/gettrendscsv" ?>';

            var params = {entitytype: <?php echo $_SESSION['entitytype'] ?>,
                          entityid: <?php echo $_SESSION['entityid'] ?>,
                          timeFrame: $('input[name="timeFrame"]:checked').val(),
                          trendEntityType: $('input[name="trendEntityType"]:checked').val()
                         };
            console.log(params);

            $.ajax({
                url: url,
                type: "POST",
                data: JSON.stringify(params),
                contentType: "application/json",
                responseType: "arraybuffer",
                success: function(data){
                    var element = document.createElement('a');
                    element.setAttribute('href', "data:application/octet-stream;charset=utf-8;base64,"+btoa(data.replace(/\n/g, '\r\n')));
                    element.setAttribute('download', "customer_outstanding_availability.csv");
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
