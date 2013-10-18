<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/* Repair Failed Data */
$refererEmail = $installer->getConnection()->select()->reset()
    ->from(array('r' => $installer->getTable('affiliateplus/referer')),array('referer_id'))
    ->joinInner(array('a' => $installer->getTable('affiliateplus/account')),
        'r.account_id = a.account_id',
        array('email')
    );
$select = $installer->getConnection()->select()->reset()
    ->joinInner(array('e' => new Zend_Db_Expr("({$refererEmail->__toString()})")),
        'e.referer_id = main_table.referer_id', null)
    ->columns(array('account_email' => 'email'));

$updateSql = $select->crossUpdateFromSelect(array('main_table' => $installer->getTable('affiliateplusstatistic')));
$installer->getConnection()->query($updateSql);

$installer->endSetup();
