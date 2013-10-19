<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

if (!$installer->getConnection()->tableColumnExists($installer->getTable('affiliateplusstatistic'), 'account_email')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('affiliateplusstatistic')
        , 'account_email'
        , 'varchar(255) NULL'
    );
}

$installer->endSetup();
