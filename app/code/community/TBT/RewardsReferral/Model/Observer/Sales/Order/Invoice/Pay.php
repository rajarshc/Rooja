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
 * Observer sales Order Invoice Pay
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Model_Observer_Sales_Order_Invoice_Pay {

    /**
     * Approve an affiliate point transfer if the admin has the option enabled to
     * auto approve pending transfers 
     * 
     * @param type $observer
     * @return TBT_RewardsReferral_Model_Observer_Sales_Order_Invoice_Pay 
     */
    public function approvePoints($observer) {
        $order = $observer->getEvent()
            ->getInvoice()
            ->getOrder();
        if ( ! $order ) {
            return $this;
        }
        
        if ( ! Mage::helper( 'rewards/config' )->shouldApprovePointsOnInvoice() ) {
            return $this;
        }
        
        $collectionReference = Mage::getResourceModel( 'rewardsref/referral_order_transfer_reference_collection' );  /* @var TBT_RewardsReferral_Model_Mysql4_Referral_Order_Transfer_Reference_Collection */
        $collectionReference->addTransferInfo();
        
        $collectionReference->filterAssociatedWithOrder( $order->getId() );
        
        $collectionReference->addFieldToFilter( 'status', array(
        	'eq' => TBT_Rewards_Model_Transfer_Status::STATUS_PENDING_EVENT
        ));
        
        $this->_approvateTransferReferences( $collectionReference );
        
        return $this;
    }

    /**
     * 
     * @param TBT_RewardsReferral_Model_Mysql4_Referral_Order_Transfer_Reference_Collection $collectionReference
     */
    protected function _approvateTransferReferences($collectionReference) {
        // No transfers need to be approved, so don't do anything.
        if ( $collectionReference->getSize() <= 0 ) {
            return $this;
        }
        
        foreach ($collectionReference as $transferReference) {
            if ( empty( $transferReference ) ) {
                continue;
            }
            
            if ( ! $transferReference->getId() ) {
                continue;
            }
            
            $this->_approvePointsTransfer( $transferReference );
        }
        
        return $this;
    }
    
    
    /**
     * 
     * @param TBT_Rewards_Model_Transfer_Reference $transferReference
     */
    protected function _approvePointsTransfer($transferReference) {
        $transfer = Mage::getModel( 'rewards/transfer' )->load( $transferReference->getRewardsTransferId() ); //$transferCollection->getFirstItem();
        
        $order_id = $transferReference->getReferenceId();

        if ( empty( $transfer ) ) {
            return $this;
        }
        
        $approve_result = $transfer->setStatus( $transfer->getStatus(), TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED );
        
        if ( ! $approve_result ) {
            Mage::helper( 'rewardsref' )->log("Unable to approve points transfer #{$transfer->getId()} associated with order #{$order_id}." );
            return $this;
        }
        
        $transfer->save();
        
        return $this;
    }

}