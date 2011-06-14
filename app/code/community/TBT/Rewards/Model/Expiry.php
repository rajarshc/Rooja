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
 * Handler for points expiry process.
 * @nelkaake 31/01/2010 11:09:01 PM 
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Expiry extends Varien_Object {
	const SECS_IN_DAY = 86400;
	const SECS_IN_HOUR = 3600;
	const SECS_IN_MIN = 60;
	
	/**
	 * Checks all customers for points balance expiries.  IF points have expired,
	 * their points balance will be nullified.
	 *
	 */
	public function checkAllCustomers() {
		$customers = Mage::getModel ( 'rewards/customer' )->getCollection ();
		foreach ( $customers as $c ) {
			if (! Mage::helper ( 'rewards/expiry' )->isEnabled ( $c->getStoreId () ))
				continue;
			$c = Mage::getModel ( 'rewards/customer' )->load ( $c->getId () );
			$this->checkExpirePoints ( $c );
		}
	}
	
	/**
	 * Checks if there are points to expire for each and every customer then
	 * expires the points if need be.
	 */
	public function checkExpirePoints($c) {
		if (! $c->getId ())
			return $this;
		if (! Mage::helper ( 'rewards/expiry' )->isEnabled ( $c->getStoreId () ))
			return $this;
		if ($c->hasPoints ()) {
			$last_date_str = $c->getLatestActivityDate ();
			if ($last_date_str == null) {
				$last_date_str = 0;
			}
			$last_date = strtotime ( $last_date_str ) - 1;
			$last_date_d = $this->getNumDays ( $last_date ) * self::SECS_IN_DAY;
			$current_date = strtotime ( Mage::helper ( 'rewards' )->now ( false ) );
			
			$expiry_delay = Mage::helper ( 'rewards/expiry' )->getDelayDays ( $c->getStoreId () ) * self::SECS_IN_DAY;
			$time_since = $current_date - $last_date_d;
			$day_time_since = $this->getNumDays ( $time_since ) * self::SECS_IN_DAY;
			
			if ($time_since >= $expiry_delay) {
				$old_balance = $c->getPointsSummary ();
				$c->expireAllPoints ();
				$c->load ( $c->getId () ); // to refresh points balance
				Mage::helper ( 'rewards/expiry' )->logExpiry ( $c, $old_balance, $time_since );
			} else {
				$expires_in = $expiry_delay - $time_since;
				$expires_in_days = $this->getNumDays ( $expires_in );
				$expires_on = $current_date + $expires_in;
				$expires_on_date = Mage::helper ( 'core' )->formatDate ( new Zend_Date ( $expires_on, Zend_Date::TIMESTAMP ) );
				$this->checkNotifications ( $c, $expires_in_days );
			
		//die("Points didn't expire yet. <BR />points expire on $expires_on_date <BR />$expires_in_days days left");
			}
		}
		return $this;
	}
	
	/**
	 * Checks if there are points to expire for each and every customer then
	 * expires the points if need be.
	 * @return null if customer doesn't exist, points are expired, or the customer doesn't have points; number of days otherwise 	  
	 */
	public function getDaysUntilExpiry($c) {
		$expires_in = $this->getTimeLeftToExpire ( $c );
		if ($expires_in) {
			$expires_in_days = $this->getNumDays ( $expires_in );
			return $expires_in_days + 1;
		}
		return null;
	}
	
	/**
	 * Checks if there are points to expire for each and every customer then
	 * expires the points if need be.
	 * @return null if customer doesn't exist, points are expired, or the customer doesn't have points; date otherwise	  
	 */
	public function getExpiryDate($c) {
		$expires_in = $this->getTimeLeftToExpire ( $c );
		if ($expires_in) {
			$expires_on = $current_date + $expires_in;
			$current_date = strtotime ( Mage::helper ( 'rewards' )->now ( false ) );
			$expires_on_date = Mage::helper ( 'core' )->formatDate ( new Zend_Date ( $expires_on, Zend_Date::TIMESTAMP ) );
			return $expires_in_days + 1;
		}
		return null;
	}
	
	/**
	 * Checks if there are points to expire for each and every customer then
	 * expires the points if need be.
	 * @return null if customer doesn't exist, points are expired, or the customer doesn't have points; number of seconds otherwise 	  
	 */
	public function getTimeLeftToExpire($c) {
		if (! $c->getId ())
			return null;
		if (! Mage::helper ( 'rewards/expiry' )->isEnabled ( $c->getStoreId () ))
			return null;
		if ($c->hasPoints ()) {
			$last_date_str = $c->getLatestActivityDate ();
			if ($last_date_str == null) {
				$last_date_str = 0;
			}
			$last_date = strtotime ( $last_date_str ) - 1;
			$last_date_d = $this->getNumDays ( $last_date ) * self::SECS_IN_DAY;
			$current_date = strtotime ( Mage::helper ( 'rewards' )->now ( false ) );
			
			$expiry_delay = Mage::helper ( 'rewards/expiry' )->getDelayDays ( $c->getStoreId () ) * self::SECS_IN_DAY;
			$time_since = $current_date - $last_date_d;
			$day_time_since = $this->getNumDays ( $time_since ) * self::SECS_IN_DAY;
			
			if ($time_since < $expiry_delay) {
				$expires_in = $expiry_delay - $time_since;
				return $expires_in;
			}
		} else {
			return null;
		}
		return null;
	}
	
	/**
	 * Checks to see if there are notifications that need to be sent.  If there are, 
	 * those notifications are setn and a log is written.
	 *
	 * @param TBT_Rewards_Model_Customer $c
	 * @param int $expires_in_days
	 */
	public function checkNotifications($c, $expires_in_days) {
		if ($expires_in_days == Mage::helper ( 'rewards/expiry' )->getWarning1Days ( $c->getStoreId () )) {
			$template = Mage::helper ( 'rewards/expiry' )->getWarning1EmailTemplate ( $c->getStoreId () );
			if ($template) {
				$expires_in_days = Mage::helper ( 'rewards/expiry' )->getWarning1Days ( $c->getStoreId () );
				$this->sendWarningEmail ( $c, $template, $expires_in_days );
				Mage::helper ( 'rewards/expiry' )->logExpiryNotification ( $c, $expires_in_days );
			}
		}
		if ($expires_in_days == Mage::helper ( 'rewards/expiry' )->getWarning2Days ( $c->getStoreId () )) {
			$template = Mage::helper ( 'rewards/expiry' )->getWarning2EmailTemplate ( $c->getStoreId () );
			if ($template) {
				$expires_in_days = Mage::helper ( 'rewards/expiry' )->getWarning2Days ( $c->getStoreId () );
				$this->sendWarningEmail ( $c, $template, $expires_in_days );
				Mage::helper ( 'rewards/expiry' )->logExpiryNotification ( $c, $expires_in_days );
			}
		}
	}
	
	/**
	 * Sends a warning e-mail that points balance will expire in [$expires_in_days]
	 * days t oa given customer
	 *
	 * @param TBT_Rewards_Model_Customer $parent
	 * @param unknown_type $template
	 * @param int $expires_in_days
	 * @return boolean send successful?
	 */
	public function sendWarningEmail($parent, $template, $expires_in_days) {
		$translate = Mage::getSingleton ( 'core/translate' ); /* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline ( false );
		
		$email = Mage::getModel ( 'core/email_template' ); /* @var $email Mage_Core_Model_Email_Template */
		$sender = array ('name' => strip_tags ( Mage::helper ( 'rewards/expiry' )->getSenderName ( $parent->getStoreId () ) ), 'email' => strip_tags ( Mage::helper ( 'rewards/expiry' )->getSenderEmail ( $parent->getStoreId () ) ) );
		
		$email->setDesignConfig ( array ('area' => 'frontend', 'store' => $parent->getStoreId () ) );
		$vars = array ('customer_name' => $parent->getName (), 'customer_email' => $parent->getEmail (), 'store_name' => $parent->getStore ()->getName (), 'days_left' => $expires_in_days, 'points_balance' => ( string ) $parent->getPointsSummary () );
		$email->sendTransactional ( $template, $sender, $parent->getEmail (), $parent->getName (), $vars );
		
		$translate->setTranslateInline ( true );
		
		return $email->getSentSuccess ();
	}
	
	public function getNumDays($secs) {
		$days = floor ( $secs / self::SECS_IN_DAY );
		return $days;
	}
	
	public function getRemainingTime($secs) {
		$days = floor ( $secs / self::SECS_IN_DAY );
		$secs = $secs % self::SECS_IN_DAY;
		$hours = floor ( $secs / self::SECS_IN_HOUR );
		$secs = $secs % self::SECS_IN_HOUR;
		$minutes = floor ( $secs / self::SECS_IN_MIN );
		$secs = $secs % self::SECS_IN_MIN;
		return array ($days, $hours, $minutes, $secs );
	}

}