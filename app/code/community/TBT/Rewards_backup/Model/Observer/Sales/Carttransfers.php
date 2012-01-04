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
 * Observer Sales Cart Transfer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Observer_Sales_Carttransfers {
	
	private $cart_redem_rule_ids = null;
	private $all_cart_points = null;
	private $increment_id_to_apply_points = null;
	private $total_earned_string = null;
	private $total_redeemed_string = null;
	
	public function __construct() {
		$this->total_earned_string = Mage::helper ( 'rewards' )->__ ( 'No points' );
		$this->total_redeemed_string = Mage::helper ( 'rewards' )->__ ( 'No points' );
	}
	
	public function addCartPoints($cart_point_totals) {
		if (! $this->all_cart_points) {
			$this->all_cart_points = array ();
		}
		
		$this->all_cart_points [] = $cart_point_totals;
		
		return $this;
	}
	
	public function getAllCartPoints() {
		if (! $this->all_cart_points) {
			return array ();
		}
		
		return $this->all_cart_points;
	}
	
	public function clearCartPoints() {
		$this->all_cart_points = null;
		return $this;
	}
	
	public function addRedemptionRuleId($rule_id) {
		if (! $this->cart_redem_rule_ids) {
			$this->cart_redem_rule_ids = array ();
		}
		
		$this->cart_redem_rule_ids [] = $rule_id;
		
		return $this;
	}
	
	public function setRedemptionRuleIds($rule_ids) {
		$this->cart_redem_rule_ids = $rule_ids;
		return $this;
	}
	
	public function getRedemptionRuleIds() {
		if (! $this->cart_redem_rule_ids) {
			return array ();
		}
		
		return $this->cart_redem_rule_ids;
	}
	
	public function clearRedemptionRuleIds() {
		$this->cart_redem_rule_ids = null;
		return $this;
	}
	
	public function setIncrementId($increment_id) {
		$this->increment_id_to_apply_points = $increment_id;
		return $this;
	}
	
	public function getIncrementId() {
		return $this->increment_id_to_apply_points;
	}
	
	public function clearIncrementId() {
		$this->increment_id_to_apply_points = null;
		return $this;
	}
	
	public function setEarnedPointsString($string) {
		$this->total_earned_string = $string;
		return $this;
	}
	
	public function getEarnedPointsString() {
		return $this->total_earned_string;
	}
	
	public function clearEarnedPointsString() {
		$this->total_earned_string = Mage::helper ( 'rewards' )->__ ( 'No points' );
		return $this;
	}
	
	public function setRedeemedPointsString($string) {
		$this->total_redeemed_string = $string;
		return $this;
	}
	
	public function getRedeemedPointsString() {
		return $this->total_redeemed_string;
	}
	
	public function clearRedeemedPointsString() {
		$this->total_redeemed_string = Mage::helper ( 'rewards' )->__ ( 'No points' );
		return $this;
	}

}