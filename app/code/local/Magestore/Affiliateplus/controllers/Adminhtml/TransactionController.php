<?php

class Magestore_Affiliateplus_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('affiliateplus/transaction')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Transactions Manager'), Mage::helper('adminhtml')->__('Transaction Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Transactions'));
		$this->_initAction()
			->renderLayout();
	}
	
	public function gridAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->loadLayout();
        $this->renderLayout();
    }
	
	public function viewAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$id     = $this->getRequest()->getParam('id');
		$transaction  = Mage::getModel('affiliateplus/transaction')->load($id);
		
		$this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Transactions'))
			->_title($this->__($transaction->getAccountName()));
		
		if ($transaction->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$transaction->setData($data);
			}

			Mage::register('transaction_data', $transaction);
			
			$this->loadLayout();
			$this->_setActiveMenu('affiliateplus/transactions');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Transaction Manager'), Mage::helper('adminhtml')->__('Transaction Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Transaction News'), Mage::helper('adminhtml')->__('Transaction News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('affiliateplus/adminhtml_transaction_edit'))
				->_addLeft($this->getLayout()->createBlock('affiliateplus/adminhtml_transaction_edit_tabs'));
			
			$this->renderLayout();
			
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('Transaction does not exist'));
			$this->_redirect('*/*/');
		}
	}
    
    /**
     * Cancel transaction
     */
    public function cancelAction()
    {
        if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $transactionId = $this->getRequest()->getParam('id');
        if ($transactionId > 0) {
            $model = Mage::getModel('affiliateplus/transaction');
            try {
                $model->load($transactionId)
                    ->cancelTransaction();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Transaction was canceled successfully'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/view', array('id' => $transactionId));
    }
    
    public function massCancelAction()
    {
        if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $ids = $this->getRequest()->getParam('transaction');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select transaction(s)'));
        } else {
            $collection = Mage::getResourceModel('affiliateplus/transaction_collection');
            $collection->addFieldToFilter('transaction_id', array('in' => $ids));
            $successed = 0;
            foreach ($collection as $model) {
                try {
                    $model->cancelTransaction();
                    $successed++;
                } catch (Exception $e) {
                    
                }
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Total %s of %s transactions were canceled successfully', $successed, count($ids))
            );
        }
        $this->_redirect('*/*/index');
    }
    
    public function exportCsvAction()
    {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $fileName   = 'transaction.csv';
        $content    = $this->getLayout()->createBlock('affiliateplus/adminhtml_transaction_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $fileName   = 'transaction.xml';
        $content    = $this->getLayout()->createBlock('affiliateplus/adminhtml_transaction_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }
	
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}