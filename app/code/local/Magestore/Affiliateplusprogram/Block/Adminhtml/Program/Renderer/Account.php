<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Renderer_Account extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		return sprintf('<a href="%s" title="%s">%s</a>',
			$this->getUrl('affiliateplusadmin/adminhtml_account/edit',array(
				'_current'	=>true,
				'id'		=> $row->getAccountId(),
				'store'		=> $this->getRequest()->getParam('store'),
			)),
			Mage::helper('affiliateplusprogram')->__('View Affiliate Account Detail'),
			$row->getAccountName()
		);
	}
}