<?php

class Magestore_Affiliateplusprogram_Model_Account extends Mage_Core_Model_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplusprogram/account');
    }
    
    public function saveAll(){
    	if ($this->getProgramId()){
    		$this->setJoined(now());
    		$newAccountIds = array();
    		if ($this->getAccountIds() && is_array($this->getAccountIds()))
    			$newAccountIds = array_combine($this->getAccountIds(),$this->getAccountIds());
    		
    		$collection = $this->getCollection()->addFieldToFilter('program_id',$this->getProgramId());
    		foreach ($collection as $account){
    			$accountId = $account->getAccountId();
    			if (in_array($accountId,$newAccountIds))
    				unset($newAccountIds[$accountId]);
    			else 
    				$this->setId($account->getId())->delete();
    		}
    		$this->addAccount($newAccountIds);
    	}
    	return $this;
    }
    
    /**
     * Add Account to table
     *
     * @param array $accountIds
     * @return Magestore_Affiliateplusprogram_Model_Account
     */
    public function addAccount($accountIds){
    	foreach ($accountIds as $account)
    		if (is_numeric($account))
    			$this->setAccountId($account)->setId(null)->save();
    	return $this;
    }
    
    /**
     * remove Account from table
     *
     * @param array $accountIds
     * @return Magestore_Affiliateplusprogram_Model_Account
     */
    public function removeAccount($accountIds){
    	foreach ($accountIds as $account){
    		if (is_numeric($account))
    			$this->setId($account)->delete();
    	}
    	return $this;
    }
}