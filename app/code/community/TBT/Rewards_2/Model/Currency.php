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
 * Currency
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Currency extends Mage_Core_Model_Abstract implements TBT_Rewards_Model_Migration_Importable {
	
	private $currency_captions = null;
	
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'rewards/currency' );
	}
	
	/**
	 * Returns a map of currency_id=>caption.  
	 * @see getAvailCurrencyIds() to get a simple list of all available currency ids.
	 * TODO: filter these currency ids for only this store.
	 * 
	 * @return array
	 */
	public function getAvailCurrencies() {
		$currencies = Mage::getSingleton ( 'rewards/currency' )->getCollection ();
		
		$currency_list = array ();
		foreach ( $currencies as $currency_id => $currency ) {
			$currency_list [$currency_id] = $currency->getCaption ();
		}
		
		return $currency_list;
	}
	
	/**
	 * Fetches an array of all available currency ids
	 * TODO: filter these currency ids for only this store.
	 *
	 * @return array
	 */
	public function getAvailCurrencyIds() {
		return array_keys ( $this->getAvailCurrencies () );
	}
	
	/**
	 * Fetches the caption for a currency given.
	 *
	 * @param int $currency_id
	 * @return string
	 */
	public function getCurrencyCaption($currency_id) {
		if ($this->currency_captions == null) {
			$this->currency_captions = $this->getAvailCurrencies ();
		}
		if (! isset ( $this->currency_captions [$currency_id] )) {
			throw new Exception ( "Either the current user cannot use the currency with id={$currency_id}, or the currency no longer exists." );
		}
		$cap = $this->currency_captions [$currency_id];
		return $cap;
	}
	
	public function _afterSave() {
		// Order was not changed, so it must be new or existing.
		/* I don't think this is actually used ... ? */
		if ($this->getOrderId ()) {
			$ref = Mage::getModel ( 'rewards/transfer_reference' )->loadByTransferAndReference ( $this->getId (), $this->getOrderId () );
			$o->setData ( $this->getData () );
		}
	}
	
	public function _beforeDelete() {
		throw new Exception ( "Deleted currencies is not supported yet because of 
                complications it makes ot all other related transfers." );
		return parent::_beforeSave ();
	}
	
	public function _afterDelete() {
		// TODO: Change all rules that are associated with giving points in this currency
		// 		 to instead give points of no qty
		//		 Next, send a warning to the administrator????
		return parent::_afterDelete ();
	}
	
	/**
	 * Forcefully Save object data even if ID does not exist
	 * Used for migrating data and ST campaigns.     
	 *
	 * @return Mage_Core_Model_Abstract
	 */
	public function saveWithId() {
		$real_id = $this->getId ();
		$exists = Mage::getModel ( $this->_resourceName )->setId ( null )->load ( $real_id )->getId ();
		
		if (! $exists) {
			$this->setId ( null );
		}
		
		$this->save ();
		
		if (! $exists) {
			$write = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
			$write->update ( $this->_getResource ()->getMainTable (), array ($this->_getResource ()->getIdFieldName () => $real_id ), array ("`{$this->_getResource()->getIdFieldName()}` = {$this->getId()}" ) );
			$write->commit ();
		}
		
		return $this;
	}

}