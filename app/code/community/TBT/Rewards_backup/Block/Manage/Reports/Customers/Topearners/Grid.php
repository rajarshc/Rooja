<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml products in carts report grid block
 * 
 * @nelkaake Added on Sunday August 15, 2010:  
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class TBT_Rewards_Block_Manage_Reports_Customers_Topearners_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'gridRewardsCustomers' );
	}
	
	protected function _prepareCollection() {
		$collection = Mage::getResourceModel ( 'rewards/reports_customer_collection' )->addNameToSelect ()->addPointsBalance ();
		/* @var $collection Mage_Reports_Model_Mysql4_Product_Collection */
		
		$this->setCollection ( $collection );
		return parent::_prepareCollection ();
	}
	
	protected function _prepareColumns() {
		$this->addColumn ( 'entity_id', array ('header' => Mage::helper ( 'reports' )->__ ( 'ID' ), 'width' => '50px', 'align' => 'right', 'index' => 'entity_id' ) );
		
		$this->addColumn ( 'name', array ('header' => Mage::helper ( 'reports' )->__ ( 'Customer Name' ), 'index' => 'name' ) );
		
		$this->addColumn ( 'email', array ('header' => Mage::helper ( 'reports' )->__ ( 'Email' ), 'width' => '80px', 'index' => 'email' ) );
		
		$this->addColumn ( 'points_balance', array ('header' => Mage::helper ( 'reports' )->__ ( 'Points Balance' ), 'width' => '120px', 'align' => 'right', 'index' => 'points_balance' ) );
		
		$this->setFilterVisibility ( false );
		
		$this->addExportType ( '*/*/exportTopEarnersCsv', Mage::helper ( 'reports' )->__ ( 'CSV' ) );
		$this->addExportType ( '*/*/exportTopEarnersExcel', Mage::helper ( 'reports' )->__ ( 'Excel' ) );
		
		return parent::_prepareColumns ();
	}
	
	public function getRowUrl($row) {
		return $this->getUrl ( 'admin/customer/edit', array ('id' => $row->getEntityId () ) );
	}

}

