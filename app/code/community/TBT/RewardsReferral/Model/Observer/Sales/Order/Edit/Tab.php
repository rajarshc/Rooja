<?php

class TBT_RewardsReferral_Model_Observer_Sales_Order_Edit_Tab extends TBT_Rewards_Model_Transfer_Edit_Tab_Observer {

    /**
     * 
     * 
     * @param TBT_Rewards_Model_Transfer $transfer
     * @param TBT_Rewards_Block_Manage_Transfer_Edit_Tabs $block
     */
    protected function _addTab($transfer, $block) {
        
        $ref_col = Mage::getResourceModel( 'rewardsref/referral_order_transfer_reference_collection' ); /* @var TBT_RewardsReferral_Model_Mysql4_Referral_Order_Transfer_Reference_Collection */
        $ref_col->filterByTransfer( $transfer->getId() )
            ->addFieldToFilter( 'reference_type', 
            array(
                'eq' => TBT_RewardsReferral_Model_Transfer_Reference_Referral_Order::REFERENCE_TYPE_ID
            ) );
        
        // If the transfer has a reference that is a type, referral order
        if ( $ref_col->count() <= 0 ) {
            return $this;
        }
        
        // if the user is allowed to see sales order information
        if ( ! Mage::getSingleton( 'admin/session' )->isAllowed( 'sales/order/actions/view' ) ) {
            return $this;
        }
        
        // Grab the reference object
        $reference = $ref_col->getFirstItem();
        
        //  Add the transfer reference tab
        $this->_addOrderRefTab( $transfer, $reference, $block );
        
        return $this;
    }

    /**
     * 
     * @param TBT_Rewards_Model_Transfer $transfer
     * @param TBT_Rewards_Model_Transfer_Reference $reference
     * @param TBT_Rewards_Block_Manage_Transfer_Edit_Tabs $block
     */
    protected function _addOrderRefTab($transfer, $reference, $block) {
        
        $order_id = $reference->getReferenceId();
        
        // Set the reference info in the registry so that it is shown
        $reg_transfer = Mage::registry( 'transfer_data' );
        if ( $reg_transfer ) {
            Mage::unregister( 'transfer_data' );
        } else {
            $reg_transfer = $transfer;
        }
        $reg_transfer->setData( 'order_id', $order_id );
        Mage::register( 'transfer_data', $reg_transfer );
        
        $reference_order_block = $block->getLayout()->createBlock( 'rewards/manage_transfer_edit_tab_grid_orders' );
        
        $reference_order_block->setOrderId( $order_id );
        
        return $this;
    }

}