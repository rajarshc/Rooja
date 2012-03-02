<?php

/**
 * Url generator/parser helper
 *
 * @category   TBT
 * @package    TBT_RewardsReferral
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Helper_Url extends Mage_Core_Helper_Abstract {
    
    public function getUrl(Mage_Customer_Model_Customer $customer) {
        switch (Mage::helper('rewardsref')->getReferralUrlStyle()) {
            case TBT_RewardsReferral_Helper_Data::REWARDSREF_URL_STYLE_EMAIL:
                $url_data = array(
                    'email' => urlencode($customer->getEmail()),
                );
                break;
            case TBT_RewardsReferral_Helper_Data::REWARDSREF_URL_STYLE_CODE:
                $url_data = array(
                    'code' => urlencode(Mage::helper('rewardsref/code')->getCode($customer->getEmail())),
                );
                break;
            default:
                $url_data = array(
                    'id' => $customer->getId(),
                );
        }
        $url = Mage::getUrl('rewardsref/index/refer', $url_data);
        return $url;
    }
    
}
