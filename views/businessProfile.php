<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

$states = json_decode(file_get_contents(API_HOST_URL . '/states?columns=abbreviation,name&order=name'));

try {
    $result = json_decode(file_get_contents(API_HOST_URL . '/entities?include=members,users,locations,contacts&filter=id,eq,'.$_SESSION['entityid']),true);
      $result = php_crud_api_transform($result);

      if (count($result) > 0) {

        $entityName = $result['entities'][0]['name'];

        $configuration_settings = json_decode($result['entities'][0]['configuration_settings'],true);

        foreach($result['entities'][0]['locations'] as $location) {
                if ($location['locationTypeID'] == 1) {
                  $address1 = $location['address1'];
                  $address2 = $location['address2'];
                  $city = $location['city'];
                  $state = $location['state'];
                  $zip = $location['zip'];
                }
        }

        foreach($result['entities'][0]['contacts'] as $contact) {
                if ($contact['contactTypeID'] == 1) {
                  $firstName = $contact['firstName'];
                  $lastName = $contact['lastName'];
                  $title = $contact['title'];
                  $email = $contact['emailAddress'];
                  $phone = $contact['primaryPhone'];
                  $phoneExt = $contact['secondaryPhone'];
                  $fax = $contact['fax'];
                }
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

// Get Configuration Settings for Availability or Needs
$cdpvargs = array(
    "include"=>"configuration_data_point_values",
    "filter[0]"=>"id,eq,".$_SESSION['entitytype'],
    "filter[1]"=>"configuration_data_point_values.status,eq,Active",
    "transform"=>1
);
$cdpvurl = API_HOST_URL . "/configuration_data_points?".http_build_query($cdpvargs);
$cdpvoptions = array(
  'http' => array(
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
      'method'  => 'GET'
  )
);
$cdpvcontext  = stream_context_create($cdpvoptions);
$cdpvresult = json_decode(file_get_contents($cdpvurl,false,$cdpvcontext),true);

$cdpvList = $cdpvresult["configuration_data_points"];

?>

<script>

function verifyInput() {

      // Build the configuration_settings data
      var settingsarray = [];
      var obj = $("#dp-check-list-box li select");
      for (var i = 0; i < obj.length; i++) {
          item = {};
          item[obj[i].id] = obj[i].value;
          settingsarray.push(item);
      }

      $("#configuration_settings").val(JSON.stringify(settingsarray));

      return true;
}

</script>

<ol class="breadcrumb">
    <li>ADMIN</li>
    <li class="active">Business Maintenance</li>
</ol>
<div class="row">
    <div class="col-md-12 col-md-offset-0">
        <section class="widget">
            <div class="widget-body">
              <form id="formRegister" class="register-form mt-lg" method="POST" action="/entities" onsubmit="return verifyInput();">
                <input type="hidden" id="configuration_settings" name="configuration_settings" />
                <div class="row">
                    <div class="col-sm-4">
                        <label for="firstName">First Name</label>
                        <div class="form-group">
                          <input type="text" class="form-control" id="firstName" name="firstName" placeholder="*First Name" value="<?php echo $firstName; ?>"
                                  required="required" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="lastName">Last Name</label>
                        <div class="form-group">
                          <input type="text" id="lastName" name="lastName" class="form-control" placeholder="*Last Name" value="<?php echo $lastName; ?>"
                                 required="required" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                      <label for="title">Title</label>
                      <div class="form-group">
                        <input type="text" id="title" name="title" class="form-control" placeholder="Title" value="<?php echo $title; ?>" />
                      </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label for="entityName">Company Name</label>
                        <div class="form-group">
                          <input type="text" id="entityName" name="entityName" class="form-control" placeholder="*Company Name" value="<?php echo $entityName; ?>"
                                 required="required" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="address1">Address 1</label>
                        <div class="form-group">
                          <input type="text" id="address1" name="address1" class="form-control mb-sm" placeholder="Company Address" value="<?php echo $address1; ?>" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="address2">Suite # / Apt #</label>
                        <div class="form-group">
                          <input type="text" id="address2" name="address2" class="form-control mb-sm" placeholder="Bldg. Number/Suite" value="<?php echo $address2; ?>" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label for="city">City</label>
                        <div class="form-group">
                          <input type="text" id="city" name="city" class="form-control" placeholder="*City" value="<?php echo $city; ?>"
                                 required="required" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="state">State</label>
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
                        <label for="zip">Zip</label>
                        <div class="form-group">
                          <input type="text" id="zip" name="zip" class="form-control mb-sm" placeholder="Zip" value="<?php echo $zip; ?>" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label for="phone">Phone</label>
                        <div class="form-group">
                           <div class="col-sm-7" style="padding-left: 0; padding-right: 0">
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="*Phone" value="<?php echo $phone; ?>" required="required" />
                           </div>
                           <div class="col-sm-5" style="padding-right: 0;">
                              <input type="text" maxlength="15" id="phoneExt" name="phoneExt" class="form-control" placeholder="Ext" value="<?php echo $phoneExt; ?>" />
                           </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="fax">Fax</label>
                        <div class="form-group">
                            <input type="text" id="fax" name="fax" class="form-control" placeholder="Fax" value="<?php echo $fax; ?>" />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="email">Email Address</label>
                        <div class="form-group">
                          <input type="email" id="email" name="email" class="form-control" placeholder="*Email Address" value="<?php echo $email; ?>"
                                 data-parsley-trigger="change"
                                 required="required" />
                        </div>
                    </div>
                </div>
                <hr />
                <!--div class="container" style="margin-top:20px;"-->
                     <div class="row">
                       <div class="col-xs-2">
                            <h5 class="text-center"><strong>Configuration Settings</strong></h5>
                            <div class="well" style="max-height: 200px;overflow: auto;">
                                <ul id="dp-check-list-box" class="list-group">
<?php
                                for ($i = 0; $i < count($cdpvList); $i++) {

                                    // Get the saved value for the configuration setting column name
                                    $selectedValue = "";
                                    for ($cs = 0; $cs < count($configuration_settings); $cs++) {
                                        while (list($key, $val) = each($configuration_settings[$cs])) {
                                            //echo "$key => $val\n";
                                            if ($key == $cdpvList[$i]['columnName']) {
                                                $selectedValue = $val;
                                            }
                                        }
                                    }

                                    echo "<li>". $cdpvList[$i]['title'] . "\n";
                                    echo "<select class=\"form-control mb-sm\" id=\"" . $cdpvList[$i]['columnName'] . "\" name=\"" . $cdpvList[$i]['columnName'] . "\">\n
                                            <option value=\"\" selected=selected>-Select From List-</option>\n";

                                    foreach($cdpvList[$i]['configuration_data_point_values'] as $value) {
                                        $selected = ($value['value'] == $selectedValue) ? 'selected=selected':'';
                                        echo "<option value=" .$value['value']. " " . $selected . ">" . $value['title'] . "</option>\n";
                                    }

                                    echo "</select>\n
                                          </li>\n";

                                }
?>
                                </ul>
                            </div>
                       </div>
                     </div>
                <!--/div-->
                <div class="clearfix">
                    <div class="btn-toolbar pull-left">
                      &nbsp;
                    </div>
                    <div>&nbsp;</div>
                    <div class="btn-toolbar pull-right">
                      <input type="submit" name="register" class="btn btn-inverse btn-sm" value="Save" />
                    </div>
                </div>
              </form>
            </div>
        </section>
    </div>
</div>
