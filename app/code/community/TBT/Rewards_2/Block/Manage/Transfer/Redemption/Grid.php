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
 * Manage Transfer Redemptions Grid
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Transfer_Redemption_Grid extends TBT_Rewards_Block_Manage_Transfer_Grid {
	
	protected $collection = null;
	
	protected function _prepareCollection() {
		if ($this->collection == null) {
			$this->collection = Mage::getModel ( 'rewards/transfer' )->getCollection ();
		}
		
		$this->collection->selectOnlyRedemptions ();
		
		return parent::_prepareCollection ();
	}
	
	protected function _prepareColumns() {
		if ($this->columnsAreSet)
			return parent::_prepareColumns ();
		else
			$this->columnsAreSet = true;
		
		$this->addColumn ( 'rewards_transfer_id', array ('header' => Mage::helper ( 'rewards' )->__ ( 'ID' ), 'align' => 'right', 'width' => '36px', 'index' => 'rewards_transfer_id' ) );
		
		$this->addColumn ( 'creation_ts', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Created Time' ), 'width' => '40px', 'type' => 'datetime', 'index' => 'creation_ts' ) );
		
		$this->addColumn ( 'points', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Points' ), 'align' => 'left', 'width' => '70px', 'index' => 'points', 'filter_index' => "CONCAT(main_table.quantity, ' ', currency_table.caption)" ) );
		
		/*
          $this->addColumn('customer_id', array(
          'header'    => Mage::helper('rewards')->__('Customer ID'),
          'align'     =>'left',
          'width'     => '100px',
          'index'     => 'customer_id',
          ));

         */
		
		$this->addColumn ( 'fullcustomername', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Customer' ), 'align' => 'left', 'width' => '80px', 'index' => 'fullcustomername', 'filter_index' => "CONCAT(customer_firstname_table.value, ' ', customer_lastname_table.value)" ) );
		
		$reasons = Mage::getSingleton ( 'rewards/transfer_reason' )->getRedemptionReasons ();
		if (count ( $reasons ) > 1) {
			$this->addColumn ( 'reason', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Reason' ), 'align' => 'left', 'width' => '100px', 'index' => 'reason_id', 'type' => 'options', 'options' => $reasons ) );
		}
		
		$this->addColumn ( 'comments', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Comments/Notes' ), 'width' => '330px', 'index' => 'comments' ) );
		
		$statuses = Mage::getSingleton ( 'rewards/transfer_status' )->getOptionArray ();
		$this->addColumn ( 'status', array ('header' => Mage::helper ( 'rewards' )->__ ( 'Status' ), 'align' => 'left', 'width' => '80px', 'index' => 'status', 'type' => 'options', 'options' => $statuses ) );
		
		return parent::_prepareColumns ();
	}

}