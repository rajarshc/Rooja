<?php
class Aitoc_Aitsys_Model_Module_Acl extends Aitoc_Aitsys_Abstract_Model
{
    /**
     * 
     * @param $module
     * @return Aitoc_Aitsys_Model_Module
     */
    public function setModule( $module )
    {
        return $this->setData('module',$module);
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module
     */
    public function getModule()
    {
        return $this->getData('module');
    }
    
    /**
     *
     * @return Mage_Admin_Model_Acl
     */
    public function getAcl()
    {
        if (!$this->hasData('acl'))
        {
            $config = clone Mage::getConfig();
            $config->reinit();
            $config->setNode('adminhtml/acl/resources', '');
            $file = $config->getModuleDir('etc', $this->getModule()->getKey()).DS.'config.xml';
            $config->loadFile($file);
            $node = $config->getNode('adminhtml/acl/resources');
            if ($node === false)
                return false;
                
            $acl = Mage::getModel('admin/acl');
            /* @var $acl Mage_Admin_Model_Acl */
            Mage::getSingleton('admin/config')->loadAclResources($acl, $node);
            $this->setData('acl', $acl);
        }
        
        return $this->getData('acl');
    }
    
    /**
     * Check if module has 'All' acl configuration
     *
     * @return bool
     */
    public function hasAllAcl()
    {
        $acl = $this->getAcl();
        if (!$acl)
            return false;
        return $acl->has('all') || $acl->has('acl/admin');
    }
    
    /**
     * Check if module has acl resource
     *
     * @param string $resource resource name
     * @return bool 
     */
    public function hasAcl($resource)
    {
        $acl = $this->getAcl();
        if (!$acl)
            return false;
        if (!preg_match('#^acl/#', $resource))
        {
            $resource = 'acl/'.$resource;
        }
        return $acl->has($resource);
    }
    
    /**
     * Get role resources with "allow" permission
     *
     * @param Mage_Admin_Model_Roles $role
     * @return array allowed role resources
     */
    public static function getRoleResources($role)
    {
        $aclRolesModel = new Aitoc_Aitsys_Model_Module_Acl_Roles();
        $resources = $aclRolesModel->getResourcesList();
        $rules_set = Mage::getResourceModel('admin/rules_collection')->getByRoles($role->getId())->load();
        
        $selrids = array();

        foreach ($rules_set->getItems() as $item) {
            if (array_key_exists(strtolower($item->getResource_id()), $resources) && $item->getPermission() == 'allow') {
                array_push($selrids, $item->getResource_id());
            }
        }
        return $selrids;
    }
}