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
 * Catalog Rule Actions
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Catalogrule_Actions extends Varien_Object {
	const GIVE_POINTS_ACTION = 'give_points';
	const GIVE_BY_AMOUNT_SPENT_ACTION = 'give_by_amount_spent';
	const GIVE_BY_PROFIT_ACTION = 'give_by_profit';
	const DEDUCT_POINTS_ACTION = 'deduct_points';
	const DEDUCT_BY_AMOUNT_SPENT_ACTION = 'deduct_by_amount_spent';
	const DISCOUNT_BY_POINTS_SPENT_ACTION = 'discount_by_points_spent';
	
	public function getOptionArray() {
		return array_merge ( $this->getRedemptionOptionArray (), array ('' => Mage::helper ( 'rewards' )->__ ( '--  --' ) ), $this->getDistributionOptionArray () );
	}
	
	public function getRedemptionOptionArray() {
		return array (self::DEDUCT_POINTS_ACTION => Mage::helper ( 'rewards' )->__ ( 'Spends X Points' ), self::DEDUCT_BY_AMOUNT_SPENT_ACTION => Mage::helper ( 'rewards' )->__ ( 'Spends X points for every Y dollar amount in price' ) );
	}
	
	public function getDistributionOptionArray() {
		return array (self::GIVE_POINTS_ACTION => Mage::helper ( 'rewards' )->__ ( 'Give X points to customer' ), self::GIVE_BY_AMOUNT_SPENT_ACTION => Mage::helper ( 'rewards' )->__ ( 'For every Y dollar amount in PRICE, give X points' ), self::GIVE_BY_PROFIT_ACTION => Mage::helper ( 'rewards' )->__ ( 'For every Y dollar amount in PROFIT, give X points' ) );
	}
	
	/**
	 * Is the specified action a give points action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isGivePointsAction($action) {
		return $action == self::GIVE_POINTS_ACTION;
	}
	
	/**
	 * Is the specified action a GIVE_BY_AMOUNT_SPENT action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isGiveByAmountSpentAction($action) {
		return $action == self::GIVE_BY_AMOUNT_SPENT_ACTION;
	}
	
	/**
	 * Is the specified action a GIVE_BY_PROFIT_ACTION action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isGiveByProfitAction($action) {
		return $action == self::GIVE_BY_PROFIT_ACTION;
	}
	
	/**
	 * Is the specified action a DEDUCT_POINTS action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isDeductPointsAction($action) {
		return $action == self::DEDUCT_POINTS_ACTION;
	}
	
	/**
	 * Is the specified action a DEDUCT_BY_AMOUNT_SPENT action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isDeductByAmountSpentAction($action) {
		return $action == self::DEDUCT_BY_AMOUNT_SPENT_ACTION;
	}
	
	/**
	 * Is the specified action a DISCOUNT_BY_POINTS_SPENT action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isDiscountByPointsSpentAction($action) {
		return $action == self::DISCOUNT_BY_POINTS_SPENT_ACTION;
	}
	
	/**
	 * Returns true if the specified action deducts points from the customer
	 * in any way shape or form.
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isRedemptionAction($action) {
		return $this->isDeductPointsAction ( $action ) || $this->isDeductByAmountSpentAction ( $action ) || $this->isDiscountByPointsSpentAction ( $action );
	}
	
	/**
	 * Returns an arrya of actions that are considered redemptions
	 *
	 * @return array
	 */
	public function getRedemptionActions() {
		return array (self::DEDUCT_POINTS_ACTION, self::DEDUCT_BY_AMOUNT_SPENT_ACTION, self::DISCOUNT_BY_POINTS_SPENT_ACTION );
	}
	
	/**
	 * Returns true if the specified action distributes points to the customer
	 * in any way shape or form.
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isDistributionAction($action) {
		return $this->isGivePointsAction ( $action ) || $this->isGiveByAmountSpentAction ( $action ) || $this->isGiveByProfitAction ( $action );
	}
	
	/**
	 * Fetches all the action codes that are considered distributions
	 *
	 * @return array
	 */
	public function getDistributionActions() {
		return array (self::GIVE_POINTS_ACTION, self::GIVE_BY_AMOUNT_SPENT_ACTION, self::GIVE_BY_PROFIT_ACTION );
	}
	
	/**
	 * Returns a type id for this rule.
	 * A type id specifies whether this is a distribution or redemption.
	 * 
	 * @see TBT_Rewards_Helper_Rule_Type
	 *
	 * @param string $action
	 * @return int
	 */
	public function getRuleTypeId($action) {
		if ($this->isRedemptionAction ( $action )) {
			$typeId = TBT_Rewards_Helper_Rule_Type::REDEMPTION;
		} elseif ($this->isDistributionAction ( $action )) {
			$typeId = TBT_Rewards_Helper_Rule_Type::DISTRIBUTION;
		} else {
			$typeId = null;
		}
		return $typeId;
	}

}