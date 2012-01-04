<?php
class TBT_Rewardssocial_Model_Facebook_Like_Special_Condition_Like
        extends TBT_Rewards_Model_Special_Condition_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->setCaption(Mage::helper('rewardssocial')->__("Facebook Like"));
        $this->setDescription(Mage::helper('rewardssocial')->__("Customer will get points when they a page with Facebook."));
        $this->setCode('social_facebook_like');
    }

    public function givePoints(&$customer) { }

    public function revokePoints(&$customer) { }

    public function holdPoints(&$customer) { }

    public function cancelPoints(&$customer) { }

    public function approvePoints(&$customer) { }
}
