<?php


class TBT_Rewards_Model_Salesrule_List_Valid extends TBT_Rewards_Model_Salesrule_List {
	
	public function initQuote($quote) {
		$this->init($quote->getRewardsValidRedemptions());
		return $this;
	}
	public function saveToQuote($quote) {
		$quote->setRewardsValidRedemptions($this->getCsv());
		return $this;
	}
}