<?php
require_once 'Mage/Customer/controllers/AccountController.php';
class Cutehits_Customer_AccountController extends Mage_Customer_AccountController
{
 // override existing method
 // public function createPostAction()
 //{
 //die("function calling");
 //}
 public function unapprovedAction()
    {
    	die('1231231');
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $this->getResponse()->setHeader('Login-Required', 'true');
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session'); 
        $this->renderLayout();
    }
     public function preDispatch()
    {
        // a brute-force protection here would be nice

 	//       parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        $pattern = '/^(create|login|unapproved|logoutSuccess|forgotpassword|forgotpasswordpost|confirm|confirmation)/i';
        if (!preg_match($pattern, $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
    }

}
?>