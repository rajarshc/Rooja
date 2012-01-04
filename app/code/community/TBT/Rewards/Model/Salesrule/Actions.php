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
 * Sales Rules Actions
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Salesrule_Actions extends Varien_Object {
	const ACTION_GIVE_POINTS = 'give_points';
	const ACTION_GIVE_BY_AMOUNT_SPENT = 'give_by_amount_spent';
	const ACTION_GIVE_BY_QTY = 'give_by_qty';
	const ACTION_DEDUCT_POINTS = 'deduct_points';
	const ACTION_DEDUCT_BY_AMOUNT_SPENT = 'deduct_by_amount_spent';
	const ACTION_DEDUCT_BY_QTY = 'deduct_by_qty';
	const ACTION_DISCOUNT_BY_POINTS_SPENT = 'discount_by_points_spent';
	
	public function getOptionArray() {
		$options = array_merge ( array ('' => Mage::helper ( 'rewards' )->__ ( '--Don\'t Transfer Points--' ) ), $this->getDistributionsOptionArray (), array ('' => Mage::helper ( 'rewards' )->__ ( '--  --' ) ), $this->getRedemptionsOptionArray () );
		return $options;
	}
	
	public function getRedemptionsOptionArray() {
		return array (self::ACTION_DEDUCT_POINTS => Mage::helper ( 'rewards' )->__ ( 'Deduct X points from customer' ), //self::ACTION_DEDUCT_BY_AMOUNT_SPENT => Mage::helper('rewards')->__('For every Y amount spent, deduct X points [in development]'),
		//self::ACTION_DEDUCT_BY_QTY => Mage::helper('rewards')->__('For every Z qty purchased, deduct X points [in development]'),
		self::ACTION_DISCOUNT_BY_POINTS_SPENT => Mage::helper ( 'rewards' )->__ ( ' For every X points spent, discount Y' ) )

		;
	}
	
	public function getDistributionsOptionArray() {
		return array (self::ACTION_GIVE_POINTS => Mage::helper ( 'rewards' )->__ ( 'Give X points to customer' ), self::ACTION_GIVE_BY_AMOUNT_SPENT => Mage::helper ( 'rewards' )->__ ( 'For every Y amount spent, give X points' ), self::ACTION_GIVE_BY_QTY => Mage::helper ( 'rewards' )->__ ( 'For every Z qty purchased, give X points' ) )

		;
	}
	
	public function getDistributionActions() {
		return array (self::ACTION_GIVE_POINTS, self::ACTION_GIVE_BY_AMOUNT_SPENT, self::ACTION_GIVE_BY_QTY );
	}
	
	public function getRedemptionActions() {
		return array (self::ACTION_DEDUCT_POINTS, self::ACTION_DEDUCT_BY_AMOUNT_SPENT, self::ACTION_DEDUCT_BY_QTY, self::ACTION_DISCOUNT_BY_POINTS_SPENT );
	}
	
	/**
	 * Is the specified action a give points action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isGivePointsAction($action) {
		return $action == self::ACTION_GIVE_POINTS;
	}
	
	/**
	 * Is the specified action a GIVE_BY_AMOUNT_SPENT action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isGiveByAmountSpentAction($action) {
		return $action == self::ACTION_GIVE_BY_AMOUNT_SPENT;
	}
	
	/**
	 * Is the specified action a ACTION_GIVE_BY_QTY action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isGiveByQtyAction($action) {
		return $action == self::ACTION_GIVE_BY_QTY;
	}
	
	/**
	 * Is the specified action a DEDUCT_POINTS action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isDeductPointsAction($action) {
		return $action == self::ACTION_DEDUCT_POINTS;
	}
	
	/**
	 * Is the specified action a DEDUCT_BY_AMOUNT_SPENT action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isDeductByAmountSpentAction($action) {
		return $action == self::ACTION_DEDUCT_BY_AMOUNT_SPENT;
	}
	
	/**
	 * Is the specified action a ACTION_DEDUCT_BY_QTY action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isDeductByQtyAction($action) {
		return $action == self::ACTION_DEDUCT_BY_QTY;
	}
	
	/**
	 * Is the specified action a DISCOUNT_BY_POINTS_SPENT action?
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isDiscountByPointsSpentAction($action) {
		return $action == self::ACTION_DISCOUNT_BY_POINTS_SPENT;
	}
	
	/**
	 * Returns true if the specified action deducts points from the customer
	 * in any way shape or form.
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isRedemptionAction($action) {
		return $this->isDeductPointsAction ( $action ) || $this->isDeductByAmountSpentAction ( $action ) || $this->isDeductByQtyAction ( $action ) || $this->isDiscountByPointsSpentAction ( $action );
	}
	
	/**
	 * Returns true if the specified action distributes points to the customer
	 * in any way shape or form.
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isDistributionAction($action) {
		return $this->isGivePointsAction ( $action ) || $this->isGiveByQtyAction ( $action ) || $this->isGiveByAmountSpentAction ( $action );
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