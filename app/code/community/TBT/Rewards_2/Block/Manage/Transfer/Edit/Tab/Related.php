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
 * Manage Transfer Edit Tab Related
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Transfer_Edit_Tab_Related extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'transfer_related' );
		$this->setDefaultSort ( 'id' );
	
		//$this->setUseAjax(true);
	}
	
	protected function _addColumnFilterToCollection($column) {
		// Set custom filter for in category flag
		if ($column->getId () == 'related_product') {
			$productIds = $this->_getSelectedProducts ();
			if (empty ( $productIds )) {
				$productIds = 0;
			}
			if ($column->getFilter ()->getValue ()) {
				$this->getCollection ()->addFieldToFilter ( 'entity_id', array ('in' => $productIds ) );
			} elseif (! empty ( $productIds )) {
				$this->getCollection ()->addFieldToFilter ( 'entity_id', array ('nin' => $productIds ) );
			}
		} else {
			parent::_addColumnFilterToCollection ( $column );
		}
		return $this;
	}
	
	protected function _prepareCollection() {
		$collection = Mage::getModel ( 'catalog/product' )->getCollection ()->addAttributeToSelect ( 'name' )->addAttributeToSelect ( 'sku' )->addAttributeToSelect ( 'price' )->addStoreFilter ( $this->getRequest ()->getParam ( 'store' ) )->joinField ( 'position', 'catalog/category_product', 'position', 'product_id=entity_id', 'category_id=' . ( int ) $this->getRequest ()->getParam ( 'id', 0 ), 'left' );
		$this->setCollection ( $collection );
		
		return parent::_prepareCollection ();
	}
	
	protected function _prepareColumns() {
		$this->addColumn ( 'related_product', array ('header_css_class' => 'a-center', 'type' => 'checkbox', 'name' => 'related_product', 'values' => $this->_getSelectedProducts (), 'align' => 'center', 'index' => 'entity_id' ) );
		$this->addColumn ( 'id', array ('header' => Mage::helper ( 'catalog' )->__ ( 'ID' ), 'sortable' => true, 'width' => '60px', 'index' => 'entity_id' ) );
		$this->addColumn ( 'name', array ('header' => Mage::helper ( 'catalog' )->__ ( 'Name' ), 'index' => 'name' ) );
		$this->addColumn ( 'sku', array ('header' => Mage::helper ( 'catalog' )->__ ( 'SKU' ), 'width' => '120px', 'index' => 'sku' ) );
		$this->addColumn ( 'price', array ('header' => Mage::helper ( 'catalog' )->__ ( 'Price' ), 'type' => 'currency', 'currency_code' => ( string ) Mage::getStoreConfig ( Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE ), 'index' => 'price' ) );
		$this->addColumn ( 'position', array ('header' => Mage::helper ( 'catalog' )->__ ( 'Position' ), 'name' => 'position', 'type' => 'number', 'validate_class' => 'validate-number', 'index' => 'position', 'width' => '60px', 'editable' => true ) );
		
		return parent::_prepareColumns ();
	}
	
	public function getGridUrl() {
		return $this->getUrl ( '*/*/grid', array ('_current' => true ) );
	}
	
	protected function _getProduct() {
		return Mage::registry ( 'blog_data' );
	}
	
	protected function _getSelectedProducts() {
		$products = $this->getRequest ()->getPost ( 'related_product' );
		if (is_null ( $products )) {
			$collection = Mage::getModel ( 'blog/related' )->getCollection ()->addPostFilter ( Mage::registry ( 'blog_data' )->getId () );
			
			foreach ( $collection as $product ) {
				$products [] = $product->getProductId ();
			}
		}
		return $products;
	}

}