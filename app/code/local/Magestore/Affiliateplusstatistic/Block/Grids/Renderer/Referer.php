<?php
class Magestore_Affiliateplusstatistic_Block_Grids_Renderer_Referer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if ($row->getReferer()){
			return sprintf('<a href="http://%s" target="_blank">%s</a>',$row->getReferer(),$row->getReferer());
		}
		return $this->__('N/A');
	}
}