<?php

class Magestore_AffiliateplusReferFriend_ReferController extends Mage_Core_Controller_Front_Action {

    /**
     * get Account helper
     *
     * @return Magestore_Affiliateplus_Helper_Account
     */
    protected function _getAccountHelper() {
        return Mage::helper('affiliateplus/account');
    }

    /**
     * get Affiliateplus helper
     *
     * @return Magestore_Affiliateplus_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('affiliateplus');
    }

    /**
     * getConfigHelper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    protected function _getConfigHelper() {
        return Mage::helper('affiliateplus/config');
    }

    /**
     * get Core Session
     *
     * @return Mage_Core_Model_Session
     */
    protected function _getCoreSession() {
        return Mage::getSingleton('core/session');
    }

    public function indexAction() {
//        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
//            return;
//        }
        if ($this->_getAccountHelper()->accountNotLogin()) {
            return $this->_redirect('affiliateplus/account/login');
        }
        if (!$this->_getConfigHelper()->getReferConfig('enable')) {
            return $this->_redirect('affiliateplus/index/index');
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('affiliateplus')->__('Refer Friends and Earn Money'));
        $this->renderLayout();
    }

    /* Personal URL */

    public function personalAction() {
//        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
//            return;
//        }
        if ($this->_getAccountHelper()->accountNotLogin()) {
            return $this->_redirect('affiliateplus/account/login');
        }
        if ($data = $this->getRequest()->getPost()) {
            $session = $this->_getCoreSession();
            if (!$data['personal_url']) {
                $session->addError($this->__('Please enter a valid custom url'));
                return $this->_redirect('*/*/index');
            }
            $requestPath = $this->_getConfigHelper()->getReferConfig('url_prefix') . trim($data['personal_url']);
            $account = Mage::getSingleton('affiliateplus/session')->getAccount();
            $store = Mage::app()->getStore();

            $idPath = 'affiliateplus/' . $store->getId() . '/' . $account->getId();

            /* Magic fix url include '@','©','®','À'... */
            $requestPath = Mage::helper('catalog/product_url')->format($requestPath);
            $requestPath = str_replace(" ", "", $requestPath);
            /* END */

            $existedRewirte = Mage::getResourceModel('core/url_rewrite_collection')
                    ->addFieldToFilter('store_id', $store->getId())
                    ->addFieldToFilter('request_path', $requestPath)
                    ->addFieldToFilter('id_path', array('neq' => $idPath))
                    ->getFirstItem();
            if ($existedRewirte->getId()) {
                $session->addError($this->__('This url already exists. Please choose another custom url.'));
                $session->setAffilateCustomUrl($data['personal_url']);
                return $this->_redirect('*/*/index');
            }
            $targetPath = $this->_getDefaultPath($store);
            if (strpos($targetPath, '?') === false)
                $targetPath .= '/?acc=';
            else
                $targetPath .= '&acc=';
            $targetPath .= $account->getIdentifyCode();

            $rewrite = Mage::getModel('core/url_rewrite')->load($idPath, 'id_path');
            $rewrite->addData(array(
                'store_id' => $store->getId(),
                'id_path' => $idPath,
                'request_path' => $requestPath,
                'target_path' => $targetPath,
                'is_system' => 0,
            ));
            try {
                $rewrite->save();
                $session->addSuccess($this->__('Your custom url has been saved successfully!'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                $session->setAffilateCustomUrl($data['personal_url']);
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function _getDefaultPath($store = null) {
        $defaultPath = Mage::getStoreConfig('web/default/front', $store);
        $p = explode('/', $defaultPath);
        switch (count($p)) {
            case 1: $p[] = 'index';
            case 2: $p[] = 'index';
        }
        return implode('/', $p);
    }

    /* Email */

    public function emailAction() {
//        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
//            return;
//        }
        if ($this->_getAccountHelper()->accountNotLogin()) {
            return $this->_redirect('affiliateplus/account/login');
        }
        if ($data = $this->getRequest()->getPost()) {
            $emails = $data['emails'];
            $data['email_subject'] = $data['email_subject'] ? $data['email_subject'] : Mage::getBlockSingleton('affiliateplusreferfriend/refer')->getDefaultEmailSubject();
            $data['email_content'] = $data['email_content'] ? $data['email_content'] : Mage::getBlockSingleton('affiliateplusreferfriend/refer')->getDefaultEmailContent();
            $session = $this->_getCoreSession();
            if (strpos($emails, '@') === false) {
                $session->addError($this->__('No email address is available!'));
                $session->setEmailFormData($data);
                return $this->_redirect('*/*/index', array('tab' => 'email'));
            }
            $contacts = array_unique(explode(',', $emails));
            $totalSent = 0;

            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);
            $account = Mage::getSingleton('affiliateplus/session')->getAccount();
            $sender = array('name' => $account->getName(), 'email' => $account->getEmail());
            $template = $this->_getConfigHelper()->getEmailConfig('refer_template');

            $store = Mage::app()->getStore();
            $mailTemplate = Mage::getModel('core/email_template')
                    ->setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()));
            foreach ($contacts as $contact) {
                if (strpos($contact, '@') === false)
                    continue;
                $name = '';
                if (strpos($contact, '<') !== false) {
                    $name = substr($contact, 0, strpos($contact, '<'));
                    $contact = substr($contact, strpos($contact, '<') + 1);
                }
                $email = rtrim(rtrim($contact), '>');
                if (!$name) {
                    $emailExtract = explode('@', $email);
                    $name = $emailExtract[0];
                }
                $subject = str_replace(array('{{friend_name}}', '{{friend_email}}'), array($name, $email), $data['email_subject']);
                $content = str_replace(array('{{friend_name}}', '{{friend_email}}'), array($name, $email), $data['email_content']);
                try {
                    $mailTemplate->sendTransactional(
                            $template, $sender, $email, $name, array(
                        'store' => $store,
                        'contact_name' => $name,
                        'sender_name' => $account->getName(),
                        'subject' => $subject,
                        'content' => $content,
                            )
                    );
                    $totalSent++;
                } catch (Exception $e) {
                    
                }
            }
            $translate->setTranslateInline(true);

            if ($totalSent) {
                $session->addSuccess($this->__('Total %s email(s) have been sent successfully!', $totalSent));
            } else {
                $session->addError($this->__('No email has been sent.'));
                $session->setEmailFormData($data);
                return $this->_redirect('*/*/index', array('tab' => 'email'));
            }
        }
        return $this->_redirect('*/*/index');
    }

    public function yahooAction() {
//        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
//            return;
//        }
        if ($this->_getAccountHelper()->accountNotLogin()) {
            $url = Mage::getUrl('affiliateplus/account/login');
            echo "<html><head></head><body><script type='text/javascript'>
				try{
					window.opener.location.href = '$url';
				}catch(e){}
		    	window.close();
	    	</script></body></html>";
            exit();
        }
        $yahoo = Mage::getSingleton('affiliateplusreferfriend/refer_yahoo');
        if (!$yahoo->hasSession() || !$this->getRequest()->getParam('oauth_token') || !$this->getRequest()->getParam('oauth_verifier'))
            return $this->_redirectUrl($yahoo->getAuthUrl());

        $this->loadLayout();
        $this->renderLayout();
    }

    public function gmailAction() {
//        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
//            return;
//        }
        if ($this->_getAccountHelper()->accountNotLogin()) {
            $url = Mage::getUrl('affiliateplus/account/login');
            echo "<html><head></head><body><script type='text/javascript'>
				try{
					window.opener.location.href = '$url';
				}catch(e){}
		    	window.close();
	    	</script></body></html>";
            exit();
        }
        $gmail = Mage::getSingleton('affiliateplusreferfriend/refer_gmail');
        if (!$gmail->isAuth())
            return $this->_redirectUrl($gmail->getAuthUrl());

        $this->loadLayout();
        $this->renderLayout();
    }

    public function hotmailAction() {
//        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
//            return;
//        }
        if ($this->_getAccountHelper()->accountNotLogin()) {
            $url = Mage::getUrl('affiliateplus/account/login');
            echo "<html><head></head><body><script type='text/javascript'>
				try{
					window.opener.location.href = '$url';
				}catch(e){}
		    	window.close();
	    	</script></body></html>";
            exit();
        }
        $hotmail = Mage::getSingleton('affiliateplusreferfriend/refer_hotmail');
        if (!$hotmail->isAuth())
            return $this->_redirectUrl($hotmail->getAuthUrl());

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * facebook share action
     */
    public function facebookAction() {
//        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
//            return;
//        }
        if ($this->_getAccountHelper()->accountNotLogin()) {
            $url = Mage::getUrl('affiliateplus/account/login');
            echo "<html><head></head><body><script type='text/javascript'>
				try{
					window.opener.location.href = '$url';
				}catch(e){}
		    	window.close();
	    	</script></body></html>";
            exit();
        }
        try {
            $isAuth = $this->getRequest()->getParam('auth');
            if (!class_exists('Facebook'))
                require_once(Mage::getBaseDir('lib') . DS . 'Facebookv3' . DS . 'facebook.php');
            $facebook = new Facebook(array(
                        'appId' => $this->_getConfigHelper()->getReferConfig('fbapp_id'),
                        'secret' => $this->_getConfigHelper()->getReferConfig('fbapp_secret'),
                        'cookie' => true
                    ));
            $userId = $facebook->getUser();
            if ($isAuth || !$userId) {
                $loginUrl = $facebook->getLoginUrl(array(
                    'display' => 'popup',
                    'redirect_uri' => Mage::getUrl('*/*/facebook'),
                    'scope' => 'publish_stream,email',
                        ));
                unset($_SESSION['fb_' . $this->_getConfigHelper()->getReferConfig('fbapp_id') . '_code']);
                unset($_SESSION['fb_' . $this->_getConfigHelper()->getReferConfig('fbapp_id') . '_access_token']);
                unset($_SESSION['fb_' . $this->_getConfigHelper()->getReferConfig('fbapp_id') . '_user_id']);
                die("<script type='text/javascript'>top.location.href = '$loginUrl';</script>");
            }
            $params = $this->getRequest()->getParams();
            if (!isset($params['message'])) {
                echo "<html><head></head><body><script type='text/javascript'>
				var newUrl = window.location.href;
				var message = '';
				try{
					message = window.opener.document.getElementById('affiliate-facebook-content').value;
					message = encodeURIComponent(message);
				}catch(e){}
				var fragment = '';
				if (newUrl.indexOf('#')){
					fragment = '#' + newUrl.split('#')[1];
					newUrl = newUrl.split('#')[0];
				}
				if (newUrl.indexOf('?') != -1) newUrl += '&message=' + message;
				else newUrl += '?message=' + message;
				newUrl += fragment;
				top.location.href = newUrl;
				</script></body></html>";
                exit();
            }
            $message = $params['message'];
            if (!$message)
                $message = Mage::getBlockSingleton('affiliateplusreferfriend/refer')->getDefaultSharingContent();

            $facebook->api("/$userId/feed", 'POST', array('message' => $message));

            echo "<script type='text/javascript'>
			try{
				window.opener.document.getElementById('affiliate-facebook-msg').show();
			}catch(e){}
			window.close();
			</script>";
            exit();
        } catch (Exception $e) {
            
        }
        echo "<script type='text/javascript'>
		try{
			window.opener.document.getElementById('affiliate-facebook-msg').hide();
		}catch(e){}
    	window.close();
    	</script>";
        exit();
    }

    public function refineCustomUrlAction() {
        $custom_url = $this->getRequest()->getParam('custom_url');
        $requestPath = Mage::helper('catalog/product_url')->format($custom_url);
        $response = str_replace(" ", "", $requestPath);
        $this->getResponse()->setBody(json_encode($response));
    }
    
    public function emailboxAction() {
        $formBlock = $this->getLayout()->createBlock('affiliateplusreferfriend/email_form');
        $formBlock->setTemplate('affiliateplusreferfriend/email/form.phtml');
        $this->getResponse()->setBody($formBlock->toHtml());
    }
    
    public function sendemailAction() {
//        if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) {
//            return;
//        }
        $result = array();
        if ($this->_getAccountHelper()->accountNotLogin()) {
            $result['redirect'] = Mage::getUrl('affiliateplus/account/login');
            return $this->responseJson($result);
        }
        if ($data = $this->getRequest()->getPost()) {
            $emails = $data['emails'];
            $data['email_subject'] = $data['email_subject'] ? $data['email_subject'] : Mage::getBlockSingleton('affiliateplusreferfriend/refer')->getDefaultEmailSubject();
            $data['email_content'] = $data['email_content'] ? $data['email_content'] : Mage::getBlockSingleton('affiliateplusreferfriend/refer')->getDefaultEmailContent();
            $session = $this->_getCoreSession();
            if (strpos($emails, '@') === false) {
                $result['error'] = 1;
                $result['message'] = $this->__('No email address is available!');
                return $this->responseJson($result);
            }
            $contacts = array_unique(explode(',', $emails));
            $totalSent = 0;

            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);
            $account = Mage::getSingleton('affiliateplus/session')->getAccount();
            $sender = array('name' => $account->getName(), 'email' => $account->getEmail());
            $template = $this->_getConfigHelper()->getEmailConfig('refer_template');

            $store = Mage::app()->getStore();
            $mailTemplate = Mage::getModel('core/email_template')
                    ->setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()));
            foreach ($contacts as $contact) {
                if (strpos($contact, '@') === false)
                    continue;
                $name = '';
                if (strpos($contact, '<') !== false) {
                    $name = substr($contact, 0, strpos($contact, '<'));
                    $contact = substr($contact, strpos($contact, '<') + 1);
                }
                $email = rtrim(rtrim($contact), '>');
                if (!$name) {
                    $emailExtract = explode('@', $email);
                    $name = $emailExtract[0];
                }
                $subject = str_replace(array('{{friend_name}}', '{{friend_email}}'), array($name, $email), $data['email_subject']);
                $content = str_replace(array('{{friend_name}}', '{{friend_email}}'), array($name, $email), $data['email_content']);
                try {
                    $mailTemplate->sendTransactional(
                            $template, $sender, $email, $name, array(
                        'store' => $store,
                        'contact_name' => $name,
                        'sender_name' => $account->getName(),
                        'subject' => $subject,
                        'content' => $content,
                            )
                    );
                    $totalSent++;
                } catch (Exception $e) {
                    
                }
            }
            $translate->setTranslateInline(true);
            
            if ($totalSent) {
                $result['success'] = 1;
                $result['message'] = $this->__('Total %s email(s) have been sent successfully!', $totalSent);
                return $this->responseJson($result);
            } else {
                $result['error'] = 1;
                $result['message'] = $this->__('No email has been sent.');
                return $this->responseJson($result);
            }
        }
    }
    
    public function responseJson($result) {
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
