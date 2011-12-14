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
 * Product View Points
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Sales_Order_Points extends TBT_Rewards_Block_Sales_Order_Abstract {
	
	/**
	 * Returns a string containing the total number of points sepnt for this order. 
	 * @return string
	 */
	public function getPointsEarnedString() {
		$order = $this->getOrder ();
		$points_earned = $order->getTotalEarnedPoints ();
		$earned_str = Mage::helper ( 'rewards' )->getPointsString ( $points_earned );
		return $earned_str;
	}
	
	/**
	 * Returns a string containing the total number of points earned for this order. 
	 * @return string
	 */
	public function getPointsSpentString() {
		$order = $this->getOrder ();
		$points_spent = $order->getTotalSpentPoints ();
		$spent_str = Mage::helper ( 'rewards' )->getPointsString ( $points_spent );
		return $spent_str;
	}
	

	/**
	 * Returns the catalog points discount for the order
	 */
	public function getCatalogSpendingDiscount() {
		$order = $this->getOrder ();
		
		$rewards_discount_amount = $order->getRewardsDiscountAmount();
		$rewards_discount_amount_str = $this->getOrder()->getStore()->formatPrice($rewards_discount_amount, false);
		
		return $rewards_discount_amount_str;
	}

    /**
     * Returns true if catalog points were spent on this order. 
     * @return boolean
     */
    public function hasCatalogSpendingDiscount() {
        $csd = $this->getOrder()->getRewardsDiscountAmount();
        
        //convert it to float if it's not already empty, just in case we have a string like '0.000'.
        $csd = empty( $csd )   ?   $csd   :   ((float) $csd);
        
        return ! empty( $csd );
    }

	
	public function getTotalsBlockName() {
	    return 'order_points';
	}
	public function getTotalsCode() {
	    return 'rewards';
	}
	
	
}