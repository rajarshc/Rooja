<?php
class Magestore_Affiliateplus_TestController extends Mage_Core_Controller_Front_Action
{
	public function indexAction(){
		//$account = Mage::getModel('affiliateplus/account')->load(3);
		//$account->sendMailToNewAccount();
		$tra = Mage::getModel('affiliateplus/transaction')->load(15);
		$tra = $tra->sendMailUpdatedTransactionToAccount(true);
		$tra = $tra->sendMailUpdatedTransactionToAccount(false);
		/* $data = array(array('amount' => '9.00', 'email' => 'sonvn_1293183760_per@yahoo.com'));
		$url = Mage::helper('affiliateplus/payment_paypal')->getPaymanetUrl($data);
		$http = new Varien_Http_Adapter_Curl();
		$http->write(Zend_Http_Client::GET, $url);
		$response = $http->read();
		$pos = strpos($response, 'ACK=Success');
		//print_r($response);die();
		if($pos){
			$storeId = Mage::app()->getStore()->getId();
			echo 'xxx' .$storeId; die();
		} */
	}
}