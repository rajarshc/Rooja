<?php


class TBT_Rewards_Model_Salesrule_List extends Varien_Object {
	
	protected $list = array();
	
	public function getList() {
		return $this->list;
	}
	
	public function init($original_list = array()) {
		if(!is_array($original_list)) {
        	$original_list = explode(',', $original_list);
		} 
		$this->list = $original_list;
		$this->clean();
		return $this;
	}
	
	public function getCsv() {
		$this->clean();
		$csv = implode(",", $this->list);
		$csv = trim($csv, ',');
		return $csv;
	}
	
	public function hasRuleId($rule_id) {
		return array_search($rule_id, $this->list) !== false;
	}

	public function hasRule($rule) {
		if(is_numeric($rule)) {
			return $this->hasRule($rule);
		}
		return array_search($rule->getId(), $this->list) !== false;
	}
	
	public function add($rule) {
		if($rule instanceof Varien_Object || $rule instanceof Mage_SalesRule_Model_Rule) {
			$rule_id = $rule->getId();
		} else {
			$rule_id = (int)$rule;
		}
		if(!$this->hasRuleId($rule_id)) {
			$this->list[] = $rule_id;
		}
		return $this;
	}

	public function remove($rule) {
		$rule_id = ($rule instanceof Varien_Object) ? $rule->getId() : $rule;
		
		if(empty($rule)) return $this;
		
		if($this->hasRuleId($rule_id)) {
			$this->list = array_values(array_diff($this->list,array($rule_id)));
		}
		return $this;
	}
	
	/**
	 * unifies the list
	 */
	public function clean() {
		$this->list = array_unique($this->list);
		
		// Remove all the empty values in the list
		foreach($this->list as $index => $item) {
			if(empty($item)) {
				unset($this->list[$index]);
			}
		}
		return $this;		
	}
	
	public function out() {
		return "[". $this->getCsv() . "]";
	}


	/**
	 * Removes all rules that don't have an ID and/or are non-existent
	 */
	public function removeDeadRules() {
        //@nelkaake -a 11/03/11: if any rules were deleted, remove them from the list.
        foreach ($this->getList() as $rule_id) {
        	$rule = Mage::helper('rewards/rule')->getSalesrule($rule_id);
        	if(empty($rule)) {
        		$this->remove($rule_id);
        		continue;
        	}
        	if(!$rule->getRuleId()) {
        		$this->remove($rule_id);
        	}
        }
        return $this;
	}
}