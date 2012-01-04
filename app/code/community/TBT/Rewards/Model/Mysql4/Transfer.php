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
 * Mysql Transfer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Mysql4_Transfer extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {
		// Note that the rewards_transfer_id refers to the key field in your database table.
		$this->_init ( 'rewards/transfer', 'rewards_transfer_id' );
	}
	
	/**
	 * Dispatch events after the points are transferred
	 *
	 * @param Mage_Core_Model_Abstract $object
	 * @return TBT_Rewards_Model_Mysql4_Transfer
	 */
	protected function _afterSave(Mage_Core_Model_Abstract $object) {
		return parent::_afterSave ( $object );
	}
	
	/**
	 *
	 * @param Mage_Core_Model_Abstract $object
	 */
	protected function _afterLoad(Mage_Core_Model_Abstract $object) {
		
		$select = $this->_getReadAdapter ()->select ()->from ( $this->getTable ( 'transfer_reference' ) )->where ( 'rewards_transfer_id = ?', $object->getId () );
		
		if ($data = $this->_getReadAdapter ()->fetchAll ( $select )) {
			$referencesArray = array ();
			foreach ( $data as $row ) {
				$referencesArray [] = $row ['reference_id'];
			}
			$object->setData ( 'reference_id', $referencesArray );
		}
		
		return parent::_afterLoad ( $object );
	}
	/**
	 *
	 * @param Mage_Core_Model_Abstract $object
	 */
	protected function _beforeLoad(Mage_Core_Model_Abstract $object) {
		$this->getSelect ()->join ( array ('cps' => 'customer_entity' ), $this->getMainTable () . '.customer_id = cps.entity_id' );
		$this->getSelect ()->join ( array ('cpsv' => 'customer_entity_varchar' ), 'cps.entity_id = cpsv.entity_id AND cpsv.attribute_id = 1' );
		// Append the associated orders for this transfer
		

		return parent::_beforeLoad ( $object );
	}
	
	/**
	 * Retrieve select object for load object data
	 *
	 * @param string $field
	 * @param mixed $value
	 * @return Zend_Db_Select
	 */
	protected function _getLoadSelect($field, $value, $object) {
		
		$select = parent::_getLoadSelect ( $field, $value, $object );
		return $select;
	}

}