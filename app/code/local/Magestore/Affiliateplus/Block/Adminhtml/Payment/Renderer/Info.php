<?php 
class Magestore_Affiliateplus_Block_Adminhtml_Payment_Renderer_Info
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		return $row->getPaymentMethodHtml();
	}
}