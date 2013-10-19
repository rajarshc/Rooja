<?php
class Magestore_Affiliateplusstatistic_IndexController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('affiliateplus/statistic/chart')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Affiliate Plus'), Mage::helper('adminhtml')->__('Statistic'));
		
		return $this;
	}
 
	public function indexAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_title($this->__('Affiliateplus'))->_title($this->__('Statistic'));
		$this->_initAction()
			->renderLayout();
	}
	
	public function tunnelAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $httpClient = new Varien_Http_Client();
        $gaData = $this->getRequest()->getParam('ga');
        $gaHash = $this->getRequest()->getParam('h');
        if ($gaData && $gaHash) {
            $newHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
            if ($newHash == $gaHash) {
                if ($params = unserialize(base64_decode(urldecode($gaData)))) {
                    $response = $httpClient->setUri(Mage_Adminhtml_Block_Dashboard_Graph::API_URL)
                            ->setParameterGet($params)
                            ->setConfig(array('timeout' => 5))
                            ->request('GET');

                    $headers = $response->getHeaders();

                    $this->getResponse()
                        ->setHeader('Content-type', $headers['Content-type'])
                        ->setBody($response->getBody());
                }
            }
        }
    }
    
    public function ajaxBlockAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
    	$output = '';
    	$blockTab = $this->getRequest()->getParam('block');
    	if (in_array($blockTab, array(
    		'diagrams_sales',
    		'diagrams_transactions',
    		'diagrams_commissions',
    		'diagrams_totals',
    		'diagrams_traffics',
            'diagrams_impressions',
    	))){
    		$output = $this->getLayout()->createBlock("affiliateplusstatistic/$blockTab")->toHtml();
    	}
    	$this->getResponse()->setBody($output);
    }
    
    public function accountsAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
    	$this->getResponse()->setBody($this->getLayout()->createBlock('affiliateplusstatistic/grids_accounts')->toHtml());
    }
    
    public function bestsellerAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
    	$this->getResponse()->setBody($this->getLayout()->createBlock('affiliateplusstatistic/grids_bestseller')->toHtml());
    }
    
    public function affiliatesAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
    	$this->getResponse()->setBody($this->getLayout()->createBlock('affiliateplusstatistic/grids_affiliates')->toHtml());
    }
    
    public function referersAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
    	$this->getResponse()->setBody($this->getLayout()->createBlock('affiliateplusstatistic/grids_referers')->toHtml());
    }
}