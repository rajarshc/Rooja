<?php

class TBT_RewardsReferral_Model_Observer_Createaccount extends Varien_Object {

    /**
     * Observer called when an account is being created the standard way
     * @param unknown_type $o
     */
    public function beforeCreate($o) {
        $this->attemptReferralCheck($o);
        return $this;
    }

    /**
     * Observer called when an account is being created through the checkout
     * @param unknown_type $o
     */
    public function beforeSaveBilling($o) {
        $this->attemptReferralCheck($o, 'billing');
        return $this;
    }

    /**
     * Base observer method that gets called by above observer methods whenever an account is being created in the frontend.
     * @param unknown_type $o
     * @param unknown_type $subfield
     * @throws Exception
     */
    protected function attemptReferralCheck($o, $subfield=null) {
        try {
            //@nelkaake The customer is already logged in so there is no need to create referral links
            if (Mage::getSingleton('rewards/session')->isCustomerLoggedIn()) {
                return $this;
            }

            $code_field = 'rewards_referral';
            $email_field = 'email';
            $firstname_field = 'firstname';
            $lastname_field = 'lastname';

            $action = $o->getControllerAction();
            $request = $action->getRequest();
            $this->setRequest($request);
            $data = $request->getPost();

            //@nelkaake Added on Thursday July 8, 2010: If a subfield (like billing) is needed, use it.
            if ($subfield) {
                if (isset($data[$subfield])) {
                    $data = $data[$subfield];
                }
            }

            //@nelkaake Added on Tuesday July 5, 2010: First some failsafe checks...
            if (empty($data)) {
                throw new Exception(
                        "Dispatched an event after the customer account creation method, " .
                        'but no data was found in app\code\community\TBT\RewardsReferral\Model\Observer\Createaccount.php ' .
                        "in TBT_RewardsReferral_Model_Observer_Createaccount::attemptReferralCheck."
                        , 1);
                return $this;
            }

            //@nelkaake Added on Thursday July 8, 2010: Was a code and e-mail passed?       
            //@nelkaake (add) on 1/11/10: By default, use the textbox code/email. 
            $use_field = true;
            if (!isset($data[$code_field])) {
                $use_field = false;
            } else {
                if (empty($data[$code_field])) {
                    $use_field = false;
                }
            }

            // If it's not set or if it's empty using the field, use the session  
            if (!$use_field) {
                //@nelkaake Changed on Wednesday October 6, 2010: Change the code if the customer does  
                if (Mage::helper('rewardsref/code')->getReferral()) {
                    $data[$code_field] = Mage::helper('rewardsref/code')->getReferral();
                } else {
                    $data[$code_field] = '';
                    //throw new Exception("Customer signup was detected with data, but the '{$code_field}' field was not detected.", 1);
                }
            }

            // If all the possible referral code options are empty or not set, exit the registration system.       
            if (empty($data[$code_field])) {
                return $this;
            }

            if (!isset($data[$email_field])) {
                throw new Exception("Customer signup was detected with data and the 'rewards_referral', but the '{$email_field}' field was not detected.", 1);
            }
            if (!isset($data[$firstname_field])) {
                Mage::helper('rewardsref')->log("Customer '{$firstname_field}' was not detected, but other data was detected.");
                $data[$firstname_field] = ' ';
            }
            if (!isset($data[$lastname_field])) {
                Mage::helper('rewardsref')->log("Customer '{$lastname_field}' was not detected, but other data was detected.");
                $data[$lastname_field] = ' ';
            }

            //@nelkaake Added on Thursday July 8, 2010: Fetchthe required data and load the customer
            $referral_code_or_email = $data[$code_field];
            $new_customer_email = $data[$email_field];
            //@nelkaake Added on Thursday July 8, 2010: We use this method of getting the full name becuase Magento has it's own getName() logic.
            $new_customer_name = Mage::getModel('customer/customer')
                    ->setFirstname($data[$firstname_field])
                    ->setLastname($data[$lastname_field])
                    ->getName();


            // Let's make sure the referral entry is valid.
            $referral_email = Mage::helper('rewardsref/code')->parseEmailFromReferralString($referral_code_or_email);
            if ($referral_email == $new_customer_email) {
                throw new Exception("Customer with e-mail {$new_customer_email} tried to refer his/her self {$referral_email}.", 1);
            }

            Mage::helper('rewardsref/code')->setReferral($referral_code_or_email);
            Mage::helper('rewardsref')->initateSessionReferral2($new_customer_email, $new_customer_name);
        } catch (Exception $e) {
            Mage::helper('rewardsref')->log($e->getMessage());
            if ($e->getCode() != 1) {
                Mage::logException($e);
            }
        }
    }

}
