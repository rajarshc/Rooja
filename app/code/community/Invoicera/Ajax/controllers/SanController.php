<?php
class Invoicera_Ajax_SanController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		echo "Congratulations! Your ajax module is configured correctly.";
	}

	public function exportOrderAction()
	{	
		$varAdminPath = Mage_Core_Model_Store::ADMIN_CODE;
		$arrOrderID = $_POST['order_ids'];
		$varOrderCount = count($arrOrderID);		
		if($varOrderCount > 0 && $varOrderCount < 21)
		{
			for($i=0; $i<$varOrderCount; $i++)
			{
				if($arrOrderID[$i] != '')
				{
					$this->createInvoiceFromOrderToInvoicera($arrOrderID[$i]);
				}
			}
			$varURL = str_replace('ajax', $varAdminPath, Mage::helper("adminhtml")->getUrl("*/sales_order"));
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Selected order(s) exported successfully.'));
			header('location:'.$varURL);
			die;
		}
		else
		{
			
			$varURL = str_replace('ajax', $varAdminPath, Mage::helper("adminhtml")->getUrl("*/sales_order"));
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('You cannot export more than 20 orders in a single request.'));
            header('location:'.$varURL);
			die;
		}
		
	}
	
	public function createInvoiceFromOrderToInvoicera($varOrderID)
	{
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');	
	
		$varCurrenyCode =  Mage::app()->getStore()->getCurrentCurrency()->getCode();
		// GETTING ORDER STATUS
		$resultOne = $db->query("SELECT entity_id, status, customer_email, base_currency_code, shipping_description, shipping_amount, increment_id FROM sales_flat_order WHERE entity_id=".$varOrderID);
		$rowOne = $resultOne->fetch(PDO::FETCH_ASSOC);
		
		
		if($rowOne['status'] == 'processing' || $rowOne['status'] == 'complete')
		{
			$varStatus = 'Paid';
		}
		else
		{
			$varStatus = 'Sent';
		}
			
		$result = $db->query("SELECT item_id, product_type, product_options, order_id, sku, name, description, qty_ordered, base_price, tax_percent, tax_amount, base_discount_amount FROM sales_flat_order_item WHERE order_id=".$varOrderID);
		
		if(!$result) {
			return false;
		}
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$arrData[] = $row;
			}
		if(!$arrData) {
			return false;
		}
		$comment = '';
		//$comment = $data['comment_text'];
		// getting po_number
		$random_number = rand(0, pow(10, 7));
		// GETTING INVOICE PREFIX
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');				
		$varPath = 'invoice_options/invoice/invoice_prefix';
		$resultTwo = $db->query("SELECT value FROM core_config_data WHERE path LIKE '".$varPath."'");
		$rowTwo = $resultTwo->fetch(PDO::FETCH_ASSOC);
		
		$varInvoicePrefix = $rowTwo['value'];
		
		$invoiceListXml = '<?xml version="1.0" encoding="utf-8"?>
					<request method="listInvoice">
						<filter>
							<invoice_number>'.$varInvoicePrefix.$rowOne['increment_id'].'</invoice_number>
							<page>1</page>
							<per_page_record>1</per_page_record>
						</filter>
					</request>';
		$curlInvoiceListResult = $this->sendCurlRequest($invoiceListXml);
		$xml = stripslashes($curlInvoiceListResult);
		$objXml = new SimpleXMLElement($xml); 
		
		$arrInvoiceListParamList = $this->objectsIntoArray($objXml);

		if($arrInvoiceListParamList['@attributes']['status'] == '400')
		{		
			$clientListXml = '<?xml version="1.0" encoding="utf-8"?>
					<request method="listClient">
						<filter>
							<client_email>'.$rowOne['customer_email'].'</client_email>
							<page>1</page>
							<per_page_record>10</per_page_record>
						</filter>
					</request>';
			$curlClientListResult = $this->sendCurlRequest($clientListXml);
			$xml = stripslashes($curlClientListResult);
			$objXml = new SimpleXMLElement($xml);                
			$arrParamList = $this->objectsIntoArray($objXml);

			$varClientID = 0;
			if($arrParamList['@attributes']['status'] == '403')
			{
				$db = Mage::getSingleton('core/resource')->getConnection('core_write');	
				$resultAdminData = $db->query("SELECT concat_ws(' ', firstname, lastname) as Name, email FROM admin_user WHERE user_id = 1");
				$rowAdmindata = $resultAdminData->fetch(PDO::FETCH_ASSOC);
				$varMessage = "
Dear ".$rowAdmindata['Name']."
			
Invoicera API authentication failed. Please check your invoicera configuration.
				
Regards
Invoicera Team";
				$this->notify_to_admin($rowAdmindata['Name'], $rowAdmindata['email'], $varMessage);
			}
			if($arrParamList['@attributes']['status'] == '200' && $arrParamList['clients']['@attributes']['total'] == 1)
			{
				$varClientID = $arrParamList['clients']['client']['client_id']; 
				$varClientAddress = $arrParamList['clients']['client']['address']; 
			}
			else
			{
				// GETTING CLIENT DETAILS
				$resultThree = $db->query("SELECT firstname, country.iso3_code as CountryCode, lastname, company, email, telephone, concat_ws(',', street, city, region, postcode) as ClientAddress FROM sales_flat_order_address as sfoa LEFT JOIN directory_country as country ON sfoa.country_id = country.country_id WHERE parent_id='".$varOrderID."' AND address_type = 'billing'");
				$rowThree = $resultThree->fetch(PDO::FETCH_ASSOC);

				$clientCreateXml = '<?xml version="1.0" encoding="utf-8"?>
								<request method="createClient">
									<name>'.$rowThree['firstname'].' '.$rowThree['lastname'].'</name>
									<organization>'.$rowThree['firstname'].' '.$rowThree['lastname'].'</organization>
									<email>'.$rowOne['customer_email'].'</email>       	
									<work_phone>'.$rowThree['telephone'].'</work_phone> 	
									<address>'.$rowThree['ClientAddress'].'</address>  
									<currency>'.$varCurrenyCode.'</currency>
									<country>'.$rowThree['CountryCode'].'</country>
								</request>';
							
				$curlClientResult = $this->sendCurlRequest($clientCreateXml);
				$xml = stripslashes($curlClientResult);
				$objXml = new SimpleXMLElement($xml);                
				$arrParamList = $this->objectsIntoArray($objXml);
				
				if($arrParamList['@attributes']['status'] == '403')
				{
					$db = Mage::getSingleton('core/resource')->getConnection('core_write');	
					$resultAdminData = $db->query("SELECT concat_ws(' ', firstname, lastname) as Name, email FROM admin_user WHERE user_id = 1");
					$rowAdmindata = $resultAdminData->fetch(PDO::FETCH_ASSOC);
					$varMessage = "
Dear ".$rowAdmindata['Name']."
					
Invoicera API authentication failed. Please check your invoicera configuration.
					
Regards
Invoicera Team";
					$this->notify_to_admin($rowAdmindata['Name'], $rowAdmindata['email'], $varMessage);
				}
				
				if($arrParamList['@attributes']['status'] == '426')
				{
					$db = Mage::getSingleton('core/resource')->getConnection('core_write');	
					$resultAdminData = $db->query("SELECT concat_ws(' ', firstname, lastname) as Name, email FROM admin_user WHERE user_id = 1");
					$rowAdmindata = $resultAdminData->fetch(PDO::FETCH_ASSOC);
					$varMessage = "
Dear ".$rowAdmindata['Name']."
					
This is to notify you that now your orders can not be imported and saved as invoices into your Invoicera account as your Invoicera account has exceeded the client limit. Due to which, your customers who would make any order on your store now, would not be able to get an invoice from your Invoicera Account. To avoid this inconvenience, we would request you to upgrade your Invoicera account.

Please feel free to contact us in case you have any query.

Thanks & Regards,
Invoicera Team

http://www.invoicera.com/
You may also catch us over Twitter at http://www.twitter.com/invoicera";
					$this->notify_to_admin($rowAdmindata['Name'], $rowAdmindata['email'], $varMessage);
				}
				if($arrParamList['@attributes']['status'] == '200')
				{
					$varClientID = $arrParamList['client_id'];
					$varClientAddress = $rowThree['ClientAddress'];
				}
			}
			if($varClientID)
			{
			
				// creating XML for creating invoice
				$createInvoiceXML = '';
				$createInvoiceXML .= '<?xml version="1.0" encoding="utf-8"?>
					<request method="createInvoice">
					<client>
					<client_id>'.$varClientID.'</client_id>            		
						<address>'.$varClientAddress.'</address>     		
					</client>
					<invoice_title>Order #'.$rowOne['increment_id'].'</invoice_title>
					<number>'.$varInvoicePrefix.$rowOne['increment_id'].'</number>
					<date>'.trim(substr($arrData[0]['created_at'], 0, 10)).'</date>	
					<due_date></due_date>
					<schedule_date></schedule_date>
					<po_number>'.trim($random_number).'</po_number>
					<status>'.trim($varStatus).'</status>
					<currency_code>'.trim($varCurrenyCode).'</currency_code>
					<notes>'.$comment.'</notes>
					<terms></terms>
					<items>';
					for($i=0;$i<count($arrData);$i++)
					{  
						$arrItemOptions = unserialize($arrData[$i]['product_options']);
						$varDescription = '';

						if($arrItemOptions['bundle_options'])
						{
							foreach($arrItemOptions['bundle_options'] as $key=>$value)
							{
								$varDescription .= $value['label'].": ".$value['value'][0]['title']."\n";
							}
						}
						else
						if($arrItemOptions['options'])
						{
							for($k=0; $k <count($arrItemOptions['options']); $k++)
							{
								$varDescription .= $arrItemOptions['options'][$k]['label'].": ".$arrItemOptions['options'][$k]['print_value']."\n";
							}
						}
						else
						if($arrItemOptions['attributes_info'])
						{
							for($k=0; $k <count($arrItemOptions['attributes_info']); $k++)
							{
								$varDescription .= $arrItemOptions['attributes_info'][$k]['label'].": ".$arrItemOptions['attributes_info'][$k]['value']."\n";
							}
						}
						else
						{
							$varDescription = "[".$arrData[$i]['sku']."] ".trim($arrData[$i]['name']);
						}
						if($arrData[$i]['product_type'] == 'bundle' || $arrData[$i]['product_type'] == 'group')
						{
							$createInvoiceXML .='<item>
										<name>'.trim($arrData[$i]['name']).'</name>
										<type>Product</type>
										<description>'.$varDescription.'</description>	
										<unit_cost>'.trim(number_format($arrData[$i]['base_price'], 2, '.', '')).'</unit_cost>									
										<quantity>1.00</quantity>										
										<discount>0.00</discount>										
										<discount_type>Fixed</discount_type>';
										if($arrData[$i]['tax_percent'] > 0)
										{
											$createInvoiceXML .='
										<tax1_name>Tax-1</tax1_name>										
										<tax1_percent>'.trim(number_format($arrData[$i]['tax_percent'], 2, '.', '')).'</tax1_percent>';
										}
										$createInvoiceXML .='
								  </item>';
						}
						else
						{
							$createInvoiceXML .='<item>
										<name>'.trim($arrData[$i]['name']).'</name>
										<type>Product</type>
										<description>'.$varDescription.'</description>	
										<unit_cost>'.trim(number_format($arrData[$i]['base_price'], 2, '.', '')).'</unit_cost>									
										<quantity>'.trim(number_format($arrData[$i]['qty_ordered'], 2, '.', '')).'</quantity>										
										<discount>'.trim(number_format($arrData[$i]['base_discount_amount'], 2, '.', '')).'</discount>										
										<discount_type>Fixed</discount_type>';
										if($arrData[$i]['tax_percent'] > 0)
										{
										$createInvoiceXML .='
										<tax1_name>Tax-1</tax1_name>										
										<tax1_percent>'.trim(number_format($arrData[$i]['tax_percent'], 2, '.', '')).'</tax1_percent>';
										}
										$createInvoiceXML .='
								  </item>';
						}
					}
					$createInvoiceXML .= '</items>';
					if($rowOne['shipping_amount'] > 0)
					{
						$createInvoiceXML .= '
										<additional_charges>													
											<additional_charge>
												<name>'.trim($rowOne['shipping_description']).'</name>
												<type>Fixed</type>
												<amount>'.trim($rowOne['shipping_amount']).'</amount>
											  </additional_charge>
										</additional_charges>';
					}
				$createInvoiceXML .= '</request>';
				$curlInvoiveResult = $this->sendCurlRequest($createInvoiceXML);
				
				// GETTING SEND MAIL SETTING
				$db = Mage::getSingleton('core/resource')->getConnection('core_write');				
				$varPath = 'invoice_options/invoice/send_mail';
				$resultTwo = $db->query("SELECT value FROM core_config_data WHERE path LIKE '".$varPath."'");
				$rowTwo = $resultTwo->fetch(PDO::FETCH_ASSOC);
				$varSendMailFlag = $rowTwo['value'];
				
				if($varSendMailFlag)
				{				
					$xml = stripslashes($curlInvoiveResult);
					$objXml = new SimpleXMLElement($xml);                
					$arrParamList = $this->objectsIntoArray($objXml);
					
					if($arrParamList['@attributes']['status'] == '200')
					{
						$varInvoiceID = $arrParamList['invoice_id'];
						
						$varSendInvoiceXml = '<?xml version="1.0" encoding="utf-8"?>
						<request method="sendInvoiceMail">
							<invoice_id>'.$varInvoiceID.'</invoice_id>
						</request>';
						$curlInvoiceSendResult = $this->sendCurlRequest($varSendInvoiceXml);
						
					}
				}
			}
		}
	}
	public function exportInvoiceAction()
	{	
		$varAdminPath = Mage_Core_Model_Store::ADMIN_CODE;
		$arrInvoiceID = $_POST['invoice_ids'];
		$varInvoiceCount = count($arrInvoiceID);		
		if($varInvoiceCount > 0 && $varInvoiceCount < 21)
		{
			for($i=0; $i<$varInvoiceCount; $i++)
			{
				if($arrInvoiceID[$i] != '')
				{
					$this->createInvoiceForInvoicera($arrInvoiceID[$i]);
				}
			}
			$varURL = str_replace('ajax', $varAdminPath, Mage::helper("adminhtml")->getUrl("*/sales_invoice"));
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Selected invoce(s) exported successfully.'));
			header('location:'.$varURL);
			die;
		}
		else
		{
			
			$varURL = str_replace('ajax', $varAdminPath, Mage::helper("adminhtml")->getUrl("*/sales_invoice"));
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('You cannot export more than 20 invoices in a single request.'));
            header('location:'.$varURL);
			die;
		}
		
	}
	
	public function createInvoiceForInvoicera($varInvoiceID)
	{
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');	
	
		$varCurrenyCode =  Mage::app()->getStore()->getCurrentCurrency()->getCode();
		// GETTING ORDER STATUS
		$resultOne = $db->query("SELECT sfo.customer_email, sfo.base_currency_code, sfo.shipping_description, sfo.shipping_amount, sfo.increment_id FROM sales_flat_invoice as sfi INNER JOIN sales_flat_order AS sfo ON sfi.order_id = sfo.entity_id WHERE sfi.entity_id=".$varInvoiceID);
		$rowOne = $resultOne->fetch(PDO::FETCH_ASSOC);
		
		
		
		if($rowOne['status'] == 'processing' || $rowOne['status'] == 'complete')
		{
			$varStatus = 'Paid';
		}
		else
		{
			$varStatus = 'Sent';
		}
			
		$result = $db->query("SELECT sfoi.product_options, sfoi.product_type, sfii.sku, sfii.name, sfoi.description, sfii.qty, sfii.base_price, sfoi.tax_percent, sfoi.tax_amount, sfoi.base_discount_amount FROM sales_flat_invoice_item as sfii INNER JOIN sales_flat_order_item as sfoi ON sfii.order_item_id = sfoi.item_id WHERE sfii.parent_id=".$varInvoiceID." ");
		
		if(!$result) {
			return false;
		}
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$arrData[] = $row;
			}
		if(!$arrData) {
			return false;
		}
		$comment = '';
		//$comment = $data['comment_text'];
		// getting po_number
		$random_number = rand(0, pow(10, 7));
		// GETTING INVOICE PREFIX
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');				
		$varPath = 'invoice_options/invoice/invoice_prefix';
		$resultTwo = $db->query("SELECT value FROM core_config_data WHERE path LIKE '".$varPath."'");
		$rowTwo = $resultTwo->fetch(PDO::FETCH_ASSOC);
		
		$varInvoicePrefix = $rowTwo['value'];
		
		$invoiceListXml = '<?xml version="1.0" encoding="utf-8"?>
					<request method="listInvoice">
						<filter>
							<invoice_number>'.$varInvoicePrefix.$rowOne['increment_id'].'</invoice_number>
							<page>1</page>
							<per_page_record>1</per_page_record>
						</filter>
					</request>';
		$curlInvoiceListResult = $this->sendCurlRequest($invoiceListXml);
		$xml = stripslashes($curlInvoiceListResult);
		$objXml = new SimpleXMLElement($xml); 
		
		$arrInvoiceListParamList = $this->objectsIntoArray($objXml);

		if($arrInvoiceListParamList['@attributes']['status'] == '400')
		{		
			$clientListXml = '<?xml version="1.0" encoding="utf-8"?>
					<request method="listClient">
						<filter>
							<client_email>'.$rowOne['customer_email'].'</client_email>
							<page>1</page>
							<per_page_record>10</per_page_record>
						</filter>
					</request>';
			$curlClientListResult = $this->sendCurlRequest($clientListXml);
			$xml = stripslashes($curlClientListResult);
			$objXml = new SimpleXMLElement($xml);                
			$arrParamList = $this->objectsIntoArray($objXml);

			$varClientID = 0;
			if($arrParamList['@attributes']['status'] == '403')
			{
				$db = Mage::getSingleton('core/resource')->getConnection('core_write');	
				$resultAdminData = $db->query("SELECT concat_ws(' ', firstname, lastname) as Name, email FROM admin_user WHERE user_id = 1");
				$rowAdmindata = $resultAdminData->fetch(PDO::FETCH_ASSOC);
				$varMessage = "
Dear ".$rowAdmindata['Name']."
			
Invoicera API authentication failed. Please check your invoicera configuration.
				
Regards
Invoicera Team";
				$this->notify_to_admin($rowAdmindata['Name'], $rowAdmindata['email'], $varMessage);
			}
			if($arrParamList['@attributes']['status'] == '200' && $arrParamList['clients']['@attributes']['total'] == 1)
			{
				$varClientID = $arrParamList['clients']['client']['client_id']; 
			}
			else
			{
				// GETTING CLIENT DETAILS
				//$resultThree = $db->query("SELECT firstname, lastname, company, email, telephone, concat_ws(',', street, city, region, postcode) as ClientAddress FROM sales_flat_order_address WHERE email='".$rowOne['customer_email']."'");
				
				$resultThree = $db->query("SELECT firstname, country.iso3_code as CountryCode, lastname, company, email, telephone, concat_ws(',', street, city, region, postcode) as ClientAddress FROM sales_flat_order_address as sfoa INNER JOIN directory_country as country ON sfoa.country_id = country.country_id WHERE email='".$rowOne['customer_email']."'");
				$rowThree = $resultThree->fetch(PDO::FETCH_ASSOC);
				
				$clientCreateXml = '<?xml version="1.0" encoding="utf-8"?>
								<request method="createClient">
									<name>'.$rowThree['firstname'].' '.$rowThree['lastname'].'</name>
									<organization>'.$rowThree['firstname'].' '.$rowThree['lastname'].'</organization>
									<email>'.$rowOne['customer_email'].'</email>       	
									<work_phone>'.$rowThree['telephone'].'</work_phone> 	
									<address>'.$rowThree['ClientAddress'].'</address>  
									<currency>'.$varCurrenyCode.'</currency>
									<country>'.$rowThree['CountryCode'].'</country>
								</request>';
				$curlClientResult = $this->sendCurlRequest($clientCreateXml);
				$xml = stripslashes($curlClientResult);
				$objXml = new SimpleXMLElement($xml);                
				$arrParamList = $this->objectsIntoArray($objXml);
				
				if($arrParamList['@attributes']['status'] == '403')
				{
					$db = Mage::getSingleton('core/resource')->getConnection('core_write');	
					$resultAdminData = $db->query("SELECT concat_ws(' ', firstname, lastname) as Name, email FROM admin_user WHERE user_id = 1");
					$rowAdmindata = $resultAdminData->fetch(PDO::FETCH_ASSOC);
					$varMessage = "
Dear ".$rowAdmindata['Name']."
					
Invoicera API authentication failed. Please check your invoicera configuration.
					
Regards
Invoicera Team";
					$this->notify_to_admin($rowAdmindata['Name'], $rowAdmindata['email'], $varMessage);
				}
				
				if($arrParamList['@attributes']['status'] == '426')
				{
					$db = Mage::getSingleton('core/resource')->getConnection('core_write');	
					$resultAdminData = $db->query("SELECT concat_ws(' ', firstname, lastname) as Name, email FROM admin_user WHERE user_id = 1");
					$rowAdmindata = $resultAdminData->fetch(PDO::FETCH_ASSOC);
					$varMessage = "
Dear ".$rowAdmindata['Name']."
					
This is to notify you that now your orders can not be imported and saved as invoices into your Invoicera account as your Invoicera account has exceeded the client limit. Due to which, your customers who would make any order on your store now, would not be able to get an invoice from your Invoicera Account. To avoid this inconvenience, we would request you to upgrade your Invoicera account.

Please feel free to contact us in case you have any query.

Thanks & Regards,
Invoicera Team

http://www.invoicera.com/
You may also catch us over Twitter at http://www.twitter.com/invoicera";
					$this->notify_to_admin($rowAdmindata['Name'], $rowAdmindata['email'], $varMessage);
				}
				if($arrParamList['@attributes']['status'] == '200')
				{
					$varClientID = $arrParamList['client_id'];
				}
			}
			if($varClientID)
			{
			
				// creating XML for creating invoice
				$createInvoiceXML = '';
				$createInvoiceXML .= '<?xml version="1.0" encoding="utf-8"?>
					<request method="createInvoice">
					<client>
					<client_id>'.$varClientID.'</client_id>            		
						<address>'.$rowThree['ClientAddress'].'</address>     		
					</client>
					<invoice_title>Order #'.$rowOne['increment_id'].'</invoice_title>
					<number>'.$varInvoicePrefix.$rowOne['increment_id'].'</number>
					<date>'.trim(substr($arrData[0]['created_at'], 0, 10)).'</date>	
					<due_date></due_date>
					<schedule_date></schedule_date>
					<po_number>'.trim($random_number).'</po_number>
					<status>'.trim($varStatus).'</status>
					<currency_code>'.trim($varCurrenyCode).'</currency_code>
					<notes>'.$comment.'</notes>
					<terms></terms>
					<items>';
					for($i=0;$i<count($arrData);$i++)
					{  
						$arrItemOptions = unserialize($arrData[$i]['product_options']);
						$varDescription = '';

						if($arrItemOptions['bundle_options'])
						{
							foreach($arrItemOptions['bundle_options'] as $key=>$value)
							{
								$varDescription .= $value['label'].": ".$value['value'][0]['title']."\n";
							}
						}
						else
						if($arrItemOptions['options'])
						{
							for($k=0; $k <count($arrItemOptions['options']); $k++)
							{
								$varDescription .= $arrItemOptions['options'][$k]['label'].": ".$arrItemOptions['options'][$k]['print_value']."\n";
							}
						}
						else
						if($arrItemOptions['attributes_info'])
						{
							for($k=0; $k <count($arrItemOptions['attributes_info']); $k++)
							{
								$varDescription .= $arrItemOptions['attributes_info'][$k]['label'].": ".$arrItemOptions['attributes_info'][$k]['value']."\n";
							}
						}
						else
						{
							$varDescription = "[".$arrData[$i]['sku']."] ".trim($arrData[$i]['name']);
						}
						if($arrData[$i]['product_type'] == 'bundle')
						{
							$createInvoiceXML .='<item>
										<name>'.trim($arrData[$i]['name']).'</name>
										<type>Product</type>
										<description>'.$varDescription.'</description>	
										<unit_cost>0.00</unit_cost>									
										<quantity>0.00</quantity>										
										<discount>0.00</discount>										
										<discount_type>Fixed</discount_type>';
										if($arrData[$i]['tax_percent'] > 0)
										{
											$createInvoiceXML .='
										<tax1_name>Tax-1</tax1_name>										
										<tax1_percent>'.trim(number_format($arrData[$i]['tax_percent'], 2, '.', '')).'</tax1_percent>';
										}
										$createInvoiceXML .='
								  </item>';
						}
						else
						{
							$createInvoiceXML .='<item>
										<name>'.trim($arrData[$i]['name']).'</name>
										<type>Product</type>
										<description>'.$varDescription.'</description>	
										<unit_cost>'.trim(number_format($arrData[$i]['base_price'], 2, '.', '')).'</unit_cost>									
										<quantity>'.trim(number_format($arrData[$i]['qty'], 2, '.', '')).'</quantity>										
										<discount>'.trim(number_format($arrData[$i]['base_discount_amount'], 2, '.', '')).'</discount>										
										<discount_type>Fixed</discount_type>';
										if($arrData[$i]['tax_percent'] > 0)
										{
										$createInvoiceXML .='
										<tax1_name>Tax-1</tax1_name>										
										<tax1_percent>'.trim(number_format($arrData[$i]['tax_percent'], 2, '.', '')).'</tax1_percent>';
										}
										$createInvoiceXML .='
								  </item>';
						}
					}
					$createInvoiceXML .= '</items>';
					if($rowOne['shipping_amount'] > 0)
					{
						$createInvoiceXML .= '
										<additional_charges>													
											<additional_charge>
												<name>'.trim($rowOne['shipping_description']).'</name>
												<type>Fixed</type>
												<amount>'.trim($rowOne['shipping_amount']).'</amount>
											  </additional_charge>
										</additional_charges>';
					}
				$createInvoiceXML .= '</request>';
				$curlInvoiveResult = $this->sendCurlRequest($createInvoiceXML);
				
				// GETTING SEND MAIL SETTING
				$db = Mage::getSingleton('core/resource')->getConnection('core_write');				
				$varPath = 'invoice_options/invoice/send_mail';
				$resultTwo = $db->query("SELECT value FROM core_config_data WHERE path LIKE '".$varPath."'");
				$rowTwo = $resultTwo->fetch(PDO::FETCH_ASSOC);
				$varSendMailFlag = $rowTwo['value'];
				
				if($varSendMailFlag)
				{				
					$xml = stripslashes($curlInvoiveResult);
					$objXml = new SimpleXMLElement($xml);                
					$arrParamList = $this->objectsIntoArray($objXml);
					
					if($arrParamList['@attributes']['status'] == '200')
					{
						$varInvoiceID = $arrParamList['invoice_id'];
						
						$varSendInvoiceXml = '<?xml version="1.0" encoding="utf-8"?>
						<request method="sendInvoiceMail">
							<invoice_id>'.$varInvoiceID.'</invoice_id>
						</request>';
						$curlInvoiceSendResult = $this->sendCurlRequest($varSendInvoiceXml);
						
					}
				}
			}
		}
	}
	public function sendCurlRequest($xml)
	{
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		// GETTING API TOKEN
		$varPath = 'invoice_options/invoice/api_token';
		$resultTwo = $db->query("SELECT value FROM core_config_data WHERE path LIKE '".$varPath."'");
		$rowTwo = $resultTwo->fetch(PDO::FETCH_ASSOC);
		$token = $rowTwo['value'];
		
		// GETTING API URL
		$varURLPath = 'invoice_options/invoice/api_url';
		$resultURL = $db->query("SELECT value FROM core_config_data WHERE path LIKE '".$varURLPath."'");
		$rowURL = $resultURL->fetch(PDO::FETCH_ASSOC);
		$apiURL = $rowURL['value'];
		
		$ch = curl_init($apiURL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_USERPWD, $token.':123');
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'xml_request='.$xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, "Invoicera API tester 1.0");
		$curlClientResult = curl_exec($ch);
		curl_close ($ch);
		return $curlClientResult;
	}
	
	public function objectsIntoArray($arrObjData, $arrSkipIndices = array())
	{
		$arrData = array();
	
		// if input is object, convert into array
		if (is_object($arrObjData)) {
			$arrObjData = get_object_vars($arrObjData);
		}
	
		if (is_array($arrObjData)) {
			foreach ($arrObjData as $index => $value) {
				if (is_object($value) || is_array($value)) {
					$value = $this->objectsIntoArray($value, $arrSkipIndices); // recursive call
				}
				if (in_array($index, $arrSkipIndices)) {
					continue;
				}
				$arrData[$index] = $value;
			}
		}
		return $arrData;
	}
	
	public function notify_to_admin($name, $email, $msg) 
	{
		$varSubject = 'Invoicera Notification';
				
		Mage::log($msg);
					
		$mail = Mage::getModel('core/email');
		$mail->setToName($name);
		$mail->setToEmail($email);
		$mail->setBody($msg);
		$mail->setSubject($varSubject);
		$mail->setFromEmail("support@invoicera.com");
		$mail->setFromName("Invoicera Support");
		$mail->setType('text');
		$mail->send();
	}
	
}
?>