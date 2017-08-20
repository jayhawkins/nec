<?php

//session_start();

require '../../nec_config.php';
require '../lib/common.php';
$quickbooks_oauth_url = "http://nec.dubtel.com/oauth";
?>

<script type="text/javascript" src="https://appcenter.intuit.com/Content/IA/intuit.ipp.anywhere.js"></script>
<script type="text/javascript">
    intuit.ipp.anywhere.setup({
        grantUrl: '<?php print($quickbooks_oauth_url); ?>'
    });
</script>

 

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Quickbooks Connection Status</li>
 </ol>
 <section class="widget">
     <header>
         <h4><span class="fw-semi-bold">Quickbooks</span></h4>
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
         <h1>Quickbooks online Connection Status</h1>
         <button type="button" id="addLink" class="btn btn-primary pull-xs-right" data-target="#myModal">Add Link</button>
         <br /><br />
         <div class="row">
             Connection Results:
         </div>
     </div>
 </section>

 

 


