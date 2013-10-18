<?php

class Magestore_Affiliateplus_Block_Adminhtml_Transaction_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('transaction_form', array('legend'=>Mage::helper('affiliateplus')->__('General Information')));
		
		if ( Mage::getSingleton('adminhtml/session')->getTransationData() ){
			$data = Mage::getSingleton('adminhtml/session')->getTransationData();
			Mage::getSingleton('adminhtml/session')->setTransationData(null);
		} elseif ( Mage::registry('transaction_data') ) {
			$data = Mage::registry('transaction_data')->getData();
		}	
		
		$form->setTransationData($data);
		
		$store = Mage::getModel('core/store')->load($data['store_id']);
		
		$fieldset->addField('account_email', 'link', array(
			'label'	=> Mage::helper('affiliateplus')->__('Affiliate Account'),
			'href'	=> $this->getUrl('*/adminhtml_account/edit', array('_current'=>true, 'id' => $data['account_id'])),
			'title'	=> Mage::helper('affiliateplus')->__('View Affiliate Account Detail'),
		));

		if (!empty($data['customer_email']))
        $fieldset->addField('customer_email', 'link', array(
			'label'	=> Mage::helper('affiliateplus')->__('Customer Email'),
			'href'	=> $this->getUrl('adminhtml/customer/edit/',  array('_current'=>true, 'id' => $data['customer_id'])),
			'title'	=> Mage::helper('affiliateplus')->__('View Customer Detail'),
		));	

		//event to add more field
		Mage::dispatchEvent('affiliateplus_adminhtml_add_field_transaction_form', array('fieldset' => $fieldset, 'form' => $form));

		if (!empty($data['order_number']))
        $fieldset->addField('order_number', 'link', array(
			'label' => Mage::helper('affiliateplus')->__('Order'),
			'href'	=>  $this->getUrl('adminhtml/sales_order/view/', array('_current'=>true,'order_id' => $data['order_id'])),
			'title'	=> Mage::helper('affiliateplus')->__('View Order Detail'),
		));

		if (!empty($data['products']))
        $fieldset->addField('products', 'note', array(
			'label' => Mage::helper('affiliateplus')->__('Product(s)'),
			'text'	=> Mage::helper('affiliateplus')->getBackendProductHtmls($data['order_item_ids']),
		));

		$fieldset->addField('commission', 'note', array(
			'label' => Mage::helper('affiliateplus')->__('Commission'),
			'text'	=> '<strong>'.$store->convertPrice($data['commission'], true, true).'</strong>',
		));

		if ($data['percent_plus'] > 0)
		$fieldset->addField('percent_plus', 'note', array(
			'label' => Mage::helper('affiliateplus')->__('Additional Commission Percentage'),
			'text'	=> '<strong>'.sprintf("%.2f",$data['percent_plus']).'%'.'</strong>',
		));

		if ($data['commission_plus'] > 0)
		$fieldset->addField('commission_plus', 'note', array(
			'label' => Mage::helper('affiliateplus')->__('Additional Commission'),
			'text'	=> '<strong>'.$store->convertPrice($data['commission_plus'], true, true).'</strong>',
		));

		$fieldset->addField('discount', 'note', array(
			'label' => Mage::helper('affiliateplus')->__('Discount'),
			'text'	=> '<strong>'.$store->convertPrice($data['discount'], true, true).'</strong>',
		));

		$fieldset->addField('total_amount', 'note', array(
			'label' => Mage::helper('affiliateplus')->__('Total Amount'),
			'text'	=> '<strong>'.$store->convertPrice($data['total_amount'], true, true).'</strong>',
		));


		$statuses = array( 	1 => Mage::helper('affiliateplus')->__('Completed'), 
							2 => Mage::helper('affiliateplus')->__('Pending'), 
							3 => Mage::helper('affiliateplus')->__('Canceled'),
                            4 => Mage::helper('affiliateplus')->__('On Hold'),
		);

		$fieldset->addField('status', 'note', array(
			'label'   => Mage::helper('affiliateplus')->__('Status'),
			'text'	=> '<b>' . $statuses[$data['status']] . '</b>',
		));
  		
		$form->setValues($data);
		return parent::_prepareForm();
	}
}