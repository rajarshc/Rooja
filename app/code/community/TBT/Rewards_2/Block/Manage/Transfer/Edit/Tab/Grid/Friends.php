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
 * Manage Transfer Edit Tab Grid Friends
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Transfer_Edit_Tab_Grid_Friends extends TBT_Rewards_Block_Manage_Transfer_Edit_Tab_Customer_Grid {
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'rewards_transfer_edit_friends_grid' );
		$this->setUseAjax ( true );
		$this->setDefaultSort ( 'entity_id' );
	}
	
	/**
	 * Retirve currently edited product model
	 *
	 * @return Mage_Catalog_Model_Product
	 */
	protected function _getTransfer() {
		return Mage::registry ( 'transfer_data' );
	}
	
	protected function _prepareLayout() {
		
		$this->setChild ( 'clear_selections_button', $this->getLayout ()->createBlock ( 'adminhtml/widget_button' )->setData ( array ('label' => Mage::helper ( 'adminhtml' )->__ ( 'Clear Selections' ), 'onclick' => 'clearGridSelections(\'friend_id\')' ) ) );
		return parent::_prepareLayout ();
	}
	
	public function getClearSelectionsButtonHtml() {
		return $this->getChildHtml ( 'clear_selections_button' );
	}
	
	public function getMainButtonsHtml() {
		return $this->getClearSelectionsButtonHtml () . parent::getMainButtonsHtml ();
	}
	
	protected function _addColumnFilterToCollection($column) {
		//echo $this->getCollection()->getSelect()->__toString();
		// Set custom filter for in product flag
		if ($column->getId () == 'assigned_customer') {
			$customerIds = $this->_getSelectedCustomers ();
			if (empty ( $customerIds )) {
				$customerIds = 0;
			}
			if ($column->getFilter ()->getValue ()) {
				//$this->getCollection()->addFieldToFilter('friend_id', array('in'=>$customerIds));
				$this->getCollection ()->addFieldToFilter ( 'entity_id', array ('in' => $customerIds ) );
			} else {
				if ($customerIds) {
					//$this->getCollection()->addFieldToFilter('friend_id', array('nin'=>$customerIds));
					$this->getCollection ()->addFieldToFilter ( 'entity_id', array ('nin' => $customerIds ) );
				}
			}
		} else {
			parent::_addColumnFilterToCollection ( $column );
		}
		return $this;
	}
	
	protected function _prepareColumns() {
		//die (print_r($this->_getSelectedCustomers())+"|");
		$this->addColumn ( 'assigned_customer', array ('header_css_class' => 'a-center', 'header' => Mage::helper ( 'adminhtml' )->__ ( 'Assigned' ), 'type' => 'radio', 'html_name' => 'friend_id', 'values' => $this->_getSelectedCustomers (), 'align' => 'center', 'index' => 'entity_id', 'filter_index' => 'entity_id' ) );
		
		$this->addColumn ( 'entity_id', array ('header' => Mage::helper ( 'rewards' )->__ ( 'ID' ), 'width' => '50px', 'index' => 'entity_id', 'align' => 'right' ) );
		$this->addColumn ( 'name', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Name' ), 'index' => 'name' ) );
		$this->addColumn ( 'email', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Email' ), 'width' => '150px', 'index' => 'email' ) );
		$this->addColumn ( 'Telephone', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Telephone' ), 'width' => '100px', 'index' => 'billing_telephone' ) );
		$this->addColumn ( 'billing_postcode', array ('header' => Mage::helper ( 'rewards' )->__ ( 'ZIP/Post Code' ), 'width' => '120px', 'index' => 'billing_postcode' ) );
		$this->addColumn ( 'billing_country_id', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Country' ), 'width' => '100px', 'type' => 'country', 'index' => 'billing_country_id' ) );
		$this->addColumn ( 'billing_regione', array ('header' => Mage::helper ( 'rewards' )->__ ( 'State/Province' ), 'width' => '100px', 'index' => 'billing_regione' ) );
		
		if (! Mage::app ()->isSingleStoreMode ()) {
			$this->addColumn ( 'store_name', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Signed Up From' ), 'align' => 'center', 'index' => 'store_name', 'width' => '130px' ) );
		}
		
		return parent::_prepareColumns ();
	}
	
	public function getGridUrl() {
		return $this->getUrl ( '*/*/friendsGrid', array ('id' => Mage::registry ( 'transfer_data' )->getId () ) );
	}
	
	protected function _getSelectedCustomers() {
		if (Mage::getSingleton ( 'adminhtml/session' )->getTransferData ()) {
			$formData = Mage::getSingleton ( 'adminhtml/session' )->getTransferData ();
			$customerIds = $formData ['friend_id'];
		} elseif ($formData = Mage::registry ( 'transfer_data' )->getData ()) {
			$customerIds = isset ( $formData ['friend_id'] ) ? $formData ['friend_id'] : array ();
		} elseif ($this->getRequest ()->getPost ( 'friend_id' )) {
			$customerIds = $this->getRequest ()->getPost ( 'friend_id', null );
		} else {
			$customerIds = array ();
		}
		
		if (! is_array ( $customerIds ) && ( int ) $customerIds > 0) {
			$customerIds = array ($customerIds );
		}
		return $customerIds;
	}

}