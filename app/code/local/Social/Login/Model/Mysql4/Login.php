<?php
class Social_Login_Model_Mysql4_Login extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('login/login', 'login_id');
    }
}