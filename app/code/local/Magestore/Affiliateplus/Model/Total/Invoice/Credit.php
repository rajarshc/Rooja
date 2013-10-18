<?php

class Magestore_Affiliateplus_Model_Total_Invoice_Credit extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Invoice $invoice){
		$baseDiscount = 0;
        $discount = 0;
        
        $order = $invoice->getOrder();
        $baseOrderDiscount = $order->getBaseAffiliateCredit();
        $orderDiscount = $order->getAffiliateCredit();
        
        if ($invoice->getBaseGrandTotal() < 0.0001 || $baseOrderDiscount >= 0) {
            return $this;
        }
        $baseInvoicedDiscount = 0;
        $invoicedDiscount = 0;
        foreach ($order->getInvoiceCollection() as $_invoice) {
            $baseInvoicedDiscount += $_invoice->getBaseAffiliateCredit();
            $invoicedDiscount += $_invoice->getAffiliateCredit();
        }
        
        if ($invoice->isLast()) {
            $baseDiscount = $baseOrderDiscount - $baseInvoicedDiscount;
            $discount = $orderDiscount - $invoicedDiscount;
        } else {
            $baseOrderTotal = $order->getBaseGrandTotal() - $baseOrderDiscount;
            $baseDiscount = $baseOrderDiscount * $invoice->getBaseGrandTotal() / $baseOrderTotal;
            $discount = $orderDiscount * $invoice->getBaseGrandTotal() / $baseOrderTotal;
            if ($baseDiscount < $baseOrderDiscount) {
                $baseDiscount = $baseOrderDiscount;
                $discount = $orderDiscount;
            }
        }
        if ($baseDiscount) {
            $baseDiscount = Mage::app()->getStore()->roundPrice($baseDiscount);
            $discount = Mage::app()->getStore()->roundPrice($discount);
            
            $invoice->setBaseAffiliateCredit($baseDiscount);
            $invoice->setAffiliateCredit($discount);
            
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseDiscount);
			$invoice->setGrandTotal($invoice->getGrandTotal() + $discount);
        }
        return $this;
	}
}
