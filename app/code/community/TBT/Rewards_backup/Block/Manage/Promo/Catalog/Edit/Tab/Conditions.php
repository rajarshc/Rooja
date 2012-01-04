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
 * Manage Promo Catalog Edit Tab Conditions
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Promo_Catalog_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form {
	protected function _prepareForm() {
		$model = $this->_getCatalogRule ();
		
		//$form = new Varien_Data_Form(array('id' => 'edit_form1', 'action' => $this->getData('action'), 'method' => 'post'));
		$form = new Varien_Data_Form ();
		
		$form->setHtmlIdPrefix ( 'rule_' );
		$renderer = Mage::getBlockSingleton ( 'adminhtml/widget_form_renderer_fieldset' )->setTemplate ( 'promo/fieldset.phtml' );
		$renderer->setNewChildUrl ( $this->getUrl ( '*/manage_promo_catalog/newConditionHtml/form/rule_conditions_fieldset' ) );
		
		$fieldset = $form->addFieldset ( 'conditions_fieldset', array ('legend' => Mage::helper ( 'catalogrule' )->__ ( 'Conditions (leave blank for all products)' ) ) )->setRenderer ( $renderer );
		
		$fieldset->addField ( 'conditions', 'text', array ('name' => 'conditions', 'label' => Mage::helper ( 'catalogrule' )->__ ( 'Product Conditions' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'Product Conditions' ), 'required' => true ) )->setRule ( $model )->setRenderer ( Mage::getBlockSingleton ( 'rule/conditions' ) );
		
		if ($this->_isRedemptionType ()) {
			$fieldset = $form->addFieldset ( 'points_action_fieldset', array ('legend' => Mage::helper ( 'rewards' )->__ ( 'Customer Spends Points' ) ) );
			Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "Catalog Points Spending Rule - Conditions - Spending Amounts" );
			
			$options = Mage::getSingleton ( 'rewards/catalogrule_actions' )->getRedemptionOptionArray ();
			
			// SETUP OUR ACTION SELECTION
			$fieldset->addField ( 'points_action', 'select', array ('label' => Mage::helper ( 'salesrule' )->__ ( 'Customer Spending Style' ), 'name' => 'points_action', 'options' => $options, 'onchange' => 'toggleActionsSelect(this.value)' ) );
			
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
			$fieldset->addField ( 'points_currency_id', $currencyDataType, array ('label' => Mage::helper ( 'salesrule' )->__ ( 'Points Currency' ), 'title' => Mage::helper ( 'salesrule' )->__ ( 'Points Currency' ), 'name' => 'points_currency_id', $currencyValueType => $currencyData ) );
			
			$fieldset->addField ( 'points_amount', 'text', array ('name' => 'points_amount', 'required' => true, 'class' => 'validate-not-negative-number', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Points Amount (X)' ) ) );
			
			$fieldset->addField ( 'points_amount_step', 'text', array ('name' => 'points_amount_step', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Monetary Step (Y)' ) ) );
			//        $fieldset->addField('points_amount_step_currency_id', 'select', array(
			//            'name' => 'points_amount_step_currency_id',
			//            'label' => Mage::helper('salesrule')->__('Monetary Step Currency'),
			//            'options'    => $this->_getCurrencyList()
			//        ));
			

			$fieldset->addField ( 'points_max_qty', 'text', array ('name' => 'points_max_qty', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Maximum Total of Points To Transfer (0 for unlimited)' ) ) );
		}
		/*
        $fieldset = $form->addFieldset('actions_fieldset', array('legend'=>Mage::helper('catalogrule')->__('Actions')));

    	$fieldset->addField('actions', 'text', array(
            'name' => 'actions',
            'label' => Mage::helper('catalogrule')->__('Actions'),
            'title' => Mage::helper('catalogrule')->__('Actions'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/actions'));

        $fieldset = $form->addFieldset('options_fieldset', array('legend'=>Mage::helper('catalogrule')->__('Options')));

        $fieldset->addField('stop_rules_processing', 'select', array(
            'label'     => Mage::helper('catalogrule')->__('Stop further rules processing'),
            'title'     => Mage::helper('catalogrule')->__('Stop further rules processing'),
            'name'      => 'stop_rules_processing',
            'required' => true,
            'options'    => array(
                '1' => Mage::helper('catalogrule')->__('Yes'),
                '0' => Mage::helper('catalogrule')->__('No'),
            ),
        ));
*/
		$form->setValues ( $model->getData () );
		
		//$form->setUseContainer(true);
		

		$this->setForm ( $form );
		
		return parent::_prepareForm ();
	}
	
	private function _isRedemptionType() {
		if ($ruleTypeId = $this->_getCatalogRule ()->getRuleTypeId ()) {
			return $this->_getCatalogRule ()->isRedemptionRule ();
		}
		if ($type = ( int ) $this->getRequest ()->getParam ( 'type' )) {
			return $type === TBT_Rewards_Helper_Rule_Type::REDEMPTION;
		}
		Mage::getSingleton ( 'rewards/session' )->addError ( "Could not determine rule type in " . "Catalog/Edit/Tab/Actions so assumed redemption." );
		return true;
	}
	
	private function _isDistributionType() {
		if ($ruleTypeId = $this->_getCatalogRule ()->getRuleTypeId ()) {
			return $this->_getCatalogRule ()->isDistributionRule ();
		}
		if ($type = ( int ) $this->getRequest ()->getParam ( 'type' )) {
			return $type === TBT_Rewards_Helper_Rule_Type::DISTRIBUTION;
		}
		Mage::getSingleton ( 'rewards/session' )->addError ( "Could not determine rule type in " . "Catalog/Edit/Tab/Actions so assumed distribution." );
		return true;
	}
	
	/**
	 * Fetches the currently open catalogrule.
	 *
	 * @return TBT_Rewards_Model_Catalogrule_Rule
	 */
	protected function _getCatalogRule() {
		return Mage::registry ( 'current_promo_catalog_rule' );
	}
}
