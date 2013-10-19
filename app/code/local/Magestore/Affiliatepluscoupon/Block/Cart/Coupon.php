<?php

class Magestore_Affiliatepluscoupon_Block_Cart_Coupon extends Mage_Checkout_Block_Cart_Coupon
{
	public function getCouponCode(){
		if (Mage::getStoreConfig('affiliateplus/coupon/enable')
			&& Mage::getSingleton('checkout/session')->getData('affiliate_coupon_code')){
			return Mage::getSingleton('checkout/session')->getData('affiliate_coupon_code');
		}
		return $this->getQuote()->getCouponCode();
	}
}