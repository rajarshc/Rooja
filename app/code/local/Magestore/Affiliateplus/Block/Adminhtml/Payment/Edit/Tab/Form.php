<?php

class Magestore_Affiliateplus_Block_Adminhtml_Payment_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * get Affiliate Payment Helper
     *
     * @return Magestore_Affiliateplus_Helper_Payment
     */
    protected function _getPaymentHelper() {
        return Mage::helper('affiliateplus/payment');
    }

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('payment_data', array('legend' => Mage::helper('affiliateplus')->__('Withdrawal information')/* , 'class' => 'fieldset-wide' */));


        $data = array();
        if (Mage::getSingleton('adminhtml/session')->getPaymentData()) {
            $data = Mage::getSingleton('adminhtml/session')->getPaymentData();
            if (!isset($data['payment_method']) || !$data['payment_method']) {
                $data['payment_method'] = 'paypal';
            }
            $data['temp_payment_method'] = $data['payment_method'];
            $data['affiliate_account'] = $data['account_email'];
            // $form->setFormValues($data);
            Mage::getSingleton('adminhtml/session')->setPaymentData(null);
        } elseif (Mage::registry('payment_data')) {
            $data = Mage::registry('payment_data')->getData();
            if (!isset($data['payment_method']) || !$data['payment_method']) {
                $data['payment_method'] = 'paypal';
            }
            $data['temp_payment_method'] = $data['payment_method'];
            $data['affiliate_account'] = $data['account_email'];
            // $form->setFormValues($data);
        }
        if (!isset($data['payment_method']) || !$data['payment_method']) {
            $data['payment_method'] = 'paypal';
        }
        $form->setFormValues($data);


        $fieldset->addField('account_id', 'hidden', array(
            'name' => 'account_id',
        ));

        $fieldset->addField('account_email', 'hidden', array(
            'name' => 'account_email',
        ));
//		
//		$fieldset->addField('account_name', 'text', array(
//			'label'     => Mage::helper('affiliateplus')->__('Affiliate Name'),
//			'class'     => 'required-entry',
//			'required'  => true,
//			'name'      => 'account_name',
//			'readonly'  => 'readonly',
//		));

        $fieldset->addField('affiliate_account', 'link', array(
            'label' => Mage::helper('affiliateplus')->__('Affiliate Account'),
            'href' => $this->getUrl('*/adminhtml_account/edit', array('_current' => true, 'id' => $data['account_id'])),
            'title' => Mage::helper('affiliateplus')->__('View Affiliate Account Detail'),
        ));
        $fieldset->addField('account_name', 'hidden', array(
            'name' => 'account_name',
        ));
        $storeId = $this->getRequest()->getParam('store');
        if (isset($data['account_balance'])) {
            $fieldset->addField('account_balance', 'note', array(
                'label' => Mage::helper('affiliateplus')->__('Balance'),
                'text' => Mage::app()->getStore($storeId)->getBaseCurrency()->format($data['account_balance'])
            ));
        }

        $whoPayFees = Mage::getStoreConfig('affiliateplus/payment/who_pay_fees', $storeId);
        if ($whoPayFees == 'payer')
            $note = Mage::helper('affiliateplus')->__('Not including fee');
        else
            $note = Mage::helper('affiliateplus')->__('Including fee');
        $js='';
        if (isset($data['account_balance'])) {
            $js .='<script type="text/javascript">
                var request_amount_max ='.$data['account_balance'].';
                function checkAmountBalance(el){
                    el.value = parseFloat(el.value);
                    if (el.value < 0) el.value = 0;
                    else if (el.value > request_amount_max || el.value == \'NaN\') el.value = request_amount_max;
                }
                </script>';
        }
        $params = array(
            'label' => Mage::helper('affiliateplus')->__('Amount'),
            'name' => 'amount',
            'class' => 'required-entry',
            'required' => true,
            'onchange' =>'checkAmountBalance(this)',
            'note' => $note,
            'after_element_html' => $js,
        );
        if (isset($data['payment_method']) && $data['payment_method'] == 'credit') {
            unset($params['note']);
        }

        if ($this->getRequest()->getParam('id'))
            $params['readonly'] = 'readonly';


        if (isset($data['tax_amount']) && $data['tax_amount']) {
            $taxParams = $params;
            unset($taxParams['after_element_html']);
            if (isset($taxParams['note'])) unset($taxParams['note']);
            
            $taxParams['name'] = 'amount_incl_tax';
            $fieldset->addField('amount_incl_tax', 'text', $taxParams);
            
            $taxParams['name'] = 'tax_amount';
            $taxParams['label'] = Mage::helper('affiliateplus')->__('Tax');
            $fieldset->addField('tax_amount', 'text', $taxParams);
            
            $params['label'] = Mage::helper('affiliateplus')->__('Amount (Excl. Tax)');
        }
        if (isset($data['affiliateplus_account']) && $data['affiliateplus_account']) {
            $rate = Mage::helper('affiliateplus/payment_tax')->getTaxRate($data['affiliateplus_account']);
            if ($rate > 0) {
                $taxParams = $params;
                
                $taxParams['name'] = 'amount_incl_tax';
                $taxParams['note'] = Mage::helper('affiliateplus')->__('Including %s tax', round($rate, 2) . '%');
                $taxParams['after_element_html'] = '
                    <script type="text/javascript">
                        var request_amount_max ='.$data['account_balance'].';
                        function checkAmountBalance(el){
                            el.value = parseFloat(el.value);
                            if (el.value < 0) el.value = 0;
                            else if (el.value > request_amount_max || el.value == \'NaN\') el.value = request_amount_max;
                            var taxRate = '.$rate.';
                            var taxAmount = el.value * taxRate / (100 + taxRate);
                            taxAmount = Math.round(taxAmount * 100) / 100;
                            $(\'amount\').value = el.value - taxAmount;
                        }
                        function changeRealAmount(el) {
                            var taxRate = '.$rate.';
                            var maxRequestAmount = request_amount_max * taxRate / (100 + taxRate);
                            maxRequestAmount = Math.round(maxRequestAmount * 100) / 100;
                            
                            el.value = parseFloat(el.value);
                            if (el.value < 0) el.value = 0;
                            else if (el.value > maxRequestAmount || el.value == \'NaN\') el.value = maxRequestAmount;
                            
                            var taxAmount = el.value * taxRate / 100;
                            var totalAmount = parseFloat(el.value) + parseFloat(taxAmount);
                            totalAmount = Math.round(totalAmount * 100) / 100;
                            $(\'amount_incl_tax\').value = totalAmount;
                        }
                    </script>
                ';
                $fieldset->addField('amount_incl_tax', 'text', $taxParams);
                $params['label'] = Mage::helper('affiliateplus')->__('Amount (Excl. Tax)');
                $params['onchange'] = 'changeRealAmount(this)';
                unset($params['after_element_html']);
            }
        }
        $fieldset->addField('amount', 'text', $params);

//		$methodPaypalPayment = Mage::getStoreConfig('affiliateplus/payment/payment_method');
//		if($methodPaypalPayment != 'api' || $data['status'] == 3){ // 3 -> completed
//        $params = array(
//            'label' => Mage::helper('affiliateplus')->__('Fee'),
//            'name' => 'fee',
//						'class'     => 'required-entry',
//						'required'  => true,
//        );
//        if ($data['status'] >= 3)
//            $params['disabled'] = true;

//        $fieldset->addField('fee', 'text', $params);
//		}

        $paymentMethods = $this->_getPaymentHelper()->getAvailablePayment();

        if (isset($data['payment_method']) && $data['payment_method'] == 'credit') {
            $fieldset->addField('credit_refund_amount', 'text', array(
                'name'  => 'credit_refund_amount',
                'label' => Mage::helper('affiliateplus')->__('Refunded'),
                'readonly'  => true,
            ));
            $fieldset->addField('order_increment_id', 'note', array(
                'label' => Mage::helper('affiliateplus')->__('Pay for Order'),
                'text'  => '<a title="'.Mage::helper('affiliateplus')->__('View Order')
                        . '" href="' . Mage::getUrl('adminhtml/sales_order/view', array('order_id' => $data['credit_order_id']))
                        . '">#'
                        . $data['credit_order_increment_id'] . '</a>'
            ));
        } else if (!$this->_isActivePaymentPlugin()) {
            $fieldset->addField('payment_method', 'hidden', array(
                'name' => 'payment_method',
                'value' => 'paypal',
            ));
            $feeParams = array(
                'label' => Mage::helper('affiliateplus')->__('Fee'),
                'name' => 'fee',
            );
            if (isset($data['status']) && $data['status'] >= 3) {
                $feeParams['disabled'] = true;
            }
            $fieldset->addField('fee', 'text', $feeParams);
            
            $fieldset->addField('paypal_email', 'text', array(
                'label' => Mage::helper('affiliateplus')->__('Paypal Email'),
                'name' => 'paypal_email',
                'readonly' => 'readonly',
                'class' => 'required-entry',
                'required' => true,
            ));
            
            if ($data['status'] < 3) {
                $fieldset->addField('pay_now', 'note', array(
                    'text' => '<button type="button" class="scalable save" onclick="saveAndPayNow()"><span>' . Mage::helper('affiliateplus')->__('Pay Now') . '</span></button>',
                    'note' => Mage::helper('affiliateplus')->__('Automatically pay out for Affiliate through the paygate')
                ));
            }

            if ($data['transaction_id'] || $data['status'] < 3) {
                $fieldset->addField('transaction_id', 'text', array(
                    'label' => Mage::helper('affiliateplus')->__('Transaction ID'),
                    'name' => 'transaction_id',
//					'class'     => 'required-entry',
//					'required'  => true,
                ));
            }
        } else {
            $params = array(
                'label' => Mage::helper('affiliateplus')->__('Payment Method'),
                'name' => 'payment_method',
                'required' => true,
                'values' => $this->_getPaymentHelper()->getPaymentOption(),
                'onclick' => 'changePaymentMethod(this);',
            );

            if ($data['status'] >= 3 || $data['is_request'] == 1) {
                $params['disabled'] = true;
                $fieldset->addField('temp_payment_method', 'select', $params);

                $fieldset->addField('payment_method', 'hidden', array(
                    'name' => 'payment_method',
                ));
            }else{
                if ($this->getRequest()->getParam('id')) {
                    $params['disabled'] = true;
                }
                $fieldset->addField('payment_method', 'select', $params);
            }

            if ($data['status'] < 3) {
                $fieldset->addField('pay_now', 'note', array(
                    'text' => '<button type="button" class="scalable save" onclick="saveAndPayNow()"><span>' . Mage::helper('affiliateplus')->__('Pay Now') . '</span></button>',
                    'note' => Mage::helper('affiliateplus')->__('Automatic Payout for Affiliate through Paygate')
                ));
            }
            
            $feeParams = array(
                'label' => Mage::helper('affiliateplus')->__('Fee'),
                'name' => 'fee',
            );
            if (isset($data['status']) && $data['status'] >= 3) {
                $feeParams['disabled'] = true;
            }
            $fieldset->addField('fee', 'text', $feeParams);

            $form->addFieldset('payment_method_data', array('legend' => Mage::helper('affiliateplus')->__('Payment Method Information')));

            foreach ($paymentMethods as $code => $paymentMethod) {
                $paymentFieldset = $form->addFieldset("payment_fieldset_$code");
                
                Mage::dispatchEvent("affiliateplus_adminhtml_payment_method_form_$code", array(
                    'form' => $form,
                    'fieldset' => $paymentFieldset,
                ));
                if ($code == 'paypal') {
                    $readOnly = (isset($data['status']) && $data['status'] >= 3);
                    $paymentFieldset->addField('paypal_email', 'text', array(
                        'label' => Mage::helper('affiliateplus')->__('Paypal Email'),
                        'name' => 'paypal_email',
                        'readonly' => 'readonly',
                        'class' => 'required-entry',
                        'required' => true,
                        'note'  => $readOnly ? null : Mage::helper('affiliateplus')->__('You can change this email on the acount edit page'),
                    ));

//					if($methodPaypalPayment != 'api'){
                    $params = array(
                        'label' => Mage::helper('affiliateplus')->__('Transaction ID'),
                        'name' => 'transaction_id',
//							'class'     => 'required-entry',
//							'required'  => true,
                    );
                    if ($readOnly)
                        $params['readonly'] = 'readonly';
                    if (!$readOnly || (isset($data['paypal_transaction_id']) && $data['paypal_transaction_id'])) {
                        $paymentFieldset->addField('paypal_transaction_id', 'text', $params);
                    }
                }
            }
            $fieldset->addField('javascript', 'hidden', array(
                'after_element_html' => '
					<script type="text/javascript">
						function changePaymentMethod(el){
							var payment_fieldset = "payment_fieldset_" + el.value;
							$$("div.fieldset").each(function(e){
								if (e.id.startsWith("payment_fieldset_")){
									e.hide();
									var i = 0;
									while(e.down(".required-entry",i) != undefined)
										e.down(".required-entry",i++).disabled = true;
								}if (e.id == payment_fieldset){
									var i = 0;
									while(e.down(".required-entry",i) != undefined)
										e.down(".required-entry",i++).disabled = false;
									e.show();
								}
							});
                            if (el.value == "paypal" || el.value == "moneybooker") {
                                $("pay_now").parentNode.parentNode.show();
                            } else {
                                $("pay_now").parentNode.parentNode.hide();
                            }
						}
						document.observe("dom:loaded",function(){
							if ($("payment_method_data")) $("payment_method_data").hide();
							changePaymentMethod($("payment_method"));
						});
					</script>
					',
            ));
        }

        //event to add more field
        Mage::dispatchEvent('affiliateplus_adminhtml_add_field_payment_form', array('fieldset' => $fieldset, 'form' => $form));
        /*
          $params = array(
          'label' => Mage::helper('affiliateplus')->__('Status'),
          'name' => 'status',
          'values' => array(
          array('value' => 3, 'label' => Mage::helper('affiliateplus')->__('Completed')),
          array('value' => 1, 'label' => Mage::helper('affiliateplus')->__('Waiting')),
          array('value' => 2, 'label' => Mage::helper('affiliateplus')->__('Processing')),
          array('value' => 4, 'label' => Mage::helper('affiliateplus')->__('Canceled')),
          ));
         */
        $status = array(
            '1' => Mage::helper('affiliateplus')->__('Pending'),
            '2' => Mage::helper('affiliateplus')->__('Processing'),
            '3' => Mage::helper('affiliateplus')->__('Completed'),
            '4' => Mage::helper('affiliateplus')->__('Canceled')
        );
//        if (/* ($methodPaypalPayment == 'api' && $data['payment_method'] == 'paypal') || */$data['status'] >= 3 /* completed */)
//            $params['disabled'] = true;

        $id = $this->getRequest()->getParam('id');

        if ($id) {
//			$fieldset->addField('status', 'select', $params);
            $fieldset->addField('status_note', 'note', array(
                'label' => Mage::helper('affiliateplus')->__('Status'),
                'text' => '<strong>' . $status[$data['status']] . '</strong>'
            ));
            $fieldset->addField('status', 'hidden', array(
                'name' => 'status',
            ));
            $fieldset->addField('request_time', 'note', array(
                'label' => Mage::helper('affiliateplus')->__('Requested time'),
                'name' => 'request_time',
                'text' => $this->formatDate($data['request_time'], 'medium')
            ));
        }

        //$form->removeField('payment_data');

        $form->setValues($form->getFormValues());

        return parent::_prepareForm();
    }

    protected function _isActivePaymentPlugin() {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array) $modules;
        if (is_object($modulesArray['Magestore_Affiliatepluspayment']))
            return $modulesArray['Magestore_Affiliatepluspayment']->is('active');
        return false;
    }

}