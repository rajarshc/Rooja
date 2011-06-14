<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Rma
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Rma_Helper_Request extends Mage_Core_Helper_Abstract {
    public static function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Validate RMA request and adds it to database when all is good
     * @param AW_Rma_Model_Entity $request
     * @param boolean $guestMode
     * @return boolean
     */
    public static function save($request, $guestMode = FALSE) {
        $_data = array();
        $_addNewEntityFlag = TRUE;

        $_data['order_id'] = $request->getParam('order');
        //Checking OrderID
        if($_data['order_id']) {
            //Trying to load order
            $_order = Mage::getModel('sales/order')->loadByIncrementId($_data['order_id']);
            if(!($_order->getData() == array())) {
                //Getting order items
                $_orderItems = $request->getParam('orderitems');
                if((Mage::helper('awrma/config')->getAllowPerOrderRMA() && count($_orderItems)) || !Mage::helper('awrma/config')->getAllowPerOrderRMA()) {
                    //Gets order items from post if per-order item RMA is allowed
                    //and gets it directly from order otherwise
                    if(!Mage::helper('awrma/config')->getAllowPerOrderRMA()) {
                        $_orderItems = array();
                        foreach($_order->getItemsCollection() as $_item) {
                            $_orderItems[$_item->getId()] = $_item->getQtyOrdered()*1;
                        }
                    } else {
                        //Checking items count
                        foreach($_order->getItemsCollection() as $_item)
                            if(isset($_orderItems[$_item->getId()]) && ($_orderItems[$_item->getId()] < 1 || $_orderItems[$_item->getId()] > $_item->getQtyOrdered()*1)) {
                                $_addNewEntityFlag = FALSE;
                                self::_getSession()->addError(Mage::helper('awrma')->__('Wrong quantity for '.$_item->getName()));
                            }
                    }

                    if($_addNewEntityFlag) {
                        $_data['order_items'] = $_orderItems;
                        //Checking package opened and request type values
                        if(!(Mage::getModel('awrma/source_packageopened')->getOption($request->getParam('packageopened')) === FALSE)) {
                            $_data['package_opened'] = $request->getParam('packageopened');
                            $_data['request_type'] = $request->getParam('requesttype') ? $request->getParam('requesttype') : null;

                            $_data['created_at'] = date(AW_Rma_Model_Mysql4_Entity::DATETIMEFORMAT, time());
                            $_data['status'] = Mage::helper('awrma/status')->getPendingApprovalStatusId();
                            $_data['external_link'] = Mage::helper('awrma')->getExtLink();
                            if($guestMode) {
                                $_data['customer_email'] = $_order->getCustomerEmail();
                                $_data['customer_name'] = $_order->getShippingAddress()->getFirstname().' '.$_order->getShippingAddress()->getLastname();
                            } else {
                                $_data['customer_name'] = self::_getSession()->getCustomer()->getFirstname().' '.self::_getSession()->getCustomer()->getLastname();
                                $_data['customer_email'] = self::_getSession()->getCustomer()->getEmail();
                                $_data['customer_id'] = self::_getSession()->getCustomer()->getId();
                            }

                            $rmaEntity = Mage::getModel('awrma/entity');
                            $rmaEntity->setData($_data);
                            $rmaEntity->save();

                            if($request->getParam('additionalinfo')) {
                                //save comment
                                $_data['owner'] = AW_Rma_Model_Source_Owner::CUSTOMER;
                                Mage::helper('awrma/comments')->postComment($rmaEntity->getId(), $request->getParam('additionalinfo'), $_data, FALSE);
                            }

                            Mage::getModel('awrma/notify')->notifyNew($rmaEntity, $request->getParam('additionalinfo'));

                            //Clear form data stored in session
                            self::_getSession()->getAWRMAFormData(TRUE);
                            self::_getSession()->addSuccess(Mage::helper('awrma')->__('New RMA request has been successfully added'));
                            self::_getSession()->addNotice(Mage::helper('awrma')->__('Your RMA is currently waiting for approval'));
                            return $guestMode ? $rmaEntity->getExternalLink() : $rmaEntity->getId();

                        } else {
                            $_addNewEntityFlag = FALSE;
                            self::_getSession()->addError(Mage::helper('awrma')->__('Wrong form data'));
                        }
                    }
                } else {
                    $_addNewEntityFlag = FALSE;
                    self::_getSession()->addError(Mage::helper('awrma')->__('No items for request specified'));
                }
            } else {
                $_addNewEntityFlag = FALSE;
                self::_getSession()->addError(Mage::helper('awrma')->__('Wrong order ID'));
            }
        } else {
            $_addNewEntityFlag = FALSE;
            self::_getSession()->addError(Mage::helper('awrma')->__('Wrong form data'));
        }

        self::_getSession()->setAWRMAFormData($request->getParams());

        return $_addNewEntityFlag;
    }

    public static function getApprovementCode() {
        return strtoupper(uniqid());
    }

    public static function getDefaultPrintLabelData($order) {
        if($order->getShippingAddress()) {
            $_printLabelData = array(
                'firstname' => $order->getShippingAddress()->getData('firstname'),
                'lastname' => $order->getShippingAddress()->getData('lastname'),
                'company' => $order->getShippingAddress()->getData('company'),
                'telephone' => $order->getShippingAddress()->getData('telephone'),
                'fax' => $order->getShippingAddress()->getData('fax'),
                'streetaddress' => explode("\n", $order->getShippingAddress()->getData('street')),
                'city' => $order->getShippingAddress()->getData('city'),
                'stateprovince_id' => $order->getShippingAddress()->getData('region_id'),
                'stateprovince' => $order->getShippingAddress()->getData('region'),
                'postcode' => $order->getShippingAddress()->getData('postcode'),
                'country_id' => $order->getShippingAddress()->getData('country_id')
            );
            return $_printLabelData;
        }
        return null;
    }
}
