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
 * Checkout Cart
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Checkout_Cart extends TBT_Rewards_Block_Checkout_Abstract {
	const BOLD_POINT_AMOUNTS = true;
	
	protected function _construct() {
		parent::_construct ();
	}
	
	/**
	 * Fetches a points string with the points amounts bolded (if above const is set as true)
	 *
	 * @param integer $amt
	 * @param integer $currency_id
	 * @param integer $doPlus[ = false]
	 * @return string
	 */
	public function getRedeemPointsStr($amt, $currency_id, $doPlus = false) {
		if ($doPlus && $amt < 0) {
			$amt = $amt * - 1;
		}
		$str = Mage::getModel ( 'rewards/points' )->set ( $currency_id, $amt );
		if (self::BOLD_POINT_AMOUNTS) {
			$str = Mage::helper ( 'rewards' )->emphasizeThePoints ( $str );
		}
		if ($doPlus) {
			$str = "+" . $str;
		}
		return $str;
	
	}
	
	/**
	 * True if the customer has any applicable point redemptions or has any point redemptions applied
	 *
	 * @return boolean
	 */
	public function hasRedemptionData() {
		$redemption_data = $this->collectShoppingCartRedemptions ();
		$has_redemption_data = sizeof ( $redemption_data ) > 0;
		$has_inner_redemption_data = $this->hasApplicableRedemptionData () || $this->hasAppliedRedemptionData ();
		return $has_redemption_data && $has_inner_redemption_data;
	}
	
	/**
	 * True if the customer is earning any points on the cart level
	 *
	 * @return boolean
	 */
	public function hasCartDistributions() {
		$distribution_data = $this->updateShoppingCartPoints ();
		$has_distribution_data = ! empty ( $distribution_data );
		return $has_distribution_data;
	}
	
	/**
	 * True if the system has any cart points data
	 *
	 * @return boolean
	 */
	public function hasCartPointsData() {
		return $this->hasRedemptionData () && $this->showCartRedeemBox ();
	}
	
	/**
	 * True if the customer has any point redemptions applied
	 *
	 * @return boolean
	 */
	public function hasAppliedRedemptionData() {
		$redemption_data = $this->collectShoppingCartRedemptions ();
		if (! isset ( $redemption_data ['applied'] ))
			return false;
		$has_redemption_data = sizeof ( $redemption_data ['applied'] ) > 0;
		return $has_redemption_data;
	}
	
	/**
	 * True if the customer has any applicable point redemptions
	 * 
	 * @return boolean
	 */
	public function hasApplicableRedemptionData() {
		$redemption_data = $this->collectShoppingCartRedemptions ();
		if (! isset ( $redemption_data ['applicable'] ))
			return false;
		$has_redemption_data = sizeof ( $redemption_data ['applicable'] ) > 0;
		return $has_redemption_data;
	}
	
	public function getCustomerPoints() {
		$s = Mage::getSingleton ( 'rewards/session' );
		$m = Mage::getModel ( 'rewards/points' );
		if ($s->isCustomerLoggedIn ()) {
			$m->set ( $s->getSessionCustomer ()->getUsablePoints () );
		}
		return $m;
	}
	
	public function customerIsLoggedIn() {
		$s = Mage::getSingleton ( 'rewards/session' );
		if ($s->isCustomerLoggedIn ()) {
			return true;
		}
		return false;
	}
	
	/**
	 * Shouldwe show the shopping cart points redemption box?
	 * 
	 * @return boolean
	 */
	public function showCartRedeemBox() {
		return Mage::helper ( 'rewards/cart' )->showCartRedeemBox ();
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function needsLogin() {
		$canUseRedemptionsIfNotLoggedIn = Mage::getStoreConfigFlag ( 'rewards/general/canUseRedemptionsIfNotLoggedIn' );
		return ! $canUseRedemptionsIfNotLoggedIn && ! $this->customerIsLoggedIn ();
	}
	
	/**
	 * Show the points slider if there are any shopping cart points rules that contain any applicable or applied
	 * points redemption rules that are of the type "discount by points spent"  (dbps) 
	 * 
	 * @return boolean
	 */
	public function showPointsSlider() {
		if (! $this->hasRedemptionData ())
			return false;
		$redemption_data = $this->collectShoppingCartRedemptions ();
		foreach ( array_merge ( $redemption_data ['applicable'], $redemption_data ['applied'] ) as $entry ) {
			if (isset ( $entry ['is_dbps'] )) {
				if ($entry ['is_dbps']) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * True if there any non discount_by_points_spent type applicable or applied rules
	 * 
	 * @return boolean
	 */
	public function hasNonDbpsCartRules() {
		if (! $this->hasRedemptionData ())
			return false;
		$redemption_data = $this->collectShoppingCartRedemptions ();
		foreach ( array_merge ( $redemption_data ['applicable'], $redemption_data ['applied'] ) as $entry ) {
			if ($entry ['is_dbps'])
				continue;
			return true;
		}
		return false;
	}

}