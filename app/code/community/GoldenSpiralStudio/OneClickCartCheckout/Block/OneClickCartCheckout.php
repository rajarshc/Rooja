<?php
class GoldenSpiralStudio_OneClickCartCheckout_Block_OneClickCartCheckout extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getOneClickCartCheckout()     
     { 
        if (!$this->hasData('oneclickcartcheckout')) {
            $this->setData('oneclickcartcheckout', Mage::registry('oneclickcartcheckout'));
        }
        return $this->getData('oneclickcartcheckout');
        
    }
}