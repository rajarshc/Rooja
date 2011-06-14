<?php

class AW_Rma_Block_Adminhtml_Sales_Order_View_Tabs_Requests extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Rma
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */    protected $_product = null;

    protected $_config = null;

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('awrma')->__('RMA Requests');
    }

    public function getTabTitle()
    {
        return Mage::helper('awrma')->__('RMA Requests');
    }



    public function canShowTab(){
        return true;
    }

    /**
     * Check if tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
	

    protected function _toHtml(){
    	
		$id = $this->getRequest()->getParam('order_id');
		$order = Mage::getModel('sales/order')->load($id);

		$grid = $this->getLayout()->createBlock('awrma/adminhtml_rma_grid');
		$grid->setOrderMode($order->getIncrementId());

		$button = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setClass('add')
			->setType('button')
            ->setStyle('margin:10px 0;')
			->setOnClick('window.location.href=\''.$this->getUrl('rma/adminhtml_rma/createrequest', array('order'=>$order->getIncrementId())).'\'')
			->setLabel('Create request from this order');

		return  $button->toHtml() . $grid->toHtml();

		
    }
}
