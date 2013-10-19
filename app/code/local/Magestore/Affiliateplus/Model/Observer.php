<?php

class Magestore_Affiliateplus_Model_Observer {

    /**
     * get Config Helper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    protected function _getConfigHelper() {
        return Mage::helper('affiliateplus/config');
    }

    public function productGetFinalPrice($observer) {
        if ($this->_getConfigHelper()->getDiscountConfig('type_discount') == 'cart')
            return $this;
        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        $account = '';
        foreach ($affiliateInfo as $info)
            if ($info['account']) {
                $account = $info['account'];
                break;
            }
        if (!$account)
            return $this;
        $product = $observer['product'];
        $product->setData('final_price', $this->_getFinalPrice($product, $product->getData('final_price')));
    }

    public function productListCollection($observer) {
        if ($this->_getConfigHelper()->getDiscountConfig('type_discount') == 'cart')
            return $this;
        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        $account = '';
        foreach ($affiliateInfo as $info)
            if ($info['account']) {
                $account = $info['account'];
                break;
            }
        if (!$account)
            return $this;
        $productCollection = $observer['collection'];
        foreach ($productCollection as $product)
            $product->setData('final_price', $this->_getFinalPrice($product, $product->getData('final_price')));
    }

    protected function _getFinalPrice($product, $price) {
        $discountedObj = new Varien_Object(array(
                    'price' => $price,
                    'discounted' => false,
                ));

        Mage::dispatchEvent('affiliateplus_product_get_final_price', array(
            'product' => $product,
            'discounted_obj' => $discountedObj,
        ));

        if ($discountedObj->getDiscounted())
            return $discountedObj->getPrice();
        $price = $discountedObj->getPrice();

        $discountType  = $this->_getConfigHelper()->getDiscountConfig('discount_type');
        $discountValue = $this->_getConfigHelper()->getDiscountConfig('discount');
        if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
            if ($this->_getConfigHelper()->getDiscountConfig('use_secondary')) {
                $discountType  = $this->_getConfigHelper()->getDiscountConfig('secondary_type');
                $discountValue = $this->_getConfigHelper()->getDiscountConfig('secondary_discount');
            }
        }
        if ($discountType == 'fixed'
            || $discountType == 'cart_fixed'
        ) {
            $price -= floatval($discountValue);
        } elseif ($discountType == 'percentage') {
            $price -= floatval($discountValue) / 100 * $price;
        }
        if ($price < 0)
            return 0;
        return $price;
    }

    public function controllerActionPredispatch($observer) {
        $controller = $observer['controller_action'];
        $request = $controller->getRequest();

        /* Add event before run dispatch of affiliate system - added by David (01/11) */
        Mage::dispatchEvent('affiliateplus_controller_action_predispatch', array(
            'request' => $request
        ));

        /* magic add call funtion saveClickAction 23/10/2012 */
        $this->saveClickAction($observer);
        /* end */

        $accountCode = $request->getParam('acc');

        if (!$accountCode && $request->getParam('df08b0441bac900')) {
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');
            $write = $resource->getConnection('core_write');
            try {
                $select = $read->select()
                        ->from($resource->getTableName('affiliate_referral'), array('customer_id'))
                        ->where("identify_code=?", trim($request->getParam('df08b0441bac900')));
                $result = $read->fetchRow($select);
                $oldCustomerId = $result['customer_id'];
                if ($oldCustomerId)
                    $accountCode = Mage::getModel('affiliateplus/account')
                            ->loadByCustomerId($oldCustomerId)
                            ->getIdentifyCode();
            } catch (Exception $e) {
                
            }
        }

        if (!$accountCode)
            return $this;

        if ($account = Mage::getSingleton('affiliateplus/session')->getAccount())
            if ($account->getIdentifyCode() == $accountCode)
                return $this;

        /* Magic 19/10/2012 */

        $account = Mage::getModel('affiliateplus/account')->loadByIdentifyCode($accountCode);
        if (!$account->getId())
            return $this;
        $storeId = Mage::app()->getStore()->getId();
        if (!$storeId)
            return $this;
        
        /* David - remove storage tracking to referer table
        $ipAddress = $request->getClientIp();
        $refererModel = Mage::getModel('affiliateplus/referer');

        $refererCollection = $refererModel->getCollection()
                ->addFieldToFilter('account_id', $account->getId());
        if (!in_array($ipAddress, $refererCollection->getIpListArray())) {
            $account->setUniqueClicks($account->getUniqueClicks() + 1);
            try {
                $account->save();
            } catch (Exception $e) {
                
            }
        }

        $account->setStoreId($storeId)->load($account->getId());
        $refererCollection->addFieldToFilter('store_id', $storeId);
        if (!in_array($ipAddress, $refererCollection->getIpListArray()))
            if ($account->getUniqueClicksInStore())
                $account->setUniqueClicks($account->getUniqueClicks() + 1);
            else
                $account->setUniqueClicks(1);
        $account->setTotalClicks($account->getTotalClicks() + 1);
        try {
            $account->save();
        } catch (Exception $e) {
            
        }

        $httpReferrerInfo = parse_url($request->getServer('HTTP_REFERER'));
        $referer = isset($httpReferrerInfo['host']) ? $httpReferrerInfo['host'] : '';
        $refererModel->loadExistReferer($account->getId(), $referer, $storeId, $request->getOriginalRequest()->getPathInfo());
        //Zend_Debug::dump($refererModel->getData());die('1');
        Mage::dispatchEvent('affiliateplus_referrer_load_existed', array(
            'referrer_model' => $refererModel,
            'controller_action' => $controller,
        ));

        try {
            $refererModel->setIpAddress($ipAddress)->save();
        } catch (Exception $e) {
            
        }
        */

        /*
         * end
         */
        $expiredTime = $this->_getConfigHelper()->getGeneralConfig('expired_time');
        $cookie = Mage::getSingleton('core/cookie');
        if ($expiredTime)
            $cookie->setLifeTime(intval($expiredTime) * 86400);

        $current_index = $cookie->get('affiliateplus_map_index');

        $addCookie = new Varien_Object(array(
                    'existed' => false,
                ));
        for ($i = intval($current_index); $i > 0; $i--) {
            if ($cookie->get("affiliateplus_account_code_$i") == $accountCode) {
                $addCookie->setExisted(true);
                $addCookie->setIndex($i);
                Mage::dispatchEvent('affiliateplus_controller_action_predispatch_add_cookie', array(
                    'request' => $request,
                    'add_cookie' => $addCookie,
                    'cookie' => $cookie,
                ));
                if ($addCookie->getExisted()) {
                    // change latest account
                    $curI = intval($current_index);
                    for ($j = $i; $j < $curI; $j++) {
                        $cookie->set(
                            "affiliateplus_account_code_$j",
                            $cookie->get("affiliateplus_account_code_".intval($j+1))
                        );
                    }
                    $cookie->set("affiliateplus_account_code_$curI", $accountCode);
                    return $this;
                }
            }
        }
        $current_index = $current_index ? intval($current_index) + 1 : 1;
        $cookie->set('affiliateplus_map_index', $current_index);

        $cookie->set("affiliateplus_account_code_$current_index", $accountCode);

        $cookieParams = new Varien_Object(array(
                    'params' => array(),
                ));
        Mage::dispatchEvent('affiliateplus_controller_action_predispatch_observer', array(
            'controller_action' => $controller,
            'cookie_params' => $cookieParams,
            'cookie' => $cookie,
        ));

        foreach ($cookieParams->getParams() as $key => $value)
            $cookie->set("affiliateplus_$key" . "_$current_index", $value);

        /* Magic comment 19/10/2012 and put upward  */
        /*
          $account = Mage::getModel('affiliateplus/account')->loadByIdentifyCode($accountCode);
          if (!$account->getId())
          return $this;
          $storeId = Mage::app()->getStore()->getId();
          if (!$storeId)
          return $this;
          $ipAddress = $request->getClientIp();
          $refererModel = Mage::getModel('affiliateplus/referer');

          $refererCollection = $refererModel->getCollection()
          ->addFieldToFilter('account_id', $account->getId());
          if (!in_array($ipAddress, $refererCollection->getIpListArray())) {
          $account->setUniqueClicks($account->getUniqueClicks() + 1);
          try {
          $account->save();
          } catch (Exception $e) {

          }
          }

          $account->setStoreId($storeId)->load($account->getId());
          $refererCollection->addFieldToFilter('store_id', $storeId);
          if (!in_array($ipAddress, $refererCollection->getIpListArray()))
          if ($account->getUniqueClicksInStore())
          $account->setUniqueClicks($account->getUniqueClicks() + 1);
          else
          $account->setUniqueClicks(1);
          $account->setTotalClicks($account->getTotalClicks() + 1);
          try {
          $account->save();
          } catch (Exception $e) {

          }

          $httpReferrerInfo = parse_url($request->getServer('HTTP_REFERER'));
          $referer = isset($httpReferrerInfo['host']) ? $httpReferrerInfo['host'] : '';
          $refererModel->loadExistReferer($account->getId(), $referer, $storeId, $request->getOriginalRequest()->getPathInfo());
          //Zend_Debug::dump($refererModel->getData());die('1');
          Mage::dispatchEvent('affiliateplus_referrer_load_existed', array(
          'referrer_model' => $refererModel,
          'controller_action' => $controller,
          ));

          try {
          $refererModel->setIpAddress($ipAddress)->save();
          } catch (Exception $e) {

          }
         */
        return $this;
    }

    public function orderPlaceAfter($observer) {
        $order = $observer['order'];
        // check to run this function 1 time for 1 order
        if (Mage::getSingleton('core/session')->getData("affiliateplus_order_placed_" . $order->getId())) {
            return $this;
        }
        Mage::getSingleton('core/session')->setData("affiliateplus_order_placed_" . $order->getId(), true);
        
        // Use Store Credit to Checkout
        if ($baseAmount = $order->getBaseAffiliateCredit()) {
            $session = Mage::getSingleton('checkout/session');
            $session->setUseAffiliateCredit('');
            $session->setAffiliateCredit(0);
            
            $account = Mage::getSingleton('affiliateplus/session')->getAccount();
            $payment = Mage::getModel('affiliateplus/payment')
                ->setPaymentMethod('credit')
                ->setAmount(-$baseAmount)
                ->setAccountId($account->getId())
                ->setAccountName($account->getName())
                ->setAccountEmail($account->getEmail())
                ->setRequestTime(now())
                ->setStatus(3)
                ->setIsRequest(1)
                ->setIsPayerFee(0)
                ->setData('is_created_by_recurring', 1)
                ->setData('is_refund_balance', 1);
            if (Mage::helper('affiliateplus/config')->getSharingConfig('balance') == 'store') {
                $payment->setStoreIds($order->getStoreId());
            }
            $paymentMethod = $payment->getPayment();
            $paymentMethod->addData(array(
                'order_id'  => $order->getId(),
                'order_increment_id'    => $order->getIncrementId(),
                'base_paid_amount'  => -$baseAmount,
                'paid_amount'       => -$order->getAffiliateCredit(),
            ));
            try {
                $payment->save();
                $paymentMethod->savePaymentMethodInfo();
            } catch (Exception $e) {}
        }
        
        if (!$order->getBaseSubtotal()) {
            return $this;
        }
        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        $account = '';
        foreach ($affiliateInfo as $info)
            if ($info['account']) {
                $account = $info['account'];
                break;
            }

        if ($account && $account->getId()) {
            // Log affiliate tracking referal - only when has sales
            if ($this->_getConfigHelper()->getCommissionConfig('life_time_sales')) {
                $tracksCollection = Mage::getResourceModel('affiliateplus/tracking_collection');
                if ($order->getCustomerId()) {
                    $tracksCollection->getSelect()
                        ->where("customer_id = {$order->getCustomerId()} OR customer_email = ?",
                            $order->getCustomerEmail());
                } else {
                    $tracksCollection->addFieldToFilter('customer_email', $order->getCustomerEmail());
                }
                if (!$tracksCollection->getSize()) {
                    try {
                        Mage::getModel('affiliateplus/tracking')->setData(array(
                            'account_id'    => $account->getId(),
                            'customer_id'   => $order->getCustomerId(),
                            'customer_email'=> $order->getCustomerEmail(),
                            'created_time'  => now()
                        ))->save();
                    } catch (Exception $e) {
                    }
                }
            }
            
            $baseDiscount = $order->getBaseAffiliateplusDiscount();
            //$maxCommission = $order->getBaseGrandTotal() - $order->getBaseShippingAmount();
            // Before calculate commission
            $commissionObj = new Varien_Object(array(
                        'commission' => 0,
                        'default_commission' => true,
                        'order_item_ids' => array(),
                        'order_item_names' => array(),
                        'commission_items' => array(),
                        'extra_content' => array(),
                        'tier_commissions' => array(),
                    ));
            Mage::dispatchEvent('affiliateplus_calculate_commission_before', array(
                'order' => $order,
                'affiliate_info' => $affiliateInfo,
                'commission_obj' => $commissionObj,
            ));

            $commissionType  = $this->_getConfigHelper()->getCommissionConfig('commission_type');
            $commissionValue = floatval($this->_getConfigHelper()->getCommissionConfig('commission'));
            if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                if ($this->_getConfigHelper()->getCommissionConfig('use_secondary')) {
                    $commissionType  = $this->_getConfigHelper()->getCommissionConfig('secondary_type');
                    $commissionValue = floatval($this->_getConfigHelper()->getCommissionConfig('secondary_commission'));
                }
            }
            $commission = $commissionObj->getCommission();
            $orderItemIds = $commissionObj->getOrderItemIds();
            $orderItemNames = $commissionObj->getOrderItemNames();
            $commissionItems = $commissionObj->getCommissionItems();
            $extraContent = $commissionObj->getExtraContent();
            $tierCommissions = $commissionObj->getTierCommissions();

            $defaultItemIds = array();
            $defaultItemNames = array();
            $defaultAmount = 0;
            $defCommission = 0;
            if ($commissionValue && $commissionObj->getDefaultCommission()) {
                if ($commissionType == 'percentage') {
                    if ($commissionValue > 100)
                        $commissionValue = 100;
                    if ($commissionValue < 0)
                        $commissionValue = 0;
                }

                foreach ($order->getAllItems() as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if (in_array($item->getId(), $commissionItems)) {
                        continue;
                    }
                    
                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        // $childHasCommission = false;
                        foreach ($item->getChildrenItems() as $child) {
                            if ($this->_getConfigHelper()->getCommissionConfig('affiliate_type') == 'profit')
                                $baseProfit = $child->getBasePrice() - $child->getBaseCost();
                            else
                                $baseProfit = $child->getBasePrice();
                            $baseProfit = $child->getQtyOrdered() * $baseProfit - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                            if ($baseProfit <= 0)
                                continue;

                            // $childHasCommission = true;
                            if ($commissionType == 'fixed')
                                $defaultCommission = min($child->getQtyOrdered() * $commissionValue, $baseProfit);
                            elseif ($commissionType == 'percentage')
                                $defaultCommission = $baseProfit * $commissionValue / 100;

                            $commissionObj = new Varien_Object(array(
                                        'profit' => $baseProfit,
                                        'commission' => $defaultCommission,
                                        'tier_commission' => array(),
                                    ));
                            Mage::dispatchEvent('affiliateplus_calculate_tier_commission', array(
                                'item' => $child,
                                'account' => $account,
                                'commission_obj' => $commissionObj
                            ));

                            if ($commissionObj->getTierCommission())
                                $tierCommissions[$child->getId()] = $commissionObj->getTierCommission();
                            $commission += $commissionObj->getCommission();
                            $child->setAffiliateplusCommission($commissionObj->getCommission());

                            $defCommission += $commissionObj->getCommission();
                            $defaultAmount += $child->getBasePrice();
                            
                            $orderItemIds[] = $child->getProduct()->getId();
                            $orderItemNames[] = $child->getName();

                            $defaultItemIds[] = $child->getProduct()->getId();
                            $defaultItemNames[] = $child->getName();
                        }
                        // if ($childHasCommission) {
                            // $orderItemIds[] = $item->getProduct()->getId();
                            // $orderItemNames[] = $item->getName();

                            // $defaultItemIds[] = $item->getProduct()->getId();
                            // $defaultItemNames[] = $item->getName();
                        // }
                    } else {
                        if ($this->_getConfigHelper()->getCommissionConfig('affiliate_type') == 'profit')
                            $baseProfit = $item->getBasePrice() - $item->getBaseCost();
                        else
                            $baseProfit = $item->getBasePrice();
                        $baseProfit = $item->getQtyOrdered() * $baseProfit - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
                        if ($baseProfit <= 0)
                            continue;

                        $orderItemIds[] = $item->getProduct()->getId();
                        $orderItemNames[] = $item->getName();

                        $defaultItemIds[] = $item->getProduct()->getId();
                        $defaultItemNames[] = $item->getName();

                        if ($commissionType == 'fixed')
                            $defaultCommission = min($item->getQtyOrdered() * $commissionValue, $baseProfit);
                        elseif ($commissionType == 'percentage')
                            $defaultCommission = $baseProfit * $commissionValue / 100;

                        $commissionObj = new Varien_Object(array(
                                    'profit' => $baseProfit,
                                    'commission' => $defaultCommission,
                                    'tier_commission' => array(),
                                ));
                        Mage::dispatchEvent('affiliateplus_calculate_tier_commission', array(
                            'item' => $item,
                            'account' => $account,
                            'commission_obj' => $commissionObj
                        ));

                        if ($commissionObj->getTierCommission())
                            $tierCommissions[$item->getId()] = $commissionObj->getTierCommission();
                        $commission += $commissionObj->getCommission();
                        $item->setAffiliateplusCommission($commissionObj->getCommission());

                        $defCommission += $commissionObj->getCommission();
                        $defaultAmount += $item->getBasePrice();
                    }
                }
            }

            if (!$baseDiscount && !$commission)
                return $this;

            // $customer = Mage::getSingleton('customer/session')->getCustomer();

            // Create transaction
            $transactionData = array(
                'account_id' => $account->getId(),
                'account_name' => $account->getName(),
                'account_email' => $account->getEmail(),
                'customer_id' => $order->getCustomerId(), // $customer->getId(),
                'customer_email' => $order->getCustomerEmail(), // $customer->getEmail(),
                'order_id' => $order->getId(),
                'order_number' => $order->getIncrementId(),
                'order_item_ids' => implode(',', $orderItemIds),
                'order_item_names' => implode(',', $orderItemNames),
                'total_amount' => $order->getBaseSubtotal(),
                'discount' => $baseDiscount,
                'commission' => $commission,
                'created_time' => now(),
                'status' => '2',
                'store_id' => Mage::app()->getStore()->getId(),
                'extra_content' => $extraContent,
                'tier_commissions' => $tierCommissions,
                //'ratio'			=> $ratio,
                //'original_commission'	=> $originalCommission,
                'default_item_ids' => $defaultItemIds,
                'default_item_names' => $defaultItemNames,
                'default_commission' => $defCommission,
                'default_amount' => $defaultAmount,
                'type'          => 3,
            );
            if ($account->getUsingCoupon()) {
                $session = Mage::getSingleton('checkout/session');
                $transactionData['coupon_code'] = $session->getData('affiliate_coupon_code');
                if ($program = $account->getUsingProgram()) {
                    $transactionData['program_id'] = $program->getId();
                    $transactionData['program_name'] = $program->getName();
                } else {
                    $transactionData['program_id'] = 0;
                    $transactionData['program_name'] = 'Affiliate Program';
                }
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
            }

            $transaction = Mage::getModel('affiliateplus/transaction')->setData($transactionData)->setId(null);

            Mage::dispatchEvent('affiliateplus_calculate_commission_after', array(
                'transaction' => $transaction,
                'order' => $order,
                'affiliate_info' => $affiliateInfo,
            ));

            try {
                $transaction->save();
                Mage::dispatchEvent('affiliateplus_recalculate_commission', array(
                    'transaction' => $transaction,
                    'order' => $order,
                    'affiliate_info' => $affiliateInfo,
                ));

                if ($transaction->getIsChangedData())
                    $transaction->save();
                Mage::dispatchEvent('affiliateplus_created_transaction', array(
                    'transaction' => $transaction,
                    'order' => $order,
                    'affiliate_info' => $affiliateInfo,
                ));

                $transaction->sendMailNewTransactionToAccount();
                $transaction->sendMailNewTransactionToSales();
            } catch (Exception $e) {
                // Exception
            }
        }
    }
    
    public function orderLoadAfter($observer) {
        $order = $observer->getOrder();
        if ($order->getBaseAffiliateCredit() > -0.0001
            || Mage::app()->getStore()->roundPrice($order->getGrandTotal()) > 0
            || $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED
            || $order->isCanceled()
            || $order->canUnhold()
        ) {
            return $this;
        }
        foreach ($order->getAllItems() as $item) {
            if (($item->getQtyInvoiced() - $item->getQtyRefunded() - $item->getQtyCanceled()) > 0) {
                $order->setForcedCanCreditmemo(true);
                return $this;
            }
        }
    }

    public function orderSaveAfter($observer) {
        $order = $observer->getOrder();
        $storeId = $order->getStoreId();
        
        // Return money on affiliate balance
        if ($order->getData('state') == Mage_Sales_Model_Order::STATE_CANCELED) {
            $paymentMethod = Mage::getModel('affiliateplus/payment_credit')->load($order->getId(), 'order_id');
            if ($paymentMethod->getId()
                && $paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount() > 0
            ) {
                $payment = Mage::getModel('affiliateplus/payment')->load($paymentMethod->getPaymentId())
                    ->setData('payment', $paymentMethod);
                $account = $payment->getAffiliateplusAccount();
                if ($account && $account->getId() && $payment->getId()) {
                    try {
                        $refundAmount = $paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount();
                        $account->setBalance($account->getBalance() + $refundAmount)
                            ->setTotalPaid($account->getTotalPaid() - $refundAmount)
                            ->setTotalCommissionReceived($account->getTotalCommissionReceived() - $refundAmount)
                            ->save();
                        $paymentMethod->setBaseRefundAmount($paymentMethod->getBasePaidAmount())
                            ->setRefundAmount($paymentMethod->getPaidAmount())
                            ->save();
                        $payment->setStatus(4)->save();
                    } catch (Exception $e) {}
                }
            }
        }

        $configOrderStatus = $this->_getConfigHelper()->getCommissionConfig('updatebalance_orderstatus', $storeId);
        $configOrderStatus = $configOrderStatus ? $configOrderStatus : 'processing';
        if ($order->getStatus() == $configOrderStatus) {
            $transaction = Mage::getModel('affiliateplus/transaction')->load($order->getIncrementId(), 'order_number');
            // Complete Transaction or hold transaction
            if ($this->_getConfigHelper()->getCommissionConfig('holding_period', $storeId)) {
                return $transaction->hold();
            }
            return $transaction->complete();
        }

        $cancelStatus = explode(',', $this->_getConfigHelper()->getCommissionConfig('cancel_transaction_orderstatus', $storeId));
        if (in_array($order->getStatus(), $cancelStatus)) {
            $transaction = Mage::getModel('affiliateplus/transaction')->load($order->getIncrementId(), 'order_number');
            // Cancel Transaction
            return $transaction->cancel();
        }
    }

    public function paypalPrepareItems($observer) {
        if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
            $paypalCart = $observer->getEvent()->getPaypalCart();
            if ($paypalCart) {
                $salesEntity = $paypalCart->getSalesEntity();
                $totalDiscount = 0;
                if ($salesEntity->getBaseAffiliateplusDiscount())
                    $totalDiscount = $salesEntity->getBaseAffiliateplusDiscount();
                else
                    foreach ($salesEntity->getAddressesCollection() as $address)
                        if ($address->getBaseAffiliateplusDiscount())
                            $totalDiscount = $address->getBaseAffiliateplusDiscount();
                if ($totalDiscount)
                    $paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, abs((float) $totalDiscount), Mage::helper('affiliateplus')->__('Affiliate Discount'));
            }
        } else {
            $salesEntity = $observer->getSalesEntity();
            $additional = $observer->getAdditional();
            if ($salesEntity && $additional){
                $totalDiscount = 0;
                if ($salesEntity->getBaseAffiliateplusDiscount())
                    $totalDiscount = $salesEntity->getBaseAffiliateplusDiscount();
                else
                    foreach ($salesEntity->getAddressesCollection() as $address)
                        if ($address->getBaseAffiliateplusDiscount())
                            $totalDiscount = $address->getBaseAffiliateplusDiscount();
                if ($totalDiscount){
                    $items = $additional->getItems();
                    $items[] = new Varien_Object(array(
                        'name'	=> Mage::helper('affiliateplus')->__('Affiliate Discount'),
                        'qty'	=> 1,
                        'amount'	=> -(abs((float)$totalDiscount)),
                    ));
                    $additional->setItems($items);
                }
            }
        }
    }

    /**
     *
     * @param type $observer
     * @return \Magestore_Affiliateplus_Model_Observer
     */
    public function saveClickAction($observer) {
        $controller = $observer['controller_action'];
        $request = $controller->getRequest();
        $accountCode = $request->getParam('acc');

        if (!$accountCode && $request->getParam('df08b0441bac900')) {
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');
            try {
                $select = $read->select()
                        ->from($resource->getTableName('affiliate_referral'), array('customer_id'))
                        ->where("identify_code=?", trim($request->getParam('df08b0441bac900')));
                $result = $read->fetchRow($select);
                $oldCustomerId = $result['customer_id'];
                if ($oldCustomerId)
                    $accountCode = Mage::getModel('affiliateplus/account')
                            ->loadByCustomerId($oldCustomerId)
                            ->getIdentifyCode();
            } catch (Exception $e) {
                
            }
        }
        if (!$accountCode)
            return $this;
        if ($account = Mage::getSingleton('affiliateplus/session')->getAccount())
            if ($account->getIdentifyCode() == $accountCode)
                return $this;
        $storeId = Mage::app()->getStore()->getId();
        if (!$storeId)
            return $this;
        $account->setStoreId($storeId);
        $account = Mage::getModel('affiliateplus/account')->loadByIdentifyCode($accountCode);
        if (!$account->getId() || ($account->getStatus() != 1))
            return $this;

        $ipAddress = $request->getClientIp();
        $banner_id = $request->getParam('bannerid');
        if ($banner_id) {
            $banner = Mage::getModel('affiliateplus/banner')->load($banner_id);
            $banner->setStoreId($storeId);
            if ($banner->getStatus() != 1)
                $banner_id = 0;
        }
        /*
         * check
         */
        $check = FALSE;
        if (Mage::helper('affiliateplus')->exitedCookie())
           	return $this;
        if (!$check) {
            if (Mage::helper('affiliateplus')->isProxys())
                return $this;
        }
        if (!$check) {
            if (Mage::helper('affiliateplus')->isRobots())
                return $this;
        }
        /*
         * end check
         */
        $domain = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if (!$domain && $request->getParam('src')) {
            $domain = $request->getParam('src');
        }
        $landing_page = $request->getOriginalPathInfo();
        $actionModel = Mage::getModel('affiliateplus/action');
        if ($check) {
            $isUnique = 0;
        } else {
            $isUnique = $actionModel->checkIpClick($ipAddress, $account->getId(),$domain, $banner_id, 2);
        }
        
        $action = $actionModel->saveAction($account->getId(), $banner_id, 2, $storeId, 0, $ipAddress, $domain, $landing_page);
        if ($isUnique) {
            if (Mage::helper('affiliateplus/config')->getActionConfig('detect_iframe')) {
                $hashCode = md5($action->getCreatedDate() . $action->getId());
                $session = Mage::getSingleton('core/session');
                $session->setData('transaction_checkiframe__action_id', $action->getId());
                $session->setData('transaction_checkiframe_hash_code', $hashCode);
            } else {
                $action->setIsUnique(1)->save();
                Mage::dispatchEvent('affiliateplus_save_action_before', array(
                    'action' => $action,
                    'is_unique' => $isUnique,
                ));
            }
        }
    }

    /* magic update affiliate account when account customer change 13/11/2012 */

    public function customerSaveAfter($observer) {
        $customer = $observer->getEvent()->getCustomer();
        $account = Mage::getModel('affiliateplus/account')->loadByCustomer($customer);
        if ($account->getId() > 0) {
            $account->setName($customer->getName());
            $account->setEmail($customer->getEmail());
            $account->save();
        }
        return $this;
    }
    
    public function creditmemoSaveAfter($observer) {
        $creditmemo = $observer->getCreditmemo();
        if ($creditmemo->getState() != Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED) {
            return $this;
        }
        // Refund for Affiliate Credit
        $this->creditmemoRefund($creditmemo);
        
        $storeId    = $creditmemo->getStoreId();
        if (!$this->_getConfigHelper()->getCommissionConfig('decrease_commission_creditmemo', $storeId)) {
            return $this;
        }
        $order = $creditmemo->getOrder();
        $cancelStatus = explode(',', $this->_getConfigHelper()->getCommissionConfig('cancel_transaction_orderstatus', $storeId));
        if (in_array('closed', $cancelStatus) && !$order->canCreditmemo()) {
            return $this;
        }
        $transaction = Mage::getModel('affiliateplus/transaction')->load($order->getIncrementId(), 'order_number');
        if ($transaction->getId()) {
            $transaction->reduce($creditmemo);
        }
    }
    
    public function creditmemoRefund($creditmemo) {
        // $creditmemo = $observer->getCreditmemo();
        $order = $creditmemo->getOrder();
        
        $paymentMethod = Mage::getModel('affiliateplus/payment_credit')->load($order->getId(), 'order_id');
        if ($paymentMethod->getId()
            && $paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount() > 0
        ) {
            $payment = Mage::getModel('affiliateplus/payment')->load($paymentMethod->getPaymentId())
                ->setData('payment', $paymentMethod);
            $account = $payment->getAffiliateplusAccount();
            if ($account && $account->getId() && $payment->getId()) {
                try {
                    $refundAmount = -$creditmemo->getBaseAffiliateCredit();
                    $account->setBalance($account->getBalance() + $refundAmount)
                        ->setTotalPaid($account->getTotalPaid() - $refundAmount)
                        ->setTotalCommissionReceived($account->getTotalCommissionReceived() - $refundAmount)
                        ->save();
                    $paymentMethod->setBaseRefundAmount($paymentMethod->getBaseRefundAmount() + $refundAmount)
                        ->setRefundAmount($paymentMethod->getRefundAmount() - $creditmemo->getAffiliateCredit())
                        ->save();
                    if (abs($paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount()) < 0.0001) {
                        $payment->setStatus(4)->save();
                    }
                } catch (Exception $e) {}
            }
        }
    }
    
    public function blockToHtmlAfter($observer) {
        $helper = Mage::helper('affiliateplus/account');
        if ($helper->accountNotLogin()
            || $helper->disableStoreCredit()
            || !$helper->isEnoughBalance()
        ) {
            return ;
        }
        $block = $observer['block'];
        if ($block instanceof Mage_Checkout_Block_Cart_Coupon) {
            $requestPath = $block->getRequest()->getRequestedRouteName()
                . '_' . $block->getRequest()->getRequestedControllerName()
                . '_' . $block->getRequest()->getRequestedActionName();
            if ($requestPath == 'checkout_cart_index') {
                $transport = $observer['transport'];
                $html = $transport->getHtml();
                $html .= $block->getLayout()->createBlock('affiliateplus/credit_cart')->renderView();
                $transport->setHtml($html);
            }
        }
        if ($block instanceof Mage_Checkout_Block_Onepage_Payment_Methods) {
            $requestPath = $block->getRequest()->getRequestedRouteName()
                . '_' . $block->getRequest()->getRequestedControllerName()
                . '_' . $block->getRequest()->getRequestedActionName();
            if ($requestPath == 'onestepcheckout_index_index'
                || $requestPath == 'checkout_onepage_index'
            ) {
                return ;
            }
            $transport = $observer['transport'];
            $html = $transport->getHtml();
            
            $creditHtml = $block->getLayout()->createBlock('affiliateplus/credit_form')->renderView();
            $html .= '<script type="text/javascript">checkOutLoadAffiliateCredit('.Mage::helper('core')->jsonEncode(array('html'=>$creditHtml)).');onLoadAffiliateCreditForm();</script>';
            
            $transport->setHtml($html);
        }
    }
    
    public function salesruleValidatorProcess($observer) {
        if ($this->_getConfigHelper()->getDiscountConfig('allow_discount') != 'affiliate') {
            return $this;
        }
        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        $account = '';
        foreach ($affiliateInfo as $info)
            if ($info['account']) {
                $account = $info['account'];
                break;
            }
        if (!$account) {
            return $this;
        }
        $result = $observer['result'];
        $result->setDiscountAmount(0)
            ->setBaseDiscountAmount(0);
        $rule = $observer['rule'];
        $rule->setRuleId('')->setStopRulesProcessing(true);
    }
    
    public function unHoldTransaction()
    {
        $days = (int)$this->_getConfigHelper()->getCommissionConfig('holding_period');
        $activeTime = time() - $days * 86400;
        $collection = Mage::getResourceModel('affiliateplus/transaction_collection')
            ->addFieldToFilter('status', 4)
            ->addFieldToFilter('holding_from', array('to' => date('Y-m-d H:i:s', $activeTime)));
        foreach ($collection as $transaction) {
            try {
                $transaction->unHold();
            } catch (Exception $e) {}
        }
    }
}
