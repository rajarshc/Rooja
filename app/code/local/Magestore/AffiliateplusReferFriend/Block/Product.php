<?php

class Magestore_AffiliateplusReferFriend_Block_Product extends Mage_Core_Block_Template
{
    public function isEnableShareFriend()
    {
        if (Mage::helper('affiliateplus/account')->accountNotLogin()) {
            return false;
        }
        return Mage::helper('affiliateplus/config')->getReferConfig('refer_enable_product_detail');
    }
    
    public function getProduct()
    {
        return Mage::registry('product');
    }
    
    public function getAffiliateUrl($product)
    {
        $productUrl = $product->getProductUrl();
        return Mage::helper('affiliateplus/url')->addAccToUrl($productUrl);
    }
    
    public function getJsonEmail() {
        $result = array(
            'yahoo' => $this->getUrl('*/*/yahoo'),
            'gmail' => $this->getUrl('*/*/gmail'),
                //'hotmail'	=> $this->getUrl('*/*/hotmail'),
        );
        return Zend_Json::encode($result);
    }
    
    public function getShareIconsHtml($product) {
        $block = Mage::getBlockSingleton('affiliateplusreferfriend/product_refer');
        $block->setProduct($product);
        return $block->toHtml();
    }
}
