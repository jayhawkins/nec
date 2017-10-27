<?php

require_once 'Model.php';

class Contacts extends Model
{
    
    /**
     * The table name
     *
     * @var string
     */
    public $table = "contacts";
 
    public function post() {

    }

    public function get() {

    }

    public function put($contactid,$firstName,$lastName,$title,$phone,$fax,$email) {
        try {
            $contacturl = API_HOST_URL . '/contacts/'.$contactid;
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

    public function getContactsByEntity($id) {
        $args = array(
              "transform"=>"1",
              "filter"=>"entityID,eq,".$id
        );

        $url = API_HOST_URL . "/contacts?".http_build_query($args);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'GET'
            )
        );
        $context  = stream_context_create($options);
        try {
            $result = json_decode(file_get_contents($url,false,$context),true);
        } catch (Exception $e) {
            $result = '';
        }
        return $result;

    }
}
