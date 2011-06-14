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
class AW_Rma_Block_Customer_Printlabel extends Mage_Core_Block_Template {
    private $_rmaRequest = null;
    private $_countryCollection = null;

    public function __construct() {
        parent::__construct();
        switch(Mage::helper('awrma')->getMagentoVersionCode()) {
            case AW_Rma_Helper_Data::MAGENTO_VERSION_CE_13x:
                $_template = 'aw_rma/customer/printlabel13x.phtml';
                break;
            default:
                $_template='aw_rma/customer/printlabel.phtml';
        }
        $this->setTemplate($_template);
        return $this;
    }

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function getRmaRequest() {
        if(!$this->_rmaRequest) {
            $this->_rmaRequest = Mage::registry('awrma-request');
        }

        return $this->_rmaRequest;
    }

    public function getFormData() {
        if(!$this->getRmaRequest()->getPrintLabel()) {
            $_formData = array(
                'firstname' => $this->getShippingData('firstname'),
                'lastname' => $this->getShippingData('lastname'),
                'company' => $this->getShippingData('company'),
                'telephone' => $this->getShippingData('telephone'),
                'fax' => $this->getShippingData('fax'),
                'streetaddress' => explode("\n", $this->getShippingData('street')),
                'city' => $this->getShippingData('city'),
                'stateprovince_id' => $this->getShippingData('region_id'),
                'stateprovince' => $this->getShippingData('region'),
                'postcode' => $this->getShippingData('postcode'),
                'country_id' => $this->getShippingData('country_id')
            );
            return new Varien_Object($_formData);
        }
        return new Varien_Object($this->getRmaRequest()->getPrintLabel());
    }

    public function getFormPostUrl() {
        if($this->getGuestMode()) {
            return $this->getUrl('awrma/guest_rma/printform', array('id' => $this->getRmaRequest()->getExternalLink()));
        } else {
            return $this->getUrl('awrma/customer_rma/printform', array('id' => $this->getRmaRequest()->getId()));
        }
    }

    public function getCountryHtmlSelect($type, $countryId = null) {
        if(is_null($countryId))
            $countryId = Mage::getStoreConfig('general/country/default');

        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName('printlabel[country_id]')
            ->setId('awrma_country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());

        return $select->getHtml();
    }

    public function getCountryOptions()
    {
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }

    public function getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }

    public function getShippingData($key) {
        if($this->getRmaRequest() && $this->getRmaRequest()->getOrder() &&  $this->getRmaRequest()->getOrder()->getShippingAddress())
            return $this->getRmaRequest()->getOrder()->getShippingAddress()->getData($key);
        return null;
    }
}
