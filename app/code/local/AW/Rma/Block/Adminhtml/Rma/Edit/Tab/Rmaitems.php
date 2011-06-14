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
class AW_Rma_Block_Adminhtml_Rma_Edit_Tab_Rmaitems extends Mage_Adminhtml_Block_Abstract {
    private $_rmaRequest = null;
    private $_order = null;

    public function __construct() {
        if(!$this->getTemplate())
            $this->setTemplate('aw_rma/rmaitems.phtml');
    }

    public function getRmaRequest() {
        if(!$this->_rmaRequest)
            $this->_rmaRequest = Mage::registry('awrmaformdatarma');
        return $this->_rmaRequest;
    }

    public function getOrder() {
        if(!$this->_order)
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->getRmaRequest()->getOrderId());
        return $this->_order;
    }
}
