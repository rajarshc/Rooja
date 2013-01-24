<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitsys_PatchController extends Aitoc_Aitsys_Abstract_Adminhtml_Controller
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
    
    public function instructionAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/aitsys')
            ->_addContent($this->getLayout()->createBlock('aitsys/patch_instruction'));
        $this->getLayout()->getBlock('head')->addCss('aitoc/aitsys.css');
        $this->renderLayout();
    }
    
    public function indexAction()
    {
        $this->loadLayout()
        ->_setActiveMenu('system/aitsys')
        ->_addContent($this->getLayout()->createBlock('aitsys/patch_view'));
        $this->getLayout()->getBlock('head')->addCss('aitoc/aitsys.css');
        $this->renderLayout();
    }
    
}