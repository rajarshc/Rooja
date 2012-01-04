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
	
	public function fixTransferOptimizationAction()
	{
		echo "<pre>";
		echo "Patching database.  Please wait...\n";
		flush();
		$table_prefix = Mage::getConfig()->getTablePrefix();
	
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');

		try {
			$query = "ALTER TABLE `{$table_prefix}rewards_customer_index_points` ADD COLUMN `customer_points_pending_event` INT( 11 ) NOT NULL;";
			$result = $db->query($query);
		} catch (Exception $e){
			echo ($e->getMessage() . "<br/>");
		}

		try {
			$query = "ALTER TABLE `{$table_prefix}rewards_customer_index_points` ADD COLUMN `customer_points_pending_time` INT( 11 ) NOT NULL;";
			$result = $db->query($query);
		} catch (Exception $e){
			echo ($e->getMessage() . "<br/>");
		}
		
		try {		
			$query = "ALTER TABLE `{$table_prefix}rewards_customer_index_points` ADD COLUMN `customer_points_pending_approval` INT( 11 ) NOT NULL;";
			$result = $db->query($query);			
		} catch (Exception $e){
			echo ($e->getMessage() . "<br/>");
		}
			
		try {		
			$query = "ALTER TABLE `{$table_prefix}rewards_customer_index_points` ADD COLUMN `customer_points_active` INT( 11 ) NOT NULL;";
			$result = $db->query($query);
		} catch (Exception $e){
			echo ($e->getMessage() . "<br/>");
		}
		
		try {
			$result = $db->addConstraint('FK_TRANSFER_CUSTOMER_ID', "{$table_prefix}rewards_transfer", 'customer_id', "{$table_prefix}customer_entity_varchar", 'entity_id', 'cascade', 'cascade');
		} catch (Exception $e){
			echo ($e->getMessage() . "<br/>");
		}

		Mage::helper( 'rewards/customer_points_index' )->invalidate();
		
		echo "</pre>";
		echo "<h3>DONE</h3>";
		flush();
		return $this;
	}	

}