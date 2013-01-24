<?php
class Aitoc_Aitsys_Model_Module_Acl_Roles extends Mage_Admin_Model_Roles 
{
    static $result;
    protected function _buildResourcesArray(Varien_Simplexml_Element $resource=null, $parentName=null, $level=0, $represent2Darray=null, $rawNodes = false, $module = 'adminhtml')
    {
        if (is_null($resource)) {
            $resource = Mage::getConfig()->getNode('adminhtml/acl/resources');
            $resourceName = null;
            $level = -1;
        } else {
            $resourceName = $parentName;
            if ($resource->getName()!='title' && $resource->getName()!='sort_order' && $resource->getName() != 'children') {
                $resourceName = (is_null($parentName) ? '' : $parentName.'/').$resource->getName();

                //assigning module for its' children nodes
                if ($resource->getAttribute('module')) {
                    $module = (string)$resource->getAttribute('module');
                }

                if ($rawNodes) {
                    $resource->addAttribute("aclpath", $resourceName);
                    $resource->addAttribute("module_c", $module);
                }

                //if (!(string)$resource->title) {
                //   return array();
                //}

                //$resource->title = Mage::helper($module)->__((string)$resource->title);

                if ( is_null($represent2Darray) ) {
                    self::$result[$resourceName]['name']  = Mage::helper($module)->__((string)$resource->title);
                    self::$result[$resourceName]['level'] = $level;
                } else {
                    self::$result[] = $resourceName;
                }
            }
        }

        $children = $resource->children();
        if (empty($children)) {
            if ($rawNodes) {
                return $resource;
            } else {
                return self::$result;
            }
        }
        foreach ($children as $child) {
            $this->_buildResourcesArray($child, $resourceName, $level+1, $represent2Darray, $rawNodes, $module);
        }
        if ($rawNodes) {
            return $resource;
        } else {
            return self::$result;
        }
    }
    
}