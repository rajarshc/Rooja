<?php

class Aitoc_Aitsys_Block_Manage_Widget extends Mage_Adminhtml_Block_Widget_Form_Container
implements Aitoc_Aitsys_Abstract_Model_Interface
{
    
    protected $_controller = 'license';
    
    public function __construct()
    {
        Mage_Adminhtml_Block_Widget_Container::__construct();
        if (!$this->hasData('template')) 
        {
            $this->setTemplate('aitsys/manage/widget.phtml');
        }
        
        $helper = $this->getLicenseHelper();
        $license = $this->getLicense();
        $this->_headerText = $helper->getManageTitle($license);
        
        foreach ($helper->getManageActions($license,$this->getRequest()->getParams()) as $key => $action)
        {
            $this->_addButton($key,$action);
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
     * @return Aitoc_Aitsys_Model_Module_License
     */
    public function getLicense()
    {
        return $this->getModule()->getLicense();
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
        $block = $this->getLayout()->createBlock('aitsys/manage_form');
        $this->setChild('form',$block);
        return Mage_Adminhtml_Block_Widget_Container::_prepareLayout();
    }
    
}