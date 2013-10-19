<?php

class Magestore_AffiliateplusReferFriend_Block_Product_Refer extends Mage_Core_Block_Template
{
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('affiliateplusreferfriend/product/refer.phtml');
        $this->setData('generate_javascript', true);
    }
    
    public function getGenerateJavascript() {
        if ($this->getData('generate_javascript')) {
            $this->setData('generate_javascript', false);
            return true;
        }
        return false;
    }
    
    public function getJsonEmail() {
        $result = array(
            'yahoo' => $this->getUrl('affiliateplus/refer/yahoo'),
            'gmail' => $this->getUrl('affiliateplus/refer/gmail'),
                //'hotmail'	=> $this->getUrl('affiliateplus/refer/hotmail'),
        );
        return Zend_Json::encode($result);
    }
    
    public function getAffiliateUrl($product) {
        $productUrl = $product->getProductUrl();
        return Mage::helper('affiliateplus/url')->addAccToUrl($productUrl);
    }
}
