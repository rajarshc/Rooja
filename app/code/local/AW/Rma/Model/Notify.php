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
class AW_Rma_Model_Notify extends Mage_Core_Model_Abstract {
    protected $_storeId = null;

    /**
     * Return TRUE when Enable emails notifications isn't set to "Disabled"
     * @return boolean
     */
    protected function _isGenerallyEnabled() {
        return Mage::helper('awrma/config')->getEnableEmailNotifications($this->_storeId) != AW_Rma_Model_Source_Config_Enableemailsnotifications::DISABLED;
    }

    /**
     * Return TRUE when Enable emails notifications is set to All or Customer only
     * @return boolean
     */
    protected function _isEnabledForCustomer() {
        return $this->_isGenerallyEnabled() && Mage::helper('awrma/config')->getEnableEmailNotifications($this->_storeId) != AW_Rma_Model_Source_Config_Enableemailsnotifications::ADMINONLY;
    }

    /**
     * Return TRUE when Enable emails notifications is set to All or Admin only
     * @return boolean
     */
    protected function _isEnabledForAdmin() {
        return $this->_isGenerallyEnabled() && Mage::helper('awrma/config')->getEnableEmailNotifications($this->_storeId) != AW_Rma_Model_Source_Config_Enableemailsnotifications::CUSTOMERONLY;
    }

    /**
     * Wrapper for new request event
     * @return AW_Rma_Model_Notify
     */
    public function notifyNew($rmaRequest, $commentText = NULL) {
        $this->setFlagNotifyNew(true);
        return $this->_notifyByStatus($rmaRequest, $commentText, TRUE);
    }

    /**
     * Parse all templates in status and sent emails and adds messages to chatbox
     * @param AW_Rma_Model_Entity $rmaRequest
     * @param string $commentText
     * @param boolean $isFrontend
     * @return AW_Rma_Model_Notify
     */
    protected function _notifyByStatus($rmaRequest, $commentText = NULL, $isFrontend = FALSE) {
        if($this->_isGenerallyEnabled()) {
            $rmaRequest = Mage::getModel('awrma/entity')->load($rmaRequest->getId());

            $currentDesign = Mage::getDesign()->setAllGetOld(array(
                'package' => Mage::getStoreConfig('design/package/name', $rmaRequest->getStoreId()),
                'store' => $rmaRequest->getStoreId()
            ));

            # Common variables
            $_status = Mage::getModel('awrma/entitystatus')->load($rmaRequest->getStatus());
            if($_status->getData() == array()) throw new Exception('Can\t load request status');
            $_subject = Mage::helper('awrma')->__('Notify about RMA ').$rmaRequest->getTextId();
            $_store = Mage::app()->getSafeStore($rmaRequest->getStoreId());
            $_sender = Mage::helper('awrma/config')->getEmailSender($rmaRequest->getStoreId());
            $_departmentDisplayName = Mage::helper('awrma/config')->getDepartmentDisplayName($rmaRequest->getStoreId());

            if($commentText) {
                $rmaRequest->setData('notify_has_comment', true);
                $rmaRequest->setData('notify_comment_text', $commentText);
            }

            if($this->getFlagStatusChanged())
                $rmaRequest->setData('notify_status_changed', true);
            $rmaRequest->setData('notify_initiated_by_customer', $isFrontend);
            $rmaRequest->setData('notify_initiated_by_admin', !$isFrontend);
            $rmaRequest->setData('notify_printlabel_allowed', Mage::helper('awrma/config')->getAllowPrintLabel($rmaRequest->getStoreId()));
            $rmaRequest->setData('confirm_shipping_is_required', Mage::helper('awrma/config')->getRequireConfirmSending($rmaRequest->getStoreId()));
            $rmaRequest->setData('notify_rma_address', Mage::helper('awrma/config')->getDepartmentAddress($rmaRequest->getStoreId()));

            if(Mage::helper('awrma')->getMagentoVersionCode() == AW_Rma_Helper_Data::MAGENTO_VERSION_CE_13x)
                $_store->setFrontendName($rmaRequest->getOrder()->getStoreGroupName());

            # Notify customer
            if($this->_isEnabledForCustomer() && $_status->getToCustomer()) {
                # Notify customer
                if($this->getFlagNotifyNew()) $rmaRequest->setData('notify_has_comment', false);
                $emailTemplate = Mage::getModel('awrma/email_template')
                    ->setDesignConfig(array('area'=>'frontend', 'store'=>$rmaRequest->getStoreId()))
                    ->setAWRMATemplate($_status->getToCustomer(), AW_Rma_Model_Email_Template::AWRMA_RECIPIENT_CUSTOMER)
                    ->sendEmail($_sender, $rmaRequest->getCustomerEmail(),
                            $rmaRequest->getCustomerName(), array(
                                'request' => $rmaRequest,
                                'store' => $_store,
                                'subject' => $_subject,
                                'depname' => $_departmentDisplayName
                        ));
            }

            if($commentText) $rmaRequest->setData('notify_has_comment', true);
            # Notify admin
            if($this->_isEnabledForAdmin() && $_status->getToAdmin() && $isFrontend) {
                $rmaRequest->setData('notify_order_admin_link', Mage::helper('awrma')->getOrderUrl($rmaRequest->getOrderId()));

                $emailTemplate = Mage::getModel('awrma/email_template')
                    ->setDesignConfig(array('area'=>'frontend', 'store'=>$rmaRequest->getStoreId()))
                    ->setAWRMATemplate($_status->getToAdmin(), AW_Rma_Model_Email_Template::AWRMA_RECIPIENT_ADMIN)
                    ->sendEmail($_sender, Mage::helper('awrma/config')->getDepartmentEmail($rmaRequest->getStoreId()),
                            Mage::helper('awrma/config')->getDepartmentDisplayName($rmaRequest->getStoreId()), array(
                                'request' => $rmaRequest,
                                'store' => $_store,
                                'subject' => $_subject,
                                'depname' => $_departmentDisplayName
                        ));
            }

            # Chatbox
            if($_status->getToChatbox()) {
                $emailTemplate = Mage::getModel('awrma/email_template')
                    ->setDesignConfig(array('area'=>'frontend', 'store'=>$rmaRequest->getStoreId()))
                    ->setAWRMATemplate($_status->getToChatbox(), AW_Rma_Model_Email_Template::AWRMA_RECIPIENT_CHATBOX);
                Mage::helper('awrma/comments')->postComment($rmaRequest->getId(), $emailTemplate->getProcessedTemplate(array(
                    'request' => $rmaRequest,
                    'store' => $_store,
                    'depname' => $_departmentDisplayName)), array(), FALSE);
            }

            Mage::getDesign()->setAllGetOld($currentDesign);
        }
        return $this;
    }

    /**
     * Notify customer or admin about comment on request
     * @param AW_Rma_Model_Entity $rmaRequest
     * @param string $commentText
     * @param boolean $isFrontend
     * @return AW_Rma_Model_Notify
     */
    public function notifyAboutComment($rmaRequest, $commentText = null, $isFrontend = FALSE) {
        if($this->_isGenerallyEnabled() && $commentText) {
            $rmaRequest = Mage::getModel('awrma/entity')->load(is_object($rmaRequest) ? $rmaRequest->getId() : $rmaRequest);

            $currentDesign = Mage::getDesign()->setAllGetOld(array(
                'package' => Mage::getStoreConfig('design/package/name', $rmaRequest->getStoreId()),
                'store' => $rmaRequest->getStoreId()
            ));

            $_subject = Mage::helper('awrma')->__('Notify about RMA #').$rmaRequest->getTextId();
            $_store = Mage::app()->getSafeStore($rmaRequest->getStoreId());
            $_sender = Mage::helper('awrma/config')->getEmailSender($rmaRequest->getStoreId());
            $_departmentDisplayName = Mage::helper('awrma/config')->getDepartmentDisplayName($rmaRequest->getStoreId());

            $rmaRequest->setData('notify_initiated_by_customer', $isFrontend);
            $rmaRequest->setData('notify_initiated_by_admin', !$isFrontend);
            $rmaRequest->setData('notify_has_comment', true);
            $rmaRequest->setData('notify_comment_text', $commentText);

            if(Mage::helper('awrma')->getMagentoVersionCode() == AW_Rma_Helper_Data::MAGENTO_VERSION_CE_13x)
                $_store->setFrontendName($rmaRequest->getOrder()->getStoreGroupName());

            if($isFrontend && $this->_isEnabledForAdmin()) {
                # Notification to admin
                $emailTemplate = Mage::getModel('awrma/email_template')
                    ->setDesignConfig(array('area'=>'frontend', 'store'=>$rmaRequest->getStoreId()))
                    ->setAWRMATemplate('', AW_Rma_Model_Email_Template::AWRMA_RECIPIENT_ADMIN)
                    ->sendEmail($_sender, Mage::helper('awrma/config')->getDepartmentEmail($rmaRequest->getStoreId()),
                            Mage::helper('awrma/config')->getDepartmentDisplayName($rmaRequest->getStoreId()), array(
                                'request' => $rmaRequest,
                                'store' => $_store,
                                'subject' => $_subject,
                                'depname' => $_departmentDisplayName
                        ));
            }

            if(!$isFrontend && $this->_isEnabledForCustomer()) {
                # Notification to customer
                $emailTemplate = Mage::getModel('awrma/email_template')
                    ->setDesignConfig(array('area'=>'frontend', 'store'=>$rmaRequest->getStoreId()))
                    ->setAWRMATemplate('', AW_Rma_Model_Email_Template::AWRMA_RECIPIENT_CUSTOMER)
                    ->sendEmail($_sender, $rmaRequest->getCustomerEmail(),
                            $rmaRequest->getCustomerName(), array(
                                'request' => $rmaRequest,
                                'store' => $_store,
                                'subject' => $_subject,
                                'depname' => $_departmentDisplayName
                        ));
            }

            Mage::getDesign()->setAllGetOld($currentDesign);
        }
        return $this;
    }

    /**
     * Check changes in request and call some functions on it
     * @param AW_Rma_Model_Entity $rmaRequest
     * @param string $commentText
     * @param boolean $isFrontend
     * @return AW_Rma_Model_Notify
     */
    public function checkChanges($rmaRequest, $commentText = null, $isFrontend = FALSE) {
        if($this->_isGenerallyEnabled()) {
            if($rmaRequest->getData('status') != $rmaRequest->getOrigData('status')) {
                # Status changes
                $this->setFlagStatusChanged(true);
                $this->_notifyByStatus($rmaRequest, $commentText, $isFrontend);
            } elseif ($commentText) {
                # Comment added without status change
                $this->notifyAboutComment($rmaRequest, $commentText, $isFrontend);
            }
        }
        return $this;
    }
}
