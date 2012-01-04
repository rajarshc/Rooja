<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart item render block
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class TBT_Rewards_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer {
	
	protected $redeemed_points = null;
	protected $earned_points = null;
	
	public function getRedeemedPoints() {
		$this->redeemed_points = Mage::helper ( 'rewards' )->unhashIt ( $this->getItem ()->getRedeemedPointsHash () );
		return $this->redeemed_points;
	}
	
	public function hasRedemptions() {
		$hasRedeemed = (sizeof ( $this->getRedeemedPoints () ) > 0);
		return $hasRedeemed;
	}
	
	public function cartHasAnyCatalogRedemptions() {
		return $this->_getRewardsSess ()->getQuote ()->hasAnyAppliedCatalogRedemptions ();
	}
	
	/**
	 * Fetches the row total for the item before any catalog redemption rule
	 * discounts have taken effect.
	 * @return String
	 */
	public function getRowTotalBeforeRedemptions() {
		$price = $this->getItem ()->getRowTotalBeforeRedemptions ();
		if ($this->helper ( 'tax' )->priceIncludesTax () && Mage::helper ( 'tax' )->displayPriceIncludingTax ()) {
			$price = $this->getItem ()->getRowTotalBeforeRedemptionsInclTax ();
		}
		if (floatval ( $price ) == 0) {
			$price = $this->getItem ()->getRowTotal ();
			if ($this->helper ( 'tax' )->priceIncludesTax () && Mage::helper ( 'tax' )->displayPriceIncludingTax ()) {
				$price = $this->getItem ()->getRowTotalInclTax ();
			}
		}
		$price = Mage::app ()->getStore ()->formatPrice ( $price );
		
		return $price;
	}
	
	public function getEarnedPoints() {
		$_item = $this->getItem ();
		$this->earned_points = Mage::helper ( 'rewards/transfer' )->getEarnedPointsOnItem ( $_item );
		return $this->earned_points;
	}
	public function hasEarnedPoints() {
		$hasEarned = (sizeof ( $this->getEarnedPoints () ) > 0);
		return $hasEarned;
	}
	
	public function hasEarnings() {
		return $this->hasEarnedPoints ();
	}
	
	public function getEarningData() {
		$earned_points = $this->getEarnedPoints ();
		$earned_points_data = array ();
		
		// We do this instead of just using the pointsString function becasue we want
		// each currency to appear on a seperate line.
		foreach ( $earned_points as $cid => $points_qty ) {
			$earned_points_str = ( string ) Mage::getModel ( 'rewards/points' )->set ( array ($cid => $points_qty ) );
			$earned_points_data [] = $earned_points_str;
		}
		return $earned_points_data;
	}
	
	public function getRedemptionData() {
		$_item = $this->getItem ();
		$redeemed_points = $this->getRedeemedPoints ();
		$redeemed_points_data = array ();
		foreach ( $redeemed_points as $point ) {
			if (! $point) {
				continue;
			}
			$point = ( array ) $point;
			
			$rule = Mage::getModel ( 'rewards/catalogrule_rule' )->load ( $point ['rule_id'] );
			if (! $rule->getId ()) {
				continue;
			}
			
			$points_amt = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT];
			$item_has_redemptions = true;
			$points_qty = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT] * $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY];
			
			$discount = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_EFFECT];
			$points_applic_qty = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY];
			
			$base_item_price = $_item->getBaseCalculationPrice ();
			if ($this->helper ( 'tax' )->priceIncludesTax () && Mage::helper ( 'tax' )->displayPriceIncludingTax ()) {
				$base_item_price *= (1 + $_item->getTaxPercent () / 100);
			}
			
			$adjusted_price = Mage::helper ( 'rewards' )->priceAdjuster ( $base_item_price, $discount, false );
			
			if ($adjusted_price < 0) {
				$adjusted_price = 0;
			}
			
			$discount = ($base_item_price - $adjusted_price) * $points_applic_qty;
			
			$discount = Mage::app ()->getStore ()->convertPrice ( $discount );
			$discount = Mage::app ()->getStore ()->formatPrice ( $discount );
			
			$rule_id = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID];
			$inst_id = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_INST_ID];
			$currency_id = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID];
			$points_str = Mage::getModel ( 'rewards/points' )->set ( $currency_id, $points_qty );
			$unit_points_str = Mage::getModel ( 'rewards/points' )->set ( $currency_id, $points_amt );
			
			$img_html = $this->genRuleCtrlImg ( $rule_id, false, false, $_item->getId (), $inst_id );
			$redeemed_points_data [] = array ('points' => array ($currency_id => $points_qty ), 'points_str' => $points_str, 'discount' => $discount, 'img_html' => $img_html, 'rule' => $rule, 'instance_id' => $inst_id, 'unit_points_str' => $unit_points_str );
		
		}
		return $redeemed_points_data;
	}
	
	/**
	 * Generates that the user can click on to apply or remove rules
	 *
	 * @param int $rule_id
	 * @param bool $is_add
	 * @param bool $is_cart 
	 * @param int $item_id
	 * @return TBT_Rewards_Block_Checkout_Cart_Rulectrlimg $this
	 */
	public function genRuleCtrlImg($rule_id, $is_add = true, $is_cart = true, $item_id = 0, $redemption_instance_id = 0, $callback = "true") {
		$img_block_class = 'rewards/checkout_cart_rulectrlimg';
		$img_block = Mage::getBlockSingleton ( $img_block_class );
		$img_html = $img_block->init ( $rule_id, $is_add, $is_cart, $item_id, $redemption_instance_id, $callback )->toHtml ();
		return $img_html;
	}
	
	public function showEarnedUnderSpent() {
		return Mage::helper ( 'rewards/cart' )->showPointsAdditionalSubsection ();
	}
	
	public function isOneRedemptionMode() {
		$points_as_price = $this->getCfgHelper ()->showPointsAsPrice ();
		$one_redemption_only = $this->getCfgHelper ()->forceOneRedemption ();
		$force_redemptions = $this->getCfgHelper ()->forceRedemptions ();
		$is_one_redemption_mode = ($points_as_price && $one_redemption_only && $force_redemptions);
		return $is_one_redemption_mode;
	}
	
	// any type of redemptions, cart and catalog
	public function cartHasRedemptions() {
		return $this->_getRewardsSess ()->hasRedemptions ();
	}
	// any type of redemptions, cart and catalog
	public function cartHasDistributions() {
		return $this->_getRewardsSess ()->hasDistributions ();
	}
	
	public function showPointsColumn() {
		return Mage::helper ( 'rewards/cart' )->showPointsColumn ();
	}
	
	public function showBeforePointsColumn() {
		return Mage::helper ( 'rewards/cart' )->showBeforePointsColumn ();
	}
	
	/**
	 * Fetchtes the rewards cofnig helper
	 *
	 * @return TBT_Rewards_Helper_Config
	 */
	public function getCfgHelper() {
		return Mage::helper ( 'rewards/config' );
	}
	
	/**
	 * Fetches the rewards session singleton
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	public function getRowTotalInclTax($_item) {
		$base_row_total = $_item->getRowTotal ();
		$tax_percent = $_item->getTaxPercent () / 100;
		$base_row_total_incl_tax = $base_row_total * (1 + $tax_percent);
		return $base_row_total_incl_tax;
	}
}