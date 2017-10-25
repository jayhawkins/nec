<?php

/**
 *
 *
 * @author euecheruo
 *
 */
class MysqlDataSource extends DataSource
{
    
    /**
     * 
     * @param array $config
     */
    public function __construct(array $config = array()) {
        parent::__construct($config);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see DataSource::create()
     */
    public function create(Model $model, array $data = array(), array $options = array()) {
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see DataSource::read()
     */
    public function read(Model $model, array $query = array(), array $options = array()) {
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see DataSource::update()
     */
    public function update(Model $model, array $data = array(), array $options = array()) {
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see DataSource::delete()
     */
    public function delete(Model $model, array $data = array(), array $options = array()) {
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see DataSource::describe()
     */
    public function describe(Model $model) {
        return false;
    }
    
}