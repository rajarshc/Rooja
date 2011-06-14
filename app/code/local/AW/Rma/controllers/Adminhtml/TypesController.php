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
class AW_Rma_Adminhtml_TypesController extends Mage_Adminhtml_Controller_Action {
    private function hasErrors() {
        return (bool) $this->_getSession()->getMessages()->count();
    }

    protected function _initAction() {
        return $this->loadLayout()->_setActiveMenu('sales/awrma');
    }

    protected function editAction() {
        $this->_initAction();
        if($this->getRequest()->getParam('id')) {
            $_type = Mage::getModel('awrma/entitytypes')->load($this->getRequest()->getParam('id'));
            if($_type->getData() != array())
                Mage::register('awrmaformdatatype', $_type, TRUE);
            else {
                $this->_getSession()->addError($this->__('Can\'t load type by given ID'));
            }
        } else {
            $this->_getSession()->addError($this->__('No ID specified'));
        }

        if($this->hasErrors())
            return $this->_redirect('*/*/list');

        $this->_addContent($this->getLayout()->createBlock('awrma/adminhtml_types_edit'));
        $this->renderLayout();
    }

    protected function newAction() {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('awrma/adminhtml_types_edit'));
        $this->renderLayout();
    }

    protected function saveAction() {
        if($this->getRequest()->isPost()) {
            $_type = Mage::getModel('awrma/entitytypes');
            if($this->getRequest()->getParam('id')) {
                # Edit existing
                $_type->load($this->getRequest()->getParam('id'));
                if($_type->getData() == array())
                    $this->_getSession()->addError($this->__('Can\'t load type by given ID'));
            }

            if(!preg_match("/^[0-9]*$/", $this->getRequest()->getParam('sort')))
                $this->_getSession()->addError($this->__('Sort value must be integer'));

            if(!$this->hasErrors()) {
                $store = $this->getRequest()->getParam('store');
                $_data = array(
                    'id' => $_type->getId(),
                    'name' => $this->getRequest()->getParam('name'),
                    'store' => (reset($store)!='') ? $store:Mage::app()->getStore()->getId(),
                    'sort' => !is_null($this->getRequest()->getParam('sort')) && !($this->getRequest()->getParam('sort') == '') ? $this->getRequest()->getParam('sort') : 1,
                    'enabled' => $this->getRequest()->getParam('enabled')
                );

                $_type->setData($_data);
                $_type->save();

                $this->_getSession()->addSuccess($this->__('Type has been successfully saved'));
                $this->_getSession()->getAWRMATypesFormData(TRUE);
                if($this->getRequest()->getParam('continue'))
                    return $this->_redirect('*/*/edit', array('id' => $_type->getId()));
                else
                    return $this->_redirect('*/*/list');
            }

        } else {
            $this->_getSession()->addError($this->__('This action can be called only via post'));
        }

        if($this->hasErrors()) {
            $this->_getSession()->setAWRMATypesFormData($this->getRequest()->getParams());
            if($this->getRequest()->getParam('id'))
                return $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            else
                return $this->_redirect('*/*/new');
        }
    }

    protected function indexAction() {
        $this->_redirect('*/*/list');
    }

    protected function listAction() {
        $this->_initAction();

        $this->renderLayout();
    }

    protected function deleteAction() {
        if($this->getRequest()->getParam('id')) {
            $_type = Mage::getModel('awrma/entitytypes')->load($this->getRequest()->getParam('id'))->delete();
        }

        $this->_redirect('*/*/list');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('sales/awrma/types');
    }
}
