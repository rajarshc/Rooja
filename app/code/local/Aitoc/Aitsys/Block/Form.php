<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Block_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('module_list', array(
            'legend' => Mage::helper('adminhtml')->__('Enable/Disable Module List')
        ));

        $aitsysModel = new Aitoc_Aitsys_Model_Aitsys(); 
        $aModuleList = $aitsysModel->getAitocModuleList();

        $elementRenderer = $this->getLayout()->createBlock('aitsys/form_element_renderer');
        /* @var $elementRenderer Aitoc_Aitsys_Block_Form_Element_Renderer */ 

        if ($aModuleList)
        {
            foreach ($aModuleList as $module) 
            {
                /* @var $module Aitoc_Aitsys_Model_Module */
                $aModule = $module;
                $label = $aModule['label'].($module->getVersion()?' v'.$module->getVersion():'');
                if ($aModule['access'] || !$module->isAvailable())
                {
                    if ( ($module->isAvailable() || 1 == $module->getValue()) && !$module->getErrorMessageType())
                    {
                        $fieldset->addField('hidden_enable_'.$aModule['key'], 'hidden', array(
                            'name'=>'enable['.$aModule['key'].']',
                            'value'=>0,
                        ));
                        
                        $fieldset->addField('enable_'.$aModule['key'], 'checkbox', array(
                            'name'=> ($module->isAvailable()?'enable':'ignore') . '['.$aModule['key'].']',
                            'label'=>$label,
                            'value'=>1,
                            'checked'=>$aModule['value'],
                            'module' => $module
                        ))->setRenderer($elementRenderer);
                    }
                    else
                    {
                        $sMessage = '';
                        $sNote = null;
                        if($module->getErrorMessageType()) {
                            $sNote = '<ul class="messages"><li class="notice-msg"><ul><li>' . Mage::helper('aitsys/strings')->getString( $module->getErrorMessageType() ).'</li></ul></li></ul>';
                        }
                        $fieldset->addField('ignore_'.$aModule['key'], 'note', array(
                            'name'=>'ignore['.$aModule['key'].']',
                            'label'=>$label,
                            'text'=> $sMessage,
                            'module' => $module,
                            'note'=>  $sNote
                        ))->setRenderer($elementRenderer);
                    }
                }
                else 
                {
                    if($this->tool()->platform()->isDemoMode())
                    {
                        $sMessage = "The extension is already enabled on this Demo Magento installation and can't be disabled for security reasons. Please proceed to the next step outlined in the extension's <a href='%s' target='_blank'>User Manual</a> to see how it works.";
                        $xml = simplexml_load_file(Mage::getBaseDir()."/aitmodules.xml");
                        $link = (string) $xml->modules->$aModule['key'];
                        if ($link == '')
                        {
                            $link = 'http://www.aitoc.com/';
                        }
                        $fieldset->addField('ignore_'.$aModule['key'], 'note', array(
                            'name'=>'ignore['.$aModule['key'].']',
                            'label'=>$label,
                            'note'=> '<ul class="messages"><li class="notice-msg"><ul><li>' . Mage::helper('adminhtml')->__($sMessage, $link) . '</li></ul></li></ul>'
                        ));
                    }
                    else
                    {
                        $sMessage = 'File does not have write permissions: %s';
                        $fieldset->addField('ignore_'.$aModule['key'], 'note', array(
                            'name'=>'ignore['.$aModule['key'].']',
                            'label'=>$label,
                            'none'=> '<ul class="messages"><li class="error-msg"><ul><li>' . Mage::helper('adminhtml')->__($sMessage, $aModule['file']) . '</li></ul></li></ul>'
                        ));
                    }
                }
            }
        }

        $this->setForm($form);

        return $this;
    }
    
    public function tool()
    {
        return Aitoc_Aitsys_Abstract_Service::get();
    }
    
}


