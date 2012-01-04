<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/solutions_page_sweettooth/Sweet_Tooth_License.php
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
class TBT_Rewards_Block_Manage_Currency_Edit_Tab_Appearance extends Mage_Adminhtml_Block_Widget_Form {
	
	public function __construct() {
		parent::__construct ();
		$this->setTemplate ( 'rewards/currency/appearance.phtml' );
	}
	
	public function showImgPreview() {
		$data = $this->getCurrencyData ();
		if (empty ( $data ))
			return false;
		if (! isset ( $data ['image'] ))
			return false;
		return true;
	}
	
	public function getImgSrc() {
		$data = $this->getCurrencyData ();
		$reward_currency = $data ['rewards_currency_id'];
		$url = $this->getPointsImgUrl ( 29, $reward_currency );
		return $url;
	}
	
	/**
	 * @param integer $num_points
	 * @param integer $currency_id
	 * @return string
	 */
	public function getPointsImgUrl($num_points, $currency_id) {
		if ($num_points > 0) {
			$params = array ('quantity' => $num_points, 'currency' => $currency_id );
			$url = $this->getUrl ( 'rewards/image/', $params );
			return $url;
		} else {
			return "";
		}
	}
	
	protected function getCurrencyData() {
		
		if (Mage::getSingleton ( 'adminhtml/session' )->getCurrencyData ()) {
			$formData = Mage::getSingleton ( 'adminhtml/session' )->getCurrencyData ();
		} elseif (Mage::registry ( 'currency_data' )) {
			$formData = Mage::registry ( 'currency_data' )->getData ();
		} else {
			$formData = array ();
		}
		return $formData;
	}
	
	protected function _prepareForm() {
		
		$formData = $this->getCurrencyData ();
		$form = new Varien_Data_Form ();
		$this->setForm ( $form );
		$fieldset = $form->addFieldset ( 'currency_form', array ('legend' => Mage::helper ( 'rewards' )->__ ( 'Currency Appearance' ) ) );
		Mage::getSingleton('rewards/wikihints')->addWikiHint($fieldset, "Points Currency Section" );
		
		$image_field = $fieldset->addField ( 'image', 'text', array ('name' => 'image', 'label' => Mage::helper ( 'rewards' )->__ ( 'Image Path (relative to store skin)' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Image Path (relative to store skin)' ) ) );
		Mage::getSingleton('rewards/wikihints')->addWikiHint($image_field, "Points Currency Section - Image Path" );
		
		
		$fieldset->addField ( 'image_width', 'text', array ('name' => 'image_width', 'class' => 'validate-number', 'label' => Mage::helper ( 'rewards' )->__ ( 'Image Width (px)' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Image Width (px)' ) ) );
		
		$fieldset->addField ( 'image_height', 'text', array ('name' => 'image_height', 'class' => 'validate-number', 'label' => Mage::helper ( 'rewards' )->__ ( 'Image Height (px)' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Image Height (px)' ) ) );
		
		/*
         * 
          $font_dir = $skin_dir . "fonts". SLASH;
          $module_images_dir = $skin_dir . "images". SLASH . "rewards". SLASH;
         */
		$fieldset->addField ( 'image_write_quantity', 'select', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Print Points on Image' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Print Points on Image' ), 'name' => 'image_write_quantity', 'options' => array (0 => 'No', 1 => 'Yes' ) ) );
		
		$fieldset->addField ( 'font', 'text', array ('name' => 'font', 'label' => Mage::helper ( 'rewards' )->__ ( 'Font Path (relative to store skin)' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Font Path (relative to store skin)' ) ) );
		
		$fieldset->addField ( 'font_size', 'text', array ('name' => 'font_size', 'class' => 'validate-number', 'label' => Mage::helper ( 'rewards' )->__ ( 'Font Size (pt)' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Font Size (pt)' ) ) );
		
		$fieldset->addField ( 'font_color', 'text', array ('name' => 'font_color', 'class' => 'validate-number', 'label' => Mage::helper ( 'rewards' )->__ ( 'Font Color (numeric)' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Font Color (numeric)' ) ) );
		$fieldset->addField ( 'text_offset_x', 'text', array ('name' => 'text_offset_x', 'class' => 'validate-number', 'label' => Mage::helper ( 'rewards' )->__ ( 'Font Left Offset' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Font Left Offset' ) ) );
		$fieldset->addField ( 'text_offset_y', 'text', array ('name' => 'text_offset_y', 'class' => 'validate-number', 'label' => Mage::helper ( 'rewards' )->__ ( 'Font Top Offset' ), 'title' => Mage::helper ( 'rewards' )->__ ( 'Font Top Offset' ) ) );
		
		$form->setValues ( $formData );
		Mage::getSingleton ( 'adminhtml/session' )->getCurrencyData ( null );
		
		return parent::_prepareForm ();
	}

}