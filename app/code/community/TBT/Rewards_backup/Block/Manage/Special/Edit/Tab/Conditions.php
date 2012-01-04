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
 * Manage Special Edit Tab Conditions
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Special_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form {
	protected function _prepareForm() {
		$model = Mage::registry ( 'global_manage_special_rule' );
		
		$form = new Varien_Data_Form ();
		
		$form->setHtmlIdPrefix ( 'rule_' );
		
		$fieldset = $form->addFieldset ( 'trigger_fieldset', array ('legend' => Mage::helper ( 'salesrule' )->__ ( 'Triggers' ) ) );
		$points_conditions_field = $fieldset->addField ( 'points_conditions', 'select', array (
			'label' => Mage::helper ( 'salesrule' )->__ ( 'Customer Action or Event' ), 
			'name' => 'points_conditions', 
			'options' => Mage::getSingleton ( 'rewards/special_action' )->getOptionsArray (), 
			'required' => true
	    ));
	    Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "Customer Behavior Rule - Triggers" );
		
		$fieldset = $form->addFieldset ( 'conditions_fieldset', array ('legend' => Mage::helper ( 'salesrule' )->__ ( 'Conditions' ) ) );
	    Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "Customer Behavior Rule - Conditions" );
		
		
		$customerGroups = Mage::getResourceModel ( 'customer/group_collection' )->load ()->toOptionArray ();
		
		foreach ( $customerGroups as $group ) {
			if ($group ['value'] == 0) {
				//Removes the "Not Logged In" option, becasue its redundant for special rules
				array_shift ( $customerGroups );
			}
		}
		
		$fieldset->addField ( 'customer_group_ids', 'multiselect', array ('name' => 'customer_group_ids[]', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Customer Group Is' ), 'title' => Mage::helper ( 'salesrule' )->__ ( 'Customer Group Is' ), 'required' => true, 'values' => $customerGroups ) );
		
		$dateFormatIso = Mage::app ()->getLocale ()->getDateFormat ( Mage_Core_Model_Locale::FORMAT_TYPE_SHORT );
		$fieldset->addField ( 'from_date', 'date', array ('name' => 'from_date', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Date is on or After' ), 'title' => Mage::helper ( 'salesrule' )->__ ( 'Date is on or After' ), 'image' => $this->getSkinUrl ( 'images/grid-cal.gif' ), 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 'format' => $dateFormatIso ) );
		$fieldset->addField ( 'to_date', 'date', array ('name' => 'to_date', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Date is Before' ), 'title' => Mage::helper ( 'salesrule' )->__ ( 'Date is Before' ), 'image' => $this->getSkinUrl ( 'images/grid-cal.gif' ), 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 'format' => $dateFormatIso ) );
		
		Mage::getSingleton ( 'rewards/special_action' )->visitAdminConditions ( $fieldset );
		
		$form->setValues ( $model->getData () );
		$this->setForm ( $form );
		
		return parent::_prepareForm ();
	}

}