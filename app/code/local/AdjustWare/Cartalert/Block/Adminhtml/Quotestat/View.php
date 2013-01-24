<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 01/11/12
 * Package:     AdjustWare_Cartalert_3.1.1_0.2.3_440060
 * Purchase ID: NZmnTZChS7OANNEKozm6XF7MkbUHNw6IY9fsWFBWRT
 * Generated:   2013-01-22 11:08:03
 * File path:   app/code/local/AdjustWare/Cartalert/Block/Adminhtml/Quotestat/View.php
 * Copyright:   (c) 2013 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ IgjjhoeEUBorUICi('758ae413d6e039768fbaaa0d58661760'); ?><?php

class AdjustWare_Cartalert_Block_Adminhtml_Quotestat_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; // ?
        $this->_blockGroup = 'adjcartalert';
        $this->_controller = 'adminhtml_quotestat';
        $this->_mode = 'view';
        
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_removeButton('delete');        
    
    
        $data = Mage::registry('quotestat_data');
        $this->_order = Mage::getModel('sales/order')->getCollection()->addFieldTofilter('quote_id', array('eq'=>$data->getQuoteId()))->getFirstItem();
        $quote = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('entity_id', array('eq'=>$data->getQuoteId()))->getFirstItem();
        $this->_customer = Mage::getModel('customer/customer')->load($quote->getCustomerId());
        $this->_customerGroup = Mage::getModel('customer/group')->load($this->_customer->getGroupId());    
    
        if($this->_order->getId())
        {
            $this->_addButton('order_view', array(
                'label'     => Mage::helper('sales')->__('View Order'),
                'onclick'   => 'setLocation(\'' .  Mage::helper("adminhtml")->getUrl('adminhtml/sales_order/view', array('order_id'=>$this->_order->getId())) . '\')',
            ));
        }
    
    }

    public function getHeaderText()
    {
            return Mage::helper('adjcartalert')->__('Abandoned Carts Statistic');
    }
    
    public function getOrder()
    {
        return $this->_order;
    }

    public function getCustomer()
    {
        return $this->_customer;
    }         
    
    public function getCustomerGroup()
    {
        return $this->_customerGroup;
    }     
    
} } 