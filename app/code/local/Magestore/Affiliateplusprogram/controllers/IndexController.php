<?php
class Magestore_Affiliateplusprogram_IndexController extends Mage_Core_Controller_Front_Action
{
	/**
	 * get Account helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Account
	 */
	protected function _getAccountHelper(){
		return Mage::helper('affiliateplus/account');
	}
	
	/**
	 * get Core Session
	 *
	 * @return Mage_Core_Model_Session
	 */
	protected function _getCoreSession(){
		return Mage::getSingleton('core/session');
	}
	
    public function indexAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){ return; }
    	if ($this->_getAccountHelper()->accountNotLogin())
    		return $this->_redirect('affiliateplus/account/login');
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('My Programs'));
		$this->renderLayout();
    }
    
    public function detailAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){ return; }
    	if ($this->_getAccountHelper()->accountNotLogin())
    		return $this->_redirect('affiliateplus/account/login');
    	$program = Mage::getModel('affiliateplusprogram/program')->setStoreId(Mage::app()->getStore()->getId())->load($this->getRequest()->getParam('id'));
    	if ($program && $program->getId() && $program->getStatus()){
    		$this->loadLayout();
			$this->getLayout()->getBlock('head')->setTitle($this->__('Program: "%s"',$program->getName()));
			$this->renderLayout();
    	}else {
    		$this->_getCoreSession()->addError($this->__('Program not found!'));
    		return $this->_redirect('*/*/index');
    	}
    }
    
    public function outAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){ return; }
    	if ($this->_getAccountHelper()->accountNotLogin())
    		return $this->_redirect('affiliateplus/account/login');
    	if ($id = $this->getRequest()->getParam('id')){
    		$programAccount = Mage::getResourceModel('affiliateplusprogram/account_collection')
    			->addFieldToFilter('program_id',$id)
    			->addFieldToFilter('account_id',$this->_getAccountHelper()->getAccount()->getId())
    			->getFirstItem();
    		if ($programAccount && $programAccount->getId()){
    			try {
    				$programAccount->delete();
    				$program = Mage::getModel('affiliateplusprogram/program')->load($id);
    				$program->setNumAccount($program->getNumAccount()-1)->save();
    				$this->_getCoreSession()->addSuccess($this->__('You have been out of program "%s" successfully!',$program->getName()));
    			}catch (Exception $e){
    				$this->_getCoreSession()->addError($e->getMessage());
    			}
    		}else
    			$this->_getCoreSession()->addError($this->__('Program not joined!'));
    	}else
    		$this->_getCoreSession()->addError($this->__('Program not found!'));
    	return $this->_redirect('*/*/index');
    }
    
    public function allAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){ return; }
    	if ($this->_getAccountHelper()->accountNotLogin())
    		return $this->_redirect('affiliateplus/account/login');
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Other Programs'));
		$this->renderLayout();
    }
    
    public function joinAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){ return; }
    	if ($this->_getAccountHelper()->accountNotLogin())
    		return $this->_redirect('affiliateplus/account/login');
    	if ($id = $this->getRequest()->getParam('id')){
    		$programAccount = Mage::getResourceModel('affiliateplusprogram/account_collection')
    			->addFieldToFilter('program_id',$id)
    			->addFieldToFilter('account_id',$this->_getAccountHelper()->getAccount()->getId())
    			->getFirstItem();
    		if ($programAccount && $programAccount->getId()){
    			$this->_getCoreSession()->addError($this->__('Program joined already!'));
    		}else{
    			try {
    				$programAccount = Mage::getModel('affiliateplusprogram/account')
    					->setAccountId($this->_getAccountHelper()->getAccount()->getId())
    					->setProgramId($id)
    					->setJoined(now())
    					->save();
    				$program = Mage::getModel('affiliateplusprogram/program')->load($id);
    				$program->setNumAccount($program->getNumAccount()+1)->save();
                    // Update joined program
                    Mage::getModel('affiliateplusprogram/joined')->insertJoined($program->getId(), $programAccount->getAccountId());
    				$this->_getCoreSession()->addSuccess($this->__('You have joined program "%s" successfully!',$program->getName()));
    			}catch (Exception $e){
    				$this->_getCoreSession()->addError($e->getMessage());
    			}
    		}
    	}else
    		$this->_getCoreSession()->addError($this->__('Program not found!'));
    	return $this->_redirect('*/*/index');
    }
    
    public function joinallAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){ return; }
    	if ($this->_getAccountHelper()->accountNotLogin())
    		return $this->_redirect('affiliateplus/account/login');
    	$programIds = $this->getRequest()->getParam('program_ids');
    	if (is_array($programIds) && count($programIds)){
    		$programModel = Mage::getModel('affiliateplusprogram/account')
    			->setAccountId($this->_getAccountHelper()->getAccount()->getId())
    			->setJoined(now());
    		foreach ($programIds as $id){
    			$programAccount = Mage::getResourceModel('affiliateplusprogram/account_collection')
	    			->addFieldToFilter('program_id',$id)
	    			->addFieldToFilter('account_id',$this->_getAccountHelper()->getAccount()->getId())
	    			->getFirstItem();
	    		if ($programAccount && $programAccount->getId()){
	    			continue;
	    		}else{
	    			try {
	    				$programModel->setProgramId($id)
	    					->setId(null)
	    					->save();
	    				$program = Mage::getModel('affiliateplusprogram/program')->load($id);
	    				$program->setNumAccount($program->getNumAccount()+1)->save();
	    				$this->_getCoreSession()->addSuccess($this->__('You have joined program "%s" successfully!',$program->getName()));
	    			}catch (Exception $e){
	    				$this->_getCoreSession()->addError($e->getMessage());
	    				return $this->_redirect('*/*/all');
	    			}
	    		}
    		}
            // Update joined program for current account
            try {
                Mage::getModel('affiliateplusprogram/joined')->updateJoined(null, $programModel->getAccountId());
            } catch (Exception $e) {
                $this->_getCoreSession()->addError($e->getMessage());
                return $this->_redirect('*/*/all');
            }
    		//$this->_getCoreSession()->addSuccess($this->__('You have joined to %s program successfully!',count($programIds)));
    	}else {
    		$this->_getCoreSession()->addError($this->__('Please select a program to join!'));
    		return $this->_redirect('*/*/all');
    	}
    	return $this->_redirect('*/*/index');
    }
}