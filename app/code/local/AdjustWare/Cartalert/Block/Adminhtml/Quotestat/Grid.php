<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 01/11/12
 * Package:     AdjustWare_Cartalert_3.1.1_0.2.3_440060
 * Purchase ID: NZmnTZChS7OANNEKozm6XF7MkbUHNw6IY9fsWFBWRT
 * Generated:   2013-01-22 11:08:03
 * File path:   app/code/local/AdjustWare/Cartalert/Block/Adminhtml/Quotestat/Grid.php
 * Copyright:   (c) 2013 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ NZkkqcrdfacgfNRw('3cb08cd5c7517f9070f1b23b64e4e666'); ?><?php
class AdjustWare_Cartalert_Block_Adminhtml_Quotestat_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('quotestatGrid');
        $this->setDefaultSort('quote_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if($this->getRequest()->getParam('date'))
        {
            $this->_processUrlDateParams($this->getRequest()->getParam('date'), $this->getRequest()->getParam('period_type'));
        }
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('adjcartalert/quotestat')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _processUrlDateParams($date, $period_type)
    {
        switch($period_type)
        {
            case 'year':
                $dateArray = explode('-',$date);
                $date_from = $dateArray[0].'-01-01';
                $date_to = $dateArray[0].'-12-31';
            break;

            case 'month':
                $dateArray = explode('-',$date);
                $date_from = $dateArray[0].'-'.$dateArray[1].'-01';
                $date_to = $dateArray[0].'-'.$dateArray[1].'-'.cal_days_in_month(CAL_GREGORIAN, $dateArray[1], $dateArray[0]);            
            break;

            case 'day':
            default:
                $date_from = $date;
                $date_to = $date;
        }
        
        $date_from = new Zend_Date($date_from, Zend_Date::ISO_8601);
        $date_to = new Zend_Date($date_to, Zend_Date::ISO_8601);
        $this->setDefaultFilter(array('cart_abandon_date' => array(
                'from'      => $date_from, 
                'to'        => $date_to,
                'orig_from' => $date_from, 
                'orig_to'   => $date_to,
                )));    
    }
    
    protected function _prepareColumns()
    {
        
        $this->addColumn('quote_id', array(
            'header'    => Mage::helper('adjcartalert')->__('Quote ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'quote_id',
        ));        
 
        $this->addColumn('cart_price', array(
            'header'    => Mage::helper('adjcartalert')->__('Cart Amount'),
            'align'     => 'left',
            'type'      => 'currency',            
            'index'     => 'cart_price',
            'currency'  => 'currency',
        ));

        $this->addColumn('cart_abandon_date', array(
            'header'    => Mage::helper('adjcartalert')->__('Abandoned Date'),
            'align'     => 'left',
            'type'      => 'datetime',
            'default'   => '--',
            'index'     => 'cart_abandon_date',
        ));        
       
        $this->addColumn('order_price', array(
            'header'    => Mage::helper('adjcartalert')->__('Order Amount'),
            'type'      => 'currency', 
            'align'     => 'left',
            'index'     => 'order_price',
            'currency'  => 'currency',
        ));
        
        $this->addColumn('order_date', array(
            'header'    => Mage::helper('adjcartalert')->__('Order Date'),
            'align'     => 'left',
            'type'      => 'datetime',
            'default'   => '--',
            'index'     => 'order_date',
        ));         
 
        $this->addColumn('status', array(
            'header'    => Mage::helper('adjcartalert')->__('Status'),
            'align'     => 'left',
            'index'     => 'status',
            'filter'    => false
        ));  
        
        $this->getColumn('status')->setSortable(false);

        return parent::_prepareColumns();
    }
 
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }
 
    public function getGridUrl()
    {
      return $this->getUrl('*/*/grid', array('_current'=>true));
    }
    
} } 