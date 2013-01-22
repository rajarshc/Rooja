<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Block_ManualInstall_Form extends Mage_Adminhtml_Block_Widget_Form
implements Aitoc_Aitsys_Abstract_Model_Interface
{
	/**
     * Render block
     *
     * @return string
     */
    public function renderView()
    {
        return $this->getFormHtml();
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
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
   
        if(true == Mage::app()->getRequest()->getParam('confirmed'))
        {        
            $fieldset = $form->addFieldset('license_file_form', array('legend'=>$this->__('License file upload')));
            $fieldset->addField('license_file', 'file', array(
                'label'     => $this->__('License file'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'license_file',
            ));
        }
        else
        {
            $agreements = $this->getLicenseHelper()->getAgreements($this->getLicense());
            $fieldset = $form->addFieldset('agreements',array(
                'legend' => $this->__('Please read License Agreement and click the Confirm agreement and install button.') ,
                'class' => 'agreements'
            ));
            $fieldset->addField('license','note',array(
                'text' => '<div class="agreements_frame">'.$agreements.'</div>'
            ));
        }
                
        $form->addField('form_key','hidden',array(
            'name' => 'form_key' ,
            'value' => $this->getFormKey()
        ));
        return parent::_prepareForm();
    }
    
   
    /**
     * 
     * @return Aitoc_Aitsys_Helper_License
     */
    public function getLicenseHelper()
    {
        return $this->tool()->getLicenseHelper();
    }
    
}
