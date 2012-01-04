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
 * Image Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Product_View_RedeemController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
	
	}
	
	/**
	 * Fetches a configurable product requested by the user.
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return Mage_Catalog_Model_Product_Type_Configurable
	 */
	private function _initConfigurableProduct(Mage_Catalog_Model_Product $product = null) {
		if ($product == null) {
			$product = $this->_initProduct ();
		}
		if (! $product->isComposite ()) {
			throw new Mage_Core_Exception ( "Not a configurable product.", self::EC_NOT_CONFIGURABLE );
		} else {
			$product = $product->getTypeInstance ( false );
			if ($product instanceof Mage_Catalog_Model_Product_Type_Configurable) {
			
			} else {
				throw new Mage_Core_Exception ( "Not a configurable product.", self::EC_NOT_CONFIGURABLE );
			}
		}
		return $product;
	}
	
	/**
	 * Get request for product add to cart procedure
	 *
	 * @param   mixed $requestInfo
	 * @return  Varien_Object
	 */
	protected function _getProductRequest($requestInfo) {
		if ($requestInfo instanceof Varien_Object) {
			$request = $requestInfo;
		} elseif (is_numeric ( $requestInfo )) {
			$request = new Varien_Object ();
			$request->setQty ( $requestInfo );
		} else {
			$request = new Varien_Object ( $requestInfo );
		}
		
		if (! $request->hasQty ()) {
			$request->setQty ( 1 );
		}
		return $request;
	}
	
	/**
	 * Loads a product requested by the customer
	 *
	 * @throws Mage_Core_Exception
	 * @return Mage_Catalog_Model_Product
	 */
	private function &_initProduct() {
		if ($pid = $this->getRequest ()->get ( "product" )) {
			$product = Mage::getModel ( 'catalog/product' )->setStoreId ( Mage::app ()->getStore ()->getId () )->load ( $pid );
			if (! $product->getId ()) {
				throw new Mage_Core_Exception ( "Product ID provided does not exist", self::EC_BAD_PID );
			}
			$params = $this->getRequest ()->getParams ();
			$request = $this->_getProductRequest ( $params );
			if ($product->isConfigurable ()) {
				$product->getTypeInstance ( true )->prepareForCart ( $request, $product );
			}
			return $product;
		} else {
			throw new Mage_Core_Exception ( "No product ID provided.", self::EC_NO_PID );
		}
	}
	
	/**
	 * AJAX: Echos a redeemed product price given a redemption rule and product id.
	 *
	 */
	public function redPriceAction() {
		try {
			$product = $this->_initProduct ();
			$rule_id = $this->getRequest ()->get ( "rid" );
			$str = $product->getFinalPrice ();
			echo $str;
			$points_used = $this->getRequest ()->get ( "pts" );
		} catch ( Mage_Core_Exception $e ) {
			die ( "Error: " . $e->getMessage () );
		}
	}
	
	/**
	 * AJAX CALL. return a list of rules given a product and product price
	 * param product_id
	 * param rule_id
	 * param price
	 */
	public function getProductPriceRuleSettingsAction() {
		
		try {
			//$storeId = $product->getStoreId();
			//  Mage::app()->getStore($storeId);
			

			/* @var $store Mage_Core_Model_Store */
			$store = Mage::app ()->getStore ();
			/* @var $product Mage_Catalog_Model_Product */
			$productId = $this->getRequest ()->get ( "productId" );
			$product = Mage::getModel ( 'catalog/product' )->setStoreId ( $store->getId () )->load ( $productId );
			if (! $product->getId ())
				throw new Exception ( "Product ID provided does not exist" );
			if ($product instanceof Mage_Catalog_Model_Product)
				$product = TBT_Rewards_Model_Catalog_Product::wrap ( $product );
				
			/* @var $customer TBT_Rewards_Model_Customer */
			$customer = false; // set to false becaue getCatalogRedemptionRules( false | customer )
			if (Mage::getSingleton ( 'rewards/session' )->isCustomerLoggedIn ())
				$customer = Mage::getSingleton ( 'rewards/session' )->getCustomer ();
			
			$price = ( float ) $this->getRequest ()->get ( "price" );
			
			$productPriceRuleMap = array ();
			$applicableRules = $product->getCatalogRedemptionRules ( $customer );
			foreach ( $applicableRules as $i => &$r ) {
				$r = ( array ) $r;
				$ruleId = $r [TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID];
				$rule = Mage::getModel ( 'rewards/catalogrule_rule' )->load ( $ruleId );
				if ($rule->getId ())
					$productPriceRuleMap [$ruleId] = $rule->getPointSliderSettings ( $store, $product, $customer, $price );
			}
			echo json_encode ( $productPriceRuleMap );
		} catch ( Exception $e ) {
			die ( "Error: " . $e->getMessage () );
		}
	}

}