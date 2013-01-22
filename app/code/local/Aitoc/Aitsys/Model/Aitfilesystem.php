<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Model_Aitfilesystem extends Aitoc_Aitsys_Abstract_Model
{
    //permissions
    const PERM_FILE     = 0644;
    const PERM_DIR      = 0755;
    const PERM_ALL      = 0777;

    const MODE_ALL      = 'all';
    const MODE_NORMAL   = 'normal';
    
    //may be eighter 'all' or 'normal'
    protected $_permissionsMode;

    protected $_subsystemsDirs = array();
    
    protected $_closedMainFolders;
    
    protected $_forbiddenDirs = array('.', '..', '.svn', '.git');

    /**
     * Makes temporary file in var/ folder
     *
     * @param string $sFromFile
     * @return string file path
     */
    public function makeTemporary($sFromFile)
    {
        $oConfig = Mage::getConfig();
        $sFileType = substr($sFromFile, strrpos($sFromFile, '.'));
        $sTemp = $oConfig->getOptions()->getVarDir() . DS . uniqid(time()) . $sFileType;
        copy($sFromFile, $sTemp);
        return $sTemp;
    }
    
    public function getLocalDir()
    {
        return Mage::getConfig()->getOptions()->getCodeDir().'/local/';
    }
    
    public function getEtcDir()
    {
        return Mage::getConfig()->getOptions()->getEtcDir().'/modules';
    }
    
    public function getBaseDir()
    {
        return Mage::getConfig()->getOptions()->getBaseDir().DS;
    }
    
    public function getInstallDir()
    {
        return $this->tool()->platform()->getInstallDir();
    }
    
    public function getAitsysDir()
    {
        return $this->getAitocModulesDir().'Aitsys';
    }
    
    public function mkDir($sPath)
    {
        return $this->makeDirStructure($sPath,false);
    }
    
    public function makeDirsDiff($sOrigDir, $sChangedDir, $sPatchFilePath)
    {
        $sCmd = 'diff -aurBb ' . $sOrigDir . ' ' . $sChangedDir . ' > ' . $sPatchFilePath;
        exec($sCmd);
        $this->grantAll($sPatchFilePath);
        return $this;
    }
    
    public function grantAll( $path , $recursive = true, $all = false )
    {
        if(file_exists($path))
        {
            if($all)
            {
                @chmod($path, self::PERM_ALL);
            }
            else
            {
                if(is_dir($path))
                {
                    @chmod($path, self::PERM_DIR);
                }
                else
                {
                    @chmod($path, self::PERM_FILE);
                }
            }
            
            if ($recursive = is_dir($path))
            {
                $items = new RecursiveDirectoryIterator($path);
                foreach ($items as $item)
                {
                    if(in_array(basename($item), $this->getForbiddenDirs()))
                    {
                        continue;
                    }
                    $this->grantAll((string)$item, false, $all);
                }
            }
        }
        return $this;
    }
    
    /**
     * Removes file
     *
     * @param string $sPath
     */
    public function rmFile($sPath)
    {
        if (file_exists($sPath) && $this->isWriteable($sPath))
        {
            if (is_file($sPath))
            {
                @unlink($sPath);
            }
            else
            {
                @rmdir($sPath);
            }
            return !file_exists($sPath);
        }
    }
    
    public function moveFile($source, $destination) 
    {
        $this->cpFile($source,$destination);
        return $this->rmFile($source);
    }
    
    /**
     * Copy file. Makes directory structure if not exists.
     *
     * @param string $sSource
     * @param string $sDestination
     */
    public function cpFile($sSource, $sDestination,$exc=false)
    {
        $this->makeDirStructure($sDestination);
        $res = @copy($sSource, $sDestination);
        if(false ===  $res && $exc)
        {
            $msg = "Can't copy ".$sSource." to ".$sDestination;
            if (file_exists($sDestination) && !$this->isWriteable($sDestination,true))
            {
                $msg .= ' - desitnation path is not writeable';
            }
            $msg .= '.';
            throw new Aitoc_Aitsys_Model_Aitfilesystem_Exception($msg);
        }
        else
        {
            $this->grantAll($sDestination);
        }
        return $res;
    }
    
    /**
     * Creates directories, file and puts the content into a file and grants this file some permissions
     * 
     * @param string $filePath Path to the file where to write the data.
     * @param mixed $fileContent The data to write. Can be either a string, an array or a stream resource.
     * @param integer $permissions Permissions to be granted to the file
     * 
     * @return boolean
     */
    public function putFile($filePath, $fileContent, $permissions = null)
    {
        if(!$permissions)
        {
            $mode = $this->getPermissionsMode();
            $permissions = ($mode == self::MODE_ALL) ? self::PERM_ALL : self::PERM_FILE;
        }
    
        $this->makeDirStructure($filePath, true);
        
        $chmod = !file_exists($filePath);
        $result = file_put_contents($filePath, $fileContent);
        if($chmod)
        {
            @chmod($filePath, $permissions);
        }
        return is_int($result);
    }
    
    /**
     * @return string
     */
    public function getPermissionsMode()
    {
        if(!$this->_permissionsMode)
        {
            $this->_permissionsMode = $this->tool()->db()->getConfigValue('aitsys_write_permissions', self::MODE_NORMAL);
        }
        return $this->_permissionsMode;
    }
    
    public function setPermissionsMode($mode)
    {
        $this->_permissionsMode = $mode;
        return $this;
    }

    /**
     * @return array
     */
    public function checkMainPermissions()
    {
        if (is_null($this->_closedMainFolders))
        {
            $this->_closedMainFolders  = array();
            $foldersToCheck = array();
            $subsystemsDirs = $this->getSubsystemsDirs();
            $modulesDirs    = array(); // deprecated since 2.19.0 $this->getAitocModulesDirs();
            
            $foldersToCheck[] = BP . DS . 'var' . DS;
            $foldersToCheck = array_merge($foldersToCheck, $subsystemsDirs, $modulesDirs);
    
            foreach($foldersToCheck as $i => $folder)
            {
                if (file_exists($folder) && is_dir($folder) && !$this->isWriteable($folder, false, (bool)$i))
                {
                    $this->_closedMainFolders[] = $folder;
                }
            }
        }
        return $this->_closedMainFolders;
    }
    
    /**
     * @return array
     */
    public function getSubsystemsDirs()
    {
        if(empty($this->_subsystemsDirs))
        {
            $this->_subsystemsDirs[] = BP . Aitoc_Aitsys_Model_Rewriter_Abstract::REWRITE_CACHE_DIR;
            $this->_subsystemsDirs[] = BP . DS . 'var' . DS . Aitoc_Aitsys_Model_Platform::INSTALLATION_DIR . DS;
            $this->_subsystemsDirs[] = BP . DS . 'var' . DS . Aitoc_Aitsys_Model_Aitpatch::PATCH_DIR . DS;
        }

        return $this->_subsystemsDirs;
    }
    
    public function permissonsChange($mode)
    {
        $all = ($mode === self::MODE_ALL);
        $folders = $this->getSubsystemsDirs();
        
        foreach ($folders as $folder)
        {
            $this->grantAll($folder, true, $all);
        }
        
        $this->setPermissionsMode($mode);
        Mage::getConfig()->saveConfig('aitsys_write_permissions', $mode);
        Mage::app()->cleanCache();
    }
    
    /**
     * Makes directory structure and sets permissions
     *
     * @param string $sPath
     */
    public function makeDirStructure( $path , $isFile = true )
    {
        $path = str_replace('\\','/',$path);
        $basePath = str_replace('\\','/',dirname(Mage::getRoot())).'/';
        $pathItems = explode('/',substr($path,strlen($basePath)));
        
        $all = $this->getPermissionsMode()==self::MODE_ALL;
        
        if ($isFile)
        {
            array_pop($pathItems);
        }
        foreach ($pathItems as $dir)
        {
            if ($dir === '') 
            {
                continue;
            }
            $basePath .= $dir.'/';
            if (!@file_exists($basePath) or (@file_exists($basePath) and !@is_dir($basePath)))
            {
                @mkdir($basePath, self::PERM_DIR);
                $this->grantAll($basePath, true, $all);
            }
        }
        return $this;
    }
    
    public function getAitocModulesDir()
    {
        if (!$this->hasData('aitoc_modules_dir'))
        {
            $dir = $this->getLocalDir().'Aitoc/';
            $this->setAitocModulesDir($dir);
        }
        return $this->getData('aitoc_modules_dir');
    }
    
    public function getAitocModulesDirs()
    {
        if (!$this->hasData('aitoc_modules_dirs'))
        {
            $result = array();
            $base = $this->getLocalDir();
            foreach ($this->tool()->platform()->getModuleDirs() as $dir)
            {
                $result[] = $base.$dir.DS;
            }
            $this->setData('aitoc_modules_dirs',$result);
        }
        return $this->getData('aitoc_modules_dirs');
    }
    
    public function checkWriteable( $path , $exception = false )
    {
        if (!$path) return false;

        if (!$this->isWriteable($path) && !$this->tool()->isPhpCli())
        {
            if ($exception)
            {
                $this->_exception($path);
            }
            return false;
        }
        return true;
    }
    
    protected function _exception( $msg )
    {
        throw new Aitoc_Aitsys_Model_Aitfilesystem_Exception($msg);
    }
    
    public function isWriteable($sPath, $bCheckParentDirIfNotExists = true, $recursive = false)
    {
        clearstatcache();
        if (file_exists($sPath))
        {
            if(is_file($sPath))
            {
                return $this->isFileWritable($sPath);
            }
            else
            {
                $return = $this->isDirWritable($sPath);
                if($return && $recursive)
                {
                    $items = new RecursiveDirectoryIterator($sPath);
                    foreach ($items as $item)
                    {
                        if(in_array(basename($item), $this->getForbiddenDirs()))
                        {
                            continue;
                        }
                        if(!$this->isWriteable((string)$item, false, true))
                        {
                            return false;
                        }
                    }
                }
                
                return $return;
            }
        }
        else
        {
            if (!$bCheckParentDirIfNotExists)
            {
                return false;
            }
            $sDirname = dirname($sPath);
            while (strlen($sDirname) > 0 AND !file_exists($sDirname))
            {
                $sDirname = dirname($sDirname);
            }
            return $this->isDirWritable($sDirname);
        }
        return false;
    }
    
    public function isFileWritable($sPath)
    {
        if (!$sPath)
        {
            return false;
        }
        if (stristr(PHP_OS, "win"))
        {
            // trying to append
            $fp = @fopen($sPath, 'a+');
            if (!$fp)
            {
                return false;
            }
            fclose($fp);
            return true;
        } else 
        {
            return is_writable($sPath);
        }
    }
    
    public function isDirWritable($sPath)
    {
        if (!$sPath)
        {
            return false;
        }
        if ('/' != $sPath[strlen($sPath)-1])
        {
            $sPath .= DIRECTORY_SEPARATOR;
        }
        if (stristr(PHP_OS, "win"))
        {
            /**
             * Trying to create a new file
             */
            $sFilename = uniqid(time());
            $fp = @fopen($sPath . $sFilename, 'w');
            if (!$fp) 
            {
                return false;
            }
            if (!@fwrite($fp, 'test'))
            {
                return false;
            }
            fclose($fp);
            /**
             * clean up after ourselves
             */
            unlink($sPath . $sFilename);
            return true;
        } else 
        {
            return is_writable($sPath);
        }
    }
    
    public function emptyDir($dirname = null)
    {
        if(!is_null($dirname)) {
            if (is_dir($dirname)) {
                if ($handle = @opendir($dirname)) {
                    while (($file = readdir($handle)) !== false) {
                        if ($file != "." && $file != "..") {
                            $fullpath = $dirname . '/' . $file;
                            if (is_dir($fullpath)) {
                                $this->emptyDir($fullpath);
                                @rmdir($fullpath);
                            }
                            else {
                                @unlink($fullpath);
                            }
                        }
                    }
                    closedir($handle);
                }
            }
        }
    }

	/**
	 * Gets all the subdirectories off the $directoryPath
	 *
	 * @param string $directoryPath
	 * @return array
	 */
	public function getSubDir($directoryPath)
	{
		if (!is_dir($directoryPath))
		{
			return array();
		}

		$directories = array();
        $directoryPath = rtrim($directoryPath,'/\\').'/';
		$dir = dir($directoryPath);

		while (false !== ($file = $dir->read()))
		{
			if (!in_array($file, $this->getForbiddenDirs()) && is_dir($directoryPath.$file))
			{
				$directories[] = $file;
			}
		}

		$dir->close();

		return $directories;
	}

	/**
	 * Searches for most suitable patch file in the directory ("PATH_TO_MODULE/data/" as a rule)
	 *
	 * @param string $fileName
	 * @param string $directory
	 * @return Varien_Object 
	 */
	public function getPatchFilePath($fileName, $directory)
    {
		$data = array();

        if (!$fileName || !$directory)
		{			
            $data['is_error'] = true;
			$data['file_path'] = __('Unknown');
            return new Varien_Object($data);
		}
        
        $directory = rtrim($directory,'/\\').'/';

		$lastSuccessIndex = null;	

		$subDirectories = $this->getSubDir($directory);        

		if ($subDirectories)
		{
			uasort($subDirectories, array($this, 'sortSubdirectories'));

			// Array bounds added for convenience
			array_unshift($subDirectories, 0);
			array_push($subDirectories, 100000);

			for ($i = 0; $i < count($subDirectories); $i++)
			{
				$result = version_compare($subDirectories[$i], Mage::getVersion());
				$currentFile = $directory . $subDirectories[$i] . DIRECTORY_SEPARATOR . $fileName;

				if (0 == $result)
				{
					if (is_file($currentFile))
					{
						$lastSuccessIndex = $i;
						break;
					}
				}
				elseif ((-1) == $result)
				{
					if (is_file($currentFile))
					{
						$lastSuccessIndex = $i;
					}
				}
				elseif (1 == $result)
				{
					if (is_null($lastSuccessIndex) && is_file($currentFile))
					{
						$lastSuccessIndex = $i;
						break;
					}
				}
			}
		}
        elseif (is_file($directory . $fileName))
        {            
            $data['is_error'] = false;
            $data['file_path'] = $directory . $fileName;
            return new Varien_Object($data);
        }

		if (is_null($lastSuccessIndex))
		{
			$data['is_error'] = true;
			$data['file_path'] = $directory . $fileName;
		}
		else
		{
			$data['is_error'] = false;
			$data['file_path'] = $directory . $subDirectories[$lastSuccessIndex] . DIRECTORY_SEPARATOR . $fileName;
		}

		return new Varien_Object($data);
	}

	/**
	 * Wrapper for version_compare
	 *
	 * @param string $directoryA
	 * @param string $directoryB
	 * @return mixed
	 */

	public function sortSubdirectories($directoryA, $directoryB)
	{
		return version_compare($directoryA, $directoryB);
	}
	
	/**
	 * @return array
	 */
	public function getForbiddenDirs()
	{
	   return $this->_forbiddenDirs;
	}
}
