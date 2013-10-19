<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'base_affiliate_credit', 'decimal(12,4) default NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'affiliate_credit', 'decimal(12,4) default NULL');

$installer->getConnection()->addColumn($this->getTable('affiliateplus_transaction'), 'holding_from', 'datetime NULL');

if (version_compare(Mage::getVersion(), '1.4.1.0', '>=')) {
    $installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'base_affiliate_credit', 'decimal(12,4) default NULL');
    $installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'affiliate_credit', 'decimal(12,4) default NULL');

    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'base_affiliate_credit', 'decimal(12,4) default NULL');
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'affiliate_credit', 'decimal(12,4) default NULL');
} else {
    $setup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
    $setup->addAttribute('invoice', 'affiliate_credit', array('type' => 'decimal'));
    $setup->addAttribute('invoice', 'base_affiliate_credit', array('type' => 'decimal'));
    $setup->addAttribute('creditmemo', 'affiliate_credit', array('type' => 'decimal'));
    $setup->addAttribute('creditmemo', 'base_affiliate_credit', array('type' => 'decimal'));
}

// Withdraw Tax
$installer->getConnection()->addColumn($this->getTable('affiliateplus_payment'), 'tax_amount', 'decimal(12,4) default NULL');
$installer->getConnection()->addColumn($this->getTable('affiliateplus_payment'), 'amount_incl_tax', 'decimal(12,4) default NULL');

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('affiliateplus_tracking')};
CREATE TABLE {$this->getTable('affiliateplus_tracking')}(
    `tracking_id` int(10) unsigned NOT NULL auto_increment,
    `account_id` int(10) unsigned NOT NULL,
    `customer_id` int(10) unsigned NOT NULL,
    `customer_email` varchar(255) NOT NULL default '',
    `created_time` datetime NULL,
    PRIMARY KEY (`tracking_id`),
    KEY `FK_AFFILIATEPLUS_TRACKING_ACCOUNT_ID` (`account_id`),
    CONSTRAINT `FK_AFFILIATEPLUS_TRACKING_ACCOUNT` FOREIGN KEY (`account_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('affiliateplus_credit')};
CREATE TABLE {$this->getTable('affiliateplus_credit')}(
    `id` int(10) unsigned NOT NULL auto_increment,
    `payment_id` int(10) unsigned NOT NULL,
    `order_id` int(10) unsigned NOT NULL,
    `order_increment_id` varchar(255) NOT NULL default '',
    `base_paid_amount` decimal(12,4) NOT NULL default '0',
    `paid_amount` decimal(12,4) NOT NULL default '0',
    `base_refund_amount` decimal(12,4) NOT NULL default '0',
    `refund_amount` decimal(12,4) NOT NULL default '0',
    PRIMARY KEY (`id`),
    INDEX(`payment_id`),
    FOREIGN KEY (`payment_id`) REFERENCES {$this->getTable('affiliateplus_payment')} (`payment_id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('affiliateplus_payment_history')};
CREATE TABLE {$this->getTable('affiliateplus_payment_history')}(
    `history_id` int(10) unsigned NOT NULL auto_increment,
    `payment_id` int(10) unsigned NOT NULL,
    `status` tinyint(1) NOT NULL default '1',
    `created_time` datetime NULL,
    `description` text NOT NULL,
    PRIMARY KEY (`history_id`),
    INDEX(`payment_id`),
    FOREIGN KEY (`payment_id`) REFERENCES {$this->getTable('affiliateplus_payment')} (`payment_id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


");


$paymentSelect = $installer->getConnection()->select()->reset()
    ->from(array('p' => $this->getTable('affiliateplus_payment')), array(
        'payment_id'    => 'payment_id',
        'status'        => 'ABS(1)',
        'created_time'  => 'request_time',
        'description'   => "LTRIM('Create Withdrawal')"
    ));
$updateSql = $paymentSelect->insertFromSelect(
    $this->getTable('affiliateplus_payment_history'),
    array('payment_id', 'status', 'created_time', 'description'),
    true
);
$installer->getConnection()->query($updateSql);

// Convert from old configuration to new configuration fields
$movingPre = 'affiliateplus/';
$movingMap = array(
    'material/enable'   => 'general/material_enable',
    'material/page'     => 'general/material_page',
    
    'general/register_description'  => 'account/register_description',
    'general/required_address'      => 'account/required_address',
    'general/required_paypal'       => 'account/required_paypal',
    'general/need_approved'         => 'account/need_approved',
    'general/notification_after_signing_up' => 'account/notification_after_signing_up',
    'sharing/balance'   => 'account/balance',
    
    'general/affiliate_type'    => 'commission/affiliate_type',
    'general/commission_type'   => 'commission/commission_type',
    'general/commission'        => 'commission/commission',
    'payment/updatebalance_orderstatus'     => 'commission/updatebalance_orderstatus',
    'payment/decrease_commission_creditmemo'=> 'commission/decrease_commission_creditmemo',
    'payment/cancel_transaction_orderstatus'=> 'commission/cancel_transaction_orderstatus',
    
    'general/allow_discount'    => 'discount/allow_discount',
    'general/discount_type'     => 'discount/discount_type',
    'general/discount'          => 'discount/discount',
    'general/type_discount'     => 'discount/type_discount',
    
    'sales/type'            => 'commission/type',
    'sales/commission_type' => 'commission/add_commission_type',
    'sales/month'           => 'commission/month',
    'sales/month_tier'      => 'commission/month_tier',
    'sales/year'            => 'commission/year',
    'sales/year_tier'       => 'commission/year_tier',
    'sales/show'            => 'commission/show',
);
$movingSql = '';
foreach ($movingMap as $moveFrom => $moveTo) {
    $movingSql .= "UPDATE {$this->getTable('core/config_data')} ";
    $movingSql .= "SET path = '" . $movingPre . $moveTo . "' ";
    $movingSql .= "WHERE path = '" . $movingPre . $moveFrom . "'; ";
}
$installer->run($movingSql);

$installer->endSetup();
