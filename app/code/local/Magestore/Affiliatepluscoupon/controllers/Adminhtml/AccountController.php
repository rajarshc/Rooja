<?php

class Magestore_Affiliatepluscoupon_Adminhtml_AccountController extends Mage_Adminhtml_Controller_Action
{
	public function couponsAction(){
		if(!Mage::helper('magenotification')->checkLicenseKey('Affiliatepluscoupon')){
			return $this->getResponse()->setBody(Mage::helper('magenotification')->getInvalidKeyNotice()); 
		}
		$accountId = $this->getRequest()->getParam('id');
		$account = Mage::getModel('affiliateplus/account')->load($accountId);
		$coupon = Mage::getModel('affiliatepluscoupon/coupon')->setCurrentAccountId($accountId);
		$helper = Mage::helper('affiliatepluscoupon');
		
		$coupon->loadByProgram();
		if (!$coupon->getId()){
			try {
				$coupon->setCouponCode($helper->generateNewCoupon())
					->setAccountName($account->getName())
					->setProgramName('Affiliate Program')
					->save();
			} catch (Exception $e){}
		}
		
		if (Mage::helper('affiliatepluscoupon')->isMultiProgram()){
			$programs = Mage::getResourceModel('affiliateplusprogram/account_collection')
    			->addFieldToFilter('account_id',$accountId);
			foreach ($programs as $accProgram){
				$coupon->setId(null)->loadByProgram($accProgram->getProgramId());
				if ($coupon->getId()) continue;
				$program = Mage::getModel('affiliateplusprogram/program')->load($accProgram->getProgramId());
				if (!$program->getUseCoupon()) continue;
				try {
					$coupon->setCouponCode($helper->generateNewCoupon($program->getCouponPattern()))
						->setAccountName($account->getName())
						->setProgramName($program->getName())
						->save();
				} catch (Exception $e){}
			}
		}
		
		$this->loadLayout();
		$this->getLayout()->getBlock('account.tabs.coupon')
			->setCoupons($this->getRequest()->getPost('ocoupon',null));
		$this->renderLayout();
	}
	
	public function couponsGridAction(){
		if(!Mage::helper('magenotification')->checkLicenseKey('Affiliatepluscoupon')){
			return $this->getResponse()->setBody(Mage::helper('magenotification')->getInvalidKeyNotice());
		}
		$this->loadLayout();
		$this->getLayout()->getBlock('account.tabs.coupon')
			->setCoupons($this->getRequest()->getPost('ocoupon',null));
		$this->renderLayout();
	}
}