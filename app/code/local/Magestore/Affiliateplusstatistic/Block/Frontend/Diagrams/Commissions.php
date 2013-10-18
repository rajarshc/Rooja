<?php
class Magestore_Affiliateplusstatistic_Block_Frontend_Diagrams_Commissions extends Magestore_Affiliateplusstatistic_Block_Diagrams_Graph
{
	protected $_google_chart_params = array(
		'cht'  => 'lc',
		'chf'  => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
		'chm'  => 'B,f4d4b2,0,0,0',
		'chco' => 'db4814',
        'chxt' => 'x,y,y',
        'chxlexpend' => 'currency',
        
	);
	
	public function __construct(){
		$this->setHtmlId('commissions');
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
    	$this->setDataHelperName('affiliateplusstatistic/sales');
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();
    	if ($this->_getHelper()->getSharingConfig('balance') == 'store')
			$this->getDataHelper()->setParam('store', Mage::app()->getStore()->getId());
    	$this->getDataHelper()->setParam('account_id', $account->getId());
    	$this->setDataRows('commissions');
    	$this->_axisMaps = array(
    		'x'	=> 'range',
    		'y'	=> 'commissions'
    	);
    	
    	parent::_prepareData();
    }
}