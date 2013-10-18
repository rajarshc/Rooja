<?php

class Magestore_Affiliateplus_Model_Payment_Verify extends Mage_Core_Model_Abstract {
    
    const TEMPLATE_VERIFY_EMAIL = 'affiliateplus/email/verify_payment_email';
    const XML_PATH_EMAIL_IDENTITY = 'trans_email/ident_sales';

    public function _construct() {
        parent::_construct();
        $this->_init('affiliateplus/payment_verify');
    }
    
    public function loadExist($accountId, $field, $paymentMethod){
        $collection = $this->getCollection()
            ->addFieldToFilter('account_id', $accountId)
            ->addFieldToFilter('payment_method', $paymentMethod)
            ->addFieldToFilter('field', $field);
        if($collection->getSize())
            $this->load($collection->getFirstItem()->getId());
        return $this;
    }
    
    public function sendMailAuthentication($email, $method){
        //random code
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();
        $length = 6;
        $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $str = '';
        if($this->getId())
            $str = $this->getInfo();
        else{
            $count = strlen($charset);
            while ($length--) {
                $str .= $charset[mt_rand(0, $count-1)];
            }
        }
        $sendTo = array(
                'email' => $email,
                'name' => $account->getName(),
            );
        
        $store = Mage::app()->getStore();
        /*send authentication code to email*/
        $link = Mage::getUrl('*/*/verifyCode',array('account_id'=>$account->getId(),'payment_method'=>$method,'email'=>$email,'authentication_code'=>$str, 'from'=>'email'));
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $template = Mage::getStoreConfig(self::TEMPLATE_VERIFY_EMAIL, $store->getId());
        $sender = Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY,$store->getId());
        $mailSubject = 'Verify Email Payment';
        $mailTemplate = Mage::getModel('core/email_template');
        try{
            $mailTemplate->setEmailSubject($mailSubject)
                ->sendTransactional($template, $sender, $sendTo['email'],$sendTo['name'],array('store'=>$store,'code'=>$str,'link'=>$link,'name'=>$account->getName()),$store->getId())
                ;
            return $str;
        $translate->setTranslateInline(true);
        }  catch (Exception $e){
        }
        return ;
        /*edit send mail*/
    }


    public function isVerified(){
        if($this->getVerified() == 1)
            return true;
        return false;
    }
    
    public function verify($accountId, $field, $paymentMethod, $code = null){
        $collection = $this->getCollection()
            ->addFieldToFilter('account_id',$accountId)
            ->addFieldToFilter('payment_method',$paymentMethod)
            ->addFieldToFilter('field',$field);
        if($code)
            $collection->addFieldToFilter('info',$code);
        if($collection->getSize()){
            $model = $collection->getFirstItem();
            try{
            if($model->getVerified() == 2)
                $model->setVerified(1)
                    ->save();
                return true;
            }  catch (Exception $e){
                Mage::getSingleton('core/session')->addError($e->getMessage());
                return false;
            }
            
        }
        return false;
    }

}