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
        /*
          $fieldset->addField('points_amount_referral_first_order', 'text', array(
          'name' => 'points_amount_referral_first_order',
          'required' => true,
          'class' => 'validate-not-negative-number',
          'label' => Mage::helper('rewardsref')->__("Fixed Amount When Referral Makes First Order"),
          ));
         */
        return $this;
    }

    public function getNewActions() {
        return array();
    }

    public function getAdminFormScripts() {
        return array();
    }

    public function getAdminFormInitScripts() {
        $hidescript = "
            function checkReferralFields() {

				var wikiHintsOn = false;
        	    var rule_points_amount_row = $('rule_points_amount').up().up();
    	        if ($('rule_points_amount_container') != undefined){
					wikiHintsOn = true;
    				rule_points_amount_row = $('rule_points_amount_container').up().up();
    			}            
            
                var v = $('rule_points_conditions').value;
                if(v == 'customer_referral_order') {
                    rule_points_amount_row.cells[0].down().innerHTML = '{$this->getReferralOrderCaption()}';
                } else {
                    rule_points_amount_row.cells[0].down().innerHTML = '{$this->getDefaultCaption()}';				
                }
				
				if (wikiHintsOn) updateLinkOnElement($('rule_points_amount'));
            }
			
			$('rule_points_conditions').setAttribute('onchange','checkReferralFields();');
			
            checkReferralFields();
        ";
        return array($hidescript);
    }

    protected function getReferralOrderCaption() {
        return (Mage::helper('rewardsref')->__("% of Points Earned By Referral") . "<span class=\"required\">*</span>");
    }

    protected function getDefaultCaption() {
        return Mage::helper('salesrule')->__("Fixed Amount") . "<span class=\"required\">*</span>:";
    }

}