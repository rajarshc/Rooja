<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_CustomerController
        extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();

        $customer = Mage::getSingleton('rewards/session')->getRewardsCustomer();
        Mage::register('customer', $customer);

        $this->renderLayout();

        return $this;
    }

    public function inviteAction() {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session = Mage::getSingleton('core/session');
            $name = trim((string) strip_tags($this->getRequest()->getPost('name')));
            $msg = trim((string) strip_tags($this->getRequest()->getPost('msg')));
            $email = trim((string) strip_tags($this->getRequest()->getPost('email')));

            $customerSession = Mage::getSingleton('rewards/session');
            $sess_customer = $customerSession->getSessionCustomer();
            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if ($name == '') {
                    Mage::throwException($this->__("Please enter your referral's name."));
                }
                $referralModel = Mage::getModel('rewardsref/referral');

                $customer = Mage::getModel('rewards/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email);

                if ($referralModel->isSubscribed($email)) {
                    Mage::throwException($this->__('You or sombody else has already invited %s.', $email));
                } elseif ($sess_customer->getEmail() == $email) {
                    Mage::throwException($this->__("%s is your own e-mail address.", $email));
                } elseif ($customer->getEmail() == $email) {
                    Mage::throwException($this->__("%s is already signed up to the store.", $email));
                } else {
                    $subscribe_result = $referralModel->subscribe($sess_customer, $email, $name, $msg);
                    if ($subscribe_result) {
                        $session->addSuccess($this->__('Thank you!  Your referral e-mail to %s has been sent.', $name));
                    } else {
                        $session->addError($this->__('There was a problem with the invitation.'));
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('%s', $e->getMessage()));
            } catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the invitation.'));
                Mage::logException($e);
            }
        }

        $this->_redirect('*/*/');

        return $this;
    }

    // this is required for the plaxo address book callback to work
    public function plaxocbAction() {
        $protocol = 'http:';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == TRUE) {
            $protocol = 'https:';
        }

        echo <<<FEED
<html><head>
<script type="text/javascript" src="$protocol//www.plaxo.com/ab_chooser/abc_comm.jsdyn"></script>
</head><body></body></html>
FEED;
    }

    
    /**
     * Sends a multi-invite using data specified from PLAXO.
     */ 
    public function invitesAction() {
        // || !$this->getRequest()->getPost('contacts')
        if (!$this->getRequest()->isPost()) {
            $this->_redirect('*/*/');
            return $this;
        }

        $session = Mage::getSingleton('core/session');
        $post_contacts = $this->getRequest()->getPost('contacts');

        $contacts = $this->_getContactsFromPost($post_contacts);

        $subject = trim((string) strip_tags($this->getRequest()->getPost('subject', "")));
        $message = trim((string) strip_tags($this->getRequest()->getPost('message', "")));

        // Validate data
        try {
            $this->_validateContactsData($contacts);
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            $this->_redirect('*/*/');
            return $this;
        }

        $customerSession = Mage::getSingleton('rewards/session');
        $sess_customer = $customerSession->getSessionCustomer();

        foreach ($contacts as $contact) {
            try {
                $name = $contact[0];
                $email = $contact[1];

                $referralModel = Mage::getModel('rewardsref/referral');
                $customer = Mage::getModel('rewards/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email);

                if ($referralModel->isSubscribed($email)) {
                    $session->addError($this->__('You or sombody else has already invited %s.', $email));
                } elseif ($sess_customer->getEmail() == $email) {
                    $session->addError($this->__("%s is your own e-mail address.", $email));
                } elseif ($customer->getEmail() == $email) {
                    $session->addError($this->__("%s is already signed up to the store.", $email));
                } else {
                    $subscribe_result = $referralModel->subscribe($sess_customer, $email, $name, $message, $subject);
                    if ($subscribe_result) {
                        $session->addSuccess($this->__('Your referral e-mail to %s has been sent.', $name));
                    } else {
                        $session->addError($this->__('There was a problem with the invitation for %s.', "\"{$name}\" <{$email}>"));
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('%s', $e->getMessage()));
            } catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the invitation.'));
                Mage::logException($e);
            }
        }

        $this->_redirect('*/*/');
        return $this;
    }
    
    /**
     * Validates the contacts array of data for valid data (valid e-mails etc)
     * @param array $contacts
     * @throws Exception if the data at any point is invalid
     */
    protected function _validateContactsData($contacts) {
    
        if (0 == count($contacts))
            throw new Exception($this->__("Please enter a valid contact in format: %s", htmlentities('"Name" <email@address.com>')));

        foreach ($contacts as $contact) {
            $name = $contact[0];
            $email = $contact[1];

            // cleanup names that are emails so steve = steve@wdca.ca
            $name = preg_replace('/(@.*)/', '', $name);
            // remove fancy char from name
            $name = preg_replace('/[!@#$%^&*()\/\\+={}:<>?]/', '', $name);

            if (empty($name))
                throw new Exception($this->__("Please enter a contact name for email address %s", $email));
            if (!Zend_Validate::is($email, 'EmailAddress'))
                throw new Exception($this->__('Please enter a valid email address for contact %s.', $name));
        }
        
        return $this;
    }
    
    /**
     * Fetches contacts data array from the POST contacts data provided.
     * @param array $post_contacts
     * @return array
     */
    protected function _getContactsFromPost($post_contacts) {
    
        $contacts = array();
        // find all contacts in format like: "name" <address@bla.com>
        preg_match_all('/\"([^\"]*)\"[ ,]*<[ \"\n\r]*(.*@.*)[ \"\n\r]*>/imU', $post_contacts, $match_contacts, PREG_SET_ORDER);
        foreach ($match_contacts as $contact) {
            $name = trim($contact[1]);
            $email = trim($contact[2]);

            // if the name is a email address then use the data pre @ as the name
            preg_match_all('/([^"@<>\s*]+)@(?:[-a-z0-9]+\.)+[a-z0-9]{2,}/im', $name, $match_email, PREG_SET_ORDER);
            foreach ($match_email as $name_email) {
                $name = trim($name_email[1]);
            }

            if (false == isset($contacts[$email])) {
                $contacts[$email] = array($name, $email);
            }
        }
    
        // find all emails in simple format: name@wdca.com
        preg_match_all('/([^"@<>\s*]+)@(?:[-a-z0-9]+\.)+[a-z0-9]{2,}/im', $post_contacts, $match_contacts, PREG_SET_ORDER);
        foreach ($match_contacts as $contact) {
            $name = trim($contact[1]);
            $email = trim($contact[0]);
            if (false == isset($contacts[$email])) {
                $contacts[$email] = array($name, $email);
            }
        }
        
        return $contacts;
    }

    /**
     *  @nelkaake Added on Wednesday July 21, 2010:
     */
    public function savePrefAction() {
        if ($this->getRequest()->isPost()) {
            $session = Mage::getSingleton('core/session');
            $customerSession = Mage::getSingleton('rewards/session');
            $sess_customer = $customerSession->getSessionCustomer();
            try {
                $data = $this->getRequest()->getPost();

                $notify_flag = isset($data['rewardsref_notify_on_referral']) ? true : false;
                $sess_customer->setRewardsrefNotifyOnReferral($notify_flag);

                $sess_customer->save();

                $session->addSuccess($this->__("Your preferences were saved successfully."));
            } catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem saving your preferences.'));
                Mage::logException($e);
            }
        }

        $this->_redirect('*/*/');

        return $this;
    }

    /**
     * @see Mage_Core_Controller_Front_Action::preDispatch()
     */
    public function preDispatch() {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
        if (!Mage::helper('rewards/config')->getIsCustomerRewardsActive()) {
            $this->norouteAction();
            return;
        }
    }

}