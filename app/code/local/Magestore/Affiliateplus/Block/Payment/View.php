<?php
class Magestore_Affiliateplus_Block_Payment_View extends Mage_Core_Block_Template
{
    public function getAccount(){
    	return Mage::getSingleton('affiliateplus/session')->getAccount();
    }
    
    /**
     * get Payment Model
     *
     * @return Magestore_Affiliateplus_Model_Payment
     */
    public function getPayment(){
    	if (!$this->hasData('payment')){
    		$payment = Mage::registry('view_payment_data');
    		$payment->addPaymentInfo();
    		$this->setData('payment',$payment);
    	}
    	return $this->getData('payment');
    }
    
    /**
     * get Payment Method
     *
     * @return Magestore_Affiliateplus_Model_Payment_Abstract
     */
    public function getPaymentMethod(){
    	return $this->getPayment()->getPayment();
    }
    
    public function getStatusArray(){
    	return array(
			1	=> $this->__('Pending'),
			2	=> $this->__('Processing'),
			3	=> $this->__('Completed'),
            4	=> $this->__('Canceled'),
		);
    }
    
    public function _prepareLayout(){
		parent::_prepareLayout();
		
		if ($this->getPaymentMethod())
		if ($paymentMethodInfoBlock = $this->getLayout()->createBlock($this->getPaymentMethod()->getInfoBlockType(),'payment_method_info')){
			$paymentMethodInfoBlock->setPaymentMethod($this->getPaymentMethod());
			$this->setChild('payment_method_info',$paymentMethodInfoBlock);
		}
		
		return $this;
    }
    
    public function getFullHistory() {
        if (!$this->hasData('collection')) {
            $collection = Mage::getResourceModel('affiliateplus/payment_history_collection')
                ->addFieldToFilter('payment_id', $this->getPayment()->getId());
            $collection->getSelect()->order('created_time DESC');
            $this->setData('collection', $collection);
        }
        return $this->getData('collection');
    }
    
    public function getCollection() {
        return $this->getFullHistory();
    }
}
