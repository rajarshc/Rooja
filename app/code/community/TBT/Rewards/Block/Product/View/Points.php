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
class TBT_Rewards_Block_Product_View_Points extends TBT_Rewards_Block_Product_View_Points_Abstract {
	
	protected function _construct() {
		parent::_construct ();
		//@nelkaake Wednesday March 10, 2010 10:04:28 PM : New caching functionality
		$this->setCacheLifetime ( 86400 );
	}
	
	protected function _prepareLayout() {
		//@nelkaake Wednesday March 10, 2010 10:04:42 PM : New caching functionality 
		//@nelkaake -a 5/11/10: New cache key generation method
		$this->setCacheKey ( $this->_genCacheKey () );
		return parent::_prepareLayout ();
	}
	
	/**
	 * Generate a cache key using data from the customer
	 * @return string cache key
	 */
	protected function _genCacheKey() {
                        
            $nameInLayout = $this->getNameInLayout();
            $blockType = $this['type'];
            $product_id = $this->getProduct ()->getId ();
            $website_id = Mage::app ()->getWebsite ()->getId ();
            $customer_group_id = $this->_getRS ()->getCustomerGroupId ();
            $lang = Mage::getStoreConfig('general/locale/code');

            $key = "rewards_product_view_points_{$nameInLayout}_{$blockType}_{$product_id}_{$website_id}_{$customer_group_id}_{$lang}";
            if ($this->_getRS ()->isCustomerLoggedIn ()) {
    		    $customer = Mage::getModel('rewards/customer')->getRewardsCustomer($this->_getRS()->getCustomer());
    			$pts = (string)$customer->getPointsSummary();
                $pts = strtolower(str_replace(' ', '_', $pts));
                $pts = preg_replace ( '/[^a-z0-9_]/', '', $pts );
                $key = $key . "_{$pts}";
            }
            return $key;
	}
	/**
	 * Calculates all the points being earned from distribution rules.
	 *
	 * @return array (I think)
	 */
	public function getDistriRules() {
		return $this->getProduct ()->getDistriRules ();
	}
	
	/**
	 * Get distribution rule rewards.
	 * Sums up the rewards in the standard currency=>amt array format
	 *
	 * @return array
	 */
	public function getDistriRewards() {
		return $this->getProduct ()->getDistriRewards ();
	}
	
	public function getEarnedHtml() {
		$this->getChild ( "points_earned" )->setProduct ( $this->getProduct () );
		return $this->getChildHtml ( "points_earned" );
	}
	
	public function getRedeemedHtml() {
		$this->getChild ( "points_redeemed" )->setProduct ( $this->getProduct () );
		return $this->getChildHtml ( "points_redeemed" );
	}
	
	public function printOptionsPrice() {
		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4' ) == false)
			return false;
		
		return ! $this->hasOptions ();
	}
	
	/**
	 * Get JSON encripted configuration array which can be used for JS dynamic
	 * price calculation depending on product options
	 *
	 * @return string
	 */
	public function getJsonConfig() {
		
		$config = array ();
		
		$_request = Mage::getSingleton ( 'tax/calculation' )->getRateRequest ( false, false, false );
		$_request->setProductClassId ( $this->getProduct ()->getTaxClassId () );
		$defaultTax = Mage::getSingleton ( 'tax/calculation' )->getRate ( $_request );
		
		$_request = Mage::getSingleton ( 'tax/calculation' )->getRateRequest ();
		$_request->setProductClassId ( $this->getProduct ()->getTaxClassId () );
		$currentTax = Mage::getSingleton ( 'tax/calculation' )->getRate ( $_request );
		
		$_regularPrice = $this->getProduct ()->getPrice ();
		$_finalPrice = $this->getProduct ()->getFinalPrice ();
		$_priceInclTax = Mage::helper ( 'tax' )->getPrice ( $this->getProduct (), $_finalPrice, true );
		$_priceExclTax = Mage::helper ( 'tax' )->getPrice ( $this->getProduct (), $_finalPrice );
		
		$config = array ('productId' => $this->getProduct ()->getId (), 'priceFormat' => Mage::app ()->getLocale ()->getJsPriceFormat (), 'includeTax' => Mage::helper ( 'tax' )->priceIncludesTax () ? 'true' : 'false', 'showIncludeTax' => Mage::helper ( 'tax' )->displayPriceIncludingTax (), 'showBothPrices' => Mage::helper ( 'tax' )->displayBothPrices (), 'productPrice' => Mage::helper ( 'core' )->currency ( $_finalPrice, false, false ), 'productOldPrice' => Mage::helper ( 'core' )->currency ( $_regularPrice, false, false ), 'skipCalculate' => ($_priceExclTax != $_priceInclTax ? 0 : 1), 'defaultTax' => $defaultTax, 'currentTax' => $currentTax, 'idSuffix' => '_clone', 'oldPlusDisposition' => 0, 'plusDisposition' => 0, 'oldMinusDisposition' => 0, 'minusDisposition' => 0 );
		
		$responseObject = new Varien_Object ();
		Mage::dispatchEvent ( 'catalog_product_view_config', array ('response_object' => $responseObject ) );
		if (is_array ( $responseObject->getAdditionalOptions () )) {
			foreach ( $responseObject->getAdditionalOptions () as $option => $value ) {
				$config [$option] = $value;
			}
		}
		
		return Mage::helper ( 'core' )->jsonEncode ( $config );
	}

}
