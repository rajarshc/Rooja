<?php 
class Magestore_Affiliatepluslevel_Block_Adminhtml_Account_Renderer_Toptier
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/* Render Grid Column*/
	public function render(Varien_Object $row) 
	{
		if($row->getToptierId())
			return sprintf('
				<a href="%s" title="%s">%s</a>',
				$this->getUrl('*/*/edit/', array('_current'=>true, 'id' => $row->getToptierId())),
				Mage::helper('affiliatepluslevel')->__('View Account Detail'),
				$row->getToptierName()
			);
		else
			return Mage::helper('affiliatepluslevel')->__('N/A');
	}
}