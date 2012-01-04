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
 * Customer Sidebar
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Customer_Sidebar extends Mage_Core_Block_Template {
	
	protected function _construct() {
		parent::_construct ();
		$this->_controller = 'customer';
		$this->_blockGroup = 'rewards';
		$this->headerText = "My Points & Rewards";
		$this->setTemplate ( 'rewards/customer/sidebar.phtml' );
	}
	
	protected function _prepareLayout() {
		parent::_prepareLayout ();
	}
	
	/**
	 * Fetches a string representing the number of points being spent in the cart
	 *
	 * @return string
	 */
	public function getPointsSpending() {
		$str = $this->_getRewardsHelper ()->emphasizeThePoints ( $this->_getRewardsSess ()->getTotalPointsSpendingAsString () );
		return $str;
	}
	
	/**
	 * Fetches a string of the number of points the customer will earn from the cart.
	 *
	 * @return string
	 */
	public function getPointsEarning() {
		$str = $this->_getRewardsHelper ()->emphasizeThePoints ( $this->_getRewardsSess ()->getTotalPointsEarningAsString () );
		return $str;
	}
	
	/**
	 * Fetches a string with the customer points summary.
	 *
	 * @return string
	 */
	public function getCustomerPoints() {
		$str = $this->_getRewardsHelper ()->emphasizeThePoints ( $this->_getRewardsSess ()->getSessionCustomer ()->getPointsSummary () );
		return $str;
	}
	
	/**
	 * Fetches a string of the number of points remaining in the cart.
	 *
	 * @return string
	 */
	public function getPointsRemaining() {
		$points_remain_str = $this->_getRewardsHelper ()->emphasizeThePoints ( $this->_getRewardsSess ()->getTotalPointsRemainingAsString () );
		return $points_remain_str;
	}
	
	/**
	 * True if the customer is spending any points in their cart.
	 * False otherwise
	 *
	 * @return boolean
	 */
	public function isSpendingAnyPoints() {
		return $this->_getRewardsSess ()->hasRedemptions ();
	}
	
	/**
	 * True if the cart has overspending in it.
	 *
	 * @return boolean
	 */
	public function isCartOverspent() {
		$overspent = $this->_getRewardsSess ()->isCartOverspent ();
		return $overspent;
	}
	
	/**
	 * True if the customer is logged in.
	 *
	 * @return boolean
	 */
	public function isCustomerLoggedIn() {
		$logged_in = $this->_getRewardsSess ()->isCustomerLoggedIn ();
		return $logged_in;
	}
	
	protected function _toHtml() {
		$showSidebarWhenNotLoggedIn = Mage::helper ( 'rewards/config' )->showSidebarIfNotLoggedIn ();
		$showSidebar = Mage::helper ( 'rewards/config' )->showSidebar ();
		if (($this->isCustomerLoggedIn () || $showSidebarWhenNotLoggedIn) && $showSidebar) {
			return parent::_toHtml ();
		} else {
			return '';
		}
	}
	
	/**
	 * Fetches the customer rewards session.
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	/**
	 * Fetches the customer rewards helper singelton class
	 *
	 * @return TBT_Rewards_Helper_Data
	 */
	protected function _getRewardsHelper() {
		return Mage::helper ( 'rewards' );
	}

}
