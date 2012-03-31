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
 * Sales Order Shipment PDF model
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

class Katana_Sales_Model_Order_Pdf_Shipment extends Mage_Sales_Model_Order_Pdf_Shipment
{
	// The arguments are R, G, B for color.
	protected $color_black;
	protected $color_white;
	protected $barcodeFont;



    public function getPdf($shipments = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
		
		$this->color_black = new BCGColor(0, 0, 0);
		$this->color_white = new BCGColor(255, 255, 255);
		
		// Loading Font
		$this->barcodeFont = new BCGFontFile(Mage::getBaseDir() . '/lib/barcodegen/class/font/Arial.ttf', 18);
		
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
            }
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;

            $order = $shipment->getOrder();
			

            /* Add head */
            //$this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID, $order->getStoreId()));

			// CREATE LAYOUT
			
		
			$page->setLineColor(new Zend_Pdf_Color_GrayScale(0));
			$page->setLineWidth(0.5);
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
			$page->drawRectangle(20, 20, 578, 820);
			$page->drawLine(20, 55, 578, 55);
			$page->drawLine(20, 230, 578, 230);
			$page->drawLine(20, 475, 578, 475);
			$page->drawLine(20, 528, 578, 528);
			$page->drawLine(20, 628, 578, 628);
			$page->drawLine(20, 731, 578, 731);
			
			$page->drawLine(299, 761, 299, 790);
			
			$page->drawLine(20, 203, 308, 203);
			$page->drawLine(308, 55, 308, 230);

			$this->insertLogo($page, $shipment->getStore());
			
			// FIXED TEXTS
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$this->_setFontBold($page, 15);
            $page->drawText(Mage::helper('sales')->__('SHIP TO DELIVERY NAME AND ADDRESS: '), 30, 498, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('PLEASE DON\'T ACCEPT THE SHIPMENT IF IT HAS BEEN TAMPERED WITH'), 30, 35, 'UTF-8');

			$page->drawText(Mage::helper('sales')->__('FROM:'), 30, 711, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('TIN:'), 380, 711, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('PH:'), 380, 691, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('EMAIL:'), 380, 671, 'UTF-8');


			$this->_setFontBold($page, 16);
			$page->drawText(Mage::helper('sales')->__('www.Rooja.com'), 240, 737, 'UTF-8');

			$this->_setFontRegular($page, 30);
			$page->drawText(Mage::helper('sales')->__('Feel Special'), 320, 770, 'UTF-8');

			
			
			$this->insertAddress($page, $shipment->getStore());

			
			//INSERT ORDER NUMBERS
			$this->_setFontRegular($page, 20);
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText(Mage::helper('sales')->__('Order # '). $order->getRealOrderId(), 390, 206, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Shipping # '). $shipment->getIncrementId(), 367, 186, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('Order Date: ') . date( 'd/m/Y', strtotime( $order->getCreatedAt() ) ), 350, 166, 'UTF-8');

			 // INSERT SHIPPING ADDRESS
			
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			 $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
			 $this->y = 440;
 			$addressFont = 25;
			$font = $this->_setFontRegular($page, 25);
			
            foreach ($shippingAddress as $value){
                if ($value!=='') {
					$addressText = strip_tags(ltrim($value));
					if($addressFont == 25) $addressText = strtoupper($addressText);
					$feed = $this->getAlignCenter($addressText, 20, 558, $font, $addressFont);
                    $page->drawText($addressText, $feed, $this->y, 'UTF-8');
                    $this->y -= $addressFont;
					$addressFont = 20;
					$font = $this->_setFontRegular($page, $addressFont);

                }

            }
			
			$this->insertTrack($page, $shipment);
			
			// DO PAYMENT
			$payment = $order->getPayment();
			if($payment->getMethod() == 'cashondelivery') {
				$this->_setFontRegular($page, 35);
				$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
				$page->drawText(Mage::helper('sales')->__('COD SHIPMENT'), 40, 580, 'UTF-8');
			
				$page->drawText($order->formatPriceTxt($order->getGrandTotal()), 350, 560, 'UTF-8');
			
			
				$this->_setFontRegular($page, 25);
				$page->drawText(Mage::helper('sales')->__('COLLECT CASH ONLY'), 40, 550, 'UTF-8');
				$page->drawText(Mage::helper('sales')->__('TOTAL COLLECTABLE'), 310, 600, 'UTF-8');


				$page->drawLine(299, 528, 299, 628);
			
			} else {
				$this->_setFontRegular($page, 30);
				$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
				$page->drawText(Mage::helper('sales')->__('PREPAID SHIPMENT'), 160, 570, 'UTF-8');
			
			
			}
			
            $this->_setFontRegular($page);

            $this->y = 530; // Position for Items


            /* Add body */
            /*foreach ($shipment->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($this->y<15) {
                    $page = $this->newPage(array('table_header' => true));
                }

                $page = $this->_drawItem($item, $page, $order);
            } */
        }

        $this->_afterGetPdf();

        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
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

        return $page;
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
	
	// OVERRIDE function from core Abstract
	protected function insertAddress(&$page, $store = null)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 15);

        $page->setLineWidth(0);
        $this->y = 712;
        foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
            if ($value!=='') {
                $page->drawText(trim(strip_tags($value)), 80, $this->y, 'UTF-8');
                $this->y -=15;
            }
        }
		
		// insert email
		$page->drawText(Mage::getStoreConfig('sales/identity/tin', $store), 430, 711, 'UTF-8');
		$page->drawText(Mage::getStoreConfig('general/store_information/phone', $store), 430, 691, 'UTF-8');
		$page->drawText(Mage::getStoreConfig('trans_email/ident_support/email', $store), 430, 671, 'UTF-8');
		
		
		
    }	

	protected function insertLogo(&$page, $store = null)
    {
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $page->drawImage($image, 135, 740, 285, 810);
            }
        }
        //return $page;
    }	

	protected function insertTrack (&$page, $shipment) {
	
		$tracks = array();
		if ($shipment) {
			$tracks = $shipment->getAllTracks();
		}
        
		if (count($tracks)) {
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$font = $this->_setFontBold($page, 15);
			foreach ($tracks as $track) {
				$CarrierCode = $track->getCarrierCode();
				if ($CarrierCode!='custom') {
					$carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($CarrierCode);
					$carrierTitle = $carrier->getConfigData('title');
				} else {
					$carrierTitle = Mage::helper('sales')->__('Custom Value');
				}

				
                $truncatedTitle = strtoupper(substr($track->getTitle(), 0, 45) . (strlen($track->getTitle()) > 45 ? '...' : ''));
				
				$feed = $this->getAlignCenter($truncatedTitle, 20, 290, $font, 15);
                $page->drawText($truncatedTitle, $feed, 210, 'UTF-8');

				// Convert to barcode
				$drawException = null;
				try {
					$code = new BCGcode128();
					$code->setScale(3); // Resolution
					$code->setThickness(40); // Thickness
					$code->setForegroundColor($this->color_black); // Color of bars
					$code->setBackgroundColor($this->color_white); // Color of spaces
					$code->setFont($this->barcodeFont); // Font (or 0)
					$code->parse($track->getNumber()); // Text
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
				$page->drawImage($image, 60, 75, 260, 175);
				unlink($pngFile);
			}
		}
		
		// Now do the order Shipping Number
		// Convert to barcode
				$drawException = null;
				try {
					$code = new BCGcode128();
					$code->setScale(3); // Resolution
					$code->setThickness(40); // Thickness
					$code->setForegroundColor($this->color_black); // Color of bars
					$code->setBackgroundColor($this->color_white); // Color of spaces
					$code->setFont($this->barcodeFont); // Font (or 0)
					$code->parse($shipment->getIncrementId()); // Text
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
				$page->drawImage($image, 350, 65, 550, 155);
				unlink($pngFile);
		
		
	}



	
	protected function _setFontRegular($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine-Regular.ttf');
        $object->setFont($font, $size);
        return $font;
    }


	
	
}
