<?php

/**
 * Validation helper class
 *
 * @category   TBT
 * @package    TBT_RewardsReferral
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Helper_Validation extends Mage_Core_Helper_Abstract {

    /**
     * Validate URL
     * Allows for port, path and query string validations
     * @param    string      $url       string containing url user input
     * @return   boolean     Returns TRUE/FALSE
     */
    public function isValidUrl($url) {
        $pattern = '/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&amp;?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/';
        return preg_match($pattern, $url);
    }

    /**
     * returns true if this is a valid e-mail address
     * @param string $email
     */
    public function isValidEmail($email) {
        $validator = new Zend_Validate_EmailAddress();
        return $validator->isValid($email);
    }

}
