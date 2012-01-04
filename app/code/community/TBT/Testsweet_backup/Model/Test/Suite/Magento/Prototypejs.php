<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Prototypejs extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Prototype JavaScript framework Version');
    }

    public function getDescription() {
        return $this->__('Check Prototype JavaScript framework version.');
    }

    protected function generateSummary() {
        $target = Mage::getBaseDir('base') . "/js/prototype/prototype.js";
        $content = file($target);
        if ($content) {
            $version = $content[0];
            if( strpos($version, 'version 1.7') !== false) {
                $this->addPass($this->__("Prototype JavaScript framework is version 1.7.x"));
            } elseif( strpos($version, 'version 1.6.0.3') !== false) {
                $this->addWarning($this->__("Prototype JavaScript framework is version 1.6.0.3 and might have issues with IE9"), $this->__("For more help visit: http://www.sweettoothrewards.com/wiki/index.php/Prototype_JavaScript_Framework_1.6.0.3"));
            } else {
                // Ignore unknowen version because it's likly compatible
                $this->addPass($this->__("No issue found."));
            }
        }
    }

}

