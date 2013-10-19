<?php

class Magestore_Affiliatepluscoupon_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('affiliateplus/transaction')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Coupon Transactions Manager'), Mage::helper('adminhtml')->__('Coupon Transaction Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_title($this->__('Affiliateplus'))->_title($this->__('Transactions from Coupon'));
		$this->_initAction()
			->renderLayout();
	}
	
	public function gridAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->getResponse()->setBody($this->getLayout()->createBlock('affiliatepluscoupon/adminhtml_transaction_grid')->toHtml());
    }
}