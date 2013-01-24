<?php

class Aitoc_Aitsys_Model_Module_License_Upgrade  extends Aitoc_Aitsys_Abstract_Model
{
    const UPGRADE_FILE = 'upgrade-license.xml';
       
    /**
     * @var Aitoc_Aitsys_Model_Module_License
     */
    protected $_license;
    
    protected $_hasUpgrade = false;
    
    protected $_upgradePathes = array();
    
    protected $_installPathes = array();
    
    protected $_upgradePath;
    
    protected $_installPath;
    
    protected $_failedFiles = array();
    
    public function __construct( Aitoc_Aitsys_Model_Module_License $license )
    {
        $this->_license = $license;
        parent::__construct();
        $this->reset();
    }
    
    /**
     * Removes used upgrade files as upgrade process is finished
     */
    protected function _removeUpgradeXMLs()
    {
        foreach($this->_getUpgradePaths() as $path)
        {
            $this->tool()->filesystem()->rmFile($path);
        }
        $this->_upgradePathes = array();
    }
    
    /**
     * Check weither both license xml files have writable
     * permissions as these files should be changed during upgrade.
     * Files without writable permissions are placed in _failedFiles var
     * 
     * @return bool
     */
    protected function _checkLicensesFilesPermissions()
    {
        $result = true;
        foreach ($this->_getInstallPaths() as $installPath)
        {
            if(!$this->tool()->filesystem()->isWriteable($installPath))
            {
                $this->_failedFiles[] = $installPath;
                $result = false;
            }
        }
        return $result;
    }
    
    /**
     * Generates license and upgrade xml files' pathes for module's etc
     * and installer folders and checks if these files exist. Returns the
     * list of existing xmls.
     * 
     * @param string $firstFileSuffix File name/suffix for the file located in the module's etc dir
     * @param string $secondFileSuffix File name/suffix for the file located in the Aitsys/install/ dir
     * 
     * @return array
     */
    protected function _getPaths($firstFileSuffix, $secondFileSuffix)
    {
        $listArray = array();
        // module etc dir related path
        $moduleData = $this->tool()->platform()->getModulesList($this->getModule()->getKey());
        if(!empty($moduleData) && isset($moduleData['module_path']) && $moduleData['module_path'])
        {
            $path = $moduleData['module_path'].'/etc/'.$firstFileSuffix;
            if(@file_exists($path))
            {
                $listArray[] = $path;
            }
        }
        
        // install dir related path
        $path = $this->tool()->platform()->getInstallDir().$this->getModule()->getId().'.'.$secondFileSuffix;
        if(@file_exists($path))
        {
            $listArray[] = $path;
        }
        return $listArray;
    }
    
    /**
     * Wrapper to get upgrade files paths
     * 
     * @return array
     */
    protected function _getUpgradePaths()
    {
        if(empty($this->_upgradePathes))
        {
            $this->_upgradePathes = $this->_getPaths(self::UPGRADE_FILE, self::UPGRADE_FILE);
        }
        return $this->_upgradePathes;
    }
    
    /**
     * Retrieves the first upgrade file path from an array
     * 
     * @return string
     */
    protected function _getUpgradePath()
    {
        if(is_null($this->_upgradePath))
        {
            $this->_upgradePath = '';
            foreach($this->_getUpgradePaths()as $path)
            {
                    $this->_upgradePath = $path;
                    break;
            }
        }
        return $this->_upgradePath;
    }
    
    /**
     * Wrapper to get install files paths
     * 
     * @return array
     */
    protected function _getInstallPaths()
    {
        if(empty($this->_installPathes))
        {
            $this->_installPathes = $this->_getPaths(Aitoc_Aitsys_Model_Module_License::LICENSE_FILE, 'xml');
        }
        return $this->_installPathes;
    }
    
    /**
     * Retrieves the first install file path from an array
     * 
     * @return string
     */
    protected function _getInstallPath()
    {
        if(is_null($this->_installPath))
        {
            $this->_installPath = '';
            foreach($this->_getInstallPaths()as $path)
            {
                $this->_installPath = $path;
                break;
            }
        }
        return $this->_installPath;
    }
    
    /**
     * Upgrade initialization
     * 
     * @return Aitoc_Aitsys_Model_Module_License_Upgrade
     */
    public function reset()
    {
        $this->setData(array());
        if($path = $this->_getUpgradePath())
        {
            $this->_loadFile($path);
        }
        return $this;
    }

    /**
     * Attempt to load license upgrade file if it could be found.
     * If the file successfuly loaded then set _hasUpgrade flag to true.
     * 
     * @param string $path Path to a license upgrade file
     */
    protected function _loadFile( $path )
    {
        $module = $this->getModule();
        $xml = simplexml_load_file($path);
        if ((string)$xml->serial == $this->_license->getPurchaseId())
        {
            return;
        }
        $key = (string)$xml->product->attributes()->key;
        $constraint = array();
        foreach ($xml->constraint->children() as $child)
        {
            /* @var $child SimpleXMLElement */
            $value = (string)$child;
            if ('' === $value || null === $value)
            {
                $value = null;
            }
            $constraint[$child->getName()] = array(
            	'value' => $value ,
                'label' => (string)$child['label']
            ); 
        }
        $this->addData(array(
            'id' => (int)$xml->product->attributes()->id ,
            'label' => (string)$xml->product ,
            'key' => $key ,
            'version' => $module->getVersion() ,
            'purchase_id' => (string)$xml->serial ,
            'license_id' => (int)$xml->product->attributes()->license_id ,
            'constraint' => $constraint
        ));
        $this->_hasUpgrade = true;
    }
    
    /**
     * Return an appropriate module
     * 
     * @return Aitoc_Aitsys_Model_Module
     */
    public function getModule()
    {
        return $this->_license->getModule();
    }
    
    /**
     * Check if upgrade is possible with current license upgrade file
     * 
     * @return true
     */
    public function canUpgrade()
    {
        if (!$this->_hasUpgrade)
        {
            return false;
        }
        $module = $this->getModule();
        $currentConstrain = $this->_license->getConstrain();
        $constraint = $this->getConstraint();
        if (sizeof($currentConstrain) != sizeof($constraint))
        {
            return false;
        }
        $canUpgrade = false;
        foreach ($constraint as $type => $value)
        {
            $currentConstrain[$type] = $currentConstrain[$type]['value'];
            $value = $value['value'];
            if (!isset($currentConstrain[$type]))
            {
                return false;
            }
            if (null !== $value)
            {
                if ($currentConstrain[$type] > $value)
                {
                    return false;
                }
                else
                {
                    $canUpgrade = true;
                }
            }
            else
            {
                $canUpgrade = true;
            }
        }
        if($canUpgrade)
        {
            return $this->_license->isInstalled();
        }
        return false;
    }
    
    /**
     * Does upgrade file exist and valid
     * 
     * @return bool
     */
    public function hasUpgrade()
    {
        return $this->_hasUpgrade;
    }
    
    /**
     * Main upgrade process
     * 
     * @return Aitoc_Aitsys_Model_Module_License_Upgrade
     */
    public function upgrade()
    {
        if ($this->canUpgrade())
        {
            // without this check could lead to crash while saving new license xml files 
            if(!$this->_checkLicensesFilesPermissions())
            {
                throw new Aitoc_Aitsys_Model_License_Service_Exception(
                    "<strong>Please set writable permissions for the following files before upgrade:</strong><br />"
                     .implode('<br />', $this->_failedFiles).'<br />'
                     ."<strong>You can set permissions back for these files after upgrade.</strong>"
                );
            }
            $upgrade = array(
            	'upgrade_purchaseid' => $this->getPurchaseId() ,
                'purchaseid' => $this->_license->getPurchaseId()
            );
            $service = $this->_license->getService();
            $service->connect();
            $service->upgradeLicense($upgrade);
            $service->disconnect();
            $installXML = simplexml_load_file($this->_getInstallPath());
            $upgradeXML = simplexml_load_file($this->_getUpgradePath());
            $installXML->serial = (string)$upgradeXML->serial;
            $installXML->product['license_id'] = (string)$upgradeXML->product['license_id'];
            foreach ($upgradeXML->constraint->children() as $child)
            {
                $installXML->constraint->{$child->getName()} = (string)$child;
            }
            foreach($this->_getInstallPaths() as $installPath)
            {
                $installXML->asXML($installPath);
            }
            $this->_removeUpgradeXMLs();
            $this->getModule()->reset();
        }
        else
        {
            throw new Aitoc_Aitsys_Model_License_Service_Exception("Can`t upgrade to this license!");
        }
        return $this;
    }
    
}