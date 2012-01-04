<?php

class TBT_Rewards_Model_Catalogrule_Label extends Mage_Core_Model_Abstract {
	
	/**
	 * Resource constructor
	 * @see Varien_Object::_construct()
	 */
	protected function _construct() {
		$this->_init ( 'rewards/catalogrule_label', 'label_id' );
	}
	
	/**
	 * Returns a list of (rewards) catalog rule labels
	 * @param TBT_Rewards_Model_Catalogrule_Rule $rule
	 * @return Mage_Core_Model_Mysql4_Collection_Abstract
	 */
	public function getRuleLabels(TBT_Rewards_Model_Catalogrule_Rule $rule) {
		$ruleId = $rule->getId ();
		$ruleLabels = $this->getCollection ()->addFieldToFilter ( 'rule_id', $ruleId );
		return $ruleLabels;
	}
	
	/**
	 * Return rule labels in array format
	 * @param TBT_Rewards_Model_Catalogrule_Rule $rule
	 * @return array
	 */
	public function getRuleLabelsAsArray(TBT_Rewards_Model_Catalogrule_Rule $rule) {
		$labels = array ();
		foreach ( $this->getRuleLabels ( $rule ) as $ruleLabel ) {
			$labels [$ruleLabel->getStoreId ()] = $ruleLabel->getLabel ();
		}
		return $labels;
	}
	
	/**
	 * Remove empty label from one store
	 * @param TBT_Rewards_Model_Catalogrule_Rule $rule
	 * @param int $store_id
	 * @return void
	 */
	public function removeLabelsByRuleAndStoreId(TBT_Rewards_Model_Catalogrule_Rule $rule, $store_id) {
		$ruleLabels = $this->getLabelsByRuleAndStoreId ( $rule, $store_id );
		foreach ( $ruleLabels as $ruleLabel ) {
			$this->load ( $ruleLabel->getId () )->delete ();
		}
	}
	
	/**
	 * Get Catalog rule labels by rule and store
	 * @param TBT_Rewards_Model_Catalogrule_Rule $rule
	 * @param int $store_id
	 * @return Mage_Core_Model_Mysql4_Collection_Abstract
	 */
	public function getLabelsByRuleAndStoreId(TBT_Rewards_Model_Catalogrule_Rule $rule, $store_id) {
		$ruleLabels = $this->getRuleLabels ( $rule )->addFieldToFilter ( 'store_id', $store_id );
		return $ruleLabels;
	}

}