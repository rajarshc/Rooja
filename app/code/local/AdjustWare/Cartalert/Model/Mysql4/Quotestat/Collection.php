<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 01/11/12
 * Package:     AdjustWare_Cartalert_3.1.1_0.2.3_440060
 * Purchase ID: NZmnTZChS7OANNEKozm6XF7MkbUHNw6IY9fsWFBWRT
 * Generated:   2013-01-22 11:08:03
 * File path:   app/code/local/AdjustWare/Cartalert/Model/Mysql4/Quotestat/Collection.php
 * Copyright:   (c) 2013 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ TeZZiqDWRjqyRTIp('00e2096a0c1b902d00c3027ed8a139ae'); ?><?php

class AdjustWare_Cartalert_Model_Mysql4_Quotestat_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('adjcartalert/quotestat');
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        foreach ($this->_items as $item) {
            $item->setStatus('Just abandoned');
            if($item->getAlertNumber())
            {
                $item->setStatus('Reminded '.$item->getAlertNumber().' time(s)');
            }
            if($item->getRecoveryDate())
            {
                $item->setStatus('Recovered');
            }
            if($item->getOrderDate())
            {
                $item->setStatus('Ordered');
            }
            $item->setCurrency($currency);
        }
        return $this;
    }    
    
} } 