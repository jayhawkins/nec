<?php 

class DataSource
{
    
    /**
     * 
     * @var unknown
     */
    protected $_instance = NULL;
    
    /**
     * 
     * @var array
     */
    protected $_options = array(
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_HEADER          => true,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_TIMEOUT         => 3,
    );

    protected $_headers = array();
    
    /**
     * 
     * @var array
     */
    protected $_methods = array(
        'GET', 
        'POST', 
        'PUT',
        'PATCH',
        'DELETE'      
    );
    
    /**
     * 
     * @throws \ErrorException
     */
    public function __construct() {
        if (!extension_loaded('curl')) {
            throw new \ErrorException('cURL library is not loaded');
        }
        
        $this->_instance = curl_init();
    }
    
    /**
     * 
     * @param unknown $url
     * @param array $headers
     * @param array $options
     * @return mixed
     */
    public function read($url, $headers = array(), $options = array()) {
        return $this->_request($url, 'GET', '', $headers, $options);
    }
    
    /**
     * 
     * @param unknown $url
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return mixed
     */
    public function create($url, $data = array(), $headers = array(), $options = array()) {
        return $this->_request($url, 'POST', $data, $headers, $options);
    }
    
    /**
     * 
     * @param unknown $url
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return mixed
     */
    public function update($url, $data = array(), $headers = array(), $options = array()) {
        return $this->_request($url, 'PUT', $data, $headers, $options);
    }
    
    /**
     * 
     * @param string $url
     * @param array $headers
     * @param array $options
     * @return mixed
     */
    public function delete(string $url, array $headers = array(), array $options = array()) {
        return $this->_request($url, 'DELETE', '', $headers, $options);
    }
    
    /**
     * 
     * 
     * @param string $url
     * @param string $method
     * @param string $data
     * @param array $headers
     * @param array $options
     * @throws \Exception
     * @return mixed
     */
    protected function _request(string $url, string $method = 'GET', string $data = "", array $headers = array(), array $options = array()) {
    
        $method = strtoupper($method);
        if (!in_array($method, $this->_methods)) {
            throw new \Exception(sprintf("'%s' is not an accepted HTTP method", $method));
        }
        
        if (!empty($data) && !is_string($data)) {
            throw new \Exception(sprintf('Not an accepted data package for request to %s', $url));
        }
        
        curl_setopt_array($this->_instance, $this->_options);
        curl_setopt($this->_instance, CURLOPT_URL, $url);
        
        switch($method) {
            case 'GET':
                break;
            case 'POST':
                
                curl_setopt($this->_instance, CURLOPT_POST, true);
                curl_setopt($this->_instance, CURLOPT_POSTFIELDS, $data);
                
                break;
            case 'PUT':
            case 'PATCH':
                
                curl_setopt($this->_instance, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($this->_instance, CURLOPT_POSTFIELDS, $data);
                
                break;
            case 'DELETE':    

                curl_setopt($this->_instance, CURLOPT_CUSTOMREQUEST, $method);
                
                break;
        }
        
        curl_setopt_array($this->_instance, $options);
        curl_setopt($this->_instance, CURLOPT_HTTPHEADER, array_merge($this->_headers, $headers));
        
        $response = curl_exec($this->_instance);
        
        if ($response === false) {
            $response = curl_error($this->_instance);
        }
        curl_close($this->_instance);
        return $response;
    }
    
}

?>