<?php
class Magestore_Affiliateplusstatistic_Block_Diagrams_Commissions extends Magestore_Affiliateplusstatistic_Block_Diagrams_Graph
{
	protected $_google_chart_params = array(
		'cht'  => 'lc',
		'chf'  => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
		'chm'  => 'B,f4d4b2,0,0,0',
		'chco' => 'db4814'
	);
	
	public function __construct(){
		$this->setHtmlId('commissions');
        parent::__construct();
    }
    
    protected function _prepareData(){
    	$this->setDataHelperName('affiliateplusstatistic/sales');
    	$this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
    	
    	$this->setDataRows('commissions');
    	$this->_axisMaps = array(
    		'x'	=> 'range',
    		'y'	=> 'commissions'
    	);
    	
    	parent::_prepareData();
    }
}