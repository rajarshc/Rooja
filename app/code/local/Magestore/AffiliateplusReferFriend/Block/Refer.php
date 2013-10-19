<?php

class Magestore_AffiliateplusReferFriend_Block_Refer extends Mage_Core_Block_Template
{
    /**
     * get Helper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    public function _getHelper() {
        return Mage::helper('affiliateplus/config');
    }

    public function getReferDescription() {
        return $this->_getHelper()->getReferConfig('refer_description');
    }

    public function getSharingDescription() {
        return $this->_getHelper()->getReferConfig('sharing_description');
    }

    /** Personal URL */
    public function getPersonalUrl() {
        if (!$this->hasData('personal_url')) {
            if ($personalPath = $this->getPersonalPath())
                $this->setData('personal_url', $this->getUrl(null, array(
                            '_direct' => $personalPath,
                            '_store_to_url' => (Mage::app()->getStore()->getId() != Mage::app()->getDefaultStoreView()->getId()),
                        )));
            else
                $this->setData('personal_url', Mage::helper('affiliateplus/url')->addAccToUrl($this->getBaseUrl()));
        }
        return $this->getData('personal_url');
    }

    public function getPrefixUrl() {
        $prefixurl = str_replace(" ", "", $this->getBaseUrl() . $this->_getHelper()->getReferConfig('url_prefix'));
        return $prefixurl;
    }

    public function getSuffixUrl() {
        return $this->getUrl(null, array('_store_to_url' => (Mage::app()->getStore()->getId() != Mage::app()->getDefaultStoreView()->getId())));
    }

    public function getCustomUrl() {
        if (!$this->hasData('custom_url')) {
            $this->setData('custom_url', Mage::getSingleton('core/session')->getAffilateCustomUrl());
            Mage::getSingleton('core/session')->setAffilateCustomUrl(null);
        }
        return $this->getData('custom_url');
    }

    /** Email */
    public function getAccount() {
        return Mage::getSingleton('affiliateplus/session')->getAccount();
    }

    public function getAccountEmail() {
        return $this->getAccount()->getEmail();
    }

    public function getDefaultEmailSubject() {
        return $this->_getHelper()->getReferConfig('email_subject');
    }

    public function getDefaultEmailContent() {
        $content = $this->_getHelper()->getReferConfig('email_content');
        if ($this->getPersonalPath()) {
            $personalUrl = $this->getPersonalUrl();
        } else {
            $personalUrl = Mage::helper('affiliateplus/url')->addAccToUrl(
                $this->getUrl(null, array('_query' => array('src' => 'email')))
            );
        }
        return str_replace(
                        array(
                    '{{store_name}}',
                    '{{personal_url}}',
                    '{{account_name}}'
                        ), array(
                    Mage::app()->getStore()->getFrontendName(),
                    $personalUrl,
                    $this->getAccount()->getName()
                        ), $content
        );
    }

    public function getEmailFormData() {
        if (!$this->hasData('email_form_data')) {
            $data = Mage::getSingleton('core/session')->getEmailFormData();
            Mage::getSingleton('core/session')->setEmailFormData(null);
            $dataObj = new Varien_Object($data);
            $this->setData('email_form_data', $dataObj);
        }
        return $this->getData('email_form_data');
    }

    public function getJsonEmail() {
        $result = array(
            'yahoo' => $this->getUrl('*/*/yahoo'),
            'gmail' => $this->getUrl('*/*/gmail'),
                //'hotmail'	=> $this->getUrl('*/*/hotmail'),
        );
        return Zend_Json::encode($result);
    }

    public function getDefaultSharingContent() {
        $content = $this->_getHelper()->getReferConfig('sharing_message');
        return str_replace(
                        array(
                    '{{store_name}}',
                    '{{personal_url}}'
                        ), array(
                    Mage::app()->getStore()->getFrontendName(),
                    $this->getPersonalUrl()
                        ), $content
        );
    }
    
    public function getDefaultTwitterContent() {
        $content = $this->_getHelper()->getReferConfig('twitter_message');
        return str_replace(
            array(
                '{{store_name}}',
                '{{personal_url}}'
            ), array(
                Mage::app()->getStore()->getFrontendName(),
                $this->getPersonalUrl()
            ), $content
        );
    }

    /** Facebook */
    public function getFbLoginUrl() {
        return $this->getUrl('*/*/facebook', array('auth' => 1));
        try {
            if (!class_exists('Facebook'))
                require_once(Mage::getBaseDir('lib') . DS . 'Facebookv3' . DS . 'facebook.php');
            $facebook = new Facebook(array(
                        'appId' => $this->_getHelper()->getReferConfig('fbapp_id'),
                        'secret' => $this->_getHelper()->getReferConfig('fbapp_secret'),
                        'cookie' => true
                    ));
            $loginUrl = $facebook->getLoginUrl(array(
                'display' => 'popup',
                'redirect_uri' => $this->getUrl('*/*/facebook', array('auth' => 1)),
                'scope' => 'publish_stream,email',
                    ));
            return $loginUrl;
        } catch (Exception $e) {
            
        }
    }

    public function getActiveTab() {
        if ($tab = $this->getRequest()->getParam('tab')) {
            if (in_array($tab, array('email', 'facebook', 'twitter', 'google'))) {
                return "affiliate-opc-$tab-content";
            }
        }
        return '';
    }

    public function isActiveTab($_tab) {
        if ($tab = $this->getRequest()->getParam('tab')) {
            return ($tab == $_tab);
        }
        return false;
    }

    /**
     * get Traffic source statistic
     *
     * @return array
     */
    public function getTrafficSources() {
        $accountId = $this->getAccount()->getId();
        $referers = Mage::getResourceModel('affiliateplus/action_collection')->addFieldToFilter('account_id', $accountId);
        $referers->getSelect()
                ->columns(array('total_clicks' => 'SUM(totals)'))
                ->group(array('referer', 'landing_page', 'store_id'));
        $trafficSources = array('facebook' => 0, 'twitter' => 0, 'google' => 0, 'email' => 0);
        foreach ($referers as $refer) {
            $referer = $refer->getData('referer');
            if ($this->getPersonalPath() && strpos($this->getPersonalPath(), trim($refer->getData('landing_page'), '/')) === false)
                continue;
            if (strpos($referer, 'facebook.com') !== false) {
                $trafficSources['facebook'] += $refer->getData('total_clicks');
            } elseif (strpos($referer, 'plus.url.google.com') !== false) {
                $trafficSources['google'] += $refer->getData('total_clicks');
            } elseif (strpos($referer, 't.co') !== false || strpos($referer, 'twitter.com') !== false) {
                $trafficSources['twitter'] += $refer->getData('total_clicks');
            } elseif ($this->getPersonalPath()) {
                $trafficSources['email'] += $refer->getData('total_clicks');
            } elseif (strpos($referer, 'mail') !== false) {
                $trafficSources['email'] += $refer->getData('total_clicks');
            }
        }
        return $trafficSources;
    }

    public function getPersonalPath() {
        if (!$this->hasData('personal_path')) {
            $idPath = 'affiliateplus/' . Mage::app()->getStore()->getId() . '/' . $this->getAccount()->getId();
            $rewrite = Mage::getModel('core/url_rewrite')->load($idPath, 'id_path');
            if ($rewrite->getId())
                $this->setData('personal_path', $rewrite->getRequestPath());
            else
                $this->setData('personal_path', false);
        }
        return $this->getData('personal_path');
    }
}
