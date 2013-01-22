<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 01/11/12
 * Package:     AdjustWare_Cartalert_3.1.1_0.2.3_440060
 * Purchase ID: NZmnTZChS7OANNEKozm6XF7MkbUHNw6IY9fsWFBWRT
 * Generated:   2013-01-22 11:08:03
 * File path:   app/code/local/AdjustWare/Cartalert/Block/Adminhtml/Dailystat/Filter.php
 * Copyright:   (c) 2013 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ CmBBpwgrcMwZcCqh('0a53b4ad92946e8574579a0cbe10fbce'); ?><?php 
class AdjustWare_Cartalert_Block_Adminhtml_Dailystat_Filter extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/index');
        $form = new Varien_Data_Form(
            array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get')
        );
        $htmlIdPrefix = 'abandone_cart_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('reports')->__('Filter')));

        $dateFormatIso = '%Y-%m-%d';//Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('period_type', 'select', array(
            'name' => 'period_type',
            'options' => array(
                'day'   => Mage::helper('adjcartalert')->__('Day'),
                'month' => Mage::helper('adjcartalert')->__('Month'),
                'year'  => Mage::helper('adjcartalert')->__('Year')
            ),
            'label' => Mage::helper('adjcartalert')->__('Period'),
            'title' => Mage::helper('adjcartalert')->__('Period')
        ));

        $fieldset->addField('from', 'date', array(
            'name'      => 'from',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('adjcartalert')->__('From'),
            'title'     => Mage::helper('adjcartalert')->__('From'),
            'required'  => true
        ));

        $fieldset->addField('to', 'date', array(
            'name'      => 'to',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('adjcartalert')->__('To'),
            'title'     => Mage::helper('adjcartalert')->__('To'),
            'required'  => true
        ));

        $fieldset->addField('submit', 'submit', array(
            'name'      => 'submit',
            
            'title'     => Mage::helper('adjcartalert')->__('Show Report'),
            'value'     => Mage::helper('adjcartalert')->__('Show Report'),
            'class'     => 'form-button',
        ));        
        
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    
} } 