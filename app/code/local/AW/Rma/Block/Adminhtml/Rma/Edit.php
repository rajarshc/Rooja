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
class AW_Rma_Block_Adminhtml_Rma_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct() {
        $this->_controller = 'adminhtml_rma';
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'awrma';

        $this->_updateButton('save', 'onclick', 'formControl.validateForm()');
        $this->_updateButton('save', 'id', 'awrma-save-button');

        $this->_removeButton('delete');

        $this->_addButton('saveandcontinueedit', array(
            'label' => $this->__('Save And Continue Edit'),
            'onclick' => 'awrmaSaveAndContinueEdit()',
            'class' => 'save',
            'id' => 'awrma-save-and-continue'
        ), -200);

        $rmaRequest = Mage::registry('awrmaformdatarma');
        if(Mage::helper('awrma/config')->getAllowPrintLabel($rmaRequest->getStoreId()))
            $this->_addButton('printlabel', array(
                'label' => $this->__('Print'),
                'onclick' => "saveAndPrint()",
                'class' => 'scalable save',
                'id' => 'awrma-print'
            ), -300);

        $this->_formScripts[] =
"var formControl = new AWRMAAdminRmaFormControl(this, editForm);
formControl.observeItemsCount();

function awrmaSaveAndContinueEdit() {
    if($('edit_form').action.indexOf('continue/1/')<0)
        $('edit_form').action += 'continue/1/';

    formControl.validateForm();
}

function saveAndPrint() {
    if($('edit_form').action.indexOf('print/1/')<0)
        $('edit_form').action += 'print/1/';

    formControl.validateForm();
}";
        if($this->getRequest()->getParam('printstore') && $this->getRequest()->getParam('printurl')) {
            $this->_formScripts[] =
"function openInNewWindow(href) {
    var newWindow = window.open(href, '_blank');
    newWindow.focus();
    return false;
}
openInNewWindow('".Mage::app()->getStore($this->getRequest()->getParam('printstore'))->getUrl('awrma/guest_rma/printform', array('id' => $this->getRequest()->getParam('printurl')))."');";
        }
    }

    public function getHeaderText() {
        $_rma = Mage::registry('awrmaformdatarma');
        return $this->__('RMA '.$_rma->getTextId().' &ndash; '.$_rma->getStatusName());
    }
}
