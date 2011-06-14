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
class AW_Rma_Block_Customer_New extends Mage_Core_Block_Template {
    /**
     * Customer orders collection
     * @var Mage_Sales_Model_Mysql4_Order_Collection
     */
    private $_customerOrders = null;
    /**
     * Is this block renders for guest or for registered customer
     * @var bool
     */
    private $_guestMode = TRUE;

    public function __construct() {
        parent::__construct();
        switch(Mage::helper('awrma')->getMagentoVersionCode()) {
            case AW_Rma_Helper_Data::MAGENTO_VERSION_CE_13x:
                $_template = 'aw_rma/customer/new13x.phtml';
                break;
            default:
                $_template='aw_rma/customer/new.phtml';
        }
        $this->setTemplate($_template);
        return $this;
    }

    private function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function getGuestMode() {
        return $this->_guestMode;
    }

    public function setGuestMode($val) {
        $this->_guestMode = (bool) $val;
        return $this;
    }

    /**
     * Return saved form data
     * @param boolean $jsonItems - if it set to TRUE function returns string
     * @return array or JSON string
     */
    public function getFormData($jsonItems = FALSE) {
        $_formData = $this->_getSession()->getAWRMAFormData(TRUE);
        if($_formData)
            return $jsonItems ? Zend_Json::encode(isset($_formData['orderitems'])? $_formData['orderitems'] : array()) : $_formData;
        else
            return FALSE;
    }

    /**
     * Returns order collection with some filters
     * @return Mage_Sales_Order_Collection
     */
    public function getCustomerOrders() {
        if(!is_null($this->_customerOrders))
            return $this->_customerOrders;
        
        if($this->getGuestMode()) {
            $this->_customerOrders = array($this->_getSession()->getData('awrma_guest_order'));
        } else {
            $this->_customerOrders = Mage::getResourceModel('sales/order_collection')
                ->addFieldToFilter('customer_id', $this->_getSession()->getCustomer()->getId())
                ->addFieldToFilter('state', array('in' => array('complete')))
                ->setOrder('created_at', 'desc');

            $this->_customerOrders->getSelect()
                ->where('updated_at > DATE_SUB(NOW(), INTERVAL ? DAY)', Mage::helper('awrma/config')->getDaysAfter());

            $this->_customerOrders->load();
        }

        return $this->_customerOrders;
    }

    public function getRequestTypes() {
        return Mage::getModel('awrma/entitytypes')
            ->getCollection()
            ->setStoreFilter()
            ->setActiveFilter()
            ->setDefaultSort()
            ->load();
    }
}
