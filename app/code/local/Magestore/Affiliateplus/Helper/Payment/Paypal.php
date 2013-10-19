<?php
class Magestore_Affiliateplus_Helper_Payment_Paypal extends Mage_Core_Helper_Abstract
{
    // pay for account by API
    public function payoutByApi($account, $amount, $storeId = null, $paymentId = null) {
        if ($account->getStatus() == 2)
            return;
        if (!$storeId) {
            $stores = Mage::app()->getStores();
            foreach ($stores as $store) {
                $storeIds[] = $store->getId();
            }
        } else {
            $storeIds = $storeId;
        }
        $payment = Mage::getModel('affiliateplus/payment')
                ->load($paymentId)
                ->setId($paymentId)
                ->setPaymentMethod('paypal')
                ->setAmount($amount)
                ->setAccountId($account->getId())
                ->setAccountName($account->getName())
                ->setAccountEmail($account->getEmail())
//                ->setRequestTime(now())
                ->setStoreIds(implode(',', $storeIds))
//                ->setStatus(1)
//                ->setIsRequest(0)
                ->setIsPayerFee(0);
        if ($account->getData('is_created_by_recurring')) {
            $payment->setData('is_created_by_recurring', 1)
                ->setData('is_recurring', 1);
        }
        if (!$paymentId) {
            $payment->setRequestTime(now())
                ->setStatus(1)
                ->setIsRequest(0);
        }
        if (Mage::getStoreConfig('affiliateplus/payment/who_pay_fees',$storeId) == 'payer')
            $payment->setIsPayerFee(1);

        $requiredAmount = $payment->getAmount();
        if ($payment->getIsPayerFee())
            $amount = round($requiredAmount, 2);
        else {
            if ($requiredAmount >= 50)
                $amount = round($requiredAmount - 1, 2); // max fee is 1$ by api
            else
                $amount = round($requiredAmount / 1.02, 2); // fees 2% when payment by api
        }

        if ($amount >= 50)
            $fee = 1;
        else
            $fee = round($amount * 0.02, 2);

        $data = array(array('amount' => $amount, 'email' => $account->getPaypalEmail()));
        $url = $this->getPaymanetUrl($data);

        $http = new Varien_Http_Adapter_Curl();
        $http->write(Zend_Http_Client::GET, $url);
        $response = $http->read();
        $pos = strpos($response, 'ACK=Success');
        
        $payment->setData('affiliateplus_account', $account);
        if ($pos) { //create payment
            try {
                $payment->setPaymentMethod('paypal')
                        ->setFee($fee)
                        ->setStatus(3) //complete
//                        ->setData('is_created_by_recurring', 1)
                        ->save();

                $paypalPayment = $payment->getPayment()
                        ->setEmail($account->getPaypalEmail())
                        ->savePaymentMethodInfo();

//                $account->setBalance($account->getBalance() - $requiredAmount)
//                        ->setTotalCommissionReceived($account->getTotalCommissionReceived() + $amount)
//                        ->setTotalPaid($account->getTotalPaid() + $requiredAmount)
//                        ->save();

                //send mail process payment to account
               // $payment->sendMailProcessPaymentToAccount();

//                Mage::getSingleton('core/session')->addSuccess(Mage::helper('affiliateplus')->__('Paid sucessful'));
            } catch (Exception $e) {
//                Mage::getSingleton('core/session')->addError($e);
            }
        } else {
            $payment->save();
            $paypalPayment = $payment->getPayment()
                ->setEmail($account->getPaypalEmail())
                ->savePaymentMethodInfo();
            $account->save();
        }
        return $payment;
    }
    
//data is list email and value of payment 
	public function getPaymanetUrl($data){
		$url = $this->_getMasspayUrl();
		$i = 0;
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		foreach($data as $item){
			$url .= '&L_EMAIL'.$i.'='.$item['email'].'&L_AMT'.$i.'='.$item['amount'].'&CURRENCYCODE'.$i.'='.$baseCurrencyCode;
			// $url .= '&L_EMAIL'.$i.'='.$item['email'].'&L_AMT'.$i.'='.$item['amount'];
			$i++;
		}
		return $url;
	}
	
	protected function _getMasspayUrl(){
		$url = $this->_getApiEndpoint();
		$url .= '&METHOD=MassPay&RECEIVERTYPE=EmailAddress';
		return $url;
	}
	
	protected function _getApiEndpoint(){
		$isSandbox = Mage::getStoreConfig('paypal/wpp/sandbox_flag');
		$paypalApi = $this->_getPaypalApi();
        $url = sprintf('https://api-3t%s.paypal.com/nvp?', $isSandbox ? '.sandbox' : '');
		$url .= 'USER=' . $paypalApi['api_username'] . '&PWD=' . $paypalApi['api_password'] . '&SIGNATURE=' . $paypalApi['api_signature'] . '&VERSION=62.5';
		return $url;
    }
	
	protected function _getPaypalApi(){
		$data['api_username'] = Mage::getStoreConfig('paypal/wpp/api_username');
		$data['api_password'] = Mage::getStoreConfig('paypal/wpp/api_password');
		$data['api_signature'] = Mage::getStoreConfig('paypal/wpp/api_signature');
		return $data;
	}
}