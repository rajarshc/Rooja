<?php

class Magestore_Affiliateplus_ActiontransactionController extends Mage_Core_Controller_Front_Action {

    public function creatTransactionAction() {
        $params = $this->getRequest()->getParams();
        $hashCode = $params['hash_code'];
        $action = Mage::getModel('affiliateplus/action')->load($params['action_id']);
        $hashCodeCompare = md5($action->getCreatedDate() . $action->getId());
        if ($hashCode == $hashCodeCompare && !$action->getIsCommission()) {
            $isUnique = 1;
            $action->setIsUnique(1)->save();
            Mage::dispatchEvent('affiliateplus_save_action_before', array(
                'action' => $action,
                'is_unique' => $isUnique,
            ));
        }
    }

}