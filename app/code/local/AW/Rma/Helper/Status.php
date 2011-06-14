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
class AW_Rma_Helper_Status extends Mage_Core_Helper_Abstract {
    public static function getUneditedStatus() {
        return array(
            self::getPendingApprovalStatusId(),
            self::getApprovedStatusId(),
            self::getPackageSentStatusId(),
            self::getResolvedCanceledStatusId()
        );
    }

    public static function getPendingApprovalStatusId() {
        return 1;
    }

    public static function getApprovedStatusId() {
        return 2;
    }

    public static function getPackageSentStatusId() {
        return 3;
    }

    public static function getResolvedCanceledStatusId() {
        return 4;
    }

    public static function getResolvedStatuses() {
        $_statusCollection = Mage::getModel('awrma/entitystatus')->getCollection()
            ->setResolvedFilter();
        $_statusIds = array();
        foreach($_statusCollection as $_item)
            $_statusIds[] = $_item->getId();

        return $_statusIds;
    }
}
