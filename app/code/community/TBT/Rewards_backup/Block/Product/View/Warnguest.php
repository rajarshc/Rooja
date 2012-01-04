<?php

class TBT_Rewards_Block_Product_View_Warnguest extends Mage_Catalog_Block_Product_View {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('rewards/product/view/warnguest.phtml');
    }

    /**
     * display a message in thetelling the customer 
     * that they need to log-in or create an account to earn/redeem points.
     * 
     * @return string
     */
    public function getWarnMessage() {
        if (!$this->shouldWarnGuest())
            return '';
        return $this->__('Log-in or create an account to spend or redeem points with this product.');
    }

    /**
     * no user is logged in and quote has points spending/earning, 
     *
     * @return boolean 
     */
    public function shouldWarnGuest() {
        if ($this->isCustomerLoggedIn())
            return false;

        // get all catalog redemption rule that exists for this product
        $allSpendRules = $this->getSpendRules();
        // get catalog earning rule that exists for this product and customer
        // $allEarnRules = $this->getEarnRules();;
        // TODO: this is sataficatory.  I rgather get a array of all the rules that apply to this product ignoring customer group
        // however because of time i just used this workaround that returns true if the system has any earning rules
        $hasStoreEarnRules = $this->_getHelperRule()->storeHasAnyCatalogDistriRules();
        if (count($allSpendRules) == 0 && $hasStoreEarnRules == false)
            return false;

        // current user (guest) has no applicable redemptions or earnings
        $guestSpendRules = $this->getSpendRules(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        $guestEarnRules = $this->getEarnRules();
        if (count($guestSpendRules) > 0 || count($guestEarnRules) > 0 )
            return false;

        // should warn the guest user to login to check for possible redemption options
        return true;
    }
    
    /**
     * return an array of the earn rules using the current session (date,wid,gid,pid)
     * This should be changed to return all rules for the product ignoring just the group id
     *
     * @return array() 
     */
    public function getEarnRules() {
        $product = Mage::getModel('rewards/catalog_product')->load( parent::getProduct()->getId() );
        return $product->getDistriRules();
    }

    /**
     * Fetches redeemable rule options for this; date, wibsite, product
     * 
     * @param int $groupId if not null then filter the group aswell else if null dont filter
     * @return array()
     */
    public function getSpendRules($groupId=null) {
        $timestamp = Mage::helper('rewards')->now();
        $wId = Mage::app()->getWebsite()->getId();
        $pId = $this->getProduct()->getId();

        $res = Mage::getResourceModel('rewards/catalogrule_rule');
        $rules = $res->getApplicableRedemptionRewards($timestamp, $wId, $groupId, $pId);

        $applicable_rules = array();
        foreach ($rules as $rule) {
            if ($groupId == null || $rule->getGroupId() == $groupId)
                $applicable_rules[] = $rule;
        }
        return $applicable_rules;
    }

    /**
     * True if the customer is logged in.
     *
     * @return boolean
     */
    public function isCustomerLoggedIn() {
        return $this->_getRewardsSess()->isCustomerLoggedIn();
    }

    public function getCustomerSession() {
        return $this->_getRewardsSess()->getCustomerSession();
    }

    /**
     * Fetches the rewards session singleton
     *
     * @return TBT_Rewards_Model_Session
     */
    protected function _getRewardsSess() {
        return Mage::getSingleton('rewards/session');
    }

    /**
     *
     * @return TBT_Rewards_Helper_Rule
     */
    public function _getHelperRule() {
        return Mage::helper('rewards/rule');
    }

}