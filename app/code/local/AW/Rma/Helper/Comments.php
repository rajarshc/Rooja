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
class AW_Rma_Helper_Comments extends Mage_Core_Helper_Abstract {
    public static function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Returns string where all text like #23423423 is
     * replaced by links to orders if this order is exists
     * @param string $text
     * @return string
     */
    public static function parseOrderId($text) {
        $text .= ' ';
        preg_match_all("/#.*?[.\, \n\r$]/", $text, $matches);
        if(isset($matches[0]))
            foreach($matches[0] as $orderId) {
                $orderIdInText = substr($orderId, 0, -1);
                $incrementId = substr($orderIdInText, 1);
                $_order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
                if($_order->getData() != array())
                    $text = str_replace ($orderIdInText, "<a href=\"".Mage::app()->getStore()->getUrl('sales/order/view', array('order_id' => $_order->getId()))."\">{$orderIdInText}</a>", $text);
            }

        return substr($text, 0, -1);
    }

    /**
     * Add comment to request
     * @param int $id - RMARequest id
     * @param string $text - Comment text
     * @param array $data - Additional parameters, like created_at, owner
     * @return AW_Rma_Model_Entitycomments
     */
    public static function postComment($id, $text, $data = array(), $sendNotify = TRUE) {
        if($id) {
            $data['entity_id'] = $id;
            $data['text'] = Mage::helper('awrma')->htmlEscape($text);
            $data['text'] = nl2br(self::parseOrderId($data['text']));
            if(!isset($data['created_at']))
                $data['created_at'] = date(AW_Rma_Model_Mysql4_Entitycomments::DATETIMEFORMAT, time());
            if(!isset($data['owner']))
                $data['owner'] = AW_Rma_Model_Source_Owner::ADMIN;

            $_comment = Mage::getModel('awrma/entitycomments');
            $_comment->setData($data);
            $_comment->save();
            
            if($sendNotify)
                Mage::getModel('awrma/notify')->notifyAboutComment($id, $text, $data['owner'] == AW_Rma_Model_Source_Owner::CUSTOMER);

            return $_comment;
        }
        return FALSE;
    }

    /**
     * Validate comment and call postComment function, or
     * adds errors into a customer session if some error occurs
     * @param AW_Rma_Model_Entity $request
     * @param boolean $guestMode - Is comment posted by guest
     * @return boolean
     */
    public static function saveComment($request, $guestMode = FALSE) {
        $_okFlag = TRUE;
        $_rmaRequest = Mage::getModel('awrma/entity');
        if($guestMode)
            $_rmaRequest->loadByExternalLink($request->getParam('id'));
        else
            $_rmaRequest->load($request->getParam('id'));
        if($_rmaRequest->getData() != array()) {
            if(!in_array($_rmaRequest->getStatus(), Mage::helper('awrma/status')->getResolvedStatuses())) {
                $_data = array();
                $_data['text'] = $request->getParam('comment');
                if($_data['text']) {
                    if(isset($_FILES['attachedfile']['name']) && $_FILES['attachedfile']['name']) {
                        if(!in_array(Mage::helper('awrma/files')->getExtension($_FILES['attachedfile']['name']), Mage::helper('awrma/config')->getForbiddenExtensions())) {
                            if($_FILES['attachedfile']['size'] <= Mage::helper('awrma/config')->getMaxAttachmentsSize() && $_FILES['attachedfile']['size'] > 0) {
                                if($_FILES['attachedfile']['error'] == UPLOAD_ERR_OK) {
                                    try {
                                        $uploader = new Varien_File_Uploader('attachedfile');
                                        $uploader
                                            ->setAllowedExtensions(null)
                                            ->setAllowRenameFiles(TRUE)
                                            ->setAllowCreateFolders(TRUE)
                                            ->setFilesDispersion(FALSE);
                                        $result = $uploader->save(Mage::helper('awrma/files')->getPath(), $_FILES['attachedfile']['name']);
                                        $_data['attachments'] = $result['file'];
                                    } catch (Exception $ex) {
                                        $_okFlag = FALSE;
                                        self::_getSession()->addError($ex->getMessage());
                                    }
                                } else {
                                    $_okFlag = FALSE;
                                    self::_getSession()->addError(Mage::helper('awrma')->__('Some error occurs when uploading file'));
                                }
                            } else {
                                $_okFlag = FALSE;
                                self::_getSession()->addError(Mage::helper('awrma')->__('Maximal allowed attachment size is '.(floor(Mage::helper('awrma/config')->getMaxAttachmentsSize()/1024)).' kb'));
                            }
                        } else {
                            $_okFlag = FALSE;
                            self::_getSession()->addError(Mage::helper('awrma')->__('Forbidden file extension'));
                        }
                    }

                    if($_okFlag) {
                        $_data['owner'] = AW_Rma_Model_Source_Owner::CUSTOMER;
                        self::postComment($_rmaRequest->getId(), $_data['text'], $_data);

                        //Clear form data in session
                        self::_getSession()->getAWRMACommentFormData(TRUE);
                        self::_getSession()->addSuccess(Mage::helper('awrma')->__('Comment successfully added'));
                        return $guestMode ? $_rmaRequest->getExternalLink() : $_rmaRequest->getId();
                    }
                } else {
                    $_okFlag = FALSE;
                    self::_getSession()->addError(Mage::helper('awrma')->__('Comment text can\'t be empty'));
                }
            } else {
                $_okFlag = FALSE;
                self::_getSession()->addError(Mage::helper('awrma')->__('You can\'t comment resolved RMA'));
            }
        } else {
            $_okFlag = FALSE;
            self::_getSession()->addError(Mage::helper('awrma')->__('Can\'t load RMA request'));
        }

        self::_getSession()->setAWRMACommentFormData($request->getParams());

        return $_okFlag;
    }
}
