<?php
/**
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Debug_Rule extends Mage_Core_Helper_Abstract {
	
	/**
	 *
	 * @param int $rule_id
	 * @return TBT_Rewards_Model_Salesrule_Rule
	 */
	public function disableAllRules() {
		$this->disableAllCatalogrules ();
		$this->disableAllSalesrules ();
		// delete special rules
		foreach ( Mage::getModel ( 'rewards/special' )->getCollection () as $rule ) {
			$rule->setIsActive ( 0 )->save ();
		}
		return $this;
	}
	
	/**
	 *
	 * @param int $rule_id
	 * @return 
	 */
	public function disableAllCatalogrules() {
		$rules = $this->getAllCatalogRules ();
		return $this->enableOnlyAndReturnInCollection ( $rules, - 1 );
	}
	
	/**
	 *
	 * @param int $rule_id
	 * @return 
	 */
	public function disableAllSalesrules() {
		$rules = $this->getAllSalesRules ();
		return $this->enableOnlyAndReturnInCollection ( $rules, - 1 );
	}
	
	/**
	 *
	 * @param int $rule_id
	 * @return 
	 */
	public function enableOnlyAndReturnInCollection($rules, $rule_id = 1) {
		$active_rule = null;
		foreach ( $rules as $rule ) {
			if ($rule->getId () == $rule_id) {
				if (( int ) $rule->getIsActive () == 0) {
					$active_rule = $rule->setIsActive ( 1 );
					echo "Enabling '{$rule->getName()}':[{$rule->getId()}].";
					$rule->save ();
				}
			} else {
				if (( int ) $rule->getIsActive () == 1) {
					$rule->setIsActive ( 0 );
					echo "Disabling '{$rule->getName()}':[{$rule->getId()}].";
					$rule->save ();
				}
			}
		}
		return $active_rule;
	}
	/**
	 *
	 * @param int $rule_id
	 * @return 
	 */
	public function deleteAllWithFilter($filter) {
		$this->deleteAllSalesRulesWithFilter ( $filter );
		$this->deleteAllCatalogRulesWithFilter ( $filter );
		
		// delete special rules
		foreach ( Mage::getModel ( 'rewards/special' )->getCollection () as $rule ) {
			if (strpos ( strtolower ( $rule->getName () ), strtolower ( $filter ) ) === false)
				continue;
			$rule->delete ();
		}
		return $this;
	}
	/**
	 *
	 * @param int $rule_id
	 * @return 
	 */
	public function deleteAllSalesRulesWithFilter($filter) {
		$rules = $this->getAllSalesRules ();
		$this->deleteAllRuleWithFilter ( $rules, $filter );
		return $this;
	}
	/**
	 *
	 * @param int $rule_id
	 * @return 
	 */
	public function deleteAllCatalogRulesWithFilter($filter) {
		$rules = $this->getAllCatalogRules ();
		$this->deleteAllRuleWithFilter ( $rules, $filter );
		return $this;
	}
	/**
	 *
	 * @param int $rule_id
	 * @return 
	 */
	public function deleteAllRuleWithFilter($rules, $filter) {
		echo "Deleting all rules with name containing {$filter}...";
		foreach ( $rules as $rule ) {
			if (strpos ( strtolower ( $rule->getName () ), strtolower ( $filter ) ) !== false) {
				echo "Deleting '{$rule->getName()}':[{$rule->getId()}].";
				$rule->delete ();
			}
		}
		return $this;
	}
	/**
	 *
	 * @return TBT_Rewards_Model_Mysql4_Catalogrule_Rule_Collection
	 */
	public function getAllCatalogRules() {
		return Mage::getModel ( 'catalogrule/rule' )->getCollection ();
	}
	
	/**
	 *
	 * @return TBT_Rewards_Model_Mysql4_Salesrule_Rule_Collection
	 */
	public function getAllSalesRules() {
		return Mage::getModel ( 'salesrule/rule' )->getCollection ();
	}
	
	public function applyAllCatalogRules() {
		if (Mage::helper ( 'rewards/version' )->isMageVersionAtLeast ( '1.4.2' )) {
			Mage::getModel ( 'catalogrule/rule' )->applyAll ();
		} else {
			$resource = Mage::getResourceSingleton ( 'catalogrule/rule' );
			$resource->applyAllRulesForDateRange ();
		}
		return $this;
	}
}
