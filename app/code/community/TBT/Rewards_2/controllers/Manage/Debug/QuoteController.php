<?php

/**
 * Test Controller used for testing purposes ONLY!
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 *
 */
class TBT_Rewards_Manage_Debug_QuoteController extends Mage_Adminhtml_Controller_Action {
	
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