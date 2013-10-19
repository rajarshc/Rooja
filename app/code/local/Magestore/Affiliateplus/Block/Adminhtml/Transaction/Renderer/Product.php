<?php 
class Magestore_Affiliateplus_Block_Adminhtml_Transaction_Renderer_Product
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/* Render Grid Column*/
	//show each product in a row
	public function render(Varien_Object $row) 
	{
        if($row->getOrderItemIds()){
            $html = Mage::helper('affiliateplus')->getBackendProductHtmls($row->getOrderItemIds());
            return sprintf('%s', $html);
        }  else {
            return sprintf('%s', $row->getOrderItemNames());
        }
	}
}