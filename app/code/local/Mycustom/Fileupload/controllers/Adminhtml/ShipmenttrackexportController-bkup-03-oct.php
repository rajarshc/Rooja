<?php 
class Mycustom_Fileupload_Adminhtml_ShipmenttrackexportController extends Mage_Adminhtml_Controller_Action
{
 
	public function indexAction() 
	{
		$this->loadLayout();     
		$this->renderLayout();
		//echo "Content top be shown here";
		//$conn = Mage::getSingleton('core/resource')->getConnection('core_read');
		// Prints a list of all website names
		//$results = $conn->fetchAll("SELECT order.increment_id, address.street, address.city, address.region, address.region, address.region, track.track_number FROM sales_flat_order AS order JOIN sales_flat_order_address AS address ON address.parent_id = order.entity_id JOIN sales_flat_shipment_track AS track ON track.order_id = order.entity_id WHERE address.address_type = 'shipping'");
		

	}
	
	
}