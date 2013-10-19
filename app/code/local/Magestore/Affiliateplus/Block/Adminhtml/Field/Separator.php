<?php

class Magestore_Affiliateplus_Block_Adminhtml_Field_Separator extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * render config row
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();
        $html  = '<tr id="row_' . $id . '">'
                . '<td class="label" colspan="3">';
        $marginTop = $element->getComment() ? $element->getComment() : '0px';
        $html .= '<div style="margin-top: ' . $marginTop
                . '; font-weight: bold; border-bottom: 1px solid #dfdfdf;">';
        $html .= $element->getLabel();
        $html .= '</div></td></tr>';
        return $html;
    }
}
