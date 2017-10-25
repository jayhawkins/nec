<?php

require_once ROOT_LOCATION . '/vendor/litlab/Array2XML.php';
require_once ROOT_LOCATION . '/vendor/litlab/XML2Array.php';
require_once 'DataSource.php';

/**
 * Generic Webservice Datasource
 * 
 * @author euecheruo
 *
 */
class GenericWebserviceDataSource extends DataSource
{
    /**
     * Default configuration
     * 
     * @var array
     */
    protected $_default = array(
        'options' => array(
            'headers' => array(),
            'options' => array(),
            'type'    => 'query'
        )
    );
    
    /**
     * Curl Resource
     * 
     * @var unknown
     */
    protected $_instance = NULL;
    
    /**
     * Default options
     * 
     * @var array
     */
    protected $_options = array(
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_HEADER          => false,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_ENCODING        => 'gzip',
        CURLOPT_ENCODING        => '',    
        CURLOPT_TIMEOUT         => 3,
    );
    
    /**
     * Default headers
     * 
     * @var array
     */
    protected $_headers = array();
    
    /**
     * Allowed methods
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
     * Constructor
     * 
     * @param array $config
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
     * Makes an create request
     * 
     * {@inheritDoc}
     * @see DataSource::create()
     */
    public function create(Model $model, array $data = array(), array $options = array()) {
        if (!isset($options['url'])) {
            throw new \ErrorException('webservice url was not provided');
        }
        
        $options = $this->_resolveOptions($options);
        $data = $this->_sendData($data, $options);
        return $this->_getData($this->_request($options['url'], 'POST', $data, $options['headers'], $options['options']), $options);
    }
    
    /**
     * Makes an read request
     * 
     * {@inheritDoc}
     * @see DataSource::read()
     */
    public function read(Model $model, array $query = array(), array $options = array()) {
        if (!isset($options['url']) || empty($options['url'])) {
            throw new \ErrorException('webservice url was not provided');
        }
        $options = $this->_resolveOptions($options);
        $options['url'] = (!empty($query)) ? $options['url'] . "?" . $this->_getQuery($query) : $options['url'];
        return $this->_getData($this->_request($options['url'], 'GET', '', $options['headers'], $options['options']), $options);
    }
    
    /**
     * Makes an update request
     * 
     * {@inheritDoc}
     * @see DataSource::update()
     */
    public function update(Model $model, array $data = array(), array $options = array()) {
        if (!isset($options['url']) || empty($options['url'])) {
            throw new \ErrorException('webservice url was not provided');
        }
        
        $options = $this->_resolveOptions($options);
        $data = $this->_sendData($data, $options);
        return $this->_getData($this->_request($options['url'], 'PUT', $data, $options['headers'], $options['options']), $options);
    }
    
    /**
     * Makes a delete request
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
        return $this->_getData($this->_request($options['url'], 'DELETE', '', $options['headers'], $options['options']), $options);
    }
    
    /**
     * Makes a request
     * 
     * @param string $url The url path 
     * @param string $method The method called 
     * @param string $data the data passed
     * @param array $headers the headers
     * @param array $options the options 
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
            throw \ResponseException(curl_error($this->_instance), curl_errno($this->_instance));
        }
        curl_close($this->_instance);
        return $response;
    }
    
    /**
     * Resolves the options
     * 
     * @param array $options The options
     * @return array
     */
    protected function _resolveOptions($options = array()) {
        return array_merge($this->config['options'], $options);
    }

    /**
     * Creates the Query String 
     * 
     * @param array $data
     * @param string $method
     * @return string
     */
    protected function _getQuery(array $data = array(), string $method = 'GET') {
        return $this->_generateQuery($data);
    }
    
    /**
     * Generates Array to Querystring 
     * 
     * @param array $data The data
     * @return string fromated querystring 
     */
    protected function _generateQuery(array $data = array()) {
        return urldecode(http_build_query($data));
    }

    /**
     * Converts Array to Json data
     * 
     * @param array $data The data
     * @return string
     */
    protected function _formArraytoJson(array $data = array()) {
        return json_encode($data);
    }
    
    /**
     * Converts Json to Array data
     * 
     * @param unknown $json The Json
     * @return mixed The Array data
     */
    protected function _fromJsonToArray($json) {
        return json_decode($json, true);
    }
    
    /**
     * Coverts Array to XML 
     *
     * useage:
     * 
     * $data = array(
     *  '@attributes' => array('type' => 'fiction'),
     *  'book'=> array('1984','Foundation','Stranger in a Strange Land')
     * );
     * 
     * @param array $data The Array
     * @return string The XML data
     */
    protected function _fromArrayToXML(array $data = array(), $rootElement = 'soap') {
        return Array2XML::createXML($rootElement, $data);
    }

    /**
     * Converts XML to Array
     * 
     * @param XML $xml A well-formed XML string 
     * @return array The Array data
     */
    protected function _fromXMLToArray($xml) {
        return XML2Array::createArray($xml);
    }
    
    /**
     * Formats the request
     * 
     * @param array $data
     * @param string $type
     * @return string
     */
    protected function _sendData(array $data = array(), array $options = array('type' => 'query')) {
        
        switch($options['type']) {
            case 'query':
                $data = $this->_generateQuery($data);
                break;
            case 'xml':
                $data = $this->_fromArrayToXML($data);
                $this->_options = $this->_setOptions(array(CURLINFO_HEADER_OUT => true, CURLOPT_TIMEOUT => 30, CURLOPT_CONNECTTIMEOUT => 5));
                $this->_headers = $this->_setHeaders(array('Content-Type: application/xml', 'SOAPAction: ""'));
                break;
            case 'json':
                $data = $this->_formArraytoJson($data);
                $this->_headers = $this->_setHeaders(array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
                break;
        }
        
        return $data;
    }

    /**
     * Formats the response data
     * 
     * @param array $data
     * @param array $options
     * @return array|mixed
     */
    protected function _getData($data, array $options = array('type' => 'json')) {
        
        if ($options['type'] === 'query') {
            $options['type'] = "json";
        }
        
        switch($options['type']) {
            case 'xml':
                $data = $this->_fromXMLToArray($data);
                break;
            case 'json':
                $data = $this->_fromJsonToArray($data);
                break;
        }
        
        return $data;
    }
    
    /**
     * Sets headers 
     * 
     * @param array $headers The headers
     * @return array
     */
    protected function _setHeaders(array $headers) {
        return array_merge($this->_headers, $headers);
    }
    
    /**
     * Sets options
     * 
     * @param array $options The options
     * @return array
     */
    protected function _setOptions(array $options) {
        return array_merge($this->_options, $options);
    }
    
}
