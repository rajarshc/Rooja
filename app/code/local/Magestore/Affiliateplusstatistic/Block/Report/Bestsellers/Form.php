<?php
class Magestore_Affiliateplusstatistic_Block_Report_Bestsellers_Form extends Mage_Adminhtml_Block_Report_Filter_Form
{
	protected function _prepareForm(){
		parent::_prepareForm();
        $form = $this->getForm();
		$htmlIdPrefix = $form->getHtmlIdPrefix();
		$fieldset = $this->getForm()->getElement('base_fieldset');
		if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset){
			$values = array(
				array('value' => 1, 'label' => $this->__('Completed')),
				array('value' => 2, 'label' => $this->__('Pending')),
				array('value' => 3, 'label' => $this->__('Canceled')),
			);

			try {
                $fieldset->removeField('show_order_statuses');
                $fieldset->removeField('order_statuses');
                $fieldset->removeField('report_type');
                $fieldset->removeField('show_empty_rows');
            } catch (Exception $e) {
                // fixed for all magento version
            }

			$fieldset->addField('show_order_statuses', 'select', array(
				'name'	  => 'show_order_statuses',
				'label'	 => $this->__('Transaction Status'),
				'options'   => array(
						'0' => $this->__('Any'),
						'1' => $this->__('Specified'),
					),
				'note'	  => $this->__('Applies to Any of the Specified Transaction Statuses'),
			), 'to');

			$fieldset->addField('order_statuses', 'multiselect', array(
				'name'	  => 'order_statuses',
				'values'	=> $values,
				'display'   => 'none'
			), 'show_order_statuses');
            
            $fieldset->addField('show_empty_rows', 'select', array(
                'name'      => 'show_empty_rows',
                'options'   => array(
                    '1' => Mage::helper('reports')->__('Yes'),
                    '0' => Mage::helper('reports')->__('No')
                ),
                'label'     => Mage::helper('reports')->__('Empty Rows'),
                'title'     => Mage::helper('reports')->__('Empty Rows')
            ));

			// define field dependencies
			if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) {
				$this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
					->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
					->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
					->addFieldDependence('order_statuses', 'show_order_statuses', '1')
				);
			}
		}
		return $this;
	}
}
