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
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
abstract class TBT_Rewards_Block_System_Config_Abstractbutton extends Mage_Adminhtml_Block_System_Config_Form_Field {
	
	public function render(Varien_Data_Form_Element_Abstract $element) {
		$buttonBlock = $element->getForm ()->getParent ()->getLayout ()->createBlock ( 'adminhtml/widget_button' );
		$data = $this->getButtonData ( $buttonBlock );
		
		$id = $element->getHtmlId ();
		
		$html = '<tr><td class="label"><label for="' . $id . '">' . $element->getLabel () . '</label></td>';
		// default value
		$html .= '<td>';
		$html .= $buttonBlock->setData ( $data )->toHtml ();
		$html .= '</td>';
		$html .= '</tr>';
		return $html;
	}
	
	// @override me.
	public abstract function getButtonData($buttonBlock);
	
	protected function _getDummyElement() {
		if (empty ( $this->_dummyElement )) {
			$this->_dummyElement = new Varien_Object ( array ('show_in_default' => 1, 'show_in_website' => 0, 'show_in_store' => 0 ) );
		}
		return $this->_dummyElement;
	}
	
	protected function _getFieldRenderer() {
		if (empty ( $this->_fieldRenderer )) {
			$this->_fieldRenderer = Mage::getBlockSingleton ( 'adminhtml/system_config_form_field' );
		}
		return $this->_fieldRenderer;
	}

}
