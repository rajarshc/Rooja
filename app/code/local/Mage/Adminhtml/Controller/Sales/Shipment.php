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

 * @author      Magento Core Team <core@magentocommerce.com>

 */

class Mage_Adminhtml_Controller_Sales_Shipment extends Mage_Adminhtml_Controller_Action

{

    /**

     * Additional initialization

     *

     */

    protected function _construct()

    {

        $this->setUsedModuleName('Mage_Sales');

    }



    /**

     * Init layout, menu and breadcrumb

     *

     * @return Mage_Adminhtml_Sales_ShipmentController

     */

    protected function _initAction()

    {

        $this->loadLayout()

            ->_setActiveMenu('sales/order')

            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))

            ->_addBreadcrumb($this->__('Shipments'),$this->__('Shipments'));

        return $this;

    }



    /**

     * Shipments grid

     */

    public function indexAction()

    {

        $this->_title($this->__('Sales'))->_title($this->__('Shipments'));



        $this->_initAction()

            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_shipment'))

            ->renderLayout();

    }



    /**

     * Shipment information page

     */

    public function viewAction()

    {

        if ($shipmentId = $this->getRequest()->getParam('shipment_id')) {

            $this->_forward('view', 'sales_order_shipment', null, array('come_from'=>'shipment'));

        } else {

            $this->_forward('noRoute');

        }

    }



    public function pdfshipmentsAction(){

        $shipmentIds = $this->getRequest()->getPost('shipment_ids');

        if (!empty($shipmentIds)) {

            $shipments = Mage::getResourceModel('sales/order_shipment_collection')

                ->addAttributeToSelect('*')

                ->addAttributeToFilter('entity_id', array('in' => $shipmentIds))

                ->load();

            if (!isset($pdf)){

                $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);

            } else {

                $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);

                $pdf->pages = array_merge ($pdf->pages, $pages->pages);

            }



            return $this->_prepareDownloadResponse('packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');

        }

        $this->_redirect('*/*/');

    }





    public function printAction()

    {

        /** @see Mage_Adminhtml_Sales_Order_InvoiceController */

        if ($shipmentId = $this->getRequest()->getParam('invoice_id')) { // invoice_id o_0

            if ($shipment = Mage::getModel('sales/order_shipment')->load($shipmentId)) {

                $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(array($shipment));

                $this->_prepareDownloadResponse('packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');

            }

        }

        else {

            $this->_forward('noRoute');

        }

    }



	 public function exportlogisticsAction(){

		

		$shipmentIds = $this->getRequest()->getPost('shipment_ids');

        if (!empty($shipmentIds)) {

            $shipments = Mage::getResourceModel('sales/order_shipment_collection')

                ->addAttributeToSelect('*')

                ->addAttributeToFilter('entity_id', array('in' => $shipmentIds))

                ->load();

			$template = '"{{increment_id}}","{{order_id}}","{{order_created_at}}","{{shipping_name}}","{{created_at}}","{{tracking}}","{{courier}}","{{qty}}","{{product_name}}","{{sku}}","{{short_description}}","{{price}}","{{shipping_address1}}","{{shipping_city}}","{{shipping_state}}","{{shipping_zip}}","{{shipping_phone}}","{{total}}","{{shipping}}","{{cod_amount}}","{{mode}}"';			

        

		

			$headers = new Varien_Object(array(

				'increment_id'         => Mage::helper('sales')->__('Sr. No.'),

				'order_id'         => Mage::helper('sales')->__('Order No.'),

						'order_created_at' => Mage::helper('sales')->__('Order Date'),

						'shipping_name'  => Mage::helper('sales')->__('Customer Name'),

				'created_at' => Mage::helper('sales')->__('Dispatch Date'),

						'tracking' => Mage::helper('sales')->__('AWB No.'),

						'courier' => Mage::helper('sales')->__('Couriers vendor name'),

				'qty' => Mage::helper('sales')->__('Qty'),

						'product_name' => Mage::helper('sales')->__('Product Name'),

						'sku'  => Mage::helper('sales')->__('Product SKU'),

						'short_description'  => Mage::helper('sales')->__('Supplier Code'),

						'price'  => Mage::helper('sales')->__('Price'),

						

				

				'shipping_address1'  => Mage::helper('sales')->__('Shipping Add'),

				'shipping_city'  => Mage::helper('sales')->__('Shipping City'),

				'shipping_state'  => Mage::helper('sales')->__('Shipping State'),

				'shipping_zip'  => Mage::helper('sales')->__('Shipping Zip'),

				'shipping_phone'  => Mage::helper('sales')->__('Shipping Tel. No.'),

				

				'total'  => Mage::helper('sales')->__('Amount'),

						'shipping'  => Mage::helper('sales')->__('Shipping Charge'),

						'cod_amount'  => Mage::helper('sales')->__('COD Charge'),

				'mode'  => Mage::helper('sales')->__('Mode')

						//'shipping_comment'  => Mage::helper('sales')->__('Remarks'),

				));

			



			$content = $headers->toString($template) . "\n";



			foreach($shipments as $shipment) {

				// Get order data

				$order = Mage::getModel('sales/order')->load($shipment->getOrderId());

				

				//Mage::log($productData);

				

				$payment = $order->getPayment();

				// Get Shipping

				$shipping = $order->getShippingAddress();

				

				$invoiceData = array(

						'increment_id' 	=> $shipment->getIncrementId(),

						'order_id' 	=> $order->getIncrementId(),

						'order_created_at' 	=>$order->getCreatedAt(),

						'shipping_name' => $shipping->getFirstname() . " " . $shipping->getLastname(),

						'created_at' 	=> $shipment->getCreatedAt(),

						);

				

				$remove = array(",","'",'"');

				$shippingData = array(

						'shipping_address1' => (string)str_replace($remove, '', implode("\n",$shipping->getStreet())),

						'shipping_city' => $shipping->getCity(),

						'shipping_state' => $shipping->getRegion(),

						'shipping_zip' => $shipping->getPostcode(),

						'shipping_phone' => $shipping->getTelephone());

						

				$productContent = "";

				$i=0;

				foreach ($order->getAllItems() as $item){

					if ($item->getParentItem()) {

						continue;

					}

					

					// get product

					$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$item->getSku());

					//Mage::log($item->getSku());

					if ( $i==0 ){

						$productData = array(

								'qty' => $item->getQtyOrdered(),

								'product_name' => $product->getName(),

								'sku' => $product->getSku(),

								'short_description' => trim($product->getShortDescription()),

								'price' => $item->getPrice()

								);

						} else {

							$otherProductData = array(

									'qty' => $item->getQtyOrdered(),

									'product_name' => $product->getName(),

									'sku' => $product->getSku(),

									'short_description' => trim($product->getShortDescription()),

								'price' => $item->getPrice()

									);

							$tempData = new Varien_Object();

							$tempData->setData($otherProductData);	

							$productContent .= $tempData->toString($template) . "\n";

							}

					$i++;

					

				}

				

				//Mage::log($item);

				

				// Get order stuff

				$order = $shipment->getOrder();

				$payment = $order->getPayment();

				$shipments = Mage::getResourceModel('sales/order_shipment_collection')

		                    ->addAttributeToSelect('*')

    			              ->setOrderFilter($order->getId())

												->setOrder('created_at', 'ASC')

           			        ->load();

				foreach($shipments as $shipment);

				

				$basic = $shipment->getBaseGrandTotal() - $shipment->getShippingAmount() -  $shipment->getBaseTaxAmount();

				

				$rates = Mage::getModel('sales/order_tax')->getCollection()->loadByOrder($order)->toArray();

				if($rates['totalRecords'])

					$rateCode = $rates['items'][0]['code'];

				else

					$rateCode = "UNKNOWN";

				

				

				$orderData = array(

					//'tax_rate' => $basic ? round(($shipment->getBaseTaxAmount() / $basic) * 100, 2) : 0,

					//'tax_type' => $rateCode,

					//'basic' => $basic,

					//'tax' => $shipment->getBaseTaxAmount(),

						'total' => $order->getBaseGrandTotal(),

						'shipping' => $order->getBaseShippingAmount(),

						'cod_amount' => $order->getCodFee(),

					'mode' => $payment->getMethod());

					

				//Mage::log($payment->getFees());

				

				$tracks = array();

				$trackingData = array();

				if ($shipment) {

					$tracks = $shipment->getAllTracks();

				}

        

				if (count($tracks)) {

					foreach ($tracks as $track) {

						$CarrierCode = $track->getCarrierCode();

						if ($CarrierCode!='custom') {

							$carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($CarrierCode);

							$carrierTitle = $carrier->getConfigData('title');

						} else {

							$carrierTitle = Mage::helper('sales')->__('Custom Value');

						}

						

						$trackingData = array(

							'courier' => $track->getTitle(),

							'tracking' => $track->getNumber());

						break;

					}

				}



				$allData = new Varien_Object();

				$allData->setData(array_merge($invoiceData,$trackingData, $productData,$shippingData, $orderData));

					

				$content .= $allData->toString($template) . "\n" . $productContent;

				

			}

			

            return $this->_prepareDownloadResponse('shipment_export.csv', $content);

        }

        $this->_redirect('*/*/');

    }

	

	

    protected function _isAllowed()

    {

        return Mage::getSingleton('admin/session')->isAllowed('sales/shipment');

    }

}

