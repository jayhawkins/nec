<?php 

/**
 * The DataSource
 * 
 * @author euecheruo
 *
 */
class DataSource
{
    
    /**
     * User configiuration
     * 
     * @var array
     */
    public $config = array();

    /**
     * The default configuration
     * 
     * @var array
     */
    protected $_default = array();
    
    /**
     * Constructor
     * 
     * @param array $config
     */
    public function __construct(array $config = array()) {
        $this->setConfig($config);
    }
    
    /**
     * Add to the datasource
     * 
     * @param Model $model The Model
     * @param array $data The data passed
     * @param array $options the options
     * @return boolean
     */
    public function create(Model $model, array $data = array(), array $options = array()) {
        return false;
    }
    
    /**
     * Reads from the datasource
     * 
     * @param Model $model The Model
     * @param array $query The query request
     * @param array $options The options
     * @return boolean
     */
    public function read(Model $model, array $query = array(), array $options = array()) {
        return false;
    }
    
    /**
     * Updates the datasource
     * 
     * @param Model $model The Model
     * @param array $data The data passed
     * @param array $options The options
     * @return boolean
     */
    public function update(Model $model, array $data = array(), array $options = array()) {
        return false;
    }
    
    /**
     * Delete from the datasource
     * 
     * @param Model $model The Model
     * @param array $data The data passed
     * @param array $options the options
     * @return boolean
     */
    public function delete(Model $model, array $data = array(), array $options = array()) {
        return false;
    }
    
    /**
     * Describes the datasource
     * 
     * @param Model $model
     * @return boolean
     */
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
