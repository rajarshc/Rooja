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
 * Manage Promo Quote Edit
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Promo_Quote_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	
	public function __construct() {
		$this->_objectId = 'id';
		$this->_controller = 'manage_promo_quote';
		$this->_blockGroup = "rewards";
		
		parent::__construct ();
		
		$this->_updateButton ( 'save', 'label', Mage::helper ( 'salesrule' )->__ ( 'Save Rule' ) );
		$this->_updateButton ( 'delete', 'label', Mage::helper ( 'salesrule' )->__ ( 'Delete Rule' ) );
		
		$give_points_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_GIVE_POINTS;
		$give_by_amount_spent_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_GIVE_BY_AMOUNT_SPENT;
		$give_by_qty_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_GIVE_BY_QTY;
		$deduct_points_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_DEDUCT_POINTS;
		$deduct_by_qty_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_DEDUCT_BY_QTY;
		$deduct_by_amount_spent_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_DEDUCT_BY_AMOUNT_SPENT;
		$discount_by_points_spent = TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT;
		
		//@nelkaake 1/29/2010 12:00:57 PM : New form validation added to make sure Y value is not zero (next script and add/rem ClassName lines below)
		$this->_formScripts [] = "
        Validation.add('validate-notzero', '" . $this->__ ( 'This value cannot be less than or equal to zero.' ) . "', function (v) {
             return parseFloat(v) > 0;
        });
        var rule_simple_action_option;
        ";
		
		$old_discount_fixed_label = $this->__ ( 'Discount Amount' ) . '<span class=\"required\">*</span>';
		$spent_discount_label = $this->__ ( 'Monetary Step (Y) (in base currency)' ) . '<span class=\"required\">*</span>';
		$this->_formInitScripts [] = "
	    	rule_simple_action_option = $$('select#rule_simple_action option[value=buy_x_get_y]')[0];
	        function toggleActionsSelect(action) {  
	        
    	        var rule_points_amount_step_row = $('rule_points_amount_step').up().up();
	        	if ($('rule_points_amount_step_container') != undefined){
    				rule_points_amount_step_row = $('rule_points_amount_step_container').up().up();
	        	}
    	        
    	        var rule_points_qty_step_row = $('rule_points_qty_step').up().up();
    	        if ($('rule_points_qty_step_container') != undefined){
    				rule_points_qty_step_row = $('rule_points_qty_step_container').up().up();
	        	}	   
    	        
     	
	        
	    		if(action == '$give_points_action' || action == '$deduct_points_action' || action == '$discount_by_points_spent') { 
	    			rule_points_amount_step_row.hide(); 
	    			rule_points_qty_step_row.hide(); 
	    			$('rule_points_amount_step').removeClassName('required').removeClassName('validate-notzero');
	    		} else if(action == '$give_by_amount_spent_action' || action == '$deduct_by_amount_spent_action' ) { 
	    			rule_points_amount_step_row.show(); 
	    			rule_points_qty_step_row.hide(); 
	    			$('rule_points_amount_step').addClassName('required').addClassName('validate-notzero');
	    		} else if(action == '$give_by_qty_action' || action == '$deduct_by_qty_action') { 
	    			rule_points_amount_step_row.hide(); 
	    			rule_points_qty_step_row.show(); 
	    			$('rule_points_amount_step').removeClassName('required').removeClassName('validate-notzero');
	    		} else {
	    			rule_points_amount_step_row.show(); 
	    			rule_points_qty_step_row.show(); 
	    			$('rule_points_amount_step').addClassName('required').addClassName('validate-notzero');
	    		}
	    		
	    		if(action == '$discount_by_points_spent') {
                    if($$('label[for=rule_discount_amount]').length == 1) { 
                    	$$('label[for=rule_discount_amount]')[0].innerHTML = '{$spent_discount_label}';
	    				$('rule_discount_amount').addClassName('validate-notzero');
	    			}
	    			if($$('select#rule_simple_action option[value=buy_x_get_y]').length == 1) {
	    			    $$('select#rule_simple_action option[value=buy_x_get_y]')[0].replace('');
	    			}
                } else {
                    if($$('label[for=rule_discount_amount]').length == 1) { 
                    	$$('label[for=rule_discount_amount]')[0].innerHTML = '{$old_discount_fixed_label}';
	    				$('rule_discount_amount').removeClassName('validate-notzero');
	    			}
	    			if($$('select#rule_simple_action option[value=buy_x_get_y]').length < 1) {
	    				if($$('select#rule_simple_action').length == 1) {
	    			    	$$('select#rule_simple_action')[0].insert(rule_simple_action_option);
	    			    }
	    			}
                }
	    	}
    	";
		$this->_formInitScripts [] = "toggleActionsSelect($('rule_points_action').value)";
		
		$no_discount = "";
		$this->_formInitScripts [] = "
	        function toggleDiscountActionsSelect(action) {
    	        var rule_discount_amount_row = $('rule_discount_amount').up().up();
    	        if ($('rule_discount_amount_container') != undefined){
    				rule_discount_amount_row = $('rule_discount_amount_container').up().up();
    			}

/*        	    var rule_discount_qty_row = $('rule_discount_qty').up().up();
    	        if ($('rule_discount_qty_container') != undefined){
    				rule_discount_qty_row = $('rule_discount_qty_container').up().up();
    			}    	

            	var rule_discount_step_row = $('rule_discount_step').up().up();
    	        if ($('rule_discount_step_container') != undefined){
    				rule_discount_step_row = $('rule_discount_step_container').up().up();
    			}    */	

            	var rule_simple_free_shipping_row = $('rule_simple_free_shipping').up().up();
    	        if ($('rule_simple_free_shipping_container') != undefined){
    				rule_simple_free_shipping_row = $('rule_simple_free_shipping_container').up().up();
    			}     			
	        	
	    		if(action == '$no_discount') { 
    				rule_discount_amount_row.hide();
/*    				rule_discount_qty_row.hide();
    				rule_discount_step_row.hide();*/
    				rule_simple_free_shipping_row.hide();
	    			if($('rule_discount_amount').value == '') {
	    				$('rule_discount_amount').value = 0;
    				}
	    		} else {
    				rule_discount_amount_row.show();
/*    				rule_discount_qty_row.show();
    				rule_discount_step_row.show();*/
    				rule_simple_free_shipping_row.show();
	    		}
				
	    		// TODO: look into a better way of dealing with this:
	    		if (action == 'by_percent'){
    				//$('NoSupportNotice').show();
	    		} else {
    				$('NoSupportNotice').hide();
	    		}	    		
	    	}
    	";
		if ($this->_getRule ()->isRedemptionRule ()) {
			$this->_formInitScripts [] = "toggleDiscountActionsSelect($('rule_simple_action').value)";
		}
	
		#$this->setTemplate('promo/quote/edit.phtml');
	}
	
	public function getHeaderText() {
		$rule = $this->_getRule ();
		if ($rule->getRuleId ()) {
			return Mage::helper ( 'salesrule' )->__ ( "Edit Rule '%s'", $this->htmlEscape ( $rule->getName () ) );
		} else {
			return Mage::helper ( 'salesrule' )->__ ( 'New Rule' );
		}
	}
	
	public function getProductsJson() {
		return '{}';
	}
	
	/**
	 * Fetches the currently open salesrule.
	 *
	 * @return TBT_Rewards_Model_Salesrule_Rule
	 */
	protected function _getRule() {
		return Mage::registry ( 'current_promo_quote_rule' );
	}
	
	/**
	 * <<override>>
	 */
	public function getBackUrl() {
		if ($this->getRequest ()->getParam ( 'type' )) {
			$typeId = $this->getRequest ()->getParam ( 'type' );
		}
		if ($this->_getRule ()->getRuleTypeId ()) {
			$typeId = $this->_getRule ()->getRuleTypeId ();
		}
		return $this->getUrl ( '*/*/', array ('type' => $typeId ) );
	}
}
