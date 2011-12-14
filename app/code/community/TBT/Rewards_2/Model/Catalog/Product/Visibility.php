<?php

/**
 * This class is a decorator class for the select statement that comes from the 
 * TBT_Rewards_Model_Mysql4_CatalogRule_Rule class for selecting the currently
 * applied/validated catalog rules.
 *
 */
class TBT_Rewards_Model_Catalog_Product_Visibility extends Mage_Catalog_Model_Product_Visibility {
	
	public function __construct() {
		parent::__construct ();
		
		$eav_name = Mage::getModel ( 'catalog/resource_eav_attribute' )->loadByCode ( 'catalog_product', 'visibility' );
		$this->setAttribute ( $eav_name );
	}
	
	/**
	 *
	 * @param Zend_Db_Select $select
	 * @param int $store_id
	 * @return Zend_Db_Adapter_Mysqli
	 */
	public function addVisibileFilterToCR(&$select, $store_id = 0) {
		if ($this->getAttribute ()->isScopeGlobal ()) {
			$tableName = $this->getAttribute ()->getAttributeCode () . '_t';
			$select->joinLeft ( array ($tableName => $this->getAttribute ()->getBackend ()->getTable () ), "`p`.`product_id`=`{$tableName}`.`entity_id`" . " AND `{$tableName}`.`attribute_id`='{$this->getAttribute()->getId()}'" . " AND `{$tableName}`.`store_id`='0'", array () );
			$valueExpr = $tableName . '.value';
		} else {
			$valueTable1 = $this->getAttribute ()->getAttributeCode () . '_t1';
			$valueTable2 = $this->getAttribute ()->getAttributeCode () . '_t2';
			$select->joinLeft ( array ($valueTable1 => $this->getAttribute ()->getBackend ()->getTable () ), "`p`.`product_id`=`{$valueTable1}`.`entity_id`" . " AND `{$valueTable1}`.`attribute_id`='{$this->getAttribute()->getId()}'" . " AND `{$valueTable1}`.`store_id`='0'", array () )->joinLeft ( array ($valueTable2 => $this->getAttribute ()->getBackend ()->getTable () ), "`p`.`product_id`=`{$valueTable2}`.`entity_id`" . " AND `{$valueTable2}`.`attribute_id`='{$this->getAttribute()->getId()}'" . " AND `{$valueTable2}`.`store_id`='{$store_id}'", array () );
			$valueExpr = new Zend_Db_Expr ( "IF(`{$valueTable2}`.`value_id`>0, `{$valueTable2}`.`value`, `{$valueTable1}`.`value`)" );
		}
		$select->where ( $valueExpr . " IN (?)", $this->getVisibleInSiteIds () );
		return $this;
	}

}

?>