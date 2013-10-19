<?php 
class Magestore_Affiliateplus_Block_Adminhtml_Transaction_Renderer_Order
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/* Render Grid Column*/
	public function render(Varien_Object $row) 
	{
        if($row->getOrderId()){
            return sprintf('
                <a href="%s" title="%s">%s</a>',
                $this->getUrl('adminhtml/sales_order/view/', array('_current'=>true, 'order_id' => $row->getOrderId())),
                Mage::helper('catalog')->__('View Order Detail'),
                $row->getOrderNumber()
            );
        }else{
            return sprintf('%s', $row->getOrderNumber());
        }
	}
}