<?php

/**
 * @nelkaake 22/01/2010 3:54:41 AM : points expiry
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */

require_once("AbstractController.php");
class TBT_Rewards_Debug_IndexController extends TBT_Rewards_Debug_AbstractController
{
    
    public function indexAction()
    {
        echo "<h2>This tests installation things. </h2>";
        echo "<a href='". Mage::getUrl('rewards/debug_install/reinstallRewardsDb') ."'>Reinstall Core ST DB</a> - Forces DB re-install. <BR />";
        echo "<a href='". Mage::getUrl('rewards/debug_index/clearCache') ."'>Flush All Cache</a>. <BR />";
        
        
        echo "<h2>This tests points expiry </h2>";
        echo "<a href='". Mage::getUrl('rewards/debug_expiry/testCron') ."'>Run Daily Cron Expiry Check</a> - This will send out any e-mails, write to any logs, expire any points, etc. <BR />";
        echo "<a href='". Mage::getUrl('rewards/debug_expiry/expirePoints') ."'>VIEW points balance expiry info for customer id #1 </a> (or customer id specified as customer_id in the url param)<BR />";
        
        
        echo "<h2>This tests points expiry </h2>";
        echo "<a href='". Mage::getUrl('rewards/debug_promo_rule/disableAllCartRules') ."'>Disable all Rewards SHOPPING CART Rules</a> <BR />";
        echo "<a href='". Mage::getUrl('rewards/debug_promo_rule/disableAllCatalogRules') ."'>Disable all Rewards CATALOG Rules</a> <BR />";
        echo "<a href='". Mage::getUrl('rewards/debug_promo_rule/disableAllRules') ."'>Disable ALL Rules</a> <BR />";
        echo "<a href='". Mage::getUrl('rewards/debug_promo_rule/deleteSeleniumRules') ."'>Delete all SELENIUM test rules</a> - Assuming [selenium] in the rule name.<BR />";
        
        exit;
    }



    protected function clearCacheAction() {
        
        Mage::app()->getCacheInstance()->flush();
        echo "Cache has been cleared.";
    	return $this;
    	
    }
    
    
}