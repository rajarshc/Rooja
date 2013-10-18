<?php 
class Magestore_Affiliateplus_Block_Adminhtml_Payment_Renderer_Account
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if($row->getAccountId())
			return sprintf('
				<a href="%s" title="%s">%s</a>',
				$this->getUrl('affiliateplusadmin/adminhtml_account/edit/', array('_current'=>true, 'id' => $row->getAccountId())),
				Mage::helper('affiliateplus')->__('View Affiliate Account Detail'),
				$row->getAccountEmail()
			);
		return $row->getAccountEmail();	
	}
}