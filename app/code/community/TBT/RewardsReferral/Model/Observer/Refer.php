<?php

class TBT_RewardsReferral_Model_Observer_Refer extends Varien_Object {

    public function recordPointsUponRegistration($observer) {
        try {
            $model = Mage::getModel('rewardsref/referral_signup');
            $newCustomer = $observer->getEvent()->getCustomer();
            $model->triggerEvent($newCustomer);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    public function recordPointsForOrderEvent($observer) {
        $orderId = Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId();
        $order = Mage::getModel('rewards/sales_order')->load($orderId);

        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return false;
        }

        $this->recordPointsUponFirstOrder($order);
        $this->recordPointsOrder($order);
        
        return $this;
    }

    /**
     * 
     * 
     * @param Mage_Sales_Model_Order $order
     * @return TBT_RewardsReferral_Model_Observer_Refer
     */
    public function recordPointsUponFirstOrder($order) {
        try {
            $model = Mage::getModel('rewardsref/referral_firstorder');
            $model->setOrder($order);
            if ($model->isSubscribed($order->getCustomerEmail()) && false == $model->isConfirmed($order->getCustomerEmail())) {
                $customer = Mage::getModel('rewards/customer')->load($order->getCustomerId());
                $model->triggerEvent($customer, $order->getId());
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     *
     * @param TBT_Rewards_Model_Sales_Order $order
     * @return TBT_RewardsReferral_Model_Observer_Refer
     */
    public function recordPointsOrder($order) {
        try {
            $model = Mage::getModel('rewardsref/referral_order');
            $model->setOrder($order);
            $child = Mage::getModel('rewards/customer')->load($order->getCustomerId());
            $affiliate = Mage::getModel('rewards/customer')->load($model->getReferralParentId());
            $model->triggerEvent($child, $order->getId());
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

}
