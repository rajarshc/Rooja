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
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_System_Config_Backend_Import extends Mage_Core_Model_Config_Data {
	
	public function _afterSave() {
		$tmp_fn = $_FILES ["groups"] ["tmp_name"] ["migration"] ["fields"] ["importcampaign"] ["value"];
		if (! empty ( $tmp_fn )) {
			$this->importCampaign ( $tmp_fn );
		}
		
		$tmp_fn = $_FILES ["groups"] ["tmp_name"] ["migration"] ["fields"] ["importpoints"] ["value"];
		if (! empty ( $tmp_fn )) {
			$this->importPoints ( $tmp_fn );
		}
		
		//$tmp_fn = $_FILES["groups"]["tmp_name"]["migration"]["fields"]["importconfig"]["value"];
		//if(!empty($tmp_fn)) {
		//     $this->importConfig($tmp_fn);
		// }
		return parent::_afterSave ();
	}
	
	protected function importCampaign($tmp_fn) {
		try {
			$backup_ser = Mage::getSingleton ( 'rewards/migration_export' )->getSerializedFullExport ();
			
			Mage::getSingleton ( 'rewards/migration_delete' )->deleteAll ();
			
			Mage::getSingleton ( 'rewards/migration_import' )->importFromFile ( $tmp_fn );
			Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'All Sweet Tooth rules and settings were imported successfully.' ) );
			Mage::app ()->saveCache ( 1, 'catalog_rules_dirty' );
			Mage::getSingleton ( 'adminhtml/session' )->addNotice ( Mage::helper ( 'rewards' )->__ ( 'You must still APPLY rules before they will take effect!' ) );
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'rewards/migration_logger' )->log ( $e );
			Mage::getSingleton ( 'rewards/migration_import' )->importFromSerializedData ( $backup_ser );
			throw $e;
		}
		return $this;
	}
	
	protected function importConfig($tmp_fn) {
		try {
			$backup_ser = Mage::getSingleton ( 'rewards/migration_export' )->getSerializedConfigExport ();
			
			Mage::getSingleton ( 'rewards/migration_delete' )->deleteAllRewardsConfig ();
			
			Mage::getSingleton ( 'rewards/migration_import' )->importFromFile ( $tmp_fn );
			Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'Settings were imported successfully.' ) );
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'rewards/migration_logger' )->log ( $e );
			Mage::getSingleton ( 'rewards/migration_import' )->importFromSerializedData ( $backup_ser );
			throw $e;
		}
		return $this;
	}
	
	protected function importPoints($tmp_fn) {
		try {
			Mage::getSingleton ( 'rewards/migration_import' )->importPoints ( $tmp_fn );
			Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'Points were imported successfully.' ) );
		} catch ( Exception $e ) {
			$messages = explode ( "\n", $e->getMessage () );
			foreach ( $messages as $message ) {
				if (! empty ( $message )) {
					Mage::getSingleton ( 'adminhtml/session' )->addNotice ( Mage::helper ( 'rewards' )->__ ( $message ) );
				}
			}
			Mage::logException ( $e );
		}
		return $this;
	}

}