<?php

class Aitoc_Aitsys_Model_Core_Cache_Resource
{
    protected $_options = array();
    
    /**
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_connection;
    
    protected $_tablePrefix;
    
    public function __construct($config)
    {
        // get adapter type
        $connType = $config['resources']['default_setup']['connection']['type'];
        $connClassName = $config['resource']['connection']['types'][$connType]['class'];
        $typeInstance = new $connClassName();
        /* @var $typeInstance Mage_Core_Model_Resource_Type_Db_Pdo_Mysql */
        
        $this->_connection = $typeInstance->getConnection($config['resources']['default_setup']['connection']);
        
        $this->_tablePrefix = $config['resources']['db']['table_prefix'];
    }
    
    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    public function getConnection()
    {
        return $this->_connection;
    }
    
    public function getTableName($name)
    {
        return $this->getTablePrefix() . str_replace('/', '_', $name);
    }
    
    public function getTablePrefix()
    {
        return $this->_tablePrefix;
    }
}