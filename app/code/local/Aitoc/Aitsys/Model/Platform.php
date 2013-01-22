<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
final class Aitoc_Aitsys_Model_Platform extends Aitoc_Aitsys_Abstract_Model
{
    
    const PLATFORMFILE_SUFFIX = '.platform.xml';
    const INSTALLATION_DIR = 'ait_install';
    const CACHE_CLEAR_VERSION = '2.15.6';
    
    /**
     * 
     * @var Aitoc_Aitsys_Model_Platform
     */
    static protected $_instance;
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    static public function getInstance()
    {
        if (!self::$_instance)
        {
            self::$_instance = new self();
            try
            {
                try
                {
                    self::$_instance->init();
                }
                catch (Exception $exc)
                {
                    self::$_instance->block();
                    throw $exc;
                }
            }
            catch (Aitoc_Aitsys_Model_Aitfilesystem_Exception $exc)
            {
                $msg = "Error in the file: %s. Probably it does not have write permissions.";
                self::$_instance->addAdminError(Aitoc_Aitsys_Abstract_Service::get()->getHelper()->__($msg,$exc->getMessage()));
            }
        }
        return self::$_instance;
    }
    
    protected $_block = false;
    
    protected $_modulesList; // Module_Name => array( 'module_path' => Module_Path, 'module_file' => Module_File )
    
    protected $_modules = array();
    
    protected $_version;
    
    protected $_installDir;
    
    protected $_licenseDir; // rastorguev fix
    
    protected $_copiedPlatformFiles = array();
    
    /**
     * 
     * @var Aitoc_Aitsys_Model_License_Service
     */
    protected $_service = array();
    
    protected $_moduleIgnoreList = array('Aitoc_Aitinstall'=>0, 'Aitoc_Aitsys'=>0, 'Aitoc_Aitprepare'=>0);
    
    protected $_aitocPrefixList = array('Aitoc_','AdjustWare_');
    
    protected $_moduleDirs = array( 'Aitoc' , 'AdjustWare' );
    
    protected $_reloaded = false;
    
    protected $_needCorrection = false;
    
    protected $_adminError = '';
    
    protected $_adminErrorEventLoaded = false;
    
    protected $_addEntHash;
    
    public function addAdminError($message)
    {
        $this->_adminError = $message;
        $this->renderAdminError();
    }
    
    public function renderAdminError($eventLoaded = false)
    {
        if($eventLoaded)
        {
            $this->_adminErrorEventLoaded = true;
        }
        if($this->_adminErrorEventLoaded && $this->_adminError)
        {
            $admin = Mage::getSingleton('admin/session');
            if ($admin->isLoggedIn())
            {
                $session = Mage::getSingleton('adminhtml/session');
                /* @var $session Mage_Adminhtml_Model_Session */
                $session->addError($this->_adminError);
                $this->_adminError = '';
            }
        }
        return $this;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function block()
    {
        $this->_block = true;
        return $this;
    }
    
    public function getModuleDirs()
    {
        return $this->_moduleDirs;
    }
    
    public function isAitocNamespace( $namespace , $compare = false )
    {
        if ($compare)
        {
            return in_array($namespace,$this->_moduleDirs);
        }
        foreach ($this->_moduleDirs as $dir)
        {
            if (false !== strstr($namespace,$dir))
            {
                return true;
            }
        }
        return false;
    }
    
    public function isBlocked()
    {
        return $this->_block;
    }
    
    public function getModules()
    {
        if (!$this->_modules)
        {
            $this->_generateModuleList();
        }
        return $this->_modules;
    }
    
    public function getModuleKeysForced()
    {
        $modules = array();
        foreach($this->_modulesList as $moduleKey => $moduleData)
        {
            $modules[$moduleKey] = ('true' == (string)Mage::getConfig()->getNode('modules/'.$moduleKey.'/active'));
        }
        return $modules;
    }
    
    /**
     * 
     * @param $key
     * @return Aitoc_Aitsys_Model_Module
     */
    public function getModule( $key )
    {
        $this->getModules();
        return isset($this->_modules[$key]) ? $this->_modules[$key] : null;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_License_Service
     */
    public function getService( $for = 'default' )
    {
        if (!isset($this->_service[$for]))
        {
            $notExcluded = (!$this->hasDebugExclude() || !in_array($for,$this->debug_exclude));
            if ($this->isDebug() && $notExcluded)
            {
                $this->tool()->testMsg('Use debug service');
                $this->_service[$for] = new Aitoc_Aitsys_Model_License_Service_Debug();
            }
            else
            {
                $this->tool()->testMsg('Use real service');
                $this->_service[$for] = new Aitoc_Aitsys_Model_License_Service();
            }
            $this->_service[$for]->setServiceUrl($this->getServiceUrl());
        }
        return $this->_service[$for];
    }
    
    public function isDebug()
    {
        return $this->getData('debug');
    }
    
    public function isDebugingAllowed()
    {
        return $this->getData('debuging_allowed') ? true : false;
    }
    
    public function isCheckAllowed()
    {
    	return $this->getData('no_check') ? false : true;
    }
    
    public function getServiceUrl()
    {
        if ($url = $this->tool()->getApiUrl())
        {
            return $url;
        }
        if ($url = $this->getData('_service_url'))
        {
            return $url;
        }
        $url = $this->getData('service_url');
        return $url ? $url : Mage::getStoreConfig('aitsys/service/url');
    }
    
    public function getVersion()
    {
        if (!$this->_version)
        {
            $this->_version = (string)Mage::app()->getConfig()->getNode('modules/Aitoc_Aitsys/version'); 
        }
        return $this->_version;
    }
    
    /**
     * 
     * @param $mode
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function setTestMode( $mode = true )
    {
        if (!$this->isModePresetted())
        {
            $this->setData('mode',$mode ? 'test' : 'live');
        }
        return $this;
    }
    
    public function isModePresetted()
    {
        return $this->hasData('mode');
    }
    
    public function isTestMode() 
    {
        return $this->getData('mode') == 'test';
    }
    
    public function isDemoMode()
    {
        return $this->getData('demo_mode') ? true : false;
    }
    
    public function getInstallDir( $base = false )
    {
        if (!$this->_installDir)
        {
            $this->_installDir = $this->tool()->filesystem()->getAitsysDir().'/install/';
        }
        return $this->_installDir;
    }
    
    public function getLicenseDir( $base = false )
    {
        if (!$this->_licenseDir)
        {
            $this->_licenseDir = BP.'/var/'.self::INSTALLATION_DIR.'/';
            
            if(!$this->tool()->filesystem()->isWriteable($this->_licenseDir))
            {
                throw new Aitoc_Aitsys_Model_Aitfilesystem_Exception($this->_licenseDir." should be writeable");
            }
            
            if(!file_exists($this->_licenseDir))
            {
                $this->tool()->filesystem()->mkDir($this->_licenseDir);
            }
        }
        if ($base)
        {
            return $this->_licenseDir;
        }
        return rtrim($this->_licenseDir.$this->getPlatformId(),'/').'/';
    }
    
    public function getPlatformId()
    {
        return $this->getData('platform_id');
    }
    
    /**
     * 
     * @param $platformId
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function setPlatformId( $platformId )
    {
        return $this->setData('platform_id',$platformId);
    }
    
    public function init()
    {
        $this->_fixOldPlatform();
        if (!$this->_loadConfigFile()->_loadPlatformData()->getPlatformId())
        {
            $license = $this->getAnyLicense();
            $service = $license ? $license->getService() : $this->getService();
            
            if ($this->tool()->cleanDomain($service->getServiceUrl()) 
             == $this->tool()->cleanDomain(Mage::getBaseUrl()))
            {
                $this->reset();
                return;
            }
            
            $this->tool()->testMsg('begin register platform');
            
            try
            {
                $service->connect();
                
                $data = array(
                	'purchaseid' => $license ? $license->getPurchaseId() : '' ,
                    'initial_module_list' => $this->getModulePurchaseIdQuickList()
                );
                $platformId = $service->registerPlatform($data);
                
                $service->disconnect();
                $this->tool()->testMsg('Generated platform id: '.$platformId);
                $this->setPlatformId($platformId);
                $this->setServiceUrl($service->getServiceUrl());
                $this->_savePlatformData();
                $this->_copyToPlatform($platformId);
                $this->unsPlatformId();
                $this->_loadPlatformData();
            }
            catch (Exception $exc)
            {
                $this->tool()->testMsg($exc);
            }
        }
        $this->_checkNeedCacheCleared();
        $this->reset();
    }
    
    protected function _checkNeedCacheCleared()
    {
        if(version_compare( $this->tool()->db()->dbVersion(), self::CACHE_CLEAR_VERSION, 'lt' )) {
            $this->tool()->clearCache();
        }
    }
    
    protected function _fixOldPlatform()
    {
        $installDir = $this->getInstallDir(true);
        if ($platforms = glob($installDir.'*.platform.xml'))
        {
            foreach ($platforms as $platformFile)
            {
                $platformId = $this->_castPlatformId($platformFile);
                $platformDir = $this->getLicenseDir().$platformId;
                $this->tool()->filesystem()->makeDirStructure($platformDir);
                $oldPlatformDir = $this->getInstallDir().$platformId;
                if ($pathes = glob($oldPlatformDir.'/*'))
                {
                    foreach ($pathes as $path)
                    {
                        $fileinfo = pathinfo($path);
                        if ('xml' == $fileinfo['extension'])
                        {
                            $to = $this->getInstallDir().$fileinfo['basename'];
                        }
                        else
                        {
                            $to = $this->getLicenseDir().$platformId."/".$fileinfo['basename'];
                        }
                        $this->tool()->filesystem()->moveFile($path,$to);
                    }
                }
                $this->tool()->filesystem()->moveFile($platformFile,$platformDir.'.platform.xml');
                $this->tool()->filesystem()->rmFile($oldPlatformDir);
            }
        }
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function save()
    {
        return $this->_savePlatformData();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function reset()
    {
        $this->_modules = array(); // to reinit all licensed modules after platform registration
        foreach ($this->getModules() as $module) 
        {
            $this->tool()->testMsg('Update module '.$module->getLabel().' status after generating');
            $module->updateStatuses();
        }
        return $this;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function reload()
    {
        if($this->_reloaded) {
            return $this;
        }
        foreach ($this->_modules as $module) 
        {
            $license = $module->getLicense();
            if($license && !$license->isLight()) {
                continue;
            }
            if(!$license || $license->checkStatus()->isInstalled())
            {
                $module->setAvailable(true);
            }
            else
            {
                $module->setAvailable(false);
            }
        }        
        $this->_reloaded = true;
        return $this;
    }    
    
    /**
     * @param string $moduleKey
     * @return bool
     */
    public function isIgnoredModule( $moduleKey )
    {
        return isset($this->_moduleIgnoreList[$moduleKey]);
    }
    
    public function isPlatformFileName( $filename )
    {
        return preg_match('/'.preg_quote(self::PLATFORMFILE_SUFFIX).'$/',$filename);
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_License | null
     */
    public function getAnyLicense()
    {
        $path = $this->getInstallDir().'*.xml';
        if ($pathes = glob($path))
        {
            foreach ($pathes as $path)
            {
                if (!$this->isPlatformFileName($path))
                {
                    $module = $this->_makeModuleByInstallFile($path);
                    return $module->getLicense();
                }
            }
        }
    }
    
    public function getModulePurchaseIdQuickList()
    {
        $this->_createModulesList()->_loadLicensedModules();
        $list = array();
        foreach($this->_modules as $module)
        {
            $list[$module->getKey()] = $module->getLicense()->getPurchaseId(); 
        }
        return $list;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _loadConfigFile()
    {
        $path = dirname($this->getInstallDir(true)).'/config.php';
        $this->tool()->testMsg('check config path: '.$path);
        if (file_exists($path))
        {
            include $path;
            if (isset($config) && is_array($config))
            {
                $this->tool()->testMsg('loaded config:');
                $this->tool()->testMsg($config);
                $this->setData($config);
            }
        }
        return $this;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _generateModuleList()
    {
        $this->tool()->testMsg('Try to generate module list!'); 
        
        $this->_createModulesList()->_loadLicensedModules()->_loadAllModules();

        $this->tool()->event('aitsys_generate_module_list_after');
        $this->tool()->testMsg('Module list generated');
        return $this;
    }
    
    /**
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _loadLicensedModules()
    {
        $this->_loadLicensedModulesOld();
        foreach ($this->_modulesList as $moduleKey => $moduleData)
        {
            if(isset($this->_modules[$moduleKey]))
            {
                // module already loaded from the old-format license file
                // in the _loadLicensedModulesOld method
                continue;
            }
            
            $licenseFile = $moduleData['module_path'].'/etc/'.Aitoc_Aitsys_Model_Module_License::LICENSE_FILE;
            if(!@is_file($licenseFile))
            {
                // module license file not found in the module's `etc` folder
                continue;
            }
            
            // loading module
            $this->tool()->testMsg("Try load licensed module");
            $module = $this->_makeModuleByInstallFile($licenseFile);
            
            $this->_addLicensedModule($module);
        }
        
        return $this;
    }
    
    /**
     * Compatibility with Aitsys older then 2.18.0
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _loadLicensedModulesOld()
    {
        if (!file_exists($this->getInstallDir()))
        {
            return $this;
        }
        
        $dir = new DirectoryIterator($this->getInstallDir());
        
        foreach ($dir as $item)
        {
            /* @var $item DirectoryIterator */
            if ($item->isFile())
            {
                $filename = $item->getFilename();
                if ('.xml' === substr($filename, -4, 4))
                {
                    if ($this->isPlatformFileName($filename))
                    {
                        continue;
                    }
                    if ($this->isUpgradeFilename($filename))
                    {
                        continue;
                    }
                    // loading module
                    $this->tool()->testMsg("Try load licensed module with old license file");
                    $module = $this->_makeModuleByInstallFile($item->getPathname());
                    
                    $this->_addLicensedModule($module);                 
                }
            }
        }
        return $this;
    }
    
    protected function _addLicensedModule(Aitoc_Aitsys_Model_Module $module)
    {
        if ((!$this->_addEntHash() && $module->getLicense()->getEntHash()) || ($this->_addEntHash() && !$module->getLicense()->getEntHash()))
        {
            $this->_moduleIgnoreList[$module->getKey()] = 'ER_ENT_HASH';
            return;
        }
    
        $key = $module->getKey();
        $this->tool()->testMsg("Try load licensed module finished: ".$key);
        if (!isset($this->_modules[$key]))
        {
            $this->tool()->testMsg("Add new module");
            $this->_modules[$key] = $module;
        }
        else
        {
            $this->tool()->testMsg("Reset existed module");
            $this->_modules[$key]->reset();
        }
    }
    
    protected function _addEntHash()
    {
        if(is_null($this->_addEntHash))
        {
            $this->_addEntHash = false;
            
            $etcDir = $this->tool()->fileSystem()->getEtcDir();
            $eeModuleXmlFile = $etcDir . DS . 'Enterprise_Enterprise.xml';
            if(file_exists($eeModuleXmlFile))
            {
                try{
                    $eeModule = new SimpleXMLElement($eeModuleXmlFile, 0, true);
                    $val = $eeModule->modules->Enterprise_Enterprise->active;
                    $this->_addEntHash = ((string)$val == 'true');
                }catch(Exception $e){}
            }
        }
        return $this->_addEntHash;
    }
    
    public function isUpgradeFilename( $filename )
    {
        return false !== strstr($filename,'.upgrade-license.xml');
    }
    
    /**
     * Load certain module by using its license file
     * 
     * @param $path
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _makeModuleByInstallFile( $path )
    {
        $module = new Aitoc_Aitsys_Model_Module();
        $module->loadByInstallFile(str_replace('.php','.xml',$path));
        $this->tool()->testMsg(get_class($module->getLicense()));
        $this->tool()->event('aitsys_create_module_after',array('module' => $module));
        return $module;
    }
    
    /**
     * Based on /code/local/Aitoc|AdjustWare subfolders
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _createModulesList()
    {
        if(is_null($this->_modulesList))
        {
            $this->_modulesList = array();
            $aitocModulesDirs = $this->tool()->filesystem()->getAitocModulesDirs();
            foreach($aitocModulesDirs as $aitocModuleDir)
            if(@file_exists($aitocModuleDir) && @is_dir($aitocModuleDir))
            {
                $aitocModuleSubdirs = new DirectoryIterator($aitocModuleDir);
                foreach ($aitocModuleSubdirs as $aitocModuleSubdir)
                {
                    // skip dots folders
                    if(in_array($aitocModuleSubdir->getFilename(), $this->tool()->filesystem()->getForbiddenDirs()))
                    {
                        continue;
                    }
                    
                    $moduleKey  = basename($aitocModuleDir)."_".$aitocModuleSubdir->getFilename();
                    if(!$this->isIgnoredModule($moduleKey))
                    {
                        $moduleFile = $this->tool()->filesystem()->getEtcDir()."/{$moduleKey}.xml";
                        $this->_modulesList[$moduleKey] = array(
                            'module_path' => $aitocModuleSubdir->getPathname(),
                            'module_file' => @is_file($moduleFile) ? $moduleFile : null
                        );
                    }
                }
        }
        }
        return $this;
    }
    
    /**
     * Return list of all Aitocs' modules or certain module info
     * 
     * @return array
     */
    public function getModulesList($module = '')
    {
        if(!$module)
        {
            return $this->_modulesList;
        }
        return isset($this->_modulesList[$module]) ? $this->_modulesList[$module] : null;
    }
    
    /**
     * Load all modules which have main config file
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _loadAllModules()
    {
        foreach ($this->_modulesList as $moduleKey => $moduleData)
        {
            if($moduleData['module_file']) // only if the config file for this module in /app/etc/modules does exist
            {
                $this->_makeModuleByModuleFile($moduleKey, $moduleData['module_file']);
            }
        }
        return $this;
    }
    
	/**
	 * Load certain module by using its main config file 
	 * 
     * @param string $moduleKey
     * @param string $moduleFile
     * 
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _makeModuleByModuleFile( $moduleKey, $moduleFile )
    {
        $this->tool()->testMsg('Check: '.$moduleKey.' -- '.$moduleFile);
        
        // check if module was already loaded during licensed modules load
        if ($module = (isset($this->_modules[$moduleKey]) ? $this->_modules[$moduleKey] : null))
        {
            return $module;
        }
        
        $this->tool()->testMsg('Create: '.$moduleKey);
        $module = new Aitoc_Aitsys_Model_Module();
        $module->loadByModuleFile($moduleFile, $moduleKey);
        if($this->isIgnoredModule($moduleKey)) {
            $module->setErrorMessageType( $this->_moduleIgnoreList[$moduleKey] );
        }

        return $this->_modules[$moduleKey] = $module;
    }

    protected function _castPlatformId( $file )
    {
        if ($file instanceof SplFileInfo)
        {
            $file = $file->getFilename();
        }
        $fileinfo = pathinfo($file);
        list($platformId) = explode('.',$fileinfo['basename'],2);
        return $platformId;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _loadPlatformData()
    {
        $this->_copiedPlatformFiles = array();

        foreach ($this->getPlatforms() as $item)
        {
            /* @var $item DirectoryIterator */
            $platformId = $this->_castPlatformId($item);
            // start rastorguev fix
            if (!file_exists($item->getPathname()))
            {
                $this->tool()->testMsg("Platform id broken or superfluous: ".$platformId);
                continue;
            }
            // finish rastorguev fix
            if ($this->getPlatformId() || !$this->_checkPlatformId($platformId,$item->getPathname()))
            {
                $this->tool()->testMsg("Platform id broken or superfluous: ".$platformId);
                $this->_removePlatform($platformId);
                continue;
            }
            $dom = new DOMDocument('1.0');
            $dom->load($item->getPathname());
            $platform = $dom->getElementsByTagName('platform');
            if ($platform->length)
            {
                $platform = $platform->item(0);
                foreach ($platform->childNodes as $item)
                {
                    if ('location' == $item->nodeName)
                    {
                        continue;
                    }
                    #$this->tool()->debug($item->nodeName.': '.$item->nodeValue);
                    if (!$this->hasData($item->nodeName))
                    {
                        
                        $this->setData($item->nodeName,$item->nodeValue);
                    }
                }
            }
            $this->setPlatformId($platformId);
            $this->tool()->testMsg("Platform id:".$platformId);
        }
        if ($platformId = $this->getPlatformId())
        {
            $this->_copyToPlatform($platformId);
        }
        return $this;
    }
    
    /**
     * 
     * @return SplFileInfo[]
     */
    public function getPlatforms()
    {
        $result = array();
        try{
            $dir = new DirectoryIterator($this->getLicenseDir(true));
        }
        catch (Aitoc_Aitsys_Model_Aitfilesystem_Exception $exc){
            throw $exc;
        }
        catch (Exception $e){
            return $result;
        }

        foreach ($dir as $item)
        {            
            /* @var $item DirectoryIterator */
            if ($item->isFile() && $this->isPlatformFileName($item->getFilename()))
            {
                $result[] = $item->getFileInfo();
            }
        }
        return $result;
    }
    
    public function getPlatformPathes()
    {
        $pathes = array();
        foreach ($this->getPlatforms() as $item)
        {
            $platformId = $this->_castPlatformId($item);
            $pathes[] = dirname($item->getPathname()).'/'.$platformId.'/';
        }
        return $pathes;
    }
    
    protected function _checkPlatformId( $platformId , $path ) 
    {
        $dom = new DOMDocument('1.0');
        $dom->load($path); 
        if ($location = $dom->getElementsByTagName('location')->item(0))
        {
            /* @var $location DOMElement */
            if ($location->getAttribute('domain') == $this->tool()->getRealBaseUrl()
             && $location->getAttribute('path') == $this->_getLocationPath())
            {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _savePlatformData()
    {
        if ($platformId = $this->getPlatformId())
        {
//            $defaultInstallDir = $this->getInstallDir(true);
            $defaultInstallDir = $this->getLicenseDir(true); // rastorguev fix
            $path = $defaultInstallDir.$platformId.self::PLATFORMFILE_SUFFIX;
            $this->tool()->testMsg("Save platform path: ".$path);
            
            
            $dom = $this->getPlatformDom();
            $dom->save($path);
            if (!file_exists($path))
            {
                $msg = 'Write permissions required for: '.$defaultInstallDir.' and all files included.';
                throw new Aitoc_Aitsys_Model_Aitfilesystem_Exception($msg);
            }
        }
        return $this;
    }
    
    /**
     * 
     * Genarate platform DOM structure
     * @param $configData custom configuration data
     * @return DOMDocument
     */
    public function getPlatformDom($configData = array())
    {
        $data = array(
            'domain' => $this->tool()->getRealBaseUrl(),
            'path'   => $this->_getLocationPath(),
        ); 
        if ($configData) {
            $data = array_merge($data, $configData);
        }
        $dom = new DOMDocument('1.0');
        $platform = $dom->createElement('platform');
        $dom->appendChild($platform);
        $this->tool()->testMsg(array('try to save',$this->getData()));
        foreach ($this->getData() as $key => $value)
        {
            if (is_array($value))
            {
                continue;
            }
            $platform->appendChild($dom->createElement($key,$value));
        }
        $location = $dom->createElement('location');
        /* @var $location DOMElement */
        $location->setAttribute('domain',$data['domain']);
        $location->setAttribute('path',$data['path']);
        $platform->appendChild($location);
        
        return $dom;
    }
    
    private function _getLocationPath()
    {
        return $this->getInstallDir(true); 
        //return $this->getLicenseDir(true); // rastorguev fix 
    }
    
    /**
     * 
     * @param $platformId
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _copyToPlatform($platformId)
    {
//        $path = $this->getInstallDir(true);
        $path = $this->getLicenseDir(true); // rastorguev fix
        $platformPath = $path.$platformId.DS;
        if (!file_exists($platformPath))
        {
            $this->tool()->filesystem()->makeDirStructure($platformPath);
        }
        $dir = new DirectoryIterator($path);
        foreach ($dir as $item)
        {
            /* @var $item DirectoryIterator */ 
            $filename = $item->getFilename();
            if ($item->isFile() && !$this->isPlatformFileName($filename) 
             && $item->getFilename() != $this->tool()->getUrlFileName())
            {
                if (!$this->tool()->filesystem()->isWriteable($item->getPathname()))
                {
                    throw new Aitoc_Aitsys_Model_Aitfilesystem_Exception("File does not have write permissions: ".$item->getPathname());
                }
                $to = $platformPath.$filename;
                if (file_exists($to) && $this->_isCopiedPlatformFile($item->getFilename()))
                {
                    $this->tool()->filesystem()->rmFile($item->getPathname());
                }
                else
                {
                    $this->tool()->filesystem()->moveFile($item->getPathname(),$to);
                }
            }
        }
        return $this;
    }
    
    private function _isCopiedPlatformFile( $file )
    {
        return in_array($file,$this->_copiedPlatformFiles);
    }
    
    /**
     * 
     * @param $platformId
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _removePlatform( $platformId )
    {
        $this->_modules = array();
        return $this->_copyFromPlatform($platformId);
    }
    
    /**
     * 
     * @param $platformId
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _copyFromPlatform( $platformId )
    {
//        $path = $this->getInstallDir(true);
        $path = $this->getLicenseDir(true); //rastorguev fix
        $dir = new DirectoryIterator($path.$platformId);
        foreach ($dir as $item)
        {
            /* @var $item DirectoryIterator */
            $filename = $item->getFilename();
            if ($item->isFile() && '.php' !== substr($filename, -4))
            {
                $this->tool()->filesystem()->cpFile($item->getPathname(),$path.$filename);
                $this->_copiedPlatformFiles[] = $filename;
            }
        }
        return $this;
    }
    
    public function setNeedCorrection($value = true)
    {
        $this->_needCorrection = $value;
        return $this;
    }

    public function isNeedCorrection()
    {
        return $this->_needCorrection;
    }
}
