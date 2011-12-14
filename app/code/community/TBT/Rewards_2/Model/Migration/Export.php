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
 * Sales Rule Rule
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Migration_Export extends Varien_Object {
	const DATA_CATALOGRULE_RULE = 'catalogrule_rule';
	const DATA_SALESRULE_RULE = 'salesrule_rule';
	const DATA_SPECIAL_RULE = 'special_rule';
	const DATA_CURRENCY = 'currency';
	const DATA_CONFIG = 'config';
	const EXT = 'stcampaign';
	
	public function getSerializedCampaignExport() {
		$output = array ();
		$output [self::DATA_CATALOGRULE_RULE] = $this->getAllCatalogruleRuleData ();
		$output [self::DATA_SALESRULE_RULE] = $this->getAllSalesruleRuleData ();
		$output [self::DATA_SPECIAL_RULE] = $this->getSpecialRuleData ();
		$output [self::DATA_CURRENCY] = $this->getCurrencyData ();
		$soutput = serialize ( $output );
		return $soutput;
	}
	
	public function getSerializedConfigExport() {
		$output = array ();
		$output [self::DATA_CONFIG] = $this->getRewardsConfigData ();
		$soutput = serialize ( $output );
		return $soutput;
	}
	
	public function getSerializedFullExport() {
		$output = array ();
		$output [self::DATA_CATALOGRULE_RULE] = $this->getAllCatalogruleRuleData ();
		$output [self::DATA_SALESRULE_RULE] = $this->getAllSalesruleRuleData ();
		$output [self::DATA_SPECIAL_RULE] = $this->getSpecialRuleData ();
		$output [self::DATA_CURRENCY] = $this->getCurrencyData ();
		$output [self::DATA_CONFIG] = $this->getRewardsConfigData ();
		$soutput = serialize ( $output );
		return $soutput;
	}
	
	public function getAllCatalogruleRuleData() {
		$crs = Mage::getModel ( 'catalogrule/rule' )->getCollection ()->addFieldToFilter ( "points_action", array ('neq' => '' ) );
		return $this->_getCleanArray ( $crs );
	}
	
	public function getAllSalesruleRuleData() {
		$srs = Mage::getModel ( 'salesrule/rule' )->getCollection ()->addFieldToFilter ( "points_action", array ('neq' => '' ) );
		return $this->_getCleanArray ( $srs );
	}
	
	public function getRewardsConfigData() {
		return $this->getConfigData ( 'rewards' );
	}
	
	public function getConfigData($modulekey) {
		//$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$config_table = Mage::getConfig ()->getTablePrefix () . "core_config_data";
		$read = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_read' );
		$select = $read->select ()->from ( $config_table )->where ( 'path LIKE ?', $modulekey . '%' );
		$collection = $read->fetchAll ( $select );
		return $collection;
	}
	
	public function getSpecialRuleData() {
		$srs = Mage::getModel ( 'rewards/special' )->getCollection ();
		return $this->_getCleanArray ( $srs );
	}
	
	public function getCurrencyData() {
		$srs = Mage::getModel ( 'rewards/currency' )->getCollection ();
		return $this->_getCleanArray ( $srs );
	}
	
	protected function _getCleanArray($collection) {
		$output = array ();
		foreach ( $collection as &$sr ) {
			$output [] = $sr->getData ();
		}
		return $output;
	}

}