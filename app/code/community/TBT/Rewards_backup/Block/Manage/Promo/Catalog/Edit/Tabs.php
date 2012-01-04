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
 * Manage Promo Catalog Edit Tabs
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Promo_Catalog_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
	
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'promo_catalog_edit_tabs' );
		$this->setDestElementId ( 'edit_form' );
		if ($this->_isRedemptionType ()) {
			$this->setTitle ( Mage::helper ( 'catalogrule' )->__ ( 'Catalog Points Spending Rule' ) );
		} else {
			$this->setTitle ( Mage::helper ( 'catalogrule' )->__ ( 'Catalog Points Earning Rule' ) );
		}
	}
	
	protected function _beforeToHtml() {
		$this->addTab ( 'main_section', array ('label' => Mage::helper ( 'catalogrule' )->__ ( 'Rule Information' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'Rule Information' ), 'content' => $this->getLayout ()->createBlock ( 'rewards/manage_promo_catalog_edit_tab_main' )->toHtml (), 'active' => true ) );
		
		$this->addTab ( 'conditions_section', array ('label' => Mage::helper ( 'catalogrule' )->__ ( 'Conditions' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'Conditions' ), 'content' => $this->getLayout ()->createBlock ( 'rewards/manage_promo_catalog_edit_tab_conditions' )->toHtml () ) );
		
		$this->addTab ( 'actions_section', array ('label' => Mage::helper ( 'catalogrule' )->__ ( 'Actions' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'Actions' ), 'content' => $this->getLayout ()->createBlock ( 'rewards/manage_promo_catalog_edit_tab_actions' )->toHtml () ) );
		// Custom catalog rule labels
		if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4' ) && $this->_isRedemptionType ()) {
			$this->addTab ( 'labels_section', array ('label' => Mage::helper ( 'catalogrule' )->__ ( 'Labels' ), 'content' => $this->getLayout ()->createBlock ( 'rewards/manage_promo_catalog_edit_tab_labels' )->toHtml () ) );
		}
		
		return parent::_beforeToHtml ();
	}
	
	/**
	 * Fetches the currently open catalogrule.
	 *
	 * @return TBT_Rewards_Model_Catalogrule_Rule
	 */
	protected function _getRule() {
		return Mage::registry ( 'current_promo_catalog_rule' );
	}
	
	/**
	 * Returns true if this should display redemption
	 *
	 * @return boolean
	 */
	protected function _isRedemptionType() {
		if ($type = $this->getRequest ()->getParam ( 'type' )) {
			return $type == TBT_Rewards_Helper_Rule_Type::REDEMPTION;
		}
		if ($ruleTypeId = $this->_getRule ()->getRuleTypeId ()) {
			return $this->_getRule ()->isRedemptionRule ();
		}
		Mage::getSingleton ( 'rewards/session' )->addError ( "Could not determine rule type in " . "Catalog/Edit/Tab/Actions so assumed redemption." );
		return true;
	}
	
	/**
	 * Returns true if this should display distribution
	 *
	 * @return boolean
	 */
	protected function _isDistributionType() {
		if ($type = $this->getRequest ()->getParam ( 'type' )) {
			return $type == TBT_Rewards_Helper_Rule_Type::DISTRIBUTION;
		}
		if ($ruleTypeId = $this->_getRule ()->getRuleTypeId ()) {
			return $this->_getRule ()->isDistributionRule ();
		}
		Mage::getSingleton ( 'rewards/session' )->addError ( "Could not determine rule type in " . "Catalog/Edit/Tab/Actions so assumed distribution." );
		return true;
	}

}
