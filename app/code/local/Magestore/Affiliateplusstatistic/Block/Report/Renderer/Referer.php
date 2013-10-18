<?php
class Magestore_Affiliateplusstatistic_Block_Report_Renderer_Referer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if ($row->getReferer()){
			return sprintf('http://%s',$row->getReferer());
		}
		return $this->__('N/A');
	}
}
