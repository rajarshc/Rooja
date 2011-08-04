<?php
/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    GPL
 */
 
class J2t_Ajaxcheckout_Block_Cartdelete extends Mage_Catalog_Block_Product_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('j2tajaxcheckout/ajaxcart.phtml');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getInviteButtonHtml()
    {
        return $this->getChildHtml('invite_button');
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function canShowUpsells()
    {
        return Mage::getStoreConfig('j2tajaxcheckout/default/j2t_ajax_cart_show_upsells', Mage::app()->getStore()->getId());
    }

    public function getImageSize(){
        return str_replace("x",",",Mage::getStoreConfig('j2tajaxcheckout/default/j2t_ajax_cart_upsells_image_size', Mage::app()->getStore()->getId()));
    }
    
    public function getNumberUpsells(){
        return Mage::getStoreConfig('j2tajaxcheckout/default/j2t_ajax_cart_upsells_nb', Mage::app()->getStore()->getId());
    }
    
    public function getNumberUpsellsPerLine(){
        return Mage::getStoreConfig('j2tajaxcheckout/default/j2t_ajax_cart_upsells_nb_per_line', Mage::app()->getStore()->getId());
    }

    
    public function getUpsells()
    {
        if ($product_id = $this->getProductInserted()){
            $product = Mage::getModel('catalog/product')->load($product_id);
            $collection = $product->getUpSellProductCollection()
                ->addAttributeToSort('position', 'asc')
                ->addStoreFilter();
            $this->_addProductAttributesAndPrices($collection);
            $collection->load();

            return $collection;
        }
        return false;
    }

    public function getProductInserted()
    {
        if(Mage::getSingleton('checkout/session')->getCartWasUpdated()){
            if ($product_id = Mage::getSingleton('checkout/session')->getCartInsertedItem()){
                return $product_id;
            }
        }
        Mage::getSingleton('checkout/session')->setCartInsertedItem('');
        return false;
    }

    public function j2tAddCartLink()
    {
        //if ($parentBlock = $this->getParentBlock()) {
            $count = $this->helper('checkout/cart')->getSummaryCount();

            if( $count == 1 ) {
                $text = $this->__('My Cart (%s item)', $count);
            } elseif( $count > 0 ) {
                $text = $this->__('My Cart (%s items)', $count);
            } else {
                $text = $this->__('My Cart');
            }

            //$parentBlock->addLink($text, 'checkout/cart', $text, true, array(), 50, null, 'class="cart_content" id="cart_content"');
        //}
        //return $this;
        return $text;
    }
}
