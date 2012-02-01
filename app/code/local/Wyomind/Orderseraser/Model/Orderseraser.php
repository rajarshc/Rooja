<?php
class Wyomind_Orderseraser_Model_Orderseraser extends Varien_Object{
	
	public function _erase($orderId){		
		$resource = Mage::getSingleton('core/resource');		$delete= $resource->getConnection('core_read');		$tableSo = $resource->getTableName('sales_order');		$tableSoe = $resource->getTableName('sales_order_entity');		$tableSoei = $resource->getTableName('sales_order_entity_int');		$tableEa = $resource->getTableName('eav_attribute');		$tableSfoi = $resource->getTableName('sales_flat_order_item');				$sql= "DELETE FROM ".$tableSo." WHERE entity_id = ".$orderId.";";		$delete->query($sql);	
		$sql="DELETE FROM  ".$tableSoe." WHERE parent_id = ".$orderId.";";
		$delete->query($sql);
		$sql="DELETE s FROM  ".$tableSoe." s
				 JOIN  ".$tableSoei." si on s.entity_id = si.entity_id
				 JOIN  ".$tableEa." a on si.attribute_id = a.attribute_id
				 WHERE a.attribute_code = 'order_id'
				 AND si.value = ".$orderId.";";
		$delete->query($sql);		$sql="DELETE FROM  ".$tableSfoi." WHERE order_id=".$orderId.";";		$delete->query($sql);				
		return true;
	}
}
	