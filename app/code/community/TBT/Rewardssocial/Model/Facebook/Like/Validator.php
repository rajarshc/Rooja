<?php

class TBT_Rewardssocial_Model_Facebook_Like_Validator extends TBT_Rewards_Model_Special_Validator {

    /**
     * 
     * @param TBT_Rewards_Model_Customer $customer
     * @return boolean
     */
    public function maxLikesReached($customer) {
        $max_likes = Mage::helper('rewardssocial/facebook_config')->getMaxLikeRewardsPerDay( $customer->getStore() );
        $current_time = time();
        $h24 = 60*60*24;
        $oldest_req_time = $current_time - $h24;
        
        $all_likes_since_time = Mage::getModel('rewardssocial/facebook_like')
            ->getCollection()
            ->addFilter('customer_id', $customer->getId())
            ->addFieldToFilter('UNIX_TIMESTAMP(created_time)', array('gteq' => $oldest_req_time));
        
        if($all_likes_since_time->count() > $max_likes) {
            return true;
        }
        
        
        $like_transfers = Mage::getResourceModel('rewardssocial/facebook_like_transfer_collection')->filterCustomerRewardsSince($customer->getId(), $oldest_req_time);
        if($like_transfers->load()->count() > $max_likes) {
            return true;
        }
        
        return false;
    }

    /**
     * Loops through each Special rule. If the rule applies and the customer didn't 
     * already earn points for this like, then create (a) new points transfer(s) for the like.
     * @note: Adds messages to the session TODO: return messages instead of adding session messages
     */
    public function initReward($facebook_account_id, $liked_url, $customer) {
        
        try {
            $ruleCollection = $this->getApplicableRulesOnFacebookLike();
            
            if ($this->_likeExists($customer, $liked_url)) {
                //Mage::getSingleton('core/session')->addNotice(Mage::helper('rewardssocial')->__("You've already received points for liking this page."));
                return $this;
            }
                
            $wait_time = Mage::getModel('rewardssocial/facebook_like')->getCollection()->getTimeUntilNextLikeAllowed($customer);
            if($wait_time > 0) {
                Mage::getSingleton('core/session')->addError(
                    Mage::helper('rewards')->__('Please wait %s second(s) before liking another page if you want to be rewarded.', $wait_time));
                return $this;
            }
        
            
            $max_likes = Mage::helper('rewardssocial/facebook_config')->getMaxLikeRewardsPerDay( $customer->getStore() );
            if( $this->maxLikesReached($customer) ) {
                Mage::getSingleton('core/session')->addError(
                    Mage::helper('rewards')->__("You've reached the Facebook like rewards limit for today (%s)", $max_likes));
                return $this;
            }
            
            $like_model = Mage::getModel('rewardssocial/facebook_like')
                ->setCustomerId($customer->getId())
                ->setFacebookAccountId($facebook_account_id)
                ->setUrl($liked_url)
                ->save();
            
            if(! $like_model->getId()) {
                throw new Exception("LIKE model was not saved for some reason. Customer ID {$customer->getId()}, LIKE url: {$liked_url}.");
            }
            
            $this->_transferLikePoints($ruleCollection, $customer, $like_model);
                
            
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError(Mage::helper('rewards')->__('Could not reward you for your Facebook mention.'));
            Mage::logException($e);
        }
        
        return $this;
    }
    
    
    /**
     * Loops through each Special rule. If the rule applies, create a new transfer.
     * @note: Adds messages to the session TODO: return messages instead of adding session messages
     */
    public function cancelLikeRewards($facebook_account_id, $liked_url, $customer) {
        
        try {
            $ruleCollection = $this->getApplicableRulesOnFacebookLike();
            
            // Only if a LIKE exists should we  cancel/revoke the LIKE and associated points.
            if (!$this->_likeExists($customer, $liked_url)) { 
                return $this;
            }
            
            
            $like_model = Mage::getModel('rewardssocial/facebook_like')
                ->getCollection()
                ->addFilter('url', $liked_url)
                ->addFilter('customer_id', $customer->getId() )
                ->getFirstItem();
            
            // Cancel related points
            $this->_cancelUnlikedTransfers($like_model);
            
            // Like the like model reference
            // TODO Idealy we would add a deleted flag, but this is much quicker in the interest of time.
            $like_model->delete();
            
        } catch (Exception $e) {
            //Mage::getSingleton('core/session')->addError(  Mage::helper('rewards')->__( "Could not interface with customer rewards system. Error was: %s", $e->getMessage() )  );
            Mage::logException($e);
        }
        
        return $this;
    }
    
    /**
     * 
     * @param TBT_Rewardssocial_Model_Facebook_Like $like_model
     */
    protected function _cancelUnlikedTransfers($like_model) {
        $transfer_col = Mage::getResourceModel('rewardssocial/facebook_like_transfer_collection');
        $transfer_col->addFacebookLikeFilter($like_model);

        $cancellation_msg = Mage::helper('rewardssocial')->__("* Points cancelled because user unliked page on Facebook.");
        
        foreach($transfer_col as &$transfer) {
            // Append the comments, then cancel.  Cancelling the transfer saves it too, so no need for a duplicate save.
            $transfer->appendComments($cancellation_msg);
            $transfer->cancel();
            
            $points_string = (string) Mage::getModel('rewards/points')->set( $transfer->getCurrencyId(), $transfer->getQuantity() );
            
            Mage::getSingleton('core/session')->addSuccess(
                Mage::helper('rewardssocial')->__('The <b>%s</b> you earned for liking this have been <b>cancelled</b>.', $points_string)
            );
               
        }
        
        return $this;
    }
    
    
    
    /**
     * @param TBT_Rewards_Model_Customer
     * @param string $liked_url
     * @return boolean
     */
    public function hasLikedPage($customer, $liked_url) {
        return $this->_likeExists($customer, $liked_url);
    }
    
    
    /**
     * @param TBT_Rewards_Model_Customer
     * @param string $liked_url
     * @return boolean
     */
    protected function _likeExists($customer, $liked_url) {
        $duplicate_like = Mage::getModel('rewardssocial/facebook_like')
            ->getCollection()->containsEntry($customer->getId(), $liked_url);
        return $duplicate_like;
    }
    
    
    
    /**
     * Goes through an already validated rule collection and transfers rule points to the customer specified
     * with the like model as the reference.
     * @param array(TBT_Rewards_Model_Special) $ruleCollection
     * @param TBT_Rewards_Model_Customer $customer
     * @param TBT_Rewardssocial_Model_Facebook_Like $like_model
     * @note: Adds messages to the session TODO: return messages instead of adding session messages
     */
    protected function _transferLikePoints($ruleCollection, $customer, $like_model) {
        foreach ($ruleCollection as $rule) {
            if (!$rule->getId()) {
                continue;
            }
            
            try {
                $transfer = Mage::getModel('rewardssocial/facebook_like_transfer');
                $is_transfer_successful = $transfer->createFacebookLikePoints(
                    $customer, 
                    $like_model->getId(), 
                    $rule
                );
                
                if ($is_transfer_successful) {
                    $points_for_liking = Mage::getModel('rewards/points')->set($rule);
                    Mage::getSingleton('core/session')->addSuccess(
                        Mage::helper('rewardssocial')->__('You received <b>%s</b> for liking this page on Facebook.', $points_for_liking));
                }
            } catch (Exception $ex) {
                Mage::logException($ex);
                Mage::getSingleton('core/session')->addError($ex->getMessage());
            }
        }
        
        return $this;
    }
    
    /**
     * Returns all rules that apply wehn a customer likes something on facebook
     * @return array(TBT_Rewards_Model_Special)
     */
    public function getApplicableRulesOnFacebookLike() {
        return $this->getApplicableRules(TBT_Rewardssocial_Model_Facebook_Like_Special_Config::ACTION_CODE);
    }

    
    /**
     * Returns an array outlining the number of points they will receive for liking the item
     *
     * @return array
     */
    public function getPredictedFacebookLikePoints($page=null) {
        
        Varien_Profiler::start("TBT_Rewardssocial:: Predict Facebook Like Points");
        $ruleCollection = $this->getApplicableRulesOnFacebookLike();
        
        $predict_array = array();
        foreach ($ruleCollection as $rule) {
            // TODO: shoud this be += ? I think so.
            $predict_array[$rule->getPointsCurrencyId()] = $rule->getPointsAmount();
        }
        
        Varien_Profiler::stop("TBT_Rewardssocial:: Predict Facebook Like Points");
        return $predict_array;
    }

}
?>