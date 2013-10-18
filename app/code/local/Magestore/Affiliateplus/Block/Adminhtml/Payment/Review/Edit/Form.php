<?php

class Magestore_Affiliateplus_Block_Adminhtml_Payment_Review_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        
        $fieldset = $form->addFieldset('review_fieldset', array('legend' => Mage::helper('adminhtml')->__('Review your payment and pay')));
        $data = $this->getRequest()->getPost();
        
        $paymentMethod = Mage::getModel('affiliateplus/payment');
        if ($paymentId = $this->getRequest()->getParam('id')) {
            $paymentMethod->load($paymentId);
        }
        $paymentMethod = $paymentMethod->addData($data)
            ->getPayment();
        foreach ($data as $key => $value) {
            if ($key == 'form_key') {
                continue;
            }
            if (strpos($key, $paymentMethod->getPaymentCode()) === 0) {
                $paymentMethod->setData(str_replace($paymentMethod->getPaymentCode().'_', '', $key), $value);
            }
            $fieldset->addField($key,'hidden',array('name' => $key));
        }
        
        $fieldset->addField('show_account_email', 'note', array(
            'label' => Mage::helper('affiliateplus')->__('To Account'),
            'text'  => $data['account_email']
        ));
        
        $fieldset->addField('show_amount', 'note', array(
            'label' => Mage::helper('affiliateplus')->__('Amount To Transfer'),
            'text'  => Mage::app()->getStore()->getBaseCurrency()->format($data['amount'])
        ));
        
        if ($this->getRequest()->getParam('masspayout') == 'true') {
            $data['fee'] = $paymentMethod->getEstimateFee(
                $data['amount'],
                Mage::getStoreConfig('affiliateplus/payment/who_pay_fees', $this->getRequest()->getParam('store'))
            );
        }
        
        $fieldset->addField('show_fee', 'note', array(
            'label' => Mage::helper('affiliateplus')->__('Estimated Fee'),
            'text'  => Mage::app()->getStore()->getBaseCurrency()->format($data['fee'])
        ));
        
        $fieldset->addField('payment_info', 'note', array(
            'label' => Mage::helper('affiliateplus')->__('Payment Info'),
            'text'  => $paymentMethod->getInfoHtml()
        ));
        
        $form->setValues($data);
        $form->setAction($this->getUrl('*/*/savePayment', array(
            'id'    => $this->getRequest()->getParam('id'),
            'masspayout'    => $this->getRequest()->getParam('masspayout'),
            'store' => $this->getRequest()->getParam('store')
        )));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
