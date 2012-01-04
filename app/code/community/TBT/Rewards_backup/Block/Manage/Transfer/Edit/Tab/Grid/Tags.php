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
 * Manage Transfer Edit Tab Grid Tags
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Transfer_Edit_Tab_Grid_Tags extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'reference_tags_grid' );
		$this->setUseAjax ( true );
		$this->setDefaultSort ( 'created_at' );
		$this->setDefaultDir ( 'DESC' );
	}
	
	/**
	 * Retirve currently edited product model
	 *
	 * @return Mage_Catalog_Model_Product
	 */
	protected function _getTransfer() {
		return Mage::registry ( 'transfer_data' );
	}
	
	protected function _addColumnFilterToCollection($column) {
		// Set custom filter for in product flag
		if ($column->getId () == 'assigned_tags') {
			$customerIds = $this->_getSelectedTags ();
			if (empty ( $customerIds )) {
				$customerIds = 0;
			}
			if ($column->getFilter ()->getValue ()) {
				$this->getCollection ()->addFieldToFilter ( 'tag_id', array ('in' => $customerIds ) );
			} else {
				if ($customerIds) {
					$this->getCollection ()->addFieldToFilter ( 'tag_id', array ('nin' => $customerIds ) );
				}
			}
		} else {
			parent::_addColumnFilterToCollection ( $column );
		}
		return $this;
	}
	
	protected function _prepareLayout() {
		
		$this->setChild ( 'clear_selections_button', $this->getLayout ()->createBlock ( 'adminhtml/widget_button' )->setData ( array ('label' => Mage::helper ( 'adminhtml' )->__ ( 'Clear Selections' ), 'onclick' => 'clearGridSelections(\'tag_id\')' ) ) );
		return parent::_prepareLayout ();
	}
	
	public function getClearSelectionsButtonHtml() {
		return $this->getChildHtml ( 'clear_selections_button' );
	}
	
	public function getMainButtonsHtml() {
		return $this->getClearSelectionsButtonHtml () . parent::getMainButtonsHtml ();
	}
	
	protected function _prepareCollection() {
		$collection = Mage::getResourceModel ( 'tag/tag_collection' )->//            ->addStoreFilter(Mage::app()->getStore()->getId())
addStoresVisibility ();
		$this->setCollection ( $collection );
		return parent::_prepareCollection ();
	}
	
	protected function _prepareColumns() { //die (print_r($this->_getSelectedCustomers())+"|");
		$this->addColumn ( 'assigned_tags', array ('header_css_class' => 'a-center', 'header' => Mage::helper ( 'adminhtml' )->__ ( 'Origin' ), 'type' => 'radio', 'html_name' => 'tag_id', 'values' => $this->_getSelectedTags (), 'align' => 'center', 'index' => 'tag_id', 'filter_index' => 'tag_id' ) );
		
		$this->addColumn ( 'tag_id', array ('header' => Mage::helper ( 'tag' )->__ ( 'ID' ), 'align' => 'right', 'width' => '50px', 'index' => 'tag_id' ) );
		
		$this->addColumn ( 'name', array ('header' => Mage::helper ( 'tag' )->__ ( 'Tag' ), 'index' => 'name' ) );
		$this->addColumn ( 'total_used', array ('header' => Mage::helper ( 'tag' )->__ ( '# of Uses' ), 'width' => '140px', 'align' => 'center', 'index' => 'total_used', 'type' => 'number' ) );
		$this->addColumn ( 'status', array ('header' => Mage::helper ( 'tag' )->__ ( 'Status' ), 'width' => '90px', 'index' => 'status', 'type' => 'options', 'options' => array (Mage_Tag_Model_Tag::STATUS_DISABLED => Mage::helper ( 'tag' )->__ ( 'Disabled' ), Mage_Tag_Model_Tag::STATUS_PENDING => Mage::helper ( 'tag' )->__ ( 'Pending' ), Mage_Tag_Model_Tag::STATUS_APPROVED => Mage::helper ( 'tag' )->__ ( 'Approved' ) ) ) );
		
		$this->addColumn ( 'action', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Action' ), 'width' => '100', 'type' => 'action', 'getter' => 'getId', 'actions' => array (array ('caption' => Mage::helper ( 'rewards' )->__ ( 'View' ), 'url' => array ('base' => 'adminhtml/tag/edit/ret/all' ), 'field' => 'tag_id' ) ), 'filter' => false, 'sortable' => false, 'index' => 'stores', 'is_system' => true ) );
		
		return parent::_prepareColumns ();
	}
	
	public function getGridUrl() {
		return $this->getUrl ( '*/*/tagsGrid', array ('id' => $this->_getTransfer ()->getId () ) );
	}
	
	protected function _getSelectedTags() {
		if (Mage::getSingleton ( 'adminhtml/session' )->getTransferData ()) {
			$formData = Mage::getSingleton ( 'adminhtml/session' )->getTransferData ();
			$orderIds = isset ( $formData ['tag_id'] ) ? $formData ['tag_id'] : array ();
		} elseif (Mage::registry ( 'transfer_data' )->getData ()) {
			$formData = Mage::registry ( 'transfer_data' )->getData ();
			$orderIds = isset ( $formData ['tag_id'] ) ? $formData ['tag_id'] : array ();
		} elseif ($this->getRequest ()->getPost ( 'tag_id' )) {
			$orderIds = $this->getRequest ()->getPost ( 'tag_id', null );
		} else {
			$orderIds = array ();
		}
		if (! is_array ( $orderIds ) && ( int ) $orderIds > 0) {
			$orderIds = array ($orderIds );
		}
		return $orderIds;
	}
	
	protected function _getSelectedPolls() {
		if (Mage::getSingleton ( 'adminhtml/session' )->getTransferData ()) {
			$formData = Mage::getSingleton ( 'adminhtml/session' )->getTransferData ();
			$orderIds = isset ( $formData ['poll_id'] ) ? $formData ['poll_id'] : array ();
		} elseif (Mage::registry ( 'transfer_data' )->getData ()) {
			$formData = Mage::registry ( 'transfer_data' )->getData ();
			$orderIds = isset ( $formData ['poll_id'] ) ? $formData ['poll_id'] : array ();
		} elseif ($this->getRequest ()->getPost ( 'poll_id' )) {
			$orderIds = $this->getRequest ()->getPost ( 'poll_id', null );
		} else {
			$orderIds = array ();
		}
		if (! is_array ( $orderIds ) && ( int ) $orderIds > 0) {
			$orderIds = array ($orderIds );
		}
		return $orderIds;
	}

}