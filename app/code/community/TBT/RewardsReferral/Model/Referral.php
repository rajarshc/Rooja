<?php

/**
 * Referral Model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Model_Referral extends Mage_Core_Model_Abstract {
    const XML_PATH_SUBSCRIPTION_EMAIL_TEMPLATE = 'rewards/referral/subscription_email_template';
    const XML_PATH_SUBSCRIPTION_EMAIL_IDENTITY = 'rewards/referral/subscription_email_identity';
    const XML_PATH_CONFIRMATION_EMAIL_TEMPLATE = 'rewards/referral/confirmation_email_template';
    const XML_PATH_CONFIRMATION_EMAIL_IDENTITY = 'rewards/referral/confirmation_email_identity';

    const STATUS_REFERRAL_SENT = 0;

    public function _construct() {
        parent::_construct();
        $this->_init('rewardsref/referral');
    }

    public function getInvites($id) {
        return $this->getCollection()->addClientFilter($id);
    }

    public function loadByEmail($customerEmail) {
        $data = $this->getResource()->loadByEmail($customerEmail);
        $this->addData($data);
        return $this;
    }

    //@nelkaake Added on Saturday June 26, 2010: 
    public function registerReferral(Mage_Customer_Model_Customer $affiliate, Mage_Customer_Model_Customer $referral) {
        return $this->registerReferral2($affiliate, $referral->getEmail(), $referral->getName());
    }

    //@nelkaake Added on Saturday June 26, 2010: Same as registerReferral but uses the child email and child name (in case child is not a model yet)
    public function registerReferral2(Mage_Customer_Model_Customer $affiliate, $referral_email, $referral_name) {
        if ($this->referralExists($referral_email)) {
            $this->loadByEmail($referral_email);
            return $this;
        }
        $this->setReferralParentId($affiliate->getId())
                ->setReferralEmail($referral_email)
                ->setReferralName($referral_name);
        return $this->save();
    }

    //@nelkaake Changed on Friday October 15, 2010: 
    public function referralExists($referral_email) {
        $existing_data = $this->getResource()->loadByEmail($referral_email);
        if (!empty($existing_data)) {
            return true;
        } else {
            return false;
        }
    }

    public function subscribe(Mage_Customer_Model_Customer $affiliate, $email, $name, $msg="") {
        $this->setReferralParentId($affiliate->getId())
                ->setReferralEmail($email)
                ->setReferralName($name);
        
        $emailSent = $this->sendSubscription($affiliate, $email, $name, $msg);
        if ($emailSent)
            return $this->save();

        return false;
    }

    public function isSubscribed($email) {
        $collection = $this->getCollection()->addEmailFilter($email);
        return $collection->count() ? true : false;
    }

    public function getSubscSenderName($storeId) {
        return Mage::getStoreConfig("trans_email/ident_support/name", $storeId);
    }

    public function getSubscSenderEmail($storeId) {
        return Mage::getStoreConfig("trans_email/ident_support/email", $storeId);
    }
    
    
    /**
     * Send an email to a customer offen the affiliate of a referral notifying the customer of an
     * event that earned them poins
     * 
     * @param $affiliate TBT_Rewards_Model_Customer 
     * @param $destinationEmail
     * 
     * @return send result          
     */
    public function sendSubscription(Mage_Customer_Model_Customer $affiliate, $destinationEmail, $destinationName, $message="" ) {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $store_id = $this->getStoreId();
        $store_id = empty($store_id) ? Mage::app()->getStore()->getId() : $store_id;

        /* @var $email Mage_Core_Model_Email_Template */
        $email = Mage::getModel('core/email_template');
        $email->setDesignConfig(array('area' => 'frontend', 'store' => $store_id));

        $template = Mage::getStoreConfig(self::XML_PATH_SUBSCRIPTION_EMAIL_TEMPLATE, $store_id);
        $recipient = array(
            'email' => $destinationEmail,
            'name' => $destinationName,
        );

        //@nelkaake (chng) on 1/11/10: Use transactonal e-mail settings instead if config says so.
        if (Mage::getStoreConfigFlag('rewards/referral/subscription_email_use_sender_email')) {
            $sender = array(
                'name' => strip_tags($affiliate->getName()),
                'email' => strip_tags($affiliate->getEmail())
            );
        } else {
            $sender = array(
                'name' => strip_tags($this->getSubscSenderName($store_id)),
                'email' => strip_tags($this->getSubscSenderEmail($store_id))
            );
        }

        $store_name = Mage::getModel('core/store')->load(Mage::app()->getStore()->getCode())->getName();
        // save theme settings because sendTransactional might not restore them
        $initial_layout = Mage::getDesign()->getTheme('layout');
        $initial_template = Mage::getDesign()->getTheme('template');
        $initial_skin = Mage::getDesign()->getTheme('skin');
        $initial_locale = Mage::getDesign()->getTheme('locale');
        
        $customerId = $this->getReferralChildId();
        $customer = Mage::getModel('rewards/customer')->load($customerId);
        $email->sendTransactional($template, $sender, $recipient['email'], $recipient['name'], 
        array(
            'parent' => $affiliate,	'affiliate' => $affiliate, 
            'referral' => $this, 
            'store_name' => $store_name, 
            'msg' => $message, 
            'referral_customer' => $customer, 
            'affiliate_url' => (string) Mage::helper('rewardsref/url')->getUrl($affiliate), 
            'referral_code' => (string) Mage::helper('rewardsref/code')->getCode($affiliate->getEmail()), 
            'referral_shortcode' => (string) Mage::helper('rewardsref/shortcode')->getCode($affiliate->getEmail())
        ));

        $translate->setTranslateInline(true);
        if (!$email->getProcessedTemplate())
            throw new Exception(Mage::helper('rewards')->__("The referral e-mail template was not properly loaded. This might be due to locale issues with characters that could be read, or tempalte variables that could not be parsed properly. The template being loaded was %s", $template));

        $return_value = $email->getSentSuccess();

        // restore theme restore
        Mage::getDesign()->setTheme('layout', $initial_layout);
        Mage::getDesign()->setTheme('template', $initial_template);
        Mage::getDesign()->setTheme('skin', $initial_skin);
        Mage::getDesign()->setTheme('locale', $initial_locale);

        return $return_value;
    }

    /**
     * @param $affiliate TBT_Rewards_Model_Customer the person who's making the referral and will earn the points
     * @param $child TBT_Rewards_Model_Customer the person being referred to the site
     * @param $destinationEmail
     * 
     * @return send result          
     */
    public function sendConfirmation(Mage_Customer_Model_Customer $affiliate, $destinationEmail, $destinationName, $message="", $pointsSummary="") {           
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $store_id = $this->getStoreId();
        $store_id = empty($store_id) ? Mage::app()->getStore()->getId() : $store_id;

        $email = Mage::getModel('core/email_template');
        $email->setDesignConfig(array('area' => 'frontend', 'store' => $store_id));
        /* @var $email Mage_Core_Model_Email_Template */

        $template = Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_EMAIL_TEMPLATE, $store_id);
        $recipient = array(
            'email' => $affiliate->getEmail(),
            'name' => $affiliate->getName(),
        );

        $sender = array(
            'name' => strip_tags($this->getSubscSenderName($store_id)),
            'email' => strip_tags($this->getSubscSenderEmail($store_id))
        );
        $store_name = Mage::getModel('core/store')->load(Mage::app()->getStore()->getCode())->getName();

        // save theme settings because sendTransactional might not restore them
        $initial_layout = Mage::getDesign()->getTheme('layout');
        $initial_template = Mage::getDesign()->getTheme('template');
        $initial_skin = Mage::getDesign()->getTheme('skin');
        $initial_locale = Mage::getDesign()->getTheme('locale');
        
        $customerId = $this->getReferralChildId();
        $customer = Mage::getModel('rewards/customer')->load($customerId);
        $email->sendTransactional($template, $sender, $recipient['email'], $recipient['name'], array(
            'parent' => $affiliate,             'affiliate' => $affiliate, 
            'referral' => $this, 
            'store_name' => $store_name, 
            'msg' => $message, 
            'referral_customer' => $customer, 
            'points_earned' => $pointsSummary, 
            'affiliate_url' => (string) Mage::helper('rewardsref/url')->getUrl($affiliate), 
            'referral_code' => (string) Mage::helper('rewardsref/code')->getCode($affiliate->getEmail()), 
            'referral_shortcode' => (string) Mage::helper('rewardsref/shortcode')->getCode($affiliate->getEmail()),
        ));

        $translate->setTranslateInline(true);
        if (!$email->getProcessedTemplate())
            throw new Exception(Mage::helper('rewards')->__("The referral e-mail template was not properly loaded. This might be due to locale issues with characters that could be read, or tempalte variables that could not be parsed properly. The template being loaded was %s", $template));

        $return_value = $email->getSentSuccess();

        // restore theme restore
        Mage::getDesign()->setTheme('layout', $initial_layout);
        Mage::getDesign()->setTheme('template', $initial_template);
        Mage::getDesign()->setTheme('skin', $initial_skin);
        Mage::getDesign()->setTheme('locale', $initial_locale);

        return $return_value;
    }

    //@nelkaake (add) on 1/11/10: 
    protected function _beforeSave() {
        if ($this->getDoCheckData())
            $this->checkData();
        parent::_beforeSave();
        return $this;
    }

    public function getDoCheckData() {
        if (!$this->hasData('do_check_data')) {
            return true;
        } else {
            return $this->getData('do_check_data');
        }
    }

    /**
     * If this doesn't throw any exceptions all the data in the model are fine.
     * //@nelkaake (add) on 1/11/10:      
     * @throws Exception
     */
    public function checkData() {
        $affiliate = Mage::getModel('rewards/customer')->load($this->getReferralParentId());
        $email = $this->getReferralEmail();
        $customer = Mage::getModel('rewards/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

        if (empty($email)) {
            throw new Exception(Mage::helper('rewardsref')->__("Referral e-mail may not be empty."));
        }
        if ($affiliate->getEmail() == $email) {
            throw new Exception(Mage::helper('rewardsref')->__("%s is your own e-mail address.", $email));
        }
        if (!$this->getId() && ($this->isSubscribed($email))) {
            throw new Exception(Mage::helper('rewardsref')->__('You or sombody else has already invited %s.', $email));
        }
        if (!$this->getId() && ($customer->getEmail() == $email)) {
            throw new Exception(Mage::helper('rewardsref')->__("%s is already signed up to the store.", $email));
        }
        // Referral is model is okay because it passed all checks         '
        return $this;
    }

}
