<?php

class Magestore_Affiliateplus_Model_Total_Creditmemo_Credit extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo){
		$baseDiscount = 0;
        $discount = 0;
        
        $order = $creditmemo->getOrder();
        $baseOrderDiscount = $order->getBaseAffiliateCredit();
        $orderDiscount = $order->getAffiliateCredit();
        
        if ($creditmemo->getBaseGrandTotal() < 0.0001 || $baseOrderDiscount >= 0) {
            return $this;
        }
        $baseInvoicedDiscount = 0;
        $invoicedDiscount = 0;
        foreach ($order->getCreditmemosCollection() as $_creditmemo) {
            $baseInvoicedDiscount += $_creditmemo->getBaseAffiliateCredit();
            $invoicedDiscount += $_creditmemo->getAffiliateCredit();
        }
        $baseOrderTotal = $order->getBaseGrandTotal() - $baseOrderDiscount;
        $baseDiscount = $baseOrderDiscount * $creditmemo->getBaseGrandTotal() / $baseOrderTotal;
        $discount = $orderDiscount * $creditmemo->getBaseGrandTotal() / $baseOrderTotal;
        if ($baseDiscount < $baseOrderDiscount) {
            $baseDiscount = $baseOrderDiscount;
            $discount = $orderDiscount;
        }
        if ($baseDiscount) {
            $baseDiscount = Mage::app()->getStore()->roundPrice($baseDiscount);
            $discount = Mage::app()->getStore()->roundPrice($discount);
            
            $creditmemo->setBaseAffiliateCredit($baseDiscount);
            $creditmemo->setAffiliateCredit($discount);
            
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseDiscount);
			$creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $discount);
            
            $creditmemo->setAllowZeroGrandTotal(true);
        }
        return $this;
	}
}
