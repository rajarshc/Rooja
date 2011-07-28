<?php
class GoldenSpiralStudio_OneClickCartCheckout_Block_Checkout extends Mage_Checkout_Block_Onepage_Abstract
{
	public function _prepareLayout()
    {
		$ret =  parent::_prepareLayout();
		$configStyle = Mage::getStoreConfig('checkout/oneclickcartcheckout/checkout_style');
		if ($configStyle){
			$ret->getLayout()->getBlock("head")->addItem("skin_css","css/oneclickcartcheckout/styles/$configStyle.css");
		}
		return $ret;
    }
    
    
    public function canCheckout(){
    
    	 if($this->getQuote()->getItemsSummaryQty() == 0)    {
            return false;
        }
        return true;
    	
    }
    
    
    public function __construct()
    {
    	$this->_shippingMethod();
    
    	
    }
 	
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
    
    public function getA(){
     
    }
   
    
    
    public function _shippingMethod(){
    
    	$onepage = $this->getOnepage();
		$quote = $this->getOnepage()->getQuote();
		
		$method = $quote->getShippingAddress()->getShippingMethod();
		$shipping = $quote->getShippingAddress();
		
		/**
		 * IF METHOD IS NOT SET EARLY   set FIRST FROM AVAILABLE
		 */
		if (!$method){
			$items = Mage::getModel('sales/quote_address_rate')->getCollection(); 
			foreach($items as $item)
			{
				$method = $item->getCode();
				$shipping->setData("country_id",$this->helper("oneclickcartcheckout")->getCountry());
				break;
			}
			
		}
		
		
		$shipping->setShippingMethod($method);

		$shipping->setCollectShippingRates(true)->save();  
	
		
    }
}

