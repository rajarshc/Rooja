<?php


class TBT_Rewards_Model_Salesrule_List_Valid_Applicable extends TBT_Rewards_Model_Salesrule_List_Valid {

	public function initQuote($quote) {
		$this->init($quote->getCartRedemptions());
		$this->removeDeadRules();
		$this->intersectValidRules($quote);
		return $this;
	}
	public function saveToQuote($quote) {
		$quote->setRewardsValidApplicableRedemptions($this->getCsv());
		$quote->setCartRedemptions($this->getCsv());
		return $this;
	}
	public function intersectValidRules($quote) {
		$valid = Mage::getModel('rewards/salesrule_list_valid')->initQuote($quote);
		foreach($this->getList() as $applied_rule_id) {
			if(!$valid->hasRuleId($applied_rule_id)) {
				$this->remove($applied_rule_id);
			}
		}
		return $this;
	}
}