<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitsys_Block_Edit extends Mage_Adminhtml_Block_Widget
{
    protected $_bAllowInstall = true;
    protected $_aErrorList = '';
    
    public function __construct()
    {
        parent::__construct();
        
        $aitsysModel = new Aitoc_Aitsys_Model_Aitsys(); 
        if ($this->_aErrorList = $aitsysModel->getAllowInstallErrors())
        {
            $this->_bAllowInstall = false;
        }
        
        $this->setTemplate('aitsys/edit.phtml');
        $this->setTitle('Aitoc Module Manager v%s');
    }
    
    public function getVersion()
    {
        return Aitoc_Aitsys_Model_Platform::getInstance()->getVersion();
    }
    
    public function getPermLink()
    {
        $mode = $this->tool()->filesystem()->getPermissionsMode();
        
        if($mode===Aitoc_Aitsys_Model_Aitfilesystem::MODE_ALL)
        {
            return Aitoc_Aitsys_Model_Aitfilesystem::MODE_NORMAL;
        }
        else 
        {
            return Aitoc_Aitsys_Model_Aitfilesystem::MODE_ALL;
        }
    }
    
    public function getPermLinkTitle()
    {
        $mode = $this->tool()->filesystem()->getPermissionsMode();

        if($mode===Aitoc_Aitsys_Model_Aitfilesystem::MODE_ALL)
        {
            return 'Grant restricted write permissions';
        }
        else 
        {
            return 'Grant full write permissions';
        }
    }

    protected function _prepareLayout()
    {
        if ($this->_bAllowInstall)
        {
            $this->setChild('save_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('adminhtml')->__('Save modules settings'),
                        'onclick'   => 'configForm.submit()',
                        'class' => 'save',
                    ))
            );
        }
        $this->setChild('aitoc_notifications',
            $this->getLayout()->createBlock('aitsys/notification')
        );
        return parent::_prepareLayout();
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }

    public function initForm()
    {
        if ($this->_bAllowInstall)
        {
            $this->setChild('form',
                $this->getLayout()->createBlock('aitsys/form')
                    ->initForm()
            );
        }    
        
        return $this;
    }    
    
    public function getInstallText()
    {
        if ($this->_bAllowInstall)
        {
            $sInstallText = '';
            $closedFolders = $this->tool()->filesystem()->checkMainPermissions();
            if(!empty($closedFolders))
            {
                $sInstallText = '<ul class="messages"><li class="notice-msg"><ul><li>'.
                Mage::helper('aitsys')->__('Before any action with AITOC Modules, please ensure the folders below (including their files and subfolders) have writable permissions for the web server user (for example, apache):').
                '<br /><b>'.
                join('<br />', $closedFolders).
                '</b></li></ul></li></ul>';
            }
            
        }    
        else 
        {
            $sInstallText = '<ul class="messages"><li class="error-msg"><ul><li>';
            
            if ($this->_aErrorList)
            {
                foreach ($this->_aErrorList as $sErrorMsg)
                {
                    $sInstallText .= $sErrorMsg . '<br>';
                }
            }
            
            $sInstallText .= '</li></ul></li></ul>';
        }
        
        return $sInstallText;
    }
    
    public function tool()
    {
        return Aitoc_Aitsys_Abstract_Service::get($this);
    }
}