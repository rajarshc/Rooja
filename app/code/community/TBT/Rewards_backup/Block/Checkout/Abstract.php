<?php

class TBT_Rewards_Block_Checkout_Abstract extends Mage_Checkout_Block_Cart {
	
	protected $redemption_data = null;
	protected $distribution_data = null;
	
	protected function _construct() {
		parent::_construct ();
		$this->addItemRender ( 'grouped', 'checkout/cart_item_renderer_grouped', 'checkout/cart/item/default.phtml' );
		$this->addItemRender ( 'configurable', 'checkout/cart_item_renderer_configurable', 'checkout/cart/item/default.phtml' );
	}
	
	/**
	 * Returns an image that the user can click on to apply or remove rules
	 *
	 * @param int $rule_id
	 * @param bool $is_add
	 * @param bool $is_cart 
	 * @param int $item_id
	 * @return string
	 */
	public function genRuleCtrlImg($rule_id, $is_add = true, $is_cart = true, $item_id = 0, $redemption_instance_id = 0, $callback = "true") {
		$rule_type = ($is_cart ? 'cart' : 'catalog');
		$src = Mage::getDesign ()->getSkinUrl ( 'images/rewards/' . ($is_add ? 'add' : 'remove') . '.gif' );
		$url_key = 'rewards/cart_redeem/' . ($is_add ? $rule_type . 'add' : $rule_type . 'remove');
		$params = array ('rids' => $rule_id );
		if (! empty ( $redemption_instance_id ))
			$params ['inst_id'] = $redemption_instance_id;
		if (! $is_cart) {
			$params ['item_id'] = $item_id;
		}
		$url = $this->getUrl ( $url_key, $params );
		
		if (empty ( $callback ))
			$callback = "true";
		
		//@mlp override for above old school # hax.
		//@lulz ruin indentation with heredoc
		//@todo move preventDefault logic to javascript and call function
		$html = <<<EOT

    <a href="javascript://" onclick="
        var event=this.onclick;
        if (event.preventDefault) {
            event.preventDefault();
        } else if (event.returnValue) {
            event.returnValue = false;
        };
        if($callback){window.location='$url'};
        return false;
    "
    >
        <img src="$src" border="0" />
    </a>

EOT;
		
		return $html;
	}
	
	/**
	 * Collects redemption data
	 *
	 * @param unknown_type $quote
	 * @return array
	 */
	public function collectShoppingCartRedemptions($quote = null) {
		if ($quote == null)
			$quote = $this->getQuote ();
		if ($this->redemption_data == null) {
			$this->redemption_data = $this->_getRewardsSess ()->collectShoppingCartRedemptions ( $quote );
		}
		return $this->redemption_data;
	}
	
	/**
	 * Fetches cart distirbution data.
	 *
	 * @param unknown_type $quote
	 * @return array
	 */
	public function updateShoppingCartPoints($quote = null) {
		if ($quote == null)
			$quote = $this->getQuote ();
		if ($this->distribution_data == null) {
			$this->distribution_data = $this->_getRewardsSess ()->updateShoppingCartPoints ( $quote );
		}
		return $this->distribution_data;
	}
	
	/**
	 * True if the customer is logged in.
	 *
	 * @return boolean
	 */
	public function customerHasUsablePoints() {
		return $this->isCustomerLoggedIn () && $this->_getRewardsSess ()->getSessionCustomer ()->hasUsablePoints ();
	}
	
	/**
	 * True if the customer is logged in.
	 *
	 * @return boolean
	 */
	public function isCustomerLoggedIn() {
		return $this->_getRewardsSess ()->isCustomerLoggedIn ();
	}
	
	// any type of redemptions, cart and catalog
	public function cartHasRedemptions() {
		return $this->_getRewardsSess ()->hasRedemptions ();
	}
	
	// any type of redemptions, cart and catalog
	public function cartHasDistributions() {
		return $this->_getRewardsSess ()->hasDistributions ();
	}
	
	public function cartHasAnyCatalogRedemptions() {
		return $this->_getRewardsSess ()->getQuote ()->hasAnyAppliedCatalogRedemptions ();
	}
	
	/**
	 * Fetches the rewards session singleton
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	/**
	 * Before rendering html, but after trying to load cache
	 *
	 * @return Mage_Core_Block_Abstract
	 */
	protected function _beforeToHtml() {
		$this->loadSliderSettings ();
		return parent::_beforeToHtml ();
	}
	
	public function loadSliderSettings() {
		//TODO if there are multiple rules 
		$quote = Mage::getSingleton ( 'rewards/session' )->getQuote ();
		$this->setPointsStep ( $quote->getPointsStep () );
		$this->setMinSpendablePoints ( $quote->getMinSpendablePoints () );
		$this->setMaxSpendablePoints ( $quote->getMaxSpendablePoints () );
		
		return $this;
	}

}

?>