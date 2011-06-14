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
class AW_Rma_Customer_RmaController extends Mage_Core_Controller_Front_Action {
    private function hasErrors() {
        return (bool) count($this->_getSession()->getMessages()->getItemsByType('error'));
    }

    protected function _initAction($title = 'RMA') {
        // Redirecting to login page when there is no authorized customer
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, TRUE);
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__($title));

        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('awrma/customer_rma/list');
        }

        return $this;
    }

    private function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    protected function _getRmaRequest() {
        if($this->getRequest()->getParam('id')) {
            $_rmaRequest = Mage::getModel('awrma/entity')->load($this->getRequest()->getParam('id'));
            if(is_object($_rmaRequest) && $_rmaRequest->getData() != array()) {
                return $_rmaRequest;
            } else {
                $this->_getSession()->addError('Can\'t load RMA request');
            }
        } else {
            $this->_getSession()->addError($this->__('External RMA ID isn\'t specified'));
        }

        $this->_redirect('awrma/customer_rma/index');
        return null;
    }

    protected function indexAction() {
        $this->_redirect('*/*/list');
    }

    protected function listAction() {
        $this->_initAction()->renderLayout();
    }

    protected function viewAction() {
        $this->_initAction();

        $_okFlag = TRUE;

        if($this->getRequest()->getParam('id')) {
            $_rmaRequest = Mage::getModel('awrma/entity')->load($this->getRequest()->getParam('id'));
            if(is_object($_rmaRequest) && $_rmaRequest->getData() != array()) {
                Mage::unregister('awrma-request');
                Mage::register('awrma-request', $_rmaRequest);
            } else {
                $this->_getSession()->addError('Can\'t load RMA request');
                $_okFlag = FALSE;
            }
        } else {
            $this->_getSession()->addError('No RMA Request ID specified');
            $_okFlag = FALSE;
        }

        if(!$_okFlag)
            $this->_redirect('awrma/customer_rma/list');

        $this->renderLayout();
    }

    protected function commentAction() {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('awrma/customer_rma/list');
        }
        $newComment = Mage::helper('awrma/comments')->saveComment($this->getRequest(), FALSE);
        $this->_redirect('awrma/customer_rma/view', array('id' => $this->getRequest()->getParam('id')));
    }

    protected function downloadAction() {
        if($this->getRequest()->getParam('cid')) {
            $_comment = Mage::getModel('awrma/entitycomments')->load($this->getRequest()->getParam('cid'));
            if($_comment->getData() != array()) {
                Mage::helper('awrma/files')->downloadFile($_comment->getAttachments());
            } else {
                $this->_getSession()->addError($this->__('Can\'t load comment'));
            }
        } else {
            $this->_getSession()->addError($this->__('Comment ID isn\'t specified'));
        }
        $this->_redirect('awrma/customer_rma/list');
    }

    protected function newAction() {
        $this->_initAction('Request RMA');

        $this->renderLayout();
    }

    protected function saveAction() {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('awrma/customer_rma/new');
        }
        $newRma = Mage::helper('awrma/request')->save($this->getRequest());
        if($this->hasErrors() || !$newRma) {
            return $this->_redirect('awrma/customer_rma/new');
        } else {
            return $this->_redirect('awrma/customer_rma/view', array('id' => $newRma));
        }
    }

    public function getitemsfororderAction() {
        $this->_initAction();

        $result = array();
        if(!$this->_getSession()->getCustomer()->getId())
            return header('HTTP/1.1 403 Forbidden');
        header('Content-type: application/x-json');
        $result = Mage::helper('awrma')->getItemsForOrderHtml($this->getRequest()->getParam('incrementid'), FALSE, $this->_getSession()->getCustomer()->getId());
        echo Zend_Json::encode($result);

        exit(0);
    }

    protected function cancelAction() {
        if($this->getRequest()->getParam('id')) {
            $_rmaRequest = Mage::getModel('awrma/entity')->load($this->getRequest()->getParam('id'));
            if($_rmaRequest->getData() != array()) {
                $_rmaRequest->setStatus(Mage::helper('awrma/status')->getResolvedCanceledStatusId());
                $_rmaRequest->save();
                Mage::getModel('awrma/notify')->checkChanges($_rmaRequest, NULL, TRUE);
                $this->_getSession()->addSuccess($this->__('Your RMA successfully canceled'));
            } else {
                $this->_getSession()->addError($this->__('Can\'t load RMA by specified ID'));
            }
        } else {
            $this->_getSession()->addError($this->__('RMA Id isn\'t specified'));
        }

        if(isset($_rmaRequest) && $_rmaRequest->getId())
            $this->_redirect('awrma/customer_rma/view', array('id' => $_rmaRequest->getId()));
        else
            $this->_redirect('awrma/customer_rma/list');
    }

    protected function printlabelAction() {
        if(!Mage::helper('awrma/config')->getAllowPrintLabel())
            return $this->_redirect('*/*/view', array('id' => $this->getRequest()->getParam('id')));
        if(($rmaRequest = $this->_getRmaRequest())) {
            Mage::unregister('awrma-request');
            Mage::register('awrma-request', $rmaRequest);
        }
        $this->_initAction()->renderLayout();
    }

    protected function printformAction() {
        if(!Mage::helper('awrma/config')->getAllowPrintLabel()) {
            $this->_getSession()->addError($this->__('Print Label isn\'t allowed by admin'));
            return $this->_redirect('*/*/view', array('id' => $this->getRequest()->getParam('id')));
        }
        if(!$this->_validateFormKey())
            return $this->_redirect('*/*/list');
        if(($rmaRequest = $this->_getRmaRequest())) {
            $printLabel = $this->getRequest()->getParam('printlabel');
            if($printLabel['stateprovince_id'] && !$printLabel['stateprovince'])
                $printLabel['stateprovince'] = Mage::helper('awrma')->getRegionName($printLabel['stateprovince_id']);
            $rmaRequest->setPrintLabel($printLabel)
                ->save()
                ->load($rmaRequest->getId());
            Mage::unregister('awrma-request');
            Mage::register('awrma-request', $rmaRequest);
            Mage::unregister('awrma-formdata');
            $printLabelData = $this->getRequest()->getParam('printlabel');
            foreach($printLabelData as $key=>$value){
                if(is_array($value)){
                   foreach($printLabelData[$key] as $mKey=>$mValue)
                       $printLabelData[$key][$mKey] = strip_tags($mValue);
                }
                else
                    $printLabelData[$key] = strip_tags($value);
            }
            Mage::register('awrma-formdata', $printLabelData);
        }
        $this->_initAction()->renderLayout();
    }

    protected function testAction() {
        
    }

    protected function confirmsendAction() {
        if(Mage::helper('awrma/config')->getRequireConfirmSending() && ($rmaRequest = $this->_getRmaRequest())) {
            $rmaRequest->setStatus(Mage::helper('awrma/status')->getPackageSentStatusId());
            $rmaRequest->save();
            Mage::getModel('awrma/notify')->checkChanges($rmaRequest, null, TRUE);
            $this->_getSession()->addSuccess($this->__('RMA status has been successfully changed'));
        }
        return $this->_redirect('*/*/view', array('id' => $this->getRequest()->getParam('id')));
    }

    protected function createfororderAction() {
        $_order = Mage::getModel('sales/order')->loadByIncrementId($this->getRequest()->getParam('order_id'));
        if($_order->getData() != array()) {
            $_formData = array(
                'order' => $_order->getIncrementId()
            );
            foreach($_order->getItemsCollection() as $_item) {
                $_formData['orderitems'][$_item->getId()] = intval($_item->getQtyOrdered());
            }
            $this->_getSession()->setAWRMAFormData($_formData);
            return $this->_redirect('*/*/new');
        } else {
            $this->_getSession()->addError($this->__('Can\'t load order'));
            return $this->_redirect('sales/order/history');
        }
    }
}
