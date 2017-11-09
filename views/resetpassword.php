<!DOCTYPE html>
<html>
<head>
    <title>NEC - Reset Password</title>
    <link href="<?php echo HTTP_HOST; ?>/css/application.min.css" rel="stylesheet">
    <!-- as of IE9 cannot parse css files with more that 4K classes separating in two files -->
    <!--[if IE 9]>
        <link href="css/application-ie9-part2.css" rel="stylesheet">
    <![endif]-->
    <link rel="stylesheet" href="<?php echo HTTP_HOST ?>/vendor/font-awesome/css/font-awesome.min.css">
    <link rel="shortcut icon" href="<?php echo HTTP_HOST ?>/img/favicon.png">
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
            var checkWhitespace = new RegExp("\s");
            
            if(password.length < 8 || password.length > 32) return false;
            else if(!checkNumber.test(password)) return false;
            else if(checkWhitespace.test(password)) return false;
            else if(!checkSpecialCharacter(password)) return false;
            
            return true;
         }

         function verifyInput() {

             if ( $('#formLogin').parsley().validate() ) {
                    if(!verifyPassword()){
                            alert('The password does not meet the password policy requirements.');
                            
                            return false;
                       }
                       else{
                            
                            return true;
                       }

             } else {

                 return false;

             }
         }

    </script>
</head>
<body class="login-page">

<div class="container">
    <main id="content" class="widget-login-container" role="main">
        <div class="row">
            <div class="col-xl-4 col-md-6 col-xs-10 col-xl-offset-4 col-md-offset-3 col-xs-offset-1">
                <h5 class="widget-login-logo animated fadeInUp">
                    <img src="<?php echo HTTP_HOST; ?>/img/nec_logo.png" />
                </h5>
                <section class="widget widget-login animated fadeInUp">
                    <header>
                        <h3>Reset Password</h3>
                    </header>
                    <div class="widget-body">
                        <p class="widget-login-info">
                            Enter your NEW password and click "Submit".
                        </p>
                        <form id="formLogin" class="login-form mt-lg" method="POST" action="/resetpassword" onsubmit="return verifyInput();">
                            <input type="hidden" id="username" name="username" value="<?php echo $id ?>" />
                            <div class="form-group">
                                <label>Password   <span  id="lblPassword" class="fa fa-info-circle"></span></label>
                                <input type="text" class="form-control" id="password" name="password" placeholder="Password" required="required" />
                            </div>
                            <div class="form-group">
                                <label for="rate">Confirm Password</label>
                                <input type="text" class="form-control" id="passwordConfirm" name="passwordConfirm" placeholder="Confirm Password" data-parsley-equalto="#password" required="required" />
                            </div>
                            <div class="clearfix">
                                <div class="btn-toolbar pull-xs-right">
                                    <input type="submit" name="login" class="btn btn-inverse btn-sm" value="Submit" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-md-push-6">
                                    <div class="clearfix">
                                        <div class="abc-checkbox widget-login-info pull-xs-right ml-n-lg">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </main>
    <footer class="page-footer">
        2017 &copy; Admin Dashboard
    </footer>
</div>
<!-- The Loader. Is shown when pjax happens -->
<div class="loader-wrap hiding hide">
    <i class="fa fa-circle-o-notch fa-spin-fast"></i>
</div>

<!-- common libraries. required for every page-->
<script src="<?php echo HTTP_HOST; ?>/vendor/jquery/dist/jquery.min.js"></script>
<script src="<?php echo HTTP_HOST; ?>/vendor/jquery-pjax/jquery.pjax.js"></script>
<script src="<?php echo HTTP_HOST; ?>/vendor/tether/dist/js/tether.js"></script>
<script src="<?php echo HTTP_HOST; ?>/vendor/bootstrap/js/dist/util.js"></script>
<script src="<?php echo HTTP_HOST; ?>/vendor/bootstrap/js/dist/collapse.js"></script>
<script src="<?php echo HTTP_HOST; ?>/vendor/bootstrap/js/dist/dropdown.js"></script>
<script src="<?php echo HTTP_HOST; ?>/vendor/bootstrap/js/dist/button.js"></script>
<script src="<?php echo HTTP_HOST; ?>/vendor/bootstrap/js/dist/tooltip.js"></script>
<script src="<?php echo HTTP_HOST; ?>/vendor/bootstrap/js/dist/alert.js"></script>
<script src="<?php echo HTTP_HOST; ?>/vendor/slimScroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo HTTP_HOST; ?>/vendor/widgster/widgster.js"></script>

<!-- common app js -->
<script src="<?php echo HTTP_HOST; ?>/js/settings.js"></script>
<script src="<?php echo HTTP_HOST; ?>/js/app.js"></script>

<!-- page specific libs -->
<script src="<?php echo HTTP_HOST; ?>/vendor/parsleyjs/dist/parsley.min.js"></script>
<!-- page specific js -->


<script>
$(function() {
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
