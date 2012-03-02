<?php

class TBT_RewardsReferral_Model_Special_Order extends TBT_Rewards_Model_Special_Configabstract {
    const ACTION_REFERRAL_ORDER = 'customer_referral_order';

    public function _construct() {
        $this->setCaption("Customer Referral");
        $this->setDescription("Customer will get points for every purchase made by a referred customer.");
        $this->setCode("referral");
        return parent::_construct();
    }

    public function getNewCustomerConditions() {
        return array(
            self::ACTION_REFERRAL_ORDER => Mage::helper('rewardsref')->__('Referral makes any order'),
        );
    }
    
    public function visitAdminActions(&$fieldset) {
        $fieldset->addField('simple_action', 'select', 
        array(
            'name' => 'simple_action', 
            'label' => 'Award Points as:', 
            // 'required' => true,
            'options' => array(
                'by_percent' => Mage::helper('rewardsref')->__("% of Points Earned By Referral"), 
                'by_fixed' => Mage::helper('rewardsref')->__("Fixed Amount")
            )
        ), 'points_action');
        
        return $this;
    }

    public function getNewActions() {
        return array ();
    }

    public function getAdminFormScripts() {
        return array();
    }

    public function getAdminFormInitScripts() {
        $hidescript = "
            function checkReferralFields() {

        	    var rule_simple_action_row = $('rule_simple_action').up().up();
        	    var rule_points_amount_row = $('rule_points_amount').up().up();
        	    
                var v = $('rule_points_conditions').value;
                if(v == 'customer_referral_order') {
                    rule_simple_action_row.show(); 
	                simple_action = $('rule_simple_action').value;
	                if(simple_action == 'by_percent') {
	                    rule_points_amount_row.cells[0].down().innerHTML = '{$this->getPercentageCaption()}';
	                } else {
	                    rule_points_amount_row.cells[0].down().innerHTML = '{$this->getDefaultCaption()}';				
	                }
	                    	
                } else {
                	rule_simple_action_row.hide();
                	rule_points_amount_row.cells[0].down().innerHTML = '{$this->getDefaultCaption()}';                                   				
                }
                
            }
    			
    	   	// update the onchange events for the rule_points_conditions field.
    	   	document.observe('dom:loaded', function() {
        	   	var old_cond_onchange_event = $('rule_points_conditions').getAttribute('onchange');
        		$('rule_points_conditions').setAttribute('onchange', (old_cond_onchange_event == null ? '' : old_cond_onchange_event) + 'checkReferralFields();');
                
        	   	var old_sa_onchange_event = $('rule_simple_action').getAttribute('onchange');
        		$('rule_simple_action').setAttribute('onchange', (old_sa_onchange_event == null ? '' : old_sa_onchange_event) + 'checkReferralFields();');
    		});
    			
            checkReferralFields();
        ";
        return array($hidescript);
    }

    protected function getPercentageCaption() {
        return (Mage::helper('rewardsref')->__("% of Points Earned By Referral") . "<span class=\"required\">*</span>:");
    }

    protected function getDefaultCaption() {
        return Mage::helper('salesrule')->__("Fixed Amount") . "<span class=\"required\">*</span>:";
    }

}