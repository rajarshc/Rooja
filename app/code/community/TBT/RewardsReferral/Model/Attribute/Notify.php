<?php

/**
 * Customer group attribute source
 *
 * @category   Mage
 * @package    Mage_Customer
 */
class TBT_RewardsReferral_Model_Attribute_Notify extends Mage_Eav_Model_Entity_Attribute_Source_Config {

    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array(
                array('value' => 0, 'label' => Mage::helper('rewardsref')->__("No")),
                array('value' => 1, 'label' => Mage::helper('rewardsref')->__("Yes")),
            );
        }
        return $this->_options;
    }

}
