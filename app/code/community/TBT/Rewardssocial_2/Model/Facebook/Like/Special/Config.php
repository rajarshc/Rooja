<?php
class TBT_Rewardssocial_Model_Facebook_Like_Special_Config extends TBT_Rewards_Model_Special_Configabstract
{
    const ACTION_CODE = 'social_facebook_like';
    
    public function _construct()
    {
        return parent::_construct();
        $this->setCaption(Mage::helper('rewardssocial')->__("Facebook Like"));
        $this->setDescription(Mage::helper('rewardssocial')->__("Customer will get points when they like a page with Facebook."));
        $this->setCode('social_facebook_like');
    }
    
    public function visitAdminActions(&$fieldset)
    {
        return $this;
    }
    
    public function visitAdminConditions(&$fieldset)
    {
        return $this;
    }
    
    public function getNewCustomerConditions()
    {
        return array(
            self::ACTION_CODE => Mage::helper('rewardssocial')->__("Likes a page with Facebook")
        );
    }
    
    public function getNewActions()
    {
        return array ();
    }
    
    public function getAdminFormScripts()
    {
        return array ();
    }
    
    public function getAdminFormInitScripts()
    {
        return array ();
    }
}
