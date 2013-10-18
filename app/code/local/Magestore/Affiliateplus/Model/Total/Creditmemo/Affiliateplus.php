<?php

class Magestore_Affiliateplus_Model_Total_Creditmemo_Affiliateplus extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo){
		$baseDiscount = 0;
        $discount = 0;
        foreach ($creditmemo->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy()) {
                continue;
            }
            $orderItem = $item->getOrderItem();
            $orderItemDiscount = (float)$orderItem->getAffiliateplusAmount();
            $baseOrderItemDiscount = (float)$orderItem->getBaseAffiliateplusAmount();
            $orderItemQty = $orderItem->getQtyOrdered();
            if ($orderItemDiscount && $orderItemQty) {
                $discount -= $orderItemDiscount * $item->getQty() / $orderItemQty;
                $baseDiscount -= $baseOrderItemDiscount * $item->getQty() / $orderItemQty;
            }
        }
		if (!floatval($baseDiscount)){
            $order = $creditmemo->getOrder();
            $baseDiscount = $order->getBaseAffiliateplusDiscount();
            $discount = $order->getAffiliateplusDiscount();
        }
		if (floatval($baseDiscount)){
            $baseDiscount = Mage::app()->getStore()->roundPrice($baseDiscount);
            $discount = Mage::app()->getStore()->roundPrice($discount);
            
			$creditmemo->setBaseAffiliateplusDiscount($baseDiscount);
			$creditmemo->setAffiliateplusDiscount($discount);
			
			$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseDiscount);
			$creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $discount);
		}
		return $this;
	}
}