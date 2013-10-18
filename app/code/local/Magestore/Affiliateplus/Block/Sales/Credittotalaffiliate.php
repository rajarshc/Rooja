<?php
class Magestore_Affiliateplus_Block_Sales_Credittotalaffiliate extends Mage_Sales_Block_Order_Totals
{
	public function getAffiliateplusDiscount(){
		$order = $this->getOrder();
		return $order->getAffiliateplusDiscount();
	}
	
	public function getBaseAffiliateplusDiscount(){
		$order = $this->getOrder();
		return $order->getBaseAffiliateplusDiscount();
	}
	
	public function initTotals(){
		$amount = $this->getAffiliateplusDiscount();
		if(floatval($amount)){
			$total = new Varien_Object();
			$total->setCode('affiliateplus_discount');
			$total->setValue($amount);
			$total->setBaseValue($this->getBaseAffiliateplusDiscount());
			$total->setLabel('Affiliate Discount');
			$parent = $this->getParentBlock();
			$parent->addTotal($total,'subtotal');
		}
	}
	
	public function getOrder(){
		if(!$this->hasData('order')){
			$order = $this->getParentBlock()
				->getCreditmemo()
				->getOrder();
			$this->setData('order',$order);
		}
		return $this->getData('order');
	}
}