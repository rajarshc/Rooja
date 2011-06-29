<?php
class Rbanh_Dev_Model_Catalog_Model_Price_Observer
{

	public function __construct() 
    {
    }
    
	public function apply_custom_rates($observer)
    {
        $event = $observer->getEvent();
        $product = $event->getProduct();
        
        $p = Mage::getModel('catalog/product')
			->setCurrentStore(1)
			->load($product->getId())
			->getData();

		$price = isset($p['rl_price']) ? $p['rl_price'] : 0;
		
		if (!empty($price))
		{
		    $roundme = round($price, 2);
		    $product->setFinalPrice($roundme);
		}
		
        return $this;        
    }
}