<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Manage Currency Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Manage_CurrencyController extends Mage_Adminhtml_Controller_Action {
	const EXPORT_FILE_NAME = 'currencies';
	
	protected function _initAction() {
		$this->loadLayout ()->_setActiveMenu ( 'rewards/currency' );
		
		return $this;
	}
	
    protected function _isAllowed() {
        return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'rewards/cfg/currency' );
    }
	
	public function indexAction() {
		$this->_initAction ()->renderLayout ();
	}
	
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$model = Mage::getModel ( 'rewards/currency' )->load ( $id );
		
		if ($model->getId () || $id == 0) {
			$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
			if (! empty ( $data )) {
				$model->setData ( $data );
			}
			
			Mage::register ( 'currency_data', $model );
			
			$this->loadLayout ();
			$this->_setActiveMenu ( 'rewards/currency' );
			
			$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
			
			$this->_addContent ( $this->getLayout ()->createBlock ( 'rewards/manage_currency_edit' ) )->_addLeft ( $this->getLayout ()->createBlock ( 'rewards/manage_currency_edit_tabs' ) );
			
			$this->renderLayout ();
		} else {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'rewards' )->__ ( 'Currency does not exist' ) );
			$this->_redirect ( '*/*/' );
		}
	}
	
	public function newAction() {
		//		$id     = $this->getRequest()->getParam('id');
		//		$model  = Mage::getModel('rewards/currency')->load($id);
		//
		//		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		//		if (!empty($data)) {
		//			$model->setData($data);
		//		}
		//
		//		Mage::register('currency_data', $model);
		//
		//		$this->loadLayout();
		//		$this->_setActiveMenu('rewards/currency');
		//
		//		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		//
		//		$this->_addContent(
		//            $this->getLayout()->createBlock('rewards/manage_currency_edit')
		//        )->_addLeft(
		//            $this->getLayout()->createBlock('rewards/manage_currency_edit_tabs')
		//        );
		//
		//		$this->renderLayout();
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'adminhtml' )->__ ( 'You cannot create mulitple points currencies this way.  Please consult the Sweet Tooth documentation.' ) );
		$this->_redirect ( '*/*/' );
	}
	
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			if (isset ( $data ['status_id'] )) {
				$data ['status'] = $data ['status_id'];
			}
			
			$id = $this->getRequest ()->getParam ( 'id' );
			
			$model = Mage::getModel ( 'rewards/currency' );
			$model->load ( $id );
			
			$model = Mage::getModel ( 'rewards/currency' );
			$model->setData ( $data )->setId ( $id );
			
			try {
				
				if ($this->getRequest ()->getParam ( 'creation_ts' ) == NULL) {
					$model->setCreationTs ( now () )->setLastUpdateTs ( now () );
				} else {
					$model->setLastUpdateTs ( now () );
				}
				
				$adminFirstname = Mage::getSingleton ( 'admin/session' )->getUser ()->getFirstname ();
				$adminLastname = Mage::getSingleton ( 'admin/session' )->getUser ()->getLastname ();
				$adminFullName = $adminFirstname . " " . $adminLastname;
				if ($this->getRequest ()->getParam ( 'created_by' ) == NULL) {
					$model->setCreatedBy ( $adminFullName );
				}
				$model->setLastUpdateBy ( $adminFullName );
				
				$model->save ();
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'currency was successfully saved' ) );
				Mage::getSingleton ( 'adminhtml/session' )->setFormData ( false );
				
				if ($this->getRequest ()->getParam ( 'back' )) {
					$this->_redirect ( '*/*/edit', array ('id' => $model->getId () ) );
					return;
				}
				$this->_redirect ( '*/*/' );
				return;
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				Mage::getSingleton ( 'adminhtml/session' )->setFormData ( $data );
				$this->_redirect ( '*/*/edit', array ('id' => $this->getRequest ()->getParam ( 'id' ) ) );
				return;
			}
		}
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'rewards' )->__ ( 'Unable to find post to save' ) );
		$this->_redirect ( '*/*/' );
	}
	
	public function deleteAction() {
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'adminhtml' )->__ ( 'Cannot delete currency. Available in future upgrades of Sweet Tooth.' ) );
		$this->_redirect ( '*/*/' );
	}
	
	public function massDeleteAction() {
		
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'adminhtml' )->__ ( 'Cannot execute a mass delete' ) );
		$this->_redirect ( '*/*/' );
	}
	
	public function massStatusAction() {
		$transferIds = $this->getRequest ()->getParam ( 'transfers' );
		$newStatus = $this->getRequest ()->getParam ( 'status' );
		$allStatuses = Mage::getSingleton ( 'rewards/transfer_status' )->getOptionArray ();
		$newStatusCaption = $allStatuses [$newStatus];
		
		if (! is_array ( $transferIds )) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( $this->__ ( 'Please select transfer(s)' ) );
		} else {
			try {
				$changedTransferCount = count ( $transferIds );
				foreach ( $transferIds as $transferId ) {
					$transfer = Mage::getSingleton ( 'rewards/transfer' )->load ( $transferId );
					$currentStatus = $transfer->getStatus ();
					$currentStatusCaption = $allStatuses [$currentStatus];
					
					if ($transfer->setStatus ( $currentStatus, $newStatus ) === false) {
						if ($transfer->getStatus () == $newStatus) {
							$this->_getSession ()->addNotice ( 'Transfer #' . $transferId . ' is already ' . $currentStatusCaption );
						} else {
							$this->_getSession ()->addError ( 'Transfer #' . $transferId . ' cannot be changed from ' . $currentStatusCaption . ' to ' . $newStatusCaption );
						}
						$changedTransferCount --;
						continue;
					}
					
					$transfer->setIsMassupdate ( true )->save ();
				}
				if ($changedTransferCount > 0) {
					$this->_getSession ()->addSuccess ( $this->__ ( 'Total of %d transfer(s) were successfully changed to ' . $newStatusCaption, $changedTransferCount ) );
				}
			} catch ( Exception $e ) {
				$this->_getSession ()->addError ( $e->getMessage () );
			}
		}
		$this->_redirect ( '*/*/index' );
	}
	
	/**
	 * Export product grid to CSV format
	 */
	public function exportCsvAction() {
		$fileName = self::EXPORT_FILE_NAME . '-' . date ( "m.d.y.H:i:s" ) . '.xml';
		$content = $this->getLayout ()->createBlock ( 'rewards/manage_currency_grid' );
		$csv = $content->getCsv ();
		
		$this->_sendUploadResponse ( $fileName, $csv );
	}
	
	/**
	 * Export product grid to XML format
	 */
	public function exportXmlAction() {
		$fileName = self::EXPORT_FILE_NAME . '-' . date ( "m.d.y.H:i:s" ) . '.xml';
		$content = $this->getLayout ()->createBlock ( 'rewards/manage_currency_grid' );
		$xml = $content->getXml ();
		
		$this->_sendUploadResponse ( $fileName, $xml );
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
		$response = $this->getResponse ();
		$response->setHeader ( 'HTTP/1.1 200 OK', '' );
		
		$response->setHeader ( 'Pragma', 'public', true );
		$response->setHeader ( 'Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true );
		
		$response->setHeader ( 'Content-Disposition', 'attachment; filename=' . $fileName );
		$response->setHeader ( 'Last-Modified', date ( 'r' ) );
		$response->setHeader ( 'Accept-Ranges', 'bytes' );
		$response->setHeader ( 'Content-Length', strlen ( $content ) );
		$response->setHeader ( 'Content-type', $contentType );
		$response->setBody ( $content );
		$response->sendResponse ();
		die ();
	}
	
	public function preDispatch() {
		if (! Mage::helper ( 'rewards/loyalty_checker' )->isValid ()) {
			Mage::throwException ( "Please check your Sweet Tooth registration code your Magento configuration settings, or contact WDCA through contact@wdca.ca for a description of this problem." );
		}
		parent::preDispatch ();
	}

}
