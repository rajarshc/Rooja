<?php

class Magestore_Affiliateplus_Model_Payment_Paypal extends Magestore_Affiliateplus_Model_Payment_Abstract {

    protected $_code = 'paypal';
    protected $_formBlockType = 'affiliateplus/payment_paypal';

    public function _construct() {
        parent::_construct();
        $this->_init('affiliateplus/payment_paypal');
    }

    public function calculateFee() {
        return $this->getPayment()->getFee();
    }

    public function getInfoString() {
        $info = Mage::helper('affiliateplus/payment')->__('
			Method: %s \n
			Email: %s \n'
            //Fee: %s \n
            //'Transaction Id: %s \n
		, $this->getLabel()
        , $this->getEmail()
        //,$this->getFeePrice(false)
        //, $this->getTransactionId()
        );
        if ($this->getTransactionId()) {
            return $info . Mage::helper('affiliateplus/payment')->__('Transaction Id: %s \n', $this->getTransactionId());
        }
        return $info;
    }

    public function getInfoHtml() {
        /* Edit by blanka 28/11/2012 */
        if (!$this->getId()) {
            $payment = Mage::registry('confirm_payment_data');
            
            if ($payment){
                $this->setData($payment->getData());
                if(!$this->getEmail())
                    $this->setEmail($payment->getPaypalEmail());
            }
        }
        
        /* End edit */
        $html = Mage::helper('affiliateplus/payment')->__('Method: ');
        $html .= '<strong>' . $this->getLabel() . '</strong><br />';
        $html .= Mage::helper('affiliateplus/payment')->__('Email: ');
        $html .= '<strong>' . $this->getEmail() . '</strong><br />';
        //$html .= Mage::helper('affiliateplus/payment')->__('Fee: ');
        //$html .= '<strong>'.$this->getFeePrice(true).'</strong><br />';
        if ($this->getId() && $this->getTransactionId()) {
            $html .= Mage::helper('affiliateplus/payment')->__('Transaction Id: ');
            $html .= '<strong>' . $this->getTransactionId() . '</strong><br />';
        }
        return $html;
    }

    /**
     * load information of paypal payment method
     *
     * @return Magestore_Affiliateplus_Model_Payment_Paypal
     */
    public function loadPaymentMethodInfo() {
        if ($this->getPayment()) {
            $paymentInfo = $this->getCollection()
                    ->addFieldToFilter('payment_id', $this->getPayment()->getId())
                    ->getFirstItem();
            if ($paymentInfo)
                $this->addData($paymentInfo->getData())->setId($paymentInfo->getId());
        }
        return parent::loadPaymentMethodInfo();
    }

    /**
     * Save Payment Method Information
     *
     * @return Magestore_Affiliateplus_Model_Payment_Abstract
     */
    public function savePaymentMethodInfo() {
        $this->setPaymentId($this->getPayment()->getId())->save();
        return parent::savePaymentMethodInfo();
    }

    public function getEstimateFee($requiredAmount, $payer) {
        if ($payer=='recipient')
            $amount = round($requiredAmount, 2);
        else {
            if ($requiredAmount >= 50)
                $amount = round($requiredAmount - 1, 2); // max fee is 1$ by api
            else
                $amount = round($requiredAmount / 1.02, 2); // fees 2% when payment by api
        }

        if ($amount >= 50)
            $fee = 1;
        else
            $fee = round($amount * 0.02, 2);
        return $fee;
    }

}