<?php

class TBT_RewardsReferral_Model_Observer_Refer extends Varien_Object {

    public function recordPointsUponRegistration($observer) {

        //@nelkaake Added on Wednesday May 5, 2010:  Try/catch and a check to make sure that referral points exist before triggering.
        try {
            if (!Mage::getModel('rewardsref/referral_signup')->hasReferralPoints())
                return $this;
            $newCustomer = $observer->getEvent()->getCustomer();
            Mage::getModel('rewardsref/referral_signup')->trigger($newCustomer);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    public function recordPointsForOrderEvent($observer) {
        $orderId = Mage::getSingleton('checkout/type_onepage')
                        ->getCheckout()->getLastOrderId();
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
     */
    public function recordPointsUponFirstOrder($order) {
        try {
            if (!Mage::getModel('rewardsref/referral_firstorder')->hasReferralPoints())
                return $this;
            //$order = $observer->getEvent()->getInvoice()->getOrder();
            $referralModel = Mage::getModel('rewardsref/referral_firstorder');
            if ($referralModel->isSubscribed($order->getCustomerEmail())
                    && !$referralModel->isConfirmed($order->getCustomerEmail())) {
                $child = Mage::getModel('rewards/customer')->load($order->getCustomerId());
                Mage::getModel('rewardsref/referral_firstorder')->trigger($child);
                $parent = Mage::getModel('rewards/customer')->load($referralModel->getReferralParentId());
                $referralModel->sendConfirmation($parent, $child, $parent->getEmail());
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function recordPointsOrder($order) {
        try {
            if (!Mage::getModel('rewardsref/referral_order')->hasReferralPoints())
                return $this;
            //$order = $observer->getEvent()->getInvoice()->getOrder();
            $referralModel = Mage::getModel('rewardsref/referral_order')->setOrder($order);
            $child = Mage::getModel('rewards/customer')->load($order->getCustomerId());
            $parent = Mage::getModel('rewards/customer')->load($referralModel->getReferralParentId());
            $referralModel->trigger($child);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

}
