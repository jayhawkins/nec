<?php 

class DataSource
{
    
    public $config = array();

    protected $_default = array();
    
    public function __construct(array $config = array()) {
        $this->setConfig($config);
    }
    
    public function create(Model $model, array $data = array(), array $options = array()) {
        return false;
    }
    
    public function read(Model $model, array $query = array(), array $options = array()) {
        return false;
    }
    
    public function update(Model $model, array $data = array(), array $options = array()) {
        return false;
    }
    
    public function delete(Model $model, array $data = array(), array $options = array()) {
        return false;
    }
    
    public function describe(Model $model) {
        return false;
    }
    
    /**
     * Setup configuration for datasource
     * 
     * @param array $config
     */
    public function setConfig($config = array()) {
        $this->config = array_merge($this->_default, $this->config, $config);
    }
    
}
