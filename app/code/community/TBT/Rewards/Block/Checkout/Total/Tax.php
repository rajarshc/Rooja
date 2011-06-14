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
 * Modified tax renderer 
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Checkout_Total_Tax extends Mage_Checkout_Block_Total_Tax {
	
	// Change the template to be the Sweet Tooth one so that we can display the tax discount if we need to
	protected $_template = 'rewards/checkout/total/tax.phtml';
	
	public function getTotalInclCatalogDiscounts() {
		$total = $this->getTotal ()->getValue ();
		
		// If tax is included in the produt price then we should subtract the change in tax price from the 
		// tax total visually only.
		$store = $this->getTotal ()->getAddress ()->getQuote ()->getStore ();
		if (Mage::helper ( 'tax' )->priceIncludesTax ( $store )) {
			$total -= $this->getTotal ()->getAddress ()->getQuote ()->getRewardsDiscountTaxAmount ();
		}
		return $total;
	}
	
	/**
	 * Retrieve block view from file (template)
	 *
	 * @param   string $fileName
	 * @return  string
	 */
	public function fetchView($fileName) {
		$fileName = Mage::helper ( 'rewards/theme' )->getViewPath ( $fileName );
		
		return parent::fetchView ( $fileName );
	}
}