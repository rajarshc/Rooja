<?php

class Magestore_Affiliateplus_Block_CheckIframe extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getActionId() {
        $session = Mage::getSingleton('core/session');
        $actionId = $session->getData('transaction_checkiframe__action_id');
        $this->setActionId(NULL);
        return $actionId;
        ;
    }

    public function setActionId($actionId) {
        $session = Mage::getSingleton('core/session');
        $session->setData('transaction_checkiframe__action_id', $actionId);
    }

    public function getHashCode() {
        $session = Mage::getSingleton('core/session');
        $hashCode = $session->getData('transaction_checkiframe_hash_code');
        $this->setHashCode(NULL);
        return $hashCode;
    }

    public function setHashCode($hashCode) {
        $session = Mage::getSingleton('core/session');
        $session->setData('transaction_checkiframe_hash_code', $hashCode);
    }

}