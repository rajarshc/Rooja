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
 * Checkout Rewards Spent
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Checkout_Rewardspent extends Mage_Core_Block_Template {
	
	protected function _construct() {
		parent::_construct ();
		$this->setTemplate ( 'rewards/checkout/rewardspent.phtml' );
	}
	
	public function getPointsSpent() {
		$str = $this->_getRewardsSess ()->getTotalPointsSpendingAsStringList ();
		return $str;
	}
	
	/**
	 * Show the spendings row?
	 *
	 * @return boolean
	 */
	public function showSpendings() {
		$doShow = true;
		if ($this->_getRewardsSess ()->hasRedemptions ()) {
			$doShow = true;
		} else {
			if (Mage::helper ( 'rewards/config' )->showCartRedemptionsWhenZero ()) {
				$doShow = true;
			} else {
				$doShow = false;
			}
		}
		return $doShow;
	}
	
	/**
	 * @deprecated use getShowNEPWarning() instead.
	 * @return  boolean
	 */
	public function showWarning() {
		return $this->getShowNEPWarning ();
	}
	
	/**
	 * Should we show the Not Enough Points warning label?
	 * @return  boolean
	 */
	public function getShowNEPWarning() {
		if ($this->_getRewardsSess ()->isCustomerLoggedIn ()) {
			if ($this->_getRewardsSess ()->isCartOverspent ()) {
				return true;
			}
		} else {
			if ($this->_getRewardsSess ()->hasRedemptions ()) {
				if (Mage::helper ( 'rewards/config' )->canUseRedemptionsIfNotLoggedIn ()) {
				
				} else {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Should we show the minimum order not enough warning?
	 * @return  boolean
	 */
	public function getShowMinOrderWarning() {
		$cart = $this->_getCart ();
		if ($cart->getQuote ()->getItemsCount ()) {
			if (! $cart->getQuote ()->validateMinimumAmount ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Should we ask the user to refresh?
	 * @return  boolean
	 */
	public function getShowRefreshNotice() {
		$storeId = $this->_getCart ()->getQuote ()->getStoreId ();
		$minOrderActive = Mage::getStoreConfigFlag ( 'sales/minimum_order/active', $storeId );
		if ($minOrderActive && ! $this->getShowMinOrderWarning ())
			return true;
		return false;
	}
	
	/**
	 * @return string : The warning message that should be displayed as a result of the cart being underspent
	 * if no minimum is set this returns back an empty string.
	 * @see $this->getShowMinOrderWarning(); 
	 */
	public function getMinOrderWarning() {
		$cart = $this->_getCart ();
		if ($cart->getQuote ()->getItemsCount ()) {
			if (! $cart->getQuote ()->validateMinimumAmount ()) {
				$warning = Mage::getStoreConfig ( 'sales/minimum_order/description' );
				return $warning;
			}
		}
		return "";
	}
	
	/**
	 * Fetches the rewards session.
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	private function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	/**
	 * Fetches the checkout session
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getCheckoutSession() {
		return Mage::getSingleton ( 'checkout/session' );
	}
	
	/**
	 * Retrieve shopping cart model object
	 *
	 * @return Mage_Checkout_Model_Cart
	 */
	protected function _getCart() {
		return Mage::getSingleton ( 'checkout/cart' );
	}
	
	protected function _toHtml() {
		if (Mage::helper ( 'rewards' )->isMultishipMode ()) {
			return '';
		} else {
			return parent::_toHtml ();
		}
	}

}

?>