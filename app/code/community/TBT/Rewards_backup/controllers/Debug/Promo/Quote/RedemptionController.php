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
class TBT_Rewards_Debug_Promo_Quote_RedemptionController extends TBT_Rewards_Debug_AbstractController
{

    public function indexAction() {
        echo "<h2>This tests points expiry </h2>";
        $this->_displayTestCase('runSuite', 'Run cart redemption testing suite');
        $this->_displayTestCase('fixedpointsFixeddiscount', 'Fixed points spent + Fixed cart discount ');
        $this->_displayTestCase('bypointsspentFixeddiscount', 'By points spent + Fixed cart discount');
        $this->_displayTestCase('fixedpointsPercentdiscount', 'Fixed points spent + percent discount for whole cart');
    	return $this;
    }
    
    protected function _displayTestCase($action, $caption) {
        echo "<a href='". Mage::getUrl('rewards/debug_promo_quote_redemption/'. $action) ."'>{$caption}</a> <BR />";
        return $this;
    }
    
    public function runSuiteAction() {
    	$this->_getTest()->runSuite();
    	return $this;
    }
    
    public function fixedpointsFixeddiscountAction() {
    	$test1 = Mage::getModel('rewards/test_salesrule_redemption_fixedpoints_fixeddiscount')->runSuite();
    }

    public function bypointsspentFixeddiscountAction() {
    	$test2 = Mage::getModel('rewards/test_salesrule_redemption_bypointsspent_fixeddiscount')->runSuite();
    }
    
    public function fixedpointsPercentdiscountAction() {
    	$test3 = Mage::getModel('rewards/test_salesrule_redemption_fixedpoints_percentdiscount')->runSuite();
    }
    
    
    /**
     * @return TBT_Rewards_Model_Test_Salesrule_Redemption
     */
    protected function _getTest() {
    	return Mage::getSingleton('rewards/test_salesrule_redemption');
    }
    
    

}