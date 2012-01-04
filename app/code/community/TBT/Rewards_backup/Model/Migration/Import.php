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
 * Sales Rule Rule
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Migration_Import extends Varien_Object {
	const DATA_CATALOGRULE_RULE = TBT_Rewards_Model_Migration_Export::DATA_CATALOGRULE_RULE;
	const DATA_SALESRULE_RULE = TBT_Rewards_Model_Migration_Export::DATA_SALESRULE_RULE;
	const DATA_SPECIAL_RULE = TBT_Rewards_Model_Migration_Export::DATA_SPECIAL_RULE;
	const DATA_CURRENCY = TBT_Rewards_Model_Migration_Export::DATA_CURRENCY;
	const DATA_CONFIG = TBT_Rewards_Model_Migration_Export::DATA_CONFIG;
	const EXT = TBT_Rewards_Model_Migration_Export::EXT;
	
	public function importFromFile($filename) {
		$sinput = file_get_contents ( $filename );
		return $this->importFromSerializedData ( $sinput );
	}
	
	public function importFromSerializedData($data) {
		$input = unserialize ( $data );
		return $this->importFromData ( $input );
	}
	
	public function importFromData($data) {
		if (isset ( $data [self::DATA_CATALOGRULE_RULE] )) {
			$this->importAllCatalogruleRuleData ( $data [self::DATA_CATALOGRULE_RULE] );
		}
		if (isset ( $data [self::DATA_SALESRULE_RULE] )) {
			$this->importAllSalesruleRuleData ( $data [self::DATA_SALESRULE_RULE] );
		}
		if (isset ( $data [self::DATA_SPECIAL_RULE] )) {
			$this->importAllSpecialRuleData ( $data [self::DATA_SPECIAL_RULE] );
		}
		if (isset ( $data [self::DATA_CURRENCY] )) {
			$this->importCurrencyData ( $data [self::DATA_CURRENCY] );
		}
		if (isset ( $data [self::DATA_CONFIG] )) {
			$this->importConfigData ( $data [self::DATA_CONFIG] );
		}
		return $this;
	
		//saveConfig($path, $value,
	}
	
	public function importAllCatalogruleRuleData($rules_data) {
		return $this->importModelData ( $rules_data, 'rewards/catalogrule_rule' );
	}
	
	public function importAllSalesruleRuleData($rules_data) {
		return $this->importModelData ( $rules_data, 'rewards/salesrule_rule' );
	}
	
	public function importAllSpecialRuleData($rules_data) {
		return $this->importModelData ( $rules_data, 'rewards/special' );
	}
	
	public function importCurrencyData($curencies_data) {
		return $this->importModelData ( $curencies_data, 'rewards/currency' );
	}
	
	public function importModelData($models_data, $model_key) {
		$m = Mage::getModel ( $model_key );
		foreach ( $models_data as $md ) {
			
			$m = Mage::getModel ( $model_key );
			if ($m->getWebsiteIds ()) {
				$m->setWebsiteIds ( implode ( ",", $m->getWebsiteIds () ) );
			}
			$m = Mage::getModel ( $model_key )->setData ( $md );
			$m->saveWithId ();
		}
		return $this;
	}
	
	public function importRewardsConfigData() {
		return $this->getConfigData ( 'rewards' );
	}
	
	public function importConfigData($data) {
		$config_table = Mage::getConfig ()->getTablePrefix () . "core_config_data";
		$write = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
		foreach ( $data as $data_row ) {
			$select = $write->insert ( $config_table, $data_row );
		}
		return $this;
	}
	
	public function importPoints($filename) {
		
		/* Local Variables */
		$hasError = false;
		$errorMsg = "";
		$line = 0;
		
		/* Store indices of titles on first line of csv file */
		$NUMBER_OF_POINTS_COLUMN_INDEX = - 1;
		$CUSTOMER_ID_COLUMN_INDEX = - 1;
		$CUSTOMER_EMAIL_COLUMN_INDEX = - 1;
		$WEBSITE_ID_INDEX = - 1;
		
		/* Open file handle and read csv file line by line separating comma delaminated values */
		$handle = fopen ( $filename, "r" );
		
		while ( ($data = fgetcsv ( $handle, 1000, "," )) !== FALSE ) {
			if ($line == 0) {
				// This is the first line of the csv file. It usually contains titles of columns
				// Next iteration will propagate to "else" statement and increment to line 2 immediately		    	
				$line = 1;
				
				/* Read in column headers and save indices if they appear */
				$num = count ( $data );
				for($index = 0; $index < $num; $index ++) {
					$columnTitle = trim ( strtolower ( $data [$index] ) );
					if ($columnTitle === "customer_id") {
						$CUSTOMER_ID_COLUMN_INDEX = $index;
					}
					if ($columnTitle === "points_amount") {
						$NUMBER_OF_POINTS_COLUMN_INDEX = $index;
					}
					if ($columnTitle === "customer_email") {
						$CUSTOMER_EMAIL_COLUMN_INDEX = $index;
					}
					if ($columnTitle === "website_id") {
						$WEBSITE_ID_INDEX = $index;
					}
				}
				
				/* Terminate if no customer identifier column found */
				if ($CUSTOMER_EMAIL_COLUMN_INDEX == - 1 && $CUSTOMER_ID_COLUMN_INDEX == - 1) {
					Mage::throwException ( Mage::helper ( 'rewards' )->__ ( "Error on line" ) . " " . $line . ": " . Mage::helper ( 'rewards' )->__ ( "No customer identifier in CSV file. Please check the contents of the file." ) );
				}
				
				/* Terminate if no points column found */
				if ($NUMBER_OF_POINTS_COLUMN_INDEX == - 1) {
					Mage::throwException ( Mage::helper ( 'rewards' )->__ ( "Error on line" ) . " " . $line . ": " . Mage::helper ( 'rewards' )->__ ( "No identifier for \"points_amount\" in CSV file. Please check the contents of the file." ) );
				}
			} else {
				try {
					$line ++;
					// This handles the rest of the lines of the csv file		    		
					

					/* Prepare line data based on values provided */
					$num = count ( $data );
					$num_points = $data [$NUMBER_OF_POINTS_COLUMN_INDEX];
					$custId = null;
					$cusEmail = null;
					$websiteId = null;
					
					if ($WEBSITE_ID_INDEX != - 1) {
						$websiteId = array_key_exists ( $WEBSITE_ID_INDEX, $data ) ? $data [$WEBSITE_ID_INDEX] : null;
					}
					if ($CUSTOMER_EMAIL_COLUMN_INDEX != - 1) {
						$cusEmail = array_key_exists ( $CUSTOMER_EMAIL_COLUMN_INDEX, $data ) ? $data [$CUSTOMER_EMAIL_COLUMN_INDEX] : null; // customer email.
					}
					if ($CUSTOMER_ID_COLUMN_INDEX != - 1) {
						$custId = array_key_exists ( $CUSTOMER_ID_COLUMN_INDEX, $data ) ? $data [$CUSTOMER_ID_COLUMN_INDEX] : null; // customer id.				
					} else {
						// If no customer_id provided, try finding the id by their email
						// Customer email is website dependent. Either load deafult website or look at website ID provided in file 
						if ($websiteId == null) {
							$websiteId = Mage::app ()->getDefaultStoreView ()->getWebsiteId ();
						} else {
							$websiteId = Mage::app ()->getWebsite ( $websiteId )->getId ();
						}
						$custId = Mage::getModel ( 'customer/customer' )->setWebsiteId ( $websiteId )->loadByEmail ( $cusEmail )->getId ();
						if (empty ( $custId )) {
							$hasError = true;
							$errorMsg .= "- " . Mage::helper ( 'rewards' )->__ ( "Error on line" ) . " " . $line . ": " . Mage::helper ( 'rewards' )->__ ( "Customer with email" ) . " \"" . $cusEmail . "\" " . Mage::helper ( 'rewards' )->__ ( "was not found in website with id #" ) . $websiteId . ".\n";
							continue;
						}
					}
					// Make sure customer_id provided is actually valid
					if (Mage::getModel ( 'customer/customer' )->load ( $custId )->getId () == null) {
						$hasError = true;
						$errorMsg .= "- " . Mage::helper ( 'rewards' )->__ ( "Error on line" ) . " " . $line . ": " . Mage::helper ( 'rewards' )->__ ( "Customer with id #" ) . $custId . " " . Mage::helper ( 'rewards' )->__ ( "was not found." ) . "\n";
						continue;
					}
					
					/* Start Import */
					//Load in transfer model
					$transfer = Mage::getModel ( 'rewards/transfer' );
					
					//Load it up with information
					$transfer->setId ( null )->setCurrencyId ( 1 )->// in versions of sweet tooth 1.0-1.2 this should be set to "1"
setQuantity ( $num_points )->// number of points to transfer.  This number can be negative or positive, but not zero
setCustomerId ( $custId )->// the id of the customer that these points will be going out to
setComments ( Mage::helper ( 'rewards' )->__ ( "Points transferred from CSV file" ) ); //This is optional
					// Checks to make sure you can actually move the transfer into the new status
					// STATUS_APPROVED would transfer the points in the approved status to the customer
					if ($transfer->setStatus ( null, TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED )) {
						$transfer->save (); //Save everything and execute the transfer
					}
					
					// Keep a record in system log
					Mage::log ( "Successfully imported points data on line " . $line . " for following customer:" . "\n\tcustId: " . $custId . "\n\tcusEmail: " . $cusEmail . "\n\twebsiteId: " . $websiteId . "\n\tnum_points: " . $num_points . "\n" );
				} catch ( Exception $e ) {
					// Any other errors which happen on each line should be saved and reported at the very end
					Mage::logException ( $e );
					$hasError = true;
					$errorMsg .= "- " . Mage::helper ( 'rewards' )->__ ( "Error on line" ) . " " . $line . ": " . $e->getMessage () . "\n";
				}
			}
		}
		
		fclose ( $handle );
		if ($hasError) {
			// If there were any errors saved, now's the time to report them
			Mage::throwException ( Mage::helper ( 'rewards' )->__ ( "Points were imported with the following errors:" ) . "\n" . $errorMsg );
		}
		return $this;
	}

}