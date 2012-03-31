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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
require_once 'barcodegen/class/BCGFontFile.php';
// Including all required classes
require_once 'barcodegen/class/BCGFontFile.php';
require_once 'barcodegen/class/BCGColor.php';
require_once 'barcodegen/class/BCGDrawing.php';

// Including the barcode technology
require_once 'barcodegen/class/BCGcode128.barcode.php';
 
 
 
 
class Katana_Sales_Model_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
{
	// The arguments are R, G, B for color.
	protected $color_black;
	protected $color_white;
	protected $barcodeFont;




    public function getPdf($invoices = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
		$top = 690;
		
		$this->color_black = new BCGColor(0, 0, 0);
		$this->color_white = new BCGColor(255, 255, 255);
		
		// Loading Font
		$this->barcodeFont = new BCGFontFile(Mage::getBaseDir() . '/lib/barcodegen/class/font/Arial.ttf', 18);		
        
		foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;

            $order = $invoice->getOrder();

            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
			$page->drawLine(160, 803, 160, 750);
			
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());

            /* Add head */
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$this->_setFontBold($page, 15);
			$page->drawText(Mage::helper('sales')->__('RETAIL INVOICE'), 220, $top + 20, 'UTF-8');


			$this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()));
			$yAfterOrder = $this->y;
			
			$this->insertOrderBarCode($page, $order);

			$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
			$this->_setFontRegular($page,15);
			$page->drawText(Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId(), 35, $top - 35, 'UTF-8');
			
			$payment = $order->getPayment()->getMethodInstance();
			$page->drawText(Mage::helper('sales')->__('Payment Method: ') . $payment->getTitle(), 300, $top - 20, 'UTF-8');

            

            /* Add table */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y - 20 );
            $this->y -=13;

            /* Add table head */
			$this->_setFontRegular($page,15);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText(Mage::helper('sales')->__('S/N'), 30, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('SKU'),70, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Products'), 185, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Amount'), 295, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Qty'), 365, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax Type'), 400, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax'), 471, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Total'), 529, $this->y, 'UTF-8');

            $this->y -=30;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            /* Add body */
			$rates = Mage::getModel('sales/order_tax')->getCollection()->loadByOrder($order)->toArray();
			if($rates['totalRecords'])
				$order->setTaxRatePDF(substr($rates['items'][0]['code'], 0, 4));
			else
				$order->setTaxRatePDF(false);

			$totalItems = 0;
			$breaks = 0;
			foreach ($invoice->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

				$totalItems += $item->getQty()*1;
                if ($this->y < 35) {
					// Draw box
					$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
					$page->drawRectangle(25, $yAfterOrder, 570, 35, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                    $page = $this->newPage(array('table_header' => true));
                }

                /* Draw item */
				$this->_setFontRegular($page,15);
                $page = $this->_drawItem($item, $page, $order);
            }

            /* Add totals */
			$this->y -= 10;
            $page = $this->insertTotals($page, $invoice);

            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
			
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$page->drawRectangle(25, count($this->_getPdf()->pages) > 1 ? 780 : $yAfterOrder, 570, 131, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
			$page->drawLine(25, 161, 570, 161);
			
			// Add Footer
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, 80, 570, 50);
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
			$this->_setFontRegular($page,15);
			$page->drawText(Mage::helper('sales')->__('Have any questions? Email us at: ') . Mage::getStoreConfig('trans_email/ident_support/email', $store), 150, 63, 'UTF-8');
			
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$this->_setFontBold($page,15);
			$page->drawText(Mage::helper('sales')->__('www.Rooja.com'), 25, 20, 'UTF-8');
			$page->drawText(Mage::getStoreConfig('general/store_information/phone', $store), 500, 20, 'UTF-8');

			$this->_setFontRegular($page,12);
			$page->drawText(Mage::helper('sales')->__('This is a computer generated receipt. No signature required.'), 150, 85, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('TIN : ') . Mage::getStoreConfig('sales/identity/tin', $store), 100, 110, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('PAN : ') . Mage::getStoreConfig('sales/identity/pan', $store), 250, 110, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('SERVICE TAX NO. : ') . Mage::getStoreConfig('sales/identity/servicetaxnumber', $store), 400, 110, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('TOTAL QUANTITY FOR ORDER : ') . $totalItems, 220, 140, 'UTF-8');
			
			
			
			
        }

        $this->_afterGetPdf();

        return $pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;

        if (!empty($settings['table_header'])) {
            /* Add table */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y - 20 );
            $this->y -=13;

            /* Add table head */
			$this->_setFontRegular($page,15);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText(Mage::helper('sales')->__('S/N'), 30, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('SKU'),70, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Products'), 185, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Amount'), 295, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Qty'), 365, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax Type'), 400, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax'), 471, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Total'), 529, $this->y, 'UTF-8');

            $this->y -=30;
        }

        return $page;
    }
	
	// CUSTOMISED OVVERIDES
	protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        /* @var $order Mage_Sales_Model_Order */
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
		$top = 690;
        $page->drawRectangle(25, $top, 570, $top-60);

        
		
		
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 15);


        if ($putOrderId) {
            $page->drawText(Mage::helper('sales')->__('Order # ').$order->getRealOrderId(), 35, $top-20, 'UTF-8');
        }
        $page->drawText(Mage::helper('sales')->__('Order Date: ') . date( 'd/m/Y', strtotime( $order->getCreatedAt() )) , 35, $top-50, 'UTF-8');
		
		
		 
		

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top - 60, 297, $top - 90);
        $page->drawRectangle(297, $top - 60, 570, $top - 90);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));

        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true)
            ->toPdf();
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key=>$value){
            if (strip_tags(trim($value))==''){
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));

            $shippingMethod  = $order->getShippingDescription();
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 15);
        $page->drawText(Mage::helper('sales')->__('BILL TO:'), 320, $top - 80 , 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(Mage::helper('sales')->__('SHIP TO:'), 35, $top - 80 , 'UTF-8');
        }
        else {
            $page->drawText(Mage::helper('sales')->__('Payment Method:'), 320, $top - 80 , 'UTF-8');
        }

        $y = $top - 250;
        $this->_setFontRegular($page, 15);
        $this->y = $top - 105;

        foreach ($billingAddress as $value){
            if ($value!=='') {
                $page->drawText(strip_tags(ltrim($value)), 320, $this->y, 'UTF-8');
                $this->y -=15;
            }
        }
		
		$this->y = $top - 105;
        foreach ($shippingAddress as $value){
			if ($value!=='') {
				$page->drawText(strip_tags(ltrim($value)), 35, $this->y, 'UTF-8');
                $this->y -=15;
             }
        }
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$page->drawRectangle(25, $top - 90, 570, $this->y, Zend_Pdf_Page::SHAPE_DRAW_STROKE);

    }
	
	// OVERRIDE function from core Abstract
	protected function insertAddress(&$page, $store = null)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 12);

        $page->setLineWidth(0);
        $this->y = 795;
        foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
            if ($value!=='') {
                $page->drawText(trim(strip_tags($value)), 170, $this->y, 'UTF-8');
                $this->y -=15;
            }
        }
    }	

	protected function insertLogo(&$page, $store = null)
    {
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $page->drawImage($image, 30, 750, 150, 806);
            }
        }
        //return $page;
    }		

	
	protected function _formatAddress($address)
    {
        $return = array();
        foreach (explode('|', $address) as $str) {
            foreach (Mage::helper('core/string')->str_split($str, 40, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }
	
	protected function insertOrderBarCode (&$page, $order) {
	
		// Convert to barcode
		$drawException = null;
		try {
			$code = new BCGcode128();
			$code->setScale(2); // Resolution
			$code->setThickness(40); // Thickness
			$code->setForegroundColor($this->color_black); // Color of bars
			$code->setBackgroundColor($this->color_white); // Color of spaces
			$code->setFont($this->barcodeFont); // Font (or 0)
			$code->parse($order->getRealOrderId()); // Text
		} catch(Exception $exception) {
			$drawException = $exception;
		}

		/* Here is the list of the arguments
		1 - Filename (empty : display on screen)
		2 - Background color */
		$barcodeFile = tempnam(Mage::getConfig()->getOptions()->getTmpDir(), 'BarCode_Ship');
		$drawing = new BCGDrawing($barcodeFile, $this->color_white);
		if($drawException) {
			$drawing->drawException($drawException);
		} else {
			$drawing->setBarcode($code);
			$drawing->draw();
		}

		// Draw (or save) the image into PNG format.
		$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
		// RENAME FILE
		$pngFile = str_replace(".tmp", ".png", $barcodeFile);
		rename($barcodeFile, $pngFile);
		
		$image = Zend_Pdf_Image::imageWithPath($pngFile);
		$page->drawImage($image, 418, 752, 568, 820);
		
		unlink($pngFile);
	}


    protected function insertTotals($page, $source){
        $order = $source->getOrder();
        $totals = $this->_getTotalsList($source);
        $lineBlock = array(
            'lines'  => array(),
            'height' => 20
        );
        foreach ($totals as $total) {
            $total->setOrder($order)
                ->setSource($source);

            if ($total->canDisplay()) {
				$count = count($total->getTotalsForDisplay());
                foreach ($total->getTotalsForDisplay() as $totalData) {
					$count--;
                    $lineBlock['lines'][] = array(
                        array(
                            'text'      => $totalData['label'],
                            'feed'      => 460,
                            'align'     => 'right',
                            'font_size' => 12,
                            'font'      => 'bold',
                        ),
                        array(
                            'text'      => $totalData['amount'],
                            'feed'      => 565,
                            'align'     => 'right',
                            'font_size' => 12,
                            'font'      => 'bold',
                        ),
                    );
                }
            }
        }

        $page = $this->drawLineBlocks($page, array($lineBlock));
        return $page;
    }
	
public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                Mage::throwException(Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array.'));
            }
            $lines  = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = array($column['text']);
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->newPage($pageSettings);
            }

			$count = count($lines);
            foreach ($lines as $line) {
                $maxHeight = 0;
				$count--;
				if(!$count && count($lines) > 1)
					$this->y -= $lineSpacing;
					
				
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? 7 : $column['font_size'];
					if(!$count && count($lines) > 1) $fontSize += 5;
                    if (!empty($column['font_file'])) {
                        $font = Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font, $fontSize);
                    }
                    else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        switch ($fontStyle) {
                            case 'bold':
                                $font = $this->_setFontBold($page, $fontSize);
                                break;
                            case 'italic':
                                $font = $this->_setFontItalic($page, $fontSize);
                                break;
                            default:
                                $font = $this->_setFontRegular($page, $fontSize);
                                break;
                        }
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = array($column['text']);
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                }
                                else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                        }
                        $page->drawText($part, $feed, $this->y-$top, 'UTF-8');
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }

        return $page;
    }	
	
	protected function _setFontRegular($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine-Regular.ttf');
        $object->setFont($font, $size);
        return $font;
    }

}
