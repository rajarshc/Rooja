<?php
class Magestore_Affiliateplusstatistic_Block_Report_Renderer_Uniques extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if ($row->getVisitAt())
			return $row->getIsUnique();
		return Mage::helper('affiliateplusstatistic')->__('Summed up by ip address = %s',$row->getIsUnique());
	}
}