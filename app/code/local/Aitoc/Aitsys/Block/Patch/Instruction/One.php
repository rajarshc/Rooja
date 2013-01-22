<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitsys_Block_Patch_Instruction_One extends Aitoc_Aitsys_Abstract_Adminhtml_Block
{
    protected $_sourceFile    = '';
    protected $_extensionPath = '';
    protected $_extensionName = '';
    protected $_patchFile     = '';
    protected $_removeBasedir = true;
    
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('aitsys/patch/instruction/one.phtml');
    }
    
    public function setSourceFile($path)
    {
        $this->_sourceFile = $path;
    }
    
    public function setExtensionPath($path)
    {
        $this->_extensionPath = $path;
    }
    
    public function setExtensionName($name)
    {
        $this->_extensionName = $name;
    }
    
    public function setPatchFile($file)
    {
        $this->_patchFile = $file;
    }
    
    protected function _getBaseDir()
    {
        return str_replace(array('/','\\'),DS,Mage::getBaseDir());
    }
    
    public function getSourceFile($includeBasedir = false)
    {
        $path = $this->_sourceFile;
        $path = str_replace(array('/','\\'),DS,$path);
        if ($this->_removeBasedir && !$includeBasedir)
        {
            $path = str_replace($this->_getBaseDir(), '', $path);
        }
        return $path;
    }    
    
    public function getExtensionPath($includeBasedir = false)
    {
        $path = $this->_extensionPath;
        $path = str_replace(array('/','\\'),DS,$path);
        if ($this->_removeBasedir && !$includeBasedir)
        {
            $path = str_replace($this->_getBaseDir(),'', $path);
        }
        return $path;
    }
    
    public function getExtensionName()
    {
        return $this->_extensionName;
    }
    
    public function getPatchFile()
    {
        return str_replace(array('/','\\'),DS,$this->_patchFile);
    }
    
    public function getPatchedFileName()
    {
        return str_replace('.patch', '', $this->getPatchFile());
    }
    
    public function getDestinationFile()
    {
        $destinationFile = str_replace(Mage::getBaseDir('app'), Mage::getBaseDir('var') . DS . 'ait_patch', $this->getSourceFile(true));
        $destinationFile = substr($destinationFile, 0, strrpos($destinationFile, DS) + 1);
        $destinationFile = str_replace(strstr($destinationFile,'template'),'',$destinationFile);
        $destinationFile .= 'template'.DS.'aitcommonfiles'.DS.str_replace('.patch', '', $this->getPatchFile());
        if ($this->_removeBasedir)
        {
            $destinationFile = str_replace($this->_getBaseDir(), '', $destinationFile);
        }
        return $destinationFile;
    }
    
    public function getDestinationDir()
    {
        return dirname($this->getDestinationFile());
    }
    
    public function getPatchConfigPath()
    {
        $config = $this->getExtensionPath() . DS . 'etc' . DS . 'custom.data.xml';
        return htmlspecialchars($config);
    }
    
    public function getPatchConfigLine()
    {
        $configLine = '<file path="' . substr($this->getPatchFile(), 0, strpos($this->getPatchFile(), '.')) . '"></file>';
        return htmlspecialchars($configLine);
    }
    
    /**
    * 
    * @return Aitoc_Aitsys_Model_Aitfilepatcher
    */
    protected function _makeAitfilepatcher()
    {
        return new Aitoc_Aitsys_Model_Aitfilepatcher();
    }
    
    public function getPatchContents()
    {
        $patcher = $this->_makeAitfilepatcher();
        $oFileSys    = $this->tool()->filesystem();

        $patchPath = $oFileSys->getPatchFilePath($this->getPatchFile(), $this->getExtensionPath(true) . DS . 'data' . DS)->getFilePath();
        $patchInfo = $patcher->parsePatch(file_get_contents($patchPath));
        $html = '<div class="patch">';
        
        foreach ($patchInfo as $_data)
        {
            foreach ($_data['aChanges'] as $_data)
            {
                $bAfter  = false;
                $bAdd    = false;
                $bBefore = false;
                $bLastAfter  = false;
                $bLastAdd    = false;
                $bLastBefore = false;
                $str = '';
                $aChunk = array();
                foreach ($_data['aChangingStrings'] as $_line)
                {
                    if ($_line[0] == '+') {
                        $bBefore = false;
                        $bAdd    = true;
                        $bAfter  = false;
                    } 
                    elseif ($_line[0] == ' ') {
                        if ($bAdd || $bBefore) {
                            $bAfter  = false;
                            $bBefore = true;
                            $bAdd    = false;
                        }
                        else {
                            $bAfter  = true;
                            $bBefore = false;
                        }
                    }
                    if ($bLastAfter && !$bAfter) {
                        $aChunk[] = array(
                            'part' => 'after',
                            'str'  => $str,
                        );
                        $str = '';
                    }
                    elseif ($bLastAdd && !$bAdd) {
                        $aChunk[] = array(
                            'part' => 'add',
                            'str'  => $str,
                        );
                        $str = '';
                    }
                    elseif ($bLastBefore && !$bBefore) {
                        $aChunk[] = array(
                            'part' => 'before',
                            'str'  => $str,
                        );
                        $str = '';
                    }
                    $str .= htmlspecialchars(rtrim($_line[2])) . "\r\n";
                    $bLastAfter  = $bAfter;
                    $bLastAdd    = $bAdd;
                    $bLastBefore = $bBefore;
                }
                if ($bBefore) {
                    $aChunk[] = array(
                        'part' => 'before',
                        'str'  => $str,
                    );
                }
                $html .= $this->_getChunkHtml($aChunk);
            }
        }
        $html .= '</div>';
        return $html;
    }
    
    protected function _getChunkHtml($chunk)
    {
        $html = '';
        foreach ($chunk as $part) {
            if ($part['part'] == 'after') {
                continue;
            }
            if ($part['part'] == 'add') {
                $html .= $this->__('You will need to add the following lines &mdash;') ;
                $html .= '<pre>';
                $html .= $part['str'];
                $html .= '</pre>';
            }
            if ($part['part'] == 'before') {
                $html .= $this->__('The above lines should be added BEFORE the following code or similar to it &mdash;') ;
                $html .= '<pre>';
                $html .= $part['str'];
                $html .= '</pre>';
            }
        }
        return $html;
    }
}