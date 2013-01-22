<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 01/11/12
 * Package:     AdjustWare_Cartalert_3.1.1_0.2.3_440060
 * Purchase ID: NZmnTZChS7OANNEKozm6XF7MkbUHNw6IY9fsWFBWRT
 * Generated:   2013-01-22 11:08:03
 * File path:   app/code/local/AdjustWare/Cartalert/Model/Dailystat.php
 * Copyright:   (c) 2013 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ hyggohMaCmhDChpq('075db608b7b08b7acca7b2f48b6fe36c'); ?><?php
class AdjustWare_Cartalert_Model_Dailystat extends Mage_Core_Model_Abstract
{
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/dailystat');
    }
    
    public static function collectDay($date)
    {
        $instance = new self;
        $instance->load($date,'date');
        $instance->setDate($date);
        $carts = Mage::getModel('adjcartalert/quotestat')->getCollection();
        $carts->getSelect()->where('`cart_abandon_date` BETWEEN \''.$instance->getDate().'\' AND \''.$instance->getDate().'\' + INTERVAL 1 DAY');
        $abandonedItemsNum = 0;
        $abandonedCartsPrice = 0;
        $recoveredCarts = 0;
        $orderedItemsNum = 0;
        $orderedCartsPrice = 0;        
        $orderedCarts = 0;
        $letterStep = 0;
        //$orderTime = 0;
        $moduleCouponsUsed = 0;
        foreach($carts as $cart)
        {
            $abandonedCartsPrice+=$cart->getCartPrice();
            $items = unserialize($cart->getCartItems());
            foreach($items as $item)
            {
                $abandonedItemsNum+=$item;
            }
            if($cart->getRecoveryDate())
            {
                $recoveredCarts++;
            }            
            if($cart->getOrderDate())
            {
                $orderedCarts++;
                $orderedCartsPrice+=$cart->getOrderPrice();
                $items = unserialize($cart->getOrderItems());
                foreach($items as $item)
                {
                    $orderedItemsNum+=$item;
                }
                $letterStep += $cart->getAlertNumber();
            }
            if($cart->getAlertCouponGenerated() != '' && $cart->getAlertCouponGenerated()==$cart->getOrderCouponUsed())
            {
                $moduleCouponsUsed++;
            }
        }
        $instance->setAbandonedCartsNum(count($carts));
        $instance->setAbandonedCartsPrice($abandonedCartsPrice);
        $instance->setAbandonedItemsNum($abandonedItemsNum);
        
        $instance->setRecoveredCartsNum($recoveredCarts);
        
        $instance->setOrderedCartsNum($orderedCarts);
        $instance->setOrderedCartsPrice($orderedCartsPrice);
        $instance->setOrderedItemsNum($orderedItemsNum);
        $instance->setOrderedItemsNum($orderedItemsNum);
        $instance->setCouponsUsed($moduleCouponsUsed);
        if($orderedCarts)
        {
            /*$orderTime = round($orderTime / $orderedCarts);
            $orderTimeS = $orderTime % 60;
            $orderTime = (int)$orderTime/60;
            $orderTimeM = $orderTime % 60;
            $orderTime = (int)$orderTime/60;
            $instance->setAvBackTime($orderTime.':'.$orderTimeM.':'.$orderTimeS);*/
            $instance->setTargetLetterStep($letterStep/$orderedCarts);
        }
        $instance->save();
    }
    
} } 