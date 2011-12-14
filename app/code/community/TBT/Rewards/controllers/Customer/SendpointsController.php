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
class TBT_Rewards_Customer_SendpointsController extends Mage_Core_Controller_Front_Action {
	
	public function preDispatch() {
		parent::preDispatch ();
	}
	
	public function sendAction() {
		try {
			$friend_email = $this->getRequest ()->get ( 'friend_email' );
			$points_amt = $this->getRequest ()->get ( 'points_amt' );
			$currency_id = $this->getRequest ()->get ( 'currency_id' );
			$personal_comment = strip_tags ( $this->getRequest ()->get ( 'personal_comment' ) );
			
			if (! $friend_email || $friend_email === '') {
				throw new Exception ( 'You must enter a friend\'s email address to send them points!' );
			}
			
			if (! $this->isValidEmail ( $friend_email )) {
				throw new Exception ( 'The e-mail addres you entered is invalid.' );
			}
			
			if (! $points_amt || $points_amt <= 0) {
				throw new Exception ( 'You must enter a valid number of points to send your friend.' );
			}
			
			$friend = Mage::getModel ( 'rewards/customer' )->setWebsiteId ( Mage::app ()->getWebsite ()->getId () )->loadByEmail ( $friend_email );
			$friend_id = $friend->getId ();
			if (! $friend_id) {
				Mage::getSingleton ( 'core/session' )->addError ( $this->__ ( "There is no customer with that email address (%s).", $friend_email ) );
				$this->_redirect ( 'rewards/customer/' );
				return $this;
			}
			
			if ($currency_id == null) {
				if (Mage::getSingleton ( 'rewards/session' )->getCustomer ()->getNumCurrencies () == 1) {
					$currency_ids = Mage::getSingleton ( 'rewards/currency' )->getAvailCurrencyIds ();
					$currency_id = $currency_ids [0];
				} else {
					throw new Exception ( 'You must enter a valid currency!' );
				}
			}
			
			//Verify that the friend can receive points of that type
			/* TODO WDCA - point currencies are not yet specific to customer groups */
			if (! $friend->hasCurrencyId ( $currency_id )) {
				$currency_caption = Mage::getSingleton ( 'rewards/currency' )->getCurrencyCaption ( $currency_id );
				Mage::getSingleton ( 'core/session' )->addError ( $this->__ ( '%s is not allowed to use the %s points currency.', $friend->getName (), $currency_caption ) );
				$this->_redirect ( 'rewards/customer/' );
				return $this;
			}
			
			//Verify the sender has enough points of given type.
			$sender = Mage::getSingleton ( 'rewards/session' )->getSessionCustomer ();
			if (! $sender->getId ()) {
				Mage::getSingleton ( 'core/session' )->addError ( $this->__ ( 'You must log in or sign up before sending points to a friend!' ) );
				$this->_redirect ( 'customer/login' );
				return $this;
			}
			$balance = $sender->getUsablePointsBalance ( $currency_id );
			if (! $balance) {
				$balance = 0;
			}
			if ($points_amt > $balance) {
				
				Mage::getSingleton ( 'core/session' )->addError ( $this->__ ( 'You cannot send %s points to %s when you only have %s!', Mage::getModel ( 'rewards/points' )->set ( $currency_id, $points_amt ), $friend->getName (), Mage::getModel ( 'rewards/points' )->set ( $currency_id, $balance ) ) );
				$this->_redirect ( 'rewards/customer/' );
				return $this;
			}
			
			if ($friend_email == $sender->getEmail ()) {
				Mage::getSingleton ( 'core/session' )->addError ( $this->__ ( "You cannot send points to yourself!!" ) );
				$this->_redirect ( 'rewards/customer/' );
				return $this;
			}
			
			if (! $personal_comment || empty ( $personal_comment )) {
				$personal_comment = '';
			}
			
			$is_transfer_successful = Mage::helper ( 'rewards/transfer' )->transferPointsToFriend ( $points_amt, $currency_id, $friend_id, $personal_comment );
			if ($is_transfer_successful) {
				$points_sent_string = Mage::getModel ( 'rewards/points' )->set ( $currency_id, $points_amt );
				Mage::getSingleton ( 'core/session' )->addSuccess ( $this->__ ( 'You have successfully sent %s to %s!', $points_sent_string, $friend->getName () ) );
			} else {
				throw new Exception ( 'Points could not be sent to your friend.' );
			}
		} catch ( Exception $ex ) {
			Mage::getSingleton ( 'core/session' )->addError ( $this->__ ( $ex->getMessage () ) );
			$this->_redirect ( 'rewards/customer/' );
			return $this;
		}
		
		$this->_redirect ( 'rewards/customer/' );
		return;
	}
	
	private function isValidEmail($email) {
		$validator = new Zend_Validate_EmailAddress ();
		return $validator->isValid ( $email );
	}
}