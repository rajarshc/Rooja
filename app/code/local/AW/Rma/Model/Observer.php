<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Rma
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Rma_Model_Observer {
    public static function removeSessionData() {
        Mage::getSingleton('customer/session')->getAWRMAFormData(TRUE);
        Mage::getSingleton('customer/session')->getAWRMACommentFormData(TRUE);
    }

    /**
     * Replace view order page template in customer account for adding link
     * Request RMA
     * @return null
     */
    public static function setOrderInfoTemplate() {
        if(!Mage::getSingleton('core/layout')->getBlock('sales.order.info'))
            return;
        switch(Mage::helper('awrma')->getMagentoVersionCode()) {
            case AW_Rma_Helper_Data::MAGENTO_VERSION_CE_13x:
                $_template = 'aw_rma/sales/order/info13x.phtml';
                break;
            default:
                $_template = 'aw_rma/sales/order/info.phtml';
        }
        Mage::getSingleton('core/layout')->getBlock('sales.order.info')->setTemplate($_template);
    }
}
