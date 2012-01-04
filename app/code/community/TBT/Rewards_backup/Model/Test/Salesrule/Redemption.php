<?php
class TBT_Rewards_Model_Test_Salesrule_Redemption extends TBT_Rewards_Model_Test_Salesrule {

    public function runSuite() {
    	$this->o("BEGIN Salesrule_Redemption BEGIN <BR />");
    	//$test1 = Mage::getModel('rewards/test_salesrule_redemption_fixedpoints_fixeddiscount')->runSuite();
    	//$test2 = Mage::getModel('rewards/test_salesrule_redemption_bypointsspent_fixeddiscount')->runSuite();
    	$test3 = Mage::getModel('rewards/test_salesrule_redemption_fixedpoints_percentdiscount')->runSuite();
    	
    	$this->o("END Salesrule_Redemption test suite complete. <BR />");
    	return $this;
    }
    
    public function assertZeroPoints() {
    	$testCase = __METHOD__;
    	$this->o("&nbsp;&nbsp; BEGIN {$testCase}<BR />");
    	
        Mage::getSingleton('rewards/session')->setPointsSpending(0);
        $this->_refreshQuote();
       	$this->o("&nbsp;&nbsp; &nbsp;&nbsp; {$this->getQuote()->getGrandTotal()} == 149.99:  ");
        if($this->getQuote()->getGrandTotal() == 149.99) {
        	$this->o("PASS! ");
        } else {
        	$this->o("FAIL! ");
        }
        //$this->o("done. <BR />"; 
    	//$this->o("&nbsp;&nbsp; END a. 0 Points spending. ------------- <BR />");
    	
    	return $this;
    }
    
    public function applyRedemption($rule_id) {
    	$quote = $this->getQuote();
		$applied = Mage::getModel ( 'rewards/salesrule_list_applied' )->initQuote ( $quote );
		$applied->add($rule_id)->saveToQuote($quote);
		return  $this;
    }

    public function unapplyRedemption($rule_id) {
    	$quote = $this->getQuote();
		$applied = Mage::getModel ( 'rewards/salesrule_list_applied' )->initQuote ( $quote );
		$applied->remove($rule_id)->saveToQuote($quote);
		return $this;
    }

    public function prepareSettings() {
    	$this->resetTaxConfig();
    	
    	return $this;
    }
    
    public function cleanup() {
    	if($this->getRule()) {
    		$this->unapplyRedemption($this->getRule()->getId());
    		$msg = "<BR />". $this->getRule()->getName() . " (id={$this->getRule()->getId()}) has been deleted.";
    		$this->getRule()->delete();
    		$this->o($msg);
    	}
    	return $this;
    }
    
}