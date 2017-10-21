<!DOCTYPE html>
<html>
<head>
    <title>NEC - Invalid Request</title>
    <link href="<?php echo HTTP_HOST ?>/css/application.min.css" rel="stylesheet">
    <!-- as of IE9 cannot parse css files with more that 4K classes separating in two files -->
    <!--[if IE 9]>
        <link href="css/application-ie9-part2.css" rel="stylesheet">
    <![endif]-->
    <link rel="shortcut icon" href="<?php echo HTTP_HOST ?>/img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
</head>
<body class="login-page">

<div class="container">
    <main id="content" class="widget-login-container" role="main">
        <div class="row">
            <div class="col-xl-4 col-md-6 col-xs-10 col-xl-offset-4 col-md-offset-3 col-xs-offset-1">
                <h5 class="widget-login-logo animated fadeInUp">
                    <img src="<?php echo HTTP_HOST ?>/img/nec_logo.png" />
                </h5>
                <section class="widget widget-login animated fadeInUp">
                    <header>
                        <h3>Invalid Request</h3>
                    </header>
                    <div class="widget-body">
                        <p class="widget-login-info">
                            You have requested a page that does not exist, or that you do not have access to.
                        </p>
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
<script src="<?php echo HTTP_HOST ?>/vendor/jquery/dist/jquery.min.js"></script>
<script src="<?php echo HTTP_HOST ?>/vendor/jquery-pjax/jquery.pjax.js"></script>
<script src="<?php echo HTTP_HOST ?>/vendor/tether/dist/js/tether.js"></script>
<script src="<?php echo HTTP_HOST ?>/vendor/bootstrap/js/dist/util.js"></script>
<script src="<?php echo HTTP_HOST ?>/vendor/bootstrap/js/dist/collapse.js"></script>
<script src="<?php echo HTTP_HOST ?>/vendor/bootstrap/js/dist/dropdown.js"></script>
<script src="<?php echo HTTP_HOST ?>/vendor/bootstrap/js/dist/button.js"></script>
<script src="<?php echo HTTP_HOST ?>/vendor/bootstrap/js/dist/tooltip.js"></script>
<script src="<?php echo HTTP_HOST ?>/vendor/bootstrap/js/dist/alert.js"></script>
<script src="<?php echo HTTP_HOST ?>/vendor/slimScroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo HTTP_HOST ?>/vendor/widgster/widgster.js"></script>

<!-- common app js -->
<script src="<?php echo HTTP_HOST ?>/js/settings.js"></script>
<script src="<?php echo HTTP_HOST ?>/js/app.js"></script>

<!-- page specific libs -->
<script src="<?php echo HTTP_HOST ?>/vendor/parsleyjs/dist/parsley.min.js"></script>
<!-- page specific js -->
</body>
</html>
