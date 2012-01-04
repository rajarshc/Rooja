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
 * Product reports admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class TBT_Rewards_Manage_Reports_CustomersController extends Mage_Adminhtml_Controller_Action {
	
	/**
	 * init
	 *
	 * @return Mage_Adminhtml_Report_ProductController
	 */
	public function _initAction() {
		$act = $this->getRequest ()->getActionName ();
		if (! $act)
			$act = 'default';
		$this->loadLayout ()->_addBreadcrumb ( Mage::helper ( 'reports' )->__ ( 'Reports' ), Mage::helper ( 'reports' )->__ ( 'Reports' ) )->_addBreadcrumb ( Mage::helper ( 'reports' )->__ ( 'Products' ), Mage::helper ( 'reports' )->__ ( 'Products' ) );
		return $this;
	}
	
	/**
	 * Bestsellers
	 *
	 */
	public function topearnersAction() {
		$this->_title ( $this->__ ( 'Customer Rewards' ) )->_title ( $this->__ ( 'Reports' ) )->_title ( $this->__ ( 'Top Earners' ) );
		
		$this->_initAction ()->_setActiveMenu ( 'rewards/reports/topearners' )->_addBreadcrumb ( Mage::helper ( 'reports' )->__ ( 'Top Earners' ), Mage::helper ( 'reports' )->__ ( 'Top Earners' ) )->_addContent ( $this->getLayout ()->createBlock ( 'rewards/manage_reports_customers_topearners' ) )->renderLayout ();
	}
	
	/**
	 * Export products bestsellers report to CSV format
	 *
	 */
	public function exportTopEarnersCsvAction() {
		$fileName = 'rewards_top_earners.csv';
		$content = $this->getLayout ()->createBlock ( 'rewards/manage_reports_customers_topearners_grid' )->getCsv ();
		$this->_prepareDownloadResponse ( $fileName, $content );
	}
	
	/**
	 * Export products bestsellers report to XML format
	 *
	 */
	public function exportTopEarnersExcelAction() {
		$fileName = 'rewards_top_earners.xml';
		$content = $this->getLayout ()->createBlock ( 'rewards/manage_reports_customers_topearners_grid' )->getExcel ( $fileName );
		
		$this->_prepareDownloadResponse ( $fileName, $content );
	}
	
	/**
	 * Check is allowed for report
	 * TODO: modify this @nelkaake     
	 *
	 * @return bool
	 */
	protected function _isAllowed() {
		switch ($this->getRequest ()->getActionName ()) {
			case 'ordered' :
				return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'report/products/ordered' );
				break;
			case 'viewed' :
				return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'report/products/viewed' );
				break;
			case 'sold' :
				return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'report/products/sold' );
				break;
			case 'lowstock' :
				return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'report/products/lowstock' );
				break;
			default :
				return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'report/products' );
				break;
		}
	}

}
