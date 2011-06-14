<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Manage Transfer Edit Tab Grid Transfers
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Customer_Edit_Tab_Summary extends Mage_Adminhtml_Block_Widget_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	
	protected function _construct() {
		parent::_construct ();
	
		// 		$this->_headerText = Mage::helper('rewards')->__('Transfer Manager');
	// 		$this->setTemplate('rewards/customer/edit/tab/summary.phtml');
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
	
	public function hasPendingPoints() {
		return $this->_getCustomer ()->hasPendingPoints ();
	}
	
	public function hasActivePoints() {
		return $this->_getCustomer ()->hasPoints ();
	}
	
	public function hasOnHoldPoints() {
		return $this->_getCustomer ()->hasPointsOnHold ();
	}
	
	/**
	 * Fetches the rewards customer for this session
	 *
	 * @return TBT_Rewards_Model_Customer
	 */
	
	/**
	 * Retrieve available customer
	 *
	 * @return Mage_Model_Customer
	 */
	public function _getCustomer() {
		if ($this->hasCustomer ()) {
			return $this->getData ( 'customer' );
		}
		if (Mage::registry ( 'current_customer' )) {
			return Mage::getModel('rewards/customer')->load(Mage::registry ( 'current_customer' )->getId());
		}
		if (Mage::registry ( 'customer' )) {
			return Mage::getModel('rewards/customer')->load(Mage::registry ( 'customer' )->getId());
		}
		Mage::throwException ( Mage::helper ( 'customer' )->__ ( 'Can\'t get customer instance' ) );
	}
	
	/**
	 * ######################## TAB settings #################################
	 */
	public function getTabLabel() {
		return $this->__ ( "Points & Rewards" );
	}
	
	public function getTabTitle() {
		return $this->__ ( "Points & Rewards" );
	}
	
	public function canShowTab() {
		if (! Mage::helper ( 'rewards/loyalty_checker' )->isValid ()) {
			return false;
		}
		return true;
	}
	
	public function isHidden() {
		return false;
	}

}

?>