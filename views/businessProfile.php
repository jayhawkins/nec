<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';

$states = json_decode(file_get_contents(API_HOST_URL . '/states?columns=abbreviation,name&order=name'));

try {
    $result = json_decode(file_get_contents(API_HOST_URL . '/entities?include=members,users,locations,contacts&filter=id,eq,'.$_SESSION['entityid']),true);
      $result = php_crud_api_transform($result);
      //$result = json_encode($result);
      //print_r($result);
      //die();
      if (count($result) > 0) {

        $entityName = $result['entities'][0]['name'];

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



?>
<ol class="breadcrumb">
    <li>ADMIN</li>
    <li class="active">Business Maintenance</li>
</ol>
<div class="row">
    <div class="col-lg-12 col-md-offset-0">
        <section class="widget">
            <div class="widget-body">
              <form id="formRegister" class="register-form mt-lg" method="POST" action="/entities" onsubmit="return verifyInput();">
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
                    <div class="col-sm-8">
                        <label for="entityName">Company Name</label>
                        <div class="form-group">
                          <input type="text" id="entityName" name="entityName" class="form-control" placeholder="*Company Name" value="<?php echo $entityName; ?>"
                                 required="required" />
                        </div>
                    </div>
                    <div class="col-sm-2">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <label for="address1">Address 1</label>
                        <div class="form-group">
                          <input type="text" id="address1" name="address1" class="form-control mb-sm" placeholder="Company Address" value="<?php echo $address1; ?>" />
                        </div>
                    </div>
                    <div class="col-sm-6">
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
