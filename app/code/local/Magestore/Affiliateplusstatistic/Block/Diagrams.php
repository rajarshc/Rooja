<?php
class Magestore_Affiliateplusstatistic_Block_Diagrams extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
        parent::__construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('diagram_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');
    }
    
    protected function _prepareLayout(){
    	$this->addTab('sales',array(
    		'label'		=> $this->__('Sales Amount'),
    		'content'	=> $this->getLayout()->createBlock('affiliateplusstatistic/diagrams_sales')->toHtml(),
    		'active'	=> true,
    	));
    	
    	$this->addTab('transactions',array(
    		'label'		=> $this->__('Transactions'),
    		'content'	=> $this->getLayout()->createBlock('affiliateplusstatistic/diagrams_transactions')->toHtml(),
    	));
    	
    	$this->addTab('commissions',array(
    		'label'		=> $this->__('Commissions'),
    		'content'	=> $this->getLayout()->createBlock('affiliateplusstatistic/diagrams_commissions')->toHtml(),
    	));
    	
    	$this->addTab('traffics',array(
    		'label'		=> $this->__('Clicks'),
    		'content'	=> $this->getLayout()->createBlock('affiliateplusstatistic/diagrams_traffics')->toHtml(),
    	));
        
        $this->addTab('impressions', array(
            'label'     => $this->__('Impressions'),
            'content'   => $this->getLayout()->createBlock('affiliateplusstatistic/diagrams_impressions')->toHtml(),
        ));
    	
    	return parent::_prepareLayout();
    }
}