<?php
$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('customer', 'rewardsref_notify_on_referral', array(
    'label' => Mage::helper('rewardsref')->__('Notify on Referral'), 
    'type' => 'int', 
    'input' => 'select', 
    'visible' => true, 
    'required' => false, 
    'position' => 1, 
    'default' => 1, 
    'default_value' => 1, 
    'source' => "rewardsref/attribute_notify"
));

/* Adding extra column to specify type of points being awarded (percentage vs. fixed) as "simple_action" inside customer behaviour rules */
Mage::helper('rewards/mysql4_install')->addColumns($installer, $this->getTable('rewards_special'), 
array(
    "`simple_action` VARCHAR(32) NOT NULL DEFAULT 'by_percent'"
));

$install_version = Mage::getConfig()->getNode('modules/TBT_RewardsReferral/version');
$msg_title = "Sweet Tooth Referral System v" . $install_version . " was sucessfully installed.";
$msg_desc = "Sweet Tooth Referral System v" . $install_version . " was just installed. Remember to clear ALL cache and move template/skin files from base/default to default/default if you are running a version of Magento Community Edition lower than v1.4.";
Mage::helper('rewards/mysql4_install')->createInstallNotice($msg_title, $msg_desc);

$installer->endSetup();
