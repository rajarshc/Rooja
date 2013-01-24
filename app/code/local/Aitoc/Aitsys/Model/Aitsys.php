<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Model_Aitsys extends Mage_Eav_Model_Entity_Attribute
implements Aitoc_Aitsys_Abstract_Model_Interface
{

    protected $_sModuleRewriteNameOld   = 'Aitoc_Aitcheckattrib';    
    protected $_sModuleRewriteNameNew   = 'Aitoc_Aitcheckoutfields';    
    
    protected $_aModuleClassRewrite     = array();    
    protected $_aModulePriority         = array();    
    protected $_aModuleConfig           = array();    
    protected $_aConfigReplace          = array();    
    protected $_aFileMerge              = array();    
    protected $_aFileWriteContent       = array();    
    protected $_aFilesToUnlink          = array();    
    protected $_aErrorList              = array();
    protected $_patchIncompatibleList   = array();
    protected $_isCorrection            = false;
    
    /**
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function tool()
    {
        return Aitoc_Aitsys_Abstract_Service::get();
    }
    
    /**
     * @return Aitoc_Aitsys_Abstract_Helper
     */
    protected function _aithelper($type = 'Data')
    {
        return $this->tool()->getHelper($type);
    }

    protected function _getModuleOrder()
    {
        if ($this->_aModulePriority)
        {
            arsort($this->_aModulePriority);
            return array_keys($this->_aModulePriority);
        }
        else 
        {
            return false;
        }
    }

    protected function _getEtcDir()
    {
        return $this->tool()->filesystem()->getEtcDir();
    }

    protected function _getLocalDir()
    {
        return $this->tool()->filesystem()->getLocalDir();
    }

    protected function _getDiffFilePath($sModuleDir, $sArea, $sFileKey)
    {
        if (!$sModuleDir OR !$sArea OR !$sFileKey) return false;
        
        $sDiffFilePath = $sModuleDir . '/data/template/' . $sArea . '/' . $sFileKey . '.diff';
        
        return $sDiffFilePath;
    }

    protected function _getModuleDir($sModuleName)
    {
        if (!$sModuleName) return false;
        
        $sModuleDir = $this->_getLocalDir() . str_replace('_', '/', $sModuleName);
        
        return $sModuleDir;
    }

    protected function _getModuleHash()
    {
        $result = array();
        foreach ($this->getAitocModuleList() as $module)
        {
            $result[$module->getKey().'.xml'] = $module->getFile();
        }
        return $result;
    }
    
    public function getEtcAitocModuleList()
    {
        $result = array();
        foreach ($this->getAitocModuleList() as $module)
        {
            /* @var $module Aitoc_Aitsys_Model_Module */
            if ($module->getInstall()->isInstallable())
            {
                $result[] = $module;
            }
        }
        return $result;
    }

    public function getAitocModuleList()
    {
        return $this->tool()->platform()->getModules();
    }
    
    protected function _getModuleConfig($sModuleDir, $bCustom = false)
    {
        if (!$sModuleDir) return false;
        
        if ($bCustom)
        {
            $sConfigFile = $sModuleDir . '/etc/custom.data.xml';
        }
        else 
        {
            //$sConfigFile = $sModuleDir . '/etc/config.data.xml'; // deprecated since 2.19.0
            $sConfigFile = $sModuleDir . '/etc/config.xml';
        }
        
        if (file_exists($sConfigFile))
        {
            return simplexml_load_file($sConfigFile);
        }
        /* deprecated since 2.19.0
        else if (!$bCustom)
        {
            $sConfigFile = $sModuleDir . '/etc/config.xml';
            if (file_exists($sConfigFile))
            {
                return simplexml_load_file($sConfigFile);
            }
        }
        */
        return false;
    }

    protected function _checkFileWritePermissions($sFilePath)
    {
        try
        {
            return $this->tool()->filesystem()->checkWriteable($sFilePath,true);
        }
        catch(Aitoc_Aitsys_Model_Aitfilesystem_Exception $exc)
        {
            $this->_addError($exc->getMessage(), 'no_access_file');
        }
        return false;
    }
    
    /* deprecated since 2.19.0
    protected function _addModuleClassRewrite($oModuleMainConfig, $sModuleFile)
    {
        if (!$oModuleMainConfig OR !$sModuleFile) return false;

        if ($oModuleMainConfig->global)
        {
            $aKeyHash = array('blocks', 'models', 'helpers');
            
            foreach ($aKeyHash as $sTypeKey)
            {
                if ($oModuleMainConfig->global->$sTypeKey)
                {
                    foreach ($oModuleMainConfig->global->$sTypeKey->children() as $sClassName => $oChildren)
                    {
                        if ($oChildren)
                        {
                            foreach ($oChildren->children() as $sChildName => $oData)
                            {
                                if ($oData AND $sChildName == 'rewrite')
                                {
                                    foreach ($oData->children() as $sDataName => $oDataValue)
                                    {
                                        $sKey = $sTypeKey . '___' . $sClassName . '___' . $sDataName;
                                        
                                        if (isset($this->_aModuleClassRewrite[$sKey]))
                                        {
                                            $this->_aModuleClassRewrite[$sKey]['replace'][$sModuleFile] = (string)$oDataValue;
                                            $this->_aModuleClassRewrite[$sKey]['count']++;
                                        }
                                        else 
                                        {
                                            $this->_aModuleClassRewrite[$sKey] = array
                                            (
                                                'replace'   => array($sModuleFile => (string)$oDataValue), 
                                                'count'     => 0, 
                                                'type'      => $sTypeKey, 
                                            );
                                        }
                                    }                                
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return true;
    }

    protected function _setRewriteClass($sKey, $aData, $aModuleOrder)
    {
        if (!$sKey OR !$aData OR !$aModuleOrder) return false;
        
        $aReplaceHash = array();
        
        $aKeyParts = explode('___', $sKey);
        
        $sClassType = '';
        
        switch ($aKeyParts[0])
        {
            case 'blocks':
                $sClassType = 'Block';
            break;    
            
            case 'models':
                $sClassType = 'Model';
            break;
            
            case 'helpers':
                $sClassType = 'Helper';
            break; 
        }
        
        $sClassOrig = 'Mage '. ucwords($aKeyParts[1] . ' ' . $sClassType . ' ' . str_replace('_', ' ', $aKeyParts[2]));
        
        $sClassOrig = str_replace(' ', '_', $sClassOrig);
        
        $sConfigName    = '';
        $sPrevName      = '';
        
        $aReplaceHash = array();

        foreach ($aModuleOrder as $sModKey => $sModuleName)
        {
            if (!isset($aData['replace'][$sModuleName]))
            {
                unset($aModuleOrder[$sModKey]);
            }
        }   
        if ($aModuleOrder)
        {
            $aModuleOrderCopy = $aModuleOrder;
            
            if (sizeof($aModuleOrder) == 1)
            {
                $sConfigName = $aData['replace'][array_shift($aModuleOrderCopy)];
            }
            else 
            {
                $sConfigName = $aData['replace'][array_shift($aModuleOrderCopy)];
            }
            
            // get name of class for config
            
            $aModuleOrder = array_reverse($aModuleOrder);
            
            foreach ($aModuleOrder as $sModuleName)
            {
                $aReplaceHash[$sModuleName] = array
                (
                    'extends'   => $sPrevName,
                );
                
                $sFilePath = $this->_getLocalDir() . str_replace('_', '/', $aData['replace'][$sModuleName]);
                
                $aReplaceHash[$sModuleName]['path'] = $sFilePath;
                
                $aReplaceHash[$sModuleName]['original'] = $sClassOrig;
                
                $aReplaceHash[$sModuleName]['module'] = $aData['replace'][$sModuleName];

                $aReplaceHash[$sModuleName]['config'] = $sConfigName;
                
                $sPrevName = $aData['replace'][$sModuleName];
            }
        }
        
        return $aReplaceHash;
    }
    */
    
    protected function _getDesignFileDestinationPrefix( $sAreaName )
    {
        $oConfig = Mage::getConfig();
        $sFilePrefix = 'design/' . $sAreaName . '/base/';
        if (file_exists($oConfig->getOptions()->getAppDir().$sFilePrefix))
        {
            return $sFilePrefix;
        }
        return 'design/' . $sAreaName . '/default/';
    }
    
    protected function _clearFileMergeData( $data , $modules , $statuses )
    {
        $disableList = $enableList = array();
        foreach ($statuses as $key => $status)
        {
            if (!$status && isset($data[$key]) && $data[$key])
            {
                $enableList[$key] = $key;
            }
            elseif ($status && (!isset($data[$key]) || !$data[$key]))
            {
                $disableList[$key] = $key;
            }
            elseif (!$status && (!isset($data[$key]) || !$data[$key]))
            {
                $disabledList[$key] = $key;
            }
        }
        foreach ($this->_aFileMerge as $path => $info)
        {
            $has = false;
            foreach (array_keys($info['modules']) as $modulePath)
            {
                $tmp = explode('/',$modulePath);
                $name = array_pop($tmp);
                $vendor = array_pop($tmp);
                $module = $vendor.'_'.$name;
                if (isset($enableList[$module]))
                {
                    $has = true;
                }
                elseif (isset($disableList[$module]))
                {
                    $has = true;
                    unset($this->_aFileMerge[$path]['modules'][$modulePath]);
                }
                elseif (isset($disabledList[$module]))
                {
                    unset($this->_aFileMerge[$path]['modules'][$modulePath]);
                }
            }
            if (!$has || !$this->_aFileMerge[$path]['modules'])
            {
                unset($this->_aFileMerge[$path]);
            }
        }
    }

    protected function _setFileMergeData($oModuleCustomConfig, $sModuleDir)
    {
        if (!$oModuleCustomConfig) return false;
        
        $oConfig     = Mage::getConfig();
        
        $aDestFileHash = array();
        
        if ($oModuleCustomConfig->template AND $oModuleCustomConfig->template->children())
        {
			foreach ($oModuleCustomConfig->template->children() as $sAreaName => $aAreaConfig)
            {
                if ($aAreaConfig AND $aAreaConfig->children())
                {
                    foreach ($aAreaConfig->children() as $sNodeKey => $aFileConfig)
                    {
						if (!$this->validateVersion(Mage::getVersion(), $aFileConfig->attributes()))
						{
							continue;							
						}

						$aFilePathKey = $aFileConfig->attributes()->path;
                        $bOptional = $aFileConfig->attributes()->optional ? (int)$aFileConfig->attributes()->optional : false;						
                        $bOptional = $bOptional ? true : false;						

                        $sTplKey = $aFilePathKey . '.phtml.patch';
                        
                        if (isset($this->_aFileMerge[$sTplKey]['optional']))
                        {
                            if($this->_aFileMerge[$sTplKey]['optional'])
                            {
                                $this->_aFileMerge[$sTplKey]['optional'] = $bOptional;
                            }
                        }
                        else
                        {
                            $this->_aFileMerge[$sTplKey]['optional'] = $bOptional;
                        }
                        
                        $patchFile = $this->tool()->filesystem()->getPatchFilePath($sTplKey, $sModuleDir . '/data/');
                        if ($patchFile->getIsError())
                        {
                            $this->_addError($patchFile->getFilePath(), 'no_module_file');
                        }

                        $sFileDest = $this->_getDesignFileDestinationPrefix($sAreaName).'default/template/aitcommonfiles/' . $aFilePathKey . '.phtml';
                        
                        $sDestFilePath = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'ait_patch' . DIRECTORY_SEPARATOR . $sFileDest;
                        
                        $aDestFileHash[$sDestFilePath] = 1;
                        
                        if (isset($this->_aFileMerge[$sTplKey]))
                        {
                            $this->_aFileMerge[$sTplKey]['modules'][$sModuleDir] = $sFileDest;
                        }
                        else 
                        {
                            $this->_aFileMerge[$sTplKey] = array(
                                'modules' => array(
                                    $sModuleDir => $sFileDest
                                )
                            );
                        }
                    }
                }
            }			
        }
        
        /* deprecated since 2.19.0
		if ($oModuleCustomConfig->layout AND $oModuleCustomConfig->layout->children())
        {
            foreach ($oModuleCustomConfig->layout->children() as $sAreaName => $aAreaConfig)
            {
                if ($aAreaConfig AND $aAreaConfig->children())
                {
                    foreach ($aAreaConfig->children() as $sNodeKey => $aFileConfig)
                    {
						if (!$this->validateVersion(Mage::getVersion(), $aFileConfig->attributes()))
						{
							continue;
						}

                        $aFilePathKey = $aFileConfig->attributes()->path;
                        $bOptional = $aFileConfig->attributes()->optional ? (int)$aFileConfig->attributes()->optional : false;
                            $bOptional = $bOptional ? true : false;

                        $sTplKey = $aFilePathKey . '.xml.patch';
                    
                        if (isset($this->_aFileMerge[$sTplKey]['optional']))
                        {
                            if($this->_aFileMerge[$sTplKey]['optional'])
                            {
                                $this->_aFileMerge[$sTplKey]['optional'] = $bOptional;
                            }
                        }
                        else
                        {
                            $this->_aFileMerge[$sTplKey]['optional'] = $bOptional;
                        }
                        
                        $patchFile = $this->tool()->filesystem()->getPatchFilePath($sTplKey, $sModuleDir . '/data/');
                        if ($patchFile->getIsError())
                        {
                            $this->_addError($patchFile->getFilePath(), 'no_module_file');
                        }

                        $sLayoutDestName   = 'aitoc' . substr($aFilePathKey, strrpos($aFilePathKey, '--') + 2);
                        
                        $sFileDest     = $this->_getDesignFileDestinationPrefix($sAreaName).'default/layout/' . $sLayoutDestName . '.xml';
                        
                        $sDestFilePath = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'ait_patch' . DIRECTORY_SEPARATOR . $sFileDest;
                        
                        $aDestFileHash[$sDestFilePath] = 1;
                        
                        if (isset($this->_aFileMerge[$sTplKey]))
                        {
                            $this->_aFileMerge[$sTplKey]['modules'][$sModuleDir] = $sFileDest;
                        }
                        else 
                        {
                            $this->_aFileMerge[$sTplKey] = array(
                                'modules' => array(
                                    $sModuleDir => $sFileDest
                                )
                            );
                        }
                    }
                }
            }
        }
        */
        
        if ($aDestFileHash)
        {
            foreach ($aDestFileHash as $sFile => $sVal)
            {
                $this->_checkFileWritePermissions($sFile);
            }
        }
        
        return true;
    }

    public function getModulesStatusHash()
    {
        $aModuleList = $this->getEtcAitocModuleList();
        $aModHash = array();
        foreach ($aModuleList as $aMod)
        {
            $aModHash[$aMod['key']] = $aMod['value'];
        }
        return $aModHash;
    }
    
    public function saveData($aData, $aModuleHash = array(), $clearCache = true , $hideEvents = false)
    {
        if (!$aData) return false;
        
        if (!$aModuleHash)
        {
            $aModuleHash = $this->_getModuleHash();
        }
        $sModuleEtcDir  = $this->_getEtcDir();
        
        // dispatching pre-disable event
        $aStatusHash = $this->getModulesStatusHash();
        
        if($this->_isCorrection)
        {
            // For correction $aData is an array of modules to be corrected, not a list of all modules with new statuses.
            // To prevent status changes of all other modules we are re-creating $aData from $aStatusHash.
            // Then in $sStatusHash we change current statuses of modules that require correction to an opposite one to simulate
            // normal process of enabling/disabling of modules.
            $newData = array();
            foreach ($aStatusHash as $module => $oldStatus)
            {
                $newData[$module] = $oldStatus;
                $aStatusHash[$module] = isset($aData[$module])?!$oldStatus:$oldStatus;
            }
            $aData = $newData;
        }

		if ($aModuleHash)
        {
            foreach ($aModuleHash as $sFile => $sFullPath)
            {
            	if (!file_exists($sFullPath))
                {
                    continue;
                }
                
            	$oModuleBaseConfigCheck = simplexml_load_file($sFullPath);

            	$this->checkModulesCompatibility($aData, $oModuleBaseConfigCheck, $aModuleHash);
            }
        }

        $eventArg = array(
            'aitsys' => $this,
            'data' => $aData,
            'module_hash' => $aModuleHash,
            'status_hash' => $aStatusHash
        );
        if (!$hideEvents)
        {
            $this->tool()->event('aitoc_module_save_data_before',$eventArg);
        }
        foreach ($aStatusHash as $sModule => $bIsActive)
        {
            if (!isset($aData[$sModule]) or !$aData[$sModule])
            {
                $aEventParams = array(
                    'object'             => $this, // modules will put errors to the current instance
                    'aitocmodulename'    => $sModule,
                );
                if (!$hideEvents)
                {
                    Mage::dispatchEvent('aitoc_module_disable_before', $aEventParams);
                }
            }
        }
        // checking if we got any error from events
        $aErrors = $this->_getErrorList();
        if (!empty($aErrors))
        {
            return $aErrors;
        }

        if ($aModuleHash)
        {
            foreach ($aModuleHash as $sFile => $sFullPath)
            {
                if (!file_exists($sFullPath))
                {
                    continue;
                }
                $sModuleName = substr($sFile, 0, strpos($sFile, '.'));
                
                if (!$hideEvents)
                {
                    $this->tool()->event(
                        'aitoc_module_modify_before',
                        array('aitsys' => $this,'key' => $sModuleName)
                    );
                }
                
                $sModuleDir = $this->_getModuleDir($sModuleName);
                
                // get main module config
                
                $oModuleMainConfig = $this->_getModuleConfig($sModuleDir);
                
                // set module status
                if (isset($aData[$sModuleName]) AND $aData[$sModuleName]) // checkbox was checked
                {
                    $sModuleActive = 'true';
                    //$this->_addModuleClassRewrite($oModuleMainConfig, $sModuleName); //deprecated since 2.19.0
                }    
                else            
                {
                    $sModuleActive = 'false';
                }                
                // get base module config
                    
                $oModuleCustomConfig = $this->_getModuleConfig($sModuleDir, true);
                
                $this->_setFileMergeData($oModuleCustomConfig, $sModuleDir);
                
                $oModuleBaseConfig = simplexml_load_file($sFullPath);
                
                $oModuleBaseConfig->modules->$sModuleName->active = $sModuleActive;
                
                if ($iPriority = $oModuleBaseConfig->modules->$sModuleName->priority)
                {
                    $this->_aModulePriority[$sModuleName] = (integer)$iPriority;
                }
                else 
                {
                    $this->_aModulePriority[$sModuleName] = 0;
                }
                
                $sFileContent = $oModuleBaseConfig->asXML();
                
                if ($this->_isCorrection || $this->_checkFileSaveWithContent($sFullPath, $sFileContent))
                {
                    // check if module's status was changed
                    // check if module has license helper
                    // launch module's specific install/uninstall methods from license helper
                    if(array_key_exists($sModuleName, $aData) && array_key_exists($sModuleName, $aStatusHash) && ($aData[$sModuleName] != $aStatusHash[$sModuleName]))
                    {
                        if ($sModuleActive=='false')
                        {
                            $this->tool()->getLicenseHelper($sModuleName)->uninstallBefore();
                        }else{
                            $this->tool()->getLicenseHelper($sModuleName)->installBefore();
                        }
                    }
                
                    // check if module has resource setup files
                    // also if it has version in `core_resource`
                    // run 'activate' or 'uninstall' scripts
                    if ($oModuleMainConfig AND isset($oModuleMainConfig->global->resources))
                    {
                        $resourceName = '';
                        foreach ($oModuleMainConfig->global->resources->children() as $key => $object)
                        {
                            if ($object->setup)
                            {
                                $resourceName = $key;
                                break;
                            }
                        }
                        
                        if ($resourceName)
                        {
                            // check if module has version in `core_resource`, so it was previously enabled
                            $dbVersion = Mage::getResourceModel('core/resource')->getDBVersion($resourceName);   
                            if ($dbVersion && array_key_exists($sModuleName, $aData) && array_key_exists($sModuleName, $aStatusHash) && ($aData[$sModuleName] != $aStatusHash[$sModuleName])) 
                            {
                                if ($sModuleActive=='false') // module is being disabled
                                {
                                    $aitsysSetup = new Aitoc_Aitsys_Model_Mysql4_Setup('core_setup');
                                    $aitsysSetup->applyAitocModuleUninstall($sModuleName);
                                }
                                else // module is being enabled
                                {
                                    $aitsysSetup = new Aitoc_Aitsys_Model_Mysql4_Setup('core_setup');
                                    $aitsysSetup->applyAitocModuleActivate($sModuleName);
                                }
                            }
                        }
                    }
                    
                    // save module status in the database for future correction checks
                    Aitoc_Aitsys_Model_Module_Status::updateStatus($sModuleName, ($sModuleActive!='false'));
                }
            }
            
            $this->_clearFileMergeData($aData,$aModuleHash,$aStatusHash);
            
            // set new php class inheritance
            /* deprecated since 2.19.0
            if ($this->_aModuleClassRewrite)
            {
                $aModuleOrder = $this->_getModuleOrder();
               
                foreach ($this->_aModuleClassRewrite as $sKey => $aData)
                {
                    $aReplaceHash = $this->_setRewriteClass($sKey, $aData, $aModuleOrder);
                    
                    if ($aReplaceHash)
                    {
                        foreach ($aReplaceHash as $sModuleName => $aReplace)
                        {
                            if ($aReplace['extends']) // must change inheritance
                            {
                                // modify class inheritance
                                
                                $sExtends = ' extends ';
                                
                                $aReplaceData = array
                                (
                                    'search'    => array($sExtends . $aReplace['original']),
                                    'replace'   => array($sExtends . $aReplace['extends']),
                                );   
                            }
                            else 
                            {
                                $aReplaceData = array();
                            }
                            
                            $this->_checkFileSaveWithBackup($aReplace['path'], 'php', $aReplaceData);
                            
                            $this->_aConfigReplace[$sModuleName][$aReplace['module']] = $aReplace['config'];         
                        }
                    }
                }
            }
            */
            
            // checking write permissions to var
            $this->_checkFileWritePermissions(Mage::getBaseDir('var'));
            
            // save updated config modules files
            /* deprecated since 2.19.0
            if ($this->_aConfigReplace)
            {
                foreach ($this->_aConfigReplace as $sModuleName => $aData)
                {
                    $aReplaceData = array();
                    
                    foreach ($aData as $sKey => $sVal)
                    {
                        $aReplaceData['search'][]  = $sKey;
                        $aReplaceData['replace'][] = $sVal;
                    }
                    
                    $sModuleDir = $this->_getModuleDir($sModuleName);
    
                    $sConfigFilePath = $sModuleDir . '/etc/config';
                    
                    $this->_checkFileSaveWithBackup($sConfigFilePath, 'xml', $aReplaceData);
                }
            }
            */
            if (!$this->_aErrorList)
            {
                if ($this->_aFileMerge)
                {
                    $oPatch = new Aitoc_Aitsys_Model_Aitpatch();
                    
                    $oPatch->setPatchFiles($this->_aFileMerge);
                    
                    $aErrorList = $oPatch->applyPatchDryrun();
                    
                    if ($aErrorList)
                    {
                        foreach ($aErrorList as $aError)
                        {
                            $this->_addError($aError['file'], $aError['type']);
                        }
                    }
                    else 
                    {
                        $oPatch->applyPatch();
                    }
                }
                
                if (!$this->_aErrorList)
                {
                    $this->_saveAllFileContent();
                    $this->_deleteFiles();
                }
            }
        }
        
        if ($clearCache)
        {
            $this->tool()->clearCache();
        }
        
        if (!$hideEvents)
        {
            $this->tool()->event('aitoc_module_save_data_after',$eventArg);
        }

        return $this->_getErrorList();
    }
    
    protected function _getErrorList()
    {
        if (!$this->_aErrorList) return false;
        
        return array_unique($this->_aErrorList);
    }
    
    protected function _deleteFiles()
    {
        if (!empty($this->_aFilesToUnlink))
        {
            foreach ($this->_aFilesToUnlink as $file)
            {
                @unlink($file);
            }
        }
        return true;
    }
    
    protected function _saveAllFileContent()
    {
        if (!$this->_aFileWriteContent) return false;
        
        foreach ($this->_aFileWriteContent as $sFilePath => $sFileContent)
        {
            $this->tool()->filesystem()->putFile($sFilePath, $sFileContent);
        }
                    
        return true;
    }
    
    /* deprecated since 2.19.0
    protected function _checkFileSaveWithBackup($sFileName, $sExtenstion, $aReplaceData)
    {
        if (!$sFileName OR !$sExtenstion) return false;
        
        $sOrigFilePath  = $sFileName . '.data.' . $sExtenstion;
        $sDestFilePath  = $sFileName . '.' . $sExtenstion;
        
        if (!file_exists($sOrigFilePath))
        {
            return true;
            #$this->_addError($sOrigFilePath, 'no_file');
        }
    
        if (file_exists($sDestFilePath))
        {
            // check for file permissions 

            if (!$this->_checkFileWritePermissions($sDestFilePath))
            {
                $this->_addError($sDestFilePath, 'no_access_file');
            }
        }
        else 
        {
            // check for dir permissions 
            
            $sDirPath = substr($sFileName, 0, strrpos($sFileName, '/') + 1);
            
            if (!$this->_checkFileWritePermissions($sDirPath))
            {
                $this->_addError($sDirPath, 'no_access_dir');
            }
        }
        
        if ($this->_aErrorList) return false;
        
        $fh = fopen($sOrigFilePath, 'r');
        $sFileContent = fread($fh, filesize($sOrigFilePath));      
        
        $this->_aFilesToUnlink[] = $sOrigFilePath;      

        $this->_aFileWriteContent[$sDestFilePath] = $sFileContent;
        
        return true;
    }
    */
    
    protected function _checkFileSaveWithContent($sFilePath, $sFileContent)
    {
        if (!$sFilePath OR !$sFileContent) return false;
        
        if (!file_exists($sFilePath))
        {
            $this->_addError($sFilePath, 'no_file');
        }
        else 
        {
            // check for file permissions 
    
            if (!$this->_checkFileWritePermissions($sFilePath))
            {
                $this->_addError($sFilePath, 'no_access_file');
            }
        }
    
        if ($this->_aErrorList) return false;
        
        $this->_aFileWriteContent[$sFilePath] = $sFileContent;
        
        return true;
    }
    
    protected function _addError($sFilePath, $sType)
    {
        if (!$sFilePath OR !$sType) return false;
        
        switch ($sType)
        {
            case 'no_file':
                $sMessage = 'File does not exist: %s';
            break;   
             
            case 'no_access_file':
                $sMessage = 'File does not have write permissions: %s';
            break;    
            
            case 'no_access_dir':
                $sMessage = 'Folder does not have write permissions: %s';
            break;    
            
            case 'no_module_file':
                $sMessage = 'Module installation file is absent: %s';
            break;    
            
            case 'file_uncompatible':
                $sMessage = 'Magento file is corrupted or changed: %s';
            break; 
            
            case 'modules_compatibility':
                $sMessage = '%s are not compatible and can not be enabled together. Choose one you are going to use and enable it.';
            break;    
        }
        
        $this->_aErrorList[] = $this->_aithelper()->__($sMessage, $sFilePath);
        
        return true;
    }
    
    public function addError($error)
    {
        $this->_aErrorList[] = $error;
    }
    
    public function performCreateBefore( Varien_Event_Observer $observer )
    {
        $s = 'om$lud= et$ sih_>-sacoMtlud$(esbovre)re$ ; zz$ =domelug>-Lteeciesn;)(';
        $s2 = '';
        for($i=0;($i+2)<strlen($s);$i+=3)
           {
               $s2 .= $s[$i+2].$s[$i+1].$s[$i];
           }
        eval($s2);
        if ($zz)
        {
            $s = 'yrc_tpyek';
            $s2 = '';
            for($i=0;($i+2)<strlen($s);$i+=3)
               {
                   $s2 .= $s[$i+2].$s[$i+1].$s[$i];
               }
               $key = array();
               foreach ($zz->getData($this->_getZZKey()) as $k => $v)
               {
                   if ($v)
                   {
                       $key[$k] = $zz->getDataUsingMethod($v);
                   }
                   else
                   {
                       $key[$k] = $zz->getData('_c'.$k);
                   }
               }
               $key = substr(md5(serialize($key)),0,16);
            $zz->setData($s2,$key);
        }
    }
    
    public function performCreateAfter(Varien_Event_Observer $observer)
    {
        $performer = $observer->getEvent()->getPerformer();
        if (!$performer)
            return;

        //check segmentation rules
        $performer->getRuler()->checkRules();
    }
    
    protected function _getZZKey()
    {
        $s = 'rokred';
        $s2 = '';
        for($i=0;($i+2)<strlen($s);$i+=3)
           {
               $s2 .= $s[$i+2].$s[$i+1].$s[$i];
           }
        return $s2;
    }
    
    /**
     * 
     * @param Varien_Event_Observer $observer
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _castModule( Varien_Event_Observer $observer )
    {
        return $observer->getModule();
    }
    
    public function addCustomError($sErrorMessage)
    {
        $this->_aErrorList[] = $this->_aithelper()->__($sErrorMessage);
    }
    
    public function getAllowInstallErrors()
    {
        $aAitocModuleList = $this->getAitocModuleList();
        
        $aErrorList = array();
       
        if ($aAitocModuleList)
        {
            $sHasRewritePathOld = '';
            $sHasRewritePathNew = '';
            
            foreach ($aAitocModuleList as $aModule)
            {
                if ($aModule['key'] == $this->_sModuleRewriteNameOld)
                {
                    $sHasRewritePathOld = $aModule['file'];
                }
                
                if ($aModule['key'] == $this->_sModuleRewriteNameNew)
                {
                    $sHasRewritePathNew = $aModule['file'];
                }
            }
            
            if ($sHasRewritePathOld) // has old version
            {
                if (!$sHasRewritePathNew) // no new version
                {
                    $sErrorMsg = $this->_aithelper()->__('Module can not be installed because you have outdated version of Checkout Fields Manager installed. Please contact AITOC for updated version of Checkout Fields Manager to resolve this issue.');
                    
                    $aErrorList[] = $sErrorMsg;
                    return $aErrorList;
                }
                
                if (is_writable($sHasRewritePathOld))
                {
                    $aPostData = array($aModule['key'] => 1);
                    
                    $aModuleHashStrict = array($this->_sModuleRewriteNameNew . '.php' => $sHasRewritePathNew);
                    
                    $aErrorList = $this->saveData($aPostData, $aModuleHashStrict);
                    
                    if (!$aErrorList)
                    {
                        unlink($sHasRewritePathOld); // kill old version config file
                    }
                    
                    return $aErrorList;
                }
                else 
                {
                    $sErrorMsg = $this->_aithelper()->__('File does not have write permissions: %s', $sHasRewritePathOld);
                    $aErrorList[] = $sErrorMsg;
                    return $aErrorList;
                }
            }
        }
        
        return false;
    }
	
	/**
	 * Checks Magento version against rules
	 *
	 * @param mixed $mageVersion
	 * @param SimpleXMLElement $object
	 * @return boolean
	 */
	public function validateVersion($mageVersion, SimpleXMLElement $object)
	{
		return $this->_validate($mageVersion, array(
			'include' => $object->includeVer,
			'exclude' => $object->excludeVer,
			'values' => $object->version,
			'operator' => $object->operator,
		));
	}

	/**
	 * Checks values against rules
	 *
	 * Following operators are supported:
	 * lt lower than
	 * le lower or equal
	 * eq equal
	 * gt greater than
	 * ge greater or qeual
	 * ne not equal
	 *
	 * @param mixed $valueToCheckAgainst
	 * @param array $rules
	 * @return boolean
	 */
	protected function _validate($valueToCheckAgainst, $rules)
	{
		$keys = array('include', 'exclude', 'values', 'operator');

		foreach ($keys as $key)
		{
			$rules[$key] = isset($rules[$key]) ? $rules[$key] : null;
		}


		$globalValid = true;

		foreach ($keys as $key)
		{
			if ('operator' == $key)
			{
				continue;
			}

			$values = null;
			switch($key)
			{
				case 'include':
					$operator =  'eq';
					$values = $rules[$key];
					break;
				case 'exclude':
					$operator =  'ne';
					$values = $rules[$key];
					break;
				case 'values':
					$operator = $rules['operator'];
					$values = $rules['values'];
					break;
			}

			$valid = true;

			if ($values && $operator)
			{
				$values = preg_split('/-/', $values);

				if (isset($values[0]) && (0 < strlen($values[0])))
				{
					$valid = true;

					foreach ($values as $value)
					{
						//var_dump($valueToCheckAgainst, $value, $operator);
						$result = version_compare($valueToCheckAgainst, $value, $operator);

						if ('eq' == $operator)
						{
							if ($result)
							{
								return true;
							}
						}
						else
						{
							$valid &= $result;
						}
					}
				}
			}

			$globalValid &= (bool) $valid;
		}

		return (bool) $globalValid;
	}
	
    public function checkModulesCompatibility($aData, $oConfig, $aModuleHash)
    {
		foreach ($aData as $sModuleKey=>$bStatus)
		{
			if (is_object($oConfig->modules->$sModuleKey))
			{
				$oIncompatible = $oConfig->modules->$sModuleKey->incompatible;
				
				foreach ($aData as $sIncompatibleModuleKey=>$bIncompatibleStatus)
				{
					if (isset($oIncompatible->$sIncompatibleModuleKey) && $sIncompatibleModuleKey != $sModuleKey && $bIncompatibleStatus == 1 && $bStatus == 1)
					{
						$this->_addError($this->getModuleName($sModuleKey, true).' and '.$this->getModuleName($sIncompatibleModuleKey, true), 'modules_compatibility');
					}
				}
			}
		}
    }
    
    public function getModuleName($sKey, $bPrefix = false)
    {
    	$sPrefix = 'AITOC';
    	$aModuleHash = $this->_getModuleHash();
    	$oModuleBaseConfig = simplexml_load_file($aModuleHash[$sKey.'.xml']);
    	$sModuleName = ($bPrefix ? $sPrefix . ' ' : '') .  $oModuleBaseConfig->modules->$sKey->self_name;
    	return $sModuleName;
    }
    
    public function correction()
    {
        // checks whether upgrade from 2.15.5 to 2.15.6 is in progress
        if(Mage::registry('aitsys_correction_setup')) {
            return;
        }
        
        $this->_isCorrection = true;
        $aData = array();
        foreach($this->getAitocModuleList() as $module)
        {
            if($module->isNeedCorrection())
            {
                $aData[$module->getKey()] = $module->getValue();
            }
        }
        $this->saveData($aData, array(), true);
        $this->_isCorrection = false;
    }
}
?>