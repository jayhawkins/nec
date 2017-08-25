<?php

class Location
{
    public function __construct() {

    }

    public function post($entityID="0",$locationTypeID="1",$name="",$address1="",$address2="",$city="",$state="",$zip="",$latitude="0.00",$longitude="0.00") {
        // Now create the entity location
        $locationurl = API_HOST.'/api/locations';
        $locationdata = array(
                    "entityID" => $entityID, // this will contain the new entities id
                    "locationTypeID" => $locationTypeID,
                    "name" => $name,
                    "address1" => $address1,
                    "address2" => $address2,
                    "city" => $city,
                    "state" => $state,
                    "zip" => $zip,
                    "latitude" => $latitude,
                    "longitude" => $longitude,
                    "timeZone" => '',
                    "createdAt" => date('Y-m-d H:i:s'),
                    "updatedAt" => date('Y-m-d H:i:s')
        );
        // use key 'http' even if you send the request to https://...
        $locationoptions = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($locationdata)
            )
        );

        $locationcontext  = stream_context_create($locationoptions);
        try {
            $locationresult = file_get_contents($locationurl, false, $locationcontext);
            if ($locationresult > 0) {
                return "success";
            } else {
                return "failed";
            }
        } catch (Exception $e) {
            return $e;
        }

    }

    public function get($locationid) {
          try {
                $locationurl = API_HOST.'/api/locations/'.$locationid;
                $locationdata = array();
                // use key 'http' even if you send the request to https://...
                $locationoptions = array(
                    'http' => array(
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'GET',
                        'content' => http_build_query($locationdata)
                    )
                );
                $locationcontext  = stream_context_create($locationoptions);
                $locationresult = file_get_contents($locationurl, false, $locationcontext);
                return $locationresult;
          } catch (Exception $e) { // The authorization query failed verification
                header('HTTP/1.1 401 Unauthorized');
                header('Content-Type: text/plain; charset=utf8');
                return $e->getMessage();
          }
    }

    public function getLocationByCityState($city,$state,$entityID) {
          try {
                //$locationurl = API_HOST.'/api/locations?transform=1&filter[]=entityID,eq,'.$entityID.'&filter[]=city,eq,'.$city.'&filter[]=state,eq,'.$state.'&filter[]=status,eq,Active';
                $locationargs = array(
                      "transform"=>1,
                      "filter[0]"=>"entityID,eq,".$entityID,
                      "filter[1]"=>"city,eq,".$city,
                      "filter[2]"=>"state,eq,".$state,
                      "filter[4]"=>"status,eq,Active"
                );
                // use key 'http' even if you send the request to https://...
                $locationoptions = array(
                    'http' => array(
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'GET'
                    )
                );
                $locationurl = API_HOST.'/api/locations?'.http_build_query($locationargs);
                $locationcontext  = stream_context_create($locationoptions);
                $locationresult = json_decode(file_get_contents($locationurl, false, $locationcontext));
                if (count($locationresult->locations) > 0) {
                    return count($locationresult);
                } else {
                    return 0;
                }
          } catch (Exception $e) { // The authorization query failed verification
                header('HTTP/1.1 401 Unauthorized');
                header('Content-Type: text/plain; charset=utf8');
                return $e->getMessage();
          }
    }

    public function getLocationByCityStateZip($city,$state,$zip,$entityID) {
          try {
                //$locationurl = API_HOST.'/api/locations?transform=1&filter[]=entityID,eq,'.$entityID.'&filter[]=city,eq,'.$city.'&filter[]=state,eq,'.$state.'&filter[]=zip,eq,'.$zip.'&filter[]=status,eq,Active';
                $locationargs = array(
                      "transform"=>1,
                      "filter[0]"=>"entityID,eq,".$entityID,
                      "filter[1]"=>"city,eq,".$city,
                      "filter[2]"=>"state,eq,".$state,
                      "filter[3]"=>"zip,eq,".$zip,
                      "filter[4]"=>"status,eq,Active"
                );
                // use key 'http' even if you send the request to https://...
                $locationoptions = array(
                    'http' => array(
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'GET'
                    )
                );
                $locationurl = API_HOST.'/api/locations?'.http_build_query($locationargs);
                $locationcontext  = stream_context_create($locationoptions);
                $locationresult = json_decode(file_get_contents($locationurl, false, $locationcontext));
                if (count($locationresult->locations) > 0) {
                    return count($locationresult);
                } else {
                    return 0;
                }
          } catch (Exception $e) { // The authorization query failed verification
                header('HTTP/1.1 401 Unauthorized');
                header('Content-Type: text/plain; charset=utf8');
                return $e->getMessage();
          }
    }

    public function getLocationByAddressCityStateZip($address1,$city,$state,$zip,$entityID) {
          try {
                //$locationurl = API_HOST.'/api/locations?transform=1&filter[]=entityID,eq,'.$entityID.'&filter[]=city,eq,'.$city.'&filter[]=state,eq,'.$state.'&filter[]=zip,eq,'.$zip.'&filter[]=status,eq,Active';
                $locationargs = array(
                      "transform"=>1,
                      "filter[0]"=>"entityID,eq,".$entityID,
                      "filter[1]"=>"address1,eq,".$address1,
                      "filter[2]"=>"city,eq,".$city,
                      "filter[3]"=>"state,eq,".$state,
                      "filter[4]"=>"zip,eq,".$zip,
                      "filter[5]"=>"status,eq,Active"
                );
                // use key 'http' even if you send the request to https://...
                $locationoptions = array(
                    'http' => array(
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'GET'
                    )
                );
                //return var_dump($locationargs);
                $locationurl = API_HOST.'/api/locations?'.http_build_query($locationargs);
                $locationcontext  = stream_context_create($locationoptions);
                $locationresult = json_decode(file_get_contents($locationurl, false, $locationcontext));
                if (count($locationresult->locations) > 0) {
                    return count($locationresult);
                } else {
                    return 0;
                }
          } catch (Exception $e) { // The authorization query failed verification
                header('HTTP/1.1 401 Unauthorized');
                header('Content-Type: text/plain; charset=utf8');
                return $e->getMessage();
          }
    }

    public function put($locationid,$address1,$address2,$city,$state,$zip,$latitude="0.00",$longitude="0.00") {
        try {
              $locationurl = API_HOST.'/api/locations/'.$locationid;
              $locationdata = array(
                          "address1" => $address1,
                          "address2" => $address2,
                          "city" => $city,
                          "state" => $state,
                          "zip" => $zip,
                          "latitude"=>$latitude,
                          "longitude"=>$longitude,
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
