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
class AW_Rma_Block_Customer_View extends Mage_Core_Block_Template {
    /**
     * Collection of comments
     * @var AW_Rma_Model_Mysql4_Entitycomments_Collection
     */
    private $_comments = null;
    private $_guestMode = FALSE;
    private $_rmaRequest = null;

    public function __construct() {
        parent::__construct();
        switch(Mage::helper('awrma')->getMagentoVersionCode()) {
            case AW_Rma_Helper_Data::MAGENTO_VERSION_CE_13x:
                $_template = 'aw_rma/customer/view13x.phtml';
                break;
            default:
                $_template='aw_rma/customer/view.phtml';
        }
        $this->setTemplate($_template);
        return $this;
    }

    /**
     * Returns RMA request and loads all comments for it
     * @return AW_Rma_Model_Entity
     */
    public function getRMARequest() {
        if(!$this->_rmaRequest) {
            $_request = Mage::registry('awrma-request');
            if(!is_null($_request) && is_null($this->_comments)) {
                $this->_comments = Mage::getModel('awrma/entitycomments')->getCollection()
                    ->setEntityFilter($_request->getId())
                    ->setOrder('created_at', 'DESC')
                    ->setOrder('id', 'DESC')
                    ->load();
            }
            $this->_rmaRequest = $_request;
        }
        return $this->_rmaRequest;
    }

    /**
     * Returns all comments for current request
     * @return AW_Rma_Model_Mysql4_Entitycomments_Collection
     */
    public function getComments() {
        return $this->_comments;
    }

    /**
     * Returns stored form data
     * @return array
     */
    public function getFormData() {
        return Mage::getSingleton('customer/session')->getAWRMACommentFormData(TRUE);
    }

    public function setGuestMode($val = TRUE) {
        $this->_guestMode = (bool) $val;
        return $this;
    }

    public function getGuestMode() {
        return $this->_guestMode;
    }

    public function getPrintLabelUrl() {
        if($this->getGuestMode())
            return $this->getUrl('awrma/guest_rma/printlabel', array('id' => $this->getRMARequest()->getExternalLink()));
        else
            return $this->getUrl('awrma/customer_rma/printlabel', array('id' => $this->getRMARequest()->getId()));
    }

    public function getConfirmSendUrl() {
        if($this->getGuestMode())
            return $this->getUrl('awrma/guest_rma/confirmsend', array('id' => $this->getRMARequest()->getExternalLink()));
        else
            return $this->getUrl('awrma/customer_rma/confirmsend', array('id' => $this->getRMARequest()->getId()));
    }

    public function getCancelUrl() {
        if($this->getGuestMode())
            return $this->getUrl('awrma/guest_rma/cancel', array('id' => $this->getRMARequest()->getExternalLink()));
        else
            return $this->getUrl('awrma/customer_rma/cancel', array('id' => $this->getRMARequest()->getId()));
    }

    public function getCommentUrl() {
         if($this->getGuestMode())
            return $this->getUrl('awrma/guest_rma/comment', array('id' => $this->getRMARequest()->getExternalLink()));
        else
            return $this->getUrl('awrma/customer_rma/comment', array('id' => $this->getRMARequest()->getId()));
    }

    public function getDownloadUrl($comment) {
        if($this->getGuestMode())
            return $this->getUrl('awrma/guest_rma/download', array('cid' => $comment->getId()));
        else
            return $this->getUrl('awrma/customer_rma/download', array('cid' => $comment->getId()));
    }

    public function getPreparedJSConfirmText() {
        $confirmtext = Mage::helper('awrma/config')->getConfirmSendingText();
        $confirmtext = addslashes($confirmtext);
        $confirmtext = mb_ereg_replace("[\x0A]", '\n', $confirmtext);
        $confirmtext = mb_ereg_replace("[\x00-\x09\x0B-\x19\x7F]", '', $confirmtext);
        return $confirmtext;
    }
}
