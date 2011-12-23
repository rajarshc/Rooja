<?php
/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    GPL
 */

class J2t_Ajaxcheckout_Block_J2thead extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $model_name = Mage::getStoreConfig('j2tajaxcheckout/default/j2t_ajax_cart_template', Mage::app()->getStore()->getId());

        if ($model_name != 'default'){
            $temp = Mage::getModel("j2tajaxcheckout/template$model_name");
            $this->getLayout()->getBlock('head')->addItem('skin_css','css/j2t/'.$temp->getCssName());
        }
    }

}
