<?php
class Magestore_Affiliateplusstatistic_Block_Diagrams_Traffics extends Magestore_Affiliateplusstatistic_Block_Diagrams_Graph
{
	public function __construct(){
		$this->_google_chart_params = array(
			'cht'  => 'lc',
			'chf'  => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
			'chm'  => 'B,76a4fb,1,2,0|b,f4d4b2,0,1,0',
			'chdl' => $this->__('Unique Clicks').'|'.$this->__('Total Clicks'),
			'chco' => '2424ff,db4814'
		);
		
		$this->setHtmlId('traffics');
        parent::__construct();
    }
    
    protected function _prepareData(){
    	$this->setDataHelperName('affiliateplusstatistic/traffics');
    	$this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
    	
    	$this->setDataRows(array('uniques','clicks'));
    	$this->_axisMaps = array(
    		'x'	=> 'range',
    		'y'	=> 'clicks'
    	);
    	
    	parent::_prepareData();
    }
}