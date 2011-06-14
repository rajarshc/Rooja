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
 * @category    Mage
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customers Report collection
 * @nelkaake Added on Sunday August 15, 2010:  
 *
 * @category   Mage
 * @package    Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class TBT_Rewards_Model_Mysql4_Reports_Customer_Collection extends Mage_Reports_Model_Mysql4_Customer_Collection {
	
	//@nelkaake Added on Sunday August 15, 2010: 
	public function addPointsBalance() {
		$write = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
		$pb_table = Mage::getConfig ()->getTablePrefix () . "tmp_simple_points_balance";
		$write->query ( "
            CREATE TEMPORARY TABLE IF NOT EXISTS {$pb_table} AS
            SELECT ce.entity_id as customer_id, IFNULL(SUM(rt.quantity), 0) points_balance        
            FROM customer_entity ce
            Left JOIN rewards_transfer rt ON (
              rt.customer_id = ce.entity_id AND
              (rt.status = 5 OR (rt.status = 4 AND rt.quantity < 0))
            )
            GROUP BY ce.entity_id
            ORDER BY points_balance DESC
        " );
		
		$this->getSelect ()->joinLeft ( array ('pb' => $pb_table ), "`pb`.customer_id = `e`.entity_id", '`pb`.points_balance' );
		
		//die($this->getSelect());                               
		return $this;
	}
	
	/**
	 * Set sorting order
	 *
	 * $attribute can also be an array of attributes        
	 *
	 * @param string|array $attribute
	 * @param string $dir
	 * @return Mage_Eav_Model_Entity_Collection_Abstract
	 */
	public function setOrder($attribute, $dir = 'desc') {
		if ($attribute === "points_balance") {
			$this->getSelect ()->order ( "pb.points_balance {$dir}" );
		} else {
			parent::setOrder ( $attribute, $dir );
		}
		return $this;
	}

}
