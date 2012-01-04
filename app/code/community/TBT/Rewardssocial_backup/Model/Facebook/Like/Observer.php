<?php

class TBT_Rewardssocial_Model_Facebook_Like_Observer extends Varien_Object {
	
    /**
     * This observes the event of a customer liking something
     * on facebook. Whenever the like button is pressed on any
     * page, the details of that action are passed through this
     * observer to record and create transfers from.
     *
     * The event should contain the following details:
     * 
     * facebook_account_id - identifies which facebook account liked the url
     * liked_url - identifies which url (product, category, page, etc) was liked
     * 
     * @param unknown_type $o
     */
    public function facebookLikeAction($o) {
        
        $event = $o->getEvent();
        
        $facebook_account_id = $event->getFacebookAccountId();
        $liked_url = $event->getLikedUrl();
        $customer = $this->getCurrentlyLoggedInCustomer();
        
        if (!$customer) {
            return $this;
        }
        
        $this->initReward($facebook_account_id, $liked_url, $customer);
        
        return $this;
    }
    
    /**
     * Runs before the customer behavior rule is saved to check the evlike settings.
     * @param Varien_Object $o
     */
    public function checkFacebookSettings($o) {
        $event = $o->getEvent();
		$this->setRequest ( $o->getControllerAction ()->getRequest () );
		$this->setResponse ( $o->getControllerAction ()->getResponse () );
        $post_data = $this->getRequest ()->getPost ();
		if (empty($post_data)) {
		    return $this;
		}
		
		if($post_data ['points_conditions'] != TBT_Rewardssocial_Model_Facebook_Like_Special_Config::ACTION_CODE ) {
		    return $this;
		}

		$rewards_wiki_url = "https://sweettoothrewards.com/wiki/index.php/Sweet_Tooth_Facebook";
		
		if ( ! Mage::helper('rewardssocial/facebook_evlike')->isEvlikeEnabled() ) {
            $msg = Mage::helper('rewardssocial')->__("The Facebook Like Module by Retail Evolved has not been installed or is disabled. It is required to reward customers for liking products on Facebook with Sweet Tooth. For more information, please [rewards_wiki_facebook_link]visit this help article[/rewards_wiki_facebook_link]. Your rule was still saved.");
            
            $msg = Mage::helper('rewardssocial')->getTextWithLinks($msg, 'rewards_wiki_facebook_link', $rewards_wiki_url, array('target' => '_wiki_sweet_tooth_facebook') );
            
            Mage::getSingleton('core/session')->addError($msg);
		    return $this;                  
        }
		
		if( ! Mage::helper('rewardssocial/facebook_evlike')->isEvlikeValidRewardsConfig() ) {
		    $evlike_config_url = Mage::helper('rewardssocial/facebook_evlike')->getConfigUrl();
		    $msg = Mage::helper('rewardssocial')->__("The Facebook Like Module by Retail Evolved has not been configured properly.  Please visit the [evlike_config_link]Retail Evolved Facebook Like configuration[/evlike_config_link] section and change the Button Type to 'XFBML' or [rewards_wiki_facebook_link]visit this help article[/rewards_wiki_facebook_link]. Your rule was still saved.");
		    
            $msg = Mage::helper('rewardssocial')->getTextWithLinks($msg, 'evlike_config_link', $evlike_config_url);
            $msg = Mage::helper('rewardssocial')->getTextWithLinks($msg, 'rewards_wiki_facebook_link', $rewards_wiki_url, array('target' => '_wiki_sweet_tooth_facebook') );
            
            
		    Mage::getSingleton('core/session')->addError($msg);
		    return $this;
		}
		
		return $this;
        
    }
    
    public function getCurrentlyLoggedInCustomer() {
        
        if (Mage::app()->isInstalled() && Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::getSingleton('customer/session')->getCustomer();
        }
        return null;
    }

    /**
     * Loops through each Special rule. If the rule applies, create a new transfer.
     */
    public function initReward($facebook_account_id, $liked_url, $customer) {
        return $this->_getFacebookLikeValidator()->initReward($facebook_account_id, $liked_url, $customer);

    }
    
    /**
     * @return TBT_Rewardssocial_Model_Facebook_Like_Validator
     */
    protected function _getFacebookLikeValidator() {
        return Mage::getSingleton('rewardssocial/facebook_like_validator');
    }
    
    
}
