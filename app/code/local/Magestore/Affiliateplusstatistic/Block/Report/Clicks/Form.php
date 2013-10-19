<?php
class Magestore_Affiliateplusstatistic_Block_Report_Clicks_Form extends Mage_Adminhtml_Block_Report_Filter_Form
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
            
            $fieldset->addField('filter_group_by','select',array(
                'name'  => 'filter_group_by',
                'label' => $this->__('Group By'),
                'options'   => array(
                    '1' => $this->__('Period'),
                    '2' => $this->__('Affiliate Account'),
                    '3' => $this->__('Banner')
                ),
            ),'store_ids');
            
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
					->addFieldMap("{$htmlIdPrefix}filter_group_by", 'filter_group_by')
					// ->addFieldMap("{$htmlIdPrefix}period_type", 'period_type')
					->addFieldMap("{$htmlIdPrefix}show_empty_rows", 'show_empty_rows')
					// ->addFieldDependence('period_type', 'filter_group_by', '1')
					->addFieldDependence('show_empty_rows', 'filter_group_by', '1')
				);
			}
		}
		return $this;
	}
}
