<?php

require_once('lib/Facebook/facebook.php');
class TBT_Rewardssocial_Model_Facebook_Api_Facebook extends Facebook {
    public function __construct($config=array()) {
        
        $config = $this->_prepareConfig($config);
        
        return parent::__construct($config);
    }
    
    protected function _prepareConfig($config) {
        if(!is_array($config)) {
            $config = array();
        }
        
        if(!isset($config['appId'])) {
            $config['appId'] = Mage::helper('rewardsfb/config')->getAppId();
        }
        if(!isset($config['secret'])) {
            $config['secret'] = Mage::helper('rewardsfb/config')->getAppSecretId();
        }
        if(!isset($config['cookie'])) {
            $config['cookie'] = true;//Mage::helper('rewardsfb/config')->getAppSecretId();
        }
        
        if(!isset($config['req_perms'])) {
		    $config['req_perms'] = $this->_getStdPermList();
        }
        
        return $config;
    }

	protected function _getStdPermList() {
		$perms = array();
		$perms[] = 'email';
		$perms[] = 'read_stream';
		
		return implode(',', $perms);
	}

	public function getLoginUrl($params=array()) {
        if(!is_array($params)) {
            $params = array();
        }
        
        if(!isset($params['req_perms'])) {
		    $params['req_perms'] = $this->_getStdPermList();
        }
        
        return parent::getLoginUrl($params);
	}
}