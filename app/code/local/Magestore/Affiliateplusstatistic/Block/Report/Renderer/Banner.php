<?php
class Magestore_Affiliateplusstatistic_Block_Report_Renderer_Banner extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
        if ($row->getBannerTitle()) {
            return $row->getBannerTitle();
        }
        return $this->__('N/A');
	}
}