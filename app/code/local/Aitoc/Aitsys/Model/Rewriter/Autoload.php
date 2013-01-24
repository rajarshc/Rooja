<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 * @author Andrei
 */
class Aitoc_Aitsys_Model_Rewriter_Autoload
{
    static protected $_instance;
    protected $_rewriteConfig = array();
    protected $_rewriteDir      = '';
    protected $_aitocDirs = array( 'Aitoc' , 'AdjustWare' );
    
    private function __construct()
    {
        $this->_rewriteDir = dirname(Mage::getRoot()) . Aitoc_Aitsys_Model_Rewriter_Abstract::REWRITE_CACHE_DIR;
        $this->_readConfig();
    }
    
    public function getRewriteDir()
    {
        return $this->_rewriteDir;
    }
    
    static public function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function clearConfig()
    {
        $this->_rewriteConfig = array();
    }
    
    public function setupConfig()
    {
        $this->_readConfig();
    }
    
    public function getRewritedClasses()
    {
        $classes = array();
        foreach (array_keys($this->_rewriteConfig) as $class)
        {
            if (!strstr($class,'file:'))
            {
                $classes[] = $class;
            }
        }
        return $classes;
    }
    
    public function getFileConfig( $file )
    {
        if (isset($this->_rewriteConfig['file:'.$file]))
        {
            if (!is_array($this->_rewriteConfig['file:'.$file]))
            {
                $this->_rewriteConfig['file:'.$file] = unserialize($this->_rewriteConfig['file:'.$file]);
            }
            return $this->_rewriteConfig['file:'.$file];
        }
        return null;
    }
    
    static public function register( $base = false )
    {
        /* deprecated since 2.19.0
        if (defined('COMPILER_INCLUDE_PATH'))
        {
            $paths = array();
            $paths[] = BP . DS . 'app' . DS . 'code' . DS . 'local';
            $paths[] = BP . DS . 'app' . DS . 'code' . DS . 'community';
            #$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'core';
            #$paths[] = BP . DS . 'lib';

            $appPath = implode(PS, $paths);
            set_include_path($appPath . PS . get_include_path());
        }
        */
        $rewriter = new Aitoc_Aitsys_Model_Rewriter();
        $rewriter->preRegisterAutoloader($base);
        
        // unregistering all, and varien autoloaders to make our performing first
        $autoloaders = spl_autoload_functions();
        if ($autoloaders and is_array($autoloaders) && !empty($autoloaders))
        {
            foreach ($autoloaders as $autoloader)
            {
                spl_autoload_unregister($autoloader);
            }
        }
        if (version_compare(Mage::getVersion(),'1.3.1','>'))
        {
            spl_autoload_unregister(array(Varien_Autoload::instance(), 'autoload'));
        }
        spl_autoload_register(array(self::instance(), 'autoload'), false);
        if (version_compare(Mage::getVersion(),'1.3.1','>'))
        {
            Varien_Autoload::register();
        }
        else
        {
            spl_autoload_register(array(self::instance(), 'performStandardAutoload'));
            #self::_loadOverwrittenClasses();
        }
    }
    
    static protected function _loadOverwrittenClasses()
    {
        $instance = self::instance();
        $loaded = array();
        foreach ($instance->getRewriteClassConfig() as $class => $file)
        {
            if (!isset($loaded[$file]))
            {
                $loaded[$file] = $file;
                $instance->autoload($class);
            }
        }
    }
    
    public function performStandardAutoload($class)
    {
        return __autoload($class);
    }
    
    public function autoload($class)
    {
        if (in_array($class, array_keys($this->_rewriteConfig)))
        {
            $classFile = $this->_rewriteDir . $this->_rewriteConfig[$class] . '.php';
            try
            {
                return include $classFile;
            }
            catch (Exception $e)
            {
                if (!file_exists($classFile))
                {
                    $rewriter = new Aitoc_Aitsys_Model_Rewriter();
                    $rewriter->prepare();
                    return $this->autoload($class);
                }
                throw $e;
            }
        }
        /* should work now without this fix
        foreach ($this->_aitocDirs as $dir)
        {
            if (stristr($class,$dir))
            {
                if (!class_exists($class, false))
                {
                    $file = uc_words($class,DS).'.php';
                    if(function_exists('apc_cache_info') && $cache=@apc_cache_info() ) {
                        if(apc_compile_file($file)) {
                            return false;
                            //or
                            apc_delete_file($file);
                        }
                    }
                    
                    return include $file;
                }
            }
        }
        */
        return false;
    }
    
    public function getRewriteClassConfig()
    {
        $result = array();
        foreach ($this->_rewriteConfig as $class => $file)
        {
            if (!strstr('file:',$class))
            {
                $result[$class] = $file;
            }
        }
        return $result;
    }
    
    protected function _readConfig()
    {
        /**
        * This config was created when creating rewrite files
        */
        $configFile = $this->_rewriteDir . 'config.php';
        if (file_exists($configFile))
        {
            @include($configFile);
        }
        // $rewriteConfig was included from file
        if (isset($rewriteConfig))
        {
            $this->_rewriteConfig = $rewriteConfig;
        }
    }
    
    public function hasClass( $class )
    {
        return isset($this->_rewriteConfig[$class]);
    }
}
