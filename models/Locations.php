<?php

class Location
{
    public function __construct() {

    }

    public function post() {

    }

    public function get() {

    }

    public function put($locationid,$address1,$address2,$city,$state,$zip) {
        try {
              $locationurl = API_HOST.'/api/locations/'.$locationid;
              $locationdata = array(
                          "address1" => $address1,
                          "address2" => $address2,
                          "city" => $city,
                          "state" => $state,
                          "zip" => $zip,
                          "updatedAt" => date('Y-m-d H:i:s')
              );
              // use key 'http' even if you send the request to https://...
              $locationoptions = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'PUT',
                      'content' => http_build_query($locationdata)
                  )
              );
              $locationcontext  = stream_context_create($locationoptions);
              $locationresult = file_get_contents($locationurl, false, $locationcontext);
              return true;
        } catch (Exception $e) { // The authorization query failed verification
              header('HTTP/1.1 401 Unauthorized');
              header('Content-Type: text/plain; charset=utf8');
              return $e->getMessage();
        }
    }

    public function delete() {

    }
}
