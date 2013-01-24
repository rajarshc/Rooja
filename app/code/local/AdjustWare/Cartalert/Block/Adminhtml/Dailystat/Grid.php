<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 01/11/12
 * Package:     AdjustWare_Cartalert_3.1.1_0.2.3_440060
 * Purchase ID: NZmnTZChS7OANNEKozm6XF7MkbUHNw6IY9fsWFBWRT
 * Generated:   2013-01-22 11:08:03
 * File path:   app/code/local/AdjustWare/Cartalert/Block/Adminhtml/Dailystat/Grid.php
 * Copyright:   (c) 2013 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ NZkkqcrdfacgfNRw('4302323565b691ec93423f7640ec3985'); ?><?php
class AdjustWare_Cartalert_Block_Adminhtml_Dailystat_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('dailystatGrid');
        // This is the primary key of the database
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);        
    }
 
    private function _processPeriod($collection, $period)
    {
        switch($period)
        {
            case 'day':
                return $collection;
            break;
            
            case 'month':
                $index = 1;
            break;
            
            case 'year':
                $index = 0;
            break;            
        }
        
        
        $fields = array(
        'abandoned_carts_num',
        'abandoned_carts_price',
        'abandoned_items_num',
        'recovered_carts_num',
        'ordered_carts_num',
        'ordered_carts_price',
        'ordered_items_num',
        'av_back_time',
        'target_letter_step',
        );
        
        
        
        $date = $collection->getFirstItem()->getDate();
        $dateArray = explode('-',$date);
        $val = $dateArray[$index];
        $returnCollection = new Varien_Data_Collection;
        $object = new Varien_Object;
        //$counterABT=0;
        $counterTLS=0;
        foreach($collection as $item)
        {
            $dateArray = explode('-',$item->getDate());
            if($dateArray[$index]!=$val)
            {
                $val=$dateArray[$index];
                /*if($counterABT)
                {
                    $object->setData('av_back_time',$object->getData('av_back_time')/$counterABT);
                }*/
                if($counterTLS)
                {
                    $object->setData('target_letter_step',$object->getData('target_letter_step')/$counterTLS);
                }      
                $returnCollection->addItem($object);
                $object = new Varien_Object;
                $counterABT=0;
                $counterTLS=0;                
            }
            $object->setDate($item->getDate());
            //if($item->getData('av_back_time'))$counterABT++;
            if($item->getData('target_letter_step'))$counterTLS++;
            foreach($fields as $field)
            {
                $object->setData($field, $object->getData($field)+$item->getData($field));
            }
        }
        
        /*if($counterABT)
        {
            $object->setData('av_back_time',$object->getData('av_back_time')/$counterABT);
        }*/
        if($counterTLS)
        {
            $object->setData('target_letter_step',$object->getData('target_letter_step')/$counterTLS);
        }      
        $returnCollection->addItem($object);
        return $returnCollection;
    }


    protected function _prepareCollection()
    {
        $periodType = $this->getRequest()->getParam('period_type');
        $from = $this->getRequest()->getParam('from');
        $to = $this->getRequest()->getParam('to');
        
        if($periodType && $from && $to)
        {

            $collection = Mage::getModel('adjcartalert/dailystat')->getCollection();
            $collection->getSelect()->where('`date` BETWEEN \''.$from.'\' AND \''.$to.'\'');
            $collection = $this->_processPeriod($collection, $periodType);
            foreach($collection as $item)
            {
                if($item->getAbandonedCartsNum())
                {
                    $item->setOrderedCartsNumPercent(round($item->getOrderedCartsNum()/$item->getAbandonedCartsNum()*100));
                    $item->setRecoveredCartsNumPercent(round($item->getRecoveredCartsNum()/$item->getAbandonedCartsNum()*100));
                    $item->setOrderedCartsPricePercent(round($item->getOrderedCartsPrice()/$item->getAbandonedCartsPrice()*100));            
                }
            }
            $this->setCollection($collection);

        }
        else
        {
            $this->setCollection(new Varien_Data_Collection());
        }
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
       
        $this->addColumn('date', array(
            'header'    => Mage::helper('adjcartalert')->__('Date'),
            'align'     => 'left',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'date',
            'period_type'   => $this->getRequest()->getParam('period_type'),
            'renderer'      => 'adminhtml/report_sales_grid_column_renderer_date',  
        ));          

      
        
        $this->addColumn('abandoned_carts_num', array(
            'header'    => Mage::helper('adjcartalert')->__('Abandoned Carts, qty'),
            'align'     =>'right',
            'index'     => 'abandoned_carts_num',
        ));        

         $this->addColumn('abandoned_carts_price', array(
            'header'    => Mage::helper('adjcartalert')->__('Amount, $'),
            'align'     =>'right',
            'type'      => 'currency',             
            'index'     => 'abandoned_carts_price',
            'currency'  => 'currency',             
        ));        
        $this->addColumn('abandoned_items_num', array(
            'header'    => Mage::helper('adjcartalert')->__('Products, qty'),
            'align'     =>'right',
            'index'     => 'abandoned_items_num',
        ));        
        

        $this->addColumn('recovered_carts_num', array(
            'header'    => Mage::helper('adjcartalert')->__('Recovered, qty'),
            'align'     =>'right',
            'index'     => 'recovered_carts_num',
        ));         
        
        $this->addColumn('recovered_carts_num_percent', array(
            'header'    => Mage::helper('adjcartalert')->__('Recovered, %'),
            'align'     =>'right',
            'index'     => 'recovered_carts_num_percent',
        ));         

        $this->addColumn('ordered_carts_num', array(
            'header'    => Mage::helper('adjcartalert')->__('Ordered, qty'),
            'align'     =>'right',
            'index'     => 'ordered_carts_num',
        ));

        $this->addColumn('ordered_carts_num_percent', array(
            'header'    => Mage::helper('adjcartalert')->__('Ordered, %'),
            'align'     =>'right',
            'index'     => 'ordered_carts_num_percent',
        ));
        
        $this->addColumn('ordered_carts_price', array(
            'header'    => Mage::helper('adjcartalert')->__('Ordered, $'),
            'type'      => 'currency',    
            'align'     => 'right',
            'index'     => 'ordered_carts_price',
            'currency'  => 'currency',             
        ));        
        
        $this->addColumn('ordered_carts_price_percent', array(
            'header'    => Mage::helper('adjcartalert')->__('Ordered, $%'),
            'align'     =>'right',
            'index'     => 'ordered_carts_price_percent',
        ));          
        
        $this->addColumn('ordered_items_num', array(
            'header'    => Mage::helper('adjcartalert')->__('Ordered Products, qty'),
            'align'     =>'right',
            'index'     => 'ordered_items_num',
        ));
        
        /*$period = $this->getRequest()->getParam('period_type');
        if($period != 'month' || $period!= 'year')
        {
            $this->addColumn('av_back_time', array(
                'header'    => Mage::helper('adjcartalert')->__('Av. Back time'),
                'align'     => 'left',
                'type'      => 'time',
                'default'   => '--',
                'index'     => 'av_back_time',
            ));
        }*/
        $this->addColumn('target_letter_step', array(
            'header'    => Mage::helper('adjcartalert')->__('Target letter step'),
            'align'     =>'right',
            'index'     => 'target_letter_step',
        ));
        
        
        $this->addColumn('coupons_used', array(
            'header'    => Mage::helper('adjcartalert')->__('Generated coupons used'),
            'align'     =>'right',
            'index'     => 'coupons_used',
        ));           
        
        foreach ($this->_columns as $_column) {
            $_column->setSortable(false);
        }
        
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/adminhtml_quotestat/index', array('date' => $row->getDate(),'period_type' => $this->getRequest()->getParam('period_type')));
    }
 
    public function getGridUrl()
    {
      return $this->getUrl('*/*/grid', array('_current'=>true));
    }
} } 