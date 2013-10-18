<?php
class Magestore_Affiliateplusstatistic_Block_Grids extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
        parent::__construct();
        $this->setId('grid_tab');
        $this->setDestElementId('grid_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');
    }
    
    protected function _prepareLayout(){
    	$this->addTab('accounts',array(
    		'label'		=> $this->__('New Account'),
    		'content'	=> $this->getLayout()->createBlock('affiliateplusstatistic/grids_accounts')->toHtml(),
    		'active'	=> true,
    	));
    	
    	$this->addTab('bestseller',array(
    		'label'		=> $this->__('Bestseller Product'),
    		'url'		=> $this->getUrl('*/*/bestseller',array('_current' => true)),
    		'class'		=> 'ajax',
    	));
    	
    	$this->addTab('affiliates',array(
    		'label'		=> $this->__('Top Affiliates'),
    		'url'		=> $this->getUrl('*/*/affiliates',array('_current' => true)),
    		'class'		=> 'ajax',
    	));
    	
    	$this->addTab('referers',array(
    		'label'		=> $this->__('Top Traffic Sources'),
    		'url'		=> $this->getUrl('*/*/referers',array('_current' => true)),
    		'class'		=> 'ajax',
    	));
    	
    	return parent::_prepareLayout();
    }
}