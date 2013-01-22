<?php
class Aitoc_Aitsys_Model_Module_Status extends Aitoc_Aitsys_Abstract_Model
{
    static protected $_statuses = array();
    
    /**
     * Static fabric interface to update statuses. Returns related status model instance
     * 
     * @return Aitoc_Aitsys_Model_Module_Status
     */
    public static function updateStatus($moduleKey, $status)
    {
        if(!array_key_exists($moduleKey, self::$_statuses))
        {
            self::$_statuses[$moduleKey] = new self();
            self::$_statuses[$moduleKey]
                ->load($moduleKey, 'module')
                ->setModule($moduleKey);
        }
        self::$_statuses[$moduleKey]
            ->setStatus((int)$status)
            ->save();
        return self::$_statuses[$moduleKey];
    }

    protected function _construct()
    {
        $this->_init('aitsys/module_status');
    }
    
    /**
     * Prevents events in Mage_Core_Model_Abstract from launching (compatibility with Aitpermissions).
     * 
     * @override
     * @return Aitoc_Aitsys_Model_Module_Status
     */
    protected function _beforeSave()
    {
        return $this;
    }
    
    /**
     * @return Aitoc_Aitsys_Model_Module_Status
     */
    protected function _afterSave()
    {
        $this->getCollection()->clearTable();
        return parent::_afterSave();
    }
}