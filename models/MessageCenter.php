<?php
/*
 *
 * Project: NEC
 * File: Message.php
 * Description: Handles the NEC Message Center Processing
 * Created: 10/12/2017
 * Author: John Opoku
 *
 */

// Include Notification Class
/*
require_once "Twilio/Version.php"; // Twilio  Class
require_once "Twilio/InstanceContext.php"; // Twilio  Class
require_once "Twilio/InstanceResource.php";
require_once "Twilio/ListResource.php";
require_once "Twilio/Values.php";
require_once "Twilio/Serialize.php";
require_once "Twilio/Deserialize.php";
require_once "Twilio/VersionInfo.php";
require_once "Twilio/Http/Response.php";
require_once "Twilio/Rest/Api/V2010/Account/MessageInstance.php";
require_once "Twilio/Rest/Api/V2010/Account/MessageList.php";
require_once "Twilio/Rest/Api/V2010/AccountContext.php";
require_once "Twilio/Rest/Api/V2010.php"; // Twilio  Class
require_once "Twilio/Domain.php"; // Twilio  Class
require_once "Twilio/Rest/Api.php"; // Twilio  Class
require_once "Twilio/Http/Client.php"; // Twilio  Class
require_once "Twilio/Rest/Client.php"; // Twilio  Class
require_once "Twilio/Http/CurlClient.php"; // Twilio  Class
*/
// NEC Message Center Class

class MessageCenter{

    /**
     *
     * @var string
     */
    protected $_datasource = "GenericWebservice";
    
    // Message Center Constructor
    function __construct($content_type = '') {
        //
    }

    // Send an SMS using Twilio's REST API and PHP
    public function sendSMS($recepient, $msg) {
        $sid = "AC8c328f90034df432f3287bc44ff3deea"; // Your Account SID from www.twilio.com/console
        $token = "3f2053f04221c2fc51f89521298a5042"; // Your Auth Token from www.twilio.com/console


        $client = new Twilio\Rest\Client($sid, $token);
        $message = $client->messages->create(
                $recepient, // Text this number
                array(
            'from' => '7207091241', // From a valid Twilio number
            'body' => $msg
                )
        );
    }
}
?>
