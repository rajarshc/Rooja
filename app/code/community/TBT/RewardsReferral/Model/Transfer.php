<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
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
 * Transfer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Model_Transfer extends TBT_Rewards_Model_Transfer {
    const REFERENCE_REFERRAL = TBT_RewardsReferral_Model_Transfer_Reference_Referral::REFERENCE_TYPE_ID;

    public function getTransfersAssociatedWithReferredFriend($friend_id) {
        return $this->getCollection()
                ->addFilter('reference_type', self::REFERENCE_REFERRAL)
                ->addFilter('reference_id', $friend_id);
    }

    public function setReferredFriendId($id) {
        $this->clearReferences();
        $this->setReferenceType(self::REFERENCE_REFERRAL);
        $this->setReferenceId($id);
        $this->_data['friend_id'] = $id;
        return $this;
    }

    /**
     *
     * @param type $num_points
     * @param type $currency_id
     * @param type $earnerCustomerId
     * @param type $referredCustomerId
     * @param type $comment
     * @param type $reason_id
     * @param type $transferStatus TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED
     * @param type $referenceOrderId link transfer to order
     * @return TBT_RewardsReferral_Model_Transfer
     */
    public function create($num_points, $currency_id, $earnerCustomerId, $referredCustomerId, $comment = "", 
            $reason_id = TBT_Rewards_Model_Transfer_Reason::REASON_CUSTOMER_DISTRIBUTION, 
            $transferStatus = TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED, 
            $referenceOrderId = null) {
        
     // ALWAYS ensure that we only give an integral amount of points
        $num_points = floor( $num_points );
        if ( $num_points <= 0 ) {
            return $this;
        }
        
        $this->setReferredFriendId( $referredCustomerId );
        $this->setReasonId( $reason_id );
        if ( ! $this->setStatus( null, $transferStatus ) ) {
            return $this;
        }
        
        $this->setId( null )
            ->setCreationTs( now() )
            ->setLastUpdateTs( now() )
            ->setCurrencyId( $currency_id )
            ->setQuantity( $num_points )
            ->setComments( $comment )
            ->setCustomerId( $earnerCustomerId )
            ->save();
        
        // link point transfer to an order
        if ( ! empty( $referenceOrderId ) ) {
            $transferId = $this->getId();
            $transferReference = Mage::getModel( 'rewards/transfer_reference' );
            $transferReference->setReferenceId( $referenceOrderId )
                ->setReferenceType( TBT_RewardsReferral_Model_Transfer_Reference_Referral_Order::REFERENCE_TYPE_ID )
                ->setRewardsTransferId( $transferId )
                ->setRuleId( null )
                ->save();
            
        }
        
        return $this;
    }

}
