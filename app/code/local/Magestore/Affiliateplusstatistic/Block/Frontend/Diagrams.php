<?php
class Magestore_Affiliateplusstatistic_Block_Frontend_Diagrams extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
        parent::__construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('diagram_tab_content');
        $this->setTemplate('affiliateplusstatistic/widget/tabshoriz.phtml');
    }
    
    protected function _prepareLayout(){
    	$this->addTab('sales',array(
    		'label'		=> $this->__('Sales Amount'),
    		'content'	=> $this->getLayout()->createBlock('affiliateplusstatistic/frontend_diagrams_sales')->toHtml(),
    		'active'	=> true,
    	));
    	
    	$this->addTab('transactions',array(
    		'label'		=> $this->__('Transactions'),
    		'content'	=> $this->getLayout()->createBlock('affiliateplusstatistic/frontend_diagrams_transactions')->toHtml(),
    	));
    	
    	$this->addTab('commissions',array(
    		'label'		=> $this->__('Commissions'),
    		'content'	=> $this->getLayout()->createBlock('affiliateplusstatistic/frontend_diagrams_commissions')->toHtml(),
    	));
    	
    	$this->addTab('clicks',array(
    		'label'		=> $this->__('Clicks'),
    		'content'	=> $this->getLayout()->createBlock('affiliateplusstatistic/frontend_diagrams_clicks')->toHtml(),
    	));
        
        $this->addTab('impressions',array(
    		'label'		=> $this->__('Impressions'),
    		'content'	=> $this->getLayout()->createBlock('affiliateplusstatistic/frontend_diagrams_impressions')->toHtml(),
    	));
        
        $this->setChild('totals',$this->getLayout()->createBlock('affiliateplusstatistic/frontend_diagrams_totals'));
        $this->setChild('filters',$this->getLayout()->createBlock('affiliateplusstatistic/frontend_filters'));
        
    	return parent::_prepareLayout();
    }
}