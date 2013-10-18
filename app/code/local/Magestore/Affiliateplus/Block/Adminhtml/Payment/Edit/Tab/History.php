<?php

class Magestore_Affiliateplus_Block_Adminhtml_Payment_Edit_Tab_History extends Mage_Adminhtml_Block_Template
{
    protected function _prepareLayout() {
        parent::_prepareLayout();
        return $this->setTemplate('affiliateplus/payment/history.phtml');
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
    
    public function getPayment() {
        if (Mage::registry('payment_data')) {
            return Mage::registry('payment_data');
        }
        return new Varien_Object();
    }
}
