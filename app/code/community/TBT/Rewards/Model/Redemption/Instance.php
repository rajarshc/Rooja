<?php

class TBT_Rewards_Model_Redemption_Instance extends Varien_Object {
	const POINTS_CURRENCY_ID = 'points_currency_id';
	const POINTS_AMT = 'points_amt';
	const POINTS_EFFECT = 'effect';
	const POINTS_RULE_ID = 'rule_id';
	const POINTS_APPLICABLE_QTY = 'applicable_qty';
	const POINTS_USES = 'uses';
	const POINTS_INST_ID = 'redemption_inst_id';
	
	public function getItemDiscount() {
		if (! $this->getItem ())
			return 0;
		$item = $this->getItem ();
		
		$product_price = $item->getRowTotal ();
		
		$this->trimApplicableQty ();
		$price_after_redem = $this->_help ()->priceAdjuster ( $product_price, $this->getEffect () );
		
		$discount = $product_price - $price_after_redem;
		
		return $discount;
	}
	
	protected function trimApplicableQty($max = null) {
		if ($max == null) {
			if ($this->hasItem ()) {
				$item = $this->getItem ();
				$max = ($item->getQty () ? $item->getQty () : ($item->getQtyOrdered () ? $item->getQtyOrdered () : 1));
			}
		}
		if ($this->getApplicableQty () > $max) {
			$this->setApplicableQty ( $max );
		}
		return $this;
	}
	
	protected function _help() {
		return Mage::helper ( 'rewards' );
	}
	
	public function getId() {
		return $this->getRedemptionInstId ();
	}
	
	public function setId($id) {
		$this->setRedemptionInstId ( $id );
		return $this;
	}
	
	protected function getCustomerGroupId() {
		if ($this->hasCustomerGroupId ()) {
			$gId = $this->getData ( 'customer_group_id' );
		} else {
			if ($this->hasCustomer ()) {
				$gId = $this->getCustomer ()->getCustomerGroupId ();
			} else {
				$gId = Mage::getSingleton ( 'customer/session' )->getCustomerGroupId ();
			}
			if ($gId !== 0 && empty ( $gId )) {
				$gId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
			}
		}
		return $gId;
	}
	
	public function getWebsiteId() {
		$wId = $this->hasWebsiteId () ? $this->getData ( 'website_id' ) : Mage::app ()->getWebsite ()->getId ();
		return $wId;
	}
	
	public function getDate() {
		$date = $this->hasDate () ? $this->getData ( 'date' ) : $this->_help ()->now ();
		return $date;
	}
	
	public function getApplicableRuleInfo() {
		if (! $this->hasApplicableRuleInfo ()) {
			$applicable_rule = Mage::getResourceModel ( 'rewards/catalogrule_rule' )->getApplicableReward ( $this->getDate (), $this->getWebsiteId (), $this->getCustomerGroupId (), $this->getItem ()->getProductId (), $this->getRuleId () );
			$this->setApplicableRuleInfo ( $applicable_rule );
		}
		return $this->getData ( 'applicable_rule_info' );
	}
	
	public function calcEffect() {
		if (! $this->hasItem ()) {
			throw new Exception ( "Item must be specified before calling the calcEffect method on a redemption instance." );
		}
		
		$item = $this->getItem ();
		$product_price = $item->getRowTotal ();
		
		$applicable_rule = $this->getApplicableRuleInfo ();
		
		$cc_ratio = 0;
		if ($product_price > 0) {
			$cc = $item->getQuote ()->getStore ()->getCurrentCurrency ();
			$bc = 1 / ($item->getQuote ()->getStore ()->getBaseCurrency ()->getRate ( $cc ));
			$cc_ratio = $bc;
		}
		$product_price = $cc_ratio * $product_price;
		
		$effect = $this->_help ()->amplifyEffect ( $product_price, $applicable_rule [self::POINTS_EFFECT], $this->getUses () );
		
		//print_r("{$this->getUses()} uses of {$applicable_rule['effect']} on {$product_price} gives effect {$effect}. <BR />");
		

		$this->setEffect ( $effect );
		
		return $this;
	}
	
	public function toHash($calculate_effect = true) {
		if ($calculate_effect) {
			$this->calcEffect ();
		}
		
		return $this->_help ()->hashIt ( $this->getStorableData () );
	}
	
	public function getStorableData() {
		$data = $this->getData ();
		$keys = array (self::POINTS_CURRENCY_ID => null, self::POINTS_AMT => null, self::POINTS_EFFECT => null, self::POINTS_RULE_ID => null, self::POINTS_APPLICABLE_QTY => null, self::POINTS_USES => null, self::POINTS_INST_ID => null );
		
		$idata = array_intersect_key ( $data, $keys );
		
		return $idata;
	}
	
	public function setRule($rule) {
		if (is_int ( $rule )) {
			$rule = Mage::getModel ( 'rewards/catalogrule_rule' )->load ( $rule );
		}
		$this->setData ( 'rule', $rule );
		$this->setRuleId ( $rule->getId () );
		
		return $this;
	}
	
	public function getRule() {
		if ($this->hasRule ()) {
			return $this->getData ( 'rule' );
		}
		if ($this->hasRuleId ()) {
			return $this->setRule ( $this->getRuleId () )->getRule ();
		}
		return parent::getRule ();
	}
	
	public function loadFromRule($rule = null) {
		if ($rule != null)
			$this->setRule ( $rule );
		
		$rule = $this->getRule ();
		
		$this->setPointsCurrencyId ( $rule->getPointsCurrencyId () );
		
		$points = Mage::helper ( 'rewards/transfer' )->calculateCatalogPoints ( $rule->getId (), $this->getItem (), true );
		if (! $points) {
			throw new Exception ( "The catalog redemption rule entitled {$rule->getName()} is invalid and cannot be applied." );
		}
		$iamount = $this->getUses () * $points ['amount'] * - 1;
		
		$this->setPointsAmt ( $iamount )->setPointsAmount ( $iamount );
		
		return $this;
	}

}
