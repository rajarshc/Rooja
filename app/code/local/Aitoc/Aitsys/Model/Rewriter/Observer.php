<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 * @author Andrei
 */
class Aitoc_Aitsys_Model_Rewriter_Observer
{
    public function init($observer)
    {
        if (!Mage::registry('aitsys_autoload_initialized'))
        {
            Aitoc_Aitsys_Model_Rewriter_Autoload::register();
            Mage::register('aitsys_autoload_initialized', true);
        }
        elseif (Mage::registry('aitsys_autoload_initialized_base'))
        {
            $rewriter = new Aitoc_Aitsys_Model_Rewriter();
            $rewriter->preRegisterAutoloader();
            Mage::unregister('aitsys_autoload_initialized_base');
        }
    }
    
    public function clearCache($observer)
    {
        // this part for flush magento cache
        $tags = $observer->getTags();
        $rewriter = new Aitoc_Aitsys_Model_Rewriter();
        if (null !== $tags)
        {
            if (empty($tags) || !is_array($tags) || in_array('aitsys', $tags))
            {
                $rewriter->prepare();
            }
        }
        
        // this part for mass refresh
        $cacheTypes = Mage::app()->getRequest()->getParam('types');
        if ($cacheTypes)
        {
            $cacheTypesArray = $cacheTypes;
            if (!is_array($cacheTypesArray))
            {
                $cacheTypesArray = array($cacheTypesArray);
            }
            if (in_array('aitsys', $cacheTypesArray)) 
            {
                $rewriter->prepare();
            }
        }
        
        // this part is for flush cache storage
        if (null === $cacheTypes && null === $tags)
        {
            $rewriter->prepare();
        }
    }
}