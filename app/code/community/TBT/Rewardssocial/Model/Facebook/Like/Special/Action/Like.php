<?php
class TBT_Rewardssocial_Model_Facebook_Like_Special_Action_Like
        extends TBT_Rewards_Model_Special_Action_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setCaption(Mage::helper('rewardssocial')->__("Facebook Like"));
        $this->setDescription(Mage::helper('rewardssocial')->__("Customer will get points when they like a page with Facebook."));
        $this->setCode('social_facebook_like');
    }

    public function givePoints(&$customer) { }

    public function revokePoints(&$customer) { }

    public function holdPoints(&$customer) { }

    public function cancelPoints(&$customer) { }

    public function approvePoints(&$customer) { }
}
