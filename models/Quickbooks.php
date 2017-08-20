<?php

use QuickBooks_IPP_IntuitAnywhere;
use QuickBooks_IPP;
use QuickBooks_IPP_Service_CompanyInfo;
use QuickBooks_IPP_Service_Payment;
use QuickBooks_IPP_Service_ChangeDataCapture;
use QuickBooks_IPP_Service_Class;

class Quickbooks
{
    public function __construct() {

    }

    public function testMethod(){
        
        return "This works.";
    }
    
    public function isConnected() {
        
        
        return "connected";
    }
    
    public function oauth() {
        if ($this->QuickBooks->handle()) {
            ; // The user has been connected, and will be redirected to $that_url automatically. 
        } else {
            // If this happens, something went wrong with the OAuth handshake
            die('Oh no, something bad happened');
        }
    }

}
