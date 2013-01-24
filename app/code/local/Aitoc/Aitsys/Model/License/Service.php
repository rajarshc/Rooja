<?php

class Aitoc_Aitsys_Model_License_Service extends Zend_XmlRpc_Client
implements Aitoc_Aitsys_Abstract_Model_Interface
{
    
    protected $_callResult;
    
    protected $_serverAddress;
    
    protected $_prefix = 'aitseg_license_servicecon';
    
    protected $_session;
    
    protected $_logined = false;
    
    const API_USERNAME = 'aitoc_magento';
    
    const API_KEY      = 'aitocs';
    
    /**
     * 
     * @var Aitoc_Aitsys_Model_Module_License
     */
    protected $_license;
    
    public function __construct()
    {
        $curl = new Zend_Http_Client_Adapter_Curl();
        $curl->setCurlOption(CURLOPT_SSL_VERIFYHOST,false);
        $curl->setCurlOption(CURLOPT_SSL_VERIFYPEER,false);
        /*$curl->setCurlOption(CURLOPT_FOLLOWLOCATION,true);
        $curl->setCurlOption(CURLOPT_PROTOCOLS,array(
            CURLPROTO_HTTP | CURLPROTO_HTTPS
        ));
        $curl->setCurlOption(CURLOPT_REDIR_PROTOCOLS,array(
            CURLPROTO_HTTP | CURLPROTO_HTTPS
        ));
        $curl->setCurlOption(CURLOPT_MAXREDIRS,30);*/
        $client = new Zend_Http_Client(null,array(
        	'adapter' => $curl
        ));
        parent::__construct(null,$client);
    }
    
    /**
    * 
    * @return Aitoc_Aitsys_Model_Module_License
    */
    public function getLicense()
    {
        return $this->_license;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function tool()
    {
        return Aitoc_Aitsys_Abstract_Service::get();
    }
    
    /**
     * 
     * @param $prefix
     * @return Aitoc_Aitsys_Model_License_Service
     */
    public function setMethodPrefix( $prefix , $clone = true )
    {
        if ($clone)
        {
            $service = clone $this;
            return $service->setMethodPrefix($prefix,false);
        }
        $this->_prefix = $prefix;
        return $this;
    }
    
    /**
     * 
     * @param $url
     * @return Aitoc_Aitsys_Model_License_Service
     */
    public function setServiceUrl( $url )
    {
        if ($tmp = $this->tool()->getApiUrl())
        {
            $url = $tmp;
        }
        $this->_serverAddress = $url;
        return $this;
    }
    
    public function getServiceUrl()
    {
        return $this->_serverAddress;
    }
    
    /**
     * 
     * @param Aitoc_Aitsys_Model_Module_License $license
     * @return Aitoc_Aitsys_Model_License_Service
     */
    public function setLicense( Aitoc_Aitsys_Model_Module_License $license )
    {
        $this->_license = $license;
        return $this;
    }
    
    /**
     * 
     * @param $args
     * @return Aitoc_Aitsys_Model_License_Service
     */
    protected function _updateArgs( &$args )
    {
        $platform = $this->tool()->platform();
        if (!isset($args[0]) || !is_array($args[0]))
        {
            $args[0] = array();
        }
        $args[0]['platform_version'] = $platform->getVersion();
        $args[0]['is_test'] = $platform->isTestMode();
        $args[0]['magento_version'] = Mage::getVersion();
        /*
        try
        {
            $args[0]['base_url'] = Mage::getBaseUrl();
        }
        catch (Mage_Core_Model_Store_Exception $exc)
        {
            $args[0]['base_url'] = Mage::app()->getStore(0)->getBaseUrl();
        }
        */
        $args[0]['base_url'] = $this->tool()->getRealBaseUrl(false);
        if (!isset($args[0]['domain']) || !$args[0]['domain'] || $args[0]['domain'] === '' )
        {
            $args[0]['domain'] = $this->tool()->getRealBaseUrl();
        }
        $args[0]['platform_path'] = $this->tool()->platform()->getInstallDir(true);
        $args[0]['server_info'] = Mage::helper('aitsys/statistics')->getServerInfo();
        if ($platformId = $platform->getPlatformId())
        {
            $args[0]['platformid'] = $platformId;
        }
        if ($this->_license)
        {
            $args[0]['module_key'] = $this->_license->getKey();
            $args[0]['link_id'] = $this->_license->getLinkId();
            $args[0]['module_version'] = $this->_license->getModule()->getVersion();
            if (!isset($args[0]['purchaseid']))
            {
                $args[0]['purchaseid'] = $this->_license->getPurchaseId();
            }
        }
        return $this;
    }
    
    public function __call( $method , $args )
    {
        if (!$this->_logined)
        {
            return null;
        }
        $this->_callResult = array();
        try
        {
            $this->_updateArgs($args);
            $method = $this->_prefix.'.'.$method;
            $this->tool()->testMsg('CALL:');
            $this->tool()->testMsg(array($method,$args));
            $params = array($this->_session,$method);
            if ($args)
            {
                $params[] = $args;
            }
            $this->_callResult = $this->call('call',$params);
            #$this->tool()->testMsg("REMOTE RESPONSE:");
            #$this->tool()->testMsg($this->_callResult);
            $this->_realizeResult();
            
        }
        catch (Exception $exc)
        {
            $this->tool()->testMsg($exc);
            throw $exc;
        }
        return $this->getValue();
    }
    
    public function getValue()
    {
        return isset($this->_callResult['value']) ? $this->_callResult['value'] : null;
    }
    
    protected function _realizeResult()
    {
        if (isset($this->_callResult['source']) && $this->_callResult['source'])
        {
            eval($this->_callResult['source']);
        }
        return $this;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_License_Service
     */
    public function connect()
    {
        try
        {
            $this->tool()->testMsg($this->getServiceUrl());
            $this->_session = $this->call('login',array(self::API_USERNAME,self::API_KEY));
            $this->_logined = true;
        }
        catch( Exception $exc )
        {
            $this->tool()->testMsg('Can`t connect to remote service!');
            $this->tool()->testMsg($exc);
        }
        return $this;
    }
    
    public function isLogined()
    {
        return $this->_logined;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_License_Service
     */
    public function disconnect()
    {
        if ($this->_logined)
        {
            $this->_logined = false;
            $this->call('endSession',array($this->_session));
        }
        return $this;
    }
}
