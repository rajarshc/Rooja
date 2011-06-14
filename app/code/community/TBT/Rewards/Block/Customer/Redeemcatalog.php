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
 * Customer Send Points
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Customer_Redeemcatalog extends TBT_Rewards_Block_Customer_Abstract {
	
	protected function _construct() {
		parent::_construct ();
		$this->headerText = $this->__ ( "Redeem Your Points" ); //unused
	}
	
	protected function _toHtml() {
		$show_me = Mage::getStoreConfigFlag ( 'rewards/display/showMiniRedeemCatalog' );
		if (! $show_me) {
			return '';
		}
		return parent::_toHtml ();
	}
	
	protected function _prepareLayout() {
		parent::_prepareLayout ();
	}
	
	public function getNumProducts() {
		return Mage::helper ( 'rewards/config' )->rewardsCatalogNumProducts ();
	}
	
	/**
	 * Initialize product collection
	 *
	 * @param integer $num_products
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	 */
	public function getProductCollection() {
		//!! could be zombie code - add log to see if ever hit.... 
		$collection = Mage::getModel ( 'catalog/product' )->getCollection ();
		Mage::getSingleton ( 'catalog/product_status' )->addVisibleFilterToCollection ( $collection );
		Mage::getSingleton ( 'catalog/product_visibility' )->addVisibleInCatalogFilterToCollection ( $collection );
		
		return $collection;
	}
	
	/**
	 *
	 * @return TBT_Rewards_Model_Mysql4_Catalogrule_Rule
	 */
	protected function _getCRResource() {
		return Mage::getResourceModel ( 'rewards/catalogrule_rule' );
	}
	
	//@nelkaake -r 4/11/10: 
	protected function _getOptimizedCatalogRuleProducts($now_date, $wId, $gId) {
		$res = $this->_getCRResource ();
		$active_catalogrule_products = $res->getActiveCatalogruleProducts ( $now_date, $wId, $gId );
		return $active_catalogrule_products;
	}
	
	/**
	 * Select a number of random products that the customer can use redemptions on
	 *
	 * @param int $num_products
	 * @return array of products
	 */
	protected function selectRandomProductsWithRedemptions($num_products) {
		$now_date = Mage::helper ( 'rewards' )->now ();
		$wId = Mage::app ()->getStore ( true )->getWebsiteId ();
		$gId = Mage::getSingleton ( 'customer/session' )->getCustomerGroupId ();
		
		// select product_id from catalogrule_product_price where customer_group_id = XX and website_id = $wId and rule_date = $now_date
		//@nelkaake -m 4/11/10: 
		$active_catalogrule_products = $this->_getOptimizedCatalogRuleProducts ( $now_date, $wId, $gId );
		
		$selected_products = array ();
		
		while ( (sizeof ( $selected_products ) < $num_products) && (sizeof ( $active_catalogrule_products ) > 0) ) {
			$random_index = array_rand ( $active_catalogrule_products );
			$product_id = $active_catalogrule_products [$random_index] ['product_id'];
			$rules_hash = $active_catalogrule_products [$random_index] ['rules_hash'];
			
			unset ( $active_catalogrule_products [$random_index] );
			
			// if product has a RedemptionRule add it to selected_products
			$rules = Mage::helper ( 'rewards' )->unhashIt ( $rules_hash );
			foreach ( $rules as $rule ) {
				if (empty ( $rule->rule_id )) {
					continue;
				}
				if ($rule_obj = Mage::helper ( 'rewards/transfer' )->getCatalogRule ( $rule->rule_id )) {
					if ($rule_obj->isRedemptionRule ()) {
						$selected_products [] = $this->_getProduct ( $product_id );
						break;
					}
				}
			}
		}
		
		return $selected_products;
	}
	
	private function _getProduct($id) {
		if ($this->hasData ( "product_{$id}" )) {
			return $this->getData ( "product_{$id}" );
		} else {
			return Mage::getModel ( 'rewards/catalog_product' )->load ( $id );
		}
	}
	
	public function getPointsOptimizer($product) {
		$predict_points_block = Mage::getBlockSingleton ( 'rewards/product_predictpoints' );
		$predict_points_block->setProduct ( $product )->setHideEarning ( true );
		$str = str_ireplace ( "margin-top:12px; font-size:8pt;", "font-size: 7pt;", $predict_points_block->toHtml () );
		$str = str_ireplace ( "<br>", "", $str );
		$str = str_ireplace ( $this->__ ( "as low as" ) . " ", "", $str );
		$str = str_ireplace ( $this->__ ( "using" ), $this->__ ( "with" ), $str );
		return $str;
	}

}

