<?php


class TBT_Rewards_Model_Salesrule_List_Item_Applied extends TBT_Rewards_Model_Salesrule_List {
	
	public function initItem($item) { 
		$this->init($item->getAppliedRuleIds());
		return $this;
	}
}