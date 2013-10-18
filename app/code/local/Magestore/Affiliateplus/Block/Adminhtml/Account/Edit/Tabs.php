<?php

class Magestore_Affiliateplus_Block_Adminhtml_Account_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('account_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('affiliateplus')->__('Account Information'));
	}

	protected function _beforeToHtml()
	{
		$id = $this->getRequest()->getParam('id');
		
		// if(!$id){
			// $this->addTab('customer_section', array(
				// 'label'     => Mage::helper('affiliateplus')->__('Import Customer Info'),
				// 'title'     => Mage::helper('affiliateplus')->__('Import Customer Info'),
				// 'url'		=> $this->getUrl('*/*/customer',array('_current'=>true)),
		  		// 'class'     => 'ajax',
			// ));
			
		// }
		
		//event to add more tab
		Mage::dispatchEvent('affiliateplus_adminhtml_add_account_tab', array('form' => $this, 'id' => $id));
		
		$this->addTab('general_section', array(
			'label'     => Mage::helper('affiliateplus')->__('General Information'),
			'title'     => Mage::helper('affiliateplus')->__('General Information'),
			'content'   => $this->getLayout()->createBlock('affiliateplus/adminhtml_account_edit_tab_form')->toHtml(),
		));
        
        $this->addTab('form_section', array(
			'label'     => Mage::helper('affiliateplus')->__('Payment Information'),
			'title'     => Mage::helper('affiliateplus')->__('Payment Information'),
			'content'   => $this->getLayout()->createBlock('affiliateplus/adminhtml_account_edit_tab_paymentinfo')->toHtml(),
		));
		
		if($id){
			$this->addTab('transaction_section', array(
				'label'     => Mage::helper('affiliateplus')->__('History transaction'),
				'title'     => Mage::helper('affiliateplus')->__('History transaction'),
				'url'		=> $this->getUrl('*/*/transaction',array('_current'=>true)),
		  		'class'     => 'ajax',  
			));
			
			$this->addTab('payment_section', array(
				'label'     => Mage::helper('affiliateplus')->__('History Withdrawal'),
				'title'     => Mage::helper('affiliateplus')->__('History Withdrawal'),
				'url'		=> $this->getUrl('*/*/payment',array('_current'=>true)),
		  		'class'     => 'ajax',
			));
		}
		
		$this->setActiveTab('general_section');
		return parent::_beforeToHtml();
	}
	
	public function addTabAfter($tabId, $tab, $afterTabId)
    {
        $this->addTab($tabId, $tab);
        $this->_tabs[$tabId]->setAfter($afterTabId);
    }
}