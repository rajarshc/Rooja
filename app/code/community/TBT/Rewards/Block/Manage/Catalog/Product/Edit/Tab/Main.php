<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Manage Transfer Edit Tab Grid Transfers
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Catalog_Product_Edit_Tab_Main extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	
	public function __construct() {
		parent::__construct ();
	
		//$this->setTemplate("rewards/catalog/product/edit/tab/main.phtml");
	}
	
	public function getPredictedPointsEarned() {
		$p = $this->getproduct ();
		if (! ($p instanceof TBT_Rewards_Model_Catalog_Product)) {
			$p = Mage::getModel ( 'rewards/catalog_product' )->load ( $p->getId () );
		}
		
		if ($p) {
			$earnable = $p->getEarnablePoints ();
		} else {
			$earnable = array ();
		}
		return $earnable;
	}
	
	/**
	 * Retrive product object from object if not from registry
	 *
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct() {
		//    	if(!($_product instanceof TBT_Rewards_Model_Catalog_Product)) {
		//   			$_product = Mage::getModel('rewards/catalog_product')->load($_product->getId());
		//    	}
		

		if (! ($this->getData ( 'product' ) instanceof Mage_Catalog_Model_Product)) {
			$this->setData ( 'product', Mage::registry ( 'product' ) );
		}
		//        die(Mage::registry('current_product'));
		//    	die(print_r($this->getData(),true));
		

		if (Mage::registry ( 'product' )) {
			return Mage::registry ( 'product' );
		} else if (Mage::registry ( 'current_product' )) {
			return Mage::registry ( 'current_product' );
		}
		
		return $this->getData ( 'product' );
	}
	
	/**
	 * ######################## TAB settings #################################
	 */
	public function getTabLabel() {
		return $this->__ ( "Points & Rewards" );
	}
	
	public function getTabTitle() {
		return $this->__ ( "Points & Rewards" );
	}
	
	public function canShowTab() {
		if (! Mage::helper ( 'rewards/loyalty_checker' )->isValid ()) {
			return false;
		}
		if (! $this->getProduct ()) {
			return false;
		}
		return Mage::helper ( 'rewards/config' )->showProductEditPointsTab ();
	}
	
	public function isHidden() {
		return false;
	}

}

?>