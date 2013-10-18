<?php
class Magestore_Affiliateplus_CheckoutController extends Mage_Core_Controller_Front_Action
{
	public function creditPostAction()
    {
        if ($this->getRequest()->isPost()) {
            $session = Mage::getSingleton('checkout/session');
            if ($this->getRequest()->getPost('affiliateplus_credit')) {
                $session->setUseAffiliateCredit(true);
                $session->setAffiliateCredit(floatval($this->getRequest()->getPost('credit_amount')));
            } else {
                $session->setUseAffiliateCredit(false);
            }
            $session->addSuccess($this->__('Your affiliate store credit has been applied successfully'));
        }
        $this->_redirect('checkout/cart');
    }
    
    /**
     * get Account helper
     *
     * @return Magestore_Affiliateplus_Helper_Account
     */
    protected function _getAccountHelper() {
        return Mage::helper('affiliateplus/account');
    }
    
    public function changeUseCreditAction()
    {
        if ($this->_getAccountHelper()->disableStoreCredit()) {
            return ;
        }
        $session = Mage::getSingleton('checkout/session');
        $session->setUseAffiliateCredit($this->getRequest()->getParam('affiliatepluscredit'));
        if ($session->getAffiliateCredit() < 0.0001) {
            $session->setAffiliateCredit(10000000000);
        }
        $result = array();
        $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
        $session->getQuote()->collectTotals()->save();
        if ($updatepayment xor ($session->getQuote()->getGrandTotal() < 0.001)) {
            $result['updatepayment'] = 1;
        } else {
            $result['html']	= $this->getLayout()->createBlock('affiliateplus/credit_form')->toHtml();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function changeCreditAction()
    {
        if ($this->_getAccountHelper()->disableStoreCredit()) {
            return ;
        }
        $session = Mage::getSingleton('checkout/session');
        $amount = floatval($this->getRequest()->getParam('affiliatepluscredit'));
        if ($amount < 0) $amount = 0;
        $session->setAffiliateCredit($amount);
        $result = array();
        $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
        $session->getQuote()->collectTotals()->save();
        if ($updatepayment xor ($session->getQuote()->getGrandTotal() < 0.001)) {
            $result['updatepayment'] = 1;
        } else {
            $result['html']	= $this->getLayout()->createBlock('affiliateplus/credit_form')->toHtml();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
