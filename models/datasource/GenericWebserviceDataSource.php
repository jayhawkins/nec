<?php

require 'DataSource.php';

/**
 * 
 * 
 * @author euecheruo
 *
 */
class GenericWebserviceDataSource extends DataSource
{

    protected $_default = array(
        'options' => array(
            'headers' => array(),
            'options' => array(),
            'type'    => 'query'
        )
    );
    
    /**
     * Instance of a cURL handle 
     *
     * @var unknown
     */
    protected $_instance = NULL;
    
    /**
     * Defualt curl options
     *
     * @var array
     */
    protected $_options = array(
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_HEADER          => true,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_TIMEOUT         => 3,
    );
    
    /**
     * Default curl header options 
     * 
     * @var array
     * @access protected
     */
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
    public function __construct(array $config = array()) {
        if (!extension_loaded('curl')) {
            throw new \ErrorException('cURL library is not loaded');
        }
        parent::__construct($config);
        $this->_instance = curl_init();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see DataSource::create()
     */
    public function create(Model $model, array $data = array(), array $options = array()) {
        if (!isset($options['url'])) {
            throw new \ErrorException('webservice url was not provided');
        }
        
        $options = $this->_resolveOptions($options);
        $data = $this->_sendData($data, $options['type']);
        if ($options['type'] == 'json') {
            $options['headers'] = array('Content-Type: application/json', 'Content-Length: ' . strlen($data)) + $options['headers'];
        }
        
        return $this->_request($options['url'], 'POST', $data, $options['headers'], $options['options']);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see DataSource::read()
     */
    public function read(Model $model, array $query = array(), array $options = array()) {
        if (!isset($options['url']) || empty($options['url'])) {
            throw new \ErrorException('webservice url was not provided');
        }
        $options = $this->_resolveOptions($options);
        $options['url'] = (!empty($query)) ? $options['url'] . "?" . $this->_sendQuery($query) : $options['url'];
        return $this->_request($options['url'], 'GET', '', $options['headers'], $options['options']);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see DataSource::update()
     */
    public function update(Model $model, array $data = array(), array $options = array()) {
        if (!isset($options['url']) || empty($options['url'])) {
            throw new \ErrorException('webservice url was not provided');
        }
        
        $options = $this->_resolveOptions($options);
        $data = $this->_sendData($data, $options['type']);
        if ($options['type'] == 'json') {
            $options['headers'] = array('Content-Type: application/json', 'Content-Length: ' . strlen($data)) + $options['headers'];
        }
                
        return $this->_request($options['url'], 'PUT', $data, $options['headers'], $options['options']);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see DataSource::delete()
     */
    public function delete(Model $model, array $data = array(), array $options = array()) {
        if (!isset($options['url']) || empty($options['url'])) {
            throw new \ErrorException('webservice url was not provided');
        }
        
        $options = $this->_resolveOptions($options);
        $options['url'] = (!empty($data)) ? $options['url'] . "?" . $this->_sendQuery($data) : $options['url'];
        
        return $this->_request($options['url'], 'DELETE', '', $options['headers'], $options['options']);
    }
    
    /**
     * 
     * @param string $url
     * @param string $method
     * @param string $data
     * @param array $headers
     * @param array $options
     * @throws \ErrorException
     * @return mixed
     */
    protected function _request(string $url, string $method = 'GET', string $data = "", array $headers = array(), array $options = array()) {
        
        $method = strtoupper($method);
        if (!in_array($method, $this->_methods)) {
            throw new \ErrorException(sprintf("'%s' is not an accepted HTTP method", $method));
        }
        
        if (!empty($data) && !is_string($data)) {
            throw new \ErrorException(sprintf('Not an accepted data package for request to %s', $url));
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
                curl_setopt($this->_instance, CURLOPT_POSTFIELDS, $data);
                
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
    
    /**
     * 
     * @param array $options
     * @return array
     */
    protected function _resolveOptions($options = array()) {
        return array_merge($this->config['options'], $options);
    }
    
    /**
     * 
     * @param array $data
     * @return string
     */
    protected function _sendQuery(array $data = array()) {
        return urldecode(http_build_query($data));
    }

    /**
     * 
     * @param array $data
     * @return string
     */
    protected function _sendJson(array $data = array()) {
        return json_encode($data);
    }
    
    /**
     * 
     * @param array $data
     * @param string $type
     * @return string
     */
    protected function _sendData(array $data = array(), $type = 'query') {
        
        switch($type) {
            case 'query':
                $data = $this->_sendQuery($data);
                break;
            case 'json':
                $data = $this->_sendJson($data);
                break;
        }
        
        return $data;
    }
    
}
