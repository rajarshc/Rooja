<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
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
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer Send Points Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Manage_MigrationController extends Mage_Adminhtml_Controller_Action {
	
	private $total_migrated = 0;
	private $error_list;
	private $site_id;
	
	public function migrateAction() {
		Mage::getModel ( 'rewards/transfer' )->getResource ()->beginTransaction ();
		
		$this->total_migrated = 0;
		$this->error_list = array ();
		
		$server = $this->getRequest ()->get ( 'server' );
		$database = $this->getRequest ()->get ( 'database' );
		$user = $this->getRequest ()->get ( 'user' );
		$password = $this->getRequest ()->get ( 'pass' );
		$this->site_id = $this->getRequest ()->get ( 'site' );
		
		$db = new mysqli ( $server, $user, $password, $database );
		if ($db->connect_error) {
			die ( "There was an error connecting with the database" );
		}
		echo "Connected to database...<br>";
		
		$sql_query = 'SELECT customers_email_address,
			customers_shopping_points
			FROM customers
			WHERE customers_shopping_points != 0';
		
		echo "Running query...<br>";
		$result = $db->query ( $sql_query );
		
		while ( $row = $result->fetch_row () ) {
			if (isset ( $migration_list [$row [0]] )) {
				$this->error_list [] = "ERROR: " . $cust_email . " is a duplicate email<br>";
			}
			$migration_list [$row [0]] = $row [1];
			$this->createTransfer ( $row [0], $row [1], $site_id );
		}
		
		echo "<br><br> MIGRATION COMPLETE: ";
		echo $this->total_migrated . " customer point balances were migrated over<br><br>";
		
		echo "ERRORS:";
		if (count ( $this->error_list ) == 0) {
			echo " No errors.";
		} else {
			echo "<br>";
			foreach ( $this->error_list as $error ) {
				echo $error;
			}
		}
		$result->close ();
		$db->close ();
		
		Mage::getModel ( 'rewards/transfer' )->getResource ()->commit ();
	}
	
	private function createTransfer($cust_email, $num_points) {
		
		$cust = Mage::getModel ( 'rewards/customer' )->setWebsiteId ( $this->site_id )->loadByEmail ( $cust_email );
		if (! $cust) {
			echo "ERROR: " . $cust_email . " can not be loaded<br>";
			$this->error_list [] = "ERROR: " . $cust_email . " can not be loaded<br>";
		} else {
			echo "MIGRATED: " . $cust_email . " now has " . $num_points . " points<br>";
			$this->total_migrated ++;
			$transfer = Mage::getModel ( 'rewards/transfer' );
			
			$transfer->setId ( null )->setReasonId ( TBT_Rewards_Model_Transfer_Reason::REASON_SYSTEM_ADJUSTMENT )->setCustomerId ( $cust->getId () )->setCurrencyId ( 1 )->setQuantity ( round ( $num_points ) )->setStatus ( 5 )->setComments ( "Migrated from old database" );
			$transfer->save ();
		}
		flush ();
	}
	
	public function testAction() {
		$server = $this->getRequest ()->get ( 'server' );
		$database = $this->getRequest ()->get ( 'database' );
		$user = $this->getRequest ()->get ( 'user' );
		$password = $this->getRequest ()->get ( 'pass' );
		$this->site_id = $this->getRequest ()->get ( 'site' );
		
		$db = new mysqli ( $server, $user, $password, $database );
		if ($db->connect_error) {
			die ( "There was an error connecting with the database" );
		}
		
		echo "Server: " . $server . "<br>Database: " . $database . "<br>User: " . $user . "<br>Password: " . $password;
		
		echo "<br><br>Connected to database...<br>";
		
		$sql_query = 'SELECT customers_email_address,
			customers_shopping_points
			FROM customers
			WHERE customers_shopping_points != 0';
		
		echo "Running Test query...<br>";
		$result = $db->query ( $sql_query );
		
		while ( $row = $result->fetch_row () ) {
			if (isset ( $migration_list [$row [0]] )) {
				$this->error_list [] = "ERROR: " . $cust_email . " is a duplicate email<br>";
			}
			$migration_list [$row [0]] = $row [1];
			$cust = Mage::getModel ( 'rewards/customer' )->setWebsiteId ( $this->site_id )->loadByEmail ( $cust_email );
			if (! $cust) {
				echo "ERROR: " . $cust_email . " can not be loaded<br>";
				$this->error_list [] = "ERROR: " . $cust_email . " can not be loaded<br>";
			} else {
				echo "TESTED: " . $cust_email . " will be migrated with " . $num_points . " points<br>";
				$this->total_migrated ++;
			}
			flush ();
		}
		
		echo "<br><br> TEST COMPLETE: ";
		echo $this->total_migrated . " customer point balances were tested<br><br>";
		
		echo "ERRORS:";
		if (count ( $this->error_list ) == 0) {
			echo " No errors.";
		} else {
			echo "<br>";
			foreach ( $this->error_list as $error ) {
				echo $error;
			}
		}
		$result->close ();
		$db->close ();
	}
	
	public function exportallAction() {
		header ( "Content-type: application/txt" );
		//header("Content-Length: $len");
		$ts = Mage::app ()->getLocale ()->storeTimeStamp ();
		$filename = "rules_and_config_{$ts}.stcampaign";
		header ( "Content-Disposition: inline; filename=$filename" );
		$soutput = Mage::getModel ( 'rewards/migration_export' )->getSerializedFullExport ();
		$len = sizeof ( $soutput );
		print ($soutput) ;
		exit ();
	}
	
	public function exportcampaignAction() {
		header ( "Content-type: application/txt" );
		//header("Content-Length: $len");
		$filename = Mage::app ()->getLocale ()->storeTimeStamp () . ".stcampaign";
		header ( "Content-Disposition: inline; filename=$filename" );
		$soutput = Mage::getModel ( 'rewards/migration_export' )->getSerializedCampaignExport ();
		$len = sizeof ( $soutput );
		print ($soutput) ;
		exit ();
	}
	
	public function exportconfigAction() {
		header ( "Content-type: application/txt" );
		//header("Content-Length: $len");
		$filename = Mage::app ()->getLocale ()->storeTimeStamp () . ".stconfig";
		header ( "Content-Disposition: inline; filename=$filename" );
		$soutput = Mage::getModel ( 'rewards/migration_export' )->getSerializedConfigExport ();
		$len = sizeof ( $soutput );
		print ($soutput) ;
		exit ();
	}
	
	public function deleteallAction() {
		try {
			$backup_ser = Mage::getSingleton ( 'rewards/migration_export' )->getSerializedFullExport ();
			Mage::getSingleton ( 'rewards/migration_delete' )->deleteAll ();
			Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'adminhtml' )->__ ( 'All rules and settings were deleted.' ) );
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'rewards/migration_logger' )->log ( $e );
			Mage::getSingleton ( 'rewards/migration_import' )->importFromSerializedData ( $backup_ser );
			throw $e;
		}
		$this->_redirectReferer ();
	}
	
	public function revertconfigAction() {
		try {
			Mage::getSingleton ( 'rewards/migration_delete' )->deleteAllRewardsConfig ();
			Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'adminhtml' )->__ ( 'Config settings were reverted.' ) );
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'rewards/migration_logger' )->log ( $e );
			throw $e;
		}
		$this->_redirectReferer ();
	}

}

?>