<?php
class TBT_Rewardssocial_Model_Facebook_Like_Rewards_Observer extends Varien_Object
{
    public function transferVestation($observer)
    {
        $transfer = $observer->getEvent()->getTransfer();
        
        // TODO: check Facebook if user is still Liking this thing
        $doesFacebookAccountStillLikeThisThing = true;
        
        if (!$doesFacebookAccountStillLikeThisThing) {
            $observer->getEvent()->getResult()->setIsSafeToApprove(false);
        }
        
        return $this;
    }
}
