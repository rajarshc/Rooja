<?php
/**
 * Author : Barny Shergold
 * Date   : 29/10/2010
 * Description : Child products show parent name, quantity and unit type

 */
 
class Katana_Sales_Model_Order_Pdf_Items_Shipment_Default extends Mage_Sales_Model_Order_Pdf_Items_Shipment_Default
{
    /**
     * Draw item line
     *
     */
    public function draw()
    {
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();

        // draw Product name
				// CUSTOMISED
				$_child = Mage::getModel('catalog/product')->load($item->getProductId())->loadParentProductIds();
				if($_child->getParentProductIds() && $_child->getQuantity()) {
					$_parentIds = $_child->getParentProductIds();
					
					$printName = Mage::getModel('catalog/product')->load($_parentIds[0])->getName() . " (" . $_child->getQuantity() . " " . $_child->getAttributeText('quantity_type')   .")";  
				}	else {
					$data = $item->getData();
					$printName = $data["name"];
				}


        $lines[0] = array(array(
            'text' => Mage::helper('core/string')->str_split($printName, 100, true, true),
            'feed' => 115,
						'font_size' => 9,
        ));

        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty()*1,
						'font_size' => 12,
            'feed'  => 60
        );

        // draw SKU
        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 25),
						'font_size' => 9,
            'feed'  => 430
        );

        // Custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
										'font_size' => 10,
                    'font' => 'italic',
                    'feed' => 60
                );

                // draw options value
                if ($option['value']) {
                    $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[][] = array(
                            'text' => Mage::helper('core/string')->str_split($value, 50, true, true),
														'font_size' => 10,
                            'feed' => 65
                        );
                    }
                }
            }
        }

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 15
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}