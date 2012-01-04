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
 * Sales Rules Observer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Salesrule_Observer extends Varien_Object {
	
	/**
	 * This gets triggered after rules are processed and the discount amount is requested.
	 * @param Varien_Object $o
	 */
	public function salesruleValidatorProcess($o) {
		$event = $o->getEvent ();
		//($quote, $address, $item, $rule_id)
		$quote = $event->getQuote ();
		$address = $event->getAddress ();
		$item = $event->getItem ();
		
		$rule = $event->getRule ();
		$rule_id = $rule->getId ();
		
		try {
			//@nelkaake -a 11/03/11: FIRSTLY, if it's not a points rule, skip it and let Magento do it's thing.
			if (! Mage::helper ( 'rewards/rule' )->isPointsRule ( $rule )) {
				return $this;
			}
			
			//@nelkaake -a 11/03/11: Check to see if the rule is applied. If it is, allow it to be alive.
			$this->_getDiscountValidator ()->validateRedemptionRule ( $quote, $address, $item, $rule_id, $is_applicable );
			
			//@nelkaake -a 11/03/11: Calculate any requird discounts
			//echo "Validating {$item->getName()}::{$item->getId()} with rule {$rule->getName()}::{$rule_id} <Br />";
			$new_discounts = $this->_getDiscountCalculator ()->getNewDiscounts ( $quote, $address, $item, $rule_id );
		
		} catch ( Exception $e ) {
			Mage::helper ( 'rewards' )->log ( "An error occured while trying to process shopping cart points rules: " . $e->getMessage () . ". \nDiscounts have been reset as a result." );
			Mage::logException ( $e );
			
			//@nelkaake -a 11/03/11: Reset the discounts as a failsafe.
			$new_discounts = new Varien_Object ( array ('discount_amount' => 0, 'base_discount_amount' => 0 ) );
		}
		
		// Don't apply any discounts to shipping until we assure this feature.
		// TODO allow shipping discounts
		$event->getRule ()->setApplyToShipping ( false );
		$event->getRule ()->setSimpleFreeShipping ( false );
		
		/*
        $new_discounts = new Varien_Object(array(
        	'discount_amount'      => 0,
            'base_discount_amount' => 0,
        ));*/
		
		$original_discounts = $event->getResult ();
		$original_discounts->setDiscountAmount ( $new_discounts->getDiscountAmount () );
		$original_discounts->setBaseDiscountAmount ( $new_discounts->getBaseDiscountAmount () );
		
		if($is_applicable) {
			$rule->setStopRulesProcessing(false);
		}
		
		return $this;
	}
	
	/**
	 * @return TBT_Rewards_Model_Salesrule_Discount_Calculator
	 */
	protected function _getDiscountCalculator() {
		return Mage::getSingleton ( 'rewards/salesrule_discount_calculator' );
	}
	
	/**
	 * @return TBT_Rewards_Model_Salesrule_Discount_Validator
	 */
	protected function _getDiscountValidator() {
		return Mage::getSingleton ( 'rewards/salesrule_discount_validator' );
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $o
	 */
	public function checkRedemptionCouponAfter($o) {
		$this->setRequest ( $o->getControllerAction ()->getRequest () );
		$this->setResponse ( $o->getControllerAction ()->getResponse () );
		$couponCode = ( string ) $this->getRequest ()->getParam ( 'coupon_code' );
		
		//@nelkaake Changed on Saturday August 17, 2010: 
		$current_customer_group_id = Mage::getSingleton ( 'rewards/session' )->getCustomer ()->getGroupId ();
		$current_website_id = Mage::app ()->getStore ()->getWebsiteId ();
		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.1' )) {
			$coupon = Mage::getModel ( 'salesrule/coupon' );
			$coupon->load ( $couponCode, 'code' );
			$rr = Mage::getModel ( 'rewards/salesrule_rule' )->load ( $coupon->getRuleId () );
		} else {
			$rrs = Mage::getModel ( 'rewards/salesrule_rule' )->getCollection ();
			$rrs->addFilter ( "coupon_code", $couponCode );
			$rr = $rrs->getFirstItem ();
		}
		$is_redem = Mage::getSingleton ( 'rewards/salesrule_actions' )->isRedemptionAction ( $rr->getPointsAction () );
		if ($is_redem) {
			if ($couponCode == $this->_getQuote ()->getCouponCode ()) { //applying redemption coupon
				$this->_redirect ( 'rewards/cart_redeem/cartadd', array ("rids" => $rr->getId () ) );
			}
		}
		return $this;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $o
	 */
	public function checkRedemptionCouponBefore($o) {
		$this->setRequest ( $o->getControllerAction ()->getRequest () );
		$this->setResponse ( $o->getControllerAction ()->getResponse () );
		$couponCode = ( string ) $this->getRequest ()->getParam ( 'coupon_code' );
		
		//@nelkaake Changed on Saturday July 17, 2010: 
		$current_customer_group_id = Mage::getSingleton ( 'rewards/session' )->getCustomer ()->getGroupId ();
		$current_website_id = Mage::app ()->getStore ()->getWebsiteId ();
		$rrs = Mage::getModel ( 'rewards/salesrule_rule' )->getCollection ();
		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4.1' )) {
			$rrs->setValidationFilter ( $current_website_id, $current_customer_group_id, $couponCode );
		} else {
			$rrs->addFilter ( "coupon_code", $couponCode );
		}
		$rr = $rrs->getFirstItem ();
		
		$is_redem = Mage::getSingleton ( 'rewards/salesrule_actions' )->isRedemptionAction ( $rr->getPointsAction () );
		if ($is_redem) {
			if ($couponCode != $this->_getQuote ()->getCouponCode ()) { //applying redemption coupon
				$this->_redirect ( 'rewards/cart_redeem/cartremove', array ("rids" => $rr->getId () ) );
			}
		}
		return $this;
	}
	
	/**
	 * This gets triggered before totals are collected so we can reset cart rule discounts
	 * @param Varien_Object $o
	 */
	public function resetCartDiscounts($o) {
		$quote = $o->getEvent ()->getQuote ();
		
		//@nelkaake -a 11/03/11: Reset the counting discount field
		foreach ( $quote->getAllAddresses () as $address ) {
			$address->setRewardsCartRules ( array () );
		}
		
		return $this;
	}
	
	/**
	 * This gets triggered AFTER totals are collected so we can make sure nothing went below 0
	 * Sometimes rounding brings us bellow 0.0000000000001 or we get "-0.00".
	 * @param Varien_Object $o
	 */
	public function cleanup($o) {
		$quote = $o->getEvent ()->getQuote ();
		
		//@nelkaake -a 11/03/11: Reset the counting discount field
		foreach ( $quote->getAllAddresses () as $address ) {
			if ($address->getGrandTotal () <= - 0) {
				$address->setGrandTotal ( 0 );
			}
		}
		
		return $this;
	}
	
	/**
	 * This is meant to reset the discount labels, but it does not work yet.
	 * @param Varien_Object $o
	 */
	public function resetDiscountLabels($o) {
		/*
		 * TODO @nelkaake: This code was written so that it would reset the labels for discounted products, 
		 * but this event doesn't seme to make the event happy nad/or the address model collected here is not 
		 * persisting it's data until the point where the labels are printed. Come back to this later..
		 * 
		$item = $o->getEvent()->getItem();
		
		$quote = $item->getQuote();
		
        foreach ($o->getEvent()->getItem()->getQuote()->getAllAddresses() as $address) {
        	
			$valid_applied = Mage::getModel ( 'rewards/salesrule_list_valid_applied' )->initQuote ( $quote );
        	$applied = Mage::getModel ( 'rewards/salesrule_list_applied' )->initQuote ( $quote );
			
	        $description = $address->getDiscountDescriptionArray();
	        
			foreach($applied->getList() as $rule_id) {
				$rule = Mage::helper('rewards/rule')->getSalesrule($rule_id);
	            if( !isset($description[$rule_id])  ) continue;
	            if( !$rule->isPointsRule())				continue; 
            	if(  $valid_applied->hasRuleId($rule_id)  ) {
					if($rule->isVariablePointsRule() && Mage::getSingleton('rewards/session')->getPointsSpending() == 0) {
						unset($description[$rule_id]);
					} else {
						continue;
					}
            	} else {
            		unset($description[$rule_id]);
            	}
	            
			}
	    	$address->setDiscountDescriptionArray($description);
        }*/
		return $this;
	
	}
	
	/**
	 * Get current active quote instance
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote() {
		return $this->_getCart ()->getQuote ();
	}
	
	/**
	 * Retrieve shopping cart model object
	 *
	 * @return Mage_Checkout_Model_Cart
	 */
	protected function _getCart() {
		return Mage::getSingleton ( 'checkout/cart' );
	}
	
	/**
	 * Set redirect into responce
	 *
	 * @param   string $path
	 * @param   array $arguments
	 */
	protected function _redirect($path, $arguments = array()) {
		$this->getResponse ()->setRedirect ( Mage::getUrl ( $path, $arguments ) );
		return $this;
	}
}