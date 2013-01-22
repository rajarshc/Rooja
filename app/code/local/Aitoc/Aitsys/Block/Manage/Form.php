<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Block_Manage_Form extends Mage_Adminhtml_Block_Widget_Form
implements Aitoc_Aitsys_Abstract_Model_Interface
{
	/**
     * Render block
     *
     * @return string
     */
    public function renderView()
    {
        return $this->getFormHtml();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function tool()
    {
        return Aitoc_Aitsys_Abstract_Service::get();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_License
     */
    public function getLicense()
    {
        return $this->getModule()->getLicense();
    }
    
    public function isTestMode()
    {
        return $this->tool()->platform()->isTestMode();
    }
    
    public function isModePresetted()
    {
        return $this->tool()->platform()->isModePresetted();
    }
    
    protected function _addManageFields()
    {
        $license = $this->getLicense();
        $form = $this->getForm();
        
        if(!$license->isInstalled()) {
            $helper = $this->helper('aitsys');

            $note = false;
            if( $helper->isMysqlTimeoutValueLow() ) {
                $note = '<small class="rule-param-remove aitoc-note-message">'
                        . $this->tool()->getHelper('Strings')->getString('INSTALL_MYSQL_TIMEOUT_NOTIFICATION', true, $helper->getMysqlTimeoutValue()) . '</small>';
            }
            if( $helper->isTestConnectPassed() === false )
            {
                $note = '<small class="rule-param-remove aitoc-note-message">'
                        . $this->tool()->getHelper('Strings')->getString('INSTALL_TEST_CONNECTION_FAILED', true, $this->tool()->getApiUrl() ) . '</small>';
            }
            if($note) {
                $uncompatibleFieldset = $form->addFieldset('sql_timeout', array(
                    'legend' => $this->__('Warning') ,
                    'class' => 'license'
                ));
                $uncompatibleFieldset->addField('install_warning', 'note', array(
                    'text' => $note
                ));
            }
        }

        if (Mage::registry('aitsys_uncompatible_list'))
        {
            $domainsList = Mage::registry('aitsys_uncompatible_list');
            $uncompatibleFieldset = $form->addFieldset('uncompatible', array(
                'legend' => $this->__('Licence Incompatibility') ,
                'class' => 'license'
            ));
            $note = '<small class="rule-param-remove aitoc-note-message">' 
                    . $this->__('Licence install attempt on different website! In order to install license on your current website, it should be uninstalled from others.') . '</small>';
            $uncompatibleFieldset->addField('uncompatible_note', 'note', array(
                'text' => $note
            ));
            
            $uncompatibleFieldset->addField('uncompatible_note2', 'note', array(
                'text' => $this->__('You can uninstall license manually, as well as agree to uninstall it automatically.')
            ));
            
            $uncompatibleDomains = '';
            if (is_array($domainsList))
            {
                foreach ($domainsList as $domain)
                {
                    $uncompatibleDomains .= $domain . '<br />';
                }
            }
            $uncompatibleFieldset->addField('uncompatible_list', 'note', array(
                'label' => $this->__('License is already installed on') ,
                'text' => '<h3>'.$uncompatibleDomains.'</h3>'
            ));
            
            $uncompatibleFieldset->addField('uncompatible_confirm_uninstall', 'checkbox', array(
                'label'     => $this->__('Confirm automatic uninstall') ,
                'required'  => true,
                'value'     => 1,
                'name'      => 'uncompatible_confirm_uninstall',
                'note'      => $this->__('Yes, I agree to automatically uninstall license from another website')
            ));
        }
        
        $quickFieldset = $form->addFieldset('links',array(
            'legend' => $this->__('Aitoc - Quick access') ,
            'class' => 'license'
        ));
        
        $links = array();
        
        if ($licenseId = $license->getLicenseId())
        {
            $links[] = $this->tool()->getHelper()->getModuleLicenseUpgradeLink($license->getModule(),false); 
            $links[] = $this->tool()->getHelper()->getModuleSupportLink($license->getModule(),false);
        }
        
        if ($links)
        {
            $quickFieldset->addField('quick_links','note',array(
                'text' => join('&nbsp;&nbsp;&nbsp;',$links)
            ));
        }
        
        $paramsFieldset = $form->addFieldset('params',array(
            'legend' => $this->__('License parameters') ,
            'class' => 'license-params'
        ));
        
        $paramsFieldset->addField('version','note',array(
            'label' => $this->__('Module version') ,
            'text' => '<h3>'.$license->getModule()->getVersion().'</h3>'
        ));
        
        $paramsFieldset->addField('purchaseid','note',array(
            'label' => $this->__('Serial number') ,
            'text' => '<h3>'.$license->getPurchaseId().'</h3>'
        ));
        
        if($license->getPlatform()->getPlatformId()) {
            $paramsFieldset->addField('platformid','note',array(
                'label' => $this->__('Platform ID') ,
                'text' => '<h3>'.$license->getPlatform()->getPlatformId().'</h3>'
            ));
        }
        
        $connectionKey = $license->getConnectionKey();
        $note = null;
        if (!$connectionKey)
        {
            $note = '<small class="rule-param-remove aitoc-note-message">'.
            $this->__('Please click `Proceed to install` button at the top to finish the installation process.').
            '</small>';
        }
        $connectionKey = $connectionKey ? $connectionKey : $this->__('Not installed');
        $paramsFieldset->addField('connectionkey','note',array(
            'label' => $this->__('Connection key') ,
            'text' => '<h3>'.$connectionKey.'</h3>'
        ));
        
        if ($text = $this->_getLicenseParametersHtml())
        {
            $paramsFieldset->addField('constrain','note',array(
                'label' => $this->__('License parameters') ,
                'text' => $text,
            ));
        }
        
        if ($note)
        {
            $paramsFieldset->addField('install_note','note',array(
                'text' => $note
            ));
        }
        
        if ($license->getUpgrade()->hasUpgrade())
        {
            $upgradeFieldset = $form->addFieldset('upgrade',array(
                'legend' => $this->__('License upgrade') ,
                'class' => 'license-params'
            ));
            if($license->getUpgrade()->getPurchaseId()) {
                $upgradeFieldset->addField('new_purchaseid','note',array(
                    'label' => $this->__('Serial number') ,
                    'text' => '<h3>'.$license->getUpgrade()->getPurchaseId().'</h3>' ,
                ));
            }
            
            $text = $this->_getLicenseParametersHtml(true);
            $upgradeFieldset->addField('new_constrain','note',array(
                'label' => $this->__('New license parameters') ,
                'text' => $text,
            ));
            
            if ($license->getUpgrade()->canUpgrade())
            {
                $note = '<small class="rule-param-remove aitoc-note-message">'.$this->__('Please click `Upgrade license` button at the top to finish the upgrade process.').'</small>';
            }
            else
            {
                $note = '<small class="rule-param-remove aitoc-note-message">'.$this->__('Can`t upgrade to this license!').'</small>';
            }
            
            $upgradeFieldset->addField('upgrade_note','note',array(
                'text' => $note
            ));
        }
    }
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $license = $this->getLicense();
        $helper = $this->getLicenseHelper();
        $request = $this->getRequest();
        switch (true)
        {
            case $license->isUninstalled() && $request->getParam('confirmed'):
                $this->_addManageFields();
                break;
            case $license->isInstalled():
                $statusFieldset = $form->addFieldset('info',array(
                    'legend' => $this->__('License info') ,
                    'class' => 'license-status'
                ));
                $statusFieldset->addField('status','note',array(
                    'label' => $this->__('License status') ,
                    'text' => '<h3>'.$helper->getStatusLabel($license).'</h3>'
                ));
                $this->_addManageFields();
                break;
            case $license->isUninstalled() && !$request->getParam('confirmed'):
                $agreements = $helper->getAgreements($license);
                
                $fieldset = $form->addFieldset('agreements',array(
                    'legend' => $this->__('Please read License Agreement and click the Confirm agreement and install button.') ,
                    'class' => 'agreements'
                ));
                $fieldset->addField('license','note',array(
                    'text' => '<div class="agreements_frame">'.$agreements.'</div>'
                ));
                
                if (!$this->isModePresetted())
                {
                    $fieldset = $form->addFieldset('main',array());
                    $fieldset->addField('installation_type','radios',array(
                        'name' => 'installation_type' ,
                        'values' => array(
                            array( 
                            	'label' => $this->__('Agree and install on <b>TEST</b> host') ,
                            	'value' => 'test'
                            ) ,
                            array(
                                'label' => $this->__('Agree and install on <b>LIVE</b> host') ,
                                'value' => 'live'
                            )
                        )
                    ))->setSeparator('&nbsp;<a href="#" onclick="return false;">?</a>&nbsp;');
                    
                    $fieldset->addField('type_note','note',array(
                        'text' => '<div id="instal_type_notes">'.
                        $this->_getNote().
                        '</div>'
                    ));
                }
                break;
        }
        $form->addField('form_key','hidden',array(
            'name' => 'form_key' ,
            'value' => $this->getFormKey()
        ));
        return parent::_prepareForm();
    }
    
    protected function _getNote()
    {
        $msg = <<<NOTEMSG
<div id="test_notice">
{$this->__("If you choose TEST You will be able to install the Module only on ONE test Magento software. To install it on another  Magento software, please contact our Support department.")}
<br/>
<small class="rule-param-remove">{$this->__("The setting will be used for all other AITOC Modules, installed afterwards, as it defines your Magento software. If later willing to change to the opposite setting, you will have to make manual code edits in your Magento files. You can find more details about how to make the edits in the Installation-Uninstallation doc.")}</small>
</div>
<div id="live_notice">
{$this->__("If you choose LIVE the Module will be installed on your Magento software according to the purchased License.")}
<br/>
<small class="rule-param-remove">{$this->__("The setting will be used for all other AITOC Modules, installed afterwards, as it defines your Magento software. If later willing to change to the opposite setting, you will have to make manual code edits in your Magento files. You can find more details about how to make the edits in the Installation-Uninstallation doc.")}</small>
</div>
NOTEMSG;
        return $msg;
    }
    
    protected function _getLicenseParametersHtml($isUpgrade = false)
    {
        $html = '';
        if ($licenseRules = $this->getLicenseHelper()->getRulesInfo($this->getLicense(), $isUpgrade))
        {
            $html .= '<table class="license-params-table" cellspacing="0" cellpadding="0">';
            foreach ($licenseRules as $ruleCode => $rule)
            {
                $html .= "<tr>";
                $html .= "<td><h3>".$this->getLicenseHelper()->getRuleTitle($ruleCode,$this->getLicense(),$isUpgrade).":</h3></td>";
                $html .= "<td><h3>".$rule['used']."/".$rule['licensed']." (".$rule['total'].")</h3></td>";
                $html .= "</tr>";
            }
            $html .= "<tr><td></td><td><small>".$this->getLicenseHelper()->getRulesSignature()."</small></td></tr>";
            $html .= '</table>';
        }
        return $html;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module
     */
    public function getModule()
    {
        return Mage::registry('aitoc_module');
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Helper_License
     */
    public function getLicenseHelper()
    {
        return $this->tool()->getLicenseHelper();
    }
    
}
