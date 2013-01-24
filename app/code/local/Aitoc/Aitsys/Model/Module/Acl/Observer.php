<?php
class Aitoc_Aitsys_Model_Module_Acl_Observer extends Aitoc_Aitsys_Abstract_Model
{
    
    protected $_error;
    
    public function performRolePrepareSave(Varien_Event_Observer $observer)
    {
        if(Mage::registry('aitsys_events_created'))
        {
            return $this;
        }
        $oRequest = $observer->getEvent()->getRequest();
        $role = $observer->getEvent()->getObject();
        $resource   = explode(',', $oRequest->getParam('resource', false));
        $roleUsers  = $oRequest->getParam('in_role_user', null);
        parse_str($roleUsers, $roleUsers);
        $roleUsers = array_keys($roleUsers);
        $isAll = (boolean)$oRequest->getParam('all');

        if (!$this->_checkSaveAllow($role, $roleUsers, $isAll, $resource))
        {
            // @TODO throw exception (remove exception from _checkSaveAllow() method )
        }
        
        return $this;
    }
    
    public function performUserBeforeSave(Varien_Event_Observer $observer)
    {
        if(Mage::registry('aitsys_events_created'))
        {
            return $this;
        }
        $user = $observer->getEvent()->getObject();
        /* @var $user Mage_Admin_Model_User*/
        
        $uRoles = (array)$user->getData('roles');
        if ( sizeof($uRoles) > 1 ) {
            //@FIXME: stupid fix of previous multi-roles logic.
            $uRoles = array_slice($uRoles, 0, 1);
        }
        if (!sizeof($uRoles))
            return $this;
        $roleId = (int)array_shift($uRoles);
        
        $role = Mage::getModel('admin/roles')->load($roleId);
        /* @var $role Mage_Admin_Model_Roles */
        
        $users = $role->getRoleUsers();
        if (!in_array($user->getId(), $users))
        {
            $users[] = $user->getId();
        }
        if (!$this->_checkSaveAllow($role, $users))
        {
            // @TODO throw exception (remove exception from _checkSaveAllow() method )
        }
        
        return $this;
    }
    
    /**
     * Method helps to perform verifications of segmentation rules by admin before enabling AITOC modules
     *
     * @param Varien_Event_Observer $observer
     */
    public function performModulesBeforeSave(Varien_Event_Observer $observer)
    {
        $data = $observer->getEvent()->getData('data');
        $aitsys = $observer->getEvent()->getData('aitsys');
        
        foreach ($data as $moduleKey => $available)
        {
            $module = $this->tool()->platform()->getModule($moduleKey);
            if (!$module || !$module->isLicensed())
            {
                continue;
            }
            if (!(!$module->getInstall()->isInstalled() && $available))
            {
                continue;
            }
            if (!$performer = $module->getLicense()->getPerformer())
            {
                continue;
            }
            $ruler = $performer->getRuler();
            if (!$ruler->checkRule('admin', null, 'all'))
            {
                $rule = $ruler->getRule('admin');
                $aitsys->addError($this->_aithelper()->__(
                    $this->_aithelper()->getErrorText('seg_config_admins_module_cant_enable'), 
                    $module->getLabel(), 
                    $rule['value'],
                    $this->_aithelper()->getModuleLicenseUpgradeLink($module,false)
                ));
            }
            if (!$ruler->checkRule('store', null, 'all'))
            {
                $rule = $ruler->getRule('store');
                $aitsys->addError($this->_aithelper()->__(
                    $this->_aithelper()->getErrorText('seg_exceed_limit'), 
                    $module->getLabel(), 
                    $rule['value'],
                    $ruler->getStoreCount(),
                    $this->_aithelper()->getModuleLicenseUpgradeLink($module,false)
                ));
            }
            if (!$ruler->checkRule('product', null, 'all'))
            {
                $rule = $module->getLicense()->getPerformer()->getRuler()->getRule('product');
                $aitsys->addError($this->_aithelper()->__(
                    $this->_aithelper()->getErrorText('seg_exceed_limit'), 
                    $module->getLabel(), 
                    $rule['value'],
                    $ruler->getProductCount(),
                    $this->_aithelper()->getModuleLicenseUpgradeLink($module,false)
                ));
            }
        }
        
        return $this;
    }
    
    protected function _checkSaveAllow($role, $roleUsers, $isAll=false, $roleResources = null)
    {
        if (is_null($roleResources))
        {
            $roleResources = Aitoc_Aitsys_Model_Module_Acl::getRoleResources($role);
            $isAll = false;
        }
        if ($isAll && !in_array('all', $roleResources))
        {
            $roleResources[] = 'all';
        }
        
        foreach ($this->tool()->platform()->getModules() as $module)
        {
            if (!$module->isLicensed() || !$module->getInstall()->isInstalled())
                continue;
            
            if (!$module->getLicense()->getPerformer()->getRuler()->isAllowGrantAdminRoleWithAccess($role, $roleUsers, $roleResources))
            {
                $rule = $module->getLicense()->getPerformer()->getRuler()->getRule('admin');
                Mage::throwException($this->_aithelper()->__(
                    $this->_aithelper()->getErrorText('seg_config_admins_exceed_limit'), 
                    $module->getLabel(), 
                    $rule['value'],
                    $this->_aithelper()->getModuleLicenseUpgradeLink($module,false)
                ));
                return false;
            }
        }
        return true;
    }
}