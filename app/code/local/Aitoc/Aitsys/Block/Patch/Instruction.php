<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitsys_Block_Patch_Instruction extends Aitoc_Aitsys_Abstract_Adminhtml_Block
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('aitsys/patch/instruction.phtml');
        $this->setTitle('Aitoc Manual Patch Instructions');
    }
    
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getInstructionsHtml()
    {
        $html = '';
        $incompatibleList = Mage::getSingleton('adminhtml/session')->getData('aitsys_patch_incompatible_files');
        $currentMod       = Mage::app()->getRequest()->getParam('mod');
        if (!isset($incompatibleList[$currentMod]))
        {
            Mage::app()->getResponse()->setRedirect($this->getUrl('aitsys'));
        }
        foreach ($incompatibleList[$currentMod] as $patchFile)
        {
            $html .= '<h3>' . $this->__('File: ') . str_replace(Mage::getBaseDir('app'), '', $patchFile['file']) . '</h3>';
            $oneBlock = $this->getLayout()->createBlock('aitsys/patch_instruction_one');
            $oneBlock->setSourceFile($patchFile['file']);
            $oneBlock->setPatchFile($patchFile['patchfile']);
            $oneBlock->setExtensionPath($patchFile['mod']);
            $oneBlock->setExtensionName($currentMod);
            $html .= $oneBlock->toHtml();
        }
        
        return $html;
    }
    

}