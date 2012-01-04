<?php

class TBT_Rewards_Model_Test_Suite_Rewards_Template_Migration extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Template file migration');
    }

    public function getDescription() {
        return $this->__('Check required template files are found in the correct locations.');
    }

    protected function generateSummary() {

        $basePaths = array(
            Mage::getBaseDir('design') . '/frontend/base/default/template/rewards',
            Mage::getBaseDir('design') . '/frontend/base/default/layout/rewards.xml',
        );
        $hasRewardsFilesInBase = false;
        foreach ($basePaths as $path) {
            if (file_exists($path))
                $hasRewardsFilesInBase = true;
        }
        
        $defaultPaths = array(
            Mage::getBaseDir('design') . '/frontend/default/default/template/rewards', 
            Mage::getBaseDir('design') . '/frontend/default/default/layout/rewards.xml'
        );
        $hasRewardsFilesInDefault = false;
        foreach ($defaultPaths as $path) {
            if ( file_exists($path) ) $hasRewardsFilesInDefault = true;
        }


        if (Mage::helper('rewards/version')->isBaseMageVersionAtLeast('1.4')) {
            if($hasRewardsFilesInDefault) {
                $this->addFail($this->__('Sweet Tooth template files should be in base/default not default/default'), $this->__('Help can be found here: http://sweettoothrewards.com/wiki/index.php/Additional_Sweet_Tooth_1.6_Update_Steps'));
            } elseif($hasRewardsFilesInBase && !$hasRewardsFilesInDefault) {
                $this->addPass($this->__('Sweet Tooth template files seem to be correctly located in base/default.'));
            }
                
        } else {
            if(!$hasRewardsFilesInDefault && $hasRewardsFilesInBase) {
                $this->addWarning($this->__('Sweet Tooth template files should be in default/default'), $this->__('Help can be found here: http://sweettoothrewards.com/wiki/index.php/Additional_Sweet_Tooth_1.6_Update_Steps'));
            } elseif($hasRewardsFilesInDefault) {
                $this->addPass($this->__('Sweet Tooth template files seem to be correctly located in default/default.'));
            }
            if($hasRewardsFilesInDefault && $hasRewardsFilesInBase) {
                $this->addWarning($this->__('Sweet Tooth template files in base/default will be ignored.'), $this->__('Help can be found here: http://sweettoothrewards.com/wiki/index.php/Additional_Sweet_Tooth_1.6_Update_Steps'));
            }
        }
    }

}
