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
 * Manage Promo Catalog Edit Tab Main
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Promo_Catalog_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form {
	
	protected function _prepareForm() {
		$model = Mage::registry ( 'current_promo_catalog_rule' );
		
		$form = new Varien_Data_Form ();
		
		$form->setHtmlIdPrefix ( 'rule_' );
		
		$fieldset = $form->addFieldset ( 'base_fieldset', array ('legend' => Mage::helper ( 'catalogrule' )->__ ( 'General Information' ) ) );
		
		$fieldset->addField ( 'auto_apply', 'hidden', array ('name' => 'auto_apply' ) );
		
		if ($model->getId ()) {
			$fieldset->addField ( 'rule_id', 'hidden', array ('name' => 'rule_id' ) );
		}
		
		$fieldset->addField ( 'name', 'text', array ('name' => 'name', 'label' => Mage::helper ( 'catalogrule' )->__ ( 'Rule Name' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'Rule Name' ), 'required' => true ) );
		
		$fieldset->addField ( 'description', 'textarea', array ('name' => 'description', 'label' => Mage::helper ( 'catalogrule' )->__ ( 'Description' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'Description' ), 'style' => 'height: 100px;' ) );
		
		$fieldset->addField ( 'is_active', 'select', array ('label' => Mage::helper ( 'catalogrule' )->__ ( 'Status' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'Status' ), 'name' => 'is_active', 'required' => true, 'options' => array ('1' => Mage::helper ( 'catalogrule' )->__ ( 'Active' ), '0' => Mage::helper ( 'catalogrule' )->__ ( 'Inactive' ) ) ) );
		
		if (! Mage::app ()->isSingleStoreMode ()) {
			$fieldset->addField ( 'website_ids', 'multiselect', array ('name' => 'website_ids[]', 'label' => Mage::helper ( 'catalogrule' )->__ ( 'Websites' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'Websites' ), 'required' => true, 'values' => Mage::getSingleton ( 'adminhtml/system_config_source_website' )->toOptionArray () ) );
		} else {
			$fieldset->addField ( 'website_ids', 'hidden', array ('name' => 'website_ids[]', 'value' => Mage::app ()->getStore ( true )->getWebsiteId () ) );
			$model->setWebsiteIds ( Mage::app ()->getStore ( true )->getWebsiteId () );
		}
		
		$customerGroups = Mage::getResourceModel ( 'customer/group_collection' )->load ()->toOptionArray ();
		
		$found = false;
		foreach ( $customerGroups as $group ) {
			if ($group ['value'] == 0) {
				$found = true;
			}
		}
		if (! $found) {
			array_unshift ( $customerGroups, array ('value' => 0, 'label' => Mage::helper ( 'catalogrule' )->__ ( 'NOT LOGGED IN' ) ) );
		}
		
		$fieldset->addField ( 'customer_group_ids', 'multiselect', array ('name' => 'customer_group_ids[]', 'label' => Mage::helper ( 'catalogrule' )->__ ( 'Customer Groups' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'Customer Groups' ), 'required' => true, 'values' => $customerGroups ) );
		
		$dateFormatIso = Mage::app ()->getLocale ()->getDateFormat ( Mage_Core_Model_Locale::FORMAT_TYPE_SHORT );
		$fieldset->addField ( 'from_date', 'date', array ('name' => 'from_date', 'label' => Mage::helper ( 'catalogrule' )->__ ( 'From Date' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'From Date' ), 'image' => $this->getSkinUrl ( 'images/grid-cal.gif' ), 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 'format' => $dateFormatIso ) );
		$fieldset->addField ( 'to_date', 'date', array ('name' => 'to_date', 'label' => Mage::helper ( 'catalogrule' )->__ ( 'To Date' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'To Date' ), 'image' => $this->getSkinUrl ( 'images/grid-cal.gif' ), 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 'format' => $dateFormatIso ) );
		
		$element = $fieldset->addField ( 'sort_order', 'text', array ('name' => 'sort_order', 'label' => Mage::helper ( 'catalogrule' )->__ ( 'Priority' ) ) );
		Mage::getSingleton('rewards/wikihints')->addWikiHint($element, "Rule Priority", null, $this->__("Get help with rule priorities."));
		
		$form->setValues ( $model->getData () );
		
		$this->setForm ( $form );
		
		return parent::_prepareForm ();
	}
}