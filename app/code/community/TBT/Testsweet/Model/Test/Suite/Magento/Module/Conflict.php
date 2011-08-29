<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Module_Conflict extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Modules - Check rewrite conflicts');
    }

    public function getDescription() {
        return $this->__('Check modules for rewrite conflicts.');
    }

    protected function generateSummary() {

        $conflicts = $this->getRewriteConflicts();

        if (empty($conflicts)) {
            $this->addPass($this->__("No extension conflict found."));
        } else {
            $this->addWarning("Conflicts found");
        }

        foreach ($conflicts as $conflict) {
            if (strpos(print_r($conflict, true), 'TBT_') > 0) {
                $this->addFail("Rewrite conflict causes issues in the TBT namespace.");
            }
            $this->addNotice($this->__("Advanced conflict data") . "\n" . print_r($conflict, true));
            if (!empty($_REQUEST['debug'])) {
                if (count($conflict) == 2) {
                    $child = $conflict[0];
                    $parent = $conflict[1];
                    if ((0 === strpos($conflict[0]['rewrite_class_name'], 'TBT_')) xor !empty($_REQUEST['flip'])) {
                        $parent = $conflict[0];
                        $child = $conflict[1];
                    }
                    $string = '';
                    $string .= $this->__("Possible solution") . "\n";
                    $string .= "----------------------------------------------------------------------------------------------\n";
                    $string .= $this->__("1. backup and open: ") . "{$child['rewrite_class_file']} \n";
                    $string .= $this->__("2. change class extends\n");
                    $string .= $this->__("    |-from: ") . "class {$child['rewrite_class_name']} extends {$child['rewrite_class_parent_name']}\n";
                    $string .= $this->__("    |-to  : ") . "class {$child['rewrite_class_name']} extends {$parent['rewrite_class_name']}\n";
                    $string .= $this->__("3. backup and open: ") . "{$child['config_file']} \n";
                    $string .= $this->__("4. change rewrite: ") . "\n";
                    $string .= htmlentities($this->__("    |-from: ") . "<global><{$child['rewrite_type']}><{$child['module']}><rewrite><{$child['model']}>{$child['rewrite_class_name']}</{$child['model']}></rewrite>...\n");
                    $string .= htmlentities($this->__("    |-to  : ") . "<global><{$parent['rewrite_type']}><{$parent['class']['module_name']}><rewrite><{$parent['class']['module_path']}>{$child['rewrite_class_name']}</{$parent['class']['module_path']}></rewrite>...\n");
                    $string .= $this->__("5. backup and open: ") . "{$child['app_etc_modules_config_xml']}\n";
                    $string .= htmlentities($this->__("    |-add depends: ") . "<config><modules><depends><{$parent['class']['module_namespace']}_{$parent['class']['module_name']} /></depends>...") . "\n";
                    $string .= "----------------------------------------------------------------------------------------------\n";
                    //$string .= "SHELL:\n";
                    //$string .= "cp {$child['rewrite_class_file']} {$child['rewrite_class_file']}.org";
                    //$string .= "sed s/class {$child['rewrite_class_name']} extends {$child['rewrite_class_parent_name']}/class {$child['rewrite_class_name']} extends {$parent['rewrite_class_name']} {$child['rewrite_class_file']} > {$child['rewrite_class_file']}";
                    $this->addNotice($string);
                }
            }
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
            Mage::getStoreConfig('system/filesystem/code') . '/community',
        );

        foreach ($targets as $target) {
            if (!is_dir($target))
                continue;

            if (version_compare(phpversion(), '5.2.11', '>='))
                $directory = new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
            else
                $directory = new RecursiveDirectoryIterator($target);
            $iterator = new RecursiveIteratorIterator($directory);
            $foundfile = new RegexIterator($iterator, '/.*[\\/]config\.xml$/', RecursiveRegexIterator::GET_MATCH);
            foreach ($foundfile as $fullpath => $file) {
                if (false === strpos($fullpath, '.disable'))
                    $config_files[] = $fullpath;
            }
        }
        return $config_files;
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

            if (empty($xml)) {
                throw new Exception("Can't load config file:  $config");
            }

            // check app_etc_modules_config_xml file is active
            $etc_check = basename(realpath(dirname("$config")));
            if ($etc_check !== 'etc')
                continue;

            $module = basename(realpath(dirname("$config") . "/../"));
            $module_namespace = basename(realpath(dirname("$config") . "/../../"));
            $module_scope = basename(realpath(dirname("$config") . "/../../../"));

            $app_etc_modules_config_xml = Mage::getBaseDir('etc') . "/modules/{$module_namespace}_{$module}.xml";
            if (file_exists($app_etc_modules_config_xml)) {
                $app_etc_modules_config_xml_object = simplexml_load_file($app_etc_modules_config_xml);
                $active = $app_etc_modules_config_xml_object->xpath("/config/modules/{$module_namespace}_{$module}/active");
                $active = (string) $active[0];
                if ($active !== 'true' && $active !== '1') {
                    $this->addNotice("Module not active: {$module_namespace}_{$module} : Because active is set false : $app_etc_modules_config_xml, : Skip : $config");
                    continue;
                }
            } else {
                $this->addNotice("Module not active: {$module_namespace}_{$module} : Because file not found : $app_etc_modules_config_xml : Skip : $config");
                continue;
            }

            $rewrites = $xml->xpath('//global/models/*/rewrite/*');
            $rewrites = array_merge((array) $rewrites, (array) $xml->xpath('//global/blocks/*/rewrite/*'));

            foreach ($rewrites as $rewrite) {

                $path = $rewrite->xpath('parent::*/parent::*/parent::*'); // xpath '../' fails?
                $rewrite_type = (string) $path[0]->getName();
                $path = $rewrite->xpath('parent::*/parent::*');
                $module = (string) $path[0]->getName();
                $rewrite_obj = (string) $rewrite->getName();
                $rewrite_class = (string) trim($rewrite);

                $class_name = $rewrite_class;
                $class_file = 'error';
                $class_parent_name = 'error';
                $class_parent_file = 'error';
                try {
                    $ro = new ReflectionClass($class_name);
                    $ro_parent = $ro->getParentClass();
                    $class_file = $ro->getFileName();
                    $class_parent_name = 'null';
                    $class_parent_file = 'null';
                    if (null != $ro_parent) {
                        $class_parent_name = $ro_parent->getName();
                        $class_parent_file = $ro_parent->getFileName();
                    }
                } catch (Exception $ex) {
                    $class_parent_file = $ex->getMessage();
                }

                $key = "{$rewrite_type}/{$module}/{$rewrite_obj}";
                if (!isset($results[$key])) {
                    $results[$key] = array();
                }

                $parts = preg_split('[_]', $class_name);
                $class = array(
                    'module_namespace' => $parts[0],
                    'module_name' => $parts[1],
                    'module_type' => $parts[2],
                );
                $module_path = '';
                for ($index = 3; $index < count($parts); $index++) {
                    $module_path .= strtolower($parts[$index]);
                    if ($index + 1 < count($parts)) {
                        $module_path .= '_';
                    }
                }
                $class['module_path'] = $module_path;

                $results[$key][] = array(
                    '' => $rewrite_type,
                    'config_file' => $config,
                    'module' => $module,
                    'model' => $rewrite_obj,
                    'class' => $class,
                    'rewrite_class_name' => $class_name,
                    'rewrite_class_file' => $class_file,
                    'rewrite_class_parent_name' => $class_parent_name,
                    'rewrite_class_parent_file' => $class_parent_file,
                    'app_etc_modules_config_xml' => $app_etc_modules_config_xml,
                );
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
        foreach ($rewrites as $rewrite_key => $model_rewrites) {
            // if there is more than one rewrite working on the same module/model
            if (count($model_rewrites) > 1) {
                $conflicts[] = $model_rewrites;
            }
        }
        return $conflicts;
    }

}

?>