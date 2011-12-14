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
 * Test Controller used for testing purposes ONLY!
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 *
 */
class TBT_Rewards_Manage_Debug_ProductController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction() {
		die ( "This is the test controller that should be used for test purposes only!" );
	}
	
	public function isAdminModeAction() {
		echo "Admin is=<pre>" . print_r ( Mage::getSingleton ( 'adminhtml/session_quote' )->getData (), true ) . "</pre><BR />";
		if ($this->_getSess ()->isAdminMode ()) {
			echo "Is admin";
		} else {
			echo "not admin mode";
		}
	}
	
	public function optAction() {
		$product = $this->_getProduct ( 4 ); // notebook;
		echo "Loaded product with name [{$product->getName()}] with SKU [{$product->getSku()}]<BR />";
		$points = $this->_genPointsModel ( $product );
		echo "Points Available for Award is [{$points->getRendering()}] <BR />";
		echo "Price of loaded product is  [{$product->getFinalPrice()}] <BR />";
		$points_optimized_price = $product->getRewardAdjustedPrice ();
		echo "Rewards Optimized Price Is  [{$points_optimized_price['points_price']}] with [{$points_optimized_price['points_string']}]<BR />";
	}
	
	public function checkPointsBlockAction() {
		echo $this->createPointsModel ()->setPoints ( 1, 121 ) . " (using points model) <Br/ >";
		echo Mage::getModel ( 'rewards/points' ) . " (using points model) <Br/ >";
		echo Mage::helper ( 'rewards' )->getPointsString ( array (1 => 121 ) ) . " (using rewards helper)";
	}
	
	/**
	 * Gets a points model model
	 *
	 * @return TBT_Rewards_Model_Points
	 */
	public function createPointsModel() {
		$m = Mage::getModel ( 'rewards/points' );
		return $m;
	}
	
	/**
	 * Fetches a points model from a product.
	 * TODO: Ideally the product should reutnr the points model.
	 *
	 * @param TBT_Rewards_Model_Catalog_Product $product
	 * @return TBT_Rewards_Model_Points
	 */
	public function _genPointsModel($product) {
		$points = Mage::getModel ( 'rewards/points' );
		$points->add ( $product->getEarnablePoints () );
		return $points;
	}
	
	/**
	 * gets a product
	 *
	 * @param integer $id
	 * @return TBT_Rewards_Model_Catalog_Product
	 */
	public function _getProduct($id) {
		return Mage::getModel ( 'rewards/catalog_product' )->load ( $id );
	}
	
	/**
	 * Fetches the Jay rewards customer model.
	 *
	 * @return TBT_Rewards_Model_Customer
	 */
	public function _getJay() {
		return Mage::getModel ( 'rewards/customer' )->load ( 1 );
	}
	
	/**
	 * Fetches the rewards session
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	public function _getSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	/**
	 * Gets the default rewards helper
	 *
	 * @return TBT_Rewards_Helper_Data
	 */
	public function _getHelp() {
		return Mage::helper ( 'rewards' );
	}

}