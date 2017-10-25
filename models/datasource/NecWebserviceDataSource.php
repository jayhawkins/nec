<?php

require 'GenericWebserviceDataSource.php';

/**
 * Nec Webservice Datasource 
 * 
 * @author euecheruo
 *
 */
class NecWebserviceDataSource extends GenericWebserviceDataSource
{
      
    public $config = array(
        'type' => 'json',
        'include' => array(),
    );
    
    /**
     * 
     * @var array
     */
    protected $_filters = array(
        'contains' => 'cs',
        'between' => 'bt',
        'starts' => 'sw', 
        'ends' => 'ew', 
        'is' => 'is', 
        '>=' => 'ge', 
        '>' => 'gt', 
        '<' => 'lt', 
        '<=' => 'le'
    );
    
    /**
     * 
     * @var array
     */
    protected $_clause = array(
        'include',
        'columns',
        'exclude',
        'filter',
        'order',
        'page',
        'satisfy',
        'transform'
    );
    
    /**
     * Constructor
     * 
     * @param array $config
     */
    public function __construct(array $config = array()) {
        parent::__construct($config);
        if (!isset($this->config['baseUrl'])) {
            $this->config['baseUrl'] = API_HOST_URL;
        }
    }

    /**
     * 
     * {@inheritDoc}
     * @see GenericWebserviceDataSource::create()
     */
    public function create(Model $model, array $data = array(), array $options = array()) {
        $options['url'] = $this->_getUrl($model);
        $querystring = $this->_getQuery($data, 'POST');
        $options['url'] = (!empty($data)) ? $options['url'] . "/" . $querystring : $options['url'];
        
        if (!isset($options['url'])) {
            throw new \ErrorException('webservice url was not provided');
        }
        
        $options = $this->_resolveOptions($options);
        $data = $this->_sendData($data, $options);
        return $this->_getData($this->_request($options['url'], 'POST', $data, $options['headers'], $options['options']), $options);
    }
    
    /**
     *
     * Flight::Model()->read(
     *       array(
     *           'columns' => array('id', 'name'),
     *           'include' => array('order'),
     *           'exclude' => array('customer'),
     *           'filter' => array(
     *               'height' => 34,
     *               'name' => array('tom', 'jane', 'smith')
     *               'members' => array(
     *                   'contains' => 'fiction',
     *                   'starts' => 'geography',
     *                   'ends' => 'world',
     *                   'between' => array('new york','seattle')
     *                   'is' => 'apple',
     *               )
     *               'weight >' => 32,
     *               'age >=' => 32,
     *               'books <' => 32,
     *               'oranges <=' => 32,
     *           ),
     *           'order' => array('asc' => array('name'), 'desc' => array('id')),
     *           'satisfy'=> array('any')
     *       ),
     *       array('transform' => true)
     *   )
     *     
     * {@inheritDoc}
     * @see GenericWebserviceDataSource::read()
     */
    public function read(Model $model, array $query = array(), array $options = array()) {
        $options['url'] = $this->_getUrl($model);
        $this->_setTableRelationships($model);
        
        if (!isset($options['url']) || empty($options['url'])) {
            throw new \ErrorException('webservice url was not provided');
        }
        
        if (isset($options['transform'])) {
            $query['transform'] = 1;
        }
        
        if (!isset($query['include']) && isset($this->config['include'])) {
            $query['include'] = $this->config['include'];
        } else {
            $query = array_merge($this->config, $query);
        }
        
        $options = $this->_resolveOptions($options);
        $querystring = $this->_getQuery($query, 'GET');
        
        $options['url'] = (!empty($query)) ? $options['url'] . "?" . $querystring : $options['url'];
        
        return $this->_getData($this->_request($options['url'], 'GET', '', $options['headers'], $options['options']), $options);
    }

    /**
     * 
     * {@inheritDoc}
     * @see GenericWebserviceDataSource::update()
     */
    public function update(Model $model, array $data = array(), array $options = array()) {
        $options['url'] = $this->_getUrl($model);
        $data = $this->_getQuery($data, 'PUT');
        $options['url'] = (!empty($data)) ? $options['url'] . "/" . $data : $options['url'];
        
        if (!isset($options['url']) || empty($options['url'])) {
            throw new \ErrorException('webservice url was not provided');
        }
        
        $options = array_merge($this->config['options'], $options);
        $data = $this->_sendData($data, $options);
        return $this->_getData($this->_request($options['url'], 'PUT', $data, $options['headers'], $options['options']), $options);
    }

    public function delete(Model $model, array $data = array(), array $options = array()) {
        $options['url'] = $this->_getUrl($model);
        $data = $this->_getQuery($data, 'DELETE');
        $options['url'] = (!empty($data)) ? $options['url'] . "/" . $data : $options['url'];
        
        if (!isset($options['url']) || empty($options['url'])) {
            throw new \ErrorException('webservice url was not provided');
        }
        
        $options = $this->_resolveOptions($options);
        return $this->_getData($this->_request($options['url'], 'DELETE', '', $options['headers'], $options['options']), $options);
    }

    /**
     * Creates the Query String 
     * 
     * {@inheritDoc}
     * @see GenericWebserviceDataSource::_getQuery()
     */
    protected function _getQuery(array $data = array(), string $method = 'GET') {
        $query = "";
        $records = array();
        switch($method) {
            case 'POST':
                break;
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                
                if (count($data) > 0) {
                    foreach($data as $item) {
                        if(isset($item['id'])) {
                            $records[] = $item['id'];
                        } else {
                            throw new \ErrorException('No entries for id found!');
                        }
                    }
                    $query = implode(',', $records);
                }
                
                break;
            case 'GET':
                
                if (count($data) > 0) {
                    
                    foreach($data as $clause => $items) {
                        
                        if (in_array($clause, $this->_clause)) {
                            
                            $prefix = "";
                            switch($clause) {
                                case 'filter':
                                    $prefix = (is_array($items) && count($items) > 1) ? $clause ."[]=" : $clause . "=";
                                    break;
                                case 'order':
                                    
                                    if (isset($items['asc'])) {
                                        $prefix = (is_array($items['asc']) && count($items['asc']) > 1) ? $clause ."[]=" : $clause . "=";
                                    } elseif (isset($items['desc'])) {
                                        $prefix = (is_array($items['desc']) && count($items['desc']) > 1) ? $clause ."[]=" : $clause . "=";
                                    } else {
                                        $prefix = (is_array($items) && count($items) > 1) ? $clause ."[]=" : $clause . "=";
                                    }
                                    
                                    break;                                   
                            }
                            
                            if (!is_array($items)) {
                                $items = array($items);  
                            } 
                            
                            foreach($items as $field => $value) {
                             
                                switch($clause) {
                                    case 'transform':
                                        
                                        if($field == 0 && $value === 1) {
                                            $records[$clause][] = $value;
                                        }
                                        break;
                                    case 'filter':
                                        
                                       if (isset($value['between'])) {
                                           if (is_array($value['between']) && count($value['between']) == 2) {
                                               $records[$clause][] = $prefix . $field . "," . $this->_filters['between'] . "," . implode(',', $value['between']);
                                            }
                                        } elseif (isset($value['contains'])) {
                                            if (!is_array($value['contains'])) {
                                                $records[$clause][] = $prefix . $field . "," . $this->_filters['contains'] . "," . $value['contains'];
                                            }
                                        } elseif (isset($value['starts'])) {
                                            if (!is_array($value['starts'])) {
                                                $records[$clause][] = $prefix . $field . "," . $this->_filters['starts'] . "," . $value['starts'];
                                            }
                                        } elseif (isset($value['ends'])) {
                                            if (!is_array($value['ends'])) {
                                                $records[$clause][] = $prefix. $field . "," . $this->_filters['ends'] . "," . $value['ends'];
                                            }
                                        } elseif (isset($value['is'])) {
                                            if (!is_array($value['is'])) {
                                                $records[$clause][] = $prefix . $field . "," . $this->_filters['is'] . "," . $value['is'];
                                            }
                                        } elseif (substr($field, strlen($field) - 3) === ' >=' && !is_array($value)) {
                                            $records[$clause][] = $prefix . substr($field, 0, strlen($field) - 3) . "," . $this->_filters['>='] . "," . $value;
                                        } elseif (substr($field, strlen($field) - 3) === ' <=' && !is_array($value)) {
                                            $records[$clause][] = $prefix . substr($field, 0, strlen($field) - 3) . "," . $this->_filters['<='] . "," . $value;
                                        } elseif (substr($field, strlen($field) - 2) === ' <' && !is_array($value)) {
                                            $records[$clause][] = $prefix . substr($field, 0, strlen($field) - 2) . "," . $this->_filters['<'] . "," . $value;
                                        } elseif (substr($field, strlen($field) - 2) === ' >' && !is_array($value)) {
                                            $records[$clause][] = $prefix . substr($field, 0, strlen($field) - 2) . "," . $this->_filters['>'] . "," . $value;
                                        } elseif (is_array($value)) {
                                            $records[$clause][] = $prefix . $field . ",in," . implode(',', $value);
                                        } else {
                                            if (!is_array($value)) {
                                                $records[$clause][] = $prefix . $field . ",eq," . $value;
                                            }
                                        }
                                        
                                        break;
                                    case 'order':
                                        
                                        if ($field === 'asc') {
                                            if (!is_array($value)) {
                                                $records[$clause][] = $prefix . $value;
                                            } else {
                                                foreach ($value as $fields) {
                                                    $records[$clause][] = $prefix . $fields;
                                                }
                                            }
                                        } elseif ($field === 'desc') {
                                            if (!is_array($value)) {
                                                $records[$clause][] = $prefix . $value . ",desc";
                                            } else {
                                                foreach ($value as $fields) {
                                                    $records[$clause][] = $prefix . $fields . ",desc";
                                                }
                                            }
                                        } elseif (is_numeric($field)) {
                                            $records[$clause][] = $prefix . $value;
                                        }
                                        
                                        break;
                                    case 'page':
                                    case 'columns':
                                    case 'exclude':
                                    case 'include':
                                    case 'satisfy':
                                        
                                        if (is_numeric($field)) {
                                            $records[$clause][] = $value;
                                        }
                                        
                                        break;
                                        
                                }
                                
                            }
                                
                        }
                        
                    }
                    
                    foreach($records as $clause => $items) {

                        switch($clause) {
                            case 'filter':
                            case 'order':

                                $query .= implode('&', $records[$clause]) . "&";
                                
                                break;
                            case 'page':
                            case 'columns':
                            case 'exclude':
                            case 'include':
                            case 'satisfy':
                            case 'transform':
                                
                                $query .= $clause . "=" . implode(',', $records[$clause]) . "&";
                                
                                break;
                                
                        }
                        
                        
                    }
                    
                }
                
                break;
        }
        
        return (!empty($query)) ? substr($query, 0, strlen($query) - 1) : $query;
    }
    
    /**
     * Get Base Url Path from Model
     * 
     * @param Model $model
     * @throws \ErrorException
     * @return string|array
     */
    protected function _getUrl(Model $model) {
        if (!isset($this->config['baseUrl']) || empty($this->config['baseUrl'])) {
            throw new \ErrorException('webservice base url was not provided');
        }
        
        return (isset($model->table)) ? $this->config['baseUrl'] . "/" . $model->table : $this->config['baseUrl'];
    }
    
    /**
     * Joins relationship to other Models
     * 
     * @param Model $model
     * @throws Exception
     */
    protected function _setTableRelationships(Model $model) {
        $config = array();
        
        foreach($model->relationships as $relationship) {
            if (!file_exists(ROOT_LOCATION . "/models/" . $relationship . ".php")) {
                throw new Exception(sprintf('Cannot find associated %s Model', $relationship));
            }
            require_once ROOT_LOCATION . "/models/" . $relationship . ".php";
            $relationshipModel = new $relationship();
            $config['include'][] = $relationshipModel->table;
        }
        
        $this->setConfig($config);
    }

    
}

