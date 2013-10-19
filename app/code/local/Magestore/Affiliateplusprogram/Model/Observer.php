<?php

class Magestore_Affiliateplusprogram_Model_Observer extends Varien_Object 
{
    /**
     * get module helper
     *
     * @return Magestore_Affiliateplusprogram_Helper_Data
     */
    protected function _getHelper() {
        if (!$this->getData('helper')) {
            $this->setData('helper', Mage::helper('affiliateplusprogram'));
        }
        return $this->getData('helper');
    }

    /**
     * get Configuration helper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    protected function _getConfigHelper() {
        return Mage::helper('affiliateplus/config');
    }

    public function addColumnBannerGrid($observer) {
        $grid = $observer->getEvent()->getGrid();
        $grid->addColumn('program_id', array(
            'header' => $this->_getHelper()->__('Program Name'),
            'index' => 'program_id',
            'align' => 'left',
            'type' => 'options',
            'options' => $this->_getHelper()->getProgramOptions(),
        ));
        return $this;
    }

    public function addFieldBannerForm($observer) {
        $fieldset = $observer->getEvent()->getFieldset();
        $fieldset->addField('program_id', 'select', array(
            'label' => $this->_getHelper()->__('Program Name'),
            'name' => 'program_id',
            'values' => $this->_getHelper()->getProgramOptionArray(),
        ));
        return $this;
    }

    public function addFieldTransactionForm($observer) {
        $data = $observer->getEvent()->getForm()->getTransationData();
        $fieldset = $observer->getEvent()->getFieldset();

        $transactionPrograms = Mage::getResourceModel('affiliateplusprogram/transaction_collection')
                ->addFieldToFilter('transaction_id', $data['transaction_id']);

        $text = array();
        if ($transactionPrograms->getSize())
            foreach ($transactionPrograms as $transactionProgram) {
                if ($transactionProgram->getProgramId()) {
                    $url = Mage::getSingleton('adminhtml/url')->getUrl('affiliateplusprogramadmin/adminhtml_program/edit', array(
                        '_current' => true,
                        'id' => $transactionProgram->getProgramId(),
                        'store' => $data['store_id'],
                            ));
                    $title = $this->_getHelper()->__('View Program Detail');
                    $label = $transactionProgram->getProgramName();
                } else {
                    $url = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit', array('section' => 'affiliateplus'));
                    $title = $this->_getHelper()->__('View Program Configuration Detail');
                    $label = $this->_getHelper()->__('Affiliate Program');
                }
                $text[] = '<a href="' . $url . '" title="' . $title . '">' . $label . '</a>';
            } else {
            $url = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit', array('section' => 'affiliateplus'));
            $title = $this->_getHelper()->__('View Program Configuration Detail');
            $label = $this->_getHelper()->__('Affiliate Program');
            $text[] = '<a href="' . $url . '" title="' . $title . '">' . $label . '</a>';
        }

        $fieldset->addField('program_ids', 'note', array(
            'label' => $this->_getHelper()->__('Program(s)'),
            'text' => implode(' , ', $text),
        ));
        return $this;
    }

    public function addAccountTab($observer) {
        $block = $observer->getEvent()->getForm();
        $block->addTab('program_section', array(
            'label' => $this->_getHelper()->__('Programs'),
            'title' => $this->_getHelper()->__('Programs'),
            'url' => $block->getUrl('affiliateplusprogramadmin/adminhtml_program/program', array(
                '_current' => true,
                'id' => $block->getRequest()->getParam('id'),
                'store' => $block->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
            'after' => 'form_section',
        ));
        return $this;
    }

    public function accountSaveAfter($observer) {
        $affiliateplusAccount = $observer->getEvent()->getAffiliateplusAccount();
        if ($affiliateplusAccount->hasData('account_program')) {
            $joinPrograms = array();
            parse_str($affiliateplusAccount->getAccountProgram(), $joinPrograms);
            $joinPrograms = array_keys($joinPrograms);

            $joinedProgram = array();

            $oldProgramCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                    ->addFieldToFilter('account_id', $affiliateplusAccount->getId());
            $program = Mage::getModel('affiliateplusprogram/program');

            foreach ($oldProgramCollection as $oldProgram) {
                $joinedProgram[] = $oldProgram->getProgramId();
                if (in_array($oldProgram->getProgramId(), $joinPrograms))
                    continue;
                $program->load($oldProgram->getProgramId())
                        ->setNumAccount($program->getNumAccount() - 1)
                        ->setId($oldProgram->getProgramId())
                        ->orgSave();
                $oldProgram->delete();
            }

            $addPrograms = array_diff($joinPrograms, $joinedProgram);

            $newProgram = Mage::getModel('affiliateplusprogram/account')
                    ->setAccountId($affiliateplusAccount->getId())
                    ->setJoined(now());
            foreach ($addPrograms as $programId) {
                $program->load($programId)
                        ->setNumAccount($program->getNumAccount() + 1)
                        ->setId($programId)
                        ->orgSave();
                $newProgram->setProgramId($programId)->setId(null)->save();
            }
            Mage::getModel('affiliateplusprogram/joined')->updateJoined(null, $affiliateplusAccount->getId());
        } elseif ($affiliateplusAccount->isObjectNew()) {
            $oldProgramCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                    ->addFieldToFilter('account_id', $affiliateplusAccount->getId());
            if ($oldProgramCollection->getSize())
                return $this;

            $newProgram = Mage::getModel('affiliateplusprogram/account')
                    ->setAccountId($affiliateplusAccount->getId())
                    ->setJoined(now());
            $autoJoinPrograms = Mage::getResourceModel('affiliateplusprogram/program_collection')
                    ->addFieldToFilter('autojoin', 1);
            $group = Mage::getModel('customer/customer')->load($affiliateplusAccount->getCustomerId())->getGroupId();
            $autoJoinPrograms->getSelect()
                    ->where("scope = 0 OR (scope = 1 AND FIND_IN_SET($group,customer_groups) )");
            foreach ($autoJoinPrograms as $autoJoinProgram) {
                $autoJoinProgram->setNumAccount($autoJoinProgram->getNumAccount() + 1)->orgSave();
                $newProgram->setProgramId($autoJoinProgram->getId())->setId(null)->save();
            }
            Mage::getModel('affiliateplusprogram/joined')->updateJoined(null, $affiliateplusAccount->getId());
        }
        return $this;
    }

    public function getListProgramWelcome($observer) {
        $programListObj = $observer->getEvent()->getProgramListObject();

        $programList = $programListObj->getProgramList();
        if (isset($programList['default'])) {
            if (!$this->_getConfigHelper()->getGeneralConfig('show_default')) {
                unset($programList['default']);
            }
        }

        $collection = Mage::getResourceModel('affiliateplusprogram/program_collection')->setStoreId(Mage::app()->getStore()->getId());
        foreach ($collection as $item)
            if ($item->getStatus() && $item->getShowInWelcome()) {
                Mage::dispatchEvent('affiliateplus_prepare_program', array('info' => $item));
                $programList[$item->getId()] = $item;
            }
        $programListObj->setProgramList($programList);
        return $this;
    }

    public function bannerPrepareCollection($observer) {
        $collection = $observer->getEvent()->getCollection();

        $joinedPrograms = $this->_getHelper()->getJoinedProgramIds();
        $joinedPrograms[] = 0;
        $collection->addFieldToFilter('program_id', array('in' => $joinedPrograms));

        return $this;
    }

    public function productGetFinalPrice($observer) {
        $product = $observer->getEvent()->getProduct();
        $discountedObj = $observer->getEvent()->getDiscountedObj();

        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        foreach ($affiliateInfo as $info)
            if ($account = $info['account']) {
                $program = Mage::helper('affiliateplusprogram')->getProgramByProductAccount($product, $account);
                if ($program) {
                    $price = $discountedObj->getPrice();

                    $discountType = $program->getDiscountType();
                    $discountValue = $program->getDiscount();
                    if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                        if ($program->getSecDiscount()) {
                            $discountType = $program->getSecDiscountType();
                            $discountValue = $program->getSecondaryDiscount();
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
                        $price = 0;
                    $discountedObj->setPrice($price);
                    $discountedObj->setDiscounted(true);
                }
                return $this;
            }
        return $this;
    }

    public function addressCollectTotal($observer) {
        $address = $observer->getEvent()->getAddress();
        $discountObj = $observer->getEvent()->getDiscountObj();
        $items = $address->getAllItems();

        $affiliateInfo = $discountObj->getAffiliateInfo();
        $baseDiscount = $discountObj->getBaseDiscount();
        $discountedItems = $discountObj->getDiscountedItems();

        foreach ($affiliateInfo as $info)
            if ($account = $info['account']) {
                if ($account->getUsingCoupon()) {
                    $program = $account->getUsingProgram();
                    if (!$program)
                        return $this;
                    $discountObj->setDefaultDiscount(false);
                    if (!$program->validateOrder($address->getQuote()))
                        return $this;
                }
                foreach ($items as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if ($account->getUsingCoupon()) {
                        if (!$program->validateItem($item))
                            continue;
                    } else {
                        if (in_array($item->getId(), $discountedItems))
                            continue;
                        $program = Mage::helper('affiliateplusprogram')->getProgramByItemAccount($item, $account);
                    }
                    if ($program) {
                        $discountType = $program->getDiscountType();
                        $discountValue = floatval($program->getDiscount());
                        if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                            if ($program->getSecDiscount()) {
                                $discountType = $program->getSecDiscountType();
                                $discountValue = floatval($program->getSecondaryDiscount());
                            }
                        }
                        if ($discountType == 'cart_fixed') {
                            $baseItemsPrice = 0;
                            foreach ($address->getAllItems() as $_item) {
                                if ($_item->getParentItemId()) {
                                    continue;
                                }
                                if (in_array($_item->getId(),$discountedItems)) {
                                    continue;
                                }
                                if (!$program->validateItem($_item)) {
                                    continue;
                                }
                                if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                                    foreach ($_item->getChildren() as $child) {
                                        $baseItemsPrice += $_item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                    }
                                } elseif ($_item->getProduct()) {
                                    $baseItemsPrice += $_item->getQty() * $_item->getBasePrice() - $_item->getBaseDiscountAmount();
                                }
                            }
                            if ($baseItemsPrice) {
                                $totalBaseDiscount = min($discountValue, $baseItemsPrice);
                                foreach ($address->getAllItems() as $_item) {
                                    if ($_item->getParentItemId()) {
                                        continue;
                                    }
                                    if (in_array($_item->getId(),$discountedItems)) {
                                        continue;
                                    }
                                    if (!$program->validateItem($_item)) {
                                        continue;
                                    }
                                    if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                                        foreach ($_item->getChildren() as $child) {
                                            $price = $_item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                            $childBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                            $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                                ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount));
                                        }
                                    } elseif ($_item->getProduct()) {
                                        $price = $_item->getQty() * $_item->getBasePrice() - $_item->getBaseDiscountAmount();
                                        $itemBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                        $_item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                            ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount));
                                    }
                                    $discountedItems[] = $_item->getId();
                                }
                                $baseDiscount += $totalBaseDiscount;
                            } else {
                                $discountedItems[] = $item->getId();
                            }
                        } elseif ($discountType == 'fixed') {
                            $itemBaseDiscount = 0;
                            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                                foreach ($item->getChildren() as $child) {
                                    $childBaseDiscount = $item->getQty() * $child->getQty() * $discountValue;
                                    $price = $item->getQty() * ( $child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() );
                                    $childBaseDiscount = ($childBaseDiscount < $price) ? $childBaseDiscount : $price;
                                    $itemBaseDiscount += $childBaseDiscount;
                                    $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                        ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount));
                                }
                            } else {
                                $itemBaseDiscount = $item->getQty() * $discountValue;
                                $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                                $itemBaseDiscount = ($itemBaseDiscount < $price) ? $itemBaseDiscount : $price;
                                $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                    ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount));
                            }
                            $discountedItems[] = $item->getId();
                            $baseDiscount += $itemBaseDiscount;
                        } elseif ($discountType == 'percentage') {
                            $itemBaseDiscount = 0;
                            if ($discountValue > 100)
                                $discountValue = 100;
                            if ($discountValue < 0)
                                $discountValue = 0;
                            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                                foreach ($item->getChildren() as $child) {
                                    $price = $item->getQty() * ( $child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() );
                                    $childBaseDiscount = $price * $discountValue / 100;
                                    $itemBaseDiscount += $childBaseDiscount;
                                    $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                        ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount));
                                }
                            } else {
                                $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                                $itemBaseDiscount = $price * $discountValue / 100;
                                $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                   ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount));
                            }
                            $discountedItems[] = $item->getId();
                            $baseDiscount += $itemBaseDiscount;
                        }
                    }
                }
                $discountObj->setBaseDiscount($baseDiscount);
                $discountObj->setDiscountedItems($discountedItems);
                return $this;
            }
        return $this;
    }

    public function calculateCommissionBefore($observer) {
        $order = $observer->getEvent()->getOrder();
		$order->setQuote(Mage::getModel('sales/quote')->load($order->getQuoteId()));
        $items = $order->getAllItems();
        $affiliateInfo = $observer->getEvent()->getAffiliateInfo();
        $commissionObj = $observer->getEvent()->getCommissionObj();

        $commission = $commissionObj->getCommission();
        $orderItemIds = $commissionObj->getOrderItemIds();
        $orderItemNames = $commissionObj->getOrderItemNames();
        $commissionItems = $commissionObj->getCommissionItems();
        $extraContent = $commissionObj->getExtraContent();
        $tierCommissions = $commissionObj->getTierCommissions();

        foreach ($affiliateInfo as $info)
            if ($account = $info['account']) {
                if ($account->getUsingCoupon()) {
                    $program = $account->getUsingProgram();
                    if (!$program)
                        return $this;
                    $commissionObj->setDefaultCommission(false);
                    if (!$program->validateOrder($order))
                        return $this;
                }
                foreach ($items as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if ($account->getUsingCoupon()) {
                        if (!$program->validateItem($item))
                            continue;
                    } else {
                        if (in_array($item->getId(), $commissionItems))
                            continue;
                        $program = Mage::helper('affiliateplusprogram')
                                ->initProgram($account->getId(), $order)
                                ->getProgramByItemAccount($item, $account);
                    }
                    if (!$program) {
                        continue;
                    }
                    $affiliateType = $program->getAffiliateType() ? $program->getAffiliateType() : $this->_getConfigHelper()->getCommissionConfig('affiliate_type');
                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        $childHasCommission = false;
                        foreach ($item->getChildrenItems() as $child) {
                            if ($affiliateType == 'profit')
                                $baseProfit = $child->getBasePrice() - $child->getBaseCost();
                            else
                                $baseProfit = $child->getBasePrice();

                            $baseProfit = $child->getQtyOrdered() * $baseProfit - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                            if ($baseProfit <= 0)
                                continue;
                            $commissionType = $program->getCommissionType();
                            $commissionValue = floatval($program->getCommission());
                            if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                                if ($program->getSecCommission()) {
                                    $commissionType = $program->getSecCommissionType();
                                    $commissionValue = floatval($program->getSecondaryCommission());
                                }
                            }
                            if (!$commissionValue)
                                continue;
                            
                            $childHasCommission = true;

                            if ($commissionType == 'fixed') {
                                $itemCommission = min($child->getQtyOrdered() * $commissionValue, $baseProfit);
                            } elseif ($commissionType == 'percentage') {
                                if ($commissionValue > 100)
                                    $commissionValue = 100;
                                if ($commissionValue < 0)
                                    $commissionValue = 0;
                                $itemCommission = $baseProfit * $commissionValue / 100;
                            }
                            $commissionObject = new Varien_Object(array(
                                        'profit' => $baseProfit,
                                        'commission' => $itemCommission,
                                        'tier_commission' => array()
                                    ));
                            Mage::dispatchEvent('affiliateplusprogram_calculate_tier_commission', array(
                                'item' => $child,
                                'account' => $account,
                                'commission_obj' => $commissionObject,
                                'program' => $program
                            ));

                            if ($commissionObject->getTierCommission())
                                $tierCommissions[$child->getId()] = $commissionObject->getTierCommission();

                            $commission += $commissionObject->getCommission();
                            $child->setAffiliateplusCommission($commissionObject->getCommission());

                            if (!isset($extraContent[$program->getId()]['total_amount']))
                                $extraContent[$program->getId()]['total_amount'] = 0;
                            $extraContent[$program->getId()]['total_amount'] += $child->getBasePrice();
                            if (!isset($extraContent[$program->getId()]['commission']))
                                $extraContent[$program->getId()]['commission'] = 0;
                            $extraContent[$program->getId()]['commission'] += $commissionObject->getCommission();
                            
                            $orderItemIds[] = $child->getProduct()->getId();
                            $orderItemNames[] = $child->getName();
                            
                            $extraContent[$program->getId()]['order_item_ids'][] = $child->getProduct()->getId();
                            $extraContent[$program->getId()]['order_item_names'][] = $child->getName();
                        }
                        if ($childHasCommission) {
                            // $orderItemIds[] = $item->getProduct()->getId();
                            // $orderItemNames[] = $item->getName();
                            $commissionItems[] = $item->getId();
                            
                            $extraContent[$program->getId()]['program_name'] = $program->getName();
                            // $extraContent[$program->getId()]['order_item_ids'][] = $item->getProduct()->getId();
                            // $extraContent[$program->getId()]['order_item_names'][] = $item->getName();
                        }
                    } else {
                        if ($affiliateType == 'profit')
                            $baseProfit = $item->getBasePrice() - $item->getBaseCost();
                        else
                            $baseProfit = $item->getBasePrice();

                        $baseProfit = $item->getQtyOrdered() * $baseProfit - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
                        if ($baseProfit <= 0)
                            continue;

                        $commissionType = $program->getCommissionType();
                        $commissionValue = floatval($program->getCommission());
                        if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                            if ($program->getSecCommission()) {
                                $commissionType = $program->getSecCommissionType();
                                $commissionValue = floatval($program->getSecondaryCommission());
                            }
                        }
                        if (!$commissionValue)
                            continue;
                        $orderItemIds[] = $item->getProduct()->getId();
                        $orderItemNames[] = $item->getName();
                        $commissionItems[] = $item->getId();

                        if ($commissionType == 'fixed') {
                            $itemCommission = min($item->getQtyOrdered() * $commissionValue, $baseProfit);
                        } elseif ($commissionType == 'percentage') {
                            if ($commissionValue > 100)
                                $commissionValue = 100;
                            if ($commissionValue < 0)
                                $commissionValue = 0;
                            $itemCommission = $baseProfit * $commissionValue / 100;
                        }
                        $commissionObject = new Varien_Object(array(
                                    'profit' => $baseProfit,
                                    'commission' => $itemCommission,
                                    'tier_commission' => array()
                                ));
                        Mage::dispatchEvent('affiliateplusprogram_calculate_tier_commission', array(
                            'item' => $item,
                            'account' => $account,
                            'commission_obj' => $commissionObject,
                            'program' => $program
                        ));

                        if ($commissionObject->getTierCommission())
                            $tierCommissions[$item->getId()] = $commissionObject->getTierCommission();

                        $commission += $commissionObject->getCommission();
                        $item->setAffiliateplusCommission($commissionObject->getCommission());

                        $extraContent[$program->getId()]['program_name'] = $program->getName();
                        $extraContent[$program->getId()]['order_item_ids'][] = $item->getProduct()->getId();
                        $extraContent[$program->getId()]['order_item_names'][] = $item->getName();
                        if (!isset($extraContent[$program->getId()]['total_amount']))
                            $extraContent[$program->getId()]['total_amount'] = 0;
                        $extraContent[$program->getId()]['total_amount'] += $item->getBasePrice();
                        if (!isset($extraContent[$program->getId()]['commission']))
                            $extraContent[$program->getId()]['commission'] = 0;
                        $extraContent[$program->getId()]['commission'] += $commissionObject->getCommission();
                    }
                }
                $commissionObj->setCommission($commission);
                $commissionObj->setOrderItemIds($orderItemIds);
                $commissionObj->setOrderItemNames($orderItemNames);
                $commissionObj->setCommissionItems($commissionItems);
                $commissionObj->setExtraContent($extraContent);
                $commissionObj->setTierCommissions($tierCommissions);
                return $this;
            }
        return $this;
    }

    public function createdTransaction($observer) {
        $transaction = $observer->getEvent()->getTransaction();
        $order = $observer->getEvent()->getOrder();

        $extraContent = $transaction->getExtraContent();
        $originalCommission = $transaction->getOriginalCommission();
        if ($extraContent && count($extraContent)) {
            $transactionModel = Mage::getModel('affiliateplusprogram/transaction')
                    ->setTransactionId($transaction->getId())
                    ->setOrderId($transaction->getOrderId())
                    ->setOrderNumber($transaction->getOrderNumber())
                    ->setAccountId($transaction->getAccountId())
                    ->setAccountName($transaction->getAccountName());

            $program = Mage::getModel('affiliateplusprogram/program')->setStoreId(Mage::app()->getStore()->getId());
            foreach ($extraContent as $programId => $programData) {
                $transactionModel->addData($programData);
                $transactionModel->setOrderItemIds(implode(',', $programData['order_item_ids']))
                        ->setOrderItemNames(implode(',', $programData['order_item_names']))
                        ->setProgramId($programId)
                        ->setCommission($programData['commission'])
                        ->setId(null)->save();
                $program->load($programId);
                $program->setTotalSalesAmount($program->getTotalSalesAmount() + $transactionModel->getTotalAmount())->orgSave();
            }

            if ($transaction->getDefaultCommission())
                $transactionModel->setOrderItemIds(implode(',', $transaction->getDefaultItemIds()))
                        ->setOrderItemNames(implode(',', $transaction->getDefaultItemNames()))
                        ->setProgramId(0)
                        ->setProgramName($this->_getHelper()->__('Affiliate Program'))
                        ->setCommission($transaction->getDefaultCommission())
                        ->setTotalAmount($transaction->getDefaultAmount())
                        ->setId(null)->save();
        }
        return $this;
    }
    /* Magic 26/11/2012 change number of account when delete customer */
    public function customerDeleteBefore($observer) {
        $customer = $observer->getEvent()->getCustomer();
        $affiliateAccount = Mage::getModel('affiliateplus/account')->loadByCustomer($customer);
        $collection = Mage::getModel('affiliateplusprogram/account')->getCollection();
        $collection->addFieldToFilter('account_id', $affiliateAccount->getId());
        foreach ($collection as $value) {
            $program = Mage::getModel('affiliateplusprogram/program')->load($value->getProgramId());
            $numAccount = $program->getNumAccount();
            try {
                $program->setNumAccount($numAccount - 1);
                $program->save();
            } catch (Exception $e) {
                
            }
        }
        return $this;
    }

}