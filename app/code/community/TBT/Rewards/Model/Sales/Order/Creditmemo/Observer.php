<?php

class TBT_Rewards_Model_Sales_Order_Creditmemo_Observer extends Varien_Object
{
    public function refund($observer)
    {
        $event = $observer->getEvent();
        if (!$event) {
            return $this;
        }
        
        $creditmemo = $event->getCreditmemo();
        if (!$creditmemo) {
            return $this;
        }
        
        $order = $creditmemo->getOrder();
        if (!$order) {
            return $this;
        }
        
        $transfers = Mage::getModel('rewards/transfer')->getTransfersAssociatedWithOrder($order->getId());
        foreach ($transfers as $transfer) {
            if ($transfer->getStatus() == TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED) {
                try {
                    $transfer->revoke();
                } catch (Exception $ex) {
                    Mage::getSingleton('core/session')->addError($ex->getMessage());
                    continue;
                }
                
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('rewards')->__("Successfully cancelled transfer ID #" . $transfer->getId()));
            }
        }
        
        return $this;
    }
}
