<?php

class Magestore_Affiliatepluscoupon_Model_Observer {

    public function couponPostAction($observer) {
        if (!Mage::getStoreConfig('affiliateplus/coupon/enable'))
            return $this;

        $action = $observer->getEvent()->getControllerAction();
        $code = trim($action->getRequest()->getParam('coupon_code'));
        if (!$code)
            return $this;

        $session = Mage::getSingleton('checkout/session');

        $account = Mage::getModel('affiliatepluscoupon/coupon')->getAccountByCoupon($code);
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if (!$account->getId()) {
            if (!Mage::getStoreConfig('affiliateplus/coupon/parallel')
                    && $session->getData('affiliate_coupon_code')) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $this->clearAffiliateCookie();
            }
            return $this;
        } elseif ($account->getCustomerId() == $customerId) {
            $session->unsetData('affiliate_coupon_code');
            $session->unsetData('affiliate_coupon_data');
            $this->clearAffiliateCookie();
            return $this;
        }

        if ($action->getRequest()->getParam('remove') == 1) {
            if ($account->getCouponCode() == $session->getData('affiliate_coupon_code')) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $this->clearAffiliateCookie();
                $session->addSuccess(Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was canceled.', $account->getCouponCode()));
            } elseif (Mage::getStoreConfig('affiliateplus/coupon/parallel')) {
                return $this;
            }
        } else {
            $session->setData('affiliate_coupon_code', $account->getCouponCode());
            $session->setData('affiliate_coupon_data', array(
                'account_id' => $account->getId(),
                'program_id' => $account->getCouponPid(),
            ));
            $this->clearAffiliateCookie();

            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            if (!Mage::getStoreConfig('affiliateplus/coupon/parallel'))
                $quote->setCouponCode('');
            if ($account->getCouponPid()) {
                $program = Mage::getModel('affiliateplusprogram/program')->setStoreId(Mage::app()->getStore()->getId())->load($account->getCouponPid());
                if ($program->isAvailable()) {
                    $accountProgramCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                            ->addFieldToFilter('program_id', $account->getCouponPid())
                            ->addFieldToFilter('account_id', $account->getId())
                    ;
                    if ($accountProgramCollection->getSize())
                        $quote->collectTotals()->save();
                }
            }
            if ($account->getCouponPid() == 0) {
                // if (Mage::helper('affiliateplus/config')->getGeneralConfig('show_default')) {
                    $quote->collectTotals()->save();
                // }
            }
            $available = false;
            foreach ($quote->getAddressesCollection() as $address)
                if (!$address->isDeleted() && $address->getAffiliateplusDiscount()) {
                    $available = true;
                    break;
                }
            if ($available) {
                $session->addSuccess(Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was applied.', $code));
            } else {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $session->addError(Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" is not valid.', $code));
            }
        }
        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        $action->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
    }

    /* Magic Add event controller_action_predispatch_onestepcheckout_index_add_coupon 15/11/2012 */
    
    public function quoteGetCouponCode($observer) {
        $url_current = Mage::helper('core/url')->getCurrentUrl();
        $url_save_address = Mage::getUrl('onestepcheckout/index/save_address/');
        if ($url_current == $url_save_address) {
            $quote = $observer->getEvent()->getQuote();
            $quote->collectTotals()->save();
        }
        return $this;
    }

    public function couponPostActionOneStep($observer) {
        if (!Mage::getStoreConfig('affiliateplus/coupon/enable'))
            return $this;

        $action = $observer->getEvent()->getControllerAction();
        $code = trim($action->getRequest()->getParam('coupon_code'));
        if (!$code)
            return $this;

        $session = Mage::getSingleton('checkout/session');

        $account = Mage::getModel('affiliatepluscoupon/coupon')->getAccountByCoupon($code);
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if (!$account->getId()) {
            if (!Mage::getStoreConfig('affiliateplus/coupon/parallel')
                    && $session->getData('affiliate_coupon_code')) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $this->clearAffiliateCookie();
            }
            if ($action->getRequest()->getParam('remove') != 1) {
                return $this;
            }  elseif(!Mage::getStoreConfig('affiliateplus/coupon/parallel')) {
                return $this;
            }
        } elseif ($account->getCustomerId() == $customerId) {
            $session->unsetData('affiliate_coupon_code');
            $session->unsetData('affiliate_coupon_data');
            $this->clearAffiliateCookie();
            return $this;
        }

        if ($action->getRequest()->getParam('remove') == 1) {
            if ($account->getCouponCode() == $session->getData('affiliate_coupon_code')) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $quote = Mage::getSingleton('checkout/cart')->getQuote();
                $quote->collectTotals()
                        ->save();
                if($quote->getCouponCode())
                    $error = TRUE;
                $this->clearAffiliateCookie();
                $message = Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was canceled.', $account->getCouponCode());
            } elseif (Mage::getStoreConfig('affiliateplus/coupon/parallel')) {
                //return $this;
                $quote = $session->getQuote();
                if ($quote->getCouponCode()) {
                    return $this;
                } else {
                    $quote->collectTotals()->save();
                    $error = true;
                }
            }
        } else {
            $error = false;
            $session->setData('affiliate_coupon_code', $account->getCouponCode());
            $session->setData('affiliate_coupon_data', array(
                'account_id' => $account->getId(),
                'program_id' => $account->getCouponPid(),
            ));
            $this->clearAffiliateCookie();

            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            if (!Mage::getStoreConfig('affiliateplus/coupon/parallel'))
                $quote->setCouponCode('');
            if ($account->getCouponPid()) {
                $program = Mage::getModel('affiliateplusprogram/program')->setStoreId(Mage::app()->getStore()->getId())->load($account->getCouponPid());
                if ($program->isAvailable()) {
                    $accountProgramCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                            ->addFieldToFilter('program_id', $account->getCouponPid())
                            ->addFieldToFilter('account_id', $account->getId())
                    ;
                    if ($accountProgramCollection->getSize())
                        $quote->collectTotals()->save();
                }
            }
            if ($account->getCouponPid() == 0) {
                // if (Mage::helper('affiliateplus/config')->getGeneralConfig('show_default')) {
                    $quote->collectTotals()->save();
                // }
            }
            
            $available = false;
            foreach ($quote->getAddressesCollection() as $address)
                if (!$address->isDeleted() && $address->getAffiliateplusDiscount()) {
                    $available = true;
                    break;
                }
            if ($available) {
                $message = Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was applied.', $code);
            } else {
                $error = true;
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $message = Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" is not valid.', $code);
            }
        }

        $layout = $observer->getEvent()->getControllerAction()->getLayout();
        $update = $layout->getUpdate();
        $update->load('onestepcheckout_onestepcheckout_review');
        $layout->unsetBlock('shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        $result = array(
            'error' => $error,
            'message' => $message,
            'review_html' => $output
        );

        $action->getResponse()->setBody(Zend_Json::encode($result));
        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        return $result;
    }

    /* end onestepcheckout_index_add_coupon */

    public function onestepcheckoutIndexLoadTotals($observer) {
        $action = $observer->getEvent()->getControllerAction();
        $shippingMethod = $action->getRequest()->getPost('shipping_method', '');
        if (empty($shippingMethod)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $rate = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
        //$this->getOnepage()->getQuote()->collectTotals()->save();
        return array();
    }

    public function getAffiliateInfo($observer) {
        $session = Mage::getSingleton('checkout/session');
        $affilateData = $session->getData('affiliate_coupon_data');
        if (!$affilateData || !is_array($affilateData) || !isset($affilateData['program_id']))
            return $this;

        $account = Mage::getModel('affiliateplus/account')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($affilateData['account_id']);
        if (!$account->getId()
                || $account->getStatus() != 1
                || $account->getId() == Mage::helper('affiliateplus/account')->getAccount()->getId()) {
            return $this;
        }

        if ($affilateData['program_id']) {
            $program = Mage::getModel('affiliateplusprogram/program')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($affilateData['program_id']);
            if (!$program->isAvailable() || !$program->getUseCoupon())
                return $this;
            $account->setUsingProgram($program);
        }
        $info = array();
        $account->setUsingCoupon(true);
        $info[$account->getIdentifyCode()] = array(
            'index' => 1,
            'code' => $account->getIdentifyCode(),
            'account' => $account,
        );
        $infoObj = $observer->getEvent()->getInfoObj();
        $infoObj->setInfo($info);
    }

    public function clearAffiliateCookie() {
        $cookie = Mage::getSingleton('core/cookie');
        for ($index = intval($cookie->get('affiliateplus_map_index')); $index > 0; $index--)
            $cookie->delete("affiliateplus_account_code_$index");
        $cookie->delete('affiliateplus_map_index');
        return $this;
    }

    public function addAccountTab($observer) {
        if (!$observer->getEvent()->getId())
            return $this;
        $form = $observer->getEvent()->getForm();
        $form->addTabAfter('affiliateplus_coupon_codes', array(
            'label' => Mage::helper('affiliatepluscoupon')->__('Coupon Code'),
            'title' => Mage::helper('affiliatepluscoupon')->__('Coupon Code'),
            'url' => $form->getUrl('affiliatepluscouponadmin/adminhtml_account/coupons', array('_current' => true)),
            'class' => 'ajax',
                ), 'form_section');
    }

    public function afterSaveAccount($observer) {
        $data = $observer->getEvent()->getPostData();
        if (!isset($data['account_coupon']))
            return $this;
        if (!$data['account_coupon'])
            return $this;

        $programCoupons = array();
        parse_str(urldecode($data['account_coupon']), $programCoupons);
        if (!count($programCoupons))
            return $this;

        $account = $observer->getEvent()->getAccount();
        $accountId = $account->getId();
        $coupon = Mage::getModel('affiliatepluscoupon/coupon')->setCurrentAccountId($accountId);

        foreach ($programCoupons as $pId => $enCoded) {
            $coupon->setId(null)->loadByProgram($pId);
            if (!$coupon->getId())
                continue;
            $codeArr = array();
            $code = '';
            parse_str(base64_decode($enCoded), $codeArr);
            if (isset($codeArr['coupon_code']))
                $code = $codeArr['coupon_code'];
            if ($coupon->getCouponCode() == $code || !$code)
                continue;
            try {
                $coupon->setCouponCode($code)->save();
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addWarning($e->getMessage());
            }
        }
    }

    public function editProgramForm($observer) {
        $fieldset = $observer->getEvent()->getFieldset();
        $fieldset->addField('coupon_separator', 'text', array(
            'label'     => Mage::helper('affiliatepluscoupon')->__('Affiliate coupon'),
        ))->setRenderer(Mage::app()->getLayout()->createBlock('affiliateplus/adminhtml_field_separator'));
        $fieldset->addField('use_coupon', 'select', array(
            'label' => Mage::helper('affiliatepluscoupon')->__('Use coupon'),
            'name' => 'use_coupon',
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'onchange' => 'changeCouponOption(this)',
            'after_element_html' => '<script type="text/javascript">
				function changeCouponOption(el){
					if (el.value == 1)
						$(\'affiliateplusprogram_coupon_pattern\').parentNode.parentNode.show();
					else
						$(\'affiliateplusprogram_coupon_pattern\').parentNode.parentNode.hide();
				}
				Event.observe(window,\'load\',function(){
					changeCouponOption($(\'affiliateplusprogram_use_coupon\'));
				});
			</script>',
        ));
        $fieldset->addField('coupon_pattern', 'text', array(
            'label' => Mage::helper('affiliatepluscoupon')->__('Coupon code pattern'),
            'name' => 'coupon_pattern',
            'note' => Mage::helper('affiliatepluscoupon')->__('Used to generate coupon code for Affiliates. Pattern examples:<br/><strong>[A.8] : 8 alpha chars<br/>[N.4] : 4 numerics<br/>[AN.6] : 6 alphanumeric<br/>AFFILIATE-[A.4]-[AN.6] : AFFILIATE-ADFA-12NF0O</strong>'),
        ));
    }

    public function addFieldTransactionForm($observer) {
        $form = $observer->getEvent()->getForm();
        $transactionData = $form->getTransationData();
        if (!isset($transactionData['coupon_code']) || !$transactionData['coupon_code'])
            return $this;
        $fieldset = $observer->getEvent()->getFieldset();
        $fieldset->addField('coupon_code', 'note', array(
            'label' => Mage::helper('affiliatepluscoupon')->__('Coupon Code'),
            'text' => $transactionData['coupon_code'],
        ));
    }

    /* David */
    public function beforeToHtml($observer) {
        $block = $observer['block'];
        if ($block instanceof Mage_Checkout_Block_Onepage_Review
            || $block instanceof Mage_Checkout_Block_Cart_Coupon
        ) {
            $session = Mage::getSingleton('checkout/session');
            $quote = $session->getQuote();
            $affCode = $session->getData('affiliate_coupon_code');
            if (!$quote->getCouponCode() && $affCode) {
                $quote->setCouponCode($affCode);
                $session->setData('affiliate_coupon_code_flag',true);
            }
        }
    }
    
    public function afterToHtml($observer) {
        $block = $observer['block'];
        if ($block instanceof Mage_Checkout_Block_Onepage_Review
            || $block instanceof Mage_Checkout_Block_Cart_Coupon
        ) {
            $session = Mage::getSingleton('checkout/session');
            if ($session->getData('affiliate_coupon_code_flag')) {
                $session->unsetData('affiliate_coupon_code_flag');
                $session->getQuote()->setCouponCode('');
            }
        }
    }
    
    public function couponPostDistpatchActionOneStep($observer) {
        if (!Mage::getStoreConfig('affiliateplus/coupon/enable'))
            return $this;
        $session = Mage::getSingleton('checkout/session');
        $action = $observer->getEvent()->getControllerAction();
        if ($session->getData('affiliate_coupon_code')) {
            $result = Zend_Json::decode($action->getResponse()->getBody());
            if ($action->getRequest()->getParam('remove')) {
                $result['error'] = true;
            } else {
                $result['error'] = false;
            }
            $action->getResponse()->setBody(Zend_Json::encode($result));
        }
    }
}
