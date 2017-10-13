<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

try {

      $entityargs = array(
          "transform"=>1
      );
      $entityurl = API_HOST_URL . "/entities/".$_SESSION['entityid']."?".http_build_query($entityargs);
      $entityoptions = array(
          'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'GET'
          )
      );
      $entitycontext  = stream_context_create($entityoptions);
      $entityresult = json_decode(file_get_contents($entityurl,false,$entitycontext),true);

      if (count($entityresult) > 0) {
        $towAwayRate = number_format($entityresult['towAwayRate'],2);
        $loadOutRate = number_format($entityresult['loadOutRate'],2);
        $loadOutRateType = $entityresult['loadOutRateType'];
        if ($loadOutRateType == "Flat Rate") {
            $lorfrchecked = "checked";
            $lormchecked = "";
        } else {
            $lorfrchecked = "";
            $lormchecked = "checked";
        }
      } else {
        return false;
      }
} catch (Exception $e) { // The authorization query failed verification
      header('HTTP/1.1 404 Not Found');
      header('Content-Type: text/plain; charset=utf8');
      echo $e->getMessage();
      exit();
}



?>

<script>

function verifyAndPost() {

  if ( $('#formBusiness').parsley().validate() ) {
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
              var url = '<?php echo API_HOST_URL . "/entities" ?>/' + $("#id").val();
              type = "PUT";
          } else {
              var url = '<?php echo API_HOST_URL . "/entities" ?>';
              type = "POST";
          }

          if (type == "PUT") {
              var date = today;
              var data = {towAwayRate: $("#towAwayRate").val(), loadOutRateType: $("input[name='loadOutRateType']:checked").val(), loadOutRate: $("#loadOutRate").val(), updatedAt: date};
          } else {
              //This should never happen
              //var date = today;
              //var data = {entityID: $("#entityID").val(), towAwayRate: $("#towAwayRate").val(), loadOutRateType: $("input[name='loadOutRateType']:checked").val(), load_out_rate: $("#loadOutRate").val(), createdAt: date};
          }

          $.ajax({
             url: url,
             type: type,
             data: JSON.stringify(data),
             contentType: "application/json",
             async: false,
             success: function(data){
                if (data > 0) {
                  //alert("Rate Information Updated Successfully!");
                  window.location.replace("<?php echo HTTP_HOST; ?>");
                  passValidation = true;
                } else {
                  alert("Updating Rate Information Failed!");
                }
             },
             error: function() {
                alert("There Was An Error Adding Rates!");
             }
          });

          return passValidation;

    } else {

          return false;

    }

}

</script>
<ol class="breadcrumb">
    <li>ADMIN</li>
    <li class="active">Rates Maintenance</li>
</ol>
<div class="row">
    <div class="col-lg-6 col-md-offset-2">
        <section class="widget">
            <div class="widget-body">
              <form id="formBusiness" class="register-form mt-lg">
                <input type="hidden" id="id" name="id" value="<?php echo $_SESSION['entityid']; ?>" />
                <div class="row">
                    <div class="col-sm-4">
                        <label for="towAwayRate">Tow Away Rate</label>
                        <div class="form-group">
                          <input type="text" id="towAwayRate" name="towAwayRate" class="form-control mb-sm" value="<?php echo $towAwayRate; ?>" placeholder="Tow Away Rate $" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="negotiatedRate">Load Out Rate</label>
                        <div class="form-group">
                          <input type="text" id="loadOutRate" name="loadOutRate" class="form-control mb-sm" value="<?php echo $loadOutRate; ?>" placeholder="Load Out Rate $" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="negotiatedRate">Load Out Rate Type</label>
                        <div class="form-group" style="align: middle">
                          <input type="radio" id="loadOutRateType" name="loadOutRateType" value="Flat Rate" <?php echo $lorfrchecked; ?> /> Flat Rate
                          &nbsp;&nbsp;
                          <input type="radio" id="loadOutRateType" name="loadOutRateType" value="Mileage" <?php echo $lormchecked; ?> /> Mileage
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" onclick="return verifyAndPost();">Save Changes</button>
                </div>
              </form>
            </div>
        </section>
    </div>
</div>
