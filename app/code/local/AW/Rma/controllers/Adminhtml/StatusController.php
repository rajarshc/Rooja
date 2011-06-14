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
class AW_Rma_Adminhtml_StatusController extends Mage_Adminhtml_Controller_Action {
    private function hasErrors() {
        return (bool) count($this->_getSession()->getMessages()->getItemsByType('error'));
    }

    protected function _initAction() {
        return $this->loadLayout()->_setActiveMenu('sales/awrma');
    }

    protected function indexAction() {
        $this->_redirect('*/*/list');
    }

    protected function listAction() {
        $this->_initAction();

        $this->renderLayout();
    }

    protected function newAction() {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('awrma/adminhtml_status_edit'));
        $this->renderLayout();
    }

    protected function saveAction() {
        if($this->getRequest()->isPost()) {
            $_status = Mage::getModel('awrma/entitystatus');
            if($this->getRequest()->getParam('id')) {
                $_status->load($this->getRequest()->getParam('id'));
                if($_status->getData() == array())
                    $this->_getSession()->addError($this->__('Can\'t load status by given ID'));
            }

            if(!preg_match("/^[0-9]*$/", $this->getRequest()->getParam('sort')))
                $this->_getSession()->addError($this->__('Sort value must be integer'));

            # Search status by name
            $_status->loadByName($this->getRequest()->getParam('name'));
            
            if(!$this->hasErrors()) {
                $store = $this->getRequest()->getParam('store');
                $_data = array(
                    'id' => $_status->getId(),
                    'name' => $this->getRequest()->getParam('name'),
                    'resolve' => $this->getRequest()->getParam('resolve'),
                    'store' => ($store[0] != '')? $store : Mage::app()->getStore()->getId(),
                    'sort' => !is_null($this->getRequest()->getParam('sort')) && !($this->getRequest()->getParam('sort') == '') ? $this->getRequest()->getParam('sort') : 1,
                    'to_customer' => $this->getRequest()->getParam('to_customer'),
                    'to_admin' => $this->getRequest()->getParam('to_admin'),
                    'to_chatbox' => $this->getRequest()->getParam('to_chatbox'),
                    'removed' => 0
                );

                if(in_array($_data['id'], Mage::helper('awrma/status')->getUneditedStatus()))
                    $_data['store'] = $_status->getStore();

                $_status->setData($_data);
                $_status->save();

                $this->_getSession()->getAWRMAFormData(TRUE);
                $this->_getSession()->addSuccess($this->__('Status has been successfully saved'));
                if($this->getRequest()->getParam('continue'))
                    return $this->_redirect('*/*/edit', array('id' => $_status->getId()));
                else
                    return $this->_redirect('*/*/list');
            }
        } else {
            $this->_getSession()->addError($this->__('This action can be called only via POST'));
        }

        if($this->hasErrors()) {
            $this->_getSession()->setAWRMAFormData($this->getRequest()->getParams());
            if($this->getRequest()->getParam('id'))
                return $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            else
                return $this->_redirect('*/*/new');
        }
    }

    protected function editAction() {
        $this->_initAction();
        if($this->getRequest()->getParam('id')) {
            $_status = Mage::getModel('awrma/entitystatus')->load($this->getRequest()->getParam('id'));
            if($_status->getData() != array())
                Mage::register('awrmaformdatatype', $_status, TRUE);
            else {
                $this->_getSession()->addError($this->__('Can\'t load status by given ID'));
            }
        } else {
            $this->_getSession()->addError($this->__('No ID specified'));
        }

        if($this->hasErrors())
            return $this->_redirect('*/*/list');

        $this->_addContent($this->getLayout()->createBlock('awrma/adminhtml_status_edit'));
        $this->renderLayout();
    }

    protected function deleteAction() {
        if($this->getRequest()->getParam('id')) {
            if(!in_array($this->getRequest()->getParam('id'), Mage::helper('awrma/status')->getUneditedStatus())) {
                $_status = Mage::getModel('awrma/entitystatus')
                    ->load($this->getRequest()->getParam('id'))
                    ->setRemoved(1)
                    ->save();
            } else {
                $this->_getSession()->addError($this->__('You can\'t remove this status'));
            }
        }

        $this->_redirect('*/*/list');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('sales/awrma/status');
    }
}
