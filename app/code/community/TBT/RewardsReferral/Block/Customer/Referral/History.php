<?php

class TBT_RewardsReferral_Block_Customer_Referral_History extends TBT_RewardsReferral_Block_Customer_Referral_Abstract {

    public function _prepareLayout() {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'rewardsref.referral')
                ->setCollection($this->getReferred());
        $this->setChild('pager', $pager);
        return $this;
        //return parent::_prepareLayout();
    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }

}