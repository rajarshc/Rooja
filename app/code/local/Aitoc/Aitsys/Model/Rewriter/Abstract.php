<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 * @author Andrei
 */
abstract class Aitoc_Aitsys_Model_Rewriter_Abstract extends Aitoc_Aitsys_Abstract_Model
{
    protected $_etcDir          = '';
    protected $_codeDir         = '';
    protected $_rewriteDir      = '';
    protected $_checkClassDir   = array();
    protected $_phpcli          = false;
    protected $_conn;
    protected $_localConfig;
    
    const REWRITE_CACHE_DIR = '/var/ait_rewrite/';
    
    public function __construct()
    {
        $this->_etcDir      = Mage::getRoot().'/etc/';
        $this->_codeDir     = Mage::getRoot().'/code/';
        $this->_rewriteDir  = dirname(Mage::getRoot()) . self::REWRITE_CACHE_DIR;
        
        $this->_checkClassDir[] = $this->_codeDir . 'local/';
        $this->_checkClassDir[] = $this->_codeDir . 'community/';
        $this->_checkClassDir[] = $this->_codeDir . 'core/';
        
        if (!file_exists($this->_rewriteDir))
        {
            $this->tool()->filesystem()->mkDir($this->_rewriteDir);
        }
    }
}