<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('ig_cashondelivery_local')} ADD COLUMN `fee_mode` enum('percent','absolute') NOT NULL default 'absolute'");
$installer->run("ALTER TABLE {$this->getTable('ig_cashondelivery_foreign')} ADD COLUMN `fee_mode` enum('percent','absolute') NOT NULL default 'absolute'");
$installer->endSetup();
?>