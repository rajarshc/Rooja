<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Module_Conflict extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Check extensions for rewrite conflicts');
    }

    public function getDescription() {
        return $this->__('Check extensions for rewrite conflicts');
    }

    protected function generateSummary() {

        $conflicts = $this->getRewriteConflicts();

        if (empty($conflicts)) {
            $this->addPass($this->__("No extension conflict found"));
        }

        foreach ($conflicts as $conflict) {
            $this->addWarning(" conflict -- " . print_r($conflict, true)); //TODO: fix translation and output
        }
    }

    /**
     * create an array with all config.xml files found in local and community
     * @return string[] 
     */
    protected function findConfigFiles() {
        $config_files = array();

        $targets = array(
            Mage::getStoreConfig('system/filesystem/code') . '/local',
            Mage::getStoreConfig('system/filesystem/code') . '/community'
        );

        foreach ($targets as $target) {
            if (version_compare(phpversion(), '5.2.11', '>='))
                $directory = new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
            else
                $directory = new RecursiveDirectoryIterator($target);
            $iterator = new RecursiveIteratorIterator($directory);
            $foundfile = new RegexIterator($iterator, '/config\.xml$/', RecursiveRegexIterator::GET_MATCH);
            foreach ($foundfile as $fullpath => $file) {
                $config_files[] = $fullpath;
            }
        }
        return $config_files;
    }

    /**
     * Return all rewrites for a config.xml
     *
     * @param unknown_type $configFilePath
     */
    public function getRewriteForFile($configFilePath, $results) {
        //load xml
        $xmlcontent = file_get_contents($configFilePath);
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlcontent);

        foreach ($domDocument->documentElement->getElementsByTagName('rewrite') as $markup) {
            //parse child nodes
            $moduleName = $markup->parentNode->tagName;
            if ($this->manageModule($moduleName)) {
                foreach ($markup->getElementsByTagName('*') as $childNode) {
                    //get information
                    $className = $childNode->tagName;
                    $rewriteClass = $childNode->nodeValue;

                    //add to result
                    $key = $moduleName . '/' . $className;
                    if (!isset($results[$key]))
                        $results[$key] = array();
                    $results[$key][] = $rewriteClass;
                }
            }
        }

        return $results;
    }

    /**
     * return all rewrites for a config.xml file
     *
     * @param string $configFilePath
     * @return string[] 
     */
    protected function findRewritesInConfigFiles($configs) {
        $results = array();

        foreach ($configs as $config) {
            $xml = simplexml_load_file($config);
            $model_rewrites = $xml->xpath('//global/model//rewrite');
            // $domDocument->documentElement->getElementsByTagName('rewrite');
            //parentNode->tagName
            //$xml->xpath('//global/model//rewrite');
            foreach ($model_rewrites as $model_rewrite) {
                foreach ($model_rewrite as $module => $rewrites) {
                    foreach ($rewrites as $rewrite) {
                        foreach ($rewrite as $global_module_class_alias => $rewrite_class) {

                            $global_module = (string) $module;
                            $global_module_class_alias = (string) $global_module_class_alias;
                            $rewrite_class = (string) $rewrite_class;

                            $class_name = $rewrite_class;
                            $class_file = 'error';
                            $class_parent_name = 'error';
                            $class_parent_file = 'error';
                            try {

                                //$init = new $class_name;
                                //$ro = new ReflectionObject($init);
                                // this attemped failes because the class file cant load because of cd
                                $ro = new ReflectionClass($class_name);
                                $ro_parent = $ro->getParentClass();

                                // let the magento system load the class this way it can do its magic
                                //$init = new $class_name();
                                //$ro_parent = new ReflectionObject($init);
                                //$ro_parent->getParentClass();
                                //$class_name = $ro->getName();
                                $class_file = $ro->getFileName();
                                $class_parent_name = $ro_parent->getName();
                                $class_parent_file = $ro_parent->getFileName();
                            } catch (Exception $ex) {
                                $class_parent_file = $ex->getMessage();
                            }

                            $key = "$global_module/$global_module_class_alias";
                            if (!isset($result[$key])) {
                                $results[$key] = array();
                            }

                            $results[$key][] = array(
                                'config_file' => $config,
                                'global_module' => $global_module,
                                'global_module_class_alias' => $global_module_class_alias,
                                'rewrite_class_name' => $class_name,
                                'rewrite_class_file' => $class_file,
                                'rewrite_class_parent_name' => $class_parent_name,
                                'rewrite_class_parent_file' => $class_parent_file,
                            );
                        }
                    }
                }
            }
        }

        return $results;
    }

    /**
     *
     * @return array( array( ... ) ) 
     */
    protected function getRewriteConflicts() {
        $conflicts = array();
        $rewrites = $this->findRewritesInConfigFiles($this->findConfigFiles());
        foreach ($rewrites as $rewrite_key => $module_rewrites) {

            // if there is more then one rewrite working on the same module and class
            if (count($module_rewrites) > 1) {
                foreach ($module_rewrites as $rewrite) {
                    $rewrites[] = $rewrite;
                }
                $conflicts[] = $rewrites;
            }
        }
        return $conflicts;
    }

}

?>