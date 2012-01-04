<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping Cart Rule discount manager
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Salesrule_Discountmanager extends Mage_Core_Model_Abstract {
	protected $discount_map = array ();
	protected $rewards_discount_map = array ();
	protected $regular_discount_map = array ();
	protected $rewards_spent_discount_map = array ();
	
	public function reset() {
		$this->discount_map = array ();
		$this->rewards_discount_map = array ();
		$this->regular_discount_map = array ();
		$this->rewards_spent_discount_map = array ();
		
		return $this;
	}
	
	public function setDiscount($rule, $discount, $base_discount) {
		$rule_id = $rule->getId ();
		$entry = array ('rule_id' => $rule_id, 'discount' => $discount, 'base_discount' => $base_discount );
		$this->discount_map [$rule_id] = $entry;
		
		if ($rule->getPointsAction ()) {
			if ($rule->getPointsAction () == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
				$this->rewards_spent_discount_map [$rule_id] = $entry;
			} else {
				$this->rewards_discount_map [$rule_id] = $entry;
			}
		} else {
			$this->regular_discount_map [$rule_id] = $entry;
		}
		
		return $this;
	}
	
	public function printDiscounts() {
		echo "All: ";
		foreach ( $this->discount_map as $d )
			echo implode ( "|", $d ) . ", ";
		echo "\n<BR />";
		echo "Rew: ";
		foreach ( $this->rewards_discount_map as $d )
			echo implode ( "|", $d ) . ", ";
		echo "  _+_ Spent: ";
		foreach ( $this->rewards_spent_discount_map as $d )
			echo implode ( "|", $d ) . ", ";
		echo "\n<BR />";
		echo "Reg: ";
		foreach ( $this->regular_discount_map as $d )
			echo implode ( "|", $d ) . ", ";
		echo "\n<BR />--";
		return $this;
	}
	
	public function getTotalNonSpendingDiscount() {
		$tnsd = 0;
		foreach ( $this->rewards_discount_map as $d ) {
			$tnsd += $d ['discount'];
		}
		foreach ( $this->regular_discount_map as $d ) {
			$tnsd += $d ['discount'];
		}
		return $tnsd;
	}
}