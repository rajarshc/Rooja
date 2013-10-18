<?php

class Magestore_Affiliateplus_Block_Adminhtml_Payment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('payment_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('affiliateplus')->__('Withdrawal Information'));
	}

	protected function _beforeToHtml()
	{	
		$this->addTab('form_section', array(
			'label'     => Mage::helper('affiliateplus')->__('Withdrawal Information'),
			'title'     => Mage::helper('affiliateplus')->__('Withdrawal Information'),
			'content'   => $this->getLayout()->createBlock('affiliateplus/adminhtml_payment_edit_tab_form')->toHtml(),
		));
		
        if ($this->getRequest()->getParam('id')) {
            $this->addTab('history_tab', array(
                'label'     => Mage::helper('affiliateplus')->__('Status History'),
                'title'     => Mage::helper('affiliateplus')->__('Status History'),
                'content'   => $this->getLayout()->createBlock('affiliateplus/adminhtml_payment_edit_tab_history')->toHtml(),
            ));
        }
        
		return parent::_beforeToHtml();
	}
}
