<?php

class Aitoc_Aitsys_Model_License_Stop
{
    
    /**
    * 
    * @var Zend_Controller_Request_Abstract
    */
    protected $_request;
    
    public function __construct()
    {
        $this->_request = new Zend_Controller_Request_Http;
    }
    
    public function realize()
    {
        header('Location: '.$this->_request->getRequestUri());
        exit;
    }    
    
}