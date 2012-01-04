<?php

/**
 * Image Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Debug_SpecialController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		echo "This utility will check special rule settings... <BR />";
		
		$code_nodes = Mage::getConfig ()->getNode ( 'rewards/special' )->children ();
		foreach ( $code_nodes as $code => $special ) {
			$special = ( array ) $special;
			if (isset ( $special ['config'] )) {
				$model_code = $special ['config'];
			} else {
				throw new Exception ( "Action model for special rule code '$code' is not specified." );
			}
			$config_model = Mage::getModel ( $model_code );
			if (! ($config_model instanceof TBT_Rewards_Model_Special_Configabstract)) {
				throw new Exception ( "Config model for special rule code '$code' should extend TBT_Rewards_Model_Special_Configabstract but it doesn't." );
			}
			print_r ( $config_model->getCustomerConditions () );
		}
	
		//        Mage::getSingleton('rewards/special_action');
	}
	
	protected function getRedeem() {
		return Mage::getSingleton ( 'rewards/redeem' );
	}
	
	protected function getQuote() {
		return Mage::getSingleton ( 'rewards/session' )->getQuote ();
	}
	
	/**
	 * Controller predispatch method
	 *
	 * @return Mage_Adminhtml_Controller_Action
	 */
	public function preDispatch() {
		
		// Authentication Check:
		if (! isset ( $_SERVER ['PHP_AUTH_USER'] )) {
			$this->auth ();
		} else {
			$auth_result = Mage::getModel ( 'admin/user' )->authenticate ( $_SERVER ['PHP_AUTH_USER'], $_SERVER ['PHP_AUTH_PW'] );
			if ($auth_result) {
				return parent::preDispatch ();
			} else {
				unset ( $_SERVER ['PHP_AUTH_USER'] );
				$this->auth ();
			}
		}
	}
	
	/**
	 * Authentication Function
	 */
	protected function auth() {
		$title = "Store Administrator Log-in";
		header ( 'WWW-Authenticate: Basic realm="' . $title . '"' );
		header ( 'HTTP/1.0 401 Unauthorized' );
		echo "You must authenticate yourself before viewing this file.  Please e-mail administration if you don't think you should be seeeing this message.";
		exit ();
	}
	
	/**
	 * Retrieve shopping cart model object
	 *
	 * @return Mage_Checkout_Model_Cart
	 */
	protected function _getCart() {
		return Mage::getSingleton ( 'checkout/cart' );
	}
	
	/**
	 * Get checkout session model instance
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getSession() {
		return Mage::getSingleton ( 'checkout/session' );
	}
	
	/**
	 * Get current active quote instance
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote() {
		return $this->_getCart ()->getQuote ();
	}
	
	protected function getStore() {
		return Mage::app ()->getStore ();
	}

}