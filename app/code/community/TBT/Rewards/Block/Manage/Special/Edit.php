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
 * Manage Special Edit
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Special_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	
	public function __construct() {
		parent::__construct ();
		$this->_objectId = 'manage_special_form';
		$this->_controller = 'manage_special';
		$this->_blockGroup = 'rewards';
		
		$this->_updateButton ( 'save', 'label', Mage::helper ( 'salesrule' )->__ ( 'Save Rule' ) );
		$this->_updateButton ( 'delete', 'label', Mage::helper ( 'salesrule' )->__ ( 'Delete Rule' ) );
		
		$this->_addButton ( 'saveandcontinue', array ('label' => Mage::helper ( 'salesrule' )->__ ( 'Save And Continue Edit' ), 'onclick' => 'saveAndContinueEdit()', 'class' => 'save' ), - 100 );
		
		$this->_formScripts [] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
		$additional_scripts = Mage::getSingleton ( 'rewards/special_action' )->getAdminFormScripts ();
		$this->_formScripts = array_merge ( $this->_formScripts, $additional_scripts );
		
		$additional_init_scripts = Mage::getSingleton ( 'rewards/special_action' )->getAdminFormInitScripts ();
		$this->_formInitScripts = array_merge ( $this->_formInitScripts, $additional_init_scripts );
		
		$this->_formInitScripts [] = <<<TOGGLE_ONHOLD
	        function toggleOnholdEnabled(isEnabled) {
	        	var rule_onhold_duration = $('rule_onhold_duration').up().up();
	        	if (isEnabled == 1) {
	        		rule_onhold_duration.show();
	        		$('rule_onhold_duration').addClassName('validate-notzero')
	        			.addClassName('validate-not-negative-number');
	        	} else {
	        		rule_onhold_duration.hide();
	        		$('rule_onhold_duration').removeClassName('validate-notzero')
	        			.removeClassName('validate-not-negative-number');
	        	}
	    	}
TOGGLE_ONHOLD;
        $this->_formInitScripts [] = "toggleOnholdEnabled($('rule_is_onhold_enabled').value)";
        
        $behavior_checks = array();
        $behaviors = Mage::getSingleton('rewards/probation')->getProbationalBehaviors();

        if(count($behaviors) > 0) {
            foreach ($behaviors as $key) {
                $behavior_checks[] = "
                	if (action == '{$key}') {
                		rule_is_onhold_enabled.show();
                	}";
            }
        } else {
            // If there are no behavior checks, just make an empty IF statement that does nothing.
            $behavior_checks[] = "if(true) { }";
        }
        
        $jsIfBehaviorIsProbational = implode(" else ", $behavior_checks);

        $this->_formInitScripts [] = <<<ONHOLD_AVAILABLE
	        function toggleActionsSelect(action) {
	        	var rule_is_onhold_enabled = $('rule_is_onhold_enabled').up().up();
	        	{$jsIfBehaviorIsProbational}
	        	else {
	        		$('rule_is_onhold_enabled').value = 0;
	        		toggleOnholdEnabled(0);
	        		rule_is_onhold_enabled.hide();
	        	}
	    	}
	    	
	        // update the onchange events for the rule_points_conditions field.
	        document.observe('dom:loaded', function() {
	        	Event.observe('rule_points_conditions', 'change', function() {
	        		toggleActionsSelect(this.value);
	        	});
	        });
		
ONHOLD_AVAILABLE;
        $this->_formInitScripts [] = "toggleActionsSelect($('rule_points_conditions').value)";
	
		#$this->setTemplate('promo/quote/edit.phtml');
	}
	
	public function getHeaderText() {
		$rule = Mage::registry ( 'global_manage_special_rule' );
		if ($rule->getRewardsSpecialId ()) {
			return Mage::helper ( 'salesrule' )->__ ( "Edit Rule '%s'", $this->htmlEscape ( $rule->getName () ) );
		} else {
			return Mage::helper ( 'salesrule' )->__ ( 'New Rule' );
		}
	}
	
	public function getProductsJson() {
		return '{}';
	}

}
