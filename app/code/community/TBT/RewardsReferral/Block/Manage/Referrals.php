<?php

class TBT_RewardsReferral_Block_Manage_Referrals extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'manage_referrals';
        $this->_blockGroup = 'rewardsref';
        $this->_headerText = Mage::helper('rewardsref')->__('Customer Referrals');
        parent::__construct();
        $this->_removeButton('add');
    }

}