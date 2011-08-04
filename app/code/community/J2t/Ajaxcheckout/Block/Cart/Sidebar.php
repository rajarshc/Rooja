<?php

class J2t_Ajaxcheckout_Block_Cart_Sidebar extends Mage_Checkout_Block_Cart_Sidebar
{
    const XML_PATH_CHECKOUT_SIDEBAR_COUNT   = 'checkout/sidebar/count';

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->addItemRender('default', 'checkout/cart_item_renderer', 'checkout/cart/sidebar/default.phtml');
    }

    
}
