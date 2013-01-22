<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_IndexController extends Aitoc_Aitsys_Abstract_Adminhtml_Controller
{
    public function preDispatch()
    {
        $result = parent::preDispatch();
        $this->tool()->setInteractiveSession($this->_getSession());
        if ($this->tool()->platform()->isBlocked() && 'error' != $this->getRequest()->getActionName())
        {
            $this->_forward('error');
        }
        return $result;
    }
    
    public function errorAction()
    {
        $this->loadLayout()->_setActiveMenu('system/aitsys');
        $this->renderLayout();
    }
    
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/aitsys')
            ->_addContent($this->getLayout()->createBlock('aitsys/edit')->initForm());
        $this->getLayout()->getBlock('head')->addCss('aitoc/aitsys.css');
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }
    
    public function saveAction() {
        
        if ($data = $this->getRequest()->getPost('enable')) 
        {
            if ($aErrorList = Mage::getModel('aitsys/aitsys')->saveData($data))
            {
                $aModuleList = Mage::getModel('aitsys/aitsys')->getAitocModuleList();
                
                foreach ($aErrorList as $aError)
                {
                    $this->_getSession()->addError($aError);
                }
                if ($notices = Mage::getModel('aitsys/aitpatch')->getCompatiblityError($aModuleList))
                {
                    foreach ($notices as $notice)
                    {
                        $this->_getSession()->addNotice($notice);
                    }
                }
            }
            else 
            {
                $this->_getSession()->addSuccess($this->_aithelper()->__('Module settings saved successfully'));
            }
        }
        
        $this->_redirect('*/*');
    }
    
    public function permissionsAction()
    {
        $mode = Mage::app()->getRequest()->getParam('mode');
        
        try{
            $this->tool()->filesystem()->permissonsChange($mode);
            Mage::getSingleton('adminhtml/session')->addSuccess($this->_aithelper()->__('Write permissions were changed successfully'));
        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($this->_aithelper()->__('There was an error while changing write permissions. Permissions were not changed.'));        
        }
        
        
        $this->_redirect('*/index');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/aitsys');
    }
}