<?php
$installer = $this;

$installer->startSetup ();

$msg_title = "Sweet Tooth was upgraded to version 1.6!";
$msg_desc = "Sweet Tooth has been updated to version 1.6.0.0 for the first time.";
Mage::helper ( 'rewards/mysql4_install' )->createInstallNotice ( $msg_title, $msg_desc );

$installer->endSetup (); 
