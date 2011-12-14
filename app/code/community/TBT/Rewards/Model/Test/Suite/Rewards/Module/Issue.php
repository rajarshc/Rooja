<?php

class TBT_Rewards_Model_Test_Suite_Rewards_Module_Issue extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Check for modules that should be disabled.');
    }

    public function getDescription() {
        return $this->__('Check for modules that have knowen issues with Sweet Tooth.  This is not a complete test!');
    }

    protected function generateSummary() {
        
        $knowen_module = array(
            'Mage_Reward' => $this->__('http://www.sweettoothrewards.com/wiki/index.php/How_To_Disable_The_Default_Magento_Enterprise_Rewards_Module'),
            // TODO: add more
        );

        $modules = (array) Mage::getConfig()->getNode('modules')->children();
        
        foreach ($modules as $key => $modele) {
            foreach($knowen_module as $kmodele_key => $kmodele_help ) {
                if ($kmodele_key == $modele) {
                    $this->addNotice($this->__('Module: %s - is active and is knowen to cause issues.', $key), $kmodele_help);
                }
            }
        }
    }

}
