<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
 
 
include_once("Mage/Adminhtml/controllers/Sales/OrderController.php");
class Katana_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
	
	public function exportlogisticsAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (!empty($orderIds)) {

			$template = '"{{increment_id}}","{{created_at}}","{{customer}}","{{product_name}}","{{sku}}","{{qty}}","{{size}}"';			
		
			$headers = new Varien_Object(array(
				'increment_id'         => Mage::helper('sales')->__('Order Id'),
				'created_at' => Mage::helper('sales')->__('Date'),
				'customer'  => Mage::helper('sales')->__('Customer Name'),
				'product_name' => Mage::helper('sales')->__('Product Name'),
				'sku'  => Mage::helper('sales')->__('SKU'),
				'qty'  => Mage::helper('sales')->__('Qty Ordered'),
				'size'  => Mage::helper('sales')->__('Size'),
				));
			

			$content = $headers->toString($template) . "\n";
            foreach($orderIds as $orderId) {
				// Get order data
				$order = Mage::getModel('sales/order')->load($orderId);
				$payment = $order->getPayment();

				$orderData = array(
					'increment_id' 	=> $order->getIncrementId(),
					'created_at' 	=> $order->getCreatedAt(),
					'customer'		=> $order->getCustomerFirstname() . " " . $order->getCustomerLastname(),
				);
				

				// Get items
				foreach ($order->getAllItems() as $item){
					if ($item->getParentItem()) {
						continue;
					}
				
					// get product
					$product = Mage::getModel('catalog/product')->load($item->getProductId());
				
				
					$size = $product->getAttributeText('size');
					
					if(!$size) {
						foreach ($item->getProductOptions() as $option) {
							if($option[0]['label'] == 'Size') {
								$size = $option[0]['value'];
								break;
							}
						}
					}
				
				
					$orderItemData = array(
						'product_name' => $product->getName(),
						'sku' => $product->getSku(),
						'qty' => $item->getQtyOrdered(),
						'size' => $size,
					);
					

					$allData = new Varien_Object();
					$allData->setData(array_merge($orderData, $orderItemData));
					
					$content .= $allData->toString($template) . "\n";
				}
				
				$allData = new Varien_Object();
				$allData->setData('');
				
				$content .= $allData->toString($template) . "\n";
			}
            return $this->_prepareDownloadResponse('order_export.csv', $content);
        }
        $this->_redirect('*/*/');
    }	
}
