<?php

class Magestore_Affiliateplusprogram_Model_Mysql4_Joined extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct(){
        $this->_init('affiliateplusprogram/joined', 'id');
    }
    
    public function updateJoinedDatabase($programId = null, $accountId = null) {
        $adapter = $this->_getWriteAdapter();
        $selectSQL = $adapter->select()->reset()
            ->from(array('a' => $this->getTable('affiliateplusprogram/account')), array())
            ->columns(array('program_id', 'account_id'));
        if ($programId) {
            $selectSQL->where('program_id = ?', $programId);
        }
        if ($accountId) {
            $selectSQL->where('account_id = ?', $accountId);
        }
        $insertSQL = $selectSQL->insertFromSelect($this->getMainTable(),
            array('program_id', 'account_id'),
            true
        );
        $adapter->query($insertSQL);
        return $this;
    }
    
    public function insertJoinedDatabase(Mage_Core_Model_Abstract $object) {
        $adapter = $this->_getWriteAdapter();
        
        $select = $adapter->select()
            ->from($this->getMainTable(), array($this->getIdFieldName()))
            ->where('program_id = ?', $object->getData('program_id'))
            ->where('account_id = ?', $object->getData('account_id'));
        if ($adapter->fetchOne($select) === false) {
            $object->setId(null);
            $this->save($object);
        }
        
        return $this;
    }
}
