<?php 
class Mycustom_Fileupload_Adminhtml_ShipmenttrackexportController extends Mage_Adminhtml_Controller_Action
{
 
	public function indexAction() 
	{
		$fileextension="csv";
		$filename="report";
		$downloadedfilename = "$filename.$fileextension";
		$ctype="application/force-download";
		//db connection 
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		//query to fetch the shipment tracking details
		$sql = "SELECT order.increment_id, address.street, address.city, address.region, address.region, address.region, track.number FROM sales_flat_order AS order JOIN sales_flat_order_address AS address ON address.parent_id = order.entity_id JOIN sales_flat_shipment_track AS track ON track.order_id = order.entity_id WHERE address.address_type = 'shipping'";
		$sql = "SELECT * FROM sales_flat_order";
		$orderArray = $connection->fetchAll($sql);
		
		

		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers 
		header("Content-Type: $ctype");
		// change, added quotes to allow spaces in filenames, by Rajkumar Singh
		header("Content-Disposition: filename=\"".basename($downloadedfilename)."\";" );
		header("Content-Transfer-Encoding: binary");
		// generate your CSV content here and print them to the browser via ECHO
		foreach($orderArray as $order)
		{
			//echo $order['entity_id']."<br/>";
			echo $order['created_at'].", ".$order['order_currency_code'].", ".$order['customer_email'].", ".$order['increment_id'].", ".$order['grand_total'].", ".$order['shipping_description'].", ".$order['status'].", ".$order['state']." \n";
		}
		exit;
		

	}
	
	
}