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
class AW_Rma_Block_Customer_Printform extends Mage_Core_Block_Template {
    private $_formData = null;
    private $_rmaRequest = null;

    /**
     * Retreives RMA request from Mage Registry
     * @return AW_Model_Entity
     */
    public function getRmaRequest() {
        if(!$this->_rmaRequest) {
            $this->_rmaRequest = Mage::registry('awrma-request');
        }
        return $this->_rmaRequest;
    }

    public function setFormData($formData) {
        $this->_formData = $formData;
        return $this;
    }

    public function getFormData() {
        if(!$this->_formData) {
            $this->_formData = Mage::registry('awrma-formdata');
        }
        if(is_array($this->_formData))
            $this->_formData = new Varien_Object($this->_formData);
        return $this->_formData;
    }

    /**
     * Retreives region name
     * @return string
     */
    public function getRegionName() {
        if($this->getFormData() && $this->getFormData()->getStateprovinceId()) {
            return Mage::helper('awrma')->getRegionName($this->getFormData()->getStateprovinceId());
        }
        return null;
    }

    /**
     * Retreives country name by code
     * @return string
     */
    public function getCountryName() {
        if($this->getFormData() && $this->getFormData()->getCountryId()) {
            $country = Mage::getModel('directory/country')->load($this->getFormData()->getCountryId());
            if($country->getData() != array())
                return $country->getName();
        }
        return null;
    }
}
