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
if(Mage::helper('awrma')->isCustomSMTPInstalled()) {
    class AW_Rma_Model_Email_TemplateCommon extends AW_Customsmtp_Model_Email_Template {
    }
} else {
    class AW_Rma_Model_Email_TemplateCommon extends Mage_Core_Model_Email_Template {
    }
}

class AW_Rma_Model_Email_Template extends AW_Rma_Model_Email_TemplateCommon {
    # CONTENT REPLACEMENT CONSTRUCTION
    const AWRMA_CONTENT = '{{var content}}';
    # RECIPIENTS
    const AWRMA_RECIPIENT_ADMIN = 'admin';
    const AWRMA_RECIPIENT_CUSTOMER = 'customer';
    const AWRMA_RECIPIENT_CHATBOX = 'chatbox';

    /**
     * Load base template for recipient and replace text AWRMA_CONTENT with
     * $template in it
     * @param string $template
     * @param string $recipient
     * @return AW_Rma_Model_Email_Template
     */
    public function setAWRMATemplate($template, $recipient = self::AWRMA_RECIPIENT_CUSTOMER) {
        switch($recipient) {
            case self::AWRMA_RECIPIENT_ADMIN:
                $defaultTemplate = Mage::helper('awrma/config')->getAdminBaseTemplate($this->getDesignConfig()->getStore());
                break;
            case self::AWRMA_RECIPIENT_CUSTOMER:
                $defaultTemplate = Mage::helper('awrma/config')->getCustomerBaseTemplate($this->getDesignConfig()->getStore());
                break;
            case self::AWRMA_RECIPIENT_CHATBOX:
            default:
                $defaultTemplate = null;
                break;
        }
        if(!is_null($defaultTemplate)) {
            $this->loadDefault($defaultTemplate);
            $this->setTemplateText(str_replace(self::AWRMA_CONTENT, $template, $this->getTemplateText()));
        } else {
            $this->setTemplateText($template);
        }
        return $this;
    }

    /**
     * Sends email
     * @param $sender
     * @param $email
     * @param $name
     * @param $vars
     * @param $storeId
     * @return AW_Rma_Model_Email_Template
     */
    public function sendEmail($sender, $email, $name, $vars = array(), $storeId = null) {
        $this->setSentSuccess(false);

        if(!$email) return $this;

        if (($storeId === null) && $this->getDesignConfig()->getStore()) {
            $storeId = $this->getDesignConfig()->getStore();
        }

        if (!is_array($sender)) {
            $this->setSenderName(Mage::getStoreConfig('trans_email/ident_'.$sender.'/name', $storeId));
            $this->setSenderEmail(Mage::getStoreConfig('trans_email/ident_'.$sender.'/email', $storeId));
        } else {
            $this->setSenderName($sender['name']);
            $this->setSenderEmail($sender['email']);
        }

        if (!isset($vars['store'])) {
            $vars['store'] = Mage::app()->getStore($storeId);
        }

        if($this->getProcessedTemplate($vars)) {
            $this->setSentSuccess($this->send($email, $name, $vars));
        } else {
            $this->setSentSuccess(true);
        }
        return $this;
    }
}
