<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('sales/order_item')}
  ADD COLUMN `affiliateplus_amount` decimal(12,4) default '0.0000',
  ADD COLUMN `base_affiliateplus_amount` decimal(12,4) default '0.0000',
  ADD COLUMN `affiliateplus_commission` decimal(12,4) default '0.0000';

ALTER TABLE {$this->getTable('affiliateplus_transaction')}
  ADD COLUMN `creditmemo_ids` varchar(255) NOT NULL default '';



DROP TABLE IF EXISTS {$this->getTable('affiliateplus_payment_verify')};
CREATE TABLE {$this->getTable('affiliateplus_payment_verify')}(
    `verify_id` int(10) unsigned NOT NULL auto_increment,
    `account_id` int(10) unsigned NOT NULL,
    `payment_method` varchar(63) NOT NULL default '',
    `field` varchar(100) NOT NULL default '',
    `info`  text NOT NULL default '',
    `verified`  tinyint(1) NOT NULL default '2',
    INDEX (`account_id`),
    FOREIGN KEY (`account_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (`verify_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
    

ALTER TABLE {$this->getTable('affiliateplus_payment')}
    ADD COLUMN `is_reduced_balance` tinyint(1) NOT NULL default '0',
    ADD COLUMN `is_refund_balance` tinyint(1) NOT NULL default '0';

UPDATE {$this->getTable('affiliateplus_payment')} SET `is_reduced_balance` = '1' WHERE `status` = '3';

");

if (version_compare(Mage::getVersion(), '1.4.1.0', '>=')) {
    $installer->run("

    ALTER TABLE {$this->getTable('sales/invoice')}
      ADD COLUMN `affiliateplus_discount` decimal(12,4) default NULL,
      ADD COLUMN `base_affiliateplus_discount` decimal(12,4) default NULL;

    ALTER TABLE {$this->getTable('sales/creditmemo')}
      ADD COLUMN `affiliateplus_discount` decimal(12,4) default NULL,
      ADD COLUMN `base_affiliateplus_discount` decimal(12,4) default NULL;

    ");
} else {
    $setup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
    $setup->addAttribute('invoice', 'affiliateplus_discount', array('type' => 'decimal'));
    $setup->addAttribute('invoice', 'base_affiliateplus_discount', array('type' => 'decimal'));
    $setup->addAttribute('creditmemo', 'affiliateplus_discount', array('type' => 'decimal'));
    $setup->addAttribute('creditmemo', 'base_affiliateplus_discount', array('type' => 'decimal'));
}

/*transfer old data*/

if ($installer->tableExists($installer->getTable('affiliateplus_payment_verify'))) {
    /*transfer email paypal verified to verify table*/
    if ($installer->tableExists($installer->getTable('affiliateplus_payment_paypal'))) {
        $paypalSelect = $installer->getConnection()->select()->reset()
            ->from(array('p' => $installer->getTable('affiliateplus_payment')), array())
            ->where('p.status=3')
            ->joinInner(array('pp' => $installer->getTable('affiliateplus_payment_paypal')),
                'p.payment_id = pp.payment_id',
                array()
            )->columns(array(
                'account_id'    => 'p.account_id',
                'payment_method'    => 'p.payment_method',
                'paypal_email' => 'pp.email',
                'verified'	=> 'ABS(1)'
            ))->group(array('p.account_id', 'p.payment_method', 'pp.email'));
        //Zend_Debug::dump($paypalSelect->__toString());die('1');
        $paypalSql = $paypalSelect->insertFromSelect($installer->getTable('affiliateplus_payment_verify'),
            array(
                'account_id', 'payment_method', 'field', 'verified'
            ),
            true
        );
        $installer->getConnection()->query($paypalSql);
    }
    /*end paypal*/

    /*transfer email moneybooker verified to verify table*/
    if ($installer->tableExists($installer->getTable('affiliatepluspayment_moneybooker'))) {
        $moneybookerSelect = $installer->getConnection()->select()->reset()
            ->from(array('p' => $installer->getTable('affiliateplus_payment')), array())
            ->where('p.status=3')
            ->joinInner(array('mb' => $installer->getTable('affiliatepluspayment_moneybooker')),
                'p.payment_id = mb.payment_id',
                array()
            )->columns(array(
                'account_id'    => 'p.account_id',
                'payment_method'    => 'p.payment_method',
                'paypal_email' => 'mb.email',
                'verified'	=> 'ABS(1)'
            ))->group(array('p.account_id', 'p.payment_method', 'mb.email'));
        $moneybookerSql = $moneybookerSelect->insertFromSelect($installer->getTable('affiliateplus_payment_verify'),
            array(
                'account_id', 'payment_method', 'field', 'verified'
            ),
            true
        );
        $installer->getConnection()->query($moneybookerSql);
    }
    /*end moneybooker*/

    /*transfer offline address verified to verify table*/
    if ($installer->tableExists($installer->getTable('affiliatepluspayment_offline'))) {
        $offlineSelect = $installer->getConnection()->select()->reset()
            ->from(array('p' => $installer->getTable('affiliateplus_payment')), array())
            ->where('p.status=3')
            ->joinInner(array('ol' => $installer->getTable('affiliatepluspayment_offline')),
                'p.payment_id = ol.payment_id',
                array()
            )->columns(array(
                'account_id'    => 'p.account_id',
                'payment_method'    => 'p.payment_method',
                'address_id' => 'ol.address_id',
                'verified'	=> 'ABS(1)'
            ))->group(array('p.account_id', 'p.payment_method', 'ol.address_id'));
        $offlineSql = $offlineSelect->insertFromSelect($installer->getTable('affiliateplus_payment_verify'),
            array(
                'account_id', 'payment_method', 'field', 'verified'
            ),
            true
        );
        $installer->getConnection()->query($offlineSql);
    }
    /*end offline*/

    /*transfer bank account verified to verify table*/
    if ($installer->tableExists($installer->getTable('affiliatepluspayment_bank'))) {
        $bankSelect = $installer->getConnection()->select()->reset()
            ->from(array('p' => $installer->getTable('affiliateplus_payment')), array())
            ->where('p.status=3')
            ->joinInner(array('ba' => $installer->getTable('affiliatepluspayment_bank')),
                'p.payment_id = ba.payment_id',
                array()
            )->columns(array(
                'account_id'    => 'p.account_id',
                'payment_method'    => 'p.payment_method',
                'bankaccount_id' => 'ba.bankaccount_id',
                'verified'	=> 'ABS(1)'
            ))->group(array('p.account_id', 'p.payment_method', 'ba.bankaccount_id'));
        $bankSql = $bankSelect->insertFromSelect($installer->getTable('affiliateplus_payment_verify'),
            array(
                'account_id', 'payment_method', 'field', 'verified'
            ),
            true
        );
        $installer->getConnection()->query($bankSql);
    }
/*end bank account*/
}



$installer->endSetup();
