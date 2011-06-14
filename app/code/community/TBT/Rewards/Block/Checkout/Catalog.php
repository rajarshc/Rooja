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
 * General Block
 * @deprecated Not used.  
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Checkout_Catalog extends TBT_Rewards_Block_Checkout_Abstract {
	const ITEM_REDEEM_LIST_CLASS = 'item-points-list-redeemed';
	const ITEM_REDEEM_HEADER_CLASS = 'item-points-header-redeemed';
	
	const ITEM_EARN_LIST_CLASS = 'item-points-list-earned';
	const ITEM_EARN_HEADER_CLASS = 'item-points-header-earned';
	
	const ITEM_BLOCK_CLASS = 'item-points-block';
	const ITEM_NO_POINTS_CAPTION_CLASS = 'item-no-points-caption';
	
	const TOGGLE_EFFECT = 'slide';
	const TOGGLE_BUTTON_STYLE = '';
	
	/**
	 * Formats the html for itesm in the checkout grid to have rewards information
	 *
	 * @param Mage_Sales_Model_Quote_Item <b>$_item</b> Item model of the row we are editing
	 * @param int <b>$cols_from_right</b> Number of columns to offset points column, from the right
	 * @return string html
	 */
	public function formatItemColoumn($_item, $cols_from_right = 2) {
		
		$html_points_list = '<td align="center"><span class=\'' . self::ITEM_BLOCK_CLASS . '\'>';
		
		// Fetch points redmeption data
		$redeemed_points = ( array ) Mage::helper ( 'rewards' )->unhashIt ( $_item->getRedeemedPointsHash () );
		$hasRedeemed = (sizeof ( $redeemed_points ) > 0);
		if ($hasRedeemed) {
			$redeem_list_id = 'points-list-redeemed-' . $_item->getId ();
			$html_points_list .= '<div class="' . self::ITEM_REDEEM_HEADER_CLASS . '" ' . 'onclick="Effect.toggle(\'' . $redeem_list_id . '\', \'' . self::TOGGLE_EFFECT . '\')" ' . 'style="' . self::TOGGLE_BUTTON_STYLE . '" ' . 'title="' . $this->__ ( 'Click to see a breakdown of how your points affect this line item.' ) . '" ' . '>' . $this->__ ( 'Points Spent' ) . '</div> ';
			$html_points_list .= '<ul 	class=\'' . self::ITEM_REDEEM_LIST_CLASS . '\' ' . 'id=\'' . $redeem_list_id . '\' style="display:none;">';
			$item_has_points = false;
			foreach ( $redeemed_points as $point ) {
				if (! $point) {
					continue;
				}
				$point = ( array ) $point;
				
				$rule = Mage::getModel ( 'rewards/catalogrule_rule' )->load ( $point ['rule_id'] );
				if (! $rule->getId ()) {
					continue;
				}
				
				$item_has_redemptions = true;
				$points_qty = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT] * $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY];
				
				$discount = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_EFFECT];
				$points_applic_qty = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY];
				
				$adjusted_price = Mage::helper ( 'rewards' )->priceAdjuster ( $_item->getPrice (), $discount );
				$discount = ($_item->getPrice () - $adjusted_price) * $points_applic_qty;
				
				////// get currency /////
				$store_currency_model = Mage::app ()->getStore ()->getCurrentCurrency ();
				$store_base_currency_model = Mage::app ()->getStore ()->getBaseCurrency ();
				if ($store_currency_model->getCode () == $store_base_currency_model->getCode ()) {
					$target_currency_rate = 1.0;
				} else {
					$target_currency_rate = $store_base_currency_model->getRate ( $store_currency_model );
				}
				///// 
				

				$discount = Mage::app ()->getStore ()->formatPrice ( $discount );
				
				$rule_id = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID];
				$inst_id = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_INST_ID];
				$currency_id = $point [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID];
				$points_str = Mage::getModel ( 'rewards/points' )->set ( $currency_id, $points_qty );
				
				$img_html = $this->genRuleCtrlImg ( $rule_id, false, false, $_item->getId (), $inst_id );
				$listing_string = $this->__ ( "%s, %s off", $points_str, $discount );
				$html_points_list .= '<li>' . $listing_string . $img_html . '</li>';
			}
			$html_points_list .= '</ul>';
		}
		
		//        if (!$item_has_points) {
		//            $html_points_list .= '<span class=\'no-points-caption\' style=\'font-style:italic\'>'. $this->__('No points.') .'</i><br/>';
		//        }
		

		if ($hasEarned = $this->hasEarnedPoints ( $_item )) {
			$html_points_list .= $this->getItemPointsAsList ( $_item );
		}
		
		if (! $hasEarned && ! $hasRedeemed) {
			$html_points_list .= $this->_getNoPointsStr ();
		}
		
		$html_points_list .= "</span>";
		
		// TODO WDCA - UUUUUGLY method of dropping new column into the table...
		$item_html = explode ( '</td>', $this->getItemHtml ( $_item ) );
		
		$temp = array ();
		for($i = 0; $i < $cols_from_right; $i ++) {
			$temp [$i] = array_pop ( $item_html );
		}
		
		if ($this->helper ( 'tax' )->priceIncludesTax ()) {
			$price = $_item->getRowTotalBeforeRedemptions () + $_item->getTaxAmount ();
		} else {
			$price = $_item->getRowTotalBeforeRedemptions ();
		}
		if (! floatval ( $price )) {
			$price = $_item->getRowTotal ();
		}
		$price = Mage::app ()->getStore ()->formatPrice ( $price );
		
		if ($this->cartHasRedemptions ()) {
			array_push ( $item_html, '<td align="center">' . $price );
		}
		
		//helper('checkout')->getPriceInclTax($_item);
		//array_push($item_html, '<td align="center">'. Mage::app()->getStore()->formatPrice($_item->getRowTotalBeforeRedemptions()));
		array_push ( $item_html, $html_points_list );
		
		for($i = $cols_from_right - 1; $i >= 0; $i --) {
			array_push ( $item_html, $temp [$i] );
		}
		
		return $item_html;
	}
	
	public function hasEarnedPoints($_item) {
		$currency_points = Mage::helper ( 'rewards/transfer' )->getEarnedPointsOnItem ( $_item );
		return sizeof ( $currency_points ) > 0;
	}
	
	public function getItemPointsAsList($_item) {
		$earn_list_id = 'points-list-earned-' . $_item->getId ();
		$earned_points_list = '<div class="' . self::ITEM_EARN_HEADER_CLASS . '" ' . 'onclick="Effect.toggle(\'' . $earn_list_id . '\', \'' . self::TOGGLE_EFFECT . '\')" ' . 'style="' . self::TOGGLE_BUTTON_STYLE . '" ' . 'title="' . $this->__ ( 'Click to see a breakdown of how your points affect this line item.' ) . '" ' . '>' . $this->__ ( 'Points Earned' ) . '</div> ';
		$currency_points = Mage::helper ( 'rewards/transfer' )->getEarnedPointsOnItem ( $_item );
		$earned_points_list .= '<ul 	class=\'' . self::ITEM_EARN_LIST_CLASS . '\' ' . 'id=\'' . $earn_list_id . '\' style="display:none;">';
		
		foreach ( $currency_points as $cid => $points_qty ) {
			$points = Mage::getModel ( 'rewards/points' )->setPoints ( $cid, $points_qty );
			$earned_points_list .= '<li>' . $points . '</li>';
		}
		$earned_points_list .= '</ul>';
		
		return $earned_points_list;
	}
	
	private function _getNoPointsStr() {
		$html = '<div class=\'' . self::ITEM_NO_POINTS_CAPTION_CLASS . '\' >';
		$html .= Mage::getModel ( 'rewards/points' ) . '<div/>';
		return $html;
	}
	
	// any type of redemptions, cart and catalog
	public function cartHasRedemptions() {
		return $this->_getRewardsSess ()->hasRedemptions ();
	}
	
	/**
	 * Fetches the rewards session singleton
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}

}