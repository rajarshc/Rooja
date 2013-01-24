<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
final class Aitoc_Aitsys_Model_Module extends Aitoc_Aitsys_Abstract_Model
{
    protected $_errors = array(); 
    protected $_perfExt = null;
    protected $_needCorrection = false;

    /**
     *
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function getPlatform()
    {
        return $this->tool()->platform();
    }
    
    /**
     * 
     * @param string $error
     * @return Aitoc_Aitsys_Model_Module
     */
    public function addError( $error )
    {
        $this->_errors[] = $error;
        return $this;
    }
    
    /**
     * 
     * @param $errors
     * @return Aitoc_Aitsys_Model_Module
     */
    public function addErrors( array $errors )
    {
        foreach ($errors as $error)
        {
            $this->_errors[] = $error;
        }
        return $this;
    }
    
    public function getErrors( $clear = false )
    {
        $result = $this->_errors;
        if ($clear)
        {
            $this->_errors = array();
        }
        return array_unique($result);
    }
    
    /**
     * 
     * @param $translator
     * @param $session
     * @return bool
     */
    public function produceErrors( $translator , Mage_Adminhtml_Model_Session $session = null )
    {
        if (!$session)
        {
            $session = $this->tool()->getInteractiveSession();
        }
        if (!$session)
        {
            $session = Mage::getSingleton('adminhtml/session');
        }
        /* @var $session Mage_Adminhtml_Model_Session */
        foreach ($this->getErrors() as $error)
        {
            if (!is_array($error))
            {
                $error = (array)$error;
            }
            $msg = array_shift($error);
            $session->addError($translator->__($msg));
        }
        return $this->_errors ? true : false;
    }

    /**
     *
     * @return Aitoc_Aitsys_Model_Module
     */
    public function reset()
    {
        $path = $this->getInstall()->getPath();
        if (file_exists($path))
        {
            $this->tool()->testMsg('Reset by install file: '.$path);
            $this->loadByInstallFile($path);
        }
        else
        {
            $this->loadByModuleFile($this->getFile());
        }
        $this->updateStatuses();
        return $this;
    }

    public function getFile()
    {
        return $this->getData('file');
    }
    
    /**
     * @return string
     */
    protected function _getPerfExt($path = '')
    {
        if(is_null($this->_perfExt) && $path)
        {
            $this->_perfExt = 'perf';
            if(!file_exists($path.$this->_perfExt))
            {
                if(file_exists($path.'php'))
                {
                    $this->_perfExt = 'php';
                }
            }
        }
        return $this->_perfExt;
    }
    
    /**
     * 
     * @param $path
     * @return Aitoc_Aitsys_Model_Module
     */
    public function loadByInstallFile( $path )
    {
        $this->tool()->testMsg("Load by install file: ".$path);
        $xml = simplexml_load_file($path);
        $key = (string)$xml->product->attributes()->key;
        $linkId = (string)$xml->product->attributes()->link_id;
        $file = $this->tool()->filesystem()->getEtcDir().'/'.$key.'.xml';
        $perf = $this->tool()->filesystem()->getLocalDir().str_replace("_", DS, $key).DS."Model".DS."Performer.";
        $perf.= $this->_getPerfExt($perf);
        $this->addData(array(
            'id' => (int)$xml->product->attributes()->id ,
            'label' => (string)$xml->product ,
            'store_url' => (string)$xml->store_url,
            'key' => $key ,
            'link_id' => $linkId ,
            'value' => false ,
            'available' => false ,
            'access' => null ,
            'file' => $file,
            'perf' => $perf,
            'version' => (string)$xml->product->attributes()->version,
            'decode' => (bool)($this->_getPerfExt()=='perf')
        ))->_createInstall()->_createLicense($xml);
        $this->getInstall()->setPath($path);
        $this->getLicense()->setPurchaseId((string)$xml->serial)
            ->setCheckid( (string)$xml->checkid )
            ->setServiceUrl((string)$xml->service);
        $this->tool()->event('aitsys_module_load_install_file',array('module' => $this));
        #echo 'try: '.$path."<br/>";
        if (file_exists($file))
        {
             $this->_updateByModuleFile($file);
        }
        $this->_checkCorrectionStatus();
        return $this;
    }

    /**
     * @param $path
     * @param $key
     * @return Aitoc_Aitsys_Model_Module
     */
    public function loadByModuleFile( $path , $key = null )
    {
        if (!($path instanceof SplFileInfo))
        {
            $moduleFile = new SplFileInfo($path);
        }
        else
        {
            $moduleFile = $path;
            $path = $moduleFile->getPathname();
        }
        if (!$key)
        {
            list($key) = explode(".",$moduleFile->getFilename());
        }

        $this->addData(array(
            'key'   => $key,
            'available' => true ,
            'file'  => $path ,
            'version' => (string)Mage::getConfig()->getNode('modules')->{$key}->version
        ))->_updateByModuleFile($path)->_createInstall();
        $this->_checkCorrectionStatus();
        return $this;
    }
    
    protected function _checkCorrectionStatus()
    {
        if(version_compare($this->tool()->db()->dbVersion(), '2.15.6', 'ge'))
        {
            $dbStatus  = $this->tool()->db()->getStatus($this->getKey());
            $xmlStatus = $this->getValue();
            if($dbStatus !== $xmlStatus)
            {
                $this->_needCorrection = true;
                $this->tool()->platform()->setNeedCorrection();
            }
        }

        return $this;
    }
    
    public function isNeedCorrection()
    {
        return $this->_needCorrection;
    }

    public function getId()
    {
        return $this->getData('id');
    }

    public function isLicensed()
    {
        return $this->getId() ? true : false;
    }

    public function isAvailable()
    {
        return $this->getAvailable();
    }

    /**
     * @return Aitoc_Aitsys_Model_Module
     */
    public function updateStatuses()
    {
        $this->getInstall()->setStatusUnknown();
        $license = $this->getLicense();
        if ($license)
        {
            $this->tool()->testMsg("Unset performer");
            $license->setStatusUnknown()->unsPerformer();
        }

        $this->_updateInstallStatus()->_updateLicenseStatus();
        if($this->getPlatform()->isCheckAllowed())
        {
        	$this->tool()->event('aitsys_module_checkstatus_after',array('module' => $this));
        }
        $license = $this->getLicense();
        if (!$license || $license->isInstalled())
        {
            $this->setAvailable(true);
        }
        else
        {
            $this->setAvailable(false);
        }
        return $this;
    }

    /**
     * @return Aitoc_Aitsys_Model_Module_License
     */
    public function getLicense()
    {
        return $this->getData('license');
    }

    /**
     * @return Aitoc_Aitsys_Model_Module_Install
     */
    public function getInstall()
    {
        return $this->getData('install');
    }

    /**
     * @return bool
     */
    public function getValue()
    {
        return $this->getData('value');
    }

    public function getAccess()
    {
        return $this->getData('access');
    }
    
    public function getSourcePath( $suffix = '' )
    {
        return $this->getInstall()->getSourcePath($suffix);
    }

    /**
     * @param $path
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _updateByModuleFile( $path )
    {
        if (null === $this->getAccess())
        {
            if ($path instanceof SplFileInfo)
            {
                $moduleFile = $path;
                $path = $moduleFile->getPathname();
            }
            else
            {
                $moduleFile = new SplFileInfo($path);
            }

            $key = $this->getKey();
            $xml = simplexml_load_file($path);
            $this->tool()->testMsg('Update module: '.$key);
            $this->tool()->testMsg(htmlspecialchars($xml->asXML()));

            if (!$this->getLabel())
            {
                $this->setLabel($xml->modules->$key->self_name ? $xml->modules->$key->self_name : $key);
            }
            
            if($this->getPlatform()->isCheckAllowed())
            {
            	$access = $this->tool()->filesystem()->checkWriteable($path);
            }
            else
            {
            	$access = true;
            }
            $this->setValue('true' == (string)$xml->modules->$key->active)
            ->setAccess($access);
        }
        return $this;
    }

    /**
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _updateInstallStatus()
    {
        $this->getInstall()->checkStatus();
        return $this;
    }

    /**
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _updateLicenseStatus()
    {
        if ($license = $this->getLicense())
        {
            $license->checkStatus();
        }
        return $this;
    }

    /**
     * @param string $name
     * @param string $class
     * 
     * @return Aitoc_Aitsys_Model_Module_Abstract
     */
    protected function _getInstance($name, $class)
    {
        $model_class = 'Aitoc_Aitsys_Model_Module_'.$class;
        $model = new $model_class();
        $model->setModule($this)->init();
        $this->tool()->testMsg("Create child class: ".$model_class);
        $this->setData($name, $model);
        
        return $model;
    }

    /**
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _createLicense( SimpleXMLElement $file = null )
    {
        if (!$this->hasData('license'))
        {
            $this->tool()->testMsg("Create license object");
            if ($this->getId())
            {
                $model  = 'License';
                $model .= file_exists($this->getPerf())?'_Light':'';
                $this->_getInstance('license', $model);
                if ($file)
                {
                    if (isset($file->constraint))
                    {
                        $this->getLicense()->addConstrain($file->constraint);
                    }
                    if (isset($file->product['license_id']))
                    {
                        $this->getLicense()->setLicenseId($file->product['license_id']);
                    }  
                    
                    if (isset($file->product['ent_hash']))
                    {
                        $this->getLicense()->setEntHash((string)$file->product['ent_hash']);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _createInstall()
    {
        if (!$this->hasData('install'))
        {
            $model  = 'Install';
            $model .= file_exists($this->getPerf())?'_Light':'';
            $this->_getInstance('install', $model);
        }
        return $this;
    }
    
    /**
     * @return Aitoc_Aitsys_Model_Module
     */
    public function initSource()
    {
        if ($license = $this->getLicense())
        {
            $license->getPerformer();
        }
        return $this;
    }
    
    /**
     * @return Mage_Admin_Model_Acl
     */
    public function getAdminAcl()
    {
        if (!$this->hasData('admin_acl'))
        {
            $config = clone Mage::getConfig();
            $config->reinit();
            $config->setNode('adminhtml/acl/resources', '');
            $file = $config->getModuleDir('etc', $this->getKey()).DS.'config.xml';
            $config->loadFile($file);
            $node = $config->getNode('adminhtml/acl/resources');
            if ($node === false)
                return false;
                
            $acl = Mage::getModel('admin/acl');
            /* @var $acl Mage_Admin_Model_Acl */
            Mage::getSingleton('admin/config')->loadAclResources($acl, $node);
            $this->setData('admin_acl', $acl);
        }
        
        return $this->getData('admin_acl');
    }
    
    /**
     * Check if module has 'All' acl configuration
     *
     * @return unknown
     */
    public function hasAllAdminAcl()
    {
        $acl = $this->getAdminAcl();
        return $acl->has('all') || $acl->has('acl/admin');
    }
    
    /**
     * Check if module has acl configuration
     *
     * @param string $resource resource name
     * @return bool 
     */
    public function hasAdminAcl($resource)
    {
        $acl = $this->getAdminAcl();
        if (!preg_match('#^acl/#', $resource))
        {
            $resource .= 'acl/';
        }
        return $acl->has($resource);
    }

}