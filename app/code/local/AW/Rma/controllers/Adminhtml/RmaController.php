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
class AW_Rma_Adminhtml_RmaController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        return $this->loadLayout()->_setActiveMenu('sales/awrma');
    }

    private function hasErrors() {
        return (bool) count($this->_getSession()->getMessages()->getItemsByType('error'));
    }

    protected function saveAction() {
        $listType = $this->_getSession()->getData('awrma-list-type');
        if(!$this->_validateFormKey())
            return $this->_redirect('*/*/'.($listType ? $listType : 'list'));

        if($this->getRequest()->getParam('id')) {
            $rmaRequest = Mage::getModel('awrma/entity')->load($this->getRequest()->getParam('id'));
            if($rmaRequest->getData() != array()) {
                $_data['id'] = $rmaRequest->getId();
                $_data['status'] = $this->getRequest()->getParam('status');
                $_data['request_type'] = $this->getRequest()->getParam('request_type');
                $_data['tracking_code'] = $this->getRequest()->getParam('tracking_code');
                $_data['admin_notes'] = $this->getRequest()->getParam('admin_notes');

                if(Mage::helper('awrma/config')->getAllowPerOrderRMA($rmaRequest->getStoreId()))
                    $_data['order_items'] = $this->getRequest()->getParam('orderitems');
                else
                    $_data['order_items'] = $rmaRequest->getOrderItems();

                if($rmaRequest->getData('status') != $_data['status']
                    && $_data['status'] == Mage::helper('awrma/status')->getApprovedStatusId()
                    && !$rmaRequest->getApprovementCode())
                    $_data['approvement_code'] = Mage::helper('awrma/request')->getApprovementCode();

                $printLabel = $this->getRequest()->getParam('printlabel');
                if(isset($printLabel['stateprovince']) && filter_var($printLabel['stateprovince'], FILTER_VALIDATE_INT)) {
                    $printLabel['stateprovince_id'] = $printLabel['stateprovince'];
                    $printLabel['stateprovince'] = Mage::helper('awrma')->getRegionName($printLabel['stateprovince_id']);
                }
                
                $_data['print_label'] = $printLabel;

                $rmaRequest->setData($_data);
                $rmaRequest->save();
                $this->_getSession()->addSuccess(Mage::helper('awrma')->__('RMA successfully saved'));

                $_notified = FALSE;

                if($this->getRequest()->getParam('comment_text')) {
                    $_data = array();
                    $_data['text'] = $this->getRequest()->getParam('comment_text');
                    if($_data['text']) {
                        if(isset($_FILES['comment_file']['name']) && $_FILES['comment_file']['name']) {
                            if(!in_array(Mage::helper('awrma/files')->getExtension($_FILES['comment_file']['name']), Mage::helper('awrma/config')->getForbiddenExtensions())) {
                                if($_FILES['comment_file']['size'] <= Mage::helper('awrma/config')->getMaxAttachmentsSize() && $_FILES['comment_file']['size'] > 0) {
                                    if($_FILES['comment_file']['error'] == UPLOAD_ERR_OK) {
                                        try {
                                            $uploader = new Varien_File_Uploader('comment_file');
                                            $uploader
                                                ->setAllowedExtensions(null)
                                                ->setAllowRenameFiles(TRUE)
                                                ->setAllowCreateFolders(TRUE)
                                                ->setFilesDispersion(FALSE);
                                            $result = $uploader->save(Mage::helper('awrma/files')->getPath(), $_FILES['comment_file']['name']);
                                            $_data['attachments'] = $result['file'];
                                        } catch (Exception $ex) {
                                            $this->_getSession()->addError($ex->getMessage());
                                        }
                                    } else {
                                        $this->_getSession()->addError(Mage::helper('awrma')->__('Some error occurs when uploading file'));
                                    }
                                } else {
                                    $this->_getSession()->addError(Mage::helper('awrma')->__('Maximal allowed attachment size is '.(floor(Mage::helper('awrma/config')->getMaxAttachmentsSize()/1024)).' kb'));
                                }
                            } else {
                                $this->_getSession()->addError(Mage::helper('awrma')->__('Forbidden file extension'));
                            }
                        }

                        if(!$this->hasErrors()) {
                            $_data['owner'] = AW_Rma_Model_Source_Owner::ADMIN;
                            Mage::helper('awrma/comments')->postComment($rmaRequest->getId(), $_data['text'], $_data, FALSE);

                            $this->_getSession()->addSuccess(Mage::helper('awrma')->__('Comment successfully added'));

                            Mage::getModel('awrma/notify')->checkChanges($rmaRequest, $_data['text']);

                            $_notified = TRUE;
                        }
                    } else {
                        $this->_getSession()->addError(Mage::helper('awrma')->__('Comment text can\'t be empty'));
                    }
                }
            } else {
                $this->_getSession()->addError($this->__('Can\'t load RMA request'));
            }
        } else {
            $this->_getSession()->addError($this->__('Request ID isn\'t specified'));
        }

        if($this->hasErrors())
            return $this->_redirect('*/*/edit', array('id' => $rmaRequest->getId()));
        else {
            if(!$_notified)
                Mage::getModel('awrma/notify')->checkChanges($rmaRequest);
            if($this->getRequest()->getParam('continue')) {
                return $this->_redirect('*/*/edit', array('id' => $rmaRequest->getId()));
            } elseif($this->getRequest()->getParam('print')) {
                $rmaRequest->load($rmaRequest->getId());
                return $this->_redirect('*/*/edit', array('id' => $rmaRequest->getId(), 'printstore' => $rmaRequest->getStoreId(), 'printurl' => $rmaRequest->getExternalLink()));
            } else {
                return $this->_redirect('*/*/'.($listType ? $listType : 'list'));
            }
        }
    }

    protected function indexAction() {
        $listType = $this->_getSession()->getData('awrma-list-type');
        if($listType)
            return $this->_redirect('*/*/'.$listType);
        else
            return $this->_redirect('*/*/list');
    }

    protected function editAction() {
        $this->_initAction();

        if($this->getRequest()->getParam('id')) {
            $_rmaRequest = Mage::getModel('awrma/entity')->load($this->getRequest()->getParam('id'));
            if($_rmaRequest->getData() != array()) {
                Mage::register('awrmaformdatarma', $_rmaRequest, TRUE);
                $this->_addContent($this->getLayout()->createBlock('awrma/adminhtml_rma_edit'))
                    ->_addLeft($this->getLayout()->createBlock('awrma/adminhtml_rma_edit_tabs'));
            } else {
                $this->_getSession()->addError($this->__('Can\'t load RMA by given ID'));
            }
        } else {
            $this->_getSession()->addError($this->__('RMA id isn\'t specified'));
        }

        if($this->hasErrors()) {
            $listType = $this->_getSession()->getData('awrma-list-type');
            if($listType)
                return $this->_redirect('*/*/'.$listType);
            else
                return $this->_redirect('*/*/list');
        }

        $this->renderLayout();
    }

    protected function listpendingAction() {
        $this->_getSession()->setData('awrma-list-type', 'listpending');
        $this->_initAction()->renderLayout();
    }

    protected function listAction() {
        $this->_getSession()->setData('awrma-list-type', 'list');
        $this->_initAction()->renderLayout();
    }

    protected function downloadAction() {
        if($this->getRequest()->getParam('cid')) {
            $_comment = Mage::getModel('awrma/entitycomments')->load($this->getRequest()->getParam('cid'));
            if($_comment->getData() != array()) {
                return Mage::helper('awrma/files')->downloadFile($_comment->getAttachments());
            } else {
                $this->_getSession()->addError($this->__('Can\'t load comment'));
            }
        } else {
            $this->_getSession()->addError($this->__('Comment ID isn\'t specified'));
        }
        $this->_redirect('awrma/adminhtml_rma/index');
    }

    protected function _isAllowed() {
        $listType = $this->_getSession()->getData('awrma-list-type');
        if(!$listType)
            $listType = $this->getRequest()->getActionName();
        if($listType)
            return Mage::getSingleton('admin/session')->isAllowed('sales/awrma/'.$listType);
        else
            return Mage::getSingleton('admin/session')->isAllowed('sales/awrma/list');
    }
	public function newAction(){
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('awrma/adminhtml_rma_new'))
             ->_addContent($this->getLayout()->createBlock('awrma/adminhtml_rma_new_form'))
             ->_addLeft($this->getLayout()->createBlock('awrma/adminhtml_rma_new_tabs'));
        $this->renderLayout();
	}
    public function ordersuggestAction(){
        $text = $this->getRequest()->getParam('text');
        $orders = Mage::getModel('sales/order')->getCollection();
        $orders->getSelect()
                ->where('main_table.increment_id LIKE \'%'.$text.'%\'')
                ->limit(50)
                ;
        $suggest = array();
        foreach($orders->getItems() as $item){
            $suggest[] = array(
              'order_id'     =>  $item->getId(),
              'increment_id' =>  $item->getIncrementId(),
            );
        }
        return $this->getResponse()->setBody(Zend_Json::encode($suggest));
    }

    public function createrequestAction(){
        $session = Mage::getSingleton('adminhtml/session');
        $request = $this->getRequest();
        $_data = array();
        $_addNewEntityFlag = TRUE;

        $_data['order_id'] = $request->getParam('order');
        //Checking OrderID
        if($_data['order_id']) {
            //Trying to load order
            $_order = Mage::getModel('sales/order')->loadByIncrementId($_data['order_id']);
            if(!($_order->getData() == array())) {
                    //Gets order items from post if per-order item RMA is allowed
                    //and gets it directly from order otherwise
                        foreach($_order->getItemsCollection() as $_item) {
                            $_orderItems[$_item->getId()] = $_item->getQtyOrdered()*1;
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

                            $_data['customer_email'] = $_order->getCustomerEmail();
                            $_data['customer_name'] = $_order->getShippingAddress()->getFirstname().' '.$_order->getShippingAddress()->getLastname();
                            $_data['customer_id'] = $_order->getCustomerId();

                            $rmaEntity = Mage::getModel('awrma/entity');
                            $rmaEntity->setData($_data);
                            $rmaEntity->save();

                            //Clear form data stored in session
                            $session->getAWRMAFormData(TRUE);
                            $session->addSuccess(Mage::helper('awrma')->__('New RMA request has been successfully added'));
                            return $this->_redirect('*/*/edit', array('id' => $rmaEntity->getId()));
                            //return $rmaEntity->getId();

                        } else {
                            $_addNewEntityFlag = FALSE;
                            $session->addError(Mage::helper('awrma')->__('Wrong form data'));
                        }
                    }
            } else {
                $_addNewEntityFlag = FALSE;
                $session->addError(Mage::helper('awrma')->__('Wrong order ID'));
            }
        } else {
            $_addNewEntityFlag = FALSE;
            $session->addError(Mage::helper('awrma')->__('Wrong form data'));
        }
        return $this->_redirect('*/*/new');
    }
}
