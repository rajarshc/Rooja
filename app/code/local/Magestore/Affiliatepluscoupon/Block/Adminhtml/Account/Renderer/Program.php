<?php

class Magestore_Affiliatepluscoupon_Block_Adminhtml_Account_Renderer_Program extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if ($row->getProgramId()){
			return sprintf("<a href='%s' title='%s'>%s</a>"
				,$this->getUrl('affiliateplusprogramadmin/adminhtml_program/edit',array('id' => $row->getProgramId()))
				,$this->__('View Program')
				,$row->getProgramName()
			);
		}
		return $this->__($row->getProgramName());
	}
}