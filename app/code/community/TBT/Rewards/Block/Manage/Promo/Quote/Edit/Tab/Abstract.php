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
 * Manage Promo Quote Edit Tab Abstract
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
abstract class TBT_Rewards_Block_Manage_Promo_Quote_Edit_Tab_Abstract extends Mage_Adminhtml_Block_Widget_Form {
	
	/**
	 * @param Varien_Data_Form &$form
	 * @param TBT_Rewards_Model_Salesrule_Rule &$model
	 */
	protected function _getPriceActionFieldset(Varien_Data_Form &$form, TBT_Rewards_Model_Salesrule_Rule &$model) {
		
		$model = $this->_getRule ();
		/* December 1st, 2010: Removed "Fixed Discount" option from Shopping Cart Points Redemption Rule page.
    	 * Addresses issue: http://www.wdca.ca/mantis/view.php?id=429
		*/
		if ($this->_isRedemptionType ()) {
			$field_title = 'Update prices using the following information';
			$actions = array (// 'by_fixed' => Mage::helper('rewards')->__('Fixed amount discount'),
				'cart_fixed' => Mage::helper ( 'rewards' )->__ ( 'Fixed amount discount for whole cart' ), 
				'by_percent' => Mage::helper ( 'rewards' )->__ ( 'Percentage amount discount for whole cart' ) 
			);//'buy_x_get_y' => Mage::helper('rewards')->__('Buy X get Y free (discount amount is Y) [in development]'),
			
			$apply_caption = Mage::helper ( 'rewards' )->__ ( 'Discount Style' );
			
			$fieldset = $form->addFieldset ( 'action_fieldset', array ('legend' => Mage::helper ( 'rewards' )->__ ( $field_title ) ) );
		    Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "Shopping Cart Spending Rule - Actions - Update prices using the following information" );
			
			$fieldset->addField ( 'simple_action', 'select', array ('label' => $apply_caption, 'name' => 'simple_action', 'options' => $actions, 'onchange' => 'toggleDiscountActionsSelect(this.value)' ) );
			
			$fieldset->addField ( 'discount_amount', 'text', array ('name' => 'discount_amount', 'required' => true, 'class' => 'validate-not-negative-number', 'label' => Mage::helper ( 'rewards' )->__ ( 'Discount Amount' ) ) );
			
			/*
        $fieldset->addField('discount_qty', 'text', array(
            'name' => 'discount_qty',
            'label' => Mage::helper('rewards')->__("Maximum Qty Discount is Applied to (only for 'Buy X-Get Y' rules)"),
        ));
        $model->setDiscountQty($model->getDiscountQty()*1);

        $fieldset->addField('discount_step', 'text', array(
            'name' => 'discount_step',
            'label' => Mage::helper('rewards')->__("Discount Qty Step (Buy X, only for 'Buy X-Get Y' rules)"),
        ));
*/
			//TODO allow shipping discounts
			$disabled_field_msg_html = "<div class='disabled-field-msg' style='font-style: italic; font-size: 10px;'>" . $this->__ ( "This feature has been temporarily disabled for this version of Sweet Tooth in ensure quality.  It will be re-enabled in the next release of Sweet Tooth." ) . "</div>";
			//@nelkaake -a 16/11/10: 
			if ($this->_isRedemptionType () && Mage::helper ( 'rewards' )->isMageVersionAtLeast ( '1.4' )) {
			    
			    //@nelkaake hidden these fields. Did not remove them in case the Magento default values are not adequate.
				$fieldset->addField ( 
					'apply_to_shipping', 
					'hidden', array (
						'label' => Mage::helper ( 'rewards' )->__ ( 'Apply to Shipping Amount' ), 
						'title' => Mage::helper ( 'rewards' )->__ ( 'Apply to Shipping Amount' ), 
						'name' => 'apply_to_shipping', 
						'values' => Mage::getSingleton ( 'adminhtml/system_config_source_yesno' )->toOptionArray (), 
						'disabled' => true, 
						'value' => 0 
				    )
				);
			}
			
			//@nelkaake hidden these fields. Did not remove them in case the Magento default values are not adequate.
			$fieldset->addField ( 'simple_free_shipping', 
				'hidden', array (
					'label' => Mage::helper ( 'rewards' )->__ ( 'Free shipping' ), 
					'title' => Mage::helper ( 'rewards' )->__ ( 'Free shipping' ), 
					'name' => 'simple_free_shipping', 
					'disabled' => true, 
					'options' => array (
			            0 => Mage::helper ( 'rewards' )->__ ( 'No' ), 
			            Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM => Mage::helper ( 'rewards' )->__ ( 'For items matching the conditions below' ), 
			            Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS => Mage::helper ( 'rewards' )->__ ( 'For the whole cart' ) 
			         ),
			         'value' => 0 
			     ) 
			);
			
			$stop_rules_processing_field = $fieldset->addField ( 'stop_rules_processing', 'select', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Stop further rules processing' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Stop further rules processing' ), 'name' => 'stop_rules_processing', 'options' => array ('1' => Mage::helper ( 'rewards' )->__ ( 'Yes' ), '0' => Mage::helper ( 'rewards' )->__ ( 'No' ) ) ) );
		    Mage::getSingleton('rewards/wikihints')->addWikiHint($stop_rules_processing_field, "Shopping Cart Spending Rule - Actions - Stop Further Rules Processing" );
		
		} else {
		
			$form->addField ( 'stop_rules_processing', 'hidden', array ('name' => 'stop_rules_processing', 'value' => "0" ));
			$model->setStopRulesProcessing(0);
			
			$field_title = 'ADDITIONALLY, update prices using the following information - even if the customer does not spend any points';
			$actions = array ('' => Mage::helper ( 'rewards' )->__ ( 'No Additional Discount--' ), 'by_percent' => Mage::helper ( 'rewards' )->__ ( 'Percentage amount discount for whole cart' ), // 'by_fixed' => Mage::helper('rewards')->__('Fixed amount discount'),
			'cart_fixed' => Mage::helper ( 'rewards' )->__ ( 'Fixed amount discount for whole cart' ) )//'buy_x_get_y' => Mage::helper('rewards')->__('Buy X get Y free (discount amount is Y) [in development]'),
			;
			$apply_caption = Mage::helper ( 'rewards' )->__ ( 'Apply Additional Discount' );
		}
		
		$model->setDiscountAmount ( $model->getDiscountAmount () * 1 );
		return $this;
	
	}
	
	protected function _getApplyToActionFieldset(&$form, &$model) {
		$renderer = Mage::getBlockSingleton ( 'adminhtml/widget_form_renderer_fieldset' )->setTemplate ( 'promo/fieldset.phtml' );
		
		$renderer->setNewChildUrl ( $this->getUrl ( '*/manage_promo_quote/newActionHtml/form/rule_actions_fieldset' ) );
		
		$fieldset = $form->addFieldset ('actions_fieldset', array ('legend' => Mage::helper ( 'rewards' )->__ ( 'Apply the rule actions only to cart items matching the following conditions (leave blank for all items)' ) . '<div id="NoSupportNotice" style="display: inline-block"></div>' ) )->setRenderer ( $renderer );
		
		Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "How to apply the rule actions to a subset of products" );
		
		$fieldset->addField ( 'actions', 'text', array ('name' => 'actions', 'label' => Mage::helper ( 'rewards' )->__ ( 'Apply to' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Apply to' ), 'required' => true ) )->setRule ( $model )->setRenderer ( Mage::getBlockSingleton ( 'rule/actions' ) );
		
		return $this;
	}
	
	protected function _getPointsActionFieldset(&$form) {
		$model = $this->_getRule ();
		if ($this->_isRedemptionType ()) {
			$field_caption = Mage::helper ( 'rewards' )->__ ( 'Customer redeems the following points' );
			$action_caption = Mage::helper ( 'rewards' )->__ ( "Customer Spends" );
			$points_max_qty_caption = $this->__ ( "Maximum Redeemable Points" );
		} else {
			$field_caption = Mage::helper ( 'rewards' )->__ ( 'Reward With Points' );
			$action_caption = Mage::helper ( 'rewards' )->__ ( "Customer Earning Style" );
			$points_max_qty_caption = Mage::helper ( 'rewards' )->__ ( 'Maximum Distributed Points (0 for unlimited)' );
		}
		$fieldset = $form->addFieldset ( 'points_action_fieldset', array ('legend' => $field_caption ) );
		
		
		if ($this->_isRedemptionType ()) {
		    Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "Shopping Cart Spending Rule - Actions" );
		} else {
		    Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "Shopping Cart Earning Rule - Actions" );
		}
		
		$pointsActionField = $fieldset->addField ( 'points_action', 'select', array ('label' => $action_caption, 'name' => 'points_action', 'options' => $this->_getPointsActionOptions (), 'onchange' => 'toggleActionsSelect(this.value)' ) );
		
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
		
		$fieldset->addField ( 'points_amount', 'text', array ('name' => 'points_amount', 'required' => false, 'class' => 'validate-not-negative-number', 'label' => Mage::helper ( 'rewards' )->__ ( 'Points Amount (X)' ) ) );
		
		$fieldset->addField ( 'points_amount_step', 'text', array ('name' => 'points_amount_step', 'label' => Mage::helper ( 'rewards' )->__ ( 'Monetary Step (Y) (in base currency)' ) ) );
		
		$fieldset->addField ( 'points_qty_step', 'text', array ('name' => 'points_qty_step', 'label' => Mage::helper ( 'rewards' )->__ ( 'Quantity Step (Z)' ) ) );
		
		$points_max_qty_field = $fieldset->addField ( 'points_max_qty', 'text', array ('name' => 'points_max_qty', 'label' => Mage::helper ( 'rewards' )->__ ( $points_max_qty_caption ) ) );
		
	
		if ($this->_isRedemptionType ()) {
		    Mage::getSingleton('rewards/wikihints')->addWikiHint($points_max_qty_field, "Shopping Cart Points Spending Rule: Maximum Distributed Points" );
		} else {
		    Mage::getSingleton('rewards/wikihints')->addWikiHint($points_max_qty_field, "Shopping Cart Points Earning Rule: Maximum Distributed Points" );
		}
		
		
		return $fieldset;
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
	/**
	 * Returns the model for this form.
	 *
	 * @return TBT_Rewards_Model_Salesrule_Rule
	 */
	protected function _getRule() {
		$model = Mage::registry ( 'current_promo_quote_rule' );
		return $model;
	}
	
	/**
	 * Fetches the action option array that should be displayed.
	 *
	 * @return array;
	 */
	protected function _getPointsActionOptions() {
		if ($this->_isRedemptionType ()) {
			$options = Mage::getSingleton ( 'rewards/salesrule_actions' )->getRedemptionsOptionArray ();
		} else {
			$options = Mage::getSingleton ( 'rewards/salesrule_actions' )->getDistributionsOptionArray ();
		}
		return $options;
	}
	
	/**
	 * Returns true if this should display redemption
	 *
	 * @return boolean
	 */
	protected function _isRedemptionType() {
		if ($ruleTypeId = $this->_getRule ()->getRuleTypeId ()) {
			return $this->_getRule ()->isRedemptionRule ();
		}
		if ($type = ( int ) $this->getRequest ()->getParam ( 'type' )) {
			return $type === TBT_Rewards_Helper_Rule_Type::REDEMPTION;
		}
		Mage::getSingleton ( 'rewards/session' )->addError ( "Could not determine rule type in " . "Quote/Edit/Tab/Actions so assumed distribution." );
		return true;
	}
	/**
	 * Returns true if this should display distribution
	 *
	 * @return boolean
	 */
	protected function _isDistributionType() {
		if ($ruleTypeId = $this->_getRule ()->getRuleTypeId ()) {
			return $this->_getRule ()->isDistributionRule ();
		}
		if ($type = ( int ) $this->getRequest ()->getParam ( 'type' )) {
			return $type === TBT_Rewards_Helper_Rule_Type::DISTRIBUTION;
		
		}
		Mage::getSingleton ( 'rewards/session' )->addError ( "Could not determine rule type in " . "Quote/Edit/Tab/Actions so assumed distribution." );
		return true;
	}

}
