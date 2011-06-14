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
class AW_Rma_Block_Adminhtml_Status_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct() {
        $this->_controller = 'adminhtml_status';
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'awrma';

        $this->_addButton('saveandcontinueedit', array(
            'label' => $this->__('Save And Continue Edit'),
            'onclick' => 'awrmaSaveAndContinueEdit()',
            'class' => 'save'
        ), -200);

        $this->_formScripts[] =
"function awrmaSaveAndContinueEdit() {
    if($('edit_form').action.indexOf('continue/1/')<0)
        $('edit_form').action += 'continue/1/';

    editForm.submit();
}";
    }

    public function getHeaderText() {
        if(!Mage::registry('awrmaformdatatype'))
            return $this->__('New RMA Status');
        else
            return $this->__('Edit RMA Status');
    }
}
