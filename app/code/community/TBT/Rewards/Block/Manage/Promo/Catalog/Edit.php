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
 * Manage Promo Catalog Edit
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Promo_Catalog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId = 'id';
        $this->_controller = 'manage_promo_catalog';
        $this->_blockGroup = 'rewards';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('catalogrule')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('catalogrule')->__('Delete Rule'));

        $this->_addButton('save_apply', array('class' => 'save', 'label' => Mage::helper('catalogrule')->__('Save and Apply'), 'onclick' => "$('rule_auto_apply').value=1; editForm.submit()"));

        $give_points_action = TBT_Rewards_Model_Catalogrule_Actions::GIVE_POINTS_ACTION;
        $give_by_amount_spent_action = TBT_Rewards_Model_Catalogrule_Actions::GIVE_BY_AMOUNT_SPENT_ACTION;
        $give_by_profit_action = TBT_Rewards_Model_Catalogrule_Actions::GIVE_BY_PROFIT_ACTION;
        $deduct_points_action = TBT_Rewards_Model_Catalogrule_Actions::DEDUCT_POINTS_ACTION;
        $deduct_by_amount_spent_action = TBT_Rewards_Model_Catalogrule_Actions::DEDUCT_BY_AMOUNT_SPENT_ACTION;

        //@nelkaake 22/01/2010 1:28:24 PM : New form validation added to make sure Y value is not zero (next script and add/rem ClassName lines below)
        $this->_formScripts [] = "
        Validation.add('validate-notzero', '" . $this->__('This value cannot be less than or equal to zero.') . "', function (v) {
             return parseFloat(v) > 0;
        });
        ";

        $this->_formInitScripts [] = "
	        function toggleActionsSelect(action) {
	        	var rule_points_amount_step_row = $('rule_points_amount_step').up().up();
	        	if ($('rule_points_amount_step_container') != undefined){
    				rule_points_amount_step_row = $('rule_points_amount_step_container').up().up();
	        	}
	        	
	    		if(action == '$give_points_action' || action == '$deduct_points_action') { 
	    			rule_points_amount_step_row.hide(); 
	    			$('rule_points_amount_step').removeClassName('required').removeClassName('validate-notzero');
	    		} else if(action == '$give_by_amount_spent_action' || action == '$deduct_by_amount_spent_action' || action == '$give_by_profit_action' ) { 
	    			rule_points_amount_step_row.show(); 
	    			$('rule_points_amount_step').addClassName('required').addClassName('validate-notzero');
	    		} else {
	    			rule_points_amount_step_row.show(); 
	    			$('rule_points_amount_step').addClassName('required').addClassName('validate-notzero');
	    		}
	    	}
	    ";
        $this->_formInitScripts [] = "toggleActionsSelect($('rule_points_action').value)";

        $no_discount = "";

        $this->_formInitScripts [] = <<<FEED
            function toggleDiscountActionsSelect(action) {  

                var rule_points_catalogrule_discount_amount_row = $('rule_points_catalogrule_discount_amount').up().up();
                if ($('rule_points_catalogrule_discount_amount_container') != undefined){
                    rule_points_catalogrule_discount_amount_row = $('rule_points_catalogrule_discount_amount_container').up().up();
                }
                
                var dom_catalogrule_discount_amount = $('rule_points_catalogrule_discount_amount')

                if(action == '{$no_discount}') { 
                    rule_points_catalogrule_discount_amount_row.hide();
                    if(dom_catalogrule_discount_amount.value == '') {
                        dom_catalogrule_discount_amount.value = 0;
                    }
                } else {
                    rule_points_catalogrule_discount_amount_row.show(); 
                }
                
                Validation.reset(dom_catalogrule_discount_amount);
                dom_catalogrule_discount_amount.removeClassName('validate-greater-than-zero');
                if(action == 'by_percent') dom_catalogrule_discount_amount.addClassName('validate-greater-than-zero');
                if(action == 'by_fixed') dom_catalogrule_discount_amount.addClassName('validate-greater-than-zero');
                
                {$this->_getUsesCondJs()}
            }
FEED;
                
        $this->_formInitScripts [] = "toggleDiscountActionsSelect($('rule_points_catalogrule_simple_action').value)";
    }

    public function getHeaderText() {
        $rule = $this->_getCatalogRule();
        if ($rule->getRuleId()) {
            return Mage::helper('catalogrule')->__("Edit Rule '%s'", $this->htmlEscape($rule->getName()));
        } else {
            return Mage::helper('catalogrule')->__('New Rule');
        }
    }

    /**
     * Fetches the currently open catalogrule.
     *
     * @return TBT_Rewards_Model_Catalogrule_Rule
     */
    protected function _getCatalogRule() {
        return Mage::registry('current_promo_catalog_rule');
    }

    /**
     * <<override>>
     */
    public function getBackUrl() {
        if ($this->getRequest()->getParam('type')) {
            $typeId = $this->getRequest()->getParam('type');
        }
        if ($this->_getCatalogRule()->getRuleTypeId()) {
            $typeId = $this->_getCatalogRule()->getRuleTypeId();
        }
        return $this->getUrl('*/*/', array('type' => $typeId));
    }

    /**
     * Only return uses condition JS content if this is a redemption rule
     */
    protected function _getUsesCondJs() {
        if (!$this->_getCatalogRule()->isRedemptionRule())
            return "";

        $js = "
			/*	if (action == 'to_percent' || action == 'to_fixed'){
	    			$('rule_points_uses_per_product').value = 1;
	    			$('rule_points_uses_per_product').disable();
	    		} else {
    				$('rule_points_uses_per_product').enable();
	    		}*/
    	";
        return $js;
    }

}
