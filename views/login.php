<!DOCTYPE html>
<html>
<head>
    <title>NEC - Login</title>
    <link href="css/application.min.css" rel="stylesheet">
    <!-- as of IE9 cannot parse css files with more that 4K classes separating in two files -->
    <!--[if IE 9]>
        <link href="css/application-ie9-part2.css" rel="stylesheet">
    <![endif]-->
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

         function verifyInput() {
             var passValidation = false;
             var url = '<?php echo API_HOST_URL . "/users?filter=username,eq,' + $('#username').val() + '&transform=1" ?>';
             $.ajax({
                type: "GET",
                url: url,
                async: false,
                success: function(data){
                   if (data.users.length <= 0) {
                     alert('Username Does Not Exist!');
                   } else {
                     passValidation = true;
                   }
                },
                error: function() {
                   alert("Can't Get Email for Verification");
                }
             });
             //return true;
             return passValidation;
         }

    </script>
</head>
<body class="login-page">

<div class="container">
    <main id="content" class="widget-login-container" role="main">
        <div class="row">
            <div class="col-xl-4 col-md-6 col-xs-10 col-xl-offset-4 col-md-offset-3 col-xs-offset-1">
                <h5 class="widget-login-logo animated fadeInUp">
                    <img src="img/nec_logo.png" />
                </h5>
                <section class="widget widget-login animated fadeInUp">
                    <header>
                        <h3>Login</h3>
                    </header>
                    <div class="widget-body">
                        <p class="widget-login-info">
                            Don't have an account? <a href="register">Sign up now!</a>
                        </p>
                        <form name="formLogin" class="login-form mt-lg" method="POST" action="/login">
                            <div class="form-group">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Email" required="required" />
                            </div>
                            <div class="form-group">
                                <input class="form-control" id="password" name="password" type="password" placeholder="Password" required="required" />
                            </div>
<?php
                            if (!empty($invalidPassword)) {
                                echo "<div class=\"alert alert-danger\" role=\"alert\">". $invalidPassword . "</div>";
                            }
 ?>
                            <div class="clearfix">
                                <div class="btn-toolbar pull-xs-right">
                                    <a class="btn btn-secondary btn-sm" href="forgot">Forgot Password</a>
                                    <input type="submit" name="login" class="btn btn-inverse btn-sm" value="Login" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-md-push-6">
                                    <div class="clearfix">
                                        <div class="abc-checkbox widget-login-info pull-xs-right ml-n-lg">
                                            <input type="checkbox" id="checkbox1" value="1">
                                            <label for="checkbox1">Keep me signed in </label>
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
<script src="vendor/jquery/dist/jquery.min.js"></script>
<script src="vendor/jquery-pjax/jquery.pjax.js"></script>
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
<script src="js/app.js"></script>

<!-- page specific libs -->
<script src="vendor/parsleyjs/dist/parsley.min.js"></script>
<!-- page specific js -->
</body>
</html>
