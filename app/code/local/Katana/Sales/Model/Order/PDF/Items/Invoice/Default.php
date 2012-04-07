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
 * Sales Order Invoice Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Katana_Sales_Model_Order_Pdf_Items_Invoice_Default extends Mage_Sales_Model_Order_Pdf_Items_Invoice_Default
{
    /**
     * Draw item line
     *
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();
		
		$fontSize = 10;

        // draw SKU
        $lines[0] = array(array(
            'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 15),
			'font_size' => 10,
            'feed'  => 45
        ));

        $lines[0][] = array(
            'text' => $item->getLineNumber(),
			'font_size' => $fontSize,
			'align' => 'left',
            'feed' => 30,
        );
		
        // custom options
        $options = $this->getItemOptions();
		
		$optionsText = array();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $text = $option['label'] . ":";

                if ($option['value']) {
                    $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
						$text .= $value;
                    }
                }
				$optionsText[] = $text;
            }
        }
				
		$productName = array_merge(Mage::helper('core/string')->str_split($item->getName(), 35, true, true), $optionsText);
		
        // draw Product name
        $lines[0][] = array(
            'text' => $productName,
			'font_size' => $fontSize,
			'align' => 'left',
            'feed' => 135,
        );
		
        // draw Price
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getPrice()),
            'feed'  => 295,
            'font'  => 'bold',
			'font_size' => $fontSize,
            'align' => 'right',
			'width' => 60
        );
		
		
        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty()*1,
			'font_size' => $fontSize,
			'align' => 'centre',
            'feed'  => 365,
			'width' => 40
        );


        // draw Tax RATE
		$taxRate = (($item->getTaxAmount() / $item->getRowTotal()) * 100 ) . "%";
		if($order->getTaxRatePDF())
			$taxRate = $order->getTaxRatePDF() . " (" . $taxRate . ")";
		
        $lines[0][] = array(
            'text'  => $taxRate,
            'feed'  => 420,
            'font'  => 'bold',
			'font_size' => $fontSize,
            'align' => 'right',
			'width' => 40
        );
		
		
		
        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => 471,
            'font'  => 'bold',
			'font_size' => $fontSize,
            'align' => 'right',
			'width' => 40
        );

        // draw Subtotal
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowTotalInclTax()),
            'feed'  => 529,
            'font'  => 'bold',
			'font_size' => $fontSize,
            'align' => 'right',
			'width' => 40
        );


        $lineBlock = array(
            'lines'  => $lines,
            'height' =>15,
        );
		
        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
	
	
}
