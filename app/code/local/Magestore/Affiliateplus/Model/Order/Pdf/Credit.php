<?php

class Magestore_Affiliateplus_Model_Order_Pdf_Credit extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    public function getTotalsForDisplay(){
		$discount = $this->getAmount();
		$fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
		if(floatval($discount)){
			$discount = $this->getOrder()->formatPriceTxt($discount);
			if ($this->getAmountPrefix()){
				$discount = $this->getAmountPrefix().$discount;
			}
            $label = Mage::helper('affiliateplus')->__('Paid by Affiliate Credit');
			$totals = array(
				array(
					'label' => $label,
					'amount' => $discount,
					'font_size' => $fontSize,
				)
			);	
			return $totals;
		}
	}
	
    public function getAmount(){
        if ($this->getSource()->getAffiliateCredit()) {
            return $this->getSource()->getAffiliateCredit();
        }
        return $this->getOrder()->getAffiliateCredit();
    }	
}
