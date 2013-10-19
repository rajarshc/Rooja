<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $form->setHtmlIdPrefix('affiliateplusprogram_');
      
      $dataObj = new Varien_Object(array(
      	'program_id' => '',
      	'discount_in_store'	=> '',
      	'commission_in_store'	=> '',
      	'discount_type_in_store'	=> '',
      	'commission_type_in_store'	=> '',
	  ));
      if (Mage::getSingleton('adminhtml/session')->getAffiliateplusprogramData()){
          $data = Mage::getSingleton('adminhtml/session')->getAffiliateplusprogramData();
          $programId = isset($data['program_id']) ? $data['program_id'] : 0;
          $model = Mage::getModel('affiliateplusprogram/program')
          		->load($programId)
		  		->setData($data);
          Mage::getSingleton('adminhtml/session')->setAffiliateplusprogramData(null);
      } elseif (Mage::registry('affiliateplusprogram_data')){
          $model = Mage::registry('affiliateplusprogram_data');
          $data = $model->getData();
      }
      if (isset($data)) $dataObj->addData($data);
      $data = $dataObj->getData();
      
      $this->setForm($form);
      $fieldset = $form->addFieldset('affiliateplusprogram_actions_commission', array('legend'=>Mage::helper('affiliateplusprogram')->__('Commission')));
      
      $inStore = $this->getRequest()->getParam('store');
      $defaultLabel = Mage::helper('affiliateplusprogram')->__('Use Default');
      $defaultTitle = Mage::helper('affiliateplusprogram')->__('-- Please Select --');
      $scopeLabel = Mage::helper('affiliateplusprogram')->__('STORE VIEW');

      $fieldset->addField('affiliate_type', 'select', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Pay commission'),
          'name'      => 'affiliate_type',
          'values'    => Mage::getSingleton('affiliateplusprogram/system_config_source_type')->toOptionArray(),
          'disabled'  => ($inStore && !$data['affiliate_type_in_store']),
          'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="affiliate_type_default" name="affiliate_type_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['affiliate_type_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="affiliate_type_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']',
	  ));

      $fieldset->addField('commission_type', 'select', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Commission Type'),
          'name'      => 'commission_type',
          'values'    => Mage::getSingleton('affiliateplus/system_config_source_fixedpercentage')->toOptionArray(),
          'disabled'  => ($inStore && !$data['commission_type_in_store']),
          'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="commission_type_default" name="commission_type_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['commission_type_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="commission_type_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']',
	  ));
	  
      $fieldset->addField('commission', 'text', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Commission Value'),
          'required'  => true,
          'name'      => 'commission',
          'disabled'  => ($inStore && !$data['commission_in_store']),
          'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="commission_default" name="commission_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['commission_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="commission_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']',
      ));
	  
      $fieldset->addField('sec_commission', 'select', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Use different commission from 2nd order of a Customer'),
          'name'      => 'sec_commission',
          'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
          'disabled'  => ($inStore && !$data['sec_commission_in_store']),
          'after_element_html' => '<p class="note">' . Mage::helper('affiliateplusprogram')->__('Select "No" to apply above commission for all orders') . '</p>' .
          ($inStore ? '</td><td class="use-default">
			<input id="sec_commission_default" name="sec_commission_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['sec_commission_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="sec_commission_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']'),
      ));
      
      $fieldset->addField('sec_commission_type', 'select', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Commission Type (from 2nd order)'),
          'name'      => 'sec_commission_type',
          'values'    => Mage::getSingleton('affiliateplus/system_config_source_fixedpercentage')->toOptionArray(),
          'disabled'  => ($inStore && !$data['sec_commission_type_in_store']),
          'after_element_html' => ($inStore ? '</td><td class="use-default">
			<input id="affiliateplusprogram_sec_commission_type_inherit" name="sec_commission_type_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['sec_commission_type_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="affiliateplusprogram_sec_commission_type_inherit" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']'),
      ));
      
      $fieldset->addField('secondary_commission', 'text', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Commission Value (from 2nd order)'),
          'name'      => 'secondary_commission',
          'disabled'  => ($inStore && !$data['secondary_commission_in_store']),
          'after_element_html' => ($inStore ? '</td><td class="use-default">
			<input id="affiliateplusprogram_secondary_commission_inherit" name="secondary_commission_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['secondary_commission_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="affiliateplusprogram_secondary_commission_inherit" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']'),
      ));
      
      Mage::dispatchEvent('affiliateplusprogram_adminhtml_edit_actions',array(
	  	'form'	=> $form,
	  	'form_data'	=> $data,
        'fieldset'  => $fieldset,
        'in_store'  => $inStore,
	  ));
      
      $fieldset = $form->addFieldset('affiliateplusprogram_actions_discount', array('legend'=>Mage::helper('affiliateplusprogram')->__('Discount')));
      
      $fieldset->addField('discount_type', 'select', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Discount Type'),
          'name'      => 'discount_type',
          'values'    => Mage::getSingleton('affiliateplus/system_config_source_discounttype')->toOptionArray(),
          'disabled'  => ($inStore && !$data['discount_type_in_store']),
          'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="discount_type_default" name="discount_type_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['discount_type_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="discount_type_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']',
	  ));
	  
      $fieldset->addField('discount', 'text', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Discount Value'),
          'required'  => true,
          'name'      => 'discount',
          'disabled'  => ($inStore && !$data['discount_in_store']),
          'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="discount_default" name="discount_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['discount_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="discount_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']',
      ));
	  
      $fieldset->addField('sec_discount', 'select', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Use different discount from 2nd order of a Customer'),
          'name'      => 'sec_discount',
          'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
          'disabled'  => ($inStore && !$data['sec_discount_in_store']),
          'after_element_html' => '<p class="note">' . Mage::helper('affiliateplusprogram')->__('Select "No" to apply above discount for all orders') . '</p>' .
          ($inStore ? '</td><td class="use-default">
			<input id="affiliateplusprogram_sec_discount_inherit" name="sec_discount_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['sec_discount_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="affiliateplusprogram_sec_discount_inherit" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']'),
      ));
      
      $fieldset->addField('sec_discount_type', 'select', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Discount Type (from 2nd order)'),
          'name'      => 'sec_discount_type',
          'values'    => Mage::getSingleton('affiliateplus/system_config_source_discounttype')->toOptionArray(),
          'disabled'  => ($inStore && !$data['sec_discount_type_in_store']),
          'after_element_html' => ($inStore ? '</td><td class="use-default">
			<input id="affiliateplusprogram_sec_discount_type_inherit" name="sec_discount_type_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['sec_discount_type_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="affiliateplusprogram_sec_discount_type_inherit" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']'),
      ));
      
      $fieldset->addField('secondary_discount', 'text', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Discount Value (from 2nd order)'),
          'name'      => 'secondary_discount',
          'disabled'  => ($inStore && !$data['secondary_discount_in_store']),
          'after_element_html' => ($inStore ? '</td><td class="use-default">
			<input id="affiliateplusprogram_secondary_discount_inherit" name="secondary_discount_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['secondary_discount_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="affiliateplusprogram_secondary_discount_inherit" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']'),
      ));
	  
      $fieldset->addField('customer_group_ids', 'multiselect', array(
          'label'     => Mage::helper('affiliateplusprogram')->__('Apply for Customer Groups'),
          'required'  => false,
          'values'    => Mage::getResourceModel('customer/group_collection')->load()
							->toOptionArray(),
          'name'      => 'customer_group_ids',
          'disabled'  => ($inStore && !$data['customer_group_ids_in_store']),
          'after_element_html' => '<p class="note">' . Mage::helper('affiliateplusprogram')->__('Who buy product directly. Select none for all customers.') . '</p>' .
          ($inStore ? '</td><td class="use-default">
			<input id="customer_group_ids_default" name="customer_group_ids_default" type="checkbox" value="1" class="checkbox config-inherit" '.($data['customer_group_ids_in_store'] ? '' : 'checked="checked"').' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="customer_group_ids_default" class="inherit" title="'.$defaultTitle.'">'.$defaultLabel.'</label>
          </td><td class="scope-label">
			['.$scopeLabel.']
          ' : '</td><td class="scope-label">
			['.$scopeLabel.']'),
      ));
      
      $model->setData('actions',$model->getData('actions_serialized'));
      $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('adminhtml/promo_quote/newActionHtml/form/affiliateplusprogram_actions_fieldset'));
      
	  Mage::dispatchEvent('affiliateplusprogram_adminhtml_edit_actions_discount',array(
        'fieldset'  => $fieldset,
	  	'form'	=> $form,
	  	'form_data'	=> $data,
	  ));
      
      $fieldset = $form->addFieldset('actions_fieldset', array('legend'=>Mage::helper('affiliateplusprogram')->__('Use the program only to cart items matching the following conditions (leave blank for all items)')))->setRenderer($renderer);
      
      $fieldset->addField('actions','text',array(
      	'name'	=> 'actions',
      	'label'	=> Mage::helper('affiliateplusprogram')->__('Apply To'),
      	'title'	=> Mage::helper('affiliateplusprogram')->__('Apply To'),
      	'required'	=> true,
	  ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/actions'));
      
      $form->setValues($data);
      
      $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
          ->addFieldMap('affiliateplusprogram_sec_commission', 'use_commission')
          ->addFieldMap('affiliateplusprogram_sec_commission_type', 'commission_type')
          ->addFieldMap('affiliateplusprogram_secondary_commission', 'commission')
          ->addFieldDependence('commission_type', 'use_commission', '1')
          ->addFieldDependence('commission', 'use_commission', '1')
          ->addFieldMap('affiliateplusprogram_sec_discount', 'use_discount')
          ->addFieldMap('affiliateplusprogram_sec_discount_type', 'discount_type')
          ->addFieldMap('affiliateplusprogram_secondary_discount', 'discount')
          ->addFieldDependence('discount_type', 'use_discount', '1')
          ->addFieldDependence('discount', 'use_discount', '1')
      );
      
      return parent::_prepareForm();
  }
}
