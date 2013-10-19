<?php
class Magestore_Affiliateplusstatistic_Block_Diagrams_Transactions extends Magestore_Affiliateplusstatistic_Block_Diagrams_Graph
{
	protected $_google_chart_params = array(
		'cht'  => 'lc',
		'chf'  => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
		'chm'  => 'B,f4d4b2,0,0,0',
		'chco' => 'db4814'
	);
	
	public function __construct(){
		$this->setHtmlId('transactions');
        parent::__construct();
    }
    
    protected function _prepareData(){
    	$this->setDataHelperName('affiliateplusstatistic/sales');
    	$this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
    	
    	$this->setDataRows('transactions');
    	$this->_axisMaps = array(
    		'x'	=> 'range',
    		'y'	=> 'transactions'
    	);
    	
    	parent::_prepareData();
    }
}