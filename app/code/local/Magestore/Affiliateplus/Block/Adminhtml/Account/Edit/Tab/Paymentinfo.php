<?php

class Magestore_Affiliateplus_Block_Adminhtml_Account_Edit_Tab_Paymentinfo extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $data = array();
        if (Mage::getSingleton('adminhtml/session')->getAccountData()) {
            $data = Mage::getSingleton('adminhtml/session')->getAccountData();
            Mage::getSingleton('adminhtml/session')->setAccountData(null);
        } elseif (Mage::registry('account_data')) {
            $data = Mage::registry('account_data')->getData();
        }
        
        $storeId = $this->getRequest()->getParam('store');
        if ($storeId) {
            $store = Mage::getModel('core/store')->load($storeId);
        } else {
            $store = Mage::app()->getStore();
        }
        
        $fieldset = $form->addFieldset('paymentinfo_form', array('legend' => Mage::helper('affiliateplus')->__('Payment Information')));
        
        if (Mage::helper('affiliateplus/config')->getSharingConfig('required_paypal')) {
            $fieldset->addField('paypal_email', 'text', array(
                'label' => Mage::helper('affiliateplus')->__('Paypal Email'),
                'class' => 'required-entry validate-email',
                'required' => true,
                'name' => 'paypal_email',
            ));
        } else {
            $fieldset->addField('paypal_email', 'text', array(
                'label' => Mage::helper('affiliateplus')->__('Paypal Email'),
                'class' => 'validate-email',
                'name' => 'paypal_email',
            ));
        }
        
        //event to add more tab
        Mage::dispatchEvent('affiliateplus_adminhtml_add_account_fieldset', array('form' => $form, 'fieldset' => $fieldset, 'load_data' => $data));
        
        if ($data && isset($data['customer_id']) && $data['customer_id']) {
            $clickReport = Mage::getResourceModel('affiliateplus/action_collection')
                ->addFieldToFilter('type', 2)
                ->addFieldToFilter('account_id', $this->getRequest()->getParam('id'));
            $clickReport->getSelect()
                ->columns(array(
                    'total_clicks' => 'SUM(totals)',
                    'unique_clicks' => 'SUM(is_unique)'
                ))->group('account_id');
            $clicks = $clickReport->getFirstItem();
            if ($clicks && $clicks->getId()) {
                $data['total_clicks'] = $clicks->getData('total_clicks');
                $data['unique_clicks'] = $clicks->getData('unique_clicks');
            }
            $fieldset->addField('total_clicks', 'label', array(
                'label' => Mage::helper('affiliateplus')->__('Total Clicks'),
                'bold' => true,
            ));

            $fieldset->addField('unique_clicks', 'label', array(
                'label' => Mage::helper('affiliateplus')->__('Unique Clicks'),
                'bold' => true,
            ));

            if (!isset($data['balance']))
                $data['balance'] = 0;
            $fieldset->addField('balance', 'note', array(
                'label' => Mage::helper('affiliateplus')->__('Balance'),
                'text' => '<strong>' . $store->convertPrice($data['balance'], true, true) . '</strong>',
            ));

            if (!isset($data['total_commission_received']))
                $data['total_commission_received'] = 0;
            $fieldset->addField('total_commission_received', 'note', array(
                'label' => Mage::helper('affiliateplus')->__('Total Commissions Received'),
                'text' => '<strong>' . $store->convertPrice($data['total_commission_received'], true, true) . '</strong>',
            ));

            if (!isset($data['total_paid']))
                $data['total_paid'] = 0;
            $fieldset->addField('total_paid', 'note', array(
                'label' => Mage::helper('affiliateplus')->__('Total Paid'),
                'text' => '<strong>' . $store->convertPrice($data['total_paid'], true, true) . '</strong>',
            ));
            $form->addValues($data);
        }
        
        return parent::_prepareForm();
    }
}
