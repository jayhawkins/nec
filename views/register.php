<!DOCTYPE html>
<html>
<head>
    <title>NEC - Register</title>
    <link href="css/application.min.css" rel="stylesheet">
    <!-- as of IE9 cannot parse css files with more that 4K classes separating in two files -->
    <!--[if IE 9]>
        <link href="css/application-ie9-part2.css" rel="stylesheet">
    <![endif]-->
    <link href="vendor/select2/select2.css" rel="stylesheet">
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
    </script>
</head>
<body class="login-page">

<div class="container">
    <main id="content" class="widget-login-container" role="main">
        <div class="row">
            <!-- div class="col-xl-4 col-md-6 col-xs-10 col-xl-offset-4 col-md-offset-3 col-xs-offset-1" -->
            <div class="col-lg-8 col-lg-offset-2">
                <h5 class="widget-login-logo animated fadeInUp">
                    <img src="img/nec_logo.png" />
                </h5>
                <section class="widget widget-login animated fadeInUp">
                    <header>
                        <h3>Register</h3>
                    </header>
                    <div class="widget-body">
                      <form class="register-form mt-lg" method="POST" action="/register">
                        <div class="row">
                          <div class="col-sm-5">
                              <div class="form-group">
                                <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name"
                                        required="required" />
                              </div>
                          </div>
                          <div class="col-sm-5">
                              <div class="form-group">
                                <input type="text" id="lastName" name="lastName" class="form-control" placeholder="Last Name"
                                       required="required" />
                              </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-sm-8">
                              <div class="form-group">
                                <input type="email" id="email" name="email" class="form-control" placeholder="Email"
                                       data-parsley-trigger="change"
                                       data-parsley-validation-threshold="1"
                                       required="required" />
                              </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-sm-5">
                              <div class="form-group">
                                <input type="password" id="password" name="password" class="form-control mb-sm" placeholder="Password"
                                       data-parsley-trigger="change"
                                       data-parsley-minlength="6"
                                       required="required" />
                              </div>
                          </div>
                          <div class="col-sm-5">
                              <div class="form-group">
                                <input type="password" id="password2" name="password2" class="form-control" placeholder="Repeat Password"
                                       data-parsley-trigger="change"
                                       data-parsley-minlength="6"
                                       data-parsley-equalto="#password"
                                       required="required" />
                              </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-sm-5">
                              <div class="form-group">
                                <input class="form-control" id="businessname" name="businessname" type="text" placeholder="Business Name"
                                        required="required" />
                              </div>
                          </div>
                          <div class="col-sm-5">
                              <div class="form-group">
                                <select id="businessType" name="businessType" data-placeholder="Business Type..." class="form-control chzn-select" data-ui-jq="select2" required="required">
                                  <option value=""></option>
                                  <option value="carrier">Carrier</option>
                                  <option value="customer">Customer</option>
                                </select>
                              </div>
                          </div>
                        </div>
                        <div class="clearfix">
                          <div class="btn-toolbar pull-left">
                            <a class="btn btn-secondary btn-sm" href="login">Login</a>
                          </div>
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
<script src="js/app.js"></script>

<!-- page specific libs -->
<script src="vendor/parsleyjs/dist/parsley.min.js"></script>
<!-- page specific js -->
</body>
</html>
