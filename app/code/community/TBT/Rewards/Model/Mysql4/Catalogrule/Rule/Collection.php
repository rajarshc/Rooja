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
 * @package    Mage_CatalogRule
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TBT_Rewards_Model_Mysql4_CatalogRule_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
	protected function _construct() {
		$this->_init ( 'rewards/catalogrule_rule' );
	}
	
	protected function _afterLoad() {
		$this->walk ( 'afterLoad' );
	}
	
	public function _initSelect() {
		//die("<PRE>".$this->getSelect()->__toString(). "</PRE>");
		parent::_initSelect ();
		$this->getSelect ()->distinct ( true )->joinInner ( array ('product_table' => $this->getTable ( 'catalogrule/rule_product' ) ), 'main_table.rule_id = product_table.rule_id', array () )//                'rule_id' => 'main_table.rule_id',
//                'description' => "main_table.description",
		//                'points_amount' =>   "main_table.points_amount",
		//                'product_id' =>  "product_table.product_id"
		->where ( 'from_time=0 or from_time<=? or to_time=0 or to_time>=?', now () )->group ( 'main_table.rule_id' );
	
		//die("<PRE>".$this->getSelect()->__toString()."</PRE>");
	}
	
	public function filterByProduct($productId) {
		if ($productId) {
			$this->getSelect ()->where ( 'product_table.product_id=?', $productId );
		}
	}
	
	public function getDistriRules() {
		$this->getSelect ()->where ( 'main_table.points_action LIKE \'%give%\'' );
	}
	
	//TODO: 3 functions, one to add a where clause for product_id, one for
	//        points_action like %give%, one for points_action like %deduct% or %discount%
	

	public function getRedemRules() {
		$this->getSelect ()->where ( 'main_table.points_action LIKE \'%deduct%\' ' . 'or main_table.points_action LIKE \'%discount%\'' );
	}
	
	public function getSelectCountSql() {
		$this->_renderFilters ();
		
		$countSelect = clone $this->getSelect ();
		$countSelect->reset ();
		//        $countSelect->reset(Zend_Db_Select::DISTINCT);
		//        $countSelect->reset(Zend_Db_Select::COLUMNS);
		//        $countSelect->reset(Zend_Db_Select::INNER_JOIN);
		//        $countSelect->reset(Zend_Db_Select::WHERE);
		//        $countSelect->reset(Zend_Db_Select::GROUP);
		

		$countSelect->from ( $this->getSelect (), 'COUNT(*)' );
		//$this->getResource()->getMainTable()
		//$countSelect->where($this->getResource()->getMainTable().'.rule_id IN ?', $this->getSelect());
		

		return $countSelect;
	}

}