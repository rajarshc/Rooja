<?php

/**
 * @nelkaake 22/01/2010 3:54:41 AM : points expiry
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */

require_once("AbstractController.php");
class TBT_RewardsReferral_Debug_IndexController extends TBT_Rewards_Debug_AbstractController
{
    
    public function indexAction()
    {
        echo "<h2>This tests installation things. </h2>";
        echo "<a href='". Mage::getUrl('rewardsref/debug_index/clearCache') ."'>Flush All Cache</a>. <BR />";
        
        
        exit;
    }



    protected function clearCacheAction() {
        Mage::app()->getCacheInstance()->flush();
        echo "Cache has been cleared.";
    	return $this;
    	
    }
    
    
    public function multirefAction() {
        
        $ref_col = Mage::getModel('rewardsref/transfer_reference')
                ->getCollection()
                ->addReferences()
                ->addFieldToFilter('reference_id', $referralobj->getReferralChildId())
                ->addFieldToFilter('reference_type', TBT_RewardsReferral_Model_Transfer::REFERENCE_REFERRAL)
                ->addFieldToFilter('customer_id', $referralobj->getReferralParentId())
                ->addFieldToFilter('status', TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED);
        
        echo "Done.";
        return $this;
    }
    
}