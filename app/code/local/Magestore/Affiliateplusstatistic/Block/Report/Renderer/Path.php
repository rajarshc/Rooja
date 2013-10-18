<?php
class Magestore_Affiliateplusstatistic_Block_Report_Renderer_Path extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		return sprintf('%s%s',Mage::getBaseUrl(),trim($row->getUrlPath(),'/'));
	}
}