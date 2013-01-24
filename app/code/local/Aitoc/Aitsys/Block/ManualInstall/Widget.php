<?php

class Aitoc_Aitsys_Block_ManualInstall_Widget extends Mage_Adminhtml_Block_Widget_Form_Container
implements Aitoc_Aitsys_Abstract_Model_Interface
{
    
    protected $_controller = 'license';
    
    public function __construct()
    {
        Mage_Adminhtml_Block_Widget_Container::__construct();
        if (!$this->hasData('template')) 
        {
            $this->setTemplate('aitsys/manualInstall/widget.phtml');
        }
        
        $helper = $this->getLicenseHelper()->getAdminhtmlHelper();
        $this->_headerText = $this->__('Manual License Install for %s', $this->getModule()->getLabel());
        
        if(true == Mage::app()->getRequest()->getParam('confirmed'))
        {
            $this->_addButton('back',array(
                        'label'     => $this->__('Cancel'),
                        'onclick'   => 'setLocation(\''.$helper->getUrl('aitsys').'\')',
                        'class'     => 'back'
                    ));        

            $url = $helper->getUrl('aitsys/license/manualInstallUpload',array('modulekey' => $this->getModule()->getKey()));                
            $this->_addButton('install',array(
                        'label' => $this->__('Proceed to install') ,
                        'id' => 'proceed_to_install' ,
                        'onclick'   => 'editForm.submit(\''.$url.'\');'
                    ));
        }
        else
        {
            $this->_addButton('back',array(
                        'label'     => $this->__('Do not Agree and Cancel'),
                        'onclick'   => 'setLocation(\''.$helper->getUrl('aitsys').'\')',
                        'class'     => 'back'
                    ));

            $url = $helper->getUrl('aitsys/license/manualInstall',array('modulekey' => $this->getModule()->getKey(), 'confirmed' => true));                
            $this->_addButton('install',array(
                        'label' => $this->__('Confirm agreement and install') ,
                        'id' => 'proceed_to_install' ,
                        'onclick'   => 'setLocation(\''.$url.'\')',
                    ));
        }
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function tool()
    {
        return Aitoc_Aitsys_Abstract_Service::get();
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
     * @return Aitoc_Aitsys_Helper_License
     */
    public function getLicenseHelper()
    {
        return $this->tool()->getLicenseHelper();
    }
    
    protected function _prepareLayout()
    {
        $block = $this->getLayout()->createBlock('aitsys/manualInstall_form');
        $this->setChild('form',$block);
        return Mage_Adminhtml_Block_Widget_Container::_prepareLayout();
    }
    
}