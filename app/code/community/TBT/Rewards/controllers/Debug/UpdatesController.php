<?php

/**
 * @nelkaake 22/01/2010 3:54:41 AM : points expiry
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */

require_once ("AbstractController.php");
class TBT_Rewards_Debug_UpdatesController extends TBT_Rewards_Debug_AbstractController {
	
	public function indexAction() {
		echo "<h2>This tests update checking </h2>";

		echo "<a href='" . Mage::getUrl ( 'rewards/debug_updates/testCron' ) . "'> Check for updates </a> <BR />";
		
		exit ();
	}
	
	public function testCronAction() {
        if(!Mage::getStoreConfigFlag('rewards/general/cfu')) {
            return $this;
        }
        Mage::helper ( 'rewards/loyalty_checker' )->checkForUpdates (  );
        
		return $this;
	}
	

}