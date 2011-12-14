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
 * Helper Transfer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Transfer extends Mage_Core_Helper_Abstract {
	
	protected $_cr = array (); // deprecated
	protected $_sr = array (); // deprecated
	

	/**
	 * Creates a customer point-transfer of any amount or currency.
	 *
	 * @param  int $num_points    : Quantity of points to transfer: positive=>distribution, negative=>redemption
	 * @param  int $currency_id   : The ID of the point currency used in this transfer
	 * @param  int $reference_type: The type of action from which this transfer originates (Customer order, reviews, etc.)
	 * @param  int $reference_id  :  The ID of the object from which this transfer originates (Order ID, etc.)
	 * @param  int $rule_id       : The ID of the rule that allowed this transfer to be created... RULE MAY HAVE BEEN DISCONTINUED
	 * @return boolean            : whether or not the point-transfer succeeded
	 */
	
	public function transferOrderPoints($num_points, $currency_id, $order_id, $rule_id) {
		$transfer = $this->initTransfer ( $num_points, $currency_id, $rule_id );
		
		if (! $transfer) {
			return false;
		}
		
		// get the default starting status - usually Pending
		if (! $transfer->setStatus ( null, Mage::helper ( 'rewards/config' )->getInitialTransferStatusAfterOrder () )) {
			// we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
			return false;
		}
		if ($num_points > 0) {
			$transfer->setComments ( Mage::getStoreConfig ( 'rewards/transferComments/orderEarned' ) );
		} else if ($num_points < 0) {
			$transfer->setComments ( Mage::getStoreConfig ( 'rewards/transferComments/orderSpent' ) );
		}
		$transfer->setOrderId ( $order_id )->setCustomerId ( Mage::getModel ( 'sales/order' )->load ( $order_id )->getCustomerId () )->save ();
		
		return true;
	}
	
	/**
	 * @deprecated
	 * @see TBT_Rewards_Model_Review_Transfer
	 */
	public function transferReviewPoints($num_points, $currency_id, $review_id, $rule_id) {
		return true;
	}
	
	/**
	 * Creates a customer point-transfer of any amount or currency.
	 *
	 * @param  int $num_points    : Quantity of points to transfer: positive=>distribution, negative=>redemption
	 * @param  int $currency_id   : The ID of the point currency used in this transfer
	 * @param  int $reference_type: The type of action from which this transfer originates (Customer order, reviews, etc.)
	 * @param  int $reference_id  :  The ID of the object from which this transfer originates (Order ID, etc.)
	 * @param  int $rule_id       : The ID of the rule that allowed this transfer to be created... RULE MAY HAVE BEEN DISCONTINUED
	 * @return boolean            : whether or not the point-transfer succeeded
	 */
	public function transferSendfriendPoints($num_points, $currency_id, $rule_id) {
		// TODO WDCA - add a Sendfriend reference type and set it here, using ID -1
		$transfer = $this->initTransfer ( $num_points, $currency_id, $rule_id );
		
		if (! $transfer) {
			return false;
		}
		
		// get the default starting status - usually Pending
		if (! $transfer->setStatus ( null, Mage::helper ( 'rewards/config' )->getInitialTransferStatusAfterSendfriend () )) {
			// we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
			return false;
		}
		$transfer->setComments ( Mage::getStoreConfig ( 'rewards/transferComments/tellAFriendEarned' ) )->setCustomerId ( Mage::getSingleton ( 'customer/session' )->getCustomerId () )->save ();
		
		return true;
	}
	
	/**
	 * Creates a customer point-transfer of any amount or currency.
	 *
	 * @param  int $num_points    : Quantity of points to transfer: positive=>distribution, negative=>redemption
	 * @param  int $currency_id   : The ID of the point currency used in this transfer
	 * @param  int $reference_type: The type of action from which this transfer originates (Customer order, reviews, etc.)
	 * @param  int $reference_id  :  The ID of the object from which this transfer originates (Order ID, etc.)
	 * @param  int $rule_id       : The ID of the rule that allowed this transfer to be created... RULE MAY HAVE BEEN DISCONTINUED
	 * @return boolean            : whether or not the point-transfer succeeded
	 */
	public function transferPollPoints($num_points, $currency_id, $poll_id, $rule_id) {
		$transfer = $this->initTransfer ( $num_points, $currency_id, $rule_id );
		
		if (! $transfer) {
			return false;
		}
		
		// get the default starting status - usually Pending
		if (! $transfer->setStatus ( null, Mage::helper ( 'rewards/config' )->getInitialTransferStatusAfterPoll () )) {
			// we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
			return false;
		}
		$transfer->setPollId ( $poll_id )->setComments ( Mage::getStoreConfig ( 'rewards/transferComments/pollEarned' ) )->setCustomerId ( Mage::getSingleton ( 'customer/session' )->getCustomerId () )->save ();
		
		return true;
	}
	
	/**
	 * @deprecated
	 * @see TBT_Rewards_Model_Tag_Transfer
	 */
	public function transferTagPoints($num_points, $currency_id, $tag_id, $customer_id, $rule_id) {
		return true;
	}
	
	/**
	 * Creates a customer point-transfer of any amount or currency.
	 *
	 * @param  int $num_points    : Quantity of points to transfer: positive=>distribution, negative=>redemption
	 * @param  int $currency_id   : The ID of the point currency used in this transfer
	 * @param  int $reference_type: The type of action from which this transfer originates (Customer order, reviews, etc.)
	 * @param  int $reference_id  :  The ID of the object from which this transfer originates (Order ID, etc.)
	 * @param  int $rule          : The rule model that allowed this transfer to be created... RULE MAY HAVE BEEN DISCONTINUED
	 * @return boolean            : whether or not the point-transfer succeeded
	 */
	public function transferSignupPoints($num_points, $currency_id, $customer_id, $rule) {
		// ALWAYS ensure that we only give an integral amount of points
		$num_points = floor ( $num_points );
		
		if ($num_points == 0) {
			return false;
		}
		
		$transfer = Mage::getModel ( 'rewards/transfer' );
		
		if ($num_points > 0) {
			$transfer->setReasonId ( TBT_Rewards_Model_Transfer_Reason::REASON_CUSTOMER_DISTRIBUTION );
		} else {
			if ((Mage::getModel ( 'rewards/customer' )->getUsablePointsBalance ( $currency_id ) + $num_points) < 0) {
				throw Exception ( 'You do not have enough points for this transacation.' );
			}
			$transfer->setReasonId ( TBT_Rewards_Model_Transfer_Reason::REASON_CUSTOMER_REDEMPTION );
		}
		
		//get On-Hold initial status override
		if ($rule->getOnholdDuration() > 0) {
            $transfer->setEffectiveStart(date('Y-m-d H:i:s', strtotime("+{$rule->getOnholdDuration()} days")))
                ->setStatus(null, TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_TIME);
		} else {
    		//get the default starting status - usually Pending
    		if (! $transfer->setStatus ( null, Mage::helper ( 'rewards/config' )->getInitialTransferStatusAfterSignup () )) {
    			return false;
    		}
		}
		
		$transfer->setId(null)
		    ->setCurrencyId($currency_id)
		    ->setQuantity($num_points)
		    ->setComments(Mage::getStoreConfig('rewards/transferComments/signupEarned'))
		    ->setRuleId($rule->getId())
		    ->setCustomerId($customer_id)
		    ->setAsSignup()
		    ->save();
		
		return true;
	}
	/**
	 * Creates a customer point-transfer of any amount or currency.
	 * TODO Move this into a separate model that extends the Transfer model and instantiate it.
	 *
	 * @param  int $num_points    : Quantity of points to transfer: positive=>distribution, negative=>redemption
	 * @param  int $currency_id   : The ID of the point currency used in this transfer
	 * @param  int $reference_type: The type of action from which this transfer originates (Customer order, reviews, etc.)
	 * @param  int $reference_id  :  The ID of the object from which this transfer originates (Order ID, etc.)
	 * @param  int $rule_id       : The ID of the rule that allowed this transfer to be created... RULE MAY HAVE BEEN DISCONTINUED
	 * @return boolean            : whether or not the point-transfer succeeded
	 */
	public function transferPointsToFriend($num_points, $currency_id, $friend_id, $personal_comment) {
		if (! Mage::getSingleton ( 'customer/session' )->getCustomerId ()) {
			return false;
		}
		
		// ALWAYS ensure that we only give an integral amount of points
		$num_points = floor ( $num_points );
		
		if ($num_points == 0) {
			return false;
		}
		
		$recipient_transfer = Mage::getModel ( 'rewards/transfer' );
		$sender_transfer = Mage::getModel ( 'rewards/transfer' );
		
		// get the default starting status - usually Pending
		if (! $recipient_transfer->setStatus ( null, Mage::helper ( 'rewards/config' )->getInitialTransferToFriendStatus () )) {
			// we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
			return false;
		}
		// get the default starting status - usually Pending
		if (! $sender_transfer->setStatus ( null, Mage::helper ( 'rewards/config' )->getInitialTransferToFriendStatus () )) {
			// we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
			return false;
		}
		
		$recipient_transfer->setReasonId ( TBT_Rewards_Model_Transfer_Reason::REASON_FROM_CUSTOMER );
		$sender_transfer->setReasonId ( TBT_Rewards_Model_Transfer_Reason::REASON_TO_CUSTOMER );
		
		$to_customer = Mage::getModel ( 'customer/customer' )->load ( $friend_id );
		$from_customer = Mage::getModel ( 'customer/customer' )->load ( Mage::getModel ( 'customer/session' )->getCustomerId () );
		
		$customer = Mage::getModel ( 'rewards/customer' )->load ( Mage::getModel ( 'customer/session' )->getCustomerId () );
		if (($customer->getUsablePointsBalance ( $currency_id ) + $num_points) < 0) {
			$error = $this->__ ( 'Not enough points for transaction. You have %s, but you need %s', Mage::getModel ( 'rewards/points' )->set ( $currency_id, $customer->getUsablePointsBalance ( $currency_id ) ), Mage::getModel ( 'rewards/points' )->set ( $currency_id, $num_points * - 1 ) );
			throw new Exception ( $error );
		}
		
		$default_send_comment = Mage::getStoreConfig ( 'rewards/transferComments/sendToFriend' );
		$default_send_comment = str_replace('\n', "\n", $default_send_comment);
		$sender_comments = $this->__ ($default_send_comment , $to_customer->getName (), $personal_comment );
		
		$sender_transfer->setId ( null )->setCurrencyId ( $currency_id )->setQuantity ( $num_points * - 1 )
		        ->setCustomerId ( $from_customer->getId () )->setToFriendId ( $to_customer->getId () )
		        ->setComments ( $sender_comments )->save ();
		
		$default_receive_comment = Mage::getStoreConfig ( 'rewards/transferComments/receiveFromFriend' );
		$default_receive_comment = str_replace('\n', "\n", $default_receive_comment);
		$receiver_comment = $this->__ ( $default_receive_comment, $from_customer->getName (), $personal_comment );
		
		$recipient_transfer->setId ( null )->setCurrencyId ( $currency_id )
		        ->setQuantity ( $num_points )->setCustomerId ( $to_customer->getId () )
		        ->setFromFriendId ( $from_customer->getId () )->setComments ( $receiver_comment )->save ();
		
		return true;
	}
	
	/**
	 * Initiates a transfer model based on given criteria and verifies usage.
	 * 
	 * @deprecated As of Sweet Tooth 1.5 and up functions should call their own
	 * derivation of the TBT_Rewards_Model_Transfer model which contains this method.
	 *
	 * @param integer $num_points
	 * @param integer $currency_id
	 * @param integer $rule_id
	 * @return TBT_Rewards_Model_Transfer
	 */
	public function initTransfer($num_points, $currency_id, $rule_id) {
		if (! Mage::getSingleton ( 'rewards/session' )->isCustomerLoggedIn () && ! Mage::getSingleton ( 'rewards/session' )->isAdminMode ()) {
			return null;
		}
		// ALWAYS ensure that we only give an integral amount of points
		$num_points = floor ( $num_points );
		
		if ($num_points == 0) {
			return null;
		}
		
		$transfer = Mage::getModel ( 'rewards/transfer' );
		
		if ($num_points > 0) {
			$transfer->setReasonId ( TBT_Rewards_Model_Transfer_Reason::REASON_CUSTOMER_DISTRIBUTION );
		} else {
			$customer = Mage::getModel ( 'rewards/customer' )->load ( Mage::getSingleton ( 'customer/session' )->getCustomerId () );
			if (($customer->getUsablePointsBalance ( $currency_id ) + $num_points) < 0) {
				
				$error = $this->__ ( 'Not enough points for transaction. You have %s, but you need %s.', Mage::getModel ( 'rewards/points' )->set ( $currency_id, $customer->getUsablePointsBalance ( $currency_id ) ), Mage::getModel ( 'rewards/points' )->set ( $currency_id, $num_points * - 1 ) );
				throw new Exception ( $error );
			}
			
			$transfer->setReasonId ( TBT_Rewards_Model_Transfer_Reason::REASON_CUSTOMER_REDEMPTION );
		}
		
		$transfer->setId ( null )->setCreationTs ( now () )->setLastUpdateTs ( now () )->setCurrencyId ( $currency_id )->setQuantity ( $num_points )->setRuleId ( $rule_id );
		
		return $transfer;
	}
	
	/**
	 * Gets a list of all rule ID's that are associated with the given order/shoppingcart/quote.
	 * @deprecated.  Use order->getAPpliedDistriCartRuleIds() instead.     
	 *
	 * @param   Mage_Sales_Model_Order  $order  : The order object with which the returned rules are associated
	 * @return  array(int)                      : An array of rule ID's that are associated with the order
	 */
	public function getCartRewardsRuleIds($order) {
		/* TODO: make this method return REWARDS-SYSTEM rule id's ONLY */
		/* TODO - from JAY: You can do this by using the rewards/catalog_rule or rewards_salesrule_rule models. */
		// look up all rule ID's associated with this order, or shopping cart
		$rule_ids_string = $order->getAppliedRuleIds ();
		if (empty ( $rule_ids_string )) {
			$rule_ids = array ();
		} else {
			$rule_ids = explode ( ',', $rule_ids_string );
			$rule_ids = array_unique ( $rule_ids );
		}
		return $rule_ids;
	}
	
	/**
	 * Gets a list of all rule ID's that are associated with the given item.
	 *
	 * @param   Mage_Sales_Model_Quote_Item $item   : The item object with which the returned rules are associated
	 * @return  array(int)                          : An array of rule ID's that are associated with the item
	 */
	public function getCatalogRewardsRuleIds($item, $wId = null) {
		return $this->getCatalogRewardsRuleIdsForProduct ( $item->getProductId (), $wId );
	}
	
	/**
	 * Gets a list of all rule ID's that are associated with the given product id.
	 * @see THIS GETS ALL RULES!!!!!!
	 *
	 * @param   int $productId   					: The item id for with which the returned rules are associated
	 * @return  array(int)                          : An array of rule ID's that are associated with the item
	 */
	public function getCatalogRewardsRuleIdsForProduct($productId, $wId = null, $gId = null) {
		$p = Mage::getModel ( 'rewards/catalog_product' )->load ( $productId );
		$rules = $p->getCatalogRewardsRuleIdsForProduct ( $wId, $gId );
		return $rules;
	}
	
	/**
	 * @nelkaake Wednesday May 5, 2010: move to another helper or model class
	 * @param unknown_type $item
	 * @return unknown
	 */
	public function getEarnedPointsOnItem($item) {
		$points_to_earn = ( array ) Mage::helper ( 'rewards' )->unhashIt ( $item->getEarnedPointsHash () );
		
		$currency_points = array ();
		
		$item_has_points = false;
		if ($points_to_earn) {
			foreach ( $points_to_earn as $points ) {
				if ($points) {
					$item_has_points = true;
					$points = ( array ) $points;
					if (isset ( $currency_points [$points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID]] )) {
						$currency_points [$points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID]] += ($points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT] * $points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY]);
					} else {
						$currency_points [$points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID]] = ($points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT] * $points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY]);
					}
				}
			}
		}
		
		return $currency_points;
	}
	
	/**
	 * @deprecated Do not use.  For reference purposes only.
	 */
	public function getDiscountDueToPointsForCart($cart) {
		$discount = 0.0;
		foreach ( $cart->getAllItems () as $item ) {
			$discount += $item->getRowTotalBeforeRedemptions () - $item->getRowTotal ();
		}
		return number_format ( $discount, 2 );
	}
	
	/**
	 * Returns the rewards catalogrule points action singleton
	 *
	 * @return TBT_Rewards_Model_Catalogrule_Actions
	 */
	private function getActionsSingleton() {
		return Mage::getSingleton ( 'rewards/catalogrule_actions' );
	}
	
	/**
	 * returns an empty product if the product model could not be found
	 *
	 * @param   Mage_Sales_Model_Quote_Item||TBT_Rewards_Model_Catalog_Product $item				: the catalog item associated
	 * @return TBT_Rewards_Model_Catalog_Product
	 */
	private function assureProduct($item) {
		if ($item instanceof TBT_Rewards_Model_Catalog_Product) {
			$product = &$item;
		} else if ($item instanceof Mage_Catalog_Model_Product) {
			$product = $this->assureProduct ( TBT_Rewards_Model_Catalog_Product::wrap ( $item ) );
		} else if ($this->hasGetProductFunc ( $item )) {
			$product = $item->getProduct ();
			if (empty ( $product )) {
				$product = Mage::getModel ( 'rewards/catalog_product' );
			}
		} else {
			$product = Mage::getModel ( 'rewards/catalog_product' );
		}
		return $product;
	}
	
	private function hasGetProductFunc($obj) {
		$ret = false;
		if ($this->isItem ( $obj ) || $obj instanceof Varien_Object) { // params are function($rule)
			$ret = true;
		}
		return $ret;
	}
	
	private function isItem($obj) {
		$ret = false;
		if ($obj instanceof Mage_Sales_Model_Quote_Item || $obj instanceof Mage_Sales_Model_Quote_Item_Abstract || $obj instanceof Mage_Sales_Model_Quote_Address_Item || $obj instanceof Mage_Sales_Model_Order_Item || $obj instanceof Mage_Sales_Model_Order_Invoice_Item || $obj instanceof Mage_Sales_Model_Order_Creditmemo_Item || $obj instanceof Mage_Sales_Model_Order_Shipment_Item) { // params are function($rule)
			$ret = true;
		}
		return $ret;
	}

    /**
     * Calculates the amount of points to be given or deducted from a customer based on catalog item,
     * given the rule that is being executed and the item that caused the rule to run.
     * @nelkaake Wednesday May 5, 2010: TODO move this to something other than the transfer helper      *      
     *
     * @param   int                         $rule_id			: the ID of the rule to execute
     * @param   Mage_Sales_Model_Quote_Item||TBT_Rewards_Model_Catalog_Product $item				: the catalog item associated
     * @param	bool						$allow_redemptions	: whether or not to calculate redemptions, if given
     * @return  array											: 'amount' & 'currency' as keys
     */
    public function calculateCatalogPoints($rule_id, $item, $allow_redemptions) {
        Varien_Profiler::start("TBT_Rewards:: Catalog points calculator");
        
        // Load the rule and product model.
        $rule = $this->getCatalogRule($rule_id);
        $product = $this->assureProduct($item);
        
        // Get the store configuration
        $prices_include_tax = Mage::helper('tax')->priceIncludesTax();
        
        // Instantiate what the product cost will be evaluated to
        if ( ! $product->getCost() ) {
            $product = $product->load($product->getId());
        }
        $product_cost = (int) $product->getCost();
        
        if ( $this->isItem($item) ) {
            $qty = ($item->getQty() > 0) ? $item->getQty() : 1; //@nelkaake 04/03/2010 2:05:12 PM (terniary check jsut in case)
            if ( $prices_include_tax ) {
                //@nelkaake Changed on Wednesday May 5, 2010: 
                $price = $item->getBaseRowTotal();
                if ( Mage::helper('rewards/config')->earnCatalogPointsForTax() ) {
                    $price += $item->getBaseTaxAmount();
                }
            } else {
                $price = $item->getBaseRowTotal();
            }
            $profit = $item->getBaseRowTotal() - ($product_cost * $qty); //@nelkaake 04/03/2010 2:05:12 PM
        } else {
            //@nelkaake Changed on Wednesday May 5, 2010: 
            $qty = 1; //@nelkaake 04/03/2010 2:05:12 PM
            $price = $product->getFinalPrice();
            $profit = $product->getFinalPrice() - $product_cost;
            
            //@nelkaake Added on Wednesday May 5, 2010: 
            if ( ! Mage::helper('rewards/config')->earnCatalogPointsForTax() && $prices_include_tax ) {
                $price = $price / (1 + ((float) $product->getTaxPercent() / 100));
                $profit = $price - $product_cost;
            }
        }
        
        // Set default price and profit values
        if ( $profit < 0 ) {
            $profit = 0;
        }
        if ( $price < 0 ) {
            $price = 0;
        }
        
        if ( $rule->getId() ) {
            if ( $rule->getPointsAction() == 'give_points' ) {
                    // give a flat number of points if this rule's conditions are met
                // since this is a catalog rule, the points are relative to the quantity
                $points_to_transfer = $rule->getPointsAmount() * $qty; //@nelkaake 04/03/2010 2:05:12 PM
            } elseif ( ($rule->getPointsAction() == 'deduct_points') && $allow_redemptions ) {
                // deduct a flat number of points if this rule's conditions are met
                // since this is a catalog rule, the points are relative to the quantity
                $points_to_transfer = $rule->getPointsAmount() * - 1;
            } elseif ( $rule->getPointsAction() == 'give_by_amount_spent' || $rule->getPointsAction() == 'give_by_profit' ) {
                if ( $rule->getPointsAction() == 'give_by_amount_spent' ) {
                    $value = $price;
                } elseif ( $rule->getPointsAction() == 'give_by_profit' ) {
                    $value = $profit;
                } else {
                    $value = 0;
                }
                
                // give a set qty of points per every given amount spent if this rule's conditions are met
                $points_to_transfer = $rule->getPointsAmount() * floor(round($value / $rule->getPointsAmountStep(), 5));
                if ( $rule->getPointsMaxQty() > 0 ) {
                    if ( $points_to_transfer > $rule->getPointsMaxQty() ) {
                        $points_to_transfer = $rule->getPointsMaxQty();
                    }
                }
                if ( $points_to_transfer < 0 ) {
                    $points_to_transfer = 0;
                }
            } elseif ( ($rule->getPointsAction() == 'deduct_by_amount_spent') && $allow_redemptions ) {
                // deduct a set qty of points per every given amount spent if this rule's conditions are met
                $price = $product->getFinalPrice();
                $points_to_transfer = $rule->getPointsAmount() * floor(round($price / $rule->getPointsAmountStep(), 5)) * - 1;
                
                if ( $rule->getPointsMaxQty() > 0 ) {
                    if ( $points_to_transfer < ($rule->getPointsMaxQty() * - 1) ) {
                        $points_to_transfer = $rule->getPointsMaxQty() * - 1;
                    }
                }
            } else {
                // whatever the Points Action is set to is invalid
                // - this means no transfer of points
                

                Varien_Profiler::stop("TBT_Rewards:: Catalog points calculator");
                return null;
            }
            
            //@nelkaake Added on Sunday May 30, 2010: 
            if ( $max_points_spent = $rule->getPointsMaxQty() * $qty ) {
                if ( $points_to_transfer < 0 ) {
                    if ( - $points_to_transfer > $max_points_spent ) $points_to_transfer = - $max_points_spent;
                } else {
                    if ( $points_to_transfer > $max_points_spent ) $points_to_transfer = $max_points_spent;
                }
            }
            
            Varien_Profiler::stop("TBT_Rewards:: Catalog points calculator");
            return array(
                'amount' => $points_to_transfer, 
                'currency' => $rule->getPointsCurrencyId()
            );
        }
        
        Varien_Profiler::stop("TBT_Rewards:: Catalog points calculator");
        return null;
    }
	
	/**
	 * Calculates the amount of points to be given or deducted from a customer's cart, given the
	 * rule that is being executed and possibly a list of items to act upon, if applicable.
	 *
	 * @deprecated ??? not sure if this is deprecated... see calculateCartPoints in Rewards/session singleton
	 * 
	 * @param   int                                 $rule_id            : the ID of the rule to execute
	 * @param   array(Mage_Sales_Model_Quote_Item)  $order_items        : the list of items to act upon
	 * @param   boolean                             $allow_redemptions  : whether or not to calculate redemption rules
	 * @return  array                                                   : 'amount' & 'currency' as keys
	 */
	public function calculateCartDiscounts($rule_id, $order_items) {
		$rule = $this->getSalesRule ( $rule_id );
		$crActions = $this->getActionsSingleton ();
		
		if ($rule->getId ()) {
			if ($crActions->isDeductPointsAction ( $rule->getPointsAction () )) {
				// give a flat number of points if this rule's conditions are met
				$discount = $rule->getDiscountAmount ();
			} else if ($crActions->isDeductByAmountSpentAction ( $rule->getPointsAction () )) {
				// deduct a set qty of points per every given amount spent if this rule's conditions are met
				// - this is a total price amongst ALL associated items, so add it up
				$price = $this->getTotalAssociatedItemPrice ( $order_items, $rule->getId () );
				$points_to_transfer = $rule->getPointsAmount () * floor ( round($price / $rule->getPointsAmountStep (), 5) );
				
				if ($rule->getPointsMaxQty () > 0) {
					if ($points_to_transfer > $rule->getPointsMaxQty ()) {
						$points_to_transfer = $rule->getPointsMaxQty ();
					}
				}
				
				$discount = $rule->getDiscountAmount () * ($points_to_transfer / $rule->getPointsAmount ());
			} else if ($rule->getPointsAction () == 'deduct_by_qty') {
				// deduct a set qty of points per every given qty of items if this rule's conditions are met
				// - this is a total quantity amongst ALL associated items, so add it up
				$qty = $this->getTotalAssociatedItemQty ( $order_items, $rule->getId () );
				$points_to_transfer = $rule->getPointsAmount () * ($qty / $rule->getPointsQtyStep ());
				
				if ($rule->getPointsMaxQty () > 0) {
					if ($points_to_transfer > $rule->getPointsMaxQty ()) {
						$points_to_transfer = $rule->getPointsMaxQty ();
					}
				}
				
				$discount = $rule->getDiscountAmount () * ($points_to_transfer / $rule->getPointsAmount ());
			} else {
				// whatever the Points Action is set to is invalid
				// - this means no transfer of points
				$discount = 0;
			}
			
			return $discount;
		}
		
		return 0;
	}
	
	/**
	 * Accumulates the quantity of all items out of a list that are associated with a given rule.
	 *
	 * @param   array(Mage_Sales_Model_Quote_Item)  $order_items    : list of items to look in
	 * @param   int                                 $required_id    : ID of the rule with which to filter
	 * @return  int                                                 : the total quantity of all associated items
	 */
	public function getTotalAssociatedItemQty($order_items, $required_id) {
		$qty = 0;
		
		foreach ( $order_items as $item ) {
			// look up item rule ids
			$item_rule_ids = explode ( ',', $item->getAppliedRuleIds () );
			$item_rule_ids = array_unique ( $item_rule_ids );
			
			// TODO WDCA - change this inner loop into an array_search
			foreach ( $item_rule_ids as $item_rule_id ) {
				// instantiate an item rule and dump its data
				$item_rule = $this->getSalesRule ( $item_rule_id );
				
				if ($item_rule->getId () == $required_id) {
					// add this associated item's quantity to the running total
					if ($item->getOrderId ()) {
						$qty += $item->getQtyOrdered ();
					} else if ($item->getQuoteId ()) {
						$qty += $item->getQty ();
					}
					break;
				}
			}
		}
		
		return $qty;
	}
	
	/**
	 * Accumulates the price of all items out of a list that are associated with a given rule.
	 *
	 * @nelkaake Wednesday May 5, 2010: This should be moved to some other helper, not Transfer helper.     
	 * @nelkaake Added on Friday June 11, 2010: Added use_salesrule parameter
	 * @param   array(Mage_Sales_Model_Quote_Item)  $order_items    : list of items to look in. Could be array or an object that implements an itteratable interface
	 * @param   int                                 $required_id    : ID of the rule with which to filter
	 * @param   TBT_Rewards_Model_Salesrule_Rule    [$use_salesrule=null]   : salesrule if this is a salesrule check
	 * @param  $prediction_mode									: if enabled will add prices even though they may not be applied to the items    
	 * @return  float                                               : the total price of all associated items
	 */
	public function getTotalAssociatedItemPrice($order_items, $required_id, $use_salesrule = null, $prediction_mode = false) {
		$price = 0;
		
		// Get the store configuration
		$prices_include_tax = Mage::helper ( 'tax' )->priceIncludesTax ();
		
		foreach ( $order_items as $item ) {
			if ($this->_skipItemSumCalc($item))
				continue;
			
		//@nelkaake Added on Friday June 11, 2010: 
			if ($use_salesrule != null) {
				if (! Mage::getSingleton ( 'rewards/salesrule_validator' )->itemHasAppliedRid ( $item->getId (), $required_id )) {
					continue;
				}
			}
			// look up item rule ids
			$item_rule_ids = explode ( ',', $item->getAppliedRuleIds () );
			$item_rule_ids = $prediction_mode ? array ($required_id ) : $item_rule_ids;
			$item_rule_ids = array_unique ( $item_rule_ids );
			
			foreach ( $item_rule_ids as $item_rule_id ) {
				// instantiate an item rule and dump its data
				$item_rule = $this->getSalesRule ( $item_rule_id );
				
				if ($item_rule->getId () == $required_id) {             
					if (Mage::helper ( 'rewards/config' )->calcCartPointsAfterDiscount ()) {						
						// add this associated item's quantity-price to the running total
						if ($prices_include_tax) {
							$price += Mage::helper('rewards/price')->getReversedCurrencyPrice($item->getRowTotalAfterRedemptionsInclTax());
						} else {													
							$price += Mage::helper('rewards/price')->getReversedCurrencyPrice($item->getRowTotalAfterRedemptions());
						}																		
					} else {						
						// add this associated item's quantity-price to the running total
						if ($prices_include_tax) {
							$price += Mage::helper('rewards/price')->getReversedCurrencyPrice($item->getRowTotalBeforeRedemptionsInclTax());
						} else {													
							$price += Mage::helper('rewards/price')->getReversedCurrencyPrice($item->getRowTotalBeforeRedemptions());
						}																								
					}	
					break;														
				}				
			}
		}
		
		if ($price < 0.00001 && $price > - 0.00001) {
			$price = 0;
		}
		return $price;
	}
        
                
        /**
	 * 
	 * @param Mage_Sales_Model_Quote_Address_Item $item
	 */
	protected function _skipItemSumCalc($item) {
	    if($item->getParentItem () ) {
	        if(($item->getParentItem()->getProductType() != 'bundle')) {
	            return true;
	        }
	    }
	    return false;
	}
	
        
	
	/**
	 * Accumulates the profit of all items out of a list that are associated with a given rule.
	 *
	 * @param   array(Mage_Sales_Model_Quote_Item)  $order_items    : list of items to look in
	 * @param   int                                 $required_id    : ID of the rule with which to filter
	 * @return  float                                               : the total profit of all associated items
	 */
	public function getTotalAssociatedItemProfit($order_items, $required_id) {
		$profit = 0;
		
		foreach ( $order_items as $item ) {
			// look up item rule ids
			$item_rule_ids = explode ( ',', $item->getAppliedRuleIds () );
			$item_rule_ids = array_unique ( $item_rule_ids );
			
			foreach ( $item_rule_ids as $item_rule_id ) {
				// instantiate an item rule and dump its data
				$item_rule = $this->getSalesRule ( $item_rule_id );
				
				if ($item_rule->getId () == $required_id) {
					// add this associated item's quantity-price to the running total
					$profit += $item->getPrice () - $item->getCost ();
					break;
				}
			}
		}
		
		return $profit;
	}
	
	
	
	/**
	 * Creates a customer point-transfer of any amount or currency.
	 * @deprecated Use TBT_Rewards_Model_Transfer::revoke()
	 *
	 * @param  int $num_points    : Quantity of points to transfer: positive=>distribution, negative=>redemption
	 * @param  int $currency_id   : The ID of the point currency used in this transfer
	 * @param  int $reference_type: The type of action from which this transfer originates (Customer order, reviews, etc.)
	 * @param  int $reference_id  :  The ID of the object from which this transfer originates (Order ID, etc.)
	 * @param  int $rule_id       : The ID of the rule that allowed this transfer to be created... RULE MAY HAVE BEEN DISCONTINUED
	 * @return boolean            : whether or not the point-transfer succeeded
	 */
	public function transferRevokedPoints($num_points, $currency_id, $reference_transfer_id, $customer_id) {
		return ($this->createRevokedTransfer ( $num_points, $currency_id, $reference_transfer_id, $customer_id ) != 0);
	}
	
	/**
	 * Creates a customer point-transfer of any amount or currency.
	 * @deprecated Use TBT_Rewards_Model_Transfer::revoke()
	 *
	 * @param  int $num_points    : Quantity of points to transfer: positive=>distribution, negative=>redemption
	 * @param  int $currency_id   : The ID of the point currency used in this transfer
	 * @param  int $reference_type: The type of action from which this transfer originates (Customer order, reviews, etc.)
	 * @param  int $reference_id  :  The ID of the object from which this transfer originates (Order ID, etc.)
	 * @param  int $rule_id       : The ID of the rule that allowed this transfer to be created... RULE MAY HAVE BEEN DISCONTINUED
	 * @return boolean            : whether or not the point-transfer succeeded
	 */
	public function createRevokedTransfer($num_points, $currency_id, $reference_transfer_id, $customer_id) {
		// ALWAYS ensure that we only give an integral amount of points
		$num_points = floor ( $num_points );
		
		if ($num_points == 0) {
			return 0;
		}
		
		$transfer = Mage::getModel ( 'rewards/transfer' );
		
		// get the default starting status - usually Pending
		if (! $transfer->setStatus ( null, TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED )) {
			// we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
			return 0;
		}
		
		$customer = Mage::getModel ( 'rewards/customer' )->load ( $customer_id );
		if (($customer->getUsablePointsBalance ( $currency_id ) + $num_points) < 0) {
			$error = $this->__ ( 'Not enough points for transaction. You have %s, but you need %s', Mage::getModel ( 'rewards/points' )->set ( $currency_id, $customer->getUsablePointsBalance ( $currency_id ) ), Mage::getModel ( 'rewards/points' )->set ( $currency_id, $num_points * - 1 ) );
			throw new Exception ( $error );
		}
		
		$original_transfer = Mage::getModel ( 'rewards/transfer' )->load ( $reference_transfer_id );
		
		$transfer->setId ( null )->setCurrencyId ( $currency_id )->setQuantity ( $num_points )->setCustomerId ( $customer_id )->setReferenceTransferId ( $reference_transfer_id )->setReasonId ( TBT_Rewards_Model_Transfer_Reason::REASON_SYSTEM_REVOKED )->setComments ( Mage::getStoreConfig ( 'rewards/transferComments/revoked' ), $original_transfer->getComments () )->save ();
		
		return $transfer->getId ();
	}
	
	
	/**
	 * Fetches a cached shopping cart rule model
	 *
	 * @param integer $rule_id
	 * @return TBT_Rewards_Model_Salesrule_Rule
	 */
	public function &getSalesRule($rule_id) {
		return Mage::helper ( 'rewards/rule' )->getSalesRule ( $rule_id );
	}
	
	/**
	 * Fetches a cached catalog rule model
	 *
	 * @param integer $rule_id
	 * @return TBT_Rewards_Model_Catalogrule_Rule
	 */
	public function &getCatalogRule($rule_id) {
		return Mage::helper ( 'rewards/rule' )->getCatalogRule ( $rule_id );
	}

}