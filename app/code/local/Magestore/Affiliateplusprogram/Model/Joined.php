<?php

class Magestore_Affiliateplusprogram_Model_Joined extends Mage_Core_Model_Abstract
{
    public function _construct() {
        parent::_construct();
        $this->_init('affiliateplusprogram/joined');
    }
    
    public function updateJoined($program = null, $account = null) {
        if (is_object($program)) {
            $program = $program->getId();
        }
        if (is_object($account)) {
            $account = $account->getId();
        }
        $this->_getResource()->updateJoinedDatabase($program, $account);
        return $this;
    }
    
    public function insertJoined($program = null, $account = null) {
        if (is_object($program)) {
            $program = $program->getId();
        }
        if (is_object($account)) {
            $account = $account->getId();
        }
        if ($program) {
            $this->setData('program_id',  $program);
        }
        if ($account) {
            $this->setData('account_id', $account);
        }
        $this->_getResource()->insertJoinedDatabase($this);
        return $this;
    }
}
