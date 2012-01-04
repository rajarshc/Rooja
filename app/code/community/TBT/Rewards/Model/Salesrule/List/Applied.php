<?php


class TBT_Rewards_Model_Salesrule_List_Applied extends TBT_Rewards_Model_Salesrule_List {
	
	public function initQuote($quote) {
		$this->init($quote->getAppliedRedemptions());
		$this->removeDeadRules();
		return $this;
	}
	public function saveToQuote($quote) {
		$quote->setRewardsAppliedRedemptions($this->getCsv());
		$quote->setAppliedRedemptions($this->getCsv());
		return $this;
	}
	
	
}