<?php
class Magestore_Affiliateplusstatistic_Block_Report_Renderer_Trafficsource extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if ($row->getReferer()){
			$source = sprintf('http://%s',$row->getReferer());
            return "<a href='$source' target='_blank'>$source</a>";
		}
		return $this->__('N/A');
	}
}
