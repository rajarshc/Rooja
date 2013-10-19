<?php 
class Magestore_Affiliateplus_Block_Adminhtml_Payment_Renderer_Actions
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
            $edit=$this->getUrl('*/*/edit', array('id' => $row->getId()));
            $cancel=$this->getUrl('*/*/cancelPayment', array('id' => $row->getId()));
            if($row->getStatus()<=2){
                return sprintf('<a href="%s" title="%s">%s</a> | <a href="%s" title="%s">%s</a>',
				$edit,
				Mage::helper('affiliateplus')->__('Edit Withdrawals'),
				Mage::helper('affiliateplus')->__('Edit'),
                                $cancel,
				Mage::helper('affiliateplus')->__('Cancel Withdrawals'),
				Mage::helper('affiliateplus')->__('Cancel')
			);
            }  else {
                return sprintf('<a href="%s" title="%s">%s</a>',
				$edit,
				Mage::helper('affiliateplus')->__('Edit Withdrawals'),
				Mage::helper('affiliateplus')->__('Edit')
			);
            }
	}
}