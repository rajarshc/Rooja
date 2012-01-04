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
class TBT_Rewards_Block_Manage_Customer_Edit_Tab_Points extends Mage_Adminhtml_Block_Widget_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	
	protected $collection = null;
	protected $columnsAreSet = false;
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'transfersGrid' );
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
		    $customer = Mage::getModel('rewards/customer')->getRewardsCustomer(Mage::registry('current_customer'));
			$this->collection = $customer->getTransfers ();
		}
		
//		var_dump(get_class(Mage::getModel('rewards/customer')->load(Mage::registry('current_customer'))->getTransfers())); die();
		
		$store = $this->_getStore ();
		if ($store->getId ()) {
			$this->collection->addStoreFilter ( $store );
		}
		
		//die('<pre>'.print_r($this->collection).'</pre>');
		$this->collection->selectFullCustomerName ( 'fullcustomername' );
		$this->collection->selectPointsCaption ( 'points' );
						
		$this->setCollection ( $this->collection );
		
		//        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
		//        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XML'));
		

		return parent::_prepareCollection ();
	}
	
	protected function _prepareLayout() {
		$url = $this->getUrl ( 'rewardsadmin/manage_transfer/new', array ('module' => 'adminhtml', 'controller' => 'customer', 'action' => 'edit', 'customer_id' => $this->getCustomer ()->getId () ) );
		
		$this->setChild ( 'new_transfer', $this->getLayout ()->createBlock ( 'adminhtml/widget_button' )->setData ( array ('label' => Mage::helper ( 'adminhtml' )->__ ( 'New Transfer' ), 'onclick' => "setLocation('{$url}')", 'class' => 'add' ) ) );
		
		return parent::_prepareLayout ();
	}
	
	public function getMainButtonsHtml() {
		$html = parent::getMainButtonsHtml ();
		$html .= $this->getChildHtml ( 'new_transfer' );
		return $html;
	}
	
	protected function _prepareColumns() {
		if ($this->columnsAreSet)
			return parent::_prepareColumns ();
		else
			$this->columnsAreSet = true;
		
		$this->addColumn ( 'transfer_id', array ('header' => Mage::helper ( 'rewards' )->__ ( 'ID' ), 'align' => 'right', 'width' => '36px', 'index' => 'transfer_id', 'filter_index' => 'reference_table.rewards_transfer_id' ) );
		
		$this->addColumn ( 'points', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Points' ), 'align' => 'left', 'width' => '70px', 'index' => 'points', 'filter_index' => 'CONCAT(main_table.quantity, \' \', currency_table.caption)' ) );
		
		/*
          $this->addColumn('customer_id', array(
          'header'    => Mage::helper('rewards')->__('Customer ID'),
          'align'     =>'left',
          'width'     => '100px',
          'index'     => 'customer_id',
          ));

         */
		
		$reasons = Mage::getSingleton ( 'rewards/transfer_reason' )->getOptionArray ();
		if (count ( $reasons ) > 1) {
			$this->addColumn ( 'reason', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Reason' ), 'align' => 'left', 'width' => '100px', 'index' => 'reason_id', 'type' => 'options', 'options' => $reasons ) );
		}
		
		$this->addColumn ( 'comments', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Comments/Notes' ), 'width' => '250px', 'index' => 'comments' ) );
		
		$statuses = Mage::getSingleton ( 'rewards/transfer_status' )->getOptionArray ();
		$this->addColumn ( 'status', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Status' ), 'align' => 'left', 'width' => '80px', 'index' => 'status', 'type' => 'options', 'options' => $statuses ) );
		
		//		$this->addColumn('salesrule_name', array(
		//			'header'    => Mage::helper('rewards')->__('Shopping Cart Rule Name'),
		//			'width'     => '150px',
		//			'index'     => 'salesrule_name',
		//		));
		//		
		//		$this->addColumn('catalogrule_name', array(
		//			'header'    => Mage::helper('rewards')->__('Catalog Rule Name'),
		//			'width'     => '150px',
		//			'index'     => 'catalogrule_name',
		//		));
		//		
		//		$this->addColumn('rule_id', array(
		//			'header'    => Mage::helper('rewards')->__('Points Rule ID'),
		//			'width'     => '80px',
		//			'index'     => 'rule_id',
		//		));
		//		
		$this->addColumn ( 'action', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Action' ), 'width' => '50px', 'type' => 'action', 'getter' => 'getId', 'actions' => array (array ('caption' => Mage::helper ( 'rewards' )->__ ( 'Edit' ), //						'url'       => array('base'=> 'rewardsadmin/manage_transfer/edit'),
'url' => array ('base' => 'rewardsadmin/manage_transfer/edit/' . 'module/adminhtml/controller/customer/action/edit/' . 'customer_id/' . $this->getCustomer ()->getId () ), 'field' => 'id' ) ), 'filter' => false, 'sortable' => false, 'index' => 'stores', 'is_system' => true ) );
		
		return parent::_prepareColumns ();
	}
	
	/**
	 * TODO: UPDATE THIS FIELD TO POINT TO A REAL CONTROLLER
	 */
	public function getGridUrl() {
		return $this->getUrl ( 'rewardsadmin/manage_customer_edit/transfersGrid', array ('customer_id' => $this->getCustomerId (), '_current' => true ) ); //array('id' => $this->_getTransfer()->getId()
	}
	
	/**
	 * Retrieve available customer
	 *
	 * @return TBT_Rewards_Model_Customer
	 */
	public function getCustomer() {
	    if ($this->hasCustomer ()) {
			return Mage::getModel('rewards/customer')->getRewardsCustomer($this->getData ( 'customer' ));
		}
		if (Mage::registry ( 'current_customer' )) {
			return Mage::getModel('rewards/customer')->getRewardsCustomer(Mage::registry ( 'current_customer' ));
		}
		if (Mage::registry ( 'customer' )) {
			return Mage::getModel('rewards/customer')->getRewardsCustomer(Mage::registry ( 'customer' ));
		}
		Mage::throwException ( Mage::helper ( 'customer' )->__ ( 'Can\'t get customer instance' ) );
	}
	
	/**
	 * Fetches the customer id currently open.
	 *
	 * @return int
	 */
	public function getCustomerId() {
		$c = $this->getCustomer ();
		if ($c->getId ()) {
			$cid = $c->getId ();
		} else {
			$cid = $this->getRequest ()->getParam ( 'customer_id' );
		}
		return $cid;
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