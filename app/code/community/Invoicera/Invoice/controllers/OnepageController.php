<?php
require("Mage/Checkout/controllers/OnepageController.php");
class Invoicera_Invoice_OnepageController extends Mage_Checkout_OnepageController
{
	/**
     * Create order action
     */
    public function saveOrderAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        try {
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }
            if ($data = $this->getRequest()->getPost('payment', false)) {
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }
            $this->getOnepage()->saveOrder();
			
			// CREATING INVOICE AT INVOICERA.COM
			$this->createInvoiceForInvoicera();
            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            if ($gotoSection = $this->getOnepage()->getCheckout()->getGotoSection()) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }

            if ($updateSection = $this->getOnepage()->getCheckout()->getUpdateSection()) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
	
	public function createInvoiceForInvoicera()
	{
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');	
		// GETTING ORDER ID
		$resultOne = $db->query("SELECT max(entity_id) as LastOrderID FROM sales_flat_order");
		$rowOne = $resultOne->fetch(PDO::FETCH_ASSOC);
			
		$varOrderID = $rowOne['LastOrderID'];
		
		$varCurrenyCode =  Mage::app()->getStore()->getCurrentCurrency()->getCode();
		// GETTING ORDER STATUS
		$resultOne = $db->query("SELECT entity_id, status, customer_email, base_currency_code, shipping_description, shipping_amount, increment_id FROM sales_flat_order WHERE entity_id=".$varOrderID);
		$rowOne = $resultOne->fetch(PDO::FETCH_ASSOC);
		
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
			$resultThree = $db->query("SELECT firstname, lastname, company, email, telephone, concat_ws(',', street, city, region, postcode) as ClientAddress FROM sales_flat_order_address WHERE email='".$rowOne['customer_email']."'");
			$rowThree = $resultThree->fetch(PDO::FETCH_ASSOC);

			$clientCreateXml = '<?xml version="1.0" encoding="utf-8"?>
							<request method="createClient">
								<name>'.$rowThree['firstname'].' '.$rowThree['lastname'].'</name>
								<organization>'.$rowThree['firstname'].' '.$rowThree['lastname'].'</organization>
								<email>'.$rowOne['customer_email'].'</email>       	
								<work_phone>'.$rowThree['telephone'].'</work_phone> 	
								<address>'.$rowThree['ClientAddress'].'</address>  
								<currency>'.$varCurrenyCode.'</currency>
								<country></country>
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
		
		if($rowOne['status'] == 'processing' || $rowOne['status'] == 'complete')
		{
			$varStatus = 'Paid';
		}
		else
		{
			$varStatus = 'Sent';
		}
		
		if($varClientID)
		{				
			$result = $db->query("SELECT item_id, product_type, product_options, order_id, sku, name, description, qty_ordered, base_price, tax_percent, tax_amount, base_discount_amount FROM sales_flat_order_item WHERE order_id=".$varOrderID." AND parent_item_id IS NULL GROUP BY sku HAVING (order_id > 0) ORDER BY item_id desc");
			
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
			$comment = $data['comment_text'];
			// getting po_number
			$random_number = rand(0, pow(10, 7));
			// GETTING INVOICE PREFIX
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');				
			$varPath = 'invoice_options/invoice/invoice_prefix';
			$resultTwo = $db->query("SELECT value FROM core_config_data WHERE path LIKE '".$varPath."'");
			$rowTwo = $resultTwo->fetch(PDO::FETCH_ASSOC);
			$varInvoicePrefix = $rowTwo['value'];
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