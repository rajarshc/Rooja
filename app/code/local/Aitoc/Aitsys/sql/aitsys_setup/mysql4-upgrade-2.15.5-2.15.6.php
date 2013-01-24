<?php

/* @var $this Aitoc_Aitsys_Model_Mysql4_Setup */
$this->startSetup();

if(!Mage::registry('aitsys_correction_setup'))
{
    Mage::register('aitsys_correction_setup', true);

    $this->run("
    
    CREATE TABLE IF NOT EXISTS {$this->getTable('aitsys_status')} (
        `id` INT(10) UNSIGNED NOT NULL auto_increment ,
        `module` VARCHAR(50) NOT NULL,
        `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY ( `id` ),
        KEY `module` ( `module` )
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    ");
    
    foreach ($this->tool()->platform()->getModules() as $module)
    {
        Aitoc_Aitsys_Model_Module_Status::updateStatus($module->getKey(), $module->getValue());
    }
}

$this->endSetup();