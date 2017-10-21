<?php 

/**
 * 
 * @author euecheruo
 *
 */
class Model
{
    
    /**
     * 
     * @var string
     */
    public $table = "";
    
    /**
     * 
     * @var string
     */
    protected $_datasource = "GenericWebservice";
    
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
        require ROOT_LOCATION . "/models/datasource/" . $class . ".php";
        $this->adapter($class);
    }

    /**
     * 
     * @param unknown $adapter
     * @throws Exception
     * @return NULL|unknown
     */
    public function adapter($adapter = null) {
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
        return $this->_instance->read($this, $query, $options = array());
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

?>
