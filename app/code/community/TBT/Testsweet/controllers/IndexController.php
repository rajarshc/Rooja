<?php

class TBT_Testsweet_IndexController extends Mage_Core_Controller_Front_Action {

    public function allAction() {
        /* @var $test TBT_Testsweet_Model_Test_Abstract */
        Mage::getModel('testsweet/test')->all();
    }

    public function indexAction() {
        echo "<pre>";
        $this->allAction();
        echo "</pre>";
        //$this->loadLayout();
        //$this->renderLayout();
    }

    /**
     * Controller predispatch method
     *
     * @return Mage_Adminhtml_Controller_Action
     
    public function preDispatch() {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            $this->auth();
        } else {
            $auth_result = Mage::getModel('admin/user')->authenticate(
                            $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']
            );
            if ($auth_result) {
                return parent::preDispatch();
            } else {
                unset($_SERVER['PHP_AUTH_USER']);
                $this->auth();
            }
        }
    }
     * 
     */

    /**
     * Authentication Function
     
    protected function auth() {
        $title = 'Store Administrator Log-in';
        header('WWW-Authenticate: Basic realm="' . $title . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo "You must authenticate yourself before viewing this file.  Please e-mail administration if you don't think you should be seeeing this message.";
        exit;
    }
     * 
     */

}