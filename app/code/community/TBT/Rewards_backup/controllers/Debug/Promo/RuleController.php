<?php

/**
 * @nelkaake 22/01/2010 3:54:41 AM : points expiry
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */

include_once ("TBT".DS."Rewards".DS."controllers".DS."Debug".DS."AbstractController.php");
include_once ("Mage".DS."Checkout".DS."controllers" .DS."CartController.php");
class TBT_Rewards_Debug_Promo_RuleController extends TBT_Rewards_Debug_AbstractController
{
    
    public function indexAction()
    {
        echo "<h2>This tests points expiry </h2>";
        echo "<a href='". Mage::getUrl('rewards/debug_promo_rule/disableAllCartRules') ."'>Disable all Rewards SHOPPING CART Rules</a> <BR />";
        echo "<a href='". Mage::getUrl('rewards/debug_promo_rule/disableAllCatalogRules') ."'>Disable all Rewards CATALOG Rules</a> <BR />";
        echo "<a href='". Mage::getUrl('rewards/debug_promo_rule/disableAllRules') ."'>Disable ALL Rules</a> <BR />";
        echo "<a href='". Mage::getUrl('rewards/debug_promo_rule/deleteSeleniumRules') ."'>Delete all SELENIUM test rules</a> - Assuming [selenium] in the rule name.<BR />";
        echo "<a href='". Mage::getUrl('rewards/debug_promo_rule/disableSpecialRules') ."'>Disable Special rules</a> - Assuming [selenium] in the rule name.<BR />";
        
        exit;
    }
    /**
     *
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    protected function disableSpecialRulesAction() {
    	foreach(Mage::getModel('rewards/special')->getCollection() as $sr) {
    		$sr->setIsActive(false)->save();
    	}
    	echo "<BR /> <BR /> \n\nALL special rules have been disabled. <BR />\n";
    	return $this;
    }

    /**
     *
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    protected function deleteSeleniumRulesAction() {
    	Mage::helper('rewards/debug_rule')->deleteAllWithFilter($this->getSeleniumKey());
    	Mage::helper('rewards/debug_rule')->applyAllCatalogRules();
    	echo "<BR /> <BR /> \n\nALL rules with {$this->getSeleniumKey()} in the name have been deleted and thye have been applied! <BR />\n";
    	return $this;
    }

    /**
     *
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    protected function disableAllRulesAction() {
    	Mage::helper('rewards/debug_rule')->disableAllRules();
    	Mage::helper('rewards/debug_rule')->applyAllCatalogRules();
    	echo " <BR />  <BR /> \n\nALL rules have been disabled and reapplied! <BR />\n";
    	return $this;
    }
    
    /**
     *
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    protected function disableAllCartRulesAction() {
    	Mage::helper('rewards/debug_salesrule')->disableAllCartRules();
    	echo " <BR />  <BR /> \n\nAll sales rules have been disabled! <BR />\n";
    	return $this;
    }
    /**
     *
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    protected function disableAllCatalogRulesAction() {
    	Mage::helper('rewards/debug_salesrule')->disableAllCartRules();
    	echo " <BR />  <BR /> \n\nAll sales rules have been disabled! <BR />\n";
    	return $this;
    }
    
    /**
     *
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    protected function enableOnlyAndReturn($rule_id=1) {
    	return Mage::helper('rewards/debug_salesrule')->enableOnlyAndReturn();
    }
    
    /**
     *
     * @return TBT_Rewards_Model_Mysql4_Salesrule_Rule_Collection
     */
    protected function _getAllSalesrules() {
    	return Mage::helper('rewards/debug_salesrule')->getAllSalesrules();
    }
    
    
    
    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }


    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }
    

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function getSeleniumKey() {
    	return "[selenium]";
    }
}