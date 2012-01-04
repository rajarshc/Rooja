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
 * TBT_Rewards_Helper_Expiry
 * <email_template>rewards_default_expiry</email_template>
  <email_identity>pointsexpiry</email_identity>
  <delay_days>5</delay_days>
  <email_warning1_days>7</email_before_days>
  <email_warning2_days>2</email_before_days>
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Expiry extends Mage_Core_Helper_Abstract {
	
	/**
	 * @nelkaake 31/01/2010 8:01:06 PM : 
	 * @return string
	 */
	public function isEnabled($storeId) {
		$enabled = Mage::getStoreConfigFlag ( "rewards/expire/is_enabled", $storeId );
		return $enabled;
	}
	
	/**
	 * @nelkaake 31/01/2010 8:01:06 PM : 
	 * @return string
	 */
	public function getExpiryMsg($storeId) {
		$days = $this->getDelayDays ( $storeId );
		$msg = $this->__ ( Mage::getStoreConfig ( "rewards/transferComments/expired", $storeId ), $days );
		return $msg;
	}
	
	public function getDelayDays($storeId) {
		return ( int ) Mage::getStoreConfig ( "rewards/expire/delay_days", $storeId );
	}
	
	public function getWarning1Days($storeId) {
		return ( int ) Mage::getStoreConfig ( "rewards/expire/email_warning1_days", $storeId );
	}
	
	public function getWarning1EmailTemplate($storeId) {
		return Mage::getStoreConfig ( "rewards/expire/email_warning2_template", $storeId );
	}
	
	public function getWarning2EmailTemplate($storeId) {
		return Mage::getStoreConfig ( "rewards/expire/email_warning2_template", $storeId );
	}
	
	public function getWarning2Days($storeId) {
		return ( int ) Mage::getStoreConfig ( "rewards/expire/email_warning2_days", $storeId );
	}
	
	public function getSenderName($storeId) {
		return Mage::getStoreConfig ( "trans_email/ident_support/name", $storeId );
	}
	
	public function getSenderEmail($storeId) {
		return Mage::getStoreConfig ( "trans_email/ident_support/email", $storeId );
	}
	
	public function doLogExpiry($storeId) {
		return Mage::getStoreConfigFlag ( "rewards/expire/log_when_points_expire", $storeId );
	}
	
	public function logExpiry($customer, $amount_expired, $time_since_str) {
		$storeId = $customer->getStoreId ();
		if ($this->doLogExpiry ( $storeId )) {
			$a = $amount_expired; //@nelkaake this abcd is just for code cleanliness purposes
			$b = $customer->getName ();
			$c = $customer->getEmail ();
			$d = $time_since_str;
			$msg = $this->__ ( "The balance of %s for the customer %s with the e-mail %s has expired.  It was %s since his/her last activity.", $a, $b, $c, $d );
			Mage::log ( $msg, null, $this->getExpiryLogFile () );
		}
	}
	
	public function logExpiryNotification($customer, $days_left) {
		$storeId = $customer->getStoreId ();
		if ($this->doLogExpiry ( $storeId )) {
			$a = $customer->getName ();
			$b = $customer->getEmail ();
			$c = $days_left;
			$msg = $this->__ ( "The customer %s with the e-mail %s was sent notification that his/her points will expire in %s days.", $a, $b, $c );
			Mage::log ( $msg, null, $this->getExpiryLogFile () );
		}
	}
	
	public function getExpiryLogFile() {
		return 'rewards_expire.log';
	}

}