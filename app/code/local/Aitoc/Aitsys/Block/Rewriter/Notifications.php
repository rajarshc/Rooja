<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Block_Rewriter_Notifications extends Aitoc_Aitsys_Abstract_Adminhtml_Block
{
    public function isShow()
    {
        $aitsysCache = Mage::app()->useCache('aitsys');
        if(!$aitsysCache && in_array(1, Mage::app()->useCache()))
        {
            return true;
        }
    }
    
    /**
     * Get cache management url
     *
     * @return string
     */
    public function getManageUrl()
    {
        return $this->getUrl('adminhtml/cache');
    }
    
    /**
     * ACL validation before html generation
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::getSingleton('admin/session')->isAllowed('system/cache')) {
            return parent::_toHtml();
        }
        return '';
    }
}
