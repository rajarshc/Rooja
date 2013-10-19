<?php 
class Magestore_Affiliateplus_Block_Adminhtml_Transaction_Renderer_Customer
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/* Render Grid Column*/
	public function render(Varien_Object $row) 
	{
		if($row->getCustomerId())
			return sprintf('
				<a href="%s" title="%s">%s</a>',
				$this->getUrl('adminhtml/customer/edit/', array('_current'=>true, 'id' => $row->getCustomerId())),
				Mage::helper('affiliateplus')->__('View Customer Detail'),
				$row->getCustomerEmail()
			);
		else if($row->getCustomerEmail())
			return sprintf('%s', $row->getCustomerEmail());	
        else
            return sprintf('%s', 'N/A');	
	}
}