<?php

/**
 * @nelkaake 22/01/2010 3:54:41 AM : points expiry
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */

require_once ("AbstractController.php");
class TBT_Rewards_Debug_ExpiryController extends TBT_Rewards_Debug_AbstractController {
	const SECS_IN_DAY = 86400;
	const SECS_IN_HOUR = 3600;
	const SECS_IN_MIN = 60;
	
	public function indexAction() {
		echo "<h2>This tests points expiry </h2>";
		echo "<a href='" . Mage::getUrl ( 'rewards/debug_expiry/testCron' ) . "'>Run Daily Cron Expiry Check</a> - This will send out any e-mails, write to any logs, expire any points, etc. <BR />";
		echo "<a href='" . Mage::getUrl ( 'rewards/debug_expiry/expirePoints' ) . "'>VIEW points balance expiry info for customer id #1 </a> (or customer id specified as customer_id in the url param)<BR />";
		
		exit ();
	}
	
	public function testCronAction() {
		Mage::getSingleton ( 'rewards/observer_cron' )->checkPointsExpiry ( new Varien_Object () );
		return $this;
	}
	
	public function expirePointsAction() {
		// loads the customer id #1 by default or takes it from the parameter
		$customer_id = $this->getRequest ()->getParam ( 'customer_id', 1 );
		$c = Mage::getModel ( 'rewards/customer' )->load ( $customer_id );
		
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
			
			$time_since_str = $this->secstostr ( $time_since );
			
			if ($time_since >= $expiry_delay) {
				$c->expireAllPoints ();
				$c->load ( $c->getId () );
				die ( "points expired: $time_since_str since your last points change.  Balance: {$c->getPointsSummary()}" );
			} else {
				$expires_in = $expiry_delay - $time_since;
				$expires_in_days = $this->getNumDays ( $expires_in );
				$expires_in_str = $this->secstostr ( $expires_in );
				$expires_on = $current_date + $expires_in;
				$expires_on_date = Mage::helper ( 'core' )->formatDate ( new Zend_Date ( $expires_on, Zend_Date::TIMESTAMP ) );
				$this->checkNotifications ( $c, $expires_in_days );
				die ( "Points didn't expire yet. <BR />Last time spent points was $last_date_str <BR />Time Since last change: $time_since_str <BR />points expire on $expires_on_date <BR />$expires_in_days days left [$expires_in_str]" );
			}
		}
	}
	
	public function checkNotifications($c, $expires_in_days) {
		if ($expires_in_days == Mage::helper ( 'rewards/expiry' )->getWarning1Days ( $c->getStoreId () )) {
			$template = Mage::helper ( 'rewards/expiry' )->getWarning1EmailTemplate ( $c->getStoreId () );
			if ($template) {
				$expires_in_days = Mage::helper ( 'rewards/expiry' )->getWarning1Days ( $c->getStoreId () );
				$this->sendWarningEmail ( $c, $template, $expires_in_days );
			}
		}
		if ($expires_in_days == Mage::helper ( 'rewards/expiry' )->getWarning2Days ( $c->getStoreId () )) {
			$template = Mage::helper ( 'rewards/expiry' )->getWarning2EmailTemplate ( $c->getStoreId () );
			if ($template) {
				$expires_in_days = Mage::helper ( 'rewards/expiry' )->getWarning2Days ( $c->getStoreId () );
				$this->sendWarningEmail ( $c, $template, $expires_in_days );
			}
		}
	}
	
	public function sendWarningEmail($parent, $template, $expires_in_days) {
		$translate = Mage::getSingleton ( 'core/translate' );
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline ( false );
		$email = Mage::getModel ( 'core/email_template' );
		/* @var $email Mage_Core_Model_Email_Template */
		$recipient = array ('email' => $parent->getEmail (), 'name' => $parent->getName () );
		
		$sender = array ('name' => strip_tags ( Mage::getStoreConfig ( "trans_email/ident_support/name", $parent->getStoreId () ) ), 'email' => strip_tags ( Mage::getStoreConfig ( "trans_email/ident_support/email", $parent->getStoreId () ) ) );
		
		$email->setDesignConfig ( array ('area' => 'frontend', 'store' => $parent->getStoreId () ) )->sendTransactional ( $template, $sender, $recipient ['email'], $recipient ['name'], array ('customer' => $parent, 'store_name' => $parent->getStore ()->getName (), 'days_left' => $expires_in_days, 'points_balance' => $parent->getPointsSummary () ) );
		
		$translate->setTranslateInline ( true );
		
		return $email->getSentSuccess ();
	}
	
	public function quickTestAction() {
		echo "Le test... <BR />";
		//        Mage::getSingleton('rewards/special_action');
		$c = Mage::getModel ( 'rewards/customer' )->load ( 1 );
		$last_date = $c->getLatestActivityDate ();
		if ($last_date == null) {
			$last_date = 0;
		}
		$last_date = strtotime ( $last_date ) - 1;
		$current_date = strtotime ( Mage::helper ( 'rewards' )->now ( false ) );
		
		$expiry_delay = ( int ) Mage::getStoreConfig ( 'rewards/expire/delay_days' ) * self::SECS_IN_DAY;
		$time_since = $current_date - $last_date;
		
		$time_since_str = $this->secstostr ( $time_since );
		if ($time_since >= $expiry_delay) {
			die ( "points expired: $time_since_str since your last points change" );
		} else {
			$time_left = $expiry_delay - $time_since;
			die ( "Points didn't expire yet: $time_since_str since your last points change and $time_left_str left." );
		}
	}
	
	public function totalRedAction() {
		$c = Mage::getModel ( 'rewards/customer' )->load ( 76 );
		$neg_pts_ttl = $this->getTotalPointsSpentInLast365Days ( $c );
		print_r ( $neg_pts_ttl );
		die ();
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
	}
	
	const SECS_IN_YEAR = 31557600;
	protected function getTotalPointsSpentInLast365Days(TBT_Rewards_Model_Customer $c) {
		
		$yearStart = date ( "Y-m-d", time () - self::SECS_IN_YEAR ); // get current year
		

		$filteredTransferes = $c->getCustomerPointsCollectionAll ()->addFieldToFilter ( "quantity", array ('lt' => 0 )// look for negative points (redemptions) 
 )->addFieldToFilter ( "creation_ts", array ('gteq' => $yearStart )// set start date
 )->addFieldToFilter ( "reason_id", array ('eq' => TBT_Rewards_Model_Transfer_Reason::REASON_CUSTOMER_REDEMPTION )// only use order redemptions
 )->addFieldToFilter ( "customer_id", $c->getId () );
		
		$points_spent = $filteredTransferes->sumPoints ()->// add up all points and return a single value
getFirstItem ()->getPointsCount ();
		return $points_spent;
	}
	
	// 13th of Oct at 4pm  + 10 days = expires on the 24th
	// 13th of Oct at 12am + 10 days = expires on the 23rd
	

	public function secstostr($secs, $d = true, $h = true, $m = true, $s = true) { //@todo translatability?
		$r = "";
		if (($d && $secs >= 86400) || (! $h && ! $m && ! $s)) {
			$days = floor ( $secs / 86400 );
			$secs = $secs % 86400;
			$r = $days . ' day';
			if ($days != 1) {
				$r .= 's';
			}
			if ($secs > 0 && ($h || $m || $s)) {
				$r .= ', ';
			}
		}
		if (($h && $secs >= 3600) || (! $d && ! $m && ! $s)) {
			$hours = floor ( $secs / 3600 );
			$secs = $secs % 3600;
			$r .= $hours . ' hour';
			if ($hours != 1) {
				$r .= 's';
			}
			if ($secs > 0 && ($d || $m || $s)) {
				$r .= ', ';
			}
		}
		if (($m && $secs >= 60) || (! $d && ! $h && ! $s)) {
			$minutes = floor ( $secs / 60 );
			$secs = $secs % 60;
			$r .= $minutes . ' minute';
			if ($minutes != 1) {
				$r .= 's';
			}
			if ($secs > 0 && ($d || $h || $s)) {
				$r .= ', ';
			}
		}
		if ($s || (! $d && ! $hm && ! $m)) {
			$r .= $secs . ' second';
			if ($secs != 1) {
				$r .= 's';
			}
		}
		return $r;
	}
	
	public function secstodays($secs) {
		return $this->secstostr ( $secs, true, false, false, false );
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