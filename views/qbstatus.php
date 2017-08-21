<?php

session_start();

require '../../nec_config.php';
require '../lib/common.php';
require '../lib/quickbooksconfig.php';

define('OAUTH_REQUEST_URL', 'https://oauth.intuit.com/oauth/v1/get_request_token');
define('OAUTH_ACCESS_URL', 'https://oauth.intuit.com/oauth/v1/get_access_token');
define('OAUTH_AUTHORISE_URL', 'https://appcenter.intuit.com/Connect/Begin');
// The url to this page. it needs to be dynamic to handle runnable's dynamic urls
define('CALLBACK_URL', HTTP_HOST . '/qb_api_status');
// cleans out the token variable if comming from
// connect to QuickBooks button
if ( isset($_GET['start'] ) ) {
  unset($_SESSION['token']);
}
 
try {
  $oauth = new OAuth( OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
  $oauth->enableDebug();
  $oauth->disableSSLChecks(); //To avoid the error: (Peer certificate cannot be authenticated with given CA certificates)
  if (!isset( $_GET['oauth_token'] ) && !isset($_SESSION['token']) ){
	// step 1: get request token from Intuit
    $request_token = $oauth->getRequestToken( OAUTH_REQUEST_URL, CALLBACK_URL );
		$_SESSION['secret'] = $request_token['oauth_token_secret'];
		// step 2: send user to intuit to authorize 
		
                echo 'Not Authorized';
                //exit();
                header('Location: '. OAUTH_AUTHORISE_URL .'?oauth_token='.$request_token['oauth_token']);
	}
	
	if ( isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']) ){
		// step 3: request a access token from Intuit
    $oauth->setToken($_GET['oauth_token'], $_SESSION['secret']);
		$access_token = $oauth->getAccessToken( OAUTH_ACCESS_URL );
		
		$_SESSION['token'] = serialize( $access_token );
    $_SESSION['realmId'] = '193514475422329';  // realmId is legacy for customerId
    //$_SESSION['dataSource'] = $_REQUEST['dataSource'];
	
	 $token = $_SESSION['token'] ;
	 $realmId = $_SESSION['realmId'];
	 //$dataSource = $_SESSION['dataSource'];
	 $secret = $_SESSION['secret'] ;
	 
    // write JS to pup up to refresh parent and close popup
    echo '<script type="text/javascript">
            window.opener.location.href = window.opener.location.href;
            window.close();
          </script>';
  }
 
} catch(OAuthException $e) {
	echo "Got auth exception";
	echo '<pre>';
	print_r($e);
}
?>

 

 <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>



 <style>
    /* CSS REQUIRED */
    .state-icon {
     left: -5px;
    }
    .list-group-item-primary {
     color: rgb(255, 255, 255);
     background-color: rgb(66, 139, 202);
    }

    /* DEMO ONLY - REMOVES UNWANTED MARGIN */
    .well .list-group {
     margin-bottom: 0px;
    }

    /***************** Autocomplete ***********************/

    .orgSearch {z-index: 9999}
    .destSearch {z-index: 9999}

    #origination-list{float:left;list-style:none;margin-top:-3px;padding:0;width:250px;position: inherit;}

    #origination-list li{padding: 10px; background: #f0f0f0; border-bottom: #bbb9b9 1px solid;}

    #origination-list li:hover{background:#ece3d2;cursor: pointer;}

    #destination-list{float:left;list-style:none;margin-top:-3px;padding:0;width:250px;position: realtive;}

    #destination-list li{padding: 10px; background: #f0f0f0; border-bottom: #bbb9b9 1px solid;}

    #destination-list li:hover{background:#ece3d2;cursor: pointer;}

    td.details-control {
        background: url('../img/details_open.png') no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url('../img/details_close.png') no-repeat center center;
    }

    td.details-control-add {
        background: url('../img/details_open.png') no-repeat center center;
        cursor: pointer;
    }
    td.details-control-minus {
        background: url('../img/details_close.png') no-repeat center center;
        cursor: pointer;
    }

 </style>

 <ol class="breadcrumb">
   <li>ADMIN</li>
   <li class="active">Quickbooks</li>
 </ol>
 <section id="customer-needs" class="widget">
     <header>
         <h4><span class="fw-semi-bold">Quickbooks Connection Status</span></h4>
         <!--<div class="widget-controls">
             <a data-widgster="expand" title="Expand" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>
             <a data-widgster="collapse" title="Collapse" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>
             <a data-widgster="close" title="Close" href="#"><i class="glyphicon glyphicon-remove"></i></a>
         </div>-->
     </header>
     <div class="widget-body">
         <br /><br />
         
     </div>
 </section>
 

  <!-- Modal -->


