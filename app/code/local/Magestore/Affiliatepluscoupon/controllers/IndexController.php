<?php

class Magestore_Affiliatepluscoupon_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){ return; }
		if (Mage::helper('affiliatepluscoupon')->couponIsDisable())
			return $this->_redirect('affiliateplus/index/index');
		
		$account = Mage::helper('affiliateplus/account')->getAccount();
		$accountId = $account->getId();
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
		$account->setCouponCode($coupon->getCouponCode());
		Mage::register('account_model',$account);
		
		if (Mage::helper('affiliatepluscoupon')->isMultiProgram()){
			$programs = Mage::getResourceModel('affiliateplusprogram/account_collection')
    			->addFieldToFilter('account_id',$accountId);
			$pCouponCodes = array();
			foreach ($programs as $accProgram){
				$program = Mage::getModel('affiliateplusprogram/program')
					->setStoreId(Mage::app()->getStore()->getId())
					->load($accProgram->getProgramId());
				if (!$program->getUseCoupon() || !floatval($program->getDiscount())) continue;
				$coupon->setId(null)->loadByProgram($accProgram->getProgramId());
				if (!$coupon->getId()){
					try {
						$coupon->setCouponCode($helper->generateNewCoupon($program->getCouponPattern()))
							->setAccountName($account->getName())
							->setProgramName($program->getName())
							->save();
					} catch (Exception $e){}
				}
				if ($coupon->getCouponCode()) $pCouponCodes[$program->getId()] = $coupon->getCouponCode();
			}
			Mage::register('program_coupon_codes',$pCouponCodes);
		}
		
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Coupon Code'));
		$this->renderLayout();
	}
}