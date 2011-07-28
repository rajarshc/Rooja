<?php
class GoldenSpiralStudio_OneClickCartCheckout_Block_Checkout_Shipping_Methods_Available  extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }

    
public function getGroupedAllShippingRates()
    {
    	//dd($this->getShippingRatesCollection()->toArray());
        $rates = array();
        $collection  =Mage::getModel('sales/quote_address_rate')->getCollection();
       d($collection->getData());
        foreach ($collection as $rate) {
            if (!$rate->isDeleted() && $rate->getCarrierInstance()) {
                if (!isset($rates[$rate->getCarrier()])) {
                    $rates[$rate->getCarrier()] = array();
                }

                $rates[$rate->getCarrier()][] = $rate;
                $rates[$rate->getCarrier()][0]->carrier_sort_order = $rate->getCarrierInstance()->getSortOrder();
            }
        }
        uasort($rates, array($this, '_sortRates'));
        return $rates;
    }
    
  public function getShippingRates()
    {
        if (empty($this->_rates)) {
            
            $groups = $this->getGroupedAllShippingRates();
           return $this->_rates = $groups;
        }

        return $this->_rates;
    }
}