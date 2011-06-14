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
 * Manage Customer Points Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Manage_Customer_PointsController extends Mage_Adminhtml_Controller_Action {
	const EXPORT_FILE_NAME = 'customer_points';
	
	protected function _initAction() {
		$this->loadLayout ()->_setActiveMenu ( 'rewards/customer_points' )->_addBreadcrumb ( Mage::helper ( 'rewards' )->__ ( 'Promotions' ), Mage::helper ( 'rewards' )->__ ( 'Promotions' ) );
		return $this;
	}
	
	public function indexAction() {
		$this->_initAction ()->_addContent ( $this->getLayout ()->createBlock ( 'rewards/manage_customer_points' ) )->renderLayout ();
	}
	
	/**
	 * Transfers points to customers in the masses!
	 */
	public function massTransferPointsAction() {
		try {
			$customerIds = $this->getRequest ()->getPost ( 'customer' );
			$currencyId = $this->getRequest ()->getPost ( 'currency' );
			$quantity = ( int ) $this->getRequest ()->getPost ( 'quantity' );
			$is_deduction = ( int ) $this->getRequest ()->getParam ( 'is_deduction' ) === 1;
			
			if ($quantity <= 0) {
				throw new Exception ( $this->__ ( "Please enter a number more than zero (0)." ) );
			}
			
			// If we're deducting, invert the qty
			if ($is_deduction) {
				$quantity = $quantity * - 1;
			} else {
			
			}
			
			$numSuccessfulTransfers = 0;
			
			// Prepare the transfer template.
			$transfer = Mage::getModel ( 'rewards/transfer' )->setReasonId ( TBT_Rewards_Model_Transfer_Reason::REASON_ADMIN_ADJUSTMENT )->setComments ( Mage::helper ( 'rewards/config' )->getDefaultMassTransferComment () )->setCurrencyId ( $currencyId )->setQuantity ( $quantity );
			
			foreach ( $customerIds as $customer_id ) {
				try {
					$transfer->setId ( null )->setCustomerId ( $customer_id );
					
					// get the default starting status - usually Pending
					if (! $transfer->setStatus ( null, TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED )) {
						throw new Exception ( $this->__ ( "Could not approve points." ) );
					}
					$transfer->save ();
					$numSuccessfulTransfers ++;
				} catch ( Exception $e ) {
					$customer = Mage::getModel ( 'rewards/customer' )->load ( $customer_id );
					Mage::getSingleton ( 'core/session' )->addError ( $this->__ ( 'Failed to transfer points to customer %s', $customer->getName () ) . " : " . $e->getMessage () );
				}
			}
			
			if ($numSuccessfulTransfers > 0) {
				$success = $this->__ ( "Successfully transfered %s to %s customer(s).", Mage::getModel ( 'rewards/points' )->set ( $currencyId, $quantity ), $numSuccessfulTransfers );
				Mage::getSingleton ( 'core/session' )->addSuccess ( $success );
			}
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'core/session' )->addError ( $this->__ ( 'Woops, ran into error while trying to process your request: ' ) . $e->getMessage () );
		}
		$this->_redirect ( '*/*/' );
	}
	
	//Export product grid to CSV format
	public function exportCsvAction() {
		$fileName = self::EXPORT_FILE_NAME . '-' . date ( "m.d.y.H:i:s" ) . '.csv';
		$content = $this->getLayout ()->createBlock ( 'rewards/manage_customer_points_grid' );
		$csv = $content->getCsv ();
		
		$this->_sendUploadResponse ( $fileName, $csv );
	}
	
	//Export product grid to XML format
	public function exportXmlAction() {
		$fileName = self::EXPORT_FILE_NAME . '-' . date ( "m.d.y.H:i:s" ) . '.xml';
		$content = $this->getLayout ()->createBlock ( 'rewards/manage_customer_points_grid' );
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