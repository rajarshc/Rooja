<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 01/11/12
 * Package:     AdjustWare_Cartalert_3.1.1_0.2.3_440060
 * Purchase ID: NZmnTZChS7OANNEKozm6XF7MkbUHNw6IY9fsWFBWRT
 * Generated:   2013-01-22 11:08:03
 * File path:   app/code/local/AdjustWare/Cartalert/Block/Adminhtml/Quotestat/View/QuoteItems.php
 * Copyright:   (c) 2013 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ QDrrpCaVNZCMNQTh('07317960f5f602ac2281604d2817f855'); ?><?php 
class AdjustWare_Cartalert_Block_Adminhtml_Quotestat_View_QuoteItems extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setUseAjax(false);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);           
        
    }

    /**
     * Prepare grid
     *
     * @return void
     */
    protected function _prepareGrid()
    {
        $this->setId('customer_cart_grid' . $this->getWebsiteId());
        parent::_prepareGrid();
    }

    protected function _prepareCollection()
    {
        $data = Mage::registry('quotestat_data');
        $quote = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('entity_id', array('eq'=>$data->getQuoteId()))->getFirstItem();
            
        $collection = Mage::getModel('sales/quote_item')->getCollection()->setQuote($quote);

        $collection->addFieldToFilter('parent_item_id', array('null' => true));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('catalog')->__('Product ID'),
            'index'     => 'product_id',
            'width'     => '100px',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Product Name'),
            'index'     => 'name',
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'index'     => 'sku',
            'width'     => '100px',
        ));

        $this->addColumn('qty', array(
            'header'    => Mage::helper('catalog')->__('Qty'),
            'index'     => 'qty',
            'type'      => 'number',
            'width'     => '60px',
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('catalog')->__('Price'),
            'index'         => 'price',
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
        ));

        $this->addColumn('total', array(
            'header'        => Mage::helper('sales')->__('Total'),
            'index'         => 'row_total',
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
        ));

        foreach ($this->_columns as $_column) {
            $_column->setSortable(false);
        }        
        
        return parent::_prepareColumns();
    }
    
    
    public function getRowUrl($row)
    {
        return "javascript:void(0);";
    }    

} } 