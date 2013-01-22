<?php
class Aitoc_Aitsys_Model_Module_License_Light_Upgrade  extends Aitoc_Aitsys_Model_Module_License_Upgrade
{
    /**
     * @return Aitoc_Aitsys_Model_Module_License_Upgrade
     */
    public function reset()
    {
        $this->setData(array());
        return $this;
    }
    
    /**
     * @return Aitoc_Aitsys_Model_Module
     */
    public function getModule()
    {
        return $this->_license->getModule();
    }
    
    public function canUpgrade()
    {
        if (!$this->hasUpgrade())
        {
            return false;
        }

        $canUpgrade = true;
        if($canUpgrade)
        {
            return $this->_license->isInstalled();
        }
        return false;
    }
    
    /**
     * @return boolean
     */
    public function hasUpgrade()
    {
        $upgrade = false;
        if($this->_license->getPerformer())
        {
            $upgrade = $this->_license->getPerformer()->isUpgradeAvailable();
        }
        return $upgrade;
    }
    
    /**
     * @return Aitoc_Aitsys_Model_Module_License_Upgrade
     */
    public function upgrade()
    {
        if ($this->canUpgrade())
        {
            $upgrade = array(
            	'upgrade_purchaseid' => $this->_license->getPurchaseId() ,
                'checkid' => $this->_license->getCheckid()
            );
            $service = $this->_license->getService();
            $service->connect();
            $service->upgradeLicense($upgrade);
            $service->disconnect();

            $this->getModule()->reset();
        }
        else
        {
            throw new Aitoc_Aitsys_Model_License_Service_Exception("Can`t upgrade to this license!");
        }
        return $this;
    }
    
    public function getConstraint()
    {
        $rules = array();
        if($this->_license->getPerformer())
        {
            $rules = $this->_license->getPerformer()->getOpenedRuler()->getRules(); 
        }
        return $rules;
    }
    
    public function getPurchaseId() {
        return false;
    }
}