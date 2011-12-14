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
 * Product Predict Points
 * options: 
 * - setHideEarning(true); // hides earning line.
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Product_Predictpoints extends Mage_Core_Block_Template {
	
	/**
	 * Product for this block
	 *
	 * @var TBT_Rewards_Model_Catalog_Product
	 */
	protected $product = null;
	
	protected function _construct() {
		parent::_construct ();
		$this->setTemplate ( 'rewards/product/predictpoints.phtml' );
		//@nelkaake Wednesday March 10, 2010 10:04:42 PM : New caching functionality 
		$this->setCacheLifetime ( 86400 );
	}
	
	public function doShowAsLowAs() {
		return Mage::getStoreConfigFlag ( 'rewards/display/showPointsOptimizer' );
	}
	
	public function doGraphicalEarning() {
		return Mage::getStoreConfigFlag ( 'rewards/display/showEarningGraphic' );
	}
	
	/**
	 * Set the product to create the predict points block for.
	 *
	 * @param TBT_Rewards_Model_Catalog_Product $_product
	 */
	public function setProduct($_product) {
		if (! ($_product instanceof TBT_Rewards_Model_Catalog_Product)) {
			$_product = TBT_Rewards_Model_Catalog_Product::wrap ( $_product );
		}
		$this->product = $_product;
		//@nelkaake Wednesday March 10, 2010 10:04:42 PM : New caching functionality 
		//@nelkaake -m 5/11/10: new key generation functionality
		$this->setCacheKey ( $this->_genCacheKey () );
		return $this;
	}
	
	/**
	 * @return string key specic to this cached content
	 * @nelkaake -a 5/11/10:
	 */
	protected function _genCacheKey() {
            
            $nameInLayout = $this->getNameInLayout();
            $blockType = $this['type'];
            $product_id = $this->getProduct ()->getId ();
            $website_id = Mage::app ()->getWebsite ()->getId ();
            $customer_group_id = $this->_getRS ()->getCustomerGroupId ();
            $lang = Mage::getStoreConfig('general/locale/code');

            $key = "rewards_product_predictpoints_{$nameInLayout}_{$blockType}_{$product_id}_{$website_id}_{$customer_group_id}_{$lang}";

            if ($this->_getRS ()->isCustomerLoggedIn ()) {
    		    $customer = Mage::getModel('rewards/customer')->getRewardsCustomer($this->_getRS()->getCustomer());
    			$pts = (string)$customer->getPointsSummary();
                $pts = strtolower(str_replace(' ', '_', $pts));
                $pts = preg_replace ( '/[^a-z0-9_]/', '', $pts );
                $key = $key . "_{$pts}";
            }            
            return $key;
	}
	
	public function getPredictedPoints() {
		if ($this->product) {
			$predict = $this->product->getRewardAdjustedPrice ();
		} else {
			$predict = array ('points_price' => 0, 'points_string' => "" );
		}
		return $predict;
	}
	
	public function getPredictedPointsEarned() {
		if ($this->product) {
			$earnable = $this->product->getEarnablePoints ();
		} else {
			$earnable = array ();
		}
		return $earnable;
	}
	
	public function hasEarnablePoints() {
		$earnable_points = $this->getPredictedPointsEarned ();
		$has_earnable_points = (sizeof ( $earnable_points ) > 0);
		return $has_earnable_points;
	}
	
	public function getEarnablePointsString() {
		$earnable_points = $this->getPredictedPointsEarned ();
		$earnable_points_str = Mage::helper ( 'rewards' )->getPointsString ( $earnable_points );
		return $earnable_points_str;
	}
	
	/**
	 * @nelkaake -a 5/11/10: 
	 * @return TBT_Rewards_Model_Catalog_Product
	 */
	public function getProduct() {
		return ($this->product == null ? Mage::getModel ( 'rewards/catalog_product' ) : $this->product);
	}
	
	/**
	 * Fetches the rewards session singleton    
	 * @nelkaake -a 5/11/10:      *      
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRS() {
		return Mage::getSingleton ( 'rewards/session' );
	}

}

