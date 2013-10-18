<?php

class Magestore_Affiliateplus_Adminhtml_PaymentController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('affiliateplus/payment')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Withdrawals Manager'), Mage::helper('adminhtml')->__('Withdrawals Manager'));

        return $this;
    }

    public function indexAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Withdrawals'));
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $id = $this->getRequest()->getParam('id');
        $payment = Mage::getModel('affiliateplus/payment')->load($id);

//		$paypalPayment = Mage::getModel('affiliateplus/payment_paypal')->getCollection()
//					->addFieldToFilter('payment_id', $payment->getId())
//					->getFirstItem();
//		
//		$payment->setPaypalEmail($paypalPayment->getEmail())
//				->setTransactionId($paypalPayment->getTransactionId());

        $this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Withdrawals'));

        if ($payment && $payment->getId())
            $this->_title($this->__($payment->getAccountName()));
        else
            $this->_title($this->__('New Withdrawal'));


        if ($payment->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

            if (!$id) { // add info from account
                $accountId = $this->getRequest()->getParam('account_id');
                $account = Mage::getModel('affiliateplus/account');
                if ($storeId = $this->getRequest()->getParam('store')) {
                    if (Mage::getStoreConfig('affiliateplus/account/balance', $storeId) == 'store') {
                        $account->setStoreId($storeId);
                    }
                }
                $account->load($accountId);
                $data['affiliateplus_account'] = $account;
                $data['account_name'] = $account->getName();
                $data['account_email'] = $account->getEmail();
                $data['account_id'] = $account->getId();
                $data['paypal_email'] = $account->getPaypalEmail();
                $data['moneybooker_email'] = $account->getMoneybookerEmail();
                $data['account_balance'] = $account->getBalance();
                if ($this->getRequest()->getParam('method') == 'api')
                    $data['payment_method'] = 'paypal';
            }

            if (!empty($data)) {
                $payment->addData($data);
            }

            if ($payment->getId())
                $payment->addPaymentInfo();

            Mage::register('payment_data', $payment);

            $this->loadLayout();
            $this->_setActiveMenu('affiliateplus/payment');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Payment Manager'), Mage::helper('adminhtml')->__('Withdrawals Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Payment News'), Mage::helper('adminhtml')->__('Withdrawal News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('affiliateplus/adminhtml_payment_edit'))
                    ->_addLeft($this->getLayout()->createBlock('affiliateplus/adminhtml_payment_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }

        if ($accountId = $this->getRequest()->getParam('account_id')) {
            $waitingPayment = Mage::getModel('affiliateplus/payment')->getCollection()
                    ->addFieldToFilter('account_id', $accountId)
                    ->addFieldToFilter('status', 1) // waiting payment
                    ->getFirstItem();
            if ($waitingPayment && $waitingPayment->getId()) {
                Mage::getSingleton('adminhtml/session')->addNotice(
                        Mage::helper('affiliateplus')->__('This account has already had a pending payment!')
                );
            }
            return $this->_forward('edit');
        }
        return $this->_redirect('*/*/');

        $methodPaypalPayment = Mage::getStoreConfig('affiliateplus/payment/payment_method');
        if (/* ($methodPaypalPayment != 'api' || count(Mage::helper('affiliateplus/payment')->getAvailablePaymentCode()) !=  1) && */$this->getRequest()->getParam('account_id')) //not using api and specific account ID
            $this->_forward('edit');
        else
            $this->_redirect('*/*/');
    }

    public function reviewAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        if ($data = $this->getRequest()->getPost()) {
            $storeId = $this->getRequest()->getParam('store');
            $amount = $this->getRequest()->getParam('amount');
            $paymentId = $this->getRequest()->getParam('id');
            $account = Mage::getModel('affiliateplus/account')
                    ->setStoreId($storeId)
                    ->load($this->getRequest()->getParam('account_id'));
            if (!$paymentId && $amount < Mage::helper('affiliateplus/config')->getPaymentConfig('payment_release', $storeId)) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('The minimum balance to accept withdrawals is %s', Mage::helper('core')->currency(Mage::helper('affiliateplus/config')->getPaymentConfig('payment_release', $storeId), true, false)));
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/new', array('account_id' => $this->getRequest()->getParam('account_id')));
                return;
            }
            if (!$paymentId && $amount > $account->getBalance()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('The withdrawal amount cannot exceed the account balance: %s.', Mage::helper('core')->currency($account->getBalance(), true, false)));
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/new', array('account_id' => $this->getRequest()->getParam('account_id')));
                return;
            }
            
            if ($this->getRequest()->getParam('masspayout')) {
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('affiliateplus')->__('The system will transfer money to your affiliate account immediately through the paygate.'));
            }
            
            $this->loadLayout();
            $this->_setActiveMenu('affiliateplus/payment');
            $this->_addContent($this->getLayout()->createBlock('affiliateplus/adminhtml_payment_review_edit'));
            return $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('Unable to find payment to review'));
        }
        $this->_redirect('*/*/');
    }

    public function cancelReviewAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        if ($data = $this->getRequest()->getPost()) {
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            return $this->_redirect('*/*/edit', array(
                        'id' => $this->getRequest()->getParam('id'),
                        'store' => $this->getRequest()->getParam('store'),
                        'account_id' => isset($data['account_id']) ? $data['account_id'] : null,
                    ));
        }
        $this->_redirect('*/*/');
    }

    public function saveAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        if ($data = $this->getRequest()->getPost()) {

            if (empty($data['payment_method']))
                $data['payment_method'] = 'paypal';
            
            if (empty($data['status']))
                $data['status'] = 1;

            $paymentId = $this->getRequest()->getParam('id');
            $payment = Mage::getModel('affiliateplus/payment')->load($paymentId)
                    ->setPaymentMethod($data['payment_method']);

            $storeId = $this->getRequest()->getParam('store');

            if (!$storeId) {
                $stores = Mage::app()->getStores();
                foreach ($stores as $store) {
                    $storeIds[] = $store->getId();
                }
            }else
                $storeIds = array($storeId);

            if (!$paymentId)// set store when create new payment only
                $payment->setStoreIds(implode(',', $storeIds));

            $payment->addData($data)
                    ->setId($paymentId);

            //set is payer fees

            if (!$paymentId) { // new payment
                $whoPayFees = Mage::getStoreConfig('affiliateplus/payment/who_pay_fees');
                if ($whoPayFees == 'payer' && $payment->getPaymentMethod() == 'paypal')
                    $payment->setIsPayerFee(1);
                else
                    $payment->setIsPayerFee(0);

                $payment->setIsRequest(0);
            }

            /* Magic check amount < balance */
            $account = Mage::getModel('affiliateplus/account')
                    ->setStoreId($storeId)
                    ->load($payment->getAccountId());
            $amount = $this->getRequest()->getParam('amount');
            if (!$paymentId && $amount < Mage::helper('affiliateplus/config')->getPaymentConfig('payment_release')) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('The minimum balance to accept withdrawals is %s', Mage::helper('core')->currency(Mage::helper('affiliateplus/config')->getPaymentConfig('payment_release', $storeId), true, false)));
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/new', array('account_id' => $account->getId()));
                return;
            }
            if (!$paymentId && $amount > $account->getBalance()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('The withdrawal amount cannot exceed the account balance: %s.', Mage::helper('core')->currency($account->getBalance(), true, false)));
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/new', array('account_id' => $account->getId()));
                return;
            }
            /* magic end check */

            try {
                if ($payment->getRequestTime() == NULL)
                    $payment->setRequestTime(now());

                $payment->setData('affiliateplus_account', $account);
                if (!$payment->getId() && !$payment->getStatus()) {
                    $payment->setStatus('1');
                }
                
                $payment->save();
                
                //create payment paypal
                $paypalPayment = $payment->getPayment()->addData($data)
                        ->setEmail($data['paypal_email'])
                        ->setTransactionId($data['transaction_id'])
                        ->savePaymentMethodInfo();


//                //update balance, totao paid, commmion of account
//                if ($payment->getStatus() == 3) {//complete
//                    $isBalanceIsGlobal = Mage::helper('affiliateplus')->isBalanceIsGlobal();
//                    $account = Mage::getModel('affiliateplus/account')
//                            //->setBalanceIsGlobal($isBalanceIsGlobal)
//                            ->setStoreId($storeId)
//                            ->load($payment->getAccountId());
//
//                    if ($payment->getIsPayerFee() && $payment->getPaymentMethod() == 'paypal')
//                        $receiedAmount = $payment->getAmount();
//                    else
//                        $receiedAmount = $payment->getAmount() - $payment->getFee();
//
//                    $account->setBalance($account->getBalance() - $payment->getAmount())
//                            ->setTotalCommissionReceived($account->getTotalCommissionReceived() + $receiedAmount)
//                            ->setTotalPaid($account->getTotalPaid() + $payment->getAmount())
//                            ->save();
//
//                    //send mail process payment to account
//                    $payment->sendMailProcessPaymentToAccount();
//                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affiliateplus')->__('Withdrawal was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $payment->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $paymentId));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('Unable to find payment to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $banner = Mage::getModel('affiliateplus/payment');

                $banner->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Withdrawal was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function gridAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function viewAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $paymentId = $this->getRequest()->getParam('id');
        $payment = Mage::getModel('affiliateplus/payment')->load($paymentId);

        $this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Payments'))
                ->_title($this->__($payment->getAccountName()));

        $this->loadLayout();
        $this->renderLayout();
    }

    public function processpaymentAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $paymentId = $this->getRequest()->getParam('id');

        if (!$paymentId) { // payout from admin
            $account = Mage::getModel('affiliateplus/account')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($this->getRequest()->getParam('account_id'));

            $stores = Mage::app()->getStores();
            foreach ($stores as $store) {
                $storeIds[] = $store->getId();
            }

            $payment = Mage::getModel('affiliateplus/payment')
                    ->setPaymentMethod('paypal')
                    ->setAmount($this->getRequest()->getParam('amount'))
                    ->setAccountId($account->getId())
                    ->setAccountName($account->getName())
                    ->setAccountEmail($account->getEmail())
                    ->setRequestTime(now())
                    ->setStoreIds(implode(',', $storeIds))
                    ->setStatus(1)
                    ->setIsRequest(0)
                    ->setIsPayerFee(0);

            if (Mage::getStoreConfig('affiliateplus/payment/who_pay_fees') == 'payer')
                $payment->setIsPayerFee(1);

            try {
                $payment->save();
            } catch (Exception $e) {
                
            }
        } else {
            $payment = Mage::getModel('affiliateplus/payment')->load($paymentId);
            $account = Mage::getModel('affiliateplus/account');

            if ($payment->isMultiStore())
                $account->setStoreId(Mage::app()->getStore()->getId()); //storeId = 0
            else
                $account->setStoreId($payment->getStoreIds());

            $account->load($payment->getAccountId());
        }



        if ($account->getStatus() == 2) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('The account is disabled'));
            $this->_redirect('*/*/edit', array('id' => $paymentId));
            return;
        }

        if ($payment->getStatus() == 1) {
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
            $url = Mage::helper('affiliateplus/payment_paypal')->getPaymanetUrl($data);

            $http = new Varien_Http_Adapter_Curl();
            $http->write(Zend_Http_Client::GET, $url);
            $response = $http->read();
            $pos = strpos($response, 'ACK=Success');
            if ($pos) { //create payment
                try {
                    $payment->setPaymentMethod('paypal')
                            ->setFee($fee)
                            ->setStatus(3) //complete
                            ->setData('affiliateplus_account', $account)
                            ->save();

                    $paypalPayment = $payment->getPayment()
                            ->setEmail($account->getPaypalEmail())
                            //->setTransactionId($data['transaction_id'])
                            ->savePaymentMethodInfo();


//                    $account->setBalance($account->getBalance() - $requiredAmount)
//                            ->setTotalCommissionReceived($account->getTotalCommissionReceived() + $amount)
//                            ->setTotalPaid($account->getTotalPaid() + $requiredAmount)
//                            ->save();

                    //send mail process payment to account
//                    $payment->sendMailProcessPaymentToAccount();

                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affiliateplus')->__('Paid sucessful'));
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('There is an error in paying out by paypal, please try again'));
            }
        } elseif ($payment->getStatus() == 2) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('This payment is processing'));
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('This payment is completed'));
        }

        $this->_redirect('*/*/edit', array('id' => $payment->getPaymentId()));
    }

    public function savePaymentAction() {
        if (!$this->getRequest()->getParam('masspayout')) {
            return $this->_redirect('*/*/');
        }
        $paymentId = $this->getRequest()->getParam('id');
        $payment = Mage::getModel('affiliateplus/payment')->load($paymentId)
                ->addData($this->getRequest()->getPost());

        $helper = $payment->getPayment()->getPaymentHelper();
        if (!$helper) {
            return $this->_redirect('*/*/');
        }
        $account = Mage::getModel('affiliateplus/account');
        $storeId = $this->getRequest()->getParam('store');
        if ($storeId && Mage::getStoreConfig('affiliateplus/account/balance', $storeId) != 'global') {
            $account->setStoreId($storeId);
        }
        $account->load($payment->getData('account_id'));
        if (!$account->getId()) {
            return $this->_redirect('*/*/');
        }
        $resPayment = $helper->payoutByApi($account, $payment->getData('amount'), $storeId, $payment->getId());
        if ($resPayment && $resPayment->getId()) {
            if ($resPayment->getStatus() == 3) {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('This payout has been transfered successfully!'));
//                $resPayment->sendMailProcessPaymentToAccount();
            } else {
                if ($resPayment->getErrorMessage()) {
                    Mage::getSingleton('adminhtml/session')->addError($resPayment->getErrorMessage());
                    $resPayment->setErrorMessage(null);
                    Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('adminhtml')->__('This payout has not been transfered!'));
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('This payout has not been transfered!'));
                }
            }
            return $this->_redirect('*/*/edit', array('id' => $resPayment->getId()));
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Your payout was not transfered and saved!'));
        return $this->_redirect('*/*/');
    }

    public function getTransactionId($response) {
        //$pos = strpos($response, 'ACK=Success');
    }

    public function exportCsvAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $fileName = 'Withdrawals.csv';
        $content = $this->getLayout()->createBlock('affiliateplus/adminhtml_payment_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $fileName = 'Withdrawals.xml';
        $content = $this->getLayout()->createBlock('affiliateplus/adminhtml_payment_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    /* Magic 29/11/2012 cancel complete */

    public function cancelPaymentAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $id = $this->getRequest()->getParam('id');
        $payment = Mage::getModel('affiliateplus/payment')->load($id);
        if (($payment->getStatus() <= 2))
            try {
                $payment->setStatus(4)->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Withdrawal was successfully canceled!'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        return $this->_redirect('*/*/');
    }

    /* END */

    public function selectAccountAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }

        $this->_initAction()
                ->renderLayout();
    }

    public function selectaccountgridAction() {
        $this->_initAction()
                ->renderLayout();
    }

}