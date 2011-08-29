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
 * Transfer Reason
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Transfer_Reason extends Varien_Object {
	// status values less than 1 means that transfer is ignored in
	// customer point calculations.
	const REASON_CUSTOMER_REDEMPTION = - 1;
	const REASON_CUSTOMER_DISTRIBUTION = 1;
	const REASON_SYSTEM_ADJUSTMENT = 2;
	const REASON_FROM_CUSTOMER = 3;
	const REASON_TO_CUSTOMER = 4;
	const REASON_SYSTEM_REVOKED = 5;
	const REASON_ADMIN_ADJUSTMENT = 6;
	const REASON_UNSPECIFIED = 0;
	
	public function getDistributionReasons() {
		$base_reasons = array (self::REASON_CUSTOMER_DISTRIBUTION => Mage::helper ( 'rewards' )->__ ( 'Points Distribution' ) );
		$base_reasons += $this->_getTypes ()->getDistributionReasons ();
		return $base_reasons;
	}
	
	public function getRedemptionReasons() {
		$base_reasons = array (self::REASON_CUSTOMER_REDEMPTION => Mage::helper ( 'rewards' )->__ ( 'Points Redeemed on Order' ) );
		$base_reasons += $this->_getTypes ()->getRedemptionReasons ();
		return $base_reasons;
	}
	
	public function getOtherReasons() {
		$base_reasons = array (self::REASON_SYSTEM_ADJUSTMENT => Mage::helper ( 'rewards' )->__ ( 'System Adjustment' ), self::REASON_FROM_CUSTOMER => Mage::helper ( 'rewards' )->__ ( 'Points Received From a Friend' ), self::REASON_TO_CUSTOMER => Mage::helper ( 'rewards' )->__ ( 'Points Given To a Friend' ), self::REASON_SYSTEM_REVOKED => Mage::helper ( 'rewards' )->__ ( 'Points Revoked' ), self::REASON_ADMIN_ADJUSTMENT => Mage::helper ( 'rewards' )->__ ( 'Administrative Adjustment' ), self::REASON_UNSPECIFIED => Mage::helper ( 'rewards' )->__ ( 'Unspecified Reason' ) );
		$base_reasons += $this->_getTypes ()->getOtherReasons ();
		return $base_reasons;
	}
	
	public function getDistributionReasonIds() {
		return array_keys ( $this->getDistributionReasons () );
	}
	
	public function getRedemptionReasonIds() {
		return array_keys ( $this->getRedemptionReasons () );
	}
	
	public function getOtherReasonIds() {
		return array_keys ( $this->getOtherReasons () );
	}
	
	public function getOptionArray() {
		$base_reasons = array (self::REASON_SYSTEM_ADJUSTMENT => Mage::helper ( 'rewards' )->__ ( 'System Adjustment' ), self::REASON_CUSTOMER_REDEMPTION => Mage::helper ( 'rewards' )->__ ( 'Points Redeemed on Order' ), self::REASON_CUSTOMER_DISTRIBUTION => Mage::helper ( 'rewards' )->__ ( 'Points Distribution' ), self::REASON_FROM_CUSTOMER => Mage::helper ( 'rewards' )->__ ( 'Points Received From a Friend' ), self::REASON_TO_CUSTOMER => Mage::helper ( 'rewards' )->__ ( 'Points Given To a Friend' ), self::REASON_SYSTEM_REVOKED => Mage::helper ( 'rewards' )->__ ( 'Points Revoked' ), self::REASON_ADMIN_ADJUSTMENT => Mage::helper ( 'rewards' )->__ ( 'Administrative Adjustment' ), self::REASON_UNSPECIFIED => Mage::helper ( 'rewards' )->__ ( 'Unspecified Reason' ) );
		$base_reasons += $this->_getTypes ()->getAllReasons ();
		return $base_reasons;
	}
	
	public function getManualReasons() {
		$base_reasons = $this->getAvailReasons ( self::REASON_ADMIN_ADJUSTMENT );
		$base_reasons += $this->_getTypes ()->getManualReasons ();
		return $base_reasons;
	}
	
	public function getAvailReasons($current_reason) {
		/*
          switch($current_reason) {
          case self::REASON_SYSTEM_ADJUSTMENT:
          $availR = array(self::REASON_SYSTEM_ADJUSTMENT);
          break;
          case self::REASON_CUSTOMER_REDEMPTION:
          $availR = array(self::REASON_CUSTOMER_REDEMPTION);
          break;
          case self::REASON_CUSTOMER_DISTRIBUTION:
          $availR = array(self::REASON_CUSTOMER_DISTRIBUTION);
          break;
          case self::REASON_TO_CUSTOMER:
          $availR = array(self::REASON_TO_CUSTOMER);
          break;
          case self::REASON_FROM_CUSTOMER:
          $availR = array(self::REASON_FROM_CUSTOMER);
          break;
          case self::REASON_SYSTEM_REVOKED:
          $availR = array(self::REASON_SYSTEM_REVOKED);
          break;
          case self::REASON_ADMIN_ADJUSTMENT:
          $availR = array(self::REASON_ADMIN_ADJUSTMENT);
          break;
          case self::REASON_UNSPECIFIED:
          $availR = array(self::REASON_UNSPECIFIED);
          break;
          default:
          $availR = array(self::REASON_UNSPECIFIED);
          }
         */
		
		$availR = array ($current_reason );
		//@nelkaake Added on Sunday August 15, 2010: Removed & reference to fix bug #335 in Mantis
		$availR = $this->_getTypes ()->getAvailReasons ( $current_reason, $availR );
		
		$allR = $this->getOptionArray ();
		$ret = array ();
		foreach ( $availR as $r ) {
			$ret [$r] = $allR [$r];
		}
		return $ret;
	}
	
	protected function _getTypes() {
		return Mage::getSingleton ( 'rewards/transfer_types' );
	}

}