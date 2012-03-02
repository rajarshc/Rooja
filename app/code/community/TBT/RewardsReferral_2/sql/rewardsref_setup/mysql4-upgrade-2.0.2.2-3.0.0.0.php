<?php

$installer = $this;

$installer->startSetup();

$install_version = Mage::getConfig ()->getNode ( 'modules/TBT_RewardsReferral/version' );

$msg_title = "Sweet Tooth Referral System v". $install_version ." was sucessfully installed.";
$msg_desc = "Sweet Tooth Referral System v". $install_version ." was just installed. Remember to clear ALL cache and move template/skin files from base/default to default/default if you are running a version of Magento Community Edition lower than v1.4.";
Mage::helper ( 'rewards/mysql4_install' )->createInstallNotice ( $msg_title, $msg_desc );

$installer->endSetup();



