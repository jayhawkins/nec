<!DOCTYPE html>
<html>
<head>
    <title>NEC - Register</title>
    <link href="css/application.min.css" rel="stylesheet">
    <!-- as of IE9 cannot parse css files with more that 4K classes separating in two files -->
    <!--[if IE 9]>
        <link href="css/application-ie9-part2.css" rel="stylesheet">
    <![endif]-->
    <link rel="stylesheet" href="vendor/select2/select2.css">
    <link rel="stylesheet" href="vendor/select2/select2-bootstrap.css">
    <link rel="stylesheet" href="vendor/font-awesome/css/font-awesome.min.css">
    <link rel="shortcut icon" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <script>
        /* yeah we need this empty stylesheet here. It's cool chrome & chromium fix
         chrome fix https://code.google.com/p/chromium/issues/detail?id=167083
         https://code.google.com/p/chromium/issues/detail?id=332189
         */

         function loadStates() {
            $.ajax({
               type: "GET",
               url: "<?php echo API_HOST_URL . '/states?columns=abbreviation,name&order=name&transform=1' ?>",
               cache: false,
               success: function(data){
                  var listitems = '';
                  var $select = $('#state');
                  $.each(data.states, function( index ){
                     //console.log(data.states[index].abbreviation);
                     listitems += '<option value=' + data.states[index].abbreviation + '>' + data.states[index].name + '</option>';
                  });
                  $select.append(listitems);
               },
               error: function() {
                  alert("Can't Get States for List");
               }
            });
         }
         
         function checkSpecialCharacter(password){
            var specialChars = "!@#*>";
            
            for(var i = 0; i < specialChars.length; i++){
                if(password.indexOf(specialChars[i]) > -1){
                    return true;
                }
            }
            return false;
         }
         
         function verifyPassword(){
            var password = $('#password').val();
            var checkNumber = new RegExp("\d");
            var checkWhitespace = new RegExp(" ");
            
            if(password.length < 8 || password.length > 32) return false;
            else if(!checkNumber.test(password)) return false;
            else if(checkWhitespace.test(password)) return false;
            else if(!checkSpecialCharacter(password)) return false;
            
            return true;
         }

         function verifyInput() {
             var passValidation = false;
             var url = '<?php echo API_HOST_URL . "/users?filter=username,eq,' + $('#email').val() + '&transform=1" ?>';
             $.ajax({
                type: "GET",
                url: url,
                async: false,
                success: function(data){
                   if (data.users.length > 0) {
                     alert('Email Already Exists in the System!');
                     //$("#dialogMessage").text('Email Already Exists in the System!');
                     //$("#dialog").dialog("open");
                   } else {
                       if(!verifyPassword()){
                            alert('The password does not meet the password policy requirements.');
                            //$("#dialogMessage").text('The password does not meet the password policy requirements.');
                            //$("#dialog").dialog("open");
                       }
                       else{
                            passValidation = true;
                       }
                   }
                },
                error: function() {
                   alert("Can't Get Email for Verification");
                }
             });
             return passValidation;
         }

    </script>
</head>
<body class="login-page">

<div class="container">
    <main id="content" class="widget-login-container" role="main">
        <div class="row">
            <!-- div class="col-xl-4 col-md-6 col-xs-10 col-xl-offset-4 col-md-offset-3 col-xs-offset-1" -->
            <div class="col-lg-10 col-lg-offset-1">
                <h5 class="widget-login-logo animated fadeInUp">
                    <img src="img/nec_logo.png" />
                </h5>
                <section class="widget widget-login animated fadeInUp">
                    <header>
                        <h3>Register</h3>
                        <hr />
                        <p>Please provide the following information to create a free online account to NEC's web portal. NEC makes it easy to manage
                           your business with us at any time and from any place! <i>(Required fields are marked with an asterisk *)</i>
                        </p>
                    </header>
<?php
                            if (!empty($errorMessage)) {
                                echo "<div class=\"alert alert-danger\" role=\"alert\">". $errorMessage . "</div>";
                            }
 ?>
                    <div class="widget-body">
                      <form id="formRegister" class="register-form mt-lg" method="POST" action="/register" onsubmit="return verifyInput();">
                        <div class="row">
                            <div class="col-sm-8">
                                <div class="form-group">
                                  <input type="radio" id="customer" name="entityTypeID" value="1" checked/> Customer&nbsp;&nbsp;
                                  <input type="radio" id="carrier" name="entityTypeID" value="2"/> Carrier
                                </div>
                            </div>
                            <div class="col-sm-2">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label for="rate">First Name</label>
                                <div class="form-group">
                                  <input type="text" class="form-control" id="firstName" name="firstName" placeholder="*First Name"
                                          required="required" />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="rate">Last Name</label>
                                <div class="form-group">
                                  <input type="text" id="lastName" name="lastName" class="form-control" placeholder="*Last Name"
                                         required="required" />
                                </div>
                            </div>
                            <div class="col-sm-4">
                              <label for="rate">Title</label>
                              <div class="form-group">
                                <input type="text" id="title" name="title" class="form-control" placeholder="Title" />
                              </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-8">
                                <label for="rate">Company Name</label>
                                <div class="form-group">
                                  <input type="text" id="entityName" name="entityName" class="form-control" placeholder="*Company Name"
                                         required="required" />
                                </div>
                            </div>
                            <div class="col-sm-2">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <label for="rate">Company Address</label>
                                <div class="form-group">
                                  <input type="text" id="address1" name="address1" class="form-control mb-sm" placeholder="Company Address" />
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="rate">Bldg./Suite #</label>
                                <div class="form-group">
                                  <input type="text" id="address2" name="address2" class="form-control mb-sm" placeholder="Bldg. Number/Suite" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label for="rate">City</label>
                                <div class="form-group">
                                  <input type="text" id="city" name="city" class="form-control" placeholder="*City"
                                         required="required" />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="rate">State</label>
                                <div class="form-group">
                                  <select id="state" name="state" data-placeholder="State" class="form-control chzn-select" required="required">
                                    <option value="">*Select State...</option>
                                  </select>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="rate">Zip</label>
                                <div class="form-group">
                                  <input type="text" id="zip" name="zip" class="form-control mb-sm" placeholder="Zip" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label for="rate">Phone</label>
                                <div class="form-group">
                                    <input type="text" id="phone" name="phone" class="form-control" placeholder="*Phone"
                                           required="required" />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="rate">Fax</label>
                                <div class="form-group">
                                    <input type="text" id="fax" name="fax" class="form-control" placeholder="Fax" />
                                </div>
                            </div>
                            <div class="col-sm-2">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label for="rate">Email Address</label>
                                <div class="form-group">
                                  <input type="email" id="email" name="email" class="form-control" placeholder="*Email Address"
                                         data-parsley-trigger="change"
                                         required="required" />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="rate">Confirm Email Address</label>
                                <div class="form-group">
                                  <input type="email" id="email2" name="email_templates2" class="form-control" placeholder="*Confirm Email"
                                         data-parsley-trigger="change"
                                         data-parsley-equalto="#email"
                                         required="required" />
                                </div>
                            </div>
                            <div class="col-sm-2">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label>Password   <span  id="lblPassword" class="fa fa-info-circle"></span></label>
                                <div class="form-group">
                                  <input type="password" id="password" name="password" class="form-control" placeholder="*Password"
                                         data-parsley-trigger="change"
                                         data-parsley-minlength="6"
                                         required="required" title=""/>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="rate">Confirm Password</label>
                                <div class="form-group">
                                  <input type="password" id="password2" name="password2" class="form-control" placeholder="*Confirm Password"
                                         data-parsley-trigger="change"
                                         data-parsley-minlength="6"
                                         data-parsley-equalto="#password"
                                         required="required" />
                                </div>
                            </div>
                            <div class="col-sm-2">
                            </div>
                        </div>
                        <div class="clearfix">
                            <div class="btn-toolbar pull-left">
                              <a class="btn btn-secondary btn-sm" href="login">Login</a>
                            </div>
                            <div>&nbsp;</div>
                            <div class="btn-toolbar pull-right">
                              <input type="submit" name="register" class="btn btn-inverse btn-sm" value="Register" />
                            </div>
                        </div>
                      </form>
                    </div>
                </section>
            </div>
        </div>
    </main>
    <footer>
      <div style="text-align: center">
        2017 &copy; Nationwide Equipment Control - Registration
      </div>
    </footer>
</div>
<!-- The Loader. Is shown when pjax happens -->
<div class="loader-wrap hiding hide">
    <i class="fa fa-circle-o-notch fa-spin-fast"></i>
</div>

<!-- The Dialog. -->
<div id="dialog">
    <p id="dialogMessage"></p>
</div>

<!-- common libraries. required for every page-->
<script src="vendor/jquery/dist/jquery.min.js"></script>
<script src="vendor/jquery-pjax/jquery.pjax.js"></script>
<script src="vendor/select2/select2.min.js"></script>
<script src="vendor/tether/dist/js/tether.js"></script>
<script src="vendor/bootstrap/js/dist/util.js"></script>
<script src="vendor/bootstrap/js/dist/collapse.js"></script>
<script src="vendor/bootstrap/js/dist/dropdown.js"></script>
<script src="vendor/bootstrap/js/dist/button.js"></script>
<script src="vendor/bootstrap/js/dist/tooltip.js"></script>
<script src="vendor/bootstrap/js/dist/alert.js"></script>
<script src="vendor/slimScroll/jquery.slimscroll.min.js"></script>
<script src="vendor/widgster/widgster.js"></script>

<!-- common app js -->
<script src="js/settings.js"></script>
<!--script src="js/app.js"></script-->

<!-- page specific libs -->
<script src="vendor/parsleyjs/dist/parsley.min.js"></script>
<!-- page specific js -->

<script>
$(function() {
    //$( "#state" ).select2();
    loadStates();
    $('#lblPassword')
            .tooltip(
            {
                html: true,
                title: "Password requirements: <br>" +
                    "Minimum 8 characters <br>" +
                    "Maximum of 32 characters <br>" +
                    "Contains one numeric character <br>" +
                    "Contains one special character <br> (! @ # * >) <br>" +
                    "Cannot contain spaces", 
                placement: "right",
                trigger: "click"
            }
                    );
});
</script>

</body>
</html>
