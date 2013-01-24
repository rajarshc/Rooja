<?php

class Aitoc_Aitsys_Model_Core_Layout extends Mage_Core_Model_Layout
{
    
    protected function _getBlockInstance($block, array $attributes=array())
    {
        if (version_compare(Mage::getVersion(),'1.3.1','>'))
        {
            return parent::_getBlockInstance($block,$attributes);
        }
        if (is_string($block)) {
            if (strpos($block, '/')!==false) {
                if (!$block = Mage::getConfig()->getBlockClassName($block)) {
                    Mage::throwException(Mage::helper('core')->__('Invalid block type: %s', $block));
                }
            }
            $fileName = mageFindClassFile($block);
            if ($fileName!==false) {
                if (!class_exists($block,false))
                {
                    include_once ($fileName);
                }
                $block = new $block($attributes);
            }
        }
        if (!$block instanceof Mage_Core_Block_Abstract) {
            Mage::throwException(Mage::helper('core')->__('Invalid block type: %s', $block));
        }
        return $block;
    }
    
}