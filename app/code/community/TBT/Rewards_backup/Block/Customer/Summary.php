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
 * Customer Summary
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Customer_Summary extends Mage_Core_Block_Template {
	
	protected function _construct() {
		parent::_construct ();
		$this->setTemplate ( 'rewards/customer/summary.phtml' );
	}
	
	protected function _prepareLayout() {
		parent::_prepareLayout ();
	}
	
	/**
	 * Fetches a summary of the points that customer has.
	 *
	 * @return string
	 */
	public function getCustomerPointsSummary() {
		$pts = $this->htmlEscape ( $this->_getCustomer ()->getPointsSummary () );
		$pts = Mage::helper ( 'rewards' )->emphasizeThePoints ( $pts );
		return $pts;
	}
	
	/**
	 * Fetches a summary of the points that customer has.
	 *
	 * @return string
	 */
	public function getCustomerOnHoldPointsSummary() {
		$pts = $this->htmlEscape ( $this->_getCustomer ()->getOnHoldPointsSummary () );
		$pts = Mage::helper ( 'rewards' )->emphasizeThePoints ( $pts );
		return $pts;
	}
	
	/**
	 * Fetches a summary of the points that customer has.
	 *
	 * @return string
	 */
	public function getCustomerPendingPointsSummary() {
		$pts = $this->htmlEscape ( $this->_getCustomer ()->getPendingPointsSummary () );
		$pts = Mage::helper ( 'rewards' )->emphasizeThePoints ( $pts );
		return $pts;
	}
	
	/**
	 * Fetches a summary of the points that customer has.
	 *
	 * @return string
	 */
	public function getCustomerPendingTimePointsSummary() {
		$pts = $this->htmlEscape ( $this->_getCustomer ()->getPendingTimePointsSummary () );
		$pts = Mage::helper ( 'rewards' )->emphasizeThePoints ( $pts );
		return $pts;
	}
	
	public function hasPendingPoints() {
		return $this->_getCustomer ()->hasPendingPoints ();
	}
	
	public function hasActivePoints() {
		return $this->_getCustomer ()->hasPoints ();
	}
	
	public function hasOnHoldPoints() {
		return $this->_getCustomer ()->hasPointsOnHold ();
	}
	
	public function hasPendingTimePoints() {
	    return $this->_getCustomer()->hasPendingTimePoints();
	}
	
	/**
	 * Fetches the rewards session that contains customer and cart information
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	public function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	/**
	 * Fetches the rewards customer for this session
	 *
	 * @return TBT_Rewards_Model_Customer
	 */
	public function _getCustomer() {
		return $this->_getRewardsSess ()->getSessionCustomer ();
	}
	
	/**
	 * True if we should show the rewards link in the summary block.
	 * IOW true if the page is not currently the rewards page
	 *
	 * @return boolean
	 */
	public function doShowRewardsLink() {
		$page_isnt_mypoints_page = ! Mage::helper ( 'rewards' )->isCurrentPage ( 'rewards/customer/index' );
		return $page_isnt_mypoints_page;
	}
	
	/**
	 * The number of days until points expire
	 * @return int
	 */
	public function getDaysUntilExpiry() {
		$days_til_xp = Mage::getSingleton ( 'rewards/expiry' )->getDaysUntilExpiry ( $this->_getCustomer () );
		return $days_til_xp;
	}
	
	/**
	 * The date of the day that points will expire    
	 * @return Date
	 */
	public function getExpiryDate() {
		$date_xp = Mage::getSingleton ( 'rewards/expiry' )->getExpiryDate ( $this->_getCustomer () );
		return $date_xp;
	}
	
	/**
	 * Message to display in the summary box  
	 * @return string
	 */
	public function getSummaryMsg() {
		$msg = Mage::getStoreConfig ( 'rewards/display/customer_summary_msg' );
		if (empty ( $msg )) {
			$msg = ""; //$this->__('Take a look at our rewards catalog today.'); 
		}
		return $msg;
	}
	
	//@nelkaake Added on Friday September 3, 2010: 
	public function htmlEscape($data, $allowedTags = null) {
		$str = ( string ) $data;
		return parent::htmlEscape ( $data, $allowedTags );
	}

}
