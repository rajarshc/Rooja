<?php

class Magestore_Affiliateplus_Block_Adminhtml_Payment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'affiliateplus';
        $this->_controller = 'adminhtml_payment';
        $this->_removeButton('reset');
        $this->_removeButton('save');

        $data = Mage::registry('payment_data');

        if (isset($data['status']) && ($data['status'] == 3 || $data['status'] == 4)) { // completed payment
            $this->_removeButton('delete');
        } else {
            if (!$this->getRequest()->getParam('id')) {
                $this->_addButton('save_and_pay_manual', array(
                    'label' => Mage::helper('adminhtml')->__('Save and Pay Manual'),
                    'onclick'   => 'saveAndContinueEdit()',
                    'class'     => 'save',
                ), -200);
            } else {
                $this->_removeButton('delete');
            }

            if (isset($data['status']) && $data['status'] && $data['status'] < 3) {
                $this->_addButton('cancel', array(
                    'label' => Mage::helper('adminhtml')->__('Cancel'),
                    'onclick' => 'cancelPayment()',
                        ), -200);
                $this->_addButton('complete', array(
                    'label' => Mage::helper('adminhtml')->__('Complete Manual'),
                    'onclick' => 'completePayment()',
                    'class' => 'save'
                        ), -150);
            }

//            } else {
//                $this->_removeButton('save');
//                $this->_addButton('saveandpay', array(
//                    'label'     => Mage::helper('adminhtml')->__('Save and Pay Manual'),
//                    'onclick'   => 'saveAndPayManual()',
//                    'class'     => 'save',
//                ), -100);
//            }

            $reviewUrl = $this->getUrl('*/*/review', array(
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
                    ));

            $cancelurl = $this->getUrl('*/*/cancelPayment', array(
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
                    ));
            $confirmText = Mage::helper('adminhtml')->__('Are you sure?');

            $this->_formInitScripts[] = "
                function saveAndPayManual() {
                    editForm.submit('$reviewUrl');
                }
                function saveAndPayNow() {
                    editForm.submit('$reviewUrl'+'masspayout/true');
                }
                function cancelPayment() {
                    if (confirm('$confirmText')){
                        setLocation('$cancelurl');
                    }
                }
                function completePayment() {
                    if (confirm('$confirmText')){
                        $('status').value=3;
                        editForm.submit();
                    }
                }
                function saveAndContinueEdit(){
                    editForm.submit($('edit_form').action+'back/edit/');
                }
            ";
        }
        Mage::dispatchEvent('affiliateplus_adminhtml_payment_edit_form_action', array(
            'form'  => $this,
            'data'  => $data
        ));
        return;


        $methodPaypalPayment = Mage::getStoreConfig('affiliateplus/payment/payment_method');
        $alert = '';
        $paymentUrl = '';
        if ($data['status'] != 3) { //not complete
            if ($methodPaypalPayment == 'api' && $data['payment_method'] == 'paypal') { // pay by API paypal
                $paymentId = $this->getRequest()->getParam('id');
                $accountId = $this->getRequest()->getParam('account_id');
                $paymentUrl = $this->getUrl('affiliateplusadmin/adminhtml_payment/processpayment', array('id' => $paymentId, 'account_id' => $accountId));
                $alert = Mage::helper('affiliateplus')->__('Are you sure of paying out for this withdrawal?');

                $this->_addButton('payment', array(
                    'label' => Mage::helper('adminhtml')->__('Payout'),
                    'onclick' => 'payment()',
                        ), -200);

                $this->_removeButton('save');
                $this->_removeButton('delete');
            } else {// manual pay
                $this->_updateButton('save', 'label', Mage::helper('affiliateplus')->__('Save Withdrawal'));

                if ($data['is_request'])
                    $this->_removeButton('delete');
                else
                    $this->_updateButton('delete', 'label', Mage::helper('affiliateplus')->__('Delete Withdrawal'));

                $this->_addButton('saveandcontinue', array(
                    'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                    'onclick' => 'saveAndContinueEdit()',
                    'class' => 'save',
                        ), -100);
            }
        }else {
            $this->_removeButton('save');
            $this->_removeButton('delete');
        }


        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('affiliateplus_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'affiliateplus_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'affiliateplus_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
			
			function payment(){
				var validator = new Validation('edit_form');
				
				var form = $('edit_form');
				var amount = form['amount'];

    			if(!validator.validate()){
					return;
				}
				var answer = confirm('$alert');
				if (answer){
					location.href = '$paymentUrl'+ 'amount/' + Form.Element.getValue(amount) + '/';
				}
			}
        ";
    }

    public function getHeaderText() {
        $data = Mage::registry('payment_data');
//		$methodPaypalPayment = Mage::getStoreConfig('affiliateplus/payment/payment_method');

        if ($data['status'] == 3) {//complete
            return Mage::helper('affiliateplus')->__("View Withdrawal for '%s'", $this->htmlEscape(Mage::registry('payment_data')->getAccountName()));
//		}elseif($methodPaypalPayment == 'api' && $data['payment_method']){
//			return Mage::helper('affiliateplus')->__("Payout for '%s'", $this->htmlEscape(Mage::registry('payment_data')->getAccountName()));
        } elseif ($data && $data['payment_id']) {
            return Mage::helper('affiliateplus')->__("Edit Withdrawal for '%s'", $this->htmlEscape(Mage::registry('payment_data')->getAccountName()));
        } else {
            return Mage::helper('affiliateplus')->__("Add Withdrawal for '%s'", $this->htmlEscape(Mage::registry('payment_data')->getAccountName()));
        }
    }

}