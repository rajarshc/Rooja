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
 * Manage Transfer Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
require_once (dirname(__FILE__) . DS .'SweettoothController.php');

class TBT_Rewards_Manage_TransferController extends TBT_Rewards_Manage_SweettoothController {
	const EXPORT_FILE_NAME = 'point_transfers';
	
	protected function _initAction() {
		$this->loadLayout ()->_setActiveMenu ( 'rewards/transfer' );
		
		return $this;
	}
	
    protected function _isAllowed() {
        return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'rewards/customer/transfers' );
    }
	
	public function indexAction() {
		$this->_initAction ()->renderLayout ();
	}
	
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$model = Mage::getModel ( 'rewards/transfer' )->load ( $id );
		
		if ($model->getId () || $id == 0) {
			$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
			if (! empty ( $data )) {
				$model->setData ( $data );
			}
			
			Mage::register ( 'transfer_data', $model );
			
			$this->loadLayout ();
			$this->_setActiveMenu ( 'rewards/transfer' );
			
			$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
			
			$this->_addContent ( $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit' ) )->_addLeft ( $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tabs' ) );
			
			$this->renderLayout ();
		} else {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'rewards' )->__ ( 'Transfer does not exist' ) );
			$this->_redirect ( '*/*/' );
		}
	}
	
	public function newAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$model = Mage::getModel ( 'rewards/transfer' )->load ( $id );
		
		$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
		
		if (! empty ( $data )) {
			$model->setData ( $data );
		}
		
		Mage::register ( 'transfer_data', $model );
		
		$this->loadLayout ();
		$this->_setActiveMenu ( 'rewards/transfer' );
		
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
		
		$this->_addContent ( $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit' ) )->_addLeft ( $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tabs' ) );
		
		$this->renderLayout ();
	}
	
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			try {
				if (isset ( $data ['status_id'] )) {
					$data ['status'] = $data ['status_id'];
				}
				
				//If the customer is deducting the points, make the quantity negative
				if ($data ['transfer_style'] == 'deduct') {
					$data ['quantity'] = - $data ['quantity'];
				}
				
				$id = $this->getRequest ()->getParam ( 'id' );
				
				$model = Mage::getModel ( 'rewards/transfer' );
				$model->load ( $id );
				
				$model = Mage::getModel ( 'rewards/transfer' );
				$model->setData ( $data )->setId ( $id );
				
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
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'Transfer was successfully saved' ) );
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
	
	public function revokeAction() {
		$original_transfer_id = $this->getRequest ()->getParam ( 'id' );
		$original_transfer = Mage::getModel ( 'rewards/transfer' )->load ( $original_transfer_id );
		
		if ($original_transfer->getStatus () != TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED) {
			Mage::getSingleton ( 'core/session' )->addError ( 'Transfer must be approved in order to be revoked.' );
			Mage::getSingleton ( 'core/session' )->addError ( 'If it is not approved, set it as cancelled to achieve the same effect.' );
			$this->_redirect ( '*/*/edit', array ('id' => $original_transfer_id ) );
			return;
		}
		
		try {
			$new_transfer_id = Mage::helper ( 'rewards/transfer' )->createRevokedTransfer ( $original_transfer->getQuantity () * - 1, $original_transfer->getCurrencyId (), $original_transfer_id, $original_transfer->getCustomerId () );
		} catch ( Exception $ex ) {
			Mage::getSingleton ( 'core/session' )->addError ( $ex->getMessage () );
			$this->_redirect ( '*/*/edit', array ('id' => $original_transfer_id ) );
			return;
		}
		
		if ($new_transfer_id != 0) {
			Mage::getSingleton ( 'core/session' )->addSuccess ( "Successfully revoked transfer." );
			$this->_redirect ( '*/*/edit', array ('id' => $new_transfer_id ) );
			return;
		} else {
			Mage::getSingleton ( 'core/session' )->addError ( "Could not revoke transfer." );
			$this->_redirect ( '*/*/edit', array ('id' => $original_transfer_id ) );
			return;
		}
	}
	
	public function deleteAction() {
		if ($this->getRequest ()->getParam ( 'id' ) > 0) {
			try {
				$model = Mage::getModel ( 'rewards/transfer' );
				
				$model->setId ( $this->getRequest ()->getParam ( 'id' ) )->delete ();
				
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'adminhtml' )->__ ( 'Post was successfully deleted' ) );
				$this->_redirect ( '*/*/' );
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				$this->_redirect ( '*/*/edit', array ('id' => $this->getRequest ()->getParam ( 'id' ) ) );
			}
		}
		$this->_redirect ( '*/*/' );
	}
	
	public function massDeleteAction() {
		$transferIds = $this->getRequest ()->getParam ( 'transfers' );
		if (! is_array ( $transferIds )) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'adminhtml' )->__ ( 'Please select transfer(s)' ) );
		} else {
			try {
				foreach ( $transferIds as $transferId ) {
					$transfer = Mage::getModel ( 'rewards/transfer' )->load ( $transferId );
					$transfer->delete ();
				}
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'adminhtml' )->__ ( 'Total of %d record(s) were successfully deleted', count ( $transferIds ) ) );
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
			}
		}
		$this->_redirect ( '*/*/index' );
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
							$this->_getSession ()->addNotice ( $this->__ ( 'Transfer #%s is already %s.', $transferId, $currentStatusCaption ) );
						} else {
							$msg = $this->__ ( 'Transfer #%s cannot be changed from %s to %s.', $transferId, $currentStatusCaption, $newStatusCaption );
							if (Mage::getSingleton ( 'rewards/transfer_status' )->isFromApprovedToCancelled ( $currentStatus, $newStatus )) {
								$msg .= " " . $this->__ ( 'Try revoking the transfer(s) instead.' );
							}
							$this->_getSession ()->addError ( $msg );
						}
						$changedTransferCount --;
						continue;
					}
					
					$transfer->setIsMassupdate ( true )->save ();
				}
				if ($changedTransferCount > 0) {
					$this->_getSession ()->addSuccess ( $this->__ ( 'A total of %s transfer(s) were successfully changed to %s.', $changedTransferCount, $newStatusCaption ) );
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
		$fileName = self::EXPORT_FILE_NAME . '-' . date ( "m.d.y.H:i:s" ) . '.csv';
		$content = $this->getLayout ()->createBlock ( 'rewards/manage_transfer_grid' );
		$csv = $content->getCsv ();
		
		$this->_sendUploadResponse ( $fileName, $csv );
	}
	
	/**
	 * Export product grid to XML format
	 */
	public function exportXmlAction() {
		$fileName = self::EXPORT_FILE_NAME . '-' . date ( "m.d.y.H:i:s" ) . '.xml';
		$content = $this->getLayout ()->createBlock ( 'rewards/manage_transfer_grid' );
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
	
	protected function gridModelInit() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$model = Mage::getModel ( 'rewards/transfer' );
		if ($id) {
			$model->load ( $id );
		}
		Mage::register ( 'transfer_data', $model );
		return $this;
	}
	
	public function customersGridAction() {
		$this->gridModelInit ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_customer_grid' )->toHtml () );
	}
	
	public function ordersGridAction() {
		$this->gridModelInit ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_orders' )->toHtml () );
	}
	
	public function pollsGridAction() {
		
		$this->gridModelInit ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_polls' )->toHtml () );
	}
	
	public function friendsGridAction() {
		
		$this->gridModelInit ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_friends' )->toHtml () );
	}
	
	public function reviewsGridAction() {
		$this->gridModelInit ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_reviews' )->toHtml () );
	}
	
	/**
	 * For transfers associated with other transfers (ie revoked)
	 *
	 */
	public function transfersGridAction() {
		$this->gridModelInit ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_transfers' )->toHtml () );
	}
	
	public function tagsGridAction() {
		$this->gridModelInit ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'rewards/manage_transfer_edit_tab_grid_tags' )->toHtml () );
	}
	
	public function preDispatch() {
		parent::preDispatch ();
	}	

}