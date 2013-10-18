<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      
      $dataObj = new Varien_Object(array(
      	'program_id' => '',
      	'name_in_store'	=> '',
      	'status_in_store'	=> '',
      	'show_in_welcome_in_store'	=> '',
	  ));
      if ( Mage::getSingleton('adminhtml/session')->getAffiliateplusprogramData() ){
          $data = Mage::getSingleton('adminhtml/session')->getAffiliateplusprogramData();
          Mage::getSingleton('adminhtml/session')->setAffiliateplusprogramData(null);
      } elseif ( Mage::registry('affiliateplusprogram_data') ) {
          $data = Mage::registry('affiliateplusprogram_data')->getData();
      }
      if (isset($data)) $dataObj->addData($data);
      $data = $dataObj->getData();
      
      $this->setForm($form);
      $fieldset = $form->addFieldset('affiliateplusprogram_form', array('legend'=>Mage::helper('affiliateplusprogram')->__('Program information')));
      
      $inStore = $this->getRequest()->getParam('store');
      $defaultLabel = Mage::helper('affiliateplusprogram')->__('Use Default');
      $defaultTitle = Mage::helper('affiliateplusprogram')->__('-- Please Select --');
      $scopeLabel = Mage::helper('affiliateplusprogram')->__('STORE VIEW');
     
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Program Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'program_name',
          'disabled'  => ($inStore && !$data['name_in_store']),
          'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="name_default" name="name_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['name_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="name_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']',
      ));

      $fieldset->addField('description', 'textarea', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Description'),
          'name'      => 'description',
          'required'  => true,
          'disabled'  => ($inStore && !$data['description_in_store']),
          'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="description_default" name="description_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['description_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="description_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']',
	  ));
      
      $fieldset->addField('status', 'select', array(
          'name'      => 'status',
          'label'     => Mage::helper('affiliateplusprogram')->__('Status'),
          'required'  => true,
          'values'	  => Mage::getSingleton('affiliateplusprogram/status')->getOptions(),
          'disabled'  => ($inStore && !$data['status_in_store']),
          'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="status_default" name="status_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['status_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="status_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']',
      ));
      
      $fieldset->addField('show_in_welcome', 'select', array(
          'name'      => 'show_in_welcome',
          'label'     => Mage::helper('affiliateplusprogram')->__('Visible'),
          'values'	  => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
          'disabled'  => ($inStore && !$data['show_in_welcome_in_store']),
          'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="show_in_welcome_default" name="show_in_welcome_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['show_in_welcome_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="show_in_welcome_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']',
      ));
	  
      $fieldset->addField('autojoin', 'select', array(
          'name'      => 'autojoin',
          'label'     => Mage::helper('affiliateplusprogram')->__('Allow auto join'),
          'values'	  => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
      ));
	  
      $fieldset->addField('scope', 'select', array(
          'name'      => 'scope',
          'label'     => Mage::helper('affiliateplusprogram')->__('Apply to'),
          'required'  => true,
          'values'	  => Mage::getSingleton('affiliateplusprogram/scope')->toOptionArray(),
          'onchange'  => 'changeScope(this)',
      ));
	  
      $fieldset->addField('customer_groups', 'multiselect', array(
          'name'      => 'customer_groups',
          'label'     => Mage::helper('affiliateplusprogram')->__('Customer Groups'),
          'values'	=> Mage::getResourceModel('customer/group_collection')
							->addFieldToFilter('customer_group_id', array('gt'=> 0))
							->load()
							->toOptionArray(),
		  'after_element_html' =>  '<script type="text/javascript">
				function changeScope(el){
                    if (el.value != '.Magestore_Affiliateplusprogram_Model_Scope::SCOPE_GROUPS.'){
                        $(\'customer_groups\').up(\'tr\').hide();
                    } else {
                        $(\'customer_groups\').up(\'tr\').show();
                    }
				}
                changeScope($(\'scope\'));
			</script>',
      ));
      
      $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
      $fieldset->addField('valid_from', 'date', array(
          'name'    => 'valid_from',
          'label'   => Mage::helper('affiliateplusprogram')->__('From Date'),
          'format'	=> $dateFormatIso,
          'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
          'input_format'	=> Varien_Date::DATE_INTERNAL_FORMAT,
      ));
      
      $fieldset->addField('valid_to', 'date', array(
          'name'    => 'valid_to',
          'label'   => Mage::helper('affiliateplusprogram')->__('To Date'),
          'format'	=> $dateFormatIso,
          'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
          'input_format'	=> Varien_Date::DATE_INTERNAL_FORMAT,
      ));
      
      Mage::dispatchEvent('affiliateplusprogram_adminhtml_edit_form',array('fieldset' => $fieldset,'data_form'=>$data,'in_store'=>$inStore));
      
      if ($data['program_id']){
      	 $fieldset->addField('created_date', 'note', array(
	         'label'     => Mage::helper('affiliateplusprogram')->__('Created Date'),
	         'text'      => '<strong>'.$this->formatDate($data['created_date'],'long',false).'</strong>',
	     ));
	     
	     $fieldset->addField('num_account', 'note', array(
	         'label'     => Mage::helper('affiliateplusprogram')->__('Number of Affiliate Accounts'),
	         'text'      => '<strong>'.$data['num_account'].'</strong>',
	     ));
      	 
	     $fieldset->addField('total_sales_amount', 'note', array(
	         'label'     => Mage::helper('affiliateplusprogram')->__('Total Sales Amount'),
	         'text'      => '<strong>'.Mage::app()->getStore($inStore)->getBaseCurrency()->format($data['total_sales_amount'],array(),false).'</strong>',
	     ));
      } else {
		$data['status'] = 1;
		$data['autojoin'] = 1;
		$data['show_in_welcome'] = 1;
		$data['use_coupon'] = 1;
	  }
      
      $form->setValues($data);
      
      return parent::_prepareForm();
  }
}