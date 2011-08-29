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
 * Manage Promo Catalog Edit Tab Actions
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Promo_Catalog_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form {
	
	protected $_currencyList;
	protected $_currencyModel;
	
	protected function _prepareForm() {
		$model = $this->_getCatalogRule ();
		
		$form = new Varien_Data_Form ();
		
		$form->setHtmlIdPrefix ( 'rule_' );
		
		if ($this->_isDistributionType ()) {
			$fieldset = $form->addFieldset ( 'points_action_fieldset', array ('legend' => Mage::helper ( 'rewards' )->__ ( 'Reward With Points' ) ) );
		    Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "Catalog Earning Rules - Actions" );
		    
			$options = Mage::getSingleton ( 'rewards/catalogrule_actions' )->getDistributionOptionArray ();
			
			$fieldset->addField ( 'points_action', 'select', array ('label' => Mage::helper ( 'salesrule' )->__ ( 'Action' ), 'name' => 'points_action', 'options' => $options, 'onchange' => 'toggleActionsSelect(this.value)' ) );
			
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
			
			$fieldset->addField ( 'points_amount', 'text', array ('name' => 'points_amount', 'required' => true, 'class' => 'validate-not-negative-number', 'label' => Mage::helper ( 'rewards' )->__ ( 'Points Amount (X)' ) ) );
			
			$fieldset->addField ( 'points_amount_step', 'text', array ('name' => 'points_amount_step', 'label' => Mage::helper ( 'rewards' )->__ ( 'Monetary Step (Y)' ) ) );
			
			//TODO: Uncommetn for multiple currencies
			//        $fieldset->addField('points_amount_step_currency_id', 'select', array(
			//            'name' => 'points_amount_step_currency_id',
			//            'label' => Mage::helper('salesrule')->__('Monetary Step Currency'),
			//            'options'    => $this->_getCurrencyList()
			//        ));
			

			$points_max_qty_field = $fieldset->addField ( 'points_max_qty', 'text', array ('name' => 'points_max_qty', 'label' => Mage::helper ( 'rewards' )->__ ( 'Maximum Total of Points To Transfer (0 for unlimited)' ) ) );
		    Mage::getSingleton('rewards/wikihints')->addWikiHint($points_max_qty_field, "Catalog Points Earning Rule: Maximum Total of Points To Transfer" );
			
			$simple_actions = array ('' => Mage::helper ( 'salesrule' )->__ ( 'No Discount -- ' ), 'by_percent' => Mage::helper ( 'salesrule' )->__ ( 'By Percentage of the original price' ), 'by_fixed' => Mage::helper ( 'salesrule' )->__ ( 'By Fixed Amount' ), 'to_percent' => Mage::helper ( 'salesrule' )->__ ( 'To Percentage of the original price' ), 'to_fixed' => Mage::helper ( 'salesrule' )->__ ( 'To Fixed Amount' ) );
			$simple_actions_caption = 'Additionally, update prices using the following information';
		
		} else { // NOT distribution rule (ie, this is a redemption rule)
			

			$simple_actions = array ('by_percent' => Mage::helper ( 'salesrule' )->__ ( 'By Percentage of the original price' ), 'by_fixed' => Mage::helper ( 'salesrule' )->__ ( 'By Fixed Amount' ), 'to_percent' => Mage::helper ( 'salesrule' )->__ ( 'To Percentage of the original price' ), 'to_fixed' => Mage::helper ( 'salesrule' )->__ ( 'To Fixed Amount' ) );
			$simple_actions_caption = 'Update prices using the following information';
			
			$fieldset = $form->addFieldset ( 'action_fieldset', array ('legend' => Mage::helper ( 'catalogrule' )->__ ( $simple_actions_caption ) ) );
			Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "Catalog Points Spending Rule - Actions - Discount Amount" );
			
			$fieldset->addField ( 'points_catalogrule_simple_action', 'select', array ('label' => Mage::helper ( 'salesrule' )->__ ( 'Discount Style' ), 'name' => 'points_catalogrule_simple_action', 'options' => $simple_actions, 'onchange' => 'toggleDiscountActionsSelect(this.value)' ) );
			
			$fieldset->addField ( 'points_catalogrule_discount_amount', 'text', array (
                            'name' => 'points_catalogrule_discount_amount', 
                            'required' => true, 
                            'class' => 'validate-not-negative-number', 
                            'value' => '0', 
                            'label' => Mage::helper ( 'salesrule' )->__ ( 'Discount Amount' ) ) );
			
			$element = $fieldset->addField ( 'points_catalogrule_stop_rules_processing', 'select', array ('label' => Mage::helper ( 'salesrule' )->__ ( 'Stop further rules processing' ), 'title' => Mage::helper ( 'salesrule' )->__ ( 'Stop further rules processing' ), 'name' => 'points_catalogrule_stop_rules_processing', 'options' => array ('1' => Mage::helper ( 'salesrule' )->__ ( 'Yes' ), '0' => Mage::helper ( 'salesrule' )->__ ( 'No' ) ) ) );
			Mage::getSingleton('rewards/wikihints')->addWikiHint($element, "Stop further rules processing", null, $this->__("Get help with the Stop Further Rules Processing flag."));
					
			$points_uses_per_product_field = $fieldset->addField ( 'points_uses_per_product', 'text', array (
				'name' => 'points_uses_per_product', 
				'required' => true, 
				'class' => 'validate-not-negative-number', 
				'value' => 1, 'label' => Mage::helper ( 'rewards' )->__ ( 'Uses Allowed Per Product (0 for unlimited)' ) ) );
			Mage::getSingleton('rewards/wikihints')->addWikiHint($points_uses_per_product_field, "Catalog Points Spending Rule - Actions - Uses Allowed Per Product" );
			
		}
		
		$model_data = $model->getData ();
		if (empty ( $model_data ['points_catalogrule_discount_amount'] )) {
			$model_data ['points_catalogrule_discount_amount'] = '0';
		}
		
		$form->setValues ( $model_data );
		
		$fieldset->addField ( 'simple_action', 'hidden', array ('name' => 'simple_action', 'value' => 'by_percent' ) );
		
		$fieldset->addField ( 'discount_amount', 'hidden', array ('name' => 'discount_amount', 'required' => true, 'class' => 'validate-not-negative-number', 'value' => 0 ) );
		
		$fieldset->addField ( 'stop_rules_processing', 'hidden', array ('label' => Mage::helper ( 'salesrule' )->__ ( 'Stop further rules processing' ), 'title' => Mage::helper ( 'salesrule' )->__ ( 'Stop further rules processing' ), 'name' => 'stop_rules_processing', 'value' => 0 ) );

		
		//$form->setUseContainer(true);
		

		$this->setForm ( $form );
		
		return $this;
	}
	
	protected function _getCurrencyList() {
		if (is_null ( $this->_currencyList )) {
			$this->_currencyList = $this->_getCurrencyModel ()->getConfigAllowCurrencies ();
		}
		return $this->_currencyList;
	}
	
	protected function _getCurrencyModel() {
		if (is_null ( $this->_currencyModel ))
			$this->_currencyModel = Mage::getModel ( 'directory/currency' );
		
		return $this->_currencyModel;
	}
	
	private function _isRedemptionType() {
		if ($ruleTypeId = $this->_getCatalogRule ()->getRuleTypeId ()) {
			return $this->_getCatalogRule ()->isRedemptionRule ();
		}
		if ($type = ( int ) $this->getRequest ()->getParam ( 'type' )) {
			return $type === TBT_Rewards_Helper_Rule_Type::REDEMPTION;
		}
		Mage::getSingleton ( 'adminhtml/session' )->addError ( "Could not determine rule type in " . "Catalog/Edit/Tab/Actions so assumed redemption." );
		return true;
	}
	
	private function _isDistributionType() {
		if ($ruleTypeId = $this->_getCatalogRule ()->getRuleTypeId ()) {
			return $this->_getCatalogRule ()->isDistributionRule ();
		}
		if ($type = ( int ) $this->getRequest ()->getParam ( 'type' )) {
			return $type === TBT_Rewards_Helper_Rule_Type::DISTRIBUTION;
		}
		Mage::getSingleton ( 'adminhtml/session' )->addError ( "Could not determine rule type in " . "Catalog/Edit/Tab/Actions so assumed distribution." );
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
