<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2011 WDCA (http://www.wdca.ca)
 */

/**
 * Rewards social debug index Controller
 *
 * @category   TBT
 * @package    TBT_Rewardssocial
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
require_once("app/code/community/TBT/Rewards/controllers/Debug/AbstractController.php");

class TBT_Rewardssocial_Debug_IndexController extends TBT_Rewards_Debug_AbstractController
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
    
    public function likeSomethingAction()
    {
        $fbAcct = $this->getRequest()->has('fbAcct') ? $this->getRequest()->get('fbAcct') : '1234';
        $url = $this->getRequest()->has('url') ? $this->getRequest()->get('url') : 'http://my.domain.tld' . rand(0, 10000);
        
        Mage::dispatchEvent('rewardssocial_facebook_like_action',
            array(
                'facebook_account_id' => $fbAcct,
                'liked_url' => $url
            )
        );
        
        return $this;
    }
    
    public function vestPointsAction()
    {
        Mage::getSingleton('rewards/observer_cron')->checkPointsProbation(new Varien_Object());
        return $this;
    }
    
    protected function clearCacheAction()
    {
        Mage::app()->getCacheInstance()->flush();
        echo "Cache has been cleared.";
        return $this;
    }
    /**
     * @todo move out
     */
    public function parsePageSignedRequest () {
        if (isset($_REQUEST['signed_request'])) {
            $encoded_sig = null;
            $payload = null;
            list ($encoded_sig, $payload) = explode('.', 
            $_REQUEST['signed_request'], 2);
            $sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
            $data = json_decode(
            base64_decode(strtr($payload, '-_', '+/'), true));
            return $data;
        }
        return false;
    }
    public function checkIsFan2Action () {
        $fb = Mage::getSingleton('rewardsfb/facebook');
        $logoutUrl = $fb->getLogoutUrl();
        die($logoutUrl);
        $this->getResponse()->setRedirect($logoutUrl);
        return $this;
    }
    
      
    public function checkIsFanAction() {
        $fb = Mage::getSingleton('rewardsfb/facebook');
        $user = $fb->getUser();
        if(!$user) {
          $loginUrl = $fb->getLoginUrl();
          $this->getResponse()->setRedirect($loginUrl);
          return $this;
        }
        $likeID = $fb->api(array( 'method' => 'fql.query', 'query' => /*
        	"SELECT source_id, target_type, target_id, updated_time 
        	FROM connection WHERE source_id = {$user} and target_type = 'page' 
        	ORDER BY updated_time DESC 
        	LIMIT 100  " */
        	"SELECT object_id, post_id, user_id 
        	FROM like 
        	WHERE user_id = {$user}
        	LIMIT 100  " 
        ));
        
        
        var_dump($likeID);
        
        var_dump($user);
        
        die();
    }
}
