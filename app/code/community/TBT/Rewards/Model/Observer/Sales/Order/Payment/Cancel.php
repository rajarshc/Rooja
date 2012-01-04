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
 * Observer Sales Order Payment Cancel
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Observer_Sales_Order_Payment_Cancel extends Varien_Object {
	
	public function revokeAssociatedPendingTransfersOnCancel($observer) {
		$order = $observer->getEvent ()->getPayment ()->getOrder ();
		if (! $order) {
			return $this;
		}
		
                $hasCanceledPendingTransferes = false;
		if (Mage::helper ( 'rewards/config' )->shouldRemovePointsOnCancelledOrder ()) {
			$orderTransfers = Mage::getModel ( 'rewards/transfer' )->getTransfersAssociatedWithOrder ( $order->getId () );
			foreach ( $orderTransfers as $transfer ) {
				if (($transfer->getStatus () == TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_EVENT) || ($transfer->getStatus () == TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_APPROVAL)) {
					$transfer->setStatus ( $transfer->getStatus (), TBT_Rewards_Model_Transfer_Status::STATUS_CANCELLED );
					$transfer->save ();
					$hasCanceledPendingTransferes = true;
				} else if ($transfer->getStatus () == TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED) {
					try {
						$transfer->revoke();
					} catch ( Exception $ex ) {
						Mage::getSingleton ( 'core/session' )->addError ( Mage::helper ( 'rewards' )->__ ( 'Could not successfully revoke points associated with cancelled order.' ) );
						continue;
					}
					
					Mage::getSingleton ( 'core/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'Successfully cancelled transfer ID #' . $transfer->getId () ) );
				}
			}
		}     
	
        if($hasCanceledPendingTransferes) {
            $this->_cancelDispatchedMsgs();
        }
                
		return $this;
	}
	
	/**
	 * remove messages of pending points because they were revoked
	 * @return $this
	 */
	protected function _cancelDispatchedMsgs() {
        Mage::getSingleton ( 'core/session' )->getMessages()->deleteMessageByIdentifier('TBT_Rewards_Model_Observer_Sales_Order_Save_After_Create(pending points)');
       // Mage::getSingleton ( 'core/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'Successfully cancelled pending point transactions' ) );
        
	     return $this;
	}

}
