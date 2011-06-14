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
 * Customer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Customer extends Mage_Customer_Model_Customer {
	
	/**
	 * Stores the points balances where the key is the ID of the currency.
	 *
	 * @var array
	 */
	protected $points = array ();
	protected $on_hold_points = array ();
	protected $pending_points = array ();
	protected $usable_points = array ();
	protected $transfers = array ();
	/**
	 * This is used to store the id from the model before saving 
	 * to compare to future versions
	 *
	 * @var integer
	 */
	protected $oldId = - 1;
	protected $currencies = null;
	
	/**
	 *
	 * (override from an abstract parent)
	 */
	protected function _beforeSave() {
		$oldId = $this->getId ();
		if (! empty ( $oldId )) {
			$this->oldId = $oldId;
		}
		return parent::_beforeSave ();
	}
	
	/**
	 *
	 * (override from an abstract parent)
	 */
	protected function _afterLoad() {
		$this->_loadPointsCollections ();
		$this->_loadTransferCollections ();
		return parent::_afterLoad ();
	}
	
	/**
	 * True if the id specified is new to this customer model after a SAVE event.
	 *
	 * @param integer $checkId
	 * @return boolean
	 */
	protected function isNewCustomer($checkId) {
		return ($this->oldId != $checkId);
	}
	
	/**
	 * Processing object after save data
	 * (override from an abstract parent)
	 *
	 * @return Mage_Core_Model_Abstract
	 */
	protected function _afterSave() {
		//If the customer is new (hence not having an id before) get applicable rules,
		//and create a transfer for each one
		$isNew = false;
		if ($this->isNewCustomer ( $this->getId () )) {
			$isNew = true;
			$this->oldId = $this->getId (); //This stops multiple triggers of this function
			$this->createTransferForNewCustomer ();
		}
		Mage::getSingleton ( 'rewards/session' )->setCustomer ( $this );
		if ($isNew) {
			//		    Mage::helper('rewards')->notice("New customer event triggered with customer id #{$this->getId()}");
			Mage::getSingleton ( 'rewards/session' )->triggerNewCustomerCreate ( $this );
			Mage::dispatchEvent ( 'rewards_new_customer_create', array ('customer' => &$this ) );
		}
		return parent::_afterSave ();
	}
	
	/**
	 * Creates transfers if there are rules dealing with new customers
	 *
	 * 
	 */
	private function createTransferForNewCustomer() {
		$ruleCollection = Mage::getSingleton ( 'rewards/special_validator' )->getApplicableRulesOnSignup ();
		foreach ( $ruleCollection as $rule ) {
			try {
				//Create the Transfer
				$is_transfer_successful = Mage::helper ( 'rewards/transfer' )->transferSignupPoints ( $rule->getPointsAmount (), $rule->getPointsCurrencyId (), $this->getId (), $rule->getId () );
			} catch ( Exception $ex ) {
				Mage::getSingleton ( 'core/session' )->addError ( $ex->getMessage () );
			}
			
			if ($is_transfer_successful) {
				//Alert the customer on the distributed points   
				Mage::getSingleton ( 'core/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'You received %s for signing up!', Mage::getModel ( 'rewards/points' )->set ( $rule ) ) );
			} else {
				Mage::getSingleton ( 'core/session' )->addError ( Mage::helper ( 'rewards' )->__ ( 'Could not transfer points.' ) );
			}
		}
	}
	
	/**
	 * Loads the points summaries for this customer then saves into this customer model.
	 *
	 */
	private function _loadPointsCollections() {
		$this->points = $this->_getPointSums ( '*active*' );
		$this->on_hold_points = $this->_getPointSums ( TBT_Rewards_Model_Transfer_Status::STATUS_ON_HOLD );
		$this->pending_points = $this->_getPointSums ( TBT_Rewards_Model_Transfer_Status::STATUS_PENDING );
		// Load Indexed point balance
		try {
			if (Mage::helper('rewards/customer_points_index')->useIndex()) {
				$this->usable_points = $this->_getIndexerUsablePointsBalance ();
			} else {
				$this->usable_points = $this->_getEffectiveActivePointsSum ();
			}
		} catch ( Exception $e ) {
			$this->usable_points = $this->_getEffectiveActivePointsSum ();
		}
	}
	
	/**
	 * Loads point sums for a given status
	 * @see TBT_Rewards_Model_Transfer_Status for status ids
	 * @param integer|string $status the only string accepted is '*active*' which will fetch all active points transfers (approved)
	 * @return array
	 */
	protected function _getPointSums($status) { //@nelkaake 22/01/2010 2:53:29 AM : changed to protected
		if ($status == '*active*') {
			$point_sums = $this->getCustomerPointsCollection ()->addStoreFilter ( Mage::app ()->getStore () );
		} else {
			$point_sums = $this->getCustomerPointsCollectionAll ()->addStoreFilter ( Mage::app ()->getStore () )->addFilter ( "status", $status );
		
		//			if (($status == TBT_Rewards_Model_Transfer_Status::STATUS_PENDING) ||
		//				($status == TBT_Rewards_Model_Transfer_Status::STATUS_ON_HOLD)) {
		//					$point_sums->addFieldToFilter("quantity", array('lt' => 0));
		//			}
		}
		$point_sums->addFilter ( "customer_id", $this->getId () );
		
		$points = array ();
		//Zero's out all cuurencies on the point map
		foreach ( $this->getCustomerCurrencyIds () as $curr_id ) {
			$points [$curr_id] = 0;
		}
		
		foreach ( $point_sums as $currency_points ) {
			$points [$currency_points->getCurrencyId ()] = ( int ) $currency_points->getPointsCount ();
		}
		
		return $points;
	}
	
	/**
	 * Loads sum of pending points redemptions
	 * @see TBT_Rewards_Model_Transfer_Status for status ids
	 * @return array
	 */
	public function _getPendingPointsRedemptionsSum() {
		$point_sums = $this->getTransferCollection ()->addStoreFilter ( Mage::app ()->getStore () )->addFilter ( "status", TBT_Rewards_Model_Transfer_Status::STATUS_PENDING )->addFieldToFilter ( "quantity", array ('lt' => 0 ) )->groupByCustomers ()->addFilter ( "customer_id", $this->getId () );
		
		$points = array ();
		//Zero's out all currencies on the point map
		foreach ( $this->getCustomerCurrencyIds () as $curr_id ) {
			$points [$curr_id] = 0;
		}
		
		foreach ( $point_sums as $currency_points ) {
			$points [$currency_points->getCurrencyId ()] = ( int ) $currency_points->getPointsCount ();
		}
		
		return $points;
	}
	
	/**
	 * Loads sum of active points minus sum of pending redemptions
	 * @see TBT_Rewards_Model_Transfer_Status for status ids
	 * @return array
	 */
	private function _getEffectiveActivePointsSum() {
		$points_sum = $this->_getPointSums ( '*active*' );
		$pending_redemptions = $this->_getPendingPointsRedemptionsSum ();
		
		$points = array ();
		//Zero's out all currencies on the point map
		foreach ( $this->getCustomerCurrencyIds () as $curr_id ) {
			$points [$curr_id] = 0;
		}
		
		foreach ( $points_sum as $curr_id => $points_amt ) {
			// we're adding them because $pending_redemptions are already negative
			$points [$curr_id] = ( int ) $points_amt + ( int ) $pending_redemptions [$curr_id];
		}
		
		return $points;
	}
	
	/**
	 * Loads the transfers for this customer then saves into this customer model.
	 *
	 */
	private function _loadTransferCollections() {
		// Fetches a list of point tranfers for this customers.
		// Each row is the point tranfers for a customer in a certain currency.
		$transfers = $this->getTransferCollection ()->selectPointsCaption ( 'points_caption' )->addStoreFilter ( Mage::app ()->getStore () )->addFilter ( "customer_id", $this->getId () );
		$this->transfers = $transfers;
	}
	
	/**
	 * Returns the quantity of points available for the current
	 * customer and current store in the specified currency_id;
	 *
	 * @param int $currency_id
	 * @return int
	 */
	public function getPointsBalance($currency_id) {
		if (array_search ( $currency_id, $this->getCustomerCurrencyIds () ) !== false) {
			return $this->points [$currency_id];
		} else {
			return false;
		}
	}
	
	/**
	 * Returns the quantity of points <b>usable</b> for the current
	 * customer and current store in the specified currency_id, which is
	 * all approved points <b>minus</b> pending redemptions.
	 *
	 * @param int $currency_id
	 * @return int
	 */
	public function getUsablePointsBalance($currency_id) {
		if (array_search ( $currency_id, $this->getCustomerCurrencyIds () ) !== false) {
			return $this->usable_points [$currency_id];
		} else {
			return false;
		}
	}
	
	/**
	 * Returns the a list of points where each item is 
	 * a total balance of points
	 *
	 * @return array
	 */
	public function getPoints() {
		return $this->points;
	}
	
	/**
	 * Returns the a list of points where each item is 
	 * a total balance of points
	 *
	 * @return array
	 */
	public function getPendingPoints() {
		return $this->pending_points;
	}
	
	/**
	 * Returns the a list of points where each item is 
	 * a total balance of points
	 *
	 * @return array
	 */
	public function getOnHoldPoints() {
		return $this->on_hold_points;
	}
	
	/**
	 * Returns the a list of points where each item is 
	 * a total balance of points
	 * The usable points are the number of points that can be used towards an order RIGHT NOW.
	 * IE pending redemptions ARE deducted from this total and pending distributions are NOT 
	 * added to this total.
	 * 
	 * @return array
	 */
	public function getUsablePoints() {
		return $this->usable_points;
	}
	
	/**
	 * Get usable points (non-indexer version)
	 * 
	 * @return array
	 */
	public function getRealUsablePoints() {
		return $this->_getEffectiveActivePointsSum ();
	}
	
	/**
	 * Returns all currencies applicable to this customer
	 *
	 * @return array
	 */
	// TODO WDCA - Add in filter by customer group ID, currently not supported
	public function getCustomerCurrencyIds() {
		return Mage::getModel ( 'rewards/currency' )->getAvailCurrencyIds ();
	}
	
	/**
	 * Returns the number of currencies available to this customer.
	 *
	 * @return int
	 */
	public function getNumCurrencies() {
		return count ( Mage::getModel ( 'rewards/currency' )->getAvailCurrencyIds () );
	}
	
	public function hasCurrencyId($currency_id) {
		$currency_ids = Mage::getModel ( 'rewards/currency' )->getAvailCurrencyIds ();
		return array_search ( $currency_id, $currency_ids ) !== false;
	}
	
	/**
	 * Returns a nicely formatted string of the customer's points
	 *
	 * @return string
	 */
	public function getPointsSummary() {
		return Mage::helper ( 'rewards' )->getPointsString ( $this->usable_points );
	}
	
	/**
	 * Returns a nicely formatted string of the customer's PENDING points
	 *
	 * @return string
	 */
	public function getPendingPointsSummary() {
		return Mage::helper ( 'rewards' )->getPointsString ( $this->pending_points );
	}
	
	/**
	 * Returns a nicely formatted string of the customer's ON HOLD points
	 *
	 * @return string
	 */
	public function getOnHoldPointsSummary() {
		return Mage::helper ( 'rewards' )->getPointsString ( $this->on_hold_points );
	}
	
	/**
	 * Calculates the indexer points balance for this customer
	 * 
	 * @return array
	 */
	protected function _getIndexerUsablePointsBalance() {
		/* @var $usable_points TBT_Rewards_Model_Mysql4_Customer_Indexer_Points_Collection */
		$usable_points = Mage::getModel ( 'rewards/customer_indexer_points' )->getCollection ()->addFieldToFilter ( 'customer_id', $this->getId () );
		$usable_points_array = array (1 => 0 );
		if ($usable_points->count ()) {
			$usable_points_array [1] = $usable_points->getFirstItem ()->getCustomerPointsUsable ();
		}
		return $usable_points_array;
	}
	
	/**
	 * Returns a nicely formatted string of the customers points including 
	 * pending points and on hold points.
	 * @deprecated Using this function makes templating very rigid. see the other points summary methods
	 *
	 * @return string
	 */
	public function getPointsSummaryFull() {
		$parts = array ();
		$status_captions = Mage::getSingleton ( 'rewards/transfer_status' )->getOptionArray ();
		
		if ($this->hasPoints ()) {
			$active_points = $this->getPointsSummary ();
			$parts [] = $active_points;
		}
		if ($this->hasPendingPoints ()) {
			$points_pending = Mage::helper ( 'rewards' )->getPointsString ( $this->pending_points );
			$points_pending .= ' ' . $status_captions [TBT_Rewards_Model_Transfer_Status::STATUS_PENDING];
			$parts [] = $points_pending;
		}
		if ($this->hasPointsOnHold ()) {
			$points_on_hold = Mage::helper ( 'rewards' )->getPointsString ( $this->on_hold_points );
			$points_on_hold .= ' ' . $status_captions [TBT_Rewards_Model_Transfer_Status::STATUS_ON_HOLD];
			$parts [] = $points_on_hold;
		}
		
		$del = ' ' . Mage::helper ( 'rewards' )->__ ( 'and' ) . ' ';
		$final_str = implode ( $del, $parts );
		return $final_str;
	}
	
	public function hasPoints() {
		foreach ( $this->getPoints () as $points ) {
			if ($points > 0)
				return true;
		}
		return false;
	}
	
	public function hasUsablePoints() {
		foreach ( $this->getUsablePoints () as $points ) {
			if ($points > 0)
				return true;
		}
		return false;
	}
	
	public function hasPendingPoints() {
		foreach ( $this->getPendingPoints () as $points ) {
			if ($points > 0)
				return true;
		}
		return false;
	}
	
	public function hasPointsOnHold() {
		foreach ( $this->getOnHoldPoints () as $points ) {
			if ($points > 0)
				return true;
		}
		return false;
	}
	
	/**
	 * Fetches the transfers array for this customer.
	 *
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function getTransfers() {
		return $this->transfers;
	}
	
	/**
	 * Fetches newsletter transfers from this customer
	 * @param integer $newsletter_id
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function getNewsletterTransfers($newsletter_id) {
		$transfers = $this->getTransfers ()->addFilter ( 'reference_type', TBT_Rewards_Model_Transfer_Reference::REFERENCE_NEWSLETTER )->addFilter ( 'reference_id', $newsletter_id );
		return $transfers;
	}
	
	/**
	 * Fetches all sumemd up transfers for all customers
	 *
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function getCustomerPointsCollection() {
		return $this->getTransferCollection ()->groupByCustomers ()->selectOnlyActive ();
	}
	
	/**
	 * Fetches all summed up transfers for the customer including 
	 * pending and on_hold transfers (no status restriction)
	 *
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function getCustomerPointsCollectionAll() {
		return $this->getTransferCollection ()->groupByCustomers ();
	}
	
	/**
	 * This method fetches a collection of all transfers for <b>all customers</b>. 
	 *
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function getTransferCollection() {
		return Mage::getModel ( 'rewards/transfer' )->getCollection ();
	}
	
	/**
	 * True if the customer can afford the points specified in the currency specified.
	 * If the first parameter is an array and the second param is left out,
	 * the function will return true if the customer can afford all of the 
	 * <b>array of point sums</b> provided in the first param.
	 * Do not pass this function a list of arbitrary transfers!
	 * TODO use predictPointsRemaining to calculate this value
	 * 
	 * @param integer|array $points_quantity if this value is an array, please input the 
	 * standard format of array( currency_id=>points_quantity, ...)
	 * @param integer [$points_currency]
	 * @return boolean
	 */
	public function canAfford($points_quantity, $points_currency = null) {
		if ($points_currency == null && is_array ( $points_quantity )) {
			$points_array = $points_quantity;
			foreach ( $points_array as $currency_id => $quantity ) {
				if (! $this->canAfford ( $quantity, $currency_id )) {
					return false;
				}
			}
			return true;
		} else {
			if (! $this->hasCurrencyId ( $points_currency )) {
				return false;
			}
			if ($this->getUsablePointsBalance ( $points_currency ) >= $points_quantity) {
				return true;
			}
			return false;
		}
	}
	
	/**
	 * True if the customer can afford the points specified in the currency specified.
	 * If the first parameter is an array and the second param is left out,
	 * the function will return true if the customer can afford all of the 
	 * <b>array of point sums</b> provided in the first param.
	 * Do not pass this function a list of arbitrary transfers!
	 * TODO use predictPointsRemaining to calculate this value
	 * 
	 * @param integer|array $points_quantity if this value is an array, please input the 
	 * standard format of array( currency_id=>points_quantity, ...)
	 * @param integer [$points_currency]
	 * @return boolean
	 */
	public function canAffordFromPointsHash($points_array) {
		$total_points = array ();
		
		foreach ( $points_array as $temp_transfer ) {
			$temp_transfer = ( array ) $temp_transfer;
			
			$points_amount = $temp_transfer [TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT] * $temp_transfer [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY];
			$currency_id = $temp_transfer [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID];
			
			if (isset ( $total_points [$currency_id] )) {
				$total_points [$currency_id] += $points_amount;
			} else {
				$total_points [$currency_id] = $points_amount;
			}
		}
		
		return $this->canAfford ( $total_points );
	}
	
	/**
	 * Calculates the points remaining for this customer if the points transaction(s)
	 * proved went through.
	 * 
	 * @param integer|array $points_quantity if this value is an array, please input the 
	 * standard format of array( currency_id=>points_quantity, ...)
	 * @param integer [$points_currency]
	 * @return int|boolean|array	false means there was an error.  int is returned if a single
	 * quantity and currency is provided.  An array of remaining amounts
	 * for each currency is returned if the 
	 */
	public function predictPointsRemaining($points_quantity, $points_currency = null) {
		if ($points_currency == null && is_array ( $points_quantity )) {
			$points_array = $points_quantity;
			$q = array ();
			foreach ( $points_array as $currency_id => $quantity ) {
				if ($this->hasCurrencyId ( $currency_id )) {
					if (! isset ( $q [$currency_id] )) {
						$q [$currency_id] = $this->getUsablePointsBalance ( $currency_id );
					}
					$q [$currency_id] -= $quantity;
				} else {
					// can't use that currency so just continue;
					continue;
				}
			}
			return $q;
		} else {
			if (! $this->hasCurrencyId ( $points_currency )) {
				return false; // customer can't use these points
			}
			return $this->getUsablePointsBalance ( $points_currency ) - $points_quantity;
		}
	}
	
	//@nelkaake 22/01/2010 2:52:49 AM : returns the last time the user earned/spent points.
	public function getLatestActivityDate() {
		$last_transfers = $this->getTransfers ();
		$last_transfers->selectOnlyActive ()->addOrder ( 'last_update_ts', Varien_Data_Collection::SORT_ORDER_DESC );
		$last_transfer = $last_transfers->getFirstItem ();
		$date = $last_transfer->getLastUpdateTs ();
		if ($date) {
			return $date;
		} else {
			return null;
		}
		return $date;
	}
	
	//@nelkaake 31/01/2010 4:01:17 PM : 
	public function expireAllPoints() {
		$all_points = $this->getUsablePoints ();
		$customer_id = $this->getId ();
		$comments = Mage::helper ( 'rewards/expiry' )->getExpiryMsg ( $this->getStoreId () );
		foreach ( $all_points as $currency_id => $num_points ) {
			if ($num_points <= 0)
				continue;
			
		// ALWAYS ensure that we only give an integral amount of points
			$num_points = (- 1) * floor ( $num_points );
			$transfer = Mage::getModel ( 'rewards/transfer' )->setId ( null );
			$transfer->setReasonId ( TBT_Rewards_Model_Transfer_Reason::REASON_SYSTEM_ADJUSTMENT );
			//get the default starting status - usually Pending
			if (! $transfer->setStatus ( null, TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED )) {
				continue;
			}
			
			$transfer->setCreationTs ( now () )->setLastUpdateTs ( now () )->setCurrencyId ( $currency_id )->setQuantity ( $num_points )->setComments ( $comments )->setCustomerId ( $customer_id )->save ();
		}
		
		return $this;
	}

}