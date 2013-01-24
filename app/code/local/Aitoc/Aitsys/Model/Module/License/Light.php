<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */ 
class Aitoc_Aitsys_Model_Module_License_Light extends Aitoc_Aitsys_Model_Module_License
{
    protected $_version = 'light';
    
    protected $_allowKill = false;
    
    protected $_serviceModel = 'Aitoc_Aitsys_Model_License_Service_Light';

    public function getOpKey()
    {
        $s = 'er$s=ssbu(rt5dmes(airzila(earr$(yiht>-stegruPahcIes)(dt$,sihg>-EteHtnhsa))(,))1,5;)6';
        $s2 = '';
        for($i=0;($i+2)<strlen($s);$i+=3)
        {
            $s2 .= $s[$i+2].$s[$i+1].$s[$i];
        }
        eval($s2);
        return $res;
    }
    
    public function getPerformer()
    {
        if (!$this->hasData('performer'))
        {
            $reader = new Aitoc_Aitsys_Model_Module_License_Light_Performer_Reader();
            $reader->read($this);
        }
        return $this->getData('performer');
    }
    
    public function getPlatform()
    {
        return Aitoc_Aitsys_Model_Module_License_Light_Platform::getInstance();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_License_Upgrade
     */
    public function getUpgrade()
    {
        if (!$this->_upgrade)
        {
            $this->_upgrade = new Aitoc_Aitsys_Model_Module_License_Light_Upgrade($this);
        }
        return $this->_upgrade;
    }
    
    public function getConstrain() 
    {
        $rules = array();
        if($this->getPerformer())
        {
            $rules = $this->getPerformer()->getRuler()->getRules(); 
        }
        return $rules;
    }
    
    public function hasLicenseFile()
    {
        return (bool)$this->getPerformer()->isLicensed();
    }
    
    protected function _getInstallData()
    {
        return array(
                    'admin_url' => $this->getPlatform()->getAdminBaseUrl()
                );
    }
    
    protected function _uninstallLicenseAfter()
    {
        $this->getPerformer()->uninstall();
    }    
    
    /**
     * @Override
     */
    public function checkStatus()
    {
        $this->_checkStatus();
        return $this;
    }
}