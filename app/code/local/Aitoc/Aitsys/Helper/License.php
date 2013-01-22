<?php

/**
 * !! Make an instance of this Helper class using the following construction
 * Aitoc_Aitsys_Abstract_Service::get()->getLicenseHelper()
 * 
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Helper_License extends Aitoc_Aitsys_Helper_Data
{
    
    protected $_actions = array();
    
    /**
     * 
     * @var Aitoc_Aitsys_Model_Module_License
     */
    protected $_license;
    
    protected $_statusLabels = array(
        'uninstalled' => 'License is uninstalled' ,
        'installed' => 'License is installed'
    );
    
    protected $_manageTitles = array(
        'uninstalled' => 'Install `%s` license'
    );
    
    public function getRulesSignature()
    {
        $s = 'used / licensed (total on site)';
        return $s; 
    }
    
    public function getRuleTitle($ruleCode , Aitoc_Aitsys_Model_Module_License $license = null , $isUpgrade = false)
    {
        if ($license)
        {
            $info = $this->getRulesInfo($license,$isUpgrade);
            if (isset($info[$ruleCode]['label']) && $info[$ruleCode]['label'])
            {
                return $info[$ruleCode]['label'];
            }
        }
        switch($ruleCode)
        {
            case 'admin':
                return $this->__('Administrator accounts quantity');
                break;
            case 'product':
                return $this->__('Products quantity');
                break;
            case 'store':
                return $this->__('Stores quantity');
                break;
            default:
                return $this->__('Unknown Rule');
        }
    }
    
    public function getRulesInfo(Aitoc_Aitsys_Model_Module_License $license, $isUpgrade = false)
    {
        $rulesInfo = array();
        $constrain = $isUpgrade ? $license->getUpgrade()->getConstraint(): $license->getConstrain();
        if ($constrain)
        {
            $useNumber = array();
            if ($license && ($performer = $license->getPerformer()))
            {
                $rules = $performer->getRuler()->getRules();
                foreach ($rules as $ruleType => $rule) 
                {
                    $useNumber[$ruleType] = $performer->getRuler()->getRuleUsedUnits($ruleType);
                }
            }
            foreach ($constrain as $key => $value)
            {
                $label = $value['label'];
                $value = $value['value'];
                if (null === $value)
                {
                    $value = $this->__('Unlimited');
                }
                $useCount = isset($useNumber[$key]) ? $useNumber[$key] : 0;
                $totalCount = $this->getRuleTotalCount($key);
                if ($performer)
                    $totalCount = $performer->getRuler()->getTotalCountByRule($key);
                    
                if (!$license->getInstall()->isInstalled())
                {
                    $useCount = 0;
                }
                $rulesInfo[$key] = array(
                    'used'     => $useCount,
                    'licensed' => $value,
                    'total'    => $totalCount,
                    'label'    => $label
                );
            }
        }
        return $rulesInfo;
    }
        
    public function getMainActions( Aitoc_Aitsys_Model_Module_License $license )
    {
        $this->_license = $license;
        $this->_actions = array();
        $this->_addInstallAction();
        $this->_addReInstallAction();
        $this->_addManualInstallAction();
        return $this->_actions;
    }
    
    public function getManageActions( Aitoc_Aitsys_Model_Module_License $license , $info = array() )
    {
        $adminhtml = $this->getAdminhtmlHelper();
        $actions = array();
        switch (true)
        {
            case $license->isUninstalled() && (isset($info['confirmed']) && $info['confirmed']):
                $url = $adminhtml->getUrl('aitsys/license/install',array('modulekey' => $license->getModule()->getKey()));
                
                $actions['back'] = array(
            		'label'     => $this->__('Cancel'),
            		'onclick'   => 'setLocation(\''.$adminhtml->getUrl('aitsys').'\')',
            		'class'     => 'back'
                );
                
                $actions['install'] = array(
                    'label' => $this->__('Proceed to install') ,
                    'id' => 'proceed_to_install' ,
                    'onclick'   => 'editForm.submit(\''.$url.'\');'
                );
                
                if($license instanceof Aitoc_Aitsys_Model_Module_License_Light && !$license->getPerformer())
                {
                    $actions['install']['disabled'] = true;
                }
                
                break;
            case $license->isInstalled():
                $actions['back'] = array(
            		'label'     => $this->__('Back'),
            		'onclick'   => 'setLocation(\''.$adminhtml->getUrl('aitsys').'\')',
            		'class'     => 'back'
                );
        
                
                if ($license->getUpgrade()->hasUpgrade())
                {
                    $upgradeUrl = $adminhtml->getUrl('aitsys/license/upgrade',array('modulekey' => $license->getModule()->getKey()));
                    $actions['upgrade'] = array(
                		'label'     => $this->__('Upgrade license'),
                		'onclick'   => "deleteConfirm('".$this->__('Are you sure you want to upgrade module license?')."','".$upgradeUrl."')",
                        'disabled' => !$license->getUpgrade()->canUpgrade() ,
                        'class' => 'save '.($license->getUpgrade()->canUpgrade() ? '' : 'disabled')
                    );
                }
                
                $deleteUrl = $adminhtml->getUrl('aitsys/license/delete',array('modulekey' => $license->getModule()->getKey()));
                $actions['uninstall'] = array(
            		'label'     => $this->__('Uninstall module and license'),
            		'onclick'   => "deleteConfirm('".$this->__('Are you sure?')."','".$deleteUrl."')",
            		'class'		=> 'delete'
                );
                break;
            case $license->isUninstalled():
                
                $url = $adminhtml->getUrl('aitsys/license/confirm',array('modulekey' => $license->getModule()->getKey()));
                
                $actions['back'] = array(
            		'label'     => $this->__('Do not Agree and Cancel'),
            		'onclick'   => 'setLocation(\''.$adminhtml->getUrl('aitsys').'\')',
            		'class'     => 'back'
                );
                
                $actions['install'] = array(
                    'label' => $this->__('Confirm agreement and install') ,
                    'id' => 'confirm_and_install' ,
                    'onclick'   => 'editForm.submit(\''.$url.'\');'
                );
                break;
        }
        return $actions;
    }
    
    public function getAgreements( Aitoc_Aitsys_Model_Module_License $license )
    {
        $path = $this->tool()->platform()->getInstallDir();
        $path .= $license->getKey().'.phtml';
        if (file_exists($path))
        {
            ob_start();
            include $path;
            return ob_get_clean();
        }
        return Mage::app()->getLayout()->createBlock('core/template','license_agreements',array(
            'template' => 'aitsys/license.phtml'
        ))->toHtml();
    }
    
    public function getStatusLabel( Aitoc_Aitsys_Model_Module_License $license )
    {
        $status = $license->getStatus();
        $status = isset($this->_statusLabels[$status]) ? $this->_statusLabels[$status] : $status;
        return $this->__($status);
    }
    
    public function getManageTitle( Aitoc_Aitsys_Model_Module_License $license )
    {
        $status = $license->getStatus();
        $msg = isset($this->_manageTitles[$status]) ? $this->_manageTitles[$status] : 'Manage `%s` license'; 
        return $this->__($msg,$license->getModule()->getLabel());
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Helper_License
     */
    protected function _addInstallAction()
    {
        if ($this->_license->isUninstalled())
        {
            $this->_actions[] = array(
            	'url' => array( 
            		'path' => 'aitsys/license/manage' ,
                    'args' => array('modulekey' => $this->_license->getKey()) 
                ) ,
                'title' => $this->__('Install')
            );
        }
        elseif ($this->_license->isInstalled())
        {
            $this->_actions[] = array(
                'url' => array(
                    'path' => 'aitsys/license/manage' ,
                    'args' => array('modulekey' => $this->_license->getKey())
                ) ,
                'title' => $this->__('Manage')
            );
        }
        return $this;
    }

    /**
     * 
     * @return Aitoc_Aitsys_Helper_License
     */
    protected function _addReInstallAction()
    {
        if ($this->_license->isInstalled())
        {
            $this->_actions[] = array(
                'url' => array(
                    'path' => 'aitsys/license/reInstall' ,
                    'args' => array('modulekey' => $this->_license->getKey())
                ) ,
                'title' => $this->__('Re-Install')
            );
        }
        return $this;
    }

    /**
     * 
     * @return Aitoc_Aitsys_Helper_License
     */
    protected function _addManualInstallAction()
    {
        $this->_actions[] = array(
        	'url' => array( 
        		'path' => 'aitsys/license/manualInstall' ,
                'args' => array('modulekey' => $this->_license->getKey()) 
            ) ,
            'title' => $this->__('Manual Install')
        );
        return $this;
    }    
    
    
    public function getValidationValue($key)
    {
        $model = Mage::getModel('core/config_data')->load($key, 'path');
        return $model->getValue();
    }
    
    public function getRuleTotalCount($ruleCode)
    {
        return $this->tool()->getTotalCountByRule($ruleCode);
    }
    
    public function uninstallBefore(){}
	public function installBefore(){}
}
