<?php

class Magestore_Affiliateplus_Block_Adminhtml_Account_Edit_Tab_Form 
	extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      
      $data = array();
      if ( Mage::getSingleton('adminhtml/session')->getAccountData() )
      {
          $data = Mage::getSingleton('adminhtml/session')->getAccountData();
          Mage::getSingleton('adminhtml/session')->setAccountData(null);
      } elseif ( Mage::registry('account_data') ) {
          $data = Mage::registry('account_data')->getData();
      }
      
	  $storeId = $this->getRequest()->getParam('store');
	  if($storeId){
	  	  $store = Mage::getModel('core/store')->load($storeId);
	  }else{
	  	  $store = Mage::app()->getStore();
	  }
	  
      $fieldset = $form->addFieldset('account_form', array('legend'=>Mage::helper('affiliateplus')->__('General Information')));
	  
      $fieldset->addField('customer_id', 'hidden', array(
          'name'      => 'customer_id',	  
      ));
	  
	  $inStore = $this->getRequest()->getParam('store');
      $defaultLabel = Mage::helper('affiliateplus')->__('Use Default');
      $defaultTitle = Mage::helper('affiliateplus')->__('-- Please Select --');
      $scopeLabel = Mage::helper('affiliateplus')->__('STORE VIEW');
	
	  if(!$inStore)
	  	  $disabled = false;
	  else
	  	  $disabled = empty($data['status_in_store']);

	  $isDefaultStatusCheck = $disabled ? 'checked="checked"': '';

	  $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('affiliateplus')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array('value'     => 1, 'label'     => Mage::helper('affiliateplus')->__('Enabled')),
              array('value'     => 2, 'label'     => Mage::helper('affiliateplus')->__('Disabled'))
          ),
		  'disabled'  => $disabled,
          'after_element_html'	=> $inStore ? '</td><td class="use-default">
			<input id="status_default" name="status_default" type="checkbox" value="1" class="checkbox config-inherit" '.$isDefaultStatusCheck.' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="status_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']',
      ));
	  
	  $fieldset->addField('firstname', 'text', array(
          'label'     => Mage::helper('affiliateplus')->__('First Name'),
          'name'      => 'firstname',
          'class'     => 'required-entry',
          'required'  => true,
      ));
      
	  $fieldset->addField('lastname', 'text', array(
          'label'     => Mage::helper('affiliateplus')->__('Last Name'),
          'name'      => 'lastname',
          'class'     => 'required-entry',
          'required'  => true,
      ));
	  
      $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('affiliateplus')->__('Email'),
          'name'      => 'email',
		  //'readonly'  => 'readonly',
          'class'     => 'required-entry validate-email',
          'required'  => true,
      ));
      
      if (!isset($data['account_id']) || !$data['account_id']){
      	$fieldset->addField('password','text',array(
		  'label'	=> Mage::helper('affiliateplus')->__('Password'),
		  'name'	=> 'password',
		  'note'	=> Mage::helper('affiliateplus')->__('Password will be updated for customer account'),
		));
      } else {
      	$fieldset->addField('customer_account','note',array(
      		'label'	=> Mage::helper('affiliateplus')->__('Customer'),
      		'text'	=> '<a href="'.$this->getUrl('adminhtml/customer/edit',array('id' => $data['customer_id'])).'" title="'.Mage::helper('affiliateplus')->__('Edit Customer').'">'.$data['name'].'</a>',
  		));
      }
	
	  //event to add more tab
	  Mage::dispatchEvent('affiliateplus_adminhtml_add_account_info_fieldset', array('form' => $form, 'fieldset' => $fieldset, 'load_data' => $data));
	  
      $fieldset->addField('notification', 'select', array(
          'label'     => Mage::helper('affiliateplus')->__('Receive Notification Email'),
          'required'  => false,
          'name'      => 'notification',
          'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
          'value'	=> 1,
      ));
	  
	  if($data && isset($data['customer_id']) && $data['customer_id']){
     	 $form->addValues($data);	  
	   } else {
          $fieldset->addField('associate_website_id', 'select', array(
              'name'    => 'associate_website_id',
              'label'   => Mage::helper('affiliateplus')->__('Associate to Website'),
              'value'   => Mage::app()->getDefaultStoreView()->getWebsiteId(),
              'values'  => Mage::getModel('customer/customer_attribute_source_website')->getAllOptions(),
              'note'    => Mage::helper('affiliateplus')->__('Only available for customer do not exist on your system')
          ));
       }
			  
      return parent::_prepareForm();
  }
}
