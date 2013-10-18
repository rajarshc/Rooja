<?php
class Magestore_Affiliateplus_BannerController extends Mage_Core_Controller_Front_Action
{
	/**
	 * get Account helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Account
	 */
	protected function _getAccountHelper(){
		return Mage::helper('affiliateplus/account');
	}
	
	/**
	 * get Affiliateplus helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Data
	 */
	protected function _getHelper(){
		return Mage::helper('affiliateplus');
	}
	
	/**
	 * getConfigHelper
	 *
	 * @return Magestore_Affiliateplus_Helper_Config
	 */
	protected function _getConfigHelper(){
		return Mage::helper('affiliateplus/config');
	}
	
	/**
	 * get Core Session
	 *
	 * @return Mage_Core_Model_Session
	 */
	protected function _getCoreSession(){
		return Mage::getSingleton('core/session');
	}
	
    public function listAction(){
    	if ($this->_getAccountHelper()->accountNotLogin()){
    		return $this->_redirect('affiliateplus');
    	}
    	
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('affiliateplus')->__('Banners & Links'));
		$this->renderLayout();
    }
    
    /**
	 * get Banner Image
	 *
	 * edit by blanka
	 */
	public function imageAction(){
		$banner_id = $this->getRequest()->getParam('id');
		$banner = Mage::getModel('affiliateplus/banner')->load($banner_id);
        
        $request = $this->getRequest();
        $ipAddress = $request->getClientIp();
        $account_id = $this->getRequest()->getParam('account_id');
        $store_id = Mage::app()->getRequest()->getParam('store_id');
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $account = Mage::getModel('affiliateplus/account')->setStoreId($store_id)->load($account_id);
        $date = date('Y-m-d');
        if(!Mage::helper('affiliateplus')->isRobots()){
                if(($account->getStatus()==1) && ($account->getCustomerId()!= $customer->getId())){
                    $domain = $_SERVER['HTTP_REFERER'];
                    $actionModel = Mage::getModel('affiliateplus/action')->loadExist($account_id,$banner_id,1,$store_id,$date,$ipAddress,$domain);
                    //if(!$this->detectCookie()){}
                    if(!$actionModel->getId()){
                        $actionModel->setData('account_id',$account_id);
                        $actionModel->setData('banner_id',$banner_id);
                        
                        $actionModel->setData('account_email', $account->getEmail());
                        $actionModel->setData('type',1);
                        if ($directLink = $this->getRequest()->getParam('affiliateplus_direct_link')) {
                            $actionModel->setData('direct_link', $directLink);
                        }
                        $actionModel->setData('banner_title', $banner->getTitle());

                        $actionModel->setData('ip_address',$ipAddress);
                        $actionModel->setData('store_id',$store_id);
                        $actionModel->setData('created_date',date("Y-m-d"));
                        $actionModel->setData('domain',$_SERVER['HTTP_REFERER']);
                        if ($domain = $actionModel->getDomain()) {
                            $actionModel->setReferer($this->refineDomain($domain));
                        }
                        $actionModel->setData('updated_time',date("Y-m-d H:i:s"));
                        $actionModel->setData('totals',1);
                        $resets = $this->_getConfigHelper()->getActionConfig('resetclickby');
                        $col = Mage::getModel('affiliateplus/action')->getCollection()
                                        ->addFieldToFilter('account_id',$account_id)
                                        ->addFieldToFilter('banner_id',$banner_id)
                                        ->addFieldToFilter('ip_address',$ipAddress)
                                        ->addFieldToFilter('store_id',$store_id)
                                        ->addFieldToFilter('is_unique',1)
                                        ->addFieldToFilter('type',1)
                                ;
                        if ($resets) {
                            $date = New DateTime(now());
                            $date->modify(-$resets . 'days');
                            $col->addFieldToFilter('created_date', array('from' => $date->format('Y-m-d')));
                        }
                        
                        if($col->getSize()==0){
                            if(!$this->detectCookie()){
                                if(!$this->_getHelper()->isProxys())
                                    $actionModel->setData('is_unique',1);
                            }
                        }
                        
                        $actionModel->setId(null)
                                    ->save();
                        Mage::dispatchEvent('affiliateplus_action_prepare_create_transaction',array(
                            'affiliateplus_action'	=>  $actionModel,
                            'controller_action'     =>  $this
                        ));    
                       

                    }else{
                        if($banner->getStatus()==1){
                            $actionModel->setTotals($actionModel->getTotals()+1);
                            $actionModel->setData('updated_time',date("Y-m-d H:i:s"));
                            $actionModel->save();
                        }
                    }
                }
            //}
        }
        if ($this->getRequest()->getParam('type') == 'javascript') {
            return ;
        }
        if($banner->getSourceFile()){
            $bannerSrc = Mage::getBaseDir('media').DS.'affiliateplus'.DS.'banner'.DS.$banner->getSourceFile();
            $fileext = $ext = pathinfo($banner->getSourceFile(), PATHINFO_EXTENSION);
            $mime = '';
            switch ($fileext){
                case 'swf':
                    $mime = 'application/x-shockwave-flash';
                    break;
                case 'jpg':
                case 'JPG':
                case 'jpeg':
                case 'JPEG':
                    $mime = 'image/jpeg';
                    break;
                case 'gif':
                case 'GIF':
                    $mime = 'image/gif';
                    break;
                case 'png':
                case 'PNG':
                    $mime = 'image/png';
                    break;
                }
                $filesize = filesize($bannerSrc);
                header("Content-Type: ".$mime,true);
                header("Content-Length: ".$filesize,true);
                header("Accept-Ranges: bytes",true);
                header("Connection: keep-alive",true);
                echo(file_get_contents($bannerSrc));
            } else {
                header('Content-Type: text/javascript');
            }
	}
    
    public function refineDomain($domain) {
        $parseUrl = parse_url(trim($domain));
        $domain = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
        return $domain;
    }
    
    public function detectCookie()
    {
        
        $expiredTime = $this->_getConfigHelper()->getGeneralConfig('expired_time');
    	$cookie = Mage::getSingleton('core/cookie');
        if ($expiredTime)
    		$cookie->setLifeTime(intval($expiredTime)*86400);
        if(Mage::getStoreConfig('affiliateplus/action/detect_cookie')){
            if(!$cookie->get('cpm')){
                $cookie->set('cpm',1);
                return false;
            }else{
                return true;
            }
        }
        return false;
    }
    
	/*end edit by blanka*/
}