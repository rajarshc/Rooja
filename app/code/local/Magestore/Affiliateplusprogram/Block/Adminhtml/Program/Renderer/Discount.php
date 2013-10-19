<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Renderer_Discount extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if ($row->getDiscountType() == 'percentage'){
			return sprintf("%.2f",$row->getDiscount()).'%';
		} else {
			return Mage::app()->getStore()->getBaseCurrency()->format($row->getDiscount());
		}
	}
}