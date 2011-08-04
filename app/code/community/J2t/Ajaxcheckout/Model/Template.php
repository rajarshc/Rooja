<?php
/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    GPL
 */

class J2t_Ajaxcheckout_Model_Template
{
    public function toOptionArray()
    {
        $return_value = array(
            array('value' => 'default', 'label'=>Mage::helper('j2tajaxcheckout')->__('Default theme'))
        );
        
        $directory = dirname(__FILE__);
        if ($handle = opendir($directory)) {
            while ($file = readdir($handle)) {
                if ($file != 'Template.php' && preg_match('/Template/i', $file)){
                    $model_name = strtolower(str_replace('.php', '', $file));
                    $temp = Mage::getModel("j2tajaxcheckout/$model_name")->toOptionArray();
                    foreach($temp as $temp_val){
                        $return_value[] = $temp_val;
                    }
                }                
            }
        }
        return $return_value;
    }
}
