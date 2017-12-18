<?php

class Entities
{

    /**
     * The table name
     *
     * @var string
     */
    public $table = "entities";

    public function post() {

    }

    public function get() {

    }

    public function put($entityName,$configurationSettings) {
        try {
            $entityurl = API_HOST_URL . '/entities/'.$_SESSION['entityid'];
              $entitydata = array(
                          "name" => $entityName,
                          "configuration_settings"=>$configurationSettings,
                          "updatedAt" => date('Y-m-d H:i:s')
              );
              // use key 'http' even if you send the request to https://...
              $entityoptions = array(
                  'http' => array(
                      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                      'method'  => 'PUT',
                      'content' => http_build_query($entitydata)
                  )
              );
              $entitycontext  = stream_context_create($entityoptions);
              $entityresult = file_get_contents($entityurl, false, $entitycontext);
              return true;
        } catch (Exception $e) { // The authorization query failed verification
              header('HTTP/1.1 401 Unauthorized');
              header('Content-Type: text/plain; charset=utf8');
              return $e->getMessage();
        }
    }

}
