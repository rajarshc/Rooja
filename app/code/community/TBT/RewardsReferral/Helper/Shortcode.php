<?php

/**
 * Code generator/parser helper
 *
 * @nelkaake Added on Saturday June 26, 2010:  
 * @category   TBT
 * @package    TBT_RewardsReferral
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Helper_Shortcode extends Mage_Core_Helper_Abstract {
    const SHORT_CODE_KEY_MULTIPLIER = 77;

    /**
     * @param string $shortCode
     * @return integer customerId
     */
    public function getCustomerId($shortCode) {
        if( !$this->isValid($shortCode) ) {
            throw new Exception($this->__('"Short code: [%s] is not a valid referral code.', $shortCode));
        }
        $customerId_float = (float)$shortCode / (float)self::SHORT_CODE_KEY_MULTIPLIER;
        return (int)$customerId_float;
    }
    
    public function isValid($shortCode) {
        if( $shortCode == 0 ) {
            return false;
        }
        $customerId_float = (float)$shortCode / (float)self::SHORT_CODE_KEY_MULTIPLIER;
        if( (int)$customerId_float != $customerId_float ) {
            return false;
        }
        return true;
    }
    
    public function getCustomer($shortCode) {
        $customerId = 0;
        try {
            $customerId = $this->getCustomerId($shortCode);
        } catch(Exception $ex){
            return '';
        }
        return Mage::getModel('rewards/customer')->load($customerId);
    }
    
    public function getEmail($shortCode) {
        return $this->getCustomer($shortCode)->getEmail();
    }   

    public function getCode($customerId) {
        return $customerId * self::SHORT_CODE_KEY_MULTIPLIER;
    }
    
    protected function _getEncrypter() {
        return Mage::getSingleton('core/encryption');
    }

}
