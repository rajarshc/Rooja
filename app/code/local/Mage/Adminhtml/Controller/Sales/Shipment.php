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

	 public function exportshipmentsAction(){
        $invoicesIds = $this->getRequest()->getPost('shipment_ids');
        if (!empty($shipmentIds)) {
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $shipmentIds))
                ->load();
			$template = '"{{increment_id}}","{{order_id}}","{{created_at}}","{{items}}","{{shipping_name}}","{{shipping_address1}}","{{shipping_city}}","{{shipping_state}}","{{shipping_zip}}","{{shipping_phone}}","{{tax_type}}","{{tax_rate}}","{{basic}}","{{tax}}","{{total}}","{{shipping}}","{{cod_amount}}","{{mode}}","{{courier}}","{{tracking}}"';			
        
		
			$headers = new Varien_Object(array(
				'increment_id'         => Mage::helper('sales')->__('Shipment Id'),
				'order_id'         => Mage::helper('sales')->__('Order Id'),
				'created_at' => Mage::helper('sales')->__('Date'),
				'items' => Mage::helper('sales')->__('Items'),
				'shipping_name'  => Mage::helper('sales')->__('Shipping Name'),
				'shipping_address1'  => Mage::helper('sales')->__('Shipping Add1'),
				'shipping_city'  => Mage::helper('sales')->__('Shipping City'),
				'shipping_state'  => Mage::helper('sales')->__('Shipping State'),
				'shipping_zip'  => Mage::helper('sales')->__('Shipping Zip'),
				'shipping_phone'  => Mage::helper('sales')->__('Shipping Phone'),
				'tax_type' => Mage::helper('sales')->__('Tax Type'),
				'tax_rate'  => Mage::helper('sales')->__('Tax Rate'),
				'basic'  => Mage::helper('sales')->__('Basic'),
				'tax'  => Mage::helper('sales')->__('Tax Amt'),
				'total'  => Mage::helper('sales')->__('Total Amt'),
				'shipping' => Mage::helper('sales')->__('Shipping'),
				'cod_amount' => Mage::helper('sales')->__('Cod Amount'),
				'mode'  => Mage::helper('sales')->__('Mode'),
				'courier' => Mage::helper('sales')->__('Courier'),
				'tracking' => Mage::helper('sales')->__('Tracking')
				));
			

			$content = $headers->toString($template) . "\n";

			foreach($invoices as $invoice) {
				// Get order data
				$order = Mage::getModel('sales/order')->load($invoice->getOrderId());
				$payment = $order->getPayment();


				$invoiceData = array(
				'increment_id' 	=> $invoice->getIncrementId(),
				'order_id' 	=> $order->getIncrementId(),
				'created_at' 	=> $invoice->getCreatedAt(),
				'items'			=> $invoice->getTotalQty(),
				);
				

				// Get Shipping
				$shipping = $order->getShippingAddress();
				$shippingData = array(
				'shipping_name' => $shipping->getFirstname() . " " . $shipping->getLastname(),
				'shipping_address1' => implode("\n", $shipping->getStreet()),
				'shipping_city' => $shipping->getCity(),
				'shipping_state' => $shipping->getRegion(),
				'shipping_zip' => $shipping->getPostcode(),
				'shipping_phone' => $shipping->getTelephone());
				
				// Get order stuff
				$order = $invoice->getOrder();
				$payment = $order->getPayment();
				$shipments = Mage::getResourceModel('sales/order_shipment_collection')
		                    ->addAttributeToSelect('*')
    			              ->setOrderFilter($order->getId())
												->setOrder('created_at', 'ASC')
           			        ->load();
				foreach($shipments as $shipment);
				
				$basic = $invoice->getBaseGrandTotal() - $invoice->getShippingAmount() -  $invoice->getBaseTaxAmount();
				
				$rates = Mage::getModel('sales/order_tax')->getCollection()->loadByOrder($order)->toArray();
				if($rates['totalRecords'])
					$rateCode = $rates['items'][0]['code'];
				else
					$rateCode = "UNKNOWN";
				
				
				$orderData = array(
					'tax_rate' => $basic ? round(($invoice->getBaseTaxAmount() / $basic) * 100, 2) : 0,
					'tax_type' => $rateCode,
					'basic' => $basic,
					'tax' => $invoice->getBaseTaxAmount(),
					'total' => $invoice->getBaseGrandTotal() - $invoice->getShippingAmount(),
					'shipping' => $invoice->getBaseShippingAmount(),
					'cod_amount' => $invoice->getBaseGrandTotal(),
					'mode' => $payment->getMethod());
					
				
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
				$allData->setData(array_merge($invoiceData, $shippingData, $orderData, $trackingData));
					
				$content .= $allData->toString($template) . "\n";
				
			}
			
            return $this->_prepareDownloadResponse('invoice_export.csv', $content);
        }
        $this->_redirect('*/*/');
    }
	
	
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/shipment');
    }
}
