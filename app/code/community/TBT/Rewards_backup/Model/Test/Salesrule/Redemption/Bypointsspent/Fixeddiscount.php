<?php
class TBT_Rewards_Model_Test_Salesrule_Redemption_Bypointsspent_Fixeddiscount extends TBT_Rewards_Model_Test_Salesrule_Redemption {

    public function runSuite() {
    	$this->o("<BR />====BEGIN Salesrule_Redemption_Bypointsspent_Fixeddiscount test suite <BR />");
    	$this->prepareRules();
    	$this->prepareSettings();
    	
    	$this->ensureCustomerLoggedIn();
    	$this->emptyCart();
    	$this->addProductToCart(16); // cell phone, should be $149.99 w/o tax
    	
    	
    	$this->o("<BR />&gt; 1. No rules applied to the cart: <BR />\n");
    	$this->refreshQuote();
    	$this->printCart();
    	
    	$this->o("<BR />&gt; 2. No points spent in the cart: <BR />\n");
    	$this->refreshQuote();
    	$this->printCart();
    	
    	
    	$this->o("<BR />&gt; 3. Spending 1 point: <BR />\n");
    	Mage::getSingleton('rewards/session')->setPointsSpending(1);
    	$this->refreshQuote();
    	$this->printCart();
    	
    	$this->o("<BR />&gt; 4. Spending 10 points: <BR />\n");
    	Mage::getSingleton('rewards/session')->setPointsSpending(10);
    	$this->refreshQuote();
    	$this->printCart();
    	
    	$this->o("<BR />&gt; 5. Spending 1000 points: <BR />\n");
    	Mage::getSingleton('rewards/session')->setPointsSpending(1000);
    	$this->refreshQuote();
    	$this->printCart();
    	
    	$this->cleanup();
    	
    	$this->o("<BR />====END Salesrule_Redemption_Bypointsspent_Fixeddiscount test suite complete. <BR />");
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
a:26:{s:11:"product_ids";s:0:"";s:4:"name";s:44:"spend 1 point get $1 off (slider) [testcase]";s:11:"description";s:33:"spend 1 point get $1 off (slider)";s:9:"is_active";s:1:"1";s:11:"website_ids";a:1:{i:0;s:1:"1";}s:18:"customer_group_ids";a:2:{i:0;s:1:"0";i:1;s:1:"1";}s:11:"coupon_type";s:1:"1";s:17:"uses_per_customer";s:1:"0";s:9:"from_date";s:10:"2011-01-20";s:7:"to_date";s:0:"";s:10:"sort_order";s:1:"0";s:6:"is_rss";s:1:"1";s:13:"points_action";s:24:"discount_by_points_spent";s:18:"points_currency_id";s:1:"1";s:13:"points_amount";s:1:"1";s:18:"points_amount_step";s:4:"0.00";s:15:"points_qty_step";s:1:"0";s:14:"points_max_qty";s:1:"0";s:13:"simple_action";s:10:"cart_fixed";s:15:"discount_amount";s:1:"1";s:17:"apply_to_shipping";s:1:"0";s:20:"simple_free_shipping";s:1:"0";s:21:"stop_rules_processing";s:1:"0";s:12:"store_labels";a:4:{i:0;s:12:"dsfdsfdsfdsf";i:1;s:0:"";i:3;s:0:"";i:2;s:0:"";}s:10:"conditions";a:1:{i:1;a:4:{s:4:"type";s:32:"salesrule/rule_condition_combine";s:10:"aggregator";s:3:"all";s:5:"value";s:1:"1";s:9:"new_child";s:0:"";}}s:7:"actions";a:1:{i:1;a:4:{s:4:"type";s:40:"salesrule/rule_condition_product_combine";s:10:"aggregator";s:3:"all";s:5:"value";s:1:"1";s:9:"new_child";s:0:"";}}}
DATAFEED;
		$sd_array = unserialize($sd);
		$model->loadPost($sd_array);
		$model->save();
		
		return $model;
    }


    public function cleanup() {
    	Mage::getSingleton('rewards/session')->setPointsSpending(0);
    	return parent::cleanup();
    }
}