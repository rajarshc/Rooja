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
 * Manage Special Edit Tab Main
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Special_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form {
	
	protected function _prepareForm() {
		$model = Mage::registry ( 'global_manage_special_rule' );
		
		$form = new Varien_Data_Form ();
		
		$form->setHtmlIdPrefix ( 'rule_' );
		
		$fieldset = $form->addFieldset ( 'base_fieldset', array ('legend' => Mage::helper ( 'salesrule' )->__ ( 'General Information' ) ) );
		
		if ($model->getId ()) {
			$fieldset->addField ( 'rewards_special_id', 'hidden', array ('name' => 'rewards_special_id' ) );
		}
		
		$fieldset->addField ( 'product_ids', 'hidden', array ('name' => 'product_ids' ) );
		
		$fieldset->addField ( 'name', 'text', array ('name' => 'name', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Rule Name' ), 'title' => Mage::helper ( 'salesrule' )->__ ( 'Rule Name' ), 'required' => true ) );
		
		$fieldset->addField ( 'description', 'textarea', array ('name' => 'description', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Description' ), 'title' => Mage::helper ( 'salesrule' )->__ ( 'Description' ), 'style' => 'height: 100px;' ) );
		
		$fieldset->addField ( 'is_active', 'select', array ('label' => Mage::helper ( 'salesrule' )->__ ( 'Status' ), 'title' => Mage::helper ( 'salesrule' )->__ ( 'Status' ), 'name' => 'is_active', 'required' => true, 'options' => array ('1' => Mage::helper ( 'salesrule' )->__ ( 'Active' ), '0' => Mage::helper ( 'salesrule' )->__ ( 'Inactive' ) ) ) );
		
		if (! Mage::app ()->isSingleStoreMode ()) {
			$fieldset->addField ( 'website_ids', 'multiselect', array ('name' => 'website_ids[]', 'label' => Mage::helper ( 'catalogrule' )->__ ( 'Websites' ), 'title' => Mage::helper ( 'catalogrule' )->__ ( 'Websites' ), 'required' => true, 'values' => Mage::getSingleton ( 'adminhtml/system_config_source_website' )->toOptionArray () ) );
		} else {
			$fieldset->addField ( 'website_ids', 'hidden', array ('name' => 'website_ids[]', 'value' => Mage::app ()->getStore ( true )->getWebsiteId () ) );
			$model->setWebsiteIds ( Mage::app ()->getStore ( true )->getWebsiteId () );
		}
		
		$element = $fieldset->addField ( 'sort_order', 'text', array ('name' => 'sort_order', 'label' => Mage::helper ( 'salesrule' )->__ ( 'Priority' ) ) );
		Mage::getSingleton('rewards/wikihints')->addWikiHint($element, "Rule Priority", null, $this->__("Get help with rule priorities."));
		
		$fieldset->addField ( 'is_rss', 'select', array ('label' => Mage::helper ( 'salesrule' )->__ ( 'Public In RSS Feed' ), 'title' => Mage::helper ( 'salesrule' )->__ ( 'Public In RSS Feed' ), 'name' => 'is_rss', 'options' => array ('1' => Mage::helper ( 'salesrule' )->__ ( 'Yes' ), '0' => Mage::helper ( 'salesrule' )->__ ( 'No' ) ) ) );
		
		if (! $model->getId ()) {
			//set the default value for is_rss feed to yes for new promotion
			$model->setIsRss ( 1 );
		}
		
		$form->setValues ( $model->getData () );
		
		$this->setForm ( $form );
		
		return parent::_prepareForm ();
	}

}