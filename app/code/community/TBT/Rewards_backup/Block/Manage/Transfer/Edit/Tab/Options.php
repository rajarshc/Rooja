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
 * Manage Transfer Edit Tab Options
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Transfer_Edit_Tab_Options extends Mage_Adminhtml_Block_Widget_Form {
	
	protected function _prepareForm() {
		$form = new Varien_Data_Form ();
		$this->setForm ( $form );
		$fieldset = $form->addFieldset ( 'transfer_form', array ('legend' => Mage::helper ( 'rewards' )->__ ( 'Meta Data' ) ) );
		
		$fieldset->addField ( 'meta_keywords', 'editor', array ('name' => 'meta_keywords', 'label' => Mage::helper ( 'rewards' )->__ ( 'Keywords' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Meta Keywords' ), 'style' => 'width: 520px;' ) );
		
		$fieldset->addField ( 'meta_description', 'editor', array ('name' => 'meta_description', 'label' => Mage::helper ( 'rewards' )->__ ( 'Description' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Meta Description' ), 'style' => 'width: 520px;' ) );
		
		$fieldset = $form->addFieldset ( 'transfer_options', array ('legend' => Mage::helper ( 'rewards' )->__ ( 'Advanced Post Options' ) ) );
		
		$fieldset->addField ( 'user', 'text', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Poster' ), 'name' => 'user', 'style' => 'width: 520px;', 'after_element_html' => '<span class="hint">(Leave blank to use current user name)</span>' ) );
		
		$fieldset->addField ( 'created_time', 'text', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Post Date' ), 'name' => 'created_time', 'style' => 'width: 520px;', 'after_element_html' => '<span class="hint">(eg: YYYY-MM-DD HH:MM:SS Leave blank to use current date)</span>' ) );
	}

}