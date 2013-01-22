<?php
class Aitoc_Aitsys_Model_Notification_Store extends Aitoc_Aitsys_Abstract_Model
{
    const CONFIG_ENTITY_PREFIX = 'notification_date_';
    
    protected $_configEntities;
    
    protected $_requireSave = false;
    
    /**
    * 
    * @return Mage_Core_Model_Config_Data
    */
    protected function _getConfigData()
    {
        return Mage::getModel('core/config_data');
    }
    
    /**
     * @return array
     */
    protected function _getAllEntities()
    {
        if(is_null($this->_configEntities))
        {
            $this->_configEntities = array();
            $adapter = $this->_getReadConnection();
            $select = $adapter->select()->from(Mage::getResourceSingleton('core/config_data')->getMainTable())
            	->where('scope=?', 'default')
            	->where('scope_id=?', 0)
            	->where('path like ?', self::CONFIG_ENTITY_PREFIX.'%');
            $data = $adapter->fetchAll($select);
            foreach ($data as $entity)
            {
                $this->_configEntities[$entity['path']] = $entity;
            }
        }
        return $this->_configEntities;
    }
    
    /**
    * @param Aitoc_Aitsys_Model_Module $module
    * @return Mage_Core_Model_Config_Data
    */
    protected function _loadModel( Aitoc_Aitsys_Model_Module $module )
    {
        $model = $this->_getConfigData();
        $entities = $this->_getAllEntities();
        $key = $this->_makeKey($module);
        $data = key_exists($key, $entities) ? $entities[$key] : null; 
        return $model->setData($data);
        return $this->_getConfigData()->load($this->_makeKey($module),'path');
    }
    
    public function getNotificationDate( Aitoc_Aitsys_Model_Module $module )
    {
        return $this->_loadModel($module)->getValue();
        #return $this->tool()->platform()->getData($this->_makeKey($module));
    }
    
    public function verifyNotificationDate( Aitoc_Aitsys_Model_Module $module )
    {
        return $this->getNotificationDate($module) > time();
    }
    
    /**
     * 
     * @param Aitoc_Aitsys_Model_Module $module
     * @return Aitoc_Aitsys_Model_Notification_Store
     */
    public function resetNotificationDate( Aitoc_Aitsys_Model_Module $module )
    {
        $this->_deleteModel($this->_loadModel($module));
        #$this->_requireSave = true;
        #$this->tool()->platform()->unsetData($this->_makeKey($module));
        return $this;
    }
    
    protected function _deleteModel( Mage_Core_Model_Abstract $model )
    {
        $adapter = $this->_getWriteConnection();
        $resource = $model->getResource();
        $condition = $adapter->quoteInto($resource->getIdFieldName().'=?', $model->getId());
        $adapter->delete($resource->getMainTable(),$condition);
    }
    
    /**
     * 
     * @param Aitoc_Aitsys_Model_Module $module
     * @return Aitoc_Aitsys_Model_Notification_Store
     */
    public function setNotificationDate( Aitoc_Aitsys_Model_Module $module )
    {
        #$this->_requireSave = true;
        #$this->tool()->platform()->setData($this->_makeKey($module),time()+3600*24*7);
        $model = $this->_loadModel($module);
        if (!$model->getId())
        {
            $model->setData(array(
                'scope' => 'default' ,
                'scope_id' => 0 ,
                'path' => $this->_makeKey($module)
            ));
        }
        $notifyDays = 14;
        if ($this->tool()->platform()->hasData("notify_days")) {
            $notifyDays = $this->tool()->platform()->getData("notify_days");
        }
        $this->_saveModel($model->setValue(time()+3600*24*$notifyDays));
        return $this;
    }
    
    /**
    * 
    * @return Zend_Db_Adapter_Abstract
    */
    protected function _getWriteConnection()
    {
        return Aitoc_Aitsys_Abstract_Service::get()->getWriteConnection();
    }
    
    /**
    * 
    * @return Zend_Db_Adapter_Abstract
    */
    protected function _getReadConnection()
    {
        return Aitoc_Aitsys_Abstract_Service::get()->getReadConnection();
    }
    
    protected function _saveModel( Mage_Core_Model_Abstract $model )
    {
        $resource = $model->getResource();
        $adapter = $this->_getWriteConnection();
        if ($model->getId())
        {
            $condition = $adapter->quoteInto($resource->getIdFieldName().'=?', $model->getId());
            $adapter->update($resource->getMainTable(),$model->getData(),$condition);
        }
        else
        {
            $adapter->insert($resource->getMainTable(),$model->getData());
        }
    }
    
    protected function _makeKey( Aitoc_Aitsys_Model_Module $module )
    {
        return self::CONFIG_ENTITY_PREFIX.strtolower($module->getKey());
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification_Store
     */
    public function saveData()
    {
        #if ($this->_requireSave)
        #{
        #    $this->tool()->platform()->save();
        #}
        #$this->_requireSave = false;
        return $this;
    }
    
}