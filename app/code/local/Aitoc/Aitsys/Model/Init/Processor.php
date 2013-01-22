<?php

class Aitoc_Aitsys_Model_Init_Processor
{
    
    private $_configPath;
    
    private $_modulePaths = array();
    
    public function __construct()
    {
        $this->_configPath = Mage::getRoot().'/etc/modules/';
        $basePath = Mage::getRoot().'/code/local/';
        $this->_modulePaths = array(
            $basePath.'Aitoc/' ,
            $basePath.'AdjustWare/'
        );
    }
    
    public function isInstallerEnabled()
    {
        return $this->_isEnabled('Aitoc_Aitsys');
    }
    
    public function realize()
    {
        foreach ($this->_modulePaths as $path)
        {
            $paths = glob($path.'*');
            if ($paths)
            {
                foreach ($paths as $path)
                {
                    if ($this->_isEnabledByPath($path))
                    {
                        $path = $path.'/*.inc.php';
                        $paths = glob($path);
                        if ($paths)
                        {
                            foreach ($paths as $path)
                            {
                                include $path;
                            }
                        }
                    }
                }
            }
        }
    }
    
    protected function _isEnabledByPath( $path )
    {
        $path = str_replace('\\','/',$path);
        $pathItems = explode('/',$path);
        $moduleName = array_slice($pathItems,-2);
        $moduleName = join('_',$moduleName);
        return $this->_isEnabled($moduleName);
    }
    
    protected function _isEnabled( $moduleName )
    {
        $path = $this->_configPath.$moduleName.'.xml';
        if (file_exists($path))
        {
            $content = file_get_contents($path);
            return preg_match('!<active>\s*true\s*</active>!',$content);
        }
        return false;
    }
    
}