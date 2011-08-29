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
 * Special Edit Tab Actions
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Special_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form {
	protected function _prepareForm() {
		$model = Mage::registry ( 'global_manage_special_rule' );
		
		$form = new Varien_Data_Form ();
		
		$form->setHtmlIdPrefix ( 'rule_' );
		
		$fieldset = $form->addFieldset ( 'action_fieldset', array ('legend' => Mage::helper ( 'rewards' )->__ ( 'Actions to take' ) ) );
	    Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "Customer Behavior Rule - Actions" );
		
		$fieldset->addField ('points_action', 'select', array(
		    'name' => 'points_action',
			'label' => Mage::helper('salesrule')->__('Action'),
			'required' => true,
			'options' => Mage::getSingleton('rewards/special_action')->getActionOptionsArray()
		));
		
		// SETUP OUR CURRENCY SELECTION
		$currencyData = Mage::helper ( 'rewards/currency' )->getAvailCurrencies ();
		if (sizeof ( $currencyData ) > 1) {
			$currencyDataType = 'select';
			$currencyValueType = 'options';
		} elseif (sizeof ( $currencyData ) == 1) {
			$currencyData = array_keys ( $currencyData );
			$currencyData = array_pop ( $currencyData );
			$currencyDataType = 'hidden';
			$currencyValueType = 'value';
			$model->setPointsCurrencyId ( $currencyData );
		} else {
			throw new Exception ( "No currency specifed." );
		}
		$fieldset->addField ( 'points_currency_id', $currencyDataType, array ('label' => Mage::helper ( 'rewards' )->__ ( 'Points Currency' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Points Currency' ), 'name' => 'points_currency_id', $currencyValueType => $currencyData ) );
		
		$fieldset->addField ( 'points_amount', 'text', array ('name' => 'points_amount', 'required' => true, 'class' => 'validate-not-negative-number', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Fixed Amount' ) ) );
		
		$initial_transfer_status = Mage::getModel('rewards/transfer_status')->getStatusCaption(TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_TIME);
		$warning_msg_html = "<div class='disabled-field-msg' style='font-style: italic; font-size: 10px;'>" 
		    . $this->__("This will set the initial status for points earned by this rule to %s.", $initial_transfer_status) . "</div>";
		    
		
		$isOnholdEnabledField = $fieldset->addField('is_onhold_enabled', 'select', array(
		    'name' => 'is_onhold_enabled',
			'label' => $this->__("Start Transfers On-Hold"),
			'after_element_html' => $warning_msg_html,
		    'options' => array(
                '1' => $this->__('Yes'),
                '0' => $this->__('No')),
			'onchange' => 'toggleOnholdEnabled(this.value)'
		));
	    Mage::getSingleton('rewards/wikihints')->addWikiHint($isOnholdEnabledField, "Customer Behavior Rule - Actions - Transfer On-hold Time" );
		
		$onholdDurationField = $fieldset->addField('onhold_duration', 'text', array(
			'name' => 'onhold_duration',
			'label' => $this->__("Number of days for transfers to be on hold"),
		    //'required' => true
		));
		
		Mage::getSingleton('rewards/special_action')->visitAdminActions($fieldset);
		
		$form->setValues($model->getData());
		$this->setForm($form);
		
		/*$formDependencies = $this->getChild('form-after') ? $this->getChild('form-after') :
		    $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
		$formDependencies->addFieldMap(
		        $isOnholdEnabledField->getHtmlId(),
		        $isOnholdEnabledField->getName())
            ->addFieldMap(
                $onholdDurationField->getHtmlId(),
                $onholdDurationField->getName())
            ->addFieldDependence(
                $isOnholdEnabledField->getName(),
                'points_conditions',
                'customer_sign_up')
            ->addFieldDependence(
                $onholdDurationField->getName(),
                $isOnholdEnabledField->getName(),
                true);
        $this->setChild('form-after', $formDependencies);*/
		
		return parent::_prepareForm();
	}

}