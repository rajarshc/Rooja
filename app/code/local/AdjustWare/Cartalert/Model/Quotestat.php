<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 01/11/12
 * Package:     AdjustWare_Cartalert_3.1.1_0.2.3_440060
 * Purchase ID: NZmnTZChS7OANNEKozm6XF7MkbUHNw6IY9fsWFBWRT
 * Generated:   2013-01-22 11:08:03
 * File path:   app/code/local/AdjustWare/Cartalert/Model/Quotestat.php
 * Copyright:   (c) 2013 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ RjaaohZOQDhmQRUq('f3baf73fdeeed149b49927a5ba7c6407'); ?><?php
class AdjustWare_Cartalert_Model_Quotestat extends Mage_Core_Model_Abstract
{

    protected $_followupArray = array('first'=>1,'second'=>2,'third'=>3,''=>0);

    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/quotestat');
    }
    
    public function onOrderCreate($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->load($order->getQuoteId(),'quote_id');
        if($this->getId() && $this->getRecoveryDate())
        {
            $this->setOrderDate($order->getCreatedAt());
            $this->setOrderPrice($order->getBaseGrandTotal());
            $items = array();
            foreach($order->getAllItems() as $item)
            {
                if($item->getProductType() == 'simple')
                {            
                    $items[$item->getProductId()] = $item->getQtyOrdered();
                }
            }
            $this->setOrderItems(serialize($items));        
            $this->setOrderCouponUsed($order->getCouponCode());
            $this->save();
        }
    }
    
    public function onAlertGenerate($observer)
    {
        $quoteData = $observer->getEvent()->getQuote();
        $quote = Mage::getModel('sales/quote')->setStoreId($quoteData['store_id'])->load($quoteData['quote_id']);
        $instance = new self;
        if($quote->getEntityId())
        {
            $instance->load($quote->getEntityId(),'quote_id');
            $instance->setQuoteId($quote->getEntityId());
            $instance->setCartPrice($quote->getBaseGrandTotal());
            $instance->setCartAbandonDate($quoteData['abandoned_at']);
            $items = array();
            foreach($quote->getAllItems() as $item)
            {
                if($item->getProductType() == 'simple')
                {        
                    $items[$item->getProductId()] = $item->getQty();
                }
            }
            $instance->setCartItems(serialize($items));
            $instance->save();
        }
    }
    
    public function onAlertSend($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $history = $observer->getEvent()->getHistory();
        $this->load($quote->getQuoteId(),'quote_id');
        if($this->getId())
        {
            $this->setAlertNumber($this->_followupArray[$quote->getFollowUp()]);
            $this->setAlertDate(date('Y-m-d H:i:s'));
            $this->setAlertCouponGenerated($history->getCouponCode());
            $this->save();
        }
    }
    
    public function onCartRecovery($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $this->load($quote->getEntityId(),'quote_id');
        if($this->getId())
        {        
            $this->setRecoveryDate(date('Y-m-d H:i:s'));
            $this->save();
        }
    }    
    
    public static function collectDay($date)
    {
        $instance = new self;        
        $quoteCollection = Mage::getModel('Mage_Reports_Model_Mysql4_Quote_Collection')->prepareForAbandonedReport(0);
        $quoteCollection->getSelect()->where('`main_table`.`updated_at` BETWEEN \''.$date.'\' AND \''.$date.'\' + INTERVAL 1 DAY');
        foreach($quoteCollection as $quote)        
        {
            $instance->load($quote->getEntityId(),'quote_id');
            
            $instance->setQuoteId($quote->getEntityId());
            $instance->setCartPrice($quote->getBaseGrandTotal());
            $instance->setCartAbandonDate($quote->getUpdatedAt());
            $items = array();
            foreach($quote->getAllItems() as $item)
            {
                if($item->getProductType() == 'simple')
                {                 
                    $items[$item->getProductId()] = $item->getQty();
                }
            }
            $instance->setCartItems(serialize($items));
            
            $historyCollection = Mage::getModel('adjcartalert/history')->getCollection();
            $historyCollection->addFieldToFilter('quote_id', $quote->getEntityId())->setOrder('sent_at','DESC');
            $history = $historyCollection->getFirstItem();
        
            $instance->setAlertNumber($instance->_followupArray[$history->getFollowUp()]);
            $instance->setAlertDate($history->getSheduledAt());
            $instance->setRecoveryDate($history->getRecoveredAt());
            
            $orderCollection = Mage::getModel('sales/order')->getCollection();
            $order = $orderCollection->addFieldToFilter('quote_id',$quote->getEntityId())->getFirstItem();
            
            if($order->getId())
            {
                $instance->setOrderDate($order->getCreatedAt());
                $instance->setOrderPrice($order->getBaseGrandTotal());
                $items = array();
                foreach($order->getAllItems() as $item)
                {
                    if($item->getProductType() == 'simple')
                    {                     
                        $items[$item->getProductId()] = $item->getQtyOrdered();
                    }
                }
                $instance->setOrderItems(serialize($items));        
                $instance->setOrderCouponUsed($order->getCouponCode());
            }
            
            $instance->save();
        }
    }
    
} } 