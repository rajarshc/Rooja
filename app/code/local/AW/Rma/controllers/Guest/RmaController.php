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
class AW_Rma_Guest_RmaController extends Mage_Core_Controller_Front_Action {
    private function hasErrors() {
        return (bool) count($this->_getSession()->getMessages()->getItemsByType('error'));
    }

    protected function _initAction($title = 'RMA') {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');

        if(!Mage::helper('awrma/config')->getAllowAnonymousAccess() && $this->getRequest()->getActionName() != 'view')
            return $this->_redirect('customer/account/login');

        $this->getLayout()->getBlock('head')->setTitle($this->__($title));

        return $this;
    }

    private function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Retreive AW_Rma_Model_Entity by id given in request
     * or null otherwise
     */
    protected function _getRmaRequest() {
        if($this->getRequest()->getParam('id')) {
            $_rmaRequest = Mage::getModel('awrma/entity')->loadByExternalLink($this->getRequest()->getParam('id'));
            if(is_object($_rmaRequest) && $_rmaRequest->getData() != array()) {
                return $_rmaRequest;
            } else {
                $this->_getSession()->addError('Can\'t load RMA request');
            }
        } else {
            $this->_getSession()->addError($this->__('RMA id isn\'t specified'));
        }

        $this->_redirect('awrma/guest_rma/index');
        return null;
    }

    protected function indexAction() {
        $this->_initAction()->renderLayout();
    }

    protected function newAction() {
        if(!$this->_getSession()->hasData('awrma_guest_order'))
            return $this->_redirect('*/*/index');

        $this->_initAction()->renderLayout();
    }

    protected function viewAction() {
        $this->_initAction();
        
        if(($_rmaRequest = $this->_getRmaRequest())) {
            Mage::unregister('awrma-request');
            Mage::register('awrma-request', $_rmaRequest);
        }
        
        $this->renderLayout();
    }

    protected function saveAction() {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('awrma/guest_rma/new');
        }
        $newRma = Mage::helper('awrma/request')->save($this->getRequest(), TRUE);
        if($this->hasErrors() || !$newRma) {
            return $this->_redirect('awrma/guest_rma/new');
        } else {
            return $this->_redirect('awrma/guest_rma/view', array('id' => $newRma));
        }
    }

    protected function commentAction() {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('awrma/guest_rma/new');
        }
        $newComment = Mage::helper('awrma/comments')->saveComment($this->getRequest(), TRUE);
        $this->_redirect('awrma/guest_rma/view', array('id' => $this->getRequest()->getParam('id')));
    }

    protected function checkorderAction() {
        if($this->getRequest()->isPost()) {
            if($this->getRequest()->getParam('orderid')) {
                $orderId = trim($this->getRequest()->getParam('orderid'));
                $orderId = preg_replace('/^#/', '', $orderId);
                $_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
                if($_order->getData() != array()) {
                    if($_order->getCustomerEmail() == $this->getRequest()->getParam('email')) {
                        if(is_null($_order->getCustomerId())) {
                            if(Mage::helper('awrma')->isAllowedForOrder($_order)) {
                                $this->_getSession()->setData('awrma_guest_order', $_order);
                                return $this->_redirect('awrma/guest_rma/new');
                            } else {
                                $this->_getSession()->addError($this->__('Specified order created later than '.Mage::helper('awrma/config')->getDaysAfter().' days ago or it not completed'));
                            }
                        } else {
                            $this->_getSession()->addError($this->__('This order has been placed by registered customer. Please, authorize and request RMA via customer account.'));
                        }
                    } else {
                        $this->_getSession()->addError($this->__('Order ID and customer email didn\'t match each other'));
                    }
                } else {
                    $this->_getSession()->addError($this->__('Couldn\'t load order by given order ID'));
                }
            } else {
                $this->_getSession()->addError($this->__('Order ID isn\'t specified'));
            }
        } else {
            $this->_getSession()->addError($this->__('Wrong request method'));
        }

        if($this->hasErrors()) return $this->_redirect('awrma/guest_rma/index');
    }

    public function getitemsfororderAction() {
        $this->_initAction();

        header('Content-type: application/x-json');
        if($this->_getSession()->getData('awrma_guest_order'))
            $result = Mage::helper('awrma')->getItemsForOrderHtml($this->getRequest()->getParam('incrementid'), TRUE, $this->_getSession()->getData('awrma_guest_order')->getCustomerEmail());
        else
            return header('HTTP/1.1 403 Forbidden');
        echo Zend_Json::encode($result);

        exit(0);
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

    protected function cancelAction() {
        if(($_rmaRequest = $this->_getRmaRequest())) {
            $_rmaRequest->setStatus(Mage::helper('awrma/status')->getResolvedCanceledStatusId());
            $_rmaRequest->save();
            Mage::getModel('awrma/notify')->checkChanges($_rmaRequest, NULL, TRUE);
            $this->_getSession()->addSuccess($this->__('Your RMA successfully canceled'));
        }

        return $this->_redirect('awrma/guest_rma/view', array('id' => $_rmaRequest->getExternalLink()));
    }

    protected function testAction() {
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
        if(($rmaRequest = $this->_getRmaRequest())) {
            if($this->getRequest()->isPost()) {
                if(!$this->_validateFormKey())
                    return $this->_redirect('*/*/index');
                $printLabel = $this->getRequest()->getParam('printlabel');
                if($printLabel['stateprovince_id'] && !$printLabel['stateprovince'])
                    $printLabel['stateprovince'] = Mage::helper('awrma')->getRegionName($printLabel['stateprovince_id']);
                $rmaRequest->setPrintLabel($printLabel)
                    ->save()
                    ->load($rmaRequest->getId());
            }
            Mage::unregister('awrma-request');
            Mage::register('awrma-request', $rmaRequest);
            Mage::unregister('awrma-formdata');
            Mage::register('awrma-formdata', $rmaRequest->getPrintLabel());
        }
        $this->_initAction()->renderLayout();
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
}
