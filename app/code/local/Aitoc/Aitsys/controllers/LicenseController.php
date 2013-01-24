<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_LicenseController extends Aitoc_Aitsys_Abstract_Adminhtml_Controller
{
    
    protected $_usedModuleName = 'aitsys';
    
    protected $_prepared = false;
    
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
    
    /**
     * 
     * @return Aitoc_Aitsys_LicenseController
     */
    protected function _prepare()
    {
        if (!$this->_prepared)
        {
            $key = $this->getRequest()->getParam('modulekey');
            $this->tool()->platform()->setData('mode', 'live');
            Mage::register('aitoc_module',$this->tool()->platform()->getModule($key));
            $this->_prepared = true;
        }
        return $this;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module
     */
    public function getModule()
    {
        return Mage::registry('aitoc_module');
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_License
     */
    public function getLicense()
    {
        return $this->getModule()->getLicense();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_LicenseController
     */
    protected function _prepareLayout()
    {
        $this->_prepare()->loadLayout()->_setActiveMenu('system/aitsys')
        ->_addContent($this->getLayout()->createBlock('aitsys/manage_widget'));
        $this->getLayout()->getBlock('head')->addCss('aitoc/aitsys.css');
        return $this;
    }
    
    public function deleteAction()
    {
        $this->_prepare();
        $license = $this->getLicense();
        $license->uninstall();
        if (!$license->isUninstalled())
        {
            if ($this->getModule()->produceErrors($this,$this->_getSession()))
            {
                $this->_redirect('*/*/manage',array('modulekey' => $this->getModule()->getKey()));
                return;
            }
        }
        $this->_getSession()->addSuccess($this->__('License deleted, `%s` module uninstalled.',$this->getModule()->getLabel()));
        $this->_redirect('*');
    }
    
    public function reInstallAction()
    {
        $this->_prepare();
        $license = $this->getLicense();        
        if ($license->reInstall()->isInstalled())
        {
            $install = $license->getInstall();
            if ($install->isInstalled())
            {
                $this->_getSession()->addSuccess($this->__('License of %s has been re-installed.',$this->getModule()->getLabel()));
            }
            else 
            {
                $this->_getSession()->addWarning($this->__('License of %s hasn\'t been re-installed.',$this->getModule()->getLabel()));
                $this->getModule()->produceErrors($this,$this->_getSession());
                $aModuleList = Mage::getModel('aitsys/aitsys')->getAitocModuleList();
                if ($notices = Mage::getModel('aitsys/aitpatch')->getCompatiblityError($aModuleList))
                {
                    foreach ($notices as $notice)
                    {
                        $this->_getSession()->addNotice($notice);
                    }
                }
            }
            $this->_redirect('*');
        }
        else
        {
            if(!$this->getModule()->produceErrors($this, $this->_getSession())) {
                $helper = $this->_aithelper('Strings');
                $this->_getSession()->addError($helper->getString('ER_MODULE_CS'));
            }
            $this->getRequest()->setParam('confirmed',true);
            $this->_prepareLayout()->renderLayout();
        }
    }    
    
    public function upgradeAction()
    {
        $this->_prepare();
        $this->getLicense()->upgrade();
        if ($this->getModule()->produceErrors($this,$this->_getSession()))
        {
            $this->_redirect('*/*/manage',array('modulekey' => $this->getModule()->getKey()));
        }
        else
        {
            $this->_getSession()->addSuccess($this->__('New license for `%s` installed.',$this->getModule()->getLabel()));
            $this->_redirect('*/*/manage',array('modulekey' => $this->getModule()->getKey()));
        }
    }
    
    public function installAction()
    {
        $this->_prepare();
        $license = $this->getLicense();
        if ($license->install()->isInstalled())
        {
            $install = $license->getInstall();
            if ($install->isInstalled())
            {
                $this->_getSession()->addSuccess($this->__('License and module %s installed.',$this->getModule()->getLabel()));
            }
            else 
            {
                $this->_getSession()->addWarning($this->__('License of %s module has been installed.',$this->getModule()->getLabel()));
                $this->getModule()->produceErrors($this,$this->_getSession());
                $aModuleList = Mage::getModel('aitsys/aitsys')->getAitocModuleList();
                if ($notices = Mage::getModel('aitsys/aitpatch')->getCompatiblityError($aModuleList))
                {
                    foreach ($notices as $notice)
                    {
                        $this->_getSession()->addNotice($notice);
                    }
                }
            }
            $this->_redirect('*');
        }
        else
        {
            if(!$this->getModule()->produceErrors($this, $this->_getSession())) {
                $helper = $this->_aithelper('Strings');
                $this->_getSession()->addError($helper->getString('ER_MODULE_CS'));
            }
            $this->getRequest()->setParam('confirmed',true);
            $this->_prepareLayout()->renderLayout();
        }
    }
    
    public function confirmAction()
    {
        $platform = $this->tool()->platform(); 
        $this->_prepare();
        if (!$platform->isModePresetted())
        {
            $testMode = 'test' == $this->getRequest()->getParam('installation_type');
            $platform->setTestMode($testMode);
            $platform->save();
        }
        $this->_redirect('*/*/manage',array(
            'modulekey' => $this->getModule()->getKey() ,
            'confirmed' => true
        ));
    }
    
    public function manageAction()
    {
        $this->_prepare();
        $license = $this->getLicense();
        $request = $this->getRequest();
        
        if($request->getParam('newlicense') && !$request->getParam('confirmed')) {
            Mage::getSingleton('adminhtml/session')->addNotice( $this->_aithelper('Strings')->getString('CHANGE_LICENSE_AGREEMENT') );
        }
        
        if($license->isUninstalled() && $request->getParam('confirmed') &&
           $license instanceof Aitoc_Aitsys_Model_Module_License_Light && !$license->getPerformer())
        {
            Mage::getSingleton('adminhtml/session')->addError( $this->_aithelper('Strings')->getString('ER_PERFORMER', true, $license->getModule()->getPerf()) );
        }
        $this->_prepareLayout()->renderLayout();
    }

    public function manualInstallAction()
    {
        $this->_prepare()->loadLayout()->_setActiveMenu('system/aitsys')
        ->_addContent($this->getLayout()->createBlock('aitsys/manualInstall_widget'));
        $this->getLayout()->getBlock('head')->addCss('aitoc/aitsys.css');
        $this->renderLayout();
    }    

    public function manualInstallUploadAction()
    {
        $this->_prepare();
        
        $turnOnModule = false;
        if(!$this->getModule()->getValue() && $this->getLicense()->isUninstalled())
        {
            $turnOnModule = true;
        }
        
        if(isset($_FILES['license_file']['name']) && $_FILES['license_file']['name'] != '')
        {
            try
            {
                $path = Mage::getBaseDir('var');  
                $fname = $_FILES['license_file']['name'];
                $uploader = new Varien_File_Uploader('license_file'); 
                $uploader->setAllowedExtensions(array('sql','php')); 
                $uploader->setAllowCreateFolders(true); 
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $uploader->save($path,$fname);
        
                switch(pathinfo($fname, PATHINFO_EXTENSION))
                {
                    case 'php':
                        copy($path.DS.$fname, $path.DS.'ait_install'.DS.$this->getLicense()->getPlatform()->getPlatformId().DS.$fname);
                    break;
                    
                    case 'sql':
                    default:
                        $sql = file_get_contents($path.DS.$fname);
                        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
                        $writeConnection->query($sql);
                    break;
                }
                unlink($path.DS.$fname);
                $this->getModule()->updateStatuses();
                if(!$this->getLicense()->isUninstalled())
                {
                    if($turnOnModule)
                    {
                        $data = array();
                        foreach ($this->tool()->platform()->getModuleKeysForced() as $module => $value)
                        {
                            /* @var $module Aitoc_Aitsys_Model_Module */
                            $isCurrent = $module === $this->getModule()->getKey();
                            $data[$module] = $isCurrent ? true : $value;
                        }
                        
                        $aitsysModel = new Aitoc_Aitsys_Model_Aitsys();
                        $errors = $aitsysModel->saveData($data,array(),true);
                        if($errors)
                        {
                            foreach($errors as $error)
                            {
                                Mage::getSingleton('adminhtml/session')->addError($this->__($error));
                            }
                        }
                    }
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('License of %s module has been installed.',$this->getModule()->getLabel()));
                }
                else
                {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Unknown error. Please retry the operation again. If installation fails, contact support department.'));
                }
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        else
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('No file uploaded.'));
        }
        $this->_redirect('*');
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