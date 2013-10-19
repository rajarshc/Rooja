<?php

class Magestore_AffiliateplusReferFriend_Block_Product_List
    extends Mage_Catalog_Block_Product_List
{
    public function isEnableShareFriend()
    {
        if ($this->hasData('is_enable_share_friend')) {
            return $this->getData('is_enable_share_friend');
        }
        if (Mage::helper('affiliateplus/account')->accountNotLogin()) {
            $this->setData('is_enable_share_friend', false);
        } else {
            $this->setData('is_enable_share_friend',
                Mage::helper('affiliateplus/config')->getReferConfig('refer_enable_product_list')
            );
        }
        return $this->getData('is_enable_share_friend');
    }
    
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
    {
        $html = parent::getPriceHtml($product, $displayMinimalPrice, $idSuffix);
        if ($this->isEnableShareFriend()) {
            // Add share friend for product list page
            $block = Mage::getBlockSingleton('affiliateplusreferfriend/product_refer');
            $block->setProduct($product);
            $html = $block->toHtml() . $html;
        }
        return $html;
    }
}
