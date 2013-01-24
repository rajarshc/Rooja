<?php

abstract class Aitoc_Aitsys_Model_Rewriter_Mage_Core_Block_Abstract extends Mage_Core_Block_Abstract
{
    protected function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);
        $transport = new Varien_Object(array('html' => $html));
        Mage::dispatchEvent('aitsys_block_abstract_to_html_after', array('block' => $this, 'transport'=>$transport));  
        return $transport->getHtml();
    }
}