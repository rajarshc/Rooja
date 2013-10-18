<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('affiliateplus_action')};

CREATE TABLE {$this->getTable('affiliateplus_action')} (
  `action_id` bigint(19) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `account_email` varchar(25) NOT NULL default '',
  `banner_id`	int(10) unsigned default '0',
  `banner_title` varchar(25) NOT NULL default '',
  `type` tinyint(1) NOT NULL default '2',
  `ip_address`  varchar(25) NOT NULL default '',
  `is_unique` tinyint(1) NOT NULL default '0',
  `is_commission` int(5) NOT NULL default '0',
  `domain`  varchar(100) NOT NULL default '',
  `referer` varchar(100) NOT NULL default '',
  `landing_page`  text NOT NULL default '',
  `totals`   BIGINT(8) NOT NULL default '0',
  `created_date` date NULL,
  `updated_time` datetime NULL,
  `store_id` smallint(5) unsigned  NOT NULL,
  `direct_link` int(10) unsigned NOT NULL,
  INDEX(`account_id`),
  INDEX(`store_id`),
  FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('affiliateplus_transaction')}
  ADD COLUMN `type` TINYINT(2) NOT NULL default '3',
  ADD COLUMN `banner_id` int(10) NULL;
  
");

if ($installer->tableExists($installer->getTable('affiliateplusstatistic'))) {
    /* add column */
    if (!$installer->getConnection()->tableColumnExists(
            $installer->getTable('affiliateplusstatistic'),
            'account_email')
    ) {
        $installer->getConnection()->addColumn(
            $installer->getTable('affiliateplusstatistic')
            , 'account_email'
            , 'varchar(255) NULL'
        );
    }
    /* Fix error data */
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
    
    /* Convert from old data to new data */
    $trafficSelect = $installer->getConnection()->select()->reset()
        ->from(array('s' => $installer->getTable('affiliateplusstatistic')), array())
        ->joinInner(array('r' => $installer->getTable('affiliateplus_referer')),
            's.referer_id = r.referer_id',
            array()
        )->columns(array(
            'account_id'    => 'r.account_id',
            'account_email' => 's.account_email',
            'ip_address'    => 's.ip_address',
            // 'is_unique'     => 'ABS(1)',
            'domain'        => 's.referer',
            'referer'       => 's.referer',
            'landing_page'  => 's.url_path',
            'totals'        => 'COUNT(s.id)',
            'created_date'  => 'DATE(s.visit_at)',
            'updated_time'  => 's.visit_at',
            'store_id'      => 's.store_id'
        ))->group(array('s.referer', 's.url_path', 's.ip_address', 'DATE(s.visit_at)', 's.store_id'));
    $updateSql = $trafficSelect->insertFromSelect($installer->getTable('affiliateplus_action'),
        array(
            'account_id', 'account_email', 'ip_address', /*'is_unique', */'domain', 'referer',
            'landing_page', 'totals', 'created_date', 'updated_time', 'store_id'
        ),
        true
    );
    $installer->getConnection()->query($updateSql);
    
    /* repair unique click data */
    $uniqueSelect = $installer->getConnection()->select()->reset()
        ->from(array('u' => $installer->getTable('affiliateplus_action')),array('action_id'))
        ->group(array('u.ip_address', 'u.account_id', 'u.domain'));
    $select = $installer->getConnection()->select()->reset()
        ->joinInner(array('e' => new Zend_Db_Expr("({$uniqueSelect->__toString()})")),
            'e.action_id = main_table.action_id', null)
        ->columns(array('is_unique' => 'ABS(1)'));
    $updateSql = $select->crossUpdateFromSelect(array('main_table' => $installer->getTable('affiliateplus_action')));
    $installer->getConnection()->query($updateSql);
}

$installer->endSetup();
