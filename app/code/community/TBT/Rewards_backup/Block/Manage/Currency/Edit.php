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
 * Manage Currency Edit
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Currency_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	
	public function __construct() {
		parent::__construct ();
		
		$this->_objectId = 'id';
		$this->_blockGroup = 'rewards';
		$this->_controller = 'manage_currency';
		
		$this->_updateButton ( 'save', 'label', Mage::helper ( 'rewards' )->__ ( 'Save Currency' ) );
		$this->_removeButton ( 'delete' );
		
		//TODO: Re-enable this for multiple currencies
		//         if( Mage::registry('currency_data') && Mage::registry('currency_data')->getId() ) {
		//             $this->_addButton('create_new', array(
		//                 'label'     => Mage::helper('adminhtml')->__('Add Currency'),
		//                 'onclick'   => 'window.location=\''.$this->getUrl('*/*/new').'\'',
		//             ), -100);
		//         }
		//$this->_updateButton('delete', 'label', Mage::helper('rewards')->__('Delete Transfer'));
		

		$this->_addButton ( 'saveandcontinue', array ('label' => Mage::helper ( 'adminhtml' )->__ ( 'Save And Continue Edit' ), 'onclick' => 'saveAndContinueEdit()', 'class' => 'save' ), - 100 );
		
		$this->_formScripts [] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('currency_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            function clearGridSelections(id) {
                var nodes=document.getElementById('edit_form')[id];
                if(nodes instanceof NodeList) {
                  for(var i=0;i<nodes.length;i++) { nodes[i].checked=\"\"; }
                } else {
                  nodes.checked = \"\";
                }
            }
        ";
	}
	
	public function getHeaderText() {
		if (Mage::registry ( 'currency_data' ) && Mage::registry ( 'currency_data' )->getId ()) {
			return Mage::helper ( 'rewards' )->__ ( "Edit Currency #%s", $this->htmlEscape ( Mage::registry ( 'currency_data' )->getId () ) );
		} else {
			return Mage::helper ( 'rewards' )->__ ( 'Create New Currency' );
		}
	}

}