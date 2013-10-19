<?php

class Magestore_Affiliateplus_Model_Payment_Credit extends Magestore_Affiliateplus_Model_Payment_Abstract
{
    protected $_code = 'credit';
    
    protected $_eventPrefix = 'affiliateplus_credit';
    protected $_eventObject = 'affiliateplus_credit';
    
    public function _construct() {
        parent::_construct();
        $this->_init('affiliateplus/payment_credit');
    }
    
    public function savePaymentMethodInfo() {
        $payment = $this->getPayment();
        $this->setPaymentId($payment->getId())->save();
        return parent::savePaymentMethodInfo();
    }
    
    public function loadPaymentMethodInfo() {
        if ($this->getPayment()) {
            $this->load($this->getPayment()->getId(), 'payment_id');
        }
        return parent::loadPaymentMethodInfo();
    }
    
    public function getInfoString() {
        return Mage::helper('affiliateplus/payment')->__('
                Method: %s \n
                Pay for Order: %s \n
            ', $this->getLabel()
            , $this->getOrderIncrementId()
        );
    }
    
    public function getInfoHtml() {
        $html = Mage::helper('affiliateplus/payment')->__('Method: ');
		$html .= '<strong>'.$this->getLabel().'</strong><br />';
		$html .= Mage::helper('affiliateplus/payment')->__('Pay for Order: ');
        $html .= '<strong><a href="';
        if (Mage::app()->getStore()->isAdmin()) {
            $html .= Mage::getUrl('adminhtml/sales_order/view', array('order_id' => $this->getOrderId()));
        } else {
            $html .= Mage::getUrl('sales/order/view', array('order_id' => $this->getOrderId()));
        }
		$html .= '" title="'.Mage::helper('affiliateplus/payment')->__('View Order').'">#'.$this->getOrderIncrementId().'</a></strong><br />';
        if ($this->getBaseRefundAmount() > 0) {
            $html .= Mage::helper('affiliateplus/payment')->__('Refunded: ');
            $formatedAmount = Mage::app()->getStore()->getBaseCurrency()->format(
                $this->getBaseRefundAmount(),
                array(),
                false
            );
            $html .= '<strong>'.$formatedAmount.'</strong><br />';
        }
		return $html;
    }
}
