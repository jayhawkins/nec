<?php 

class Model
{
    /**
     * 
     * @var string
     */
    protected $_name = "";
    
    /**
     * 
     * @var string
     */
    protected $_baseUrl = "";
    
    public function __construct() {
        $this->_baseUrl = API_HOST_URL . "/" . $this->_name;
    }
    
    public function get(array $fields = array(), array $conditions = array()) {
        $url = $this->_baseUrl;
        return Flight::datasource()->read($url);
    }
    
    /**
     * 
     * @param bool $transform
     * @return unknown
     */
    public function getAll(bool $transform = false) {
        $url = $this->_baseUrl;
        if($transform) {
            $url .= "?transform=1";
        }
        return Flight::datasource()->read($url);
    }
    
}

?>
