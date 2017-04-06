<?php

class Contact
{
    public function __construct() {

    }

    public function post() {

    }

    public function get() {

    }

    public function put($contactid,$firstName,$lastName,$title,$phone,$fax,$email) {
        try {
              $contacturl = API_HOST.'/api/contacts/'.$contactid;
              $contactdata = array(
                          "firstName" => $firstName,
                          "lastName" => $lastName,
                          "title" => $title,
                          "primaryPhone" => $phone,
                          "fax" => $fax,
                          "emailAddress" => $email,
                          "updatedAt" => date('Y-m-d H:i:s')
              );
              // use key 'http' even if you send the request to https://...
              $contactoptions = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'PUT',
                      'content' => http_build_query($contactdata)
                  )
              );
              $contactcontext  = stream_context_create($contactoptions);
              $contactresult = file_get_contents($contacturl, false, $contactcontext);
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
