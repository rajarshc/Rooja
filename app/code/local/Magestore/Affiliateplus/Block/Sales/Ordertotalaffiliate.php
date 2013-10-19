<?php
class Magestore_Affiliateplus_Block_Sales_Ordertotalaffiliate extends Mage_Sales_Block_Order_Totals
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
			$total->setLabel('Affiliate Discount' . $this->getAffiliateCouponLabel());
			$parent = $this->getParentBlock();
			$parent->addTotal($total,'subtotal');
		}
	}
    
    public function getAffiliateCouponLabel() {
        $order = $this->getOrder();
        if ($order->getAffiliateplusCoupon()) {
            return ' (' . $order->getAffiliateplusCoupon() . ')';
        } elseif ($order->getOrder()) {
            if ($order->getOrder()->getAffiliateplusCoupon()) {
                return ' (' . $order->getOrder()->getAffiliateplusCoupon() . ')';
            }
        }
        return '';
    }
	
	public function getOrder(){
		if(!$this->hasData('order')){
			$parent = $this->getParentBlock();
            if ($parent instanceof Mage_Adminhtml_Block_Sales_Order_Invoice_Totals) {
                $order = $parent->getInvoice();
            } elseif ($parent instanceof Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals) {
                $order = $parent->getCreditmemo();
            } else {
                $order = $this->getParentBlock()->getOrder();
            }
			$this->setData('order',$order);
		}
		return $this->getData('order');
	}
}