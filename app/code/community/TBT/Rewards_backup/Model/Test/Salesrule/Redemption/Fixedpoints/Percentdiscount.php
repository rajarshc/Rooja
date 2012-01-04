<?php
class TBT_Rewards_Model_Test_Salesrule_Redemption_Fixedpoints_Percentdiscount extends TBT_Rewards_Model_Test_Salesrule_Redemption {

    public function runSuite() {
    	$this->o("<BR />====BEGIN Test_Salesrule_Redemption_Fixedpoints_Percentdiscount test suite <BR />");
    	$this->prepareRules();
    	$this->prepareSettings();
    	
    	$this->emptyCart();
    	$this->addProductToCart(16); // cell phone, should be $149.99 w/o tax
    	
    	
    	
    	$this->o("<BR />&gt; 1. No rules applied to the cart: <BR />\n");
    	$this->refreshQuote();
    	$this->printCart();
    	
    	$this->o("<BR />&gt; 2. 10% off applied to the cart: <BR />\n");
		$this->applyRedemption($this->getRule()->getId());
    	$this->refreshQuote();
    	$this->printCart();
    	
    	
    	
    	$this->cleanup();
    	
    	$this->o("<BR />====END Salesrule_Redemption_Fixedpoints_Fixeddiscount test suite complete. <BR />");
    	return $this;
    }
    
    public function prepareRules() {
    	$this->disableAllCartRules();
    	
    	$rule = $this->createCartRule();
    	$this->setRule($rule);
    	
    	return $this;
    }
    
    public function createCartRule() {
	    $model = Mage::getModel('rewards/salesrule_rule');
	    /*
	     * You can pull serialized data like this from the post by doing this in the save method of the controller for that model: 
	     *  unset($data['rule_id']);
        	unset($data['form_key']);
        	die(serialize($data));

	     */
$sd = <<<DATAFEED
a:26:{s:11:"product_ids";s:0:"";s:4:"name";s:46:"spend 50 points get 10% off (fixed) [testcase]";s:11:"description";s:35:"spend 50 points get 10% off (fixed)";s:9:"is_active";s:1:"1";s:11:"website_ids";a:1:{i:0;s:1:"1";}s:18:"customer_group_ids";a:2:{i:0;s:1:"0";i:1;s:1:"1";}s:11:"coupon_type";s:1:"1";s:17:"uses_per_customer";s:1:"0";s:9:"from_date";s:10:"2011-03-02";s:7:"to_date";s:0:"";s:10:"sort_order";s:1:"5";s:6:"is_rss";s:1:"1";s:13:"points_action";s:13:"deduct_points";s:18:"points_currency_id";s:1:"1";s:13:"points_amount";s:2:"50";s:18:"points_amount_step";s:4:"0.00";s:15:"points_qty_step";s:1:"0";s:14:"points_max_qty";s:1:"0";s:13:"simple_action";s:10:"by_percent";s:15:"discount_amount";s:2:"10";s:17:"apply_to_shipping";s:1:"0";s:20:"simple_free_shipping";s:1:"0";s:21:"stop_rules_processing";s:1:"0";s:12:"store_labels";a:4:{i:0;s:35:"spend 50 points get $50 off (fixed)";i:1;s:0:"";i:3;s:0:"";i:2;s:0:"";}s:10:"conditions";a:1:{i:1;a:4:{s:4:"type";s:32:"salesrule/rule_condition_combine";s:10:"aggregator";s:3:"all";s:5:"value";s:1:"1";s:9:"new_child";s:0:"";}}s:7:"actions";a:1:{i:1;a:4:{s:4:"type";s:40:"salesrule/rule_condition_product_combine";s:10:"aggregator";s:3:"all";s:5:"value";s:1:"1";s:9:"new_child";s:0:"";}}}
DATAFEED;
		$sd_array = unserialize($sd);
		$model->loadPost($sd_array);
		$model->save();
		
		return $model;
    }

    public function prepareSettings() {
    	$this->resetTaxConfig();
    	
    	return $this;
    }
}