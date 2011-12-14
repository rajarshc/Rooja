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
 * Manage Transfer Edit Tab Grid Polls
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Transfer_Edit_Tab_Grid_Polls extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct() {
		parent::__construct ();
		$this->setUseAjax ( true );
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
		if ($column->getId () == 'assigned_polls') {
			$customerIds = $this->_getSelectedPolls ();
			if (empty ( $customerIds )) {
				$customerIds = 0;
			}
			if ($column->getFilter ()->getValue ()) {
				$this->getCollection ()->addFieldToFilter ( 'poll_id', array ('in' => $customerIds ) );
			} else {
				if ($customerIds) {
					$this->getCollection ()->addFieldToFilter ( 'poll_id', array ('nin' => $customerIds ) );
				}
			}
		} else {
			parent::_addColumnFilterToCollection ( $column );
		}
		return $this;
	}
	
	protected function _prepareLayout() {
		
		$this->setChild ( 'clear_selections_button', $this->getLayout ()->createBlock ( 'adminhtml/widget_button' )->setData ( array ('label' => Mage::helper ( 'adminhtml' )->__ ( 'Clear Selections' ), 'onclick' => 'clearGridSelections(\'poll_id\')' ) ) );
		return parent::_prepareLayout ();
	}
	
	public function getClearSelectionsButtonHtml() {
		return $this->getChildHtml ( 'clear_selections_button' );
	}
	
	public function getMainButtonsHtml() {
		return $this->getClearSelectionsButtonHtml () . parent::getMainButtonsHtml ();
	}
	
	protected function _prepareColumns() {
		//die (print_r($this->_getSelectedCustomers())+"|");
		$this->addColumn ( 'assigned_polls', array ('header_css_class' => 'a-center', 'header' => Mage::helper ( 'adminhtml' )->__ ( 'Origin' ), 'type' => 'radio', 'html_name' => 'poll_id', 'values' => $this->_getSelectedPolls (), 'align' => 'center', 'index' => 'poll_id', 'filter_index' => 'poll_id' ) );
		
		$this->addColumn ( 'poll_id', array ('header' => Mage::helper ( 'poll' )->__ ( 'ID' ), 'align' => 'right', 'width' => '50px', 'index' => 'poll_id' ) );
		
		$this->addColumn ( 'poll_title', array ('header' => Mage::helper ( 'poll' )->__ ( 'Poll Question' ), 'align' => 'left', 'index' => 'poll_title' ) );
		
		$this->addColumn ( 'votes_count', array ('header' => Mage::helper ( 'poll' )->__ ( 'Number of Responses' ), 'width' => '50px', 'type' => 'number', 'index' => 'votes_count' ) );
		
		$this->addColumn ( 'date_posted', array ('header' => Mage::helper ( 'poll' )->__ ( 'Date Posted' ), 'align' => 'left', 'width' => '120px', 'type' => 'date', 'index' => 'date_posted' ) );
		
		$this->addColumn ( 'date_closed', array ('header' => Mage::helper ( 'poll' )->__ ( 'Date Closed' ), 'align' => 'left', 'width' => '120px', 'type' => 'date', 'default' => '--', 'index' => 'date_closed' ) );
		
		if (! Mage::app ()->isSingleStoreMode ()) {
			$this->addColumn ( 'visible_in', array ('header' => Mage::helper ( 'review' )->__ ( 'Visible In' ), 'index' => 'stores', 'type' => 'store', 'store_view' => true, 'sortable' => false ) );
		}
		
		/*
          $this->addColumn('active', array(
          'header'    => Mage::helper('poll')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'active',
          'type'      => 'options',
          'options'   => array(
          1 => 'Active',
          0 => 'Inactive',
          ),
          ));
         */
		$this->addColumn ( 'closed', array ('header' => Mage::helper ( 'poll' )->__ ( 'Status' ), 'align' => 'left', 'width' => '80px', 'index' => 'closed', 'type' => 'options', 'options' => array (1 => Mage::helper ( 'poll' )->__ ( 'Closed' ), 0 => Mage::helper ( 'poll' )->__ ( 'Open' ) ) ) );
		
		$this->addColumn ( 'action', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Action' ), 'width' => '100', 'type' => 'action', 'getter' => 'getId', 'actions' => array (array ('caption' => Mage::helper ( 'rewards' )->__ ( 'View' ), 'url' => array ('base' => 'adminhtml/poll/edit/tab/answers_section' ), 'field' => 'id' ) ), 'filter' => false, 'sortable' => false, 'index' => 'stores', 'is_system' => true ) );
		
		return parent::_prepareColumns ();
	}
	
	public function getGridUrl() {
		return $this->getUrl ( '*/*/pollsGrid', array ('id' => $this->_getTransfer ()->getId () ) );
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