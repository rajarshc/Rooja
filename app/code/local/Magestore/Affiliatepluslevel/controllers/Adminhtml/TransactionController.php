<?php

class Magestore_Affiliatepluslevel_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action
{
	public function tierAction()
	{
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->loadLayout();
        $this->renderLayout();	
	}
	
	public function tierGridAction()
	{
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->loadLayout();
        $this->renderLayout();		
	}
}