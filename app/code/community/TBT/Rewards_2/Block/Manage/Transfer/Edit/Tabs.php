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
 * Manage Transfer Edit Tabs
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Transfer_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'transfer_tabs' );
		$this->setDestElementId ( 'edit_form' );
		$this->setTitle ( Mage::helper ( 'rewards' )->__ ( 'Transfer Information' ) );
	}
	
	protected function _beforeToHtml() {
		$this->addTab ( 'form_section', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Transfer Information' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Transfer Information' ), 'content' => $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_form' )->toHtml () ) );
		
		$this->addTab ( 'customers_section', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Customer' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Customer' ), 'content' => $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_customer_grid' )->toHtml () ) );
		
		$transfer = $this->_getTransfer ();
		if ($transfer->isOrder ()) {
			if (Mage::getSingleton ( 'admin/session' )->isAllowed ( 'sales/order/actions/view' )) {
				$this->addTab ( 'orders_section', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Reference Order' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Reference Order' ), 'content' => $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_orders' )->toHtml () ) );
			}
		}
		if ($transfer->isPoll ()) {
			$this->addTab ( 'polls_section', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Reference Poll' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Reference Poll' ), 'content' => $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_polls' )->toHtml () ) );
		}
		
		if ($transfer->isFriendTransfer ()) {
			$this->addTab ( 'friends_section', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Reference Friend' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Reference Friend' ), 'content' => $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_friends' )->toHtml () ) );
		}
		if ($transfer->isTransfer ()) {
			$this->addTab ( 'reviews_section', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Reference Other Transfer' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Reference Other Transfer' ), 'content' => $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_transfers' )->toHtml () ) );
		}
		
		return parent::_beforeToHtml ();
	}
	
	/**
	 * Fetches the transfer we want to edit.
	 *
	 * @return TBT_Rewards_Model_Transfer
	 */
	protected function _getTransfer() {
		return Mage::registry ( 'transfer_data' );
	}

}