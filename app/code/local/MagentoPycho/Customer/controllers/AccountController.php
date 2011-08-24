<?php
/**
 * This class overrides the _loginPostRedirect method of default AccountController so as to tweak the redirection URL.
 * @category   MagentoPycho
 * @package    MagentoPycho_Customer
 * @author     developer@magepsycho.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require_once 'Mage/Customer/controllers/AccountController.php';
class MagentoPycho_Customer_AccountController extends Mage_Customer_AccountController
{
         
   /**
     * Overriding defaults redirect URL 
     * Define target URL and redirect customer after logging in
     */
    protected function _loginPostRedirect()
    {    
        $session                         = $this->_getSession();        
        $custom_login_redirect_flag      = Mage::getStoreConfig('mpcustomer/customloginredirect/active');
        $custom_login_redirect_url       = trim(Mage::getStoreConfig('mpcustomer/customloginredirect/url'));
        
        if(1 == $custom_login_redirect_flag){
            if($session->isLoggedIn()){
                $filtered_url = Mage::getUrl( ltrim( str_replace(Mage::getBaseUrl(), '', $custom_login_redirect_url), '/') );
                $session->setBeforeAuthUrl($filtered_url);
            }else{
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        }else{
            if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl() ) {

                // Set default URL to redirect customer to
                $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
    
                // Redirect customer to the last page visited after logging in
                if ($session->isLoggedIn())
                {
                    if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
                        if ($referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME)) {
                            $referer = Mage::helper('core')->urlDecode($referer);
                            if ($this->_isUrlInternal($referer)) {
                                $session->setBeforeAuthUrl($referer);
                            }
                        }
                    }
                } else {
                    $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
                }
            }
        }
        
        $this->_redirectUrl($session->getBeforeAuthUrl(true));        
    }
}
