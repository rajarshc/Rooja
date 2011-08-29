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
 * Manage Transfer Edit Tab Customer Grid
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Transfer_Edit_Tab_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'rewards_transfer_edit_customer_grid' );
		$this->setUseAjax ( true );
		$this->setDefaultSort ( 'entity_id' );
		// if a customer id was passed in the request or a transfer is stored in the registry, filter the customer grid
		if (($this->_getTransfer () && $this->_getTransfer ()->getId ()) || $this->getRequest ()->getParam ( 'customer_id' )) {
			$this->setDefaultFilter ( array ('assigned_customer' => 1 ) );
		}
	}
	
	protected function _prepareCollection() {
		$collection = Mage::getResourceModel ( 'customer/customer_collection' )->addNameToSelect ()
		        ->addAttributeToSelect ( 'email' )
		        ->addAttributeToSelect ( 'created_at' )
		        ->joinAttribute ( 'billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left' )
		        ->joinAttribute ( 'billing_city', 'customer_address/city', 'default_billing', null, 'left' )
		        ->joinAttribute ( 'billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left' )
		        ->joinAttribute ( 'billing_regione', 'customer_address/region', 'default_billing', null, 'left' )
		        ->joinAttribute ( 'billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left' )
		        ->joinField ( 'store_name', 'core/store', 'name', 'store_id=store_id', null, 'left' );
		
		$this->_joinCustomerPointsIndex($collection);
		
		$this->setCollection ( $collection );
		if ($customer_id = $this->getRequest ()->getParam ( 'customer_id' )) {
			//$this->_addColumnFilterToCollection($this->getColumn('assigned_customer'));
		}
		return parent::_prepareCollection ();
	}
	

	/**
	 * If we should be using the customer points balance index table, this will join the index table to this grid collection
	 * TODO this is a copy from the class TBT_Rewards_Block_Manage_Customer_Points_Grid. We should be using a decorator design pattern here.
	 * @param unknown_type $collection
	 */
	protected function _joinCustomerPointsIndex($collection=null) {
	    if(!Mage::helper('rewards/customer_points_index')->useIndex()) {
	        // Shouldn't be using the customer points index.
	        return $this;
	    }
	    
	    $collection = $collection == null ? $this->getCollection() : $collection;
	
	    $points_index_table = Mage::getResourceModel('rewards/customer_indexer_points')->getIdxTable();
	    $collection->getSelect()->joinLeft(
	        array('points_index' => $points_index_table), 
	        'e.entity_id = points_index.customer_id');
		
		return $this;
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
		
		$this->setChild ( 'clear_selections_button', $this->getLayout ()->createBlock ( 'adminhtml/widget_button' )->setData ( array ('label' => Mage::helper ( 'adminhtml' )->__ ( 'Clear Selections' ), 'onclick' => 'clearGridSelections(\'customer_id\')' ) ) );
		return parent::_prepareLayout ();
	}
	
	public function getClearSelectionsButtonHtml() {
		return $this->getChildHtml ( 'clear_selections_button' );
	}
	
	public function getMainButtonsHtml() {
		return $this->getClearSelectionsButtonHtml () . parent::getMainButtonsHtml ();
	}
	
	protected function _addColumnFilterToCollection($column) {
		// Set custom filter for in customer flag
		if ($column->getId () == 'assigned_customer') {
			$customerIds = $this->_getSelectedCustomers ();
			if (empty ( $customerIds )) {
				$customerIds = 0;
			}
			//die(print_r($customerIds, true));
			if ($column->getFilter ()->getValue ()) {
				$this->getCollection ()->addFieldToFilter ( 'entity_id', array ('in' => $customerIds ) );
			} else {
				if ($customerIds) {
					$this->getCollection ()->addFieldToFilter ( 'entity_id', array ('nin' => $customerIds ) );
				}
			}
		} else {
			parent::_addColumnFilterToCollection ( $column );
		}
		return $this;
	}
	
	protected function _prepareColumns() {
		$this->addColumn ( 'assigned_customer', array ('header_css_class' => 'a-center', 'header' => Mage::helper ( 'adminhtml' )->__ ( 'Assigned' ), 'type' => 'radio', 'html_name' => 'customer_id', 'values' => $this->_getSelectedCustomers (), 'align' => 'center', 'index' => 'entity_id', 'filter_index' => "entity_id" ) );
		
		$this->addColumn ( 'entity_id', array ('header' => Mage::helper ( 'rewards' )->__ ( 'ID' ), 'width' => '50px', 'index' => 'entity_id', 'align' => 'right' ) );
		$this->addColumn ( 'name', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Name' ), 'index' => 'name' ) );
		$this->addColumn ( 'email', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Email' ), 'width' => '150px', 'index' => 'email' ) );
		$this->addColumn ( 'billing_country_id', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Country' ), 'width' => '100px', 'type' => 'country', 'index' => 'billing_country_id' ) );
		$this->addColumn ( 'billing_regione', array ('header' => Mage::helper ( 'rewards' )->__ ( 'State/Province' ), 'width' => '100px', 'index' => 'billing_regione' ) );
		
		if (! Mage::app ()->isSingleStoreMode ()) {
			$this->addColumn ( 'store_name', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Signed Up From' ), 'align' => 'center', 'index' => 'store_name', 'width' => '130px' ) );
		}
		
		$this->addColumn ( 'points', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Points' ), 'width' => '220px', 'index' => 'customer_id', 'renderer' => 'rewards/manage_grid_renderer_points', 'sortable' => false, 'filter' => false ) );
		//
		

		return parent::_prepareColumns ();
	}
	
	public function getGridUrl() {
		$params = array ('id' => Mage::registry ( 'transfer_data' )->getId () );
		if ($customer_id = $this->getRequest ()->getParam ( 'customer_id' )) {
			$params ['customer_id'] = $customer_id;
		}
		return $this->getUrl ( '*/*/customersGrid', $params );
	}
	
	protected function _getSelectedCustomers() {
		if (Mage::getSingleton ( 'adminhtml/session' )->getTransferData ()) {
			$formData = Mage::getSingleton ( 'adminhtml/session' )->getTransferData ();
			$customerIds = $formData ['customer_id'];
		} elseif ($formData = Mage::registry ( 'transfer_data' )->getData ()) {
			$customerIds = isset ( $formData ['customer_id'] ) ? $formData ['customer_id'] : array ();
		} elseif ($this->getRequest ()->getPost ( 'customer_id' )) {
			$customerIds = $this->getRequest ()->getPost ( 'customer_id', null );
		} elseif ($this->getRequest ()->getParam ( 'customer_id' )) {
			$customerIds = $this->getRequest ()->getParam ( 'customer_id', null );
		} else {
			$customerIds = array ();
		}
		
		if (! is_array ( $customerIds ) && ( int ) $customerIds > 0) {
			$customerIds = array ($customerIds );
		}
		//Mage::log(print_r($customerIds, true));
		return $customerIds;
	}

}