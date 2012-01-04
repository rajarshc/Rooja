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
 * @copyright  Copyright (c) 2011 WDCA (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * AJAX Cart Pro Observer compatibility rewrite
 * Adds compatibility with AheadWork's AJAX Cart Pro Extension 
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
if(class_exists('AW_Ajaxcartpro_Model_Observer')) {
    
class TBT_Rewards_Model_Ajaxcartpro_Observer extends AW_Ajaxcartpro_Model_Observer {
	
    /**
     * @override $compare_version::addToCartEvent to make sure that Sweet TOoth 
     * points information is properly submitted with the product submit form.
     * @param unknown_type $observer
     */
	public function addToCartEvent($observer) {
	    // In version 2.2.3+ this is not needed.
	    if($this->_awVerAtLeast('2.2.3')) {
	        return parent::addToCartEvent ( $observer );
	    }
		// Triger the Sweet Tooth redemption catalogrule observer
		$st_obs = new TBT_Rewards_Model_Catalogrule_Observer ();
		$st_obs->appendPointsQuote ( $observer );
		
		// Trigger AheadWork's code
		parent::addToCartEvent ( $observer );
	}
	
	/**
	 * Return true if the version of AW Ajax Cart Pro is at least $compare_version
	 * @param string $compare_version
	 */
	protected function _awVerAtLeast($compare_version) {
	    $aw_version = (string) Mage::getConfig()->getNode('modules/AW_Ajaxcartpro/version');
	    
	    $version_match = version_compare($aw_version, $compare_version, '>=');
	    
	    return $version_match; 
	}

}

}

?>
