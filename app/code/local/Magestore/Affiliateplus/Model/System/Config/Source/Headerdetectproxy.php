<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_AffiliateplusPayPerClick
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * System_Config_Source_Headerdetectproxy Model
 * 
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @author      Magestore Developer
 */
class Magestore_Affiliateplus_Model_System_Config_Source_Headerdetectproxy {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 1, 'label' => Mage::helper('affiliateplus')->__('HTTP_VIA')),
            array('value' => 2, 'label' => Mage::helper('affiliateplus')->__('HTTP_X_FORWARDED_FOR')),
            array('value' => 3, 'label' => Mage::helper('affiliateplus')->__('HTTP_FORWARDED_FOR')),
            array('value' => 4, 'label' => Mage::helper('affiliateplus')->__('HTTP_X_FORWARDED')),
            array('value' => 5, 'label' => Mage::helper('affiliateplus')->__('HTTP_FORWARDED')),
            array('value' => 6, 'label' => Mage::helper('affiliateplus')->__('HTTP_CLIENT_IP')),
            array('value' => 7, 'label' => Mage::helper('affiliateplus')->__('HTTP_FORWARDED_FOR_IP')),
            array('value' => 8, 'label' => Mage::helper('affiliateplus')->__('HTTP_PROXY_CONNECTION')),
            array('value' => 9, 'label' => Mage::helper('affiliateplus')->__('VIA')),
            array('value' => 10, 'label' => Mage::helper('affiliateplus')->__('X_FORWARDED_FOR')),
            array('value' => 11, 'label' => Mage::helper('affiliateplus')->__('FORWARDED_FOR')),
            array('value' => 12, 'label' => Mage::helper('affiliateplus')->__('X_FORWARDED')),
            array('value' => 13, 'label' => Mage::helper('affiliateplus')->__('FORWARDED')),
            array('value' => 14, 'label' => Mage::helper('affiliateplus')->__('CLIENT_IP')),
            array('value' => 15, 'label' => Mage::helper('affiliateplus')->__('FORWARDED_FOR_IP')),
        );
    }

    public function getOptionList() {
        $result = array();
        foreach ($this->toOptionArray() as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
    }

}