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
class AW_Rma_Block_Customer_Footerlinkadder extends Mage_Core_Block_Abstract {
    /**
     * Add link to the footer menu
     */
    public function addLink() {
        if(Mage::helper('awrma')->isEnabled()) {
            $parentBlock = $this->getParentBlock();
            if($parentBlock instanceof Mage_Page_Block_Template_Links) {
                if(is_null(Mage::getSingleton('customer/session')->getId()) && Mage::helper('awrma/config')->getAllowAnonymousAccess())
                    $parentBlock->addLink($this->__('Request RMA'), Mage::app()->getStore()->getUrl('awrma/guest_rma/index'), $this->__('Request RMA'));
                else
                    $parentBlock->addLink($this->__('Request RMA'), Mage::app()->getStore()->getUrl('awrma/customer_rma/new'), $this->__('Request RMA'));
            }
        }
    }
}
