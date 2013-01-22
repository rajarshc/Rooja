<?php
/**
 * @copyright  Copyright (c) 2011 AITOC, Inc. 
 */
class Aitoc_Aitsys_Model_Database extends Aitoc_Aitsys_Abstract_Model
{
    protected $_conn;
    
    protected $_statuses;
    
    protected $_dbVersion;

    protected function _connection()
    {
        if (is_null($this->_conn)) {
            
            if (!Mage::registry('_singleton/core/resource'))
            {
                $config = $this->_config();
                $this->_conn = new Varien_Db_Adapter_Pdo_Mysql(array(
                    'host'     => (string)$config->global->resources->default_setup->connection->host,
                    'username' => (string)$config->global->resources->default_setup->connection->username,
                    'password' => (string)$config->global->resources->default_setup->connection->password,
                    'dbname'   => (string)$config->global->resources->default_setup->connection->dbname ,
                    'type'     => 'pdo_mysql' ,
                    'model'    => 'mysql4' ,
                    'active'   => 1
                ));
            }
            else
            {
                $this->_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
            }
        }
        return $this->_conn;
    }
    
    protected function _table($table)
    {
        return $this->_config()->global->resources->db->table_prefix.$table;
    }
    
    protected function _config()
    {
        if (is_null($this->_localConfig)) {
            $path = Mage::getRoot().'/etc/local.xml';
            if (file_exists($path))
            {
                $this->_localConfig = new Zend_Config_Xml($path);
            }
        }
        return $this->_localConfig;
    }
    
    public function getConfigValue($path, $defaultValue = null)
    {
        $conn = $this->_connection();
        $select = $conn->select()
           ->from($this->_table('core_config_data'))
           ->where('path = ?',$path)
           ->where('scope = ?','default');
        $data = $conn->fetchRow($select);

        //$conn->closeConnection();
        if ($data === false || !isset($data['value']) || $data['value'] === '') {
            $data = $defaultValue;
        }
        else {
            $data = $data['value'];
        }
        
        // before trying to unserialize we are replacing error_handler with another one to catch E_NOTICE run-time error
        Aitoc_Aitsys_Model_Exception::setErrorException();
        $tmpData = $data;
        try {
            $data = unserialize($data);
        }
        catch (ErrorException $e) {
            //restore old data value
            $data = $tmpData;
            unset($tmpData);
        }

        Aitoc_Aitsys_Model_Exception::restoreErrorHandler();
        return $data;
    }
    
    /**
     * Retrieves stored modules' statuses.
     * 
     *  @param string $key Module key like Aitoc_Aitmodulename
     *  
     *  @return array|bool
     */
    public function getStatus($key = '')
    {
        if(is_null($this->_statuses))
        {
            $this->_statuses = array();
            $conn = $this->_connection();
            $select = $conn->select()->from($this->_table('aitsys_status'));
            $data = $conn->fetchAll($select);
            
            foreach($data as $module)
            {
                $this->_statuses[$module['module']] = $module['status'];
            }
        }
        
        if($key)
        {
            return isset($this->_statuses[$key])?(bool)$this->_statuses[$key]:false;
        }else{
            return $this->_statuses; 
        }
    }
    
    /**
     * Return current Aitsys db resource version
     * 
     * @return string
     */
    public function dbVersion()
    {
        if(is_null($this->_dbVersion))
        {
            $conn = $this->_connection();
            $select = $conn->select()
                        ->from($this->_table('core_resource'), 'version')
                        ->where('code =?', 'aitsys_setup');
            $this->_dbVersion = $conn->fetchOne($select);
        }
        return $this->_dbVersion;
    }
}