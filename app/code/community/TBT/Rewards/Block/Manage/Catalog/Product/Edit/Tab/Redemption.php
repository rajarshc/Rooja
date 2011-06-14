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
 * Manage Transfer Edit Tab Grid Transfers
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Catalog_Product_Edit_Tab_Redemption extends Mage_Adminhtml_Block_Widget_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	
	protected $collection = null;
	protected $columnsAreSet = false;
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'rRulesGrid' );
		$this->setDefaultSort ( 'creation_ts' );
		$this->setDefaultDir ( 'DESC' );
		$this->setUseAjax ( true );
		$this->setSaveParametersInSession ( true );
	}
	
	protected function _getStore() {
		$storeId = ( int ) $this->getRequest ()->getParam ( 'store', 0 );
		return Mage::app ()->getStore ( $storeId );
	}
	
	protected function _prepareCollection() {
		if ($this->collection == null) {
			$this->collection = Mage::getModel ( 'rewards/catalogrule_rule' )->getCollection ();
		
		//	    	echo $this->getProduct()->getId();
		//	$this->collection = Mage::registry('current_customer')->getTransfers();
		//$model = Mage::getModel('rewards/catalog_product')->wrap($this->getProduct());
		//echo " ".$model->getId();
		//$this->collection = $model->getDistriRules();
		}
		//echo $collection->getSelect()->__toString();
		

		$store = $this->_getStore ();
		if ($store->getId ()) {
			$this->collection->addStoreFilter ( $store );
		}
		
		//die('<pre>'.print_r($this->collection).'</pre>');
		//        $this->collection->selectFullCustomerName('fullcustomername');
		//        $this->collection->selectPointsCaption('points');
		//$this->collection->addRules();
		$this->collection->filterByProduct ( $this->getProductId () );
		$this->collection->getRedemRules ();
		
		$this->setCollection ( $this->collection );
		
		//        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
		//        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XML'));
		

		return parent::_prepareCollection ();
	}
	
	protected function _prepareLayout() {
		$url = $this->getUrl ( 'rewardsadmin/manage_promo_catalog/new', array ('type' => 2 ) );
		
		$this->setChild ( 'new_rRule', $this->getLayout ()->createBlock ( 'adminhtml/widget_button' )->setData ( array ('label' => Mage::helper ( 'adminhtml' )->__ ( 'New Spending Rule' ), 'onclick' => "setLocation('{$url}')", 'class' => 'add' ) ) );
		
		return parent::_prepareLayout ();
	}
	
	public function getMainButtonsHtml() {
		$html = parent::getMainButtonsHtml ();
		$html .= $this->getChildHtml ( 'new_rRule' );
		return $html;
	}
	
	protected function _prepareColumns() {
		if ($this->columnsAreSet) {
			return parent::_prepareColumns ();
		} else {
			$this->columnsAreSet = true;
		}
		
		$this->addColumn ( 'rule_id', array ('header' => Mage::helper ( 'rewards' )->__ ( 'ID' ), 'align' => 'right', 'width' => '36px', 'index' => 'rule_id' ) )//'filter_index'     => 'reference_table.rewards_transfer_id'
;
		
		$this->addColumn ( 'name', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Rule Name' ), 'width' => '60px', 'index' => 'name' ) );
		
		$this->addColumn ( 'caption', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Comments/Notes' ), 'width' => '190px', 'index' => 'description' ) );
		
		$this->addColumn ( 'points', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Points Amount' ), 'align' => 'left', 'width' => '70px', 'index' => 'points_amount' ) )// 'filter_index'	=> 'CONCAT(main_table.quantity, \' \', currency_table.caption)'
;
		
		$this->addColumn ( 'action', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Action' ), 'width' => '50px', 'type' => 'action', 'getter' => 'getId', 'actions' => array (array ('caption' => Mage::helper ( 'rewards' )->__ ( 'Edit' ), //						'url'       => array('base'=> 'rewardsadmin/manage_transfer/edit'),
'url' => array ('base' => 'rewardsadmin/manage_promo_catalog/' . 'edit/type/2' ), 'field' => 'id' ) ), 'filter' => false, 'sortable' => false, 'index' => 'stores', 'is_system' => true ) );
		
		return parent::_prepareColumns ();
	}
	
	/**
	 * TODO: UPDATE THIS FIELD TO POINT TO A REAL CONTROLLER
	 */
	public function getGridUrl() {
		return $this->getUrl ( 'rewardsadmin/manage_catalog_product_edit/rRulesGrid', array ('product_id' => $this->getProductId (), '_current' => true ) ); //array('id' => $this->_getTransfer()->getId()
	}
	
	/**
	 * Retrive product object from object if not from registry
	 *
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct() {
		//    	if(!($_product instanceof TBT_Rewards_Model_Catalog_Product)) {
		//   			$_product = Mage::getModel('rewards/catalog_product')->load($_product->getId());
		//    	}
		

		if (! ($this->getData ( 'product' ) instanceof Mage_Catalog_Model_Product)) {
			$this->setData ( 'product', Mage::registry ( 'product' ) );
		}
		//        die(Mage::registry('current_product'));
		//    	die(print_r($this->getData(),true));
		

		if (Mage::registry ( 'product' )) {
			return Mage::registry ( 'product' );
		} else if (Mage::registry ( 'current_product' )) {
			return Mage::registry ( 'current_product' );
		}
		
		return $this->getData ( 'product' );
	}
	
	/**
	 * Fetches the product ID currently open.
	 *
	 * @return int
	 */
	public function getProductId() {
		$p = $this->getProduct ();
		if ($p->getId ()) {
			$pid = $p->getId ();
		} else {
			$pid = $this->getRequest ()->getParam ( 'product_id' );
		}
		return $pid;
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