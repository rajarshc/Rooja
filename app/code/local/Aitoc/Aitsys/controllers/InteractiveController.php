<?php

class Aitoc_Aitsys_InteractiveController extends Aitoc_Aitsys_Abstract_Adminhtml_Controller
{
    
    public function preDispatch()
    {
        $result = parent::preDispatch();
        $this->tool()->setInteractiveSession($this->_getSession());
        if ($this->tool()->platform()->isBlocked() && 'error' != $this->getRequest()->getActionName())
        {
            $this->_forward('error','index');
        }
        return $result;
    }
    
    public function indexAction()
    {
        $this->interactive();
        $this->_redirect('*/index');
    }
    
    public function interactive()
    {
        $query = array();
        $request = $this->getRequest();
        $method = $request->getParam('method');
        $query['cid'] = $request->getParam('cid');
        $query['args'] = $request->getParam('args');
        if (!$method)
        {
            $method = 'interactivePostback';
        }
        if (!$query['cid'])
        {
            unset($query['cid']);
        }
        if (!$query['args'])
        {
            unset($query['args']);
        }
        $service = $this->tool()->platform()->getService();
        if ($service->connect()->isLogined())
        {
            try
            {
                $service->$method($query);
            }
            catch (Exception $exc)
            {
                $this->tool()->testMsg($exc);
            }
            $service->disconnect();
        }
    }
    
}