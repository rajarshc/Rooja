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
class AW_Rma_Block_Adminhtml_Rma_Edit_Tab_Requestinformation extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $formData = Mage::registry('awrmaformdatarma');

        $details = $form->addFieldset('details_form', array(
            'legend' => $this->__('Request Details')
        ));

        $formData->setTextId($formData->getTextId());

        $details->addField('text_id', 'label', array(
            'name' => 'id',
            'label' => $this->__('ID')
        ));

        $formData->setOrderIdUrl('#'.$formData->getOrderId());

        $details->addField('order_id_url', 'awlink', array(
            'name' => 'order_id_url',
            'label' => $this->__('Order ID'),
            'href' => Mage::helper('awrma')->getOrderUrl($formData->getOrderId()),
        ));

        if($formData->getCustomerId()) {
            $formData->setCustomerIdUrl($formData->getCustomerName());

            $details->addField('customer_id_url', 'awlink', array(
                'name' => 'customer_id_url',
                'label' => $this->__('Customer Name'),
                'href' => Mage::helper('awrma')->getCustomerUrl($formData->getCustomerId(), $this->getRequest()->getParam('key'))
            ));
        } else {
            $details->addField('customer_name', 'label', array(
                'name' => 'customer_name',
                'label' => $this->__('Customer Name')
            ));
        }
        
        $formData->setPackageOpenedLabel(Mage::getModel('awrma/source_packageopened')->getOptionLabel($formData->getPackageOpened()));

        $details->addField('package_opened_label', 'label', array(
            'name' => 'package_opened_label',
            'label' => $this->__('Package Opened')
        ));

        if($formData->getApprovementCode())
            $details->addField('approvement_code', 'label', array(
                'name' => 'approvement_code',
                'label' => $this->__('Approvement Code')
            ));

        if($formData->getExternalLink())
            $details->addField('external_link', 'awlink', array(
                'name' => 'external_link',
                'label' => $this->__('External URL'),
                'href' => Mage::app()->getStore($formData->getStoreId())->getUrl('awrma/guest_rma/view', array('id' => $formData->getExternalLink())),
                'target' => '_blank'
            ));

        $requestoptions = $form->addFieldset('options_form', array(
            'legend' => $this->__('Request Options')
        ));

        $requestoptions->addField('status', 'select', array(
            'name' => 'status',
            'label' => $this->__('Set status to'),
            'values' => $this->_getStatusOptions($formData)
        ));

        $requestoptions->addField('request_type', 'select', array(
            'name' => 'request_type',
            'label' => $this->__('Request type'),
            'values' => $this->_getTypesOptions($formData)
        ));

        $requestoptions->addField('tracking_code', 'text', array(
            'name' => 'tracking_code',
            'label' => $this->__('Post tracking code')
        ));

        $addcomment = $form->addFieldset('add_comment', array(
            'legend' => $this->__('Add Comment')
        ));

        $addcomment->addField('comment_text', 'textarea', array(
            'name' => 'comment_text',
            'label' => $this->__('Message')
        ));

        $addcomment->addField('comment_file', 'file', array(
            'name' => 'comment_file',
            'label' => $this->__('File ('.Mage::helper('awrma/config')->getMaxAttachmentsSizeKb().'kb)')
        ));

        $comments = $form->addFieldset('comments_list_container', array());

        $comments->addField('comments_list', 'note', array(
            'name' => 'comments_list',
            'text' => Mage::getSingleton('core/layout')->createBlock('awrma/adminhtml_rma_edit_tab_requestinformation_comments')
                ->setRmaRequest($formData)->toHtml()
        ));
        
        $form->setValues($formData);
    }

    protected function _getStatusOptions($rmaRequest) {
        return $_statuses = Mage::getModel('awrma/entitystatus')->getCollection()
            ->setRemovedFilter()
            ->setStoreFilter($rmaRequest->getStoreId())
            ->setDefaultSort()
            ->getOptions();
    }

    protected function _getTypesOptions($rmaRequest) {
        return Mage::getModel('awrma/entitytypes')->getCollection()
            ->setStoreFilter($rmaRequest->getStoreId())
            ->setDefaultSort()
            ->setActiveFilter()
            ->getOptions();
    }
}
