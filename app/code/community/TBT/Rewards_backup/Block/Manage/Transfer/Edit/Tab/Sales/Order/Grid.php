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
 * Manage Transfer Edit Tab Sales Order Grid
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Transfer_Edit_Tab_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'sales_order_grid' );
		$this->setUseAjax ( true );
		$this->setDefaultSort ( 'created_at' );
		$this->setDefaultDir ( 'DESC' );
	}
	
	protected function _prepareCollection() {
		//TODO: add full name logic
		$collection = Mage::getResourceModel ( 'sales/order_collection' )->addAttributeToSelect ( '*' )->joinAttribute ( 'billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left' )->joinAttribute ( 'billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left' )->joinAttribute ( 'shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left' )->joinAttribute ( 'shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left' )->addExpressionAttributeToSelect ( 'billing_name', 'CONCAT({{billing_firstname}}, " ", {{billing_lastname}})', array ('billing_firstname', 'billing_lastname' ) )->addExpressionAttributeToSelect ( 'shipping_name', 'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})', array ('shipping_firstname', 'shipping_lastname' ) );
		$this->setCollection ( $collection );
		return parent::_prepareCollection ();
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
		if ($column->getId () == 'assigned_order') {
			$customerIds = $this->_getSelectedOrders ();
			if (empty ( $customerIds )) {
				$customerIds = 0;
			}
			if ($column->getFilter ()->getValue ()) {
				$this->getCollection ()->addFieldToFilter ( 'order_id', array ('in' => $customerIds ) );
			} else {
				if ($customerIds) {
					$this->getCollection ()->addFieldToFilter ( 'order_id', array ('nin' => $customerIds ) );
				}
			}
		} else {
			parent::_addColumnFilterToCollection ( $column );
		}
		return $this;
	}
	
	protected function _prepareLayout() {
		
		$this->setChild ( 'clear_selections_button', $this->getLayout ()->createBlock ( 'adminhtml/widget_button' )->setData ( array ('label' => Mage::helper ( 'adminhtml' )->__ ( 'Clear Selections' ), 'onclick' => 'clearGridSelections(\'order_id\')' ) ) );
		return parent::_prepareLayout ();
	}
	
	public function getClearSelectionsButtonHtml() {
		return $this->getChildHtml ( 'clear_selections_button' );
	}
	
	public function getMainButtonsHtml() {
		return $this->getClearSelectionsButtonHtml () . parent::getMainButtonsHtml ();
	}
	
	protected function _prepareColumns() {
		$this->addColumn ( 'assigned_order', array ('header_css_class' => 'a-center', 'header' => Mage::helper ( 'adminhtml' )->__ ( 'Origin' ), 'type' => 'radio', 'html_name' => 'order_id', 'values' => $this->_getSelectedOrders (), 'align' => 'center', 'index' => 'entity_id' ) );
		
		$this->addColumn ( 'real_order_id', array ('header' => Mage::helper ( 'sales' )->__ ( 'Order #' ), 'width' => '80px', 'type' => 'text', 'index' => 'increment_id' ) );
		
		if (! Mage::app ()->isSingleStoreMode ()) {
			$this->addColumn ( 'store_id', array ('header' => Mage::helper ( 'sales' )->__ ( 'Purchased from (store)' ), 'index' => 'store_id', 'type' => 'store', 'store_view' => true, 'display_deleted' => true ) );
		}
		
		$this->addColumn ( 'created_at', array ('header' => Mage::helper ( 'sales' )->__ ( 'Purchased On' ), 'index' => 'created_at', 'type' => 'datetime', 'width' => '100px' ) );
		
		/* $this->addColumn('billing_firstname', array(
          'header' => Mage::helper('sales')->__('Bill to First name'),
          'index' => 'billing_firstname',
          ));

          $this->addColumn('billing_lastname', array(
          'header' => Mage::helper('sales')->__('Bill to Last name'),
          'index' => 'billing_lastname',
          )); */
		$this->addColumn ( 'billing_name', array ('header' => Mage::helper ( 'sales' )->__ ( 'Bill to Name' ), 'index' => 'billing_name' ) );
		
		/* $this->addColumn('shipping_firstname', array(
          'header' => Mage::helper('sales')->__('Ship to First name'),
          'index' => 'shipping_firstname',
          ));

          $this->addColumn('shipping_lastname', array(
          'header' => Mage::helper('sales')->__('Ship to Last name'),
          'index' => 'shipping_lastname',
          )); */
		$this->addColumn ( 'shipping_name', array ('header' => Mage::helper ( 'sales' )->__ ( 'Ship to Name' ), 'index' => 'shipping_name' ) );
		
		$this->addColumn ( 'base_grand_total', array ('header' => Mage::helper ( 'sales' )->__ ( 'G.T. (Base)' ), 'index' => 'base_grand_total', 'type' => 'currency', 'currency' => 'store_currency_code' ) );
		
		$this->addColumn ( 'grand_total', array ('header' => Mage::helper ( 'sales' )->__ ( 'G.T. (Purchased)' ), 'index' => 'grand_total', 'type' => 'currency', 'currency' => 'order_currency_code' ) );
		
		$this->addColumn ( 'status_id', array ('header' => Mage::helper ( 'sales' )->__ ( 'Status' ), 'index' => 'status', 'type' => 'options', 'width' => '70px', 'options' => Mage::getSingleton ( 'sales/order_config' )->getStatuses () ) );
		/*
          if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
          $this->addColumn('action',
          array(
          'header'    => Mage::helper('sales')->__('Action'),
          'width'     => '50px',
          'type'      => 'action',
          'getter'     => 'getId',
          'actions'   => array(
          array(
          'caption' => Mage::helper('sales')->__('View'),
          'url'     => array('base'=>'admin/sales_order/view', 'target'=>'_blank'),
          'field'   => 'order_id'
          )
          ),
          'filter'    => false,
          'sortable'  => false,
          'index'     => 'stores',
          'is_system' => true,
          ));
          }
         */
		//$this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));
		

		return parent::_prepareColumns ();
	}
	
	public function getGridUrl() {
		return $this->getUrl ( '*/*/ordersGrid', array ('id' => Mage::registry ( 'transfer_data' )->getId () ) );
	}
	
	protected function _getSelectedOrders() {
		if (Mage::getSingleton ( 'adminhtml/session' )->getTransferData ()) {
			$formData = Mage::getSingleton ( 'adminhtml/session' )->getTransferData ();
			$orderIds = isset ( $formData ['order_id'] ) ? $formData ['order_id'] : array ();
		} elseif (Mage::registry ( 'transfer_data' )->getData ()) {
			$formData = Mage::registry ( 'transfer_data' )->getData ();
			$orderIds = isset ( $formData ['order_id'] ) ? $formData ['order_id'] : array ();
		} elseif ($this->getRequest ()->getPost ( 'order_id' )) {
			$orderIds = $this->getRequest ()->getPost ( 'order_id', null );
		} else {
			$orderIds = array ();
		}
		if (! is_array ( $orderIds ) && ( int ) $orderIds > 0) {
			$orderIds = array ($orderIds );
		}
		return $orderIds;
	}

}