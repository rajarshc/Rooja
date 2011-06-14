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
class AW_Rma_Block_Adminhtml_Status_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    public function __construct() {
        parent::__construct();
        $this->setId('awrmaStatusForm');
    }

    protected function _prepareForm() {
        $data = Mage::getSingleton('adminhtml/session')->getAWRMAFormData() ? Mage::getSingleton('adminhtml/session')->getAWRMAFormData(TRUE) : Mage::registry('awrmaformdatatype');
        if(!is_object($data))
            $data = new Varien_Object($data);

        $_form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('awrma/adminhtml_status/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post'
        ));

        $_fieldset = $_form->addFieldset('type_fieldset', array(
            'legend' => $this->__('Status Information')
        ));

        $_fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => $this->__('Name'),
            'required' => TRUE
        ));

        $_fieldset->addField('resolve', 'select', array(
            'name' => 'resolve',
            'label' => $this->__('Resolve RMA after obtaining status'),
            'required' => TRUE,
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

        if(!$data || !in_array($data->getId(), Mage::helper('awrma/status')->getUneditedStatus())) {
            if (!Mage::app()->isSingleStoreMode()) {
                $_fieldset->addField('store', 'multiselect', array(
                    'name'      => 'store[]',
                    'label'     => $this->__('Store View'),
                    'required'  => TRUE,
                    'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(FALSE, TRUE),
                ));
            } else {
                if(isset($data['store'])) {
                    if (is_array($data['store'])) {
                        if (isset($data['store'][0])) $data['store'] = $data['store'][0];
                        else $data['store'] = '';
                    }
                }

                $_fieldset->addField('store', 'hidden', array(
                    'name'      => 'store[]',
                    'value'     => Mage::app()->getStore(TRUE)->getId(),
                ));
            }
        }

        $_fieldset->addField('sort', 'text', array(
            'name' => 'sort',
            'label' => Mage::helper('awrma')->__('Sort Order'),
            'required' => TRUE
        ));

        $_fieldset->addField('to_customer', 'textarea', array(
            'name' => 'to_customer',
            'label' => $this->__('Notification sent to customer (leave blank not to send)')
        ));

        $_fieldset->addField('to_admin', 'textarea', array(
            'name' => 'to_admin',
            'label' => $this->__('Notification sent to administrator (leave blank not to send)')
        ));

        $_fieldset->addField('to_chatbox', 'textarea', array(
            'name' => 'to_chatbox',
            'label' => $this->__('Notification sent to messages history (leave blank not to send)')
        ));

        $_form->setValues($data);
        $_form->setUseContainer(TRUE);
        $this->setForm($_form);

        return parent::_prepareForm();
    }
}
