<?php
class TBT_Rewards_Model_Test_Salesrule extends TBT_Rewards_Model_Test_Quote {

    /**
     *
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    public function disableAllCartRules() {
    	Mage::helper('rewards/debug_salesrule')->disableAllRules();
    	return $this;
    }
    
    /**
     *
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    protected function enableOnlyAndReturn($rule_id=1) {
    	return Mage::helper('rewards/debug_salesrule')->enableOnlyAndReturn();
    }
    
    /**
     *
     * @return TBT_Rewards_Model_Mysql4_Salesrule_Rule_Collection
     */
    protected function _getAllSalesrules() {
    	return Mage::helper('rewards/debug_salesrule')->getAllRules();
    }
    
    
}