<?php
class Magestore_Affiliateplus_Block_Sales_Credit_Order extends Mage_Sales_Block_Order_Totals
{
	public function initTotals() {
        $parent = $this->getParentBlock();
        $order = $parent->getOrder();
		if($amount = floatval($order->getAffiliateCredit())){
			$total = new Varien_Object(array(
                'code'  => 'affiliateplus_credit',
                'value' => $amount,
                'base_value'    => $order->getBaseAffiliateCredit(),
                'label' => $this->__('Paid by Affiliate Credit'),
            ));
			$parent->addTotal($total, 'subtotal');
		}
	}
}
