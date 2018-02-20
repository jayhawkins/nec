<?php


class Logs
{

    /**
     * The table name
     *
     * @var string
     */
    public $table = "logs";

    
    public function enter_log($log_type_name, $log_msg, $ref_id = 0) {
        try{
            $logTypeArgs = array("filter[0]"=>"type_name,eq,".$log_type_name);

            $logTypeURL = API_HOST_URL . "/log_types?".http_build_query($logTypeArgs);
            $logTypeOptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $logTypeContext  = stream_context_create($logTypeOptions);
            $result = json_decode(file_get_contents($logTypeURL,false,$logTypeContext));

            if(count($result) < 1){

                //ADD THIS LOG TYPE ID
                $log_type_id = $this->add_log_type($log_type_name);
            }
            else{
                $log_type_id = $result->log_types->records[0][0];
            }

            //SEE IF WE HAVE AN ACTIVE SESSION TO WORK WITH
            if (isset($_SESSION['userid'])) {
                $user_id = $_SESSION['userid'];
            } else {
                $user_id = 0;
            }


            $logURL = API_HOST_URL . "/logs";
            $logData = array(
                            "log_type_id" => $log_type_id,
                            "log_descr" => $log_msg,
                            "ref_id" => $ref_id,
                            "user_id" => $user_id);

            $logOptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($logData)
                )
            );
            $logContext  = stream_context_create($logOptions);
            $logResult = file_get_contents($logURL, false, $logContext); 
            
            return $logResult;

        } catch (Exception $e) { // The authorization query failed verification
              header('HTTP/1.1 404 Not Found');
              header('Content-Type: text/plain; charset=utf8');
              echo $e->getMessage();
              exit();
        }
    }

    // END FUNCTION ENTER_LOG

    private function add_log_type($type_name) {  
        try{
            $logTypeURL = API_HOST_URL . "/log_types";
            $logTypeData = array("type_name" => $type_name);

            // use key 'http' even if you send the request to https://...
            $logTypeOptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($logTypeData)
                )
            );
            $logTypeContext  = stream_context_create($logTypeOptions);
            $logTypeResult = file_get_contents($logTypeURL, false, $logTypeContext);

            return $logTypeResult;
        } catch (Exception $e) { // The authorization query failed verification
              header('HTTP/1.1 404 Not Found');
              header('Content-Type: text/plain; charset=utf8');
              echo $e->getMessage();
              exit();
        }
    }
    
    public function get_log_for_customer_needs($customerNeedsID){
        try{
            
            $logTypeArgs = array("filter[0]"=>"type_name,eq,Customer Needs");

            $logTypeURL = API_HOST_URL . "/log_types?".http_build_query($logTypeArgs);
            $logTypeOptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $logTypeContext  = stream_context_create($logTypeOptions);
            $logTypeResult = json_decode(file_get_contents($logTypeURL,false,$logTypeContext));
            
            $log_type_id = $logTypeResult->log_types->records[0][0];

            $logArgs = array(
                "filter[0]"=>"ref_id,eq,".$customerNeedsID,
                "filter[1]"=>"log_type_id,eq,".$log_type_id,
                "transform"=>"1"
                    );

            $logURL = API_HOST_URL . "/logs?".http_build_query($logArgs);
            $logOptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $logContext  = stream_context_create($logOptions);
            $result = json_decode(file_get_contents($logURL,false,$logContext));

            return $result;
        } catch (Exception $e) { // The authorization query failed verification
              header('HTTP/1.1 404 Not Found');
              header('Content-Type: text/plain; charset=utf8');
              echo $e->getMessage();
              exit();
        }
    }

    public function get_log_for_orders($orderID){
        try{
            
            $logTypeArgs = array("filter[0]"=>"type_name,eq,Orders");

            $logTypeURL = API_HOST_URL . "/log_types?".http_build_query($logTypeArgs);
            $logTypeOptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $logTypeContext  = stream_context_create($logTypeOptions);
            $logTypeResult = json_decode(file_get_contents($logTypeURL,false,$logTypeContext));            
            
            $order_log_type_id = $logTypeResult->log_types->records[0][0];
            
            $logTypeArgs = array("filter[0]"=>"type_name,eq,Customer Needs");

            $logTypeURL = API_HOST_URL . "/log_types?".http_build_query($logTypeArgs);
            $logTypeOptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $logTypeContext  = stream_context_create($logTypeOptions);
            $logTypeResult = json_decode(file_get_contents($logTypeURL,false,$logTypeContext));            
            
            $customer_needs_log_type_id = $logTypeResult->log_types->records[0][0];
            
            
            
            
            $customerNeedToOrderArgs = array("filter[0]"=>"orderID,eq," . $orderID);
            $customerNeedToOrderURL = API_HOST_URL . "/customer_needs_to_orders?".http_build_query($customerNeedToOrderArgs);
            $customerNeedToOrderOptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $customerNeedToOrderContext  = stream_context_create($customerNeedToOrderOptions);
            $customerNeedToOrderResult = json_decode(file_get_contents($customerNeedToOrderURL,false,$customerNeedToOrderContext));   
            
            $customerNeedsID = $customerNeedToOrderResult->customer_needs_to_orders->records[0][1];
            
            $result = (object) array("logs" => array());

            $customerNeedsLogArgs = array(
                "filter[0]"=>"ref_id,eq,".$customerNeedsID,
                "filter[1]"=>"log_type_id,eq,".$customer_needs_log_type_id,
                "transform"=>"1"
                    );

            $customerNeedsLogURL = API_HOST_URL . "/logs?".http_build_query($customerNeedsLogArgs);
            $customerNeedsLogOptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $customerNeedsLogContext  = stream_context_create($customerNeedsLogOptions);
            $customerNeedsLogResult = json_decode(file_get_contents($customerNeedsLogURL,false,$customerNeedsLogContext));

            $customerNeedsLog = $customerNeedsLogResult->logs;
            
            $ordersLogArgs = array(
                "filter[0]"=>"ref_id,eq,".$orderID,
                "filter[1]"=>"log_type_id,eq,".$order_log_type_id,
                "transform"=>"1"
                    );

            $ordersLogURL = API_HOST_URL . "/logs?".http_build_query($ordersLogArgs);
            $ordersLogOptions = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'GET'
                )
            );
            $ordersLogContext  = stream_context_create($ordersLogOptions);
            $ordersLogResult = json_decode(file_get_contents($ordersLogURL,false,$ordersLogContext));

            $ordersLog = $ordersLogResult->logs;

            $result->logs = array_merge($customerNeedsLog, $ordersLog);

            return json_encode($result);
        } catch (Exception $e) { // The authorization query failed verification               
              header('HTTP/1.1 404 Not Found');
              header('Content-Type: text/plain; charset=utf8');
              echo $e->getMessage();
              exit();
        }
    }

}
