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
 * Manage Transfer Edit Tab Grid Reviews
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Transfer_Edit_Tab_Grid_Reviews extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'review_grid' );
		$this->setUseAjax ( true );
		$this->setDefaultSort ( 'created_at' );
		$this->setDefaultDir ( 'DESC' );
	}
	
	protected function _prepareCollection() {
		//TODO: add full name logic
		$collection = Mage::getResourceModel ( 'review/review_collection' );
		//->addAttributeToSelect('*');
		//            ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
		//            ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
		//            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
		//            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
		//            ->addExpressionAttributeToSelect('billing_name',
		//                'CONCAT({{billing_firstname}}, " ", {{billing_lastname}})',
		//                array('billing_firstname', 'billing_lastname'))
		//            ->addExpressionAttributeToSelect('shipping_name',
		//                'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
		//                array('shipping_firstname', 'shipping_lastname'));
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
		if ($column->getId () == 'assigned_review') {
			$customerIds = $this->_getSelectedReviews ();
			if (empty ( $customerIds )) {
				$customerIds = 0;
			}
			if ($column->getFilter ()->getValue ()) {
				$this->getCollection ()->addFieldToFilter ( 'review_id', array ('in' => $customerIds ) );
			} else {
				if ($customerIds) {
					$this->getCollection ()->addFieldToFilter ( 'review_id', array ('nin' => $customerIds ) );
				}
			}
		} else {
			parent::_addColumnFilterToCollection ( $column );
		}
		return $this;
	}
	
	protected function _prepareLayout() {
		
		$this->setChild ( 'clear_selections_button', $this->getLayout ()->createBlock ( 'adminhtml/widget_button' )->setData ( array ('label' => Mage::helper ( 'adminhtml' )->__ ( 'Clear Selections' ), 'onclick' => 'clearGridSelections(\'review_id\')' ) ) );
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
		$this->addColumn ( 'assigned_review', array ('header_css_class' => 'a-center', 'header' => Mage::helper ( 'adminhtml' )->__ ( 'Origin' ), 'type' => 'radio', 'html_name' => 'review_id', 'values' => $this->_getSelectedReviews (), 'align' => 'center', 'index' => 'review_id', 'filter_index' => 'review_id' ) );
		
		$this->addColumn ( 'review_id', array ('header' => Mage::helper ( 'review' )->__ ( 'Review #' ), 'width' => '80px', 'type' => 'text', 'index' => 'review_id' ) );
		
		if (! Mage::app ()->isSingleStoreMode ()) {
			$this->addColumn ( 'review_store_id', array ('header' => Mage::helper ( 'review' )->__ ( 'Reviewed in (store)' ), 'index' => 'store_id', 'type' => 'store', 'store_view' => true, 'display_deleted' => true ) );
		}
		
		$this->addColumn ( 'created_at', array ('header' => Mage::helper ( 'review' )->__ ( 'Reviewed On' ), 'index' => 'created_at', 'type' => 'datetime', 'width' => '100px' ) );
		
		$this->addColumn ( 'title', array ('header' => Mage::helper ( 'review' )->__ ( 'Review Title' ), 'index' => 'title' ) );
		
		$this->addColumn ( 'review_status_id', array ('header' => Mage::helper ( 'review' )->__ ( 'Status' ), 'index' => 'status_id', //'type'  => 'options',
'type' => 'int', 'width' => '70px' ) )//'options' => Mage::getSingleton('review/review')->getStatusCollection(),
;
		
		$this->addColumn ( 'action', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Action' ), 'width' => '100', 'type' => 'action', 'getter' => 'getId', 'actions' => array (array ('caption' => Mage::helper ( 'rewards' )->__ ( 'View' ), 'url' => array ('base' => 'adminhtml/catalog_product_review/edit' ), 'field' => 'id' ) ), 'filter' => false, 'sortable' => false, 'index' => 'stores', 'is_system' => true ) );
		
		return parent::_prepareColumns ();
	}
	
	public function getGridUrl() {
		return $this->getUrl ( '*/*/reviewsGrid', array ('id' => $this->_getTransfer ()->getId () ) );
	}
	
	protected function _getSelectedReviews() {
	    
		if ($this->getReview()) {
			$reviewIds = array ($this->getReview()->getId());
			
		}elseif ($this->getReviewIds()) {
			$reviewIds = $this->getReviewIds();
	    
		} elseif (Mage::getSingleton ( 'adminhtml/session' )->getTransferData ()) {
			$formData = Mage::getSingleton ( 'adminhtml/session' )->getTransferData ();
			$reviewIds = isset ( $formData ['review_id'] ) ? $formData ['review_id'] : array ();
			
		} elseif (Mage::registry ( 'transfer_data' )->getData ()) {
			$formData = Mage::registry ( 'transfer_data' )->getData ();
			$reviewIds = isset ( $formData ['review_id'] ) ? $formData ['review_id'] : array ();
		} elseif ($this->getRequest ()->getPost ( 'review_id' )) {
			$reviewIds = $this->getRequest ()->getPost ( 'review_id', null );
		} else {
			$reviewIds = array ();
		}
		if (! is_array ( $reviewIds ) && ( int ) $reviewIds > 0) {
			$reviewIds = array ($reviewIds );
		}
		return $reviewIds;
	}

}