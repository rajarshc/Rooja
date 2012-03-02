<?php

/**
 * Configuration helper 
 *
 * @category   TBT
 * @package    TBT_RewardsReferral
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Helper_Config extends Mage_Core_Helper_Abstract {

    /**
     * Returns a URL of where to redirect afer a customer hits an affiliate URL.
     * @param int $store_id
     */
    public function getRedirectPath($store_id=null) {
        $cfg_val = Mage::getStoreConfig('rewards/referral/affiliate_redirect_url', $store_id);
        if (Mage::helper('rewardsref/validation')->isValidUrl($cfg_val)) {
            $url = $cfg_val;
        } else {
            $url = Mage::getUrl($cfg_val);
        }

        return $url;
    }

}
