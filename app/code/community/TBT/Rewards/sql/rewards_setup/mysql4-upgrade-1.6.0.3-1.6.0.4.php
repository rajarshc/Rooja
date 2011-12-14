<?php
$installer = $this;

$installer->startSetup();

Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'rewards/special' ), 
    array(
        "`onhold_duration` INT(11) NOT NULL DEFAULT '0'"
    ) );

$install_version = Mage::getConfig ()->getNode ( 'modules/TBT_Rewards/version' );
$msg_title = "Sweet Tooth v{$install_version} was successfully installed!";
$msg_desc = "Sweet Tooth v{$install_version} was successfully installed on your store.";
Mage::helper( 'rewards/mysql4_install' )->createInstallNotice( $msg_title, $msg_desc );

$installer->endSetup(); 
