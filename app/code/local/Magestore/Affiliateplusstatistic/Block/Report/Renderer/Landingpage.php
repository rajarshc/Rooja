<?php
class Magestore_Affiliateplusstatistic_Block_Report_Renderer_Landingpage extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		$url = sprintf('%s%s',Mage::getBaseUrl(),trim($row->getUrlPath(),'/'));
        return "<a href='$url' target='_blank'>$url</a>";
	}
}