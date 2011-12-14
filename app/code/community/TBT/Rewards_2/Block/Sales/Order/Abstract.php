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
 * Product View Points
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
abstract class TBT_Rewards_Block_Sales_Order_Abstract extends Mage_Core_Block_Template {
	
	/**
	 *
	 */
	protected $_source;
	protected $_order;
	
	/**
	 * Fetches the order model
	 *
	 * @return TBT_Rewards_Model_Sales_Order
	 */
	public function getOrder() {
		//@nelkaake -c 14/12/10:        
		$order = null;
		$parent = $this->getParentBlock ();
		
		if ($parent) {
			$order = $parent->getOrder ();
		} elseif ($this->getData ( 'order' )) {
			$order = $this->getData ( 'order' );
		} else {
			return null;
		}
		
		if (! $order) {
			$order = Mage::registry ( 'current_order' );
		}
		
		if (! ($order instanceof TBT_Rewards_Model_Sales_Order)) {
			$order = TBT_Rewards_Model_Sales_Order::wrap ( $order );
		}
		return $order;
	}

	/**
	 * Check if we nedd display full tax total info
	 *
	 * @return bool
	 */
	public function displayFullSummary() {
		return $this->_config->displaySalesFullSummary ( $this->getOrder ()->getStore () );
	}
	
	/**
	 * Get data (totals) source model
	 *
	 * @return Varien_Object
	 */
	public function getSource() {
		return $this->_source;
	}
	
	/**
	 * Get order store object
	 *
	 * @return Mage_Core_Model_Store
	 */
	public function getStore() {
		return $this->_order->getStore ();
	}
	
	/**
	 * Initialize all order totals relates with tax
	 *
	 * @nelkaake Added on Thursday August 19, 2010:      
	 * @return Mage_Tax_Block_Sales_Order_Tax
	 */
	public function initTotals() {
		$parent = $this->getParentBlock ();
		$this->_order = $parent->getOrder ();
		$this->_source = $parent->getSource ();
		$parent->addTotal ( new Varien_Object ( array ('code' => $this->getTotalsCode(), 'block_name' => $this->getTotalsBlockName() ) ) );
		return $this;
	}

	public abstract function getTotalsBlockName();
	public abstract function getTotalsCode();
}