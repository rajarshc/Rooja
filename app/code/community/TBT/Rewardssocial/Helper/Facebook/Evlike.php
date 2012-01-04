<?php

class TBT_Rewardssocial_Helper_Facebook_Evlike extends Mage_Core_Helper_Abstract {

    /**
     * @return string backend system configuration url for the Retail Evolved Like extension module screen
     */
    public function getConfigUrl() {
        return $this->_getUrl(  'adminhtml/system_config/edit', array('section' => 'evlike')  );
    }
    
    
    /**
     * @return boolean true if module output is enabled for the Retail Evolved Like extension.
     */
    public function isEvlikeEnabled() {
        $is_enabled = ! Mage::getStoreConfigFlag('advanced/modules_disable_output/TBT_Rewards');
        $is_enabled = $is_enabled && Mage::getConfig()->getModuleConfig('Evolved_Like')->is('active', 'true');
        
        return $is_enabled;
    }
    

    /**
     * @return boolean true if module output is enabled for the Retail Evolved Like extension.
     */
    public function isEvlikeValidRewardsConfig() {
        $button_type_valid = (int) Mage::getStoreConfig('evlike/evlike/ev_facebook_type') === 0;
        
        $is_all_valid = $button_type_valid;
        
        return $is_all_valid;
    }
    
}
