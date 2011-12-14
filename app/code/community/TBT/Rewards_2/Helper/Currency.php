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
 * Helper Currency
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Currency extends Mage_Core_Helper_Abstract {
	
	/**
	 * @deprecated 
	 *
	 * @var array???
	 */
	protected $currency_captions = null;
	
	/**
	 * Returns a map of currency_id=>caption.  
	 * @see getAvailCurrencyIds() to get a simple list of all available currency ids.
	 * TODO: filter these currency ids for only this store.
	 * 
	 * @return array
	 */
	public function getAvailCurrencies() {
		return Mage::getModel ( 'rewards/currency' )->getAvailCurrencies ();
	}
	
	/**
	 * Returns a list of currency ids
	 * TODO: filter these currency ids for only this store.
	 * 
	 * @return array
	 */
	public function getAvailCurrencyIds() {
		return Mage::getModel ( 'rewards/currency' )->getAvailCurrencyIds ();
	}
	
	/**
	 * Returns a list of currency options to be displayed in a SELECT box 
	 * @see getAvailCurrencyIds() to get a simple list of all available currency ids.
	 * TODO: filter these currency ids for only this store.
	 * 
	 * @return array
	 */
	public function getAvailCurrencyOptions() {
		$currencies = Mage::getModel ( 'rewards/currency' )->getAvailCurrencies ();
		$cOptions = array ();
		foreach ( $currencies as $cid => $currency ) {
			$cOptions [] = array ('label' => $currency, 'value' => $cid );
		}
		return $cOptions;
	}
	
	public function getActiveOptions() {
		$activeOptions = array (1 => 'Yes', 0 => 'No' );
		return $activeOptions;
	}
	
	/**
	 * Returns a string with *numeric value* *currency caption*
	 *
	 * @param int $points_qty
	 * @param int $currency_id
	 * @return string
	 */
	public function formatCurrency($points_qty, $currency_id) {
		if ($this->currency_captions == null) {
			$this->currency_captions = $this->getAvailCurrencies ();
		}
		
		return $points_qty . " " . $this->currency_captions [$currency_id];
	}
	
	public function getCurrencyCaption($currency_id) {
		if ($this->currency_captions == null) {
			$this->currency_captions = $this->getAvailCurrencies ();
		}
		return $this->currency_captions [$currency_id];
	}
	
	/**
	 * Returns a string formatted to display the number of each in given array
	 * @deprecated Use Mage::getModel('rewards/points')->set(points) instead...
	 *
	 * @param array $points_array
	 * @return string
	 */
	public function getFormattedCurrencyString($points_array, $amount = 0) {
		$summary_array = array ();
		if (empty ( $points_array ) || $points_array == "0") {
			return $points_array;
		} else if (is_array ( $points_array )) {
			foreach ( $points_array as $curr_id => $point ) {
				if ($point != 0) {
					$s = $point . ' ' . Mage::getModel ( 'rewards/currency' )->getCurrencyCaption ( $curr_id ) . " ";
					if ($point != 1) {
						$s .= $this->__ ( 'Points' );
					} else {
						$s .= $this->__ ( 'Point' );
					}
					$summary_array [] = $s;
				}
			}
			if (sizeof ( $summary_array ) == 0) {
				return "";
			} elseif (sizeof ( $summary_array ) == 1) {
				return $summary_array [0];
			} else {
				array_push ( $summary_array, $this->__ ( 'and' ) . " " . array_pop ( $summary_array ) );
				return implode ( ', ', $summary_array );
			}
		} else if (is_int ( $points_array ) || (is_int ( ( int ) $points_array ) && ( int ) $points_array != 0)) {
			$curr_id = $points_array;
			$result = "";
			//if($amount != 0){
			$result = $amount . " " . Mage::getModel ( 'rewards/currency' )->getCurrencyCaption ( $curr_id ) . " ";
			if ($amount != 1) {
				$result .= $this->__ ( 'Points' );
			} else {
				$result .= $this->__ ( 'Point' );
			}
			//}
			return $result;
		}
		return $points_array;
	}
	
	/**
	 * 
	 * @deprecated Use Mage::getModel('rewards/points')->set(points) instead and set a custom template
	 *
	 * @param unknown_type $points_array
	 * @return unknown
	 */
	public function getFormattedCurrencyList($points_array) {
		$summary_array = array ();
		if (is_array ( $points_array )) {
			foreach ( $points_array as $curr_id => $point ) {
				if ($point != 0) {
					$s = $point . ' ' . Mage::getModel ( 'rewards/currency' )->getCurrencyCaption ( $curr_id ) . " ";
					if ($point > 1) {
						$s .= $this->__ ( 'Points' );
					} else {
						$s .= $this->__ ( 'Point' );
					}
					$summary_array [] = $s;
				}
			}
			return implode ( '<br>', $summary_array );
		}
		return $points_array;
	}

}