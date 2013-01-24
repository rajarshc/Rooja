<?php

class Aitoc_Aitsys_ValidateController extends Mage_Core_Controller_Front_Action
{
    public function validateAction()
    {
        $key = Mage::app()->getRequest()->getParam('key');
        if ($key)
        {
            $value = Aitoc_Aitsys_Abstract_Service::get()->getLicenseHelper()->getValidationValue($key);
            echo $value;
        }
    }
}