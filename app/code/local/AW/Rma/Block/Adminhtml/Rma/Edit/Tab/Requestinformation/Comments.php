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
class AW_Rma_Block_Adminhtml_Rma_Edit_Tab_Requestinformation_Comments extends Mage_Adminhtml_Block_Abstract {
    public function __construct() {
        if(!$this->getTemplate())
            $this->setTemplate('aw_rma/commentslist.phtml');
    }

    public function getComments() {
        if(!$this->getRmaRequest())
            return null;

        $_comments = Mage::getModel('awrma/entitycomments')->getCollection()
            ->setEntityFilter($this->getRmaRequest()->getId())
            ->setOrder('created_at', 'DESC')
            ->setOrder('id', 'DESC')
            ->load();

        return $_comments;
    }
}
