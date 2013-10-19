<?php

class Magestore_Affiliateplus_Block_Adminhtml_Payment_Review_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_blockGroup = 'affiliateplus';
        $this->_controller = 'adminhtml_payment_review';
		
        $this->_removeButton('reset');
        $this->_removeButton('delete');
        
        $this->_updateButton('back', 'onclick', 'backToEdit()');
        $this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('Pay'));
        
        $backUrl = $this->getUrl('*/*/cancelReview', array(
            'id' => $this->getRequest()->getParam('id'),
            'store' => $this->getRequest()->getParam('store')
        ));
        $this->_formInitScripts[] = "
            function backToEdit(){
                editForm.submit('$backUrl');
            }
        ";
    }

    public function getHeaderText()
    {
        return Mage::helper('affiliateplus')->__('Review your payment and pay');
    }
}