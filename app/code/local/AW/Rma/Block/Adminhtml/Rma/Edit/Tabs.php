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
class AW_Rma_Block_Adminhtml_Rma_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    public function __construct() {
        parent::__construct();
        $this->setId('awrma_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('RMA Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('request_information', array(
            'label' => $this->__('Request Information'),
            'title' => $this->__('Request Information'),
            'content' => $this->getLayout()->createBlock('awrma/adminhtml_rma_edit_tab_requestinformation')->toHtml()
        ));

        $this->addTab('rma_items', array(
            'label' => $this->__('RMA Items'),
            'title' => $this->__('RMA Items'),
            'content' => $this->getLayout()->createBlock('awrma/adminhtml_rma_edit_tab_rmaitems')->toHtml()
        ));

        $this->addTab('customer_address', array(
            'label' => $this->__('Customer Address'),
            'title' => $this->__('Customer Address'),
            'content' => $this->getLayout()->createBlock('awrma/adminhtml_rma_edit_tab_customeraddress')->toHtml()
        ));

        $this->addTab('notes', array(
            'label' => $this->__('Notes'),
            'label' => $this->__('Notes'),
            'content' => $this->getLayout()->createBlock('awrma/adminhtml_rma_edit_tab_notes')->toHtml()
        ));

        return parent::_beforeToHtml();
    }
}
