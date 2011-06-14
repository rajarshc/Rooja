<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Rma
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Rma_Block_Adminhtml_Rma_Edit_Tab_Customeraddress extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $rmaRequest = Mage::registry('awrmaformdatarma');
        if($rmaRequest->getPrintLabel())
            $formData = $rmaRequest->getPrintLabel();
        else
            $formData = Mage::helper('awrma/request')->getDefaultPrintLabelData($rmaRequest->getOrder());

        $contactInfo = $form->addFieldset('contactinformation', array(
            'legend' => $this->__('Contact Information')
        ));

        $contactInfo->addField('firstname', 'text', array(
            'name' => 'printlabel[firstname]',
            'label' => $this->__('First Name'),
            'required' => TRUE
        ));

        $contactInfo->addField('lastname', 'text', array(
            'name' => 'printlabel[lastname]',
            'label' => $this->__('Last Name'),
            'required' => TRUE
        ));

        $contactInfo->addField('company', 'text', array(
            'name' => 'printlabel[company]',
            'label' => $this->__('Company')
        ));

        $contactInfo->addField('telephone', 'text', array(
            'name' => 'printlabel[telephone]',
            'label' => $this->__('Telephone'),
            'required' => TRUE
        ));

        $contactInfo->addField('fax', 'text', array(
            'name' => 'printlabel[fax]',
            'label' => $this->__('Fax')
        ));

        $returnAddress = $form->addFieldset('return_address', array(
            'legend' => $this->__('Return Address')
        ));

        $returnAddress->addField('streetaddress', 'multiline', array(
            'name' => 'printlabel[streetaddress]',
            'label' => $this->__('Street Address'),
            'required' => TRUE
        ))->setLineCount(Mage::getStoreConfig('customer/address/street_lines', $this->getStoreId()));

        $returnAddress->addField('city', 'text', array(
            'name' => 'printlabel[city]',
            'label' => $this->__('City'),
            'required' => TRUE
        ));

        $returnAddress->addField('country_id', 'select', array(
            'name' => 'printlabel[country_id]',
            'label' => $this->__('Country'),
            'required' => TRUE
        ))->setValues(Mage::getSingleton('directory/country')->getResourceCollection()->loadByStore()->toOptionArray());

        $formData['region'] = isset($formData['stateprovince']) ? $formData['stateprovince'] : '';
        $formData['region_id'] = isset($formData['stateprovince_id']) ? $formData['stateprovince_id'] : null;

        $returnAddress->addField('region', 'text', array(
            'name' => 'printlabel[stateprovince]',
            'label' => $this->__('State/Province'),
            'required' => TRUE
        ))->setRenderer(
            $this->getLayout()->createBlock('adminhtml/customer_edit_renderer_region')
        );

        $returnAddress->addField('region_id', 'select', array(
            'name' => 'printlabel[stateprovince_id]',
            'label' => $this->__('State/Province'),
            'required' => TRUE
        ))->setNoDisplay(true);

        $returnAddress->addField('postcode', 'text', array(
            'name' => 'printlabel[postcode]',
            'label' => $this->__('Zip/Postal Code'),
            'required' => TRUE
        ));

        $additionalInfo = $form->addFieldset('addinfo', array(
            'legend' => $this->__('Additional Information')
        ));

        $formData['additionalinfo'] = isset($formData['additionalinfo']) ? $this->htmlEscape($formData['additionalinfo']) : '';
        
        $additionalInfo->addField('additionalinfo', 'textarea', array(
            'name' => 'printlabel[additionalinfo]'
        ));

        $form->setValues($formData);
    }
}
