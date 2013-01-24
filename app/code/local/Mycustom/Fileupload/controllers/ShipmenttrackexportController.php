<?php 
class Mycustom_Fileupload_ShipmenttrackexportController extends Mage_Core_Controller_Front_Action
{
 
	public function indexAction() 
	{
		//$this->loadLayout();     
		//$this->renderLayout();
		//echo "Content top be shown here";
	
		
		
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');

        $sql = "SELECT order.increment_id, sales_flat_order_address.street, sales_flat_order_address.city, sales_flat_order_address.region, sales_flat_order_address.region, sales_flat_order_address.region, sales_flat_shipment_track.number FROM sales_flat_order AS `order` INNER JOIN (sales_flat_order_address, sales_flat_shipment_track) ON (sales_flat_order_address.parent_id = order.entity_id AND sales_flat_shipment_track.order_id = order.entity_id) WHERE sales_flat_order_address.address_type = 'shipping'";
	   
	  // $sql = "select * from fileupload where pincode='636116'";
   		$rowsArray = $connection->fetchAll($sql);
    	
		
		print_r($rowsArray);

	}
	
	
}