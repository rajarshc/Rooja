<?php
class Magestore_Affiliateplusstatistic_Block_Frontend_Diagrams_Clicks extends Magestore_Affiliateplusstatistic_Block_Diagrams_Graph
{
    
	public function __construct(){
		$this->_google_chart_params = array(
			'cht'  => 'lc',
			'chf'  => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
			'chm'  => 'B,76a4fb,1,2,0|b,f4d4b2,0,1,0',
			'chdl' => $this->__('Unique Clicks').'|'.$this->__('Total Clicks'),
			'chco' => '2424ff,db4814',
            'chxt' => 'x,y,y',
            'chxlexpend' => '|2:|||(clicks)'
		);
		
		$this->setHtmlId('traffics');
        parent::__construct();
    }
    
    /**
     * get Helper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    public function _getHelper() {
        return Mage::helper('affiliateplus/config');
    }
    
    protected function _prepareData(){
    	$this->setDataHelperName('affiliateplusstatistic/clicks');
        if ($this->_getHelper()->getSharingConfig('balance') == 'store')
			$this->getDataHelper()->setParam('store', Mage::app()->getStore()->getId());
    	
    	
    	$this->setDataRows(array('uniques','clicks'));
    	$this->_axisMaps = array(
    		'x'	=> 'range',
    		'y'	=> 'clicks'
    	);
    	
    	parent::_prepareData();
    }
}