<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 * @author Andrei
 */
class Aitoc_Aitsys_Model_Rewriter extends Aitoc_Aitsys_Model_Rewriter_Abstract
{
    public function preRegisterAutoloader( $base = false )
    {
        $configFile = $this->_rewriteDir . 'config.php';
        /**
        * Will re-generate each time if config does not exist, or cache is disabled
        */
        if (!$this->tool()->isPhpCli())
        {
            if (!file_exists($configFile) || (!$base && !Mage::app()->useCache('aitsys') ))
            {
                $this->prepare();
            }
        }
    }
    
    public function prepare()
    {
        $merge = new Aitoc_Aitsys_Model_Rewriter_Merge();
        $rewriterConfig = new Aitoc_Aitsys_Model_Rewriter_Config();
        
        // first clearing current class rewrites
        Aitoc_Aitsys_Model_Rewriter_Autoload::instance()->clearConfig();
        $merge->clear();
        
        $conflict = new Aitoc_Aitsys_Model_Rewriter_Conflict();
        list($conflicts, $rewritesAbstract) = $conflict->getConflictList();
        
        /**
        * FOR NORMAL REWRITES
        */
        // will combine rewrites by alias groups
        if (!empty($conflicts))
        {
            foreach($conflicts as $groupType => $modules) {
                $groupType = substr($groupType, 0, -1);
                foreach($modules as $moduleName => $moduleRewrites) {
                    foreach($moduleRewrites['rewrite'] as $moduleClass => $rewriteClasses) 
                    {
                        /*
                         * $rewriteClasses is an array of class names for one rewrite alias
                         * for example:
                         * Array
                         *   (
                         *       [0] => AdjustWare_Deliverydate_Model_Rewrite_AdminhtmlSalesOrderCreate
                         *       [4] => Aitoc_Aitcheckoutfields_Model_Rewrite_AdminSalesOrderCreate
                         *       [10] => Aitoc_Aitorderedit_Model_Rewrite_AdminSalesOrderCreate
                         *   )
                         */
                        // building inheritance tree
                        $alias              = $moduleName . '/' . $moduleClass;
                        $classModel = new Aitoc_Aitsys_Model_Rewriter_Class();
                        $inheritanceModel = new Aitoc_Aitsys_Model_Rewriter_Inheritance();
                        $baseClass          = $classModel->getBaseClass($groupType, $alias);
                        $inheritedClasses   = $inheritanceModel->build($rewriteClasses, $baseClass);
                        
                        // don't create rewrites for excluded Magento base classes
                        if (in_array($baseClass, $this->tool()->db()->getConfigValue('aitsys_rewriter_exclude_classes', array()))) {
                            continue;
                        }
                        $mergedFilename = $merge->merge($inheritedClasses);
                        if ($mergedFilename)
                        {
                            $rewriterConfig->add($mergedFilename, $rewriteClasses);
                            $rewriterConfig->add(serialize($merge->getLatestMergedFiles()),'file:'.$mergedFilename);
                        }
                    }
                }
            }
        }
        
        /**
        * FOR ABSTRACT REWRITES (AITOC REALIZATION)
        */
        if (!empty($rewritesAbstract))
        {
            foreach($rewritesAbstract as $groupType => $modules) {
                $groupType = substr($groupType, 0, -1);
                foreach($modules as $moduleName => $moduleRewrites) {
                    foreach($moduleRewrites['rewriteabstract'] as $moduleClass => $rewriteClass) 
                    {
                        // building inheritance tree
                        $alias              = $moduleName . '/' . $moduleClass;
                        $classModel = new Aitoc_Aitsys_Model_Rewriter_Class();
                        $inheritanceModel = new Aitoc_Aitsys_Model_Rewriter_Inheritance();
                        $baseClass          = $classModel->getBaseClass($groupType, $alias);
                        $inheritedClasses   = $inheritanceModel->buildAbstract($rewriteClass, $baseClass);
                        $mergedFilename     = $merge->merge($inheritedClasses, true);
                        if ($mergedFilename)
                        {
                            $rewriterConfig->add($mergedFilename, array($rewriteClass,$baseClass));
                        }
                    }
                }
            }
        }
        
        $rewriterConfig->commit();
        
        Aitoc_Aitsys_Model_Rewriter_Autoload::instance()->setupConfig();
    }
}
