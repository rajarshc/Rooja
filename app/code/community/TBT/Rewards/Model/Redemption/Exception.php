<?php

class TBT_Rewards_Model_Redemption_Exception extends Exception {
	const FATAL = 4;
	const ERROR = 3;
	const WARNING = 2;
	const NOTICE = 1;
	
	protected $cust_msg = "Customer Not Logged In";
	protected $cart_msg = "No Cart Info";
	
	// Redefine the exception so message isn't optional
	public function __construct($message = null, $code = 0, Exception $previous = null) {
		$rs = Mage::getSingleton ( 'rewards/session' );
		if ($rs->isCustomerLoggedIn ()) {
			$customer = $rs->getSessionCustomer ();
			$this->cust_msg = "<{$customer->getName()}> {$customer->getEmail()}";
			$message = $message . "\n" . $this->cust_msg;
		}
		$cs = Mage::getSingleton ( 'checkout/session' );
		if ($cs->getQuoteId ()) {
			$cart_item_names = array ();
			$cart_item_pids = array ();
			foreach ( $cs->getQuote ()->getAllItems () as $item ) {
				$cart_item_names [] = '"' . $item->getName () . '"';
				$cart_item_pids [] = $item->getProductId ();
			}
			$cart_item_names = implode ( ",", $cart_item_names );
			$cart_item_pids = implode ( ",", $cart_item_pids );
			$this->cart_msg = "Quote #{$cs->getQuoteId()} containing items with names: [{$cart_item_names}].  Item product IDs were: [{$cart_item_pids}]";
			$message = $message . "\n" . $this->cart_msg;
		}
		// make sure everything is assigned properly
		parent::__construct ( $message, $code );
	}
	
	public function addToMessage($str) {
		$this->message = $str . "\n" . $this->message;
		return $this;
	}
	
	public function isFatal() {
		return ($this->getCode () == self::FATAL);
	}
	
	public function isError() {
		return $this->isFatal () || ($this->getCode () == self::ERROR);
	}

}
