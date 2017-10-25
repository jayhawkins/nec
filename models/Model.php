<?php 

/**
 * 
 * @author euecheruo
 *
 */
class Model
{
    
    /**
     * The table name
     *
     * @var string
     */
    public $table = "";

    /**
     * Assoicated other Models with this Model 
     * 
     * @var array
     */
    public $relationships = array();
    
    /**
     * 
     * @var string
     */
    protected $_datasource = "NecWebservice";
    
    /**
     * 
     * @var unknown
     */
    protected $_instance = null;
    
    /**
     * 
     * @throws Exception
     */
    public function __construct() {
        $class = $this->_datasource . "DataSource";
        if (!file_exists(ROOT_LOCATION . "/models/datasource/" . $class . ".php")) {
            throw new Exception(sprintf('Cannot find %s class', $class));
        }
        require_once ROOT_LOCATION . "/models/datasource/" . $class . ".php";
        $this->adapter($class);
    }

    /**
     * 
     * @param unknown $adapter
     * @throws Exception
     * @return NULL| DataSource
     */
    public function adapter(string $adapter = null) {
        if ($adapter) {
            if (is_string($adapter)) {
                $adapter = new $adapter();
            }
            if (!$adapter instanceof DataSource) {
                throw new Exception('Adapter must instance of DataSource');
            }
            $this->_instance = $adapter;
            return null;
        }
        return $this->_instance;
    }
    
    /**
     * 
     * @param array $data
     * @param array $options
     * @return unknown
     */
    public function create(array $data = array(), array $options = array()) {
        return $this->_instance->create($this, $data, $options);
    }
    
    /**
     * 
     * @param array $query
     * @param array $options
     * @return unknown
     */
    public function read(array $query = array(), array $options = array()) {
        return $this->_instance->read($this, $query, $options);
    }
    
    /**
     * 
     * @param array $data
     * @param array $options
     * @return unknown
     */
    public function update(array $data = array(), array $options = array()) {
        return $this->_instance->update($this, $data, $options);
    }
    
    /**
     * 
     * @param array $data
     * @param array $options
     * @return unknown
     */
    public function delete(array $data = array(), array $options = array()) {
        return $this->_instance->delete($this, $data, $options);
    }
    
    /**
     * 
     * @return unknown
     */
    public function describe() {
        return $this->_instance->describe($this);        
    }
    
}
