<?php
/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    GPL
 */

class J2t_Ajaxcheckout_Model_Templaterounded
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'rounded', 'label'=>Mage::helper('j2tajaxcheckout')->__('Rounded theme (not compatible IE6.5)'))
        );
    }

    public function getCssName()
    {
        return 'ajax_cart_template_rounded.css';
    }

    public function getWH()
    {
        return 20;
    }

}
