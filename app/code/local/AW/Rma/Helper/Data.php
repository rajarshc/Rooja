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
class AW_Rma_Helper_Data extends Mage_Core_Helper_Abstract {
    /** Magento 1.3.* version code */
    const MAGENTO_VERSION_CE_13x = 'CE13x';
    /** Magento 1.4.* version code */
    const MAGENTO_VERSION_CE_1_4 = 'CE14';
    /** Magento 1.5.* version code */
    const MAGENTO_VERSION_CE_1_5 = 'CE15';
    /** Magento 1.8.* version code */
    const MAGENTO_VERSION_EE_1_8 = 'EE18';
    /** Magento 1.9.* version code */
    const MAGENTO_VERSION_EE_1_9 = 'EE19';
    /** Magento 1.10.* version code */
    const MAGENTO_VERSION_EE_1_10 = 'EE10';

    /**
     * Returns Magento version code
     * @return string
     */
    public static function getMagentoVersionCode() {
        if(preg_match('|1\.10.*|',Mage::getVersion())) return self::MAGENTO_VERSION_EE_1_10;
        if(preg_match('|1\.9.*|',Mage::getVersion())) return self::MAGENTO_VERSION_EE_1_9;
        if(preg_match('|1\.8.*|',Mage::getVersion())) return self::MAGENTO_VERSION_EE_1_8;
        if(preg_match('|1\.5.*|',Mage::getVersion())) return self::MAGENTO_VERSION_CE_1_5;
        if(preg_match('|1\.4.*|',Mage::getVersion())) return self::MAGENTO_VERSION_CE_1_4;

        return self::MAGENTO_VERSION_CE_13x;
    }

    /**
     * Check is extension enabled in advanced tab
     * @return bool
     */
    public static function isEnabled() {
        return !((bool) Mage::getStoreConfig('advanced/modules_disable_output/AW_Rma'));
    }

    public static function getTypeLabel($id) {
        $_type = Mage::getModel('awrma/entitytypes')->load($id);
        if($_type->getData() == array()) return null;
        else return $_type->getName();
    }

    /**
     * Return html view of order items
     * @param <type> $orderId
     * @param <type> $guestMode
     * @param <type> $data
     * @return array
     */
    public static function getItemsForOrderHtml($orderId, $guestMode, $data) {
        $result = array();
        $_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

        if(!($_order->getData() == array())) {
            if($guestMode) {
                if($_order->getCustomerId() || $_order->getCustomerEmail() != $data) return $result;
            } else {
                if($_order->getCustomerId() != $data) return $result;
            }

            $_orderItems = $_order->getItemsCollection();

            $_itemsRenderer = new Mage_Sales_Block_Order_Items();
            $_itemsRenderer
                ->setLayout(Mage::getSingleton('core/layout'));
            switch(self::getMagentoVersionCode()) {
                case self::MAGENTO_VERSION_CE_13x:
                    $_itemsRenderer
                        ->addItemRender('default', 'sales/order_item_renderer_default', 'aw_rma/sales/order/items/renderer/default13x.phtml')
                        ->addItemRender('grouped', 'sales/order_item_renderer_grouped', 'aw_rma/sales/order/items/renderer/default13x.phtml');
                    break;
                default:
                    $_itemsRenderer
                        ->addItemRender('default', 'sales/order_item_renderer_default', 'aw_rma/sales/order/items/renderer/default.phtml')
                        ->addItemRender('grouped', 'sales/order_item_renderer_grouped', 'aw_rma/sales/order/items/renderer/default.phtml');
            }

            foreach($_orderItems as $_item) {
                if($_item->getParentItem()) continue;
                $result[] = $_itemsRenderer->getItemHtml($_item);
            }
        }

        return $result;
    }

    /**
     * Return url for order
     * @param string $incrementId
     * @param bool $admin
     * @return string
     */
    public static function getOrderUrl($incrementId, $admin = TRUE) {
        $_order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        if($_order->getData() != array()) {
            if($admin)
                return Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view', array('order_id' => $_order->getId()));
            else
                return Mage::app()->getStore()->getUrl('sales/order/view', array('order_id' => $_order->getId()));
        } else {
            return null;
        }
    }

    /**
     * Return customer edit url
     * @param int $id
     * @return string
     */
    public static function getCustomerUrl($id) {
        $_customer = Mage::getModel('customer/customer')->load($id);
        if($_customer->getData() != array())
            return Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array('id' => $id));
        else
            return null;
    }

    /**
     * Generate external link for RMA request
     * @return string
     */
    public static function getExtLink() {
        $extLink = strtoupper(uniqid(dechex(rand())));
        return $extLink;
    }

    public static function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public static function isAllowedForOrder($order) {
        return (strtotime($order->getCreatedAt()) >= strtotime('-'.Mage::helper('awrma/config')->getDaysAfter().' day', time())) && $order->getState() == 'complete';
    }

    public static function getRegionName($regionId) {
        $region = Mage::getModel('directory/region')->load($regionId);
        if($region->getData() != array())
            return $region->getName();
        else
            return null;
    }

    public static function isCustomSMTPInstalled() {
        $_modules = (array) Mage::getConfig()->getNode('modules')->children();
        if(array_key_exists('AW_Customsmtp', $_modules)
            && 'true' == (string) $_modules['AW_Customsmtp']->active
            && !(bool) Mage::getStoreConfig('advanced/modules_disable_output/AW_Customsmtp')
            && @class_exists('AW_Customsmtp_Model_Email_Template'))
            return TRUE;
        return FALSE;
    }
}
