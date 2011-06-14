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
class AW_Rma_Helper_Config extends Mage_Core_Helper_Abstract {
    # GENERAL TAB
        # Days after completion
        const DAYSAFTER = 'awrma/general/daysafterordercompletion';
        # Allow anonymous access
        const G_ALLOWANONYMOUS = 'awrma/general/allowanonymous';
        # Allow Print Label option
        const ALLOWPRINTLABEL = 'awrma/general/printlabel';
         # Path to Allow Per Order RMA option
        const ALLOWPERORDERRMA = 'awrma/general/perorderrma';
        # Require Confirm Sending
        const REQUIRECONFIRMSENDING = 'awrma/general/requireconfirmsend';
        # Confirm sending text
        const G_CONFIRMSENDING = 'awrma/general/confirmsendingtext';
        # Path to Forbidden file extensions
        const FORBIDDENFILEEXTENSIONS = 'awrma/general/rejectedext';
        # Max attachments size
        const G_MAXSIZE = 'awrma/general/maxsize';

    # CONTACTS TAB
        # Enable email notifications
        const C_ENABLEEMAILNOTIFICATIONS = 'awrma/contacts/enableemails';
        # RMA department display name
        const RMADEPARTMENTNAME = 'awrma/contacts/displayname';
        # RMA department email address
        const C_RMADEPEMAIL = 'awrma/contacts/depemail';
        # RMA department address
        const C_RMADEPADDRESS = 'awrma/contacts/depaddress';

    # TEMPLATES TAB
        # Sender
        const T_EMAILSENDER = 'awrma/templates/sender';
        # Customer Base Template
        const T_CUSTOMERBASETEMPLATE = 'awrma/templates/customer_base_template';
        # Admin Base Template
        const T_ADMINBASETEMPLATE = 'awrma/templates/admin_base_template';

    # GENERAL TAB
    public static function getDaysAfter($store = null) {
        return intval(Mage::getStoreConfig(self::DAYSAFTER, $store));
    }

    public static function getAllowAnonymousAccess($store = null) {
        return (bool) Mage::getStoreConfig(self::G_ALLOWANONYMOUS, $store);
    }

    public static function getAllowPrintLabel($store = null) {
        return (bool)(Mage::getStoreConfig(self::ALLOWPRINTLABEL, $store));
    }

    public static function getAllowPerOrderRMA($store = null) {
        return intval(Mage::getStoreConfig(self::ALLOWPERORDERRMA, $store));
    }

    public static function getRequireConfirmSending($store = null) {
        return (bool) (Mage::getStoreConfig(self::REQUIRECONFIRMSENDING, $store));
    }

    public static function getConfirmSendingText($store = null) {
        return Mage::getStoreConfig(self::G_CONFIRMSENDING, $store);
    }

    public static function getForbiddenExtensions($store = null) {
        $_fe = Mage::getStoreConfig(self::FORBIDDENFILEEXTENSIONS, $store);
        if(!is_null($_fe))
            $_fe = explode(',', $_fe);
        return $_fe;
    }

    public static function getMaxAttachmentsSize($store = null) {
        return Mage::getStoreConfig(self::G_MAXSIZE, $store)*1024;
    }

    public static function getMaxAttachmentsSizeKb($store = null) {
        return Mage::getStoreConfig(self::G_MAXSIZE, $store);
    }

    # CONTACTS TAB
    public static function getEnableEmailNotifications($store = null) {
        return Mage::getStoreConfig(self::C_ENABLEEMAILNOTIFICATIONS, $store);
    }

    public static function getDepartmentDisplayName($store = null) {
        return Mage::getStoreConfig(self::RMADEPARTMENTNAME, $store);
    }
    
    public static function getDepartmentEmail($store = null) {
        return Mage::getStoreConfig(self::C_RMADEPEMAIL, $store);
    }

    public function getDepartmentAddress($store = null) {
        return Mage::getStoreConfig(self::C_RMADEPADDRESS, $store);
    }

    # TEMPLATES TAB
    public static function getEmailSender($store = null) {
        return Mage::getStoreConfig(self::T_EMAILSENDER, $store);
    }

    public static function getCustomerBaseTemplate($store = null) {
        return Mage::getStoreConfig(self::T_CUSTOMERBASETEMPLATE, $store);
    }

    public static function getAdminBaseTemplate($store = null) {
        return Mage::getStoreConfig(self::T_ADMINBASETEMPLATE, $store);
    }
}
