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
 * Manage Currency Grid
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Currency_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	protected $collection = null;
	protected $columnsAreSet = false;
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'currenciesGrid' );
		$this->setDefaultSort ( 'rewards_currency_id' );
		$this->setDefaultDir ( 'DESC' );
		$this->setSaveParametersInSession ( true );
	}
	
	protected function _getStore() {
		$storeId = ( int ) $this->getRequest ()->getParam ( 'store', 0 );
		return Mage::app ()->getStore ( $storeId );
	}
	
	protected function _prepareCollection() {
		if ($this->collection == null) {
			$this->collection = Mage::getModel ( 'rewards/currency' )->getCollection ();
		}
		
		$store = $this->_getStore ();
		if ($store->getId ()) {
			$this->collection->addStoreFilter ( $store );
		}
		
		$this->setCollection ( $this->collection );
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XML'));
		

		return parent::_prepareCollection ();
	}
	
	protected function _prepareColumns() {
		if ($this->columnsAreSet)
			return parent::_prepareColumns ();
		else
			$this->columnsAreSet = true;
		
		$this->addColumn ( 'rewards_currency_id', array ('header' => Mage::helper ( 'rewards' )->__ ( 'ID' ), 'align' => 'right', 'width' => '80px', 'index' => 'rewards_currency_id' ) );
		
		$this->addColumn ( 'active', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Is Active' ), 'align' => 'center', 'width' => '50px', 'type' => 'checkbox', 'index' => 'active', 'values' => array ('1' => true ) ) );
		
		$this->addColumn ( 'caption', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Name' ), 'align' => 'left', 'index' => 'caption' ) );
		
		/*        $this->addColumn('value', array(
            'header' => Mage::helper('rewards')->__('Value'),
            'align' => 'left',
            'width' => '80px',
            'type' => 'number',
            'index' => 'value',
        ));
*/
		$this->addColumn ( 'action', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Action' ), 'width' => '100', 'type' => 'action', 'getter' => 'getId', 'actions' => array (array ('caption' => Mage::helper ( 'rewards' )->__ ( 'Edit' ), 'url' => array ('base' => '*/*/edit' ), 'field' => 'id' ) ), 'filter' => false, 'sortable' => false, 'index' => 'stores', 'is_system' => true ) );
		
		return parent::_prepareColumns ();
	}
	
	protected function _prepareMassaction() {
		return $this;
	}
	
	public function getRowUrl($row) {
		return $this->getUrl ( '*/*/edit', array ('id' => $row->getId () ) );
	}

}