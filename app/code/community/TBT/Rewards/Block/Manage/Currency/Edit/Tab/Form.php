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
 * Manage Curency Edit Tab Form
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Currency_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	protected function _prepareForm() {
		
		if (Mage::getSingleton ( 'adminhtml/session' )->getCurrencyData ()) {
			$formData = Mage::getSingleton ( 'adminhtml/session' )->getCurrencyData ();
		} elseif (Mage::registry ( 'currency_data' )) {
			$formData = Mage::registry ( 'currency_data' )->getData ();
		} else {
			$formData = array ();
		}
		
		$form = new Varien_Data_Form ();
		$this->setForm ( $form );
		$fieldset = $form->addFieldset ( 'currency_form', array ('legend' => Mage::helper ( 'rewards' )->__ ( 'Currency Information' ) ) );
		Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "Points Currency Appearance" );
		
		$fieldset->addField ( 'active', 'hidden', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Active' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Active' ), 'name' => 'active', 'options' => Mage::helper ( 'rewards/currency' )->getActiveOptions () ) );
		
		$fieldset->addField ( 'caption', 'text', array ('name' => 'caption', //            'required' => true,      //@nelkaake no longer required as of Sweet Tooth 1.3 (2/17/2010 7:00:09 AM)
//            'class'     => 'required-entry',
		'label' => Mage::helper ( 'rewards' )->__ ( 'Currency Caption' ) ) );
		
		//        $fieldset->addField('caption_plural', 'text', array(
		//            'name' => 'caption_plural',
		//            'required' => true,
		//            'class'     => 'required-entry',
		//            'label' => Mage::helper('rewards')->__('Plural Currency Caption'),
		//        ));
		

		$fieldset->addField ( 'value', 'hidden', array ('name' => 'value', 'required' => true, 'class' => 'validate-number', 'label' => Mage::helper ( 'rewards' )->__ ( 'Currency Value' ) ) );
		
		$form->setValues ( $formData );
		Mage::getSingleton ( 'adminhtml/session' )->getCurrencyData ( null );
		
		return parent::_prepareForm ();
	}

}