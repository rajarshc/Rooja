<?php

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('affiliateplus_coupon')};
CREATE TABLE {$this->getTable('affiliateplus_coupon')}(
  `coupon_id` int(10) unsigned NOT NULL auto_increment,
  `coupon_code` varchar(255) NOT NULL default '',
  `account_id` int(10) unsigned,
  `account_name` varchar(255) NOT NULL default '',
  `program_id` int(10) unsigned,
  `program_name` varchar(255) default '',
  UNIQUE(`coupon_code`),
  PRIMARY KEY (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('affiliateplus_transaction')}
  ADD COLUMN `coupon_code` varchar(255) default '' AFTER `total_amount`,
  ADD COLUMN `program_id` int(10) unsigned AFTER `coupon_code`,
  ADD COLUMN `program_name` varchar(255) default '' AFTER `program_id`;

");
$installer->getConnection()->resetDdlCache($this->getTable('affiliateplus_transaction'));

$accounts = Mage::getResourceModel('affiliateplus/account_collection')
	->addFieldToFilter('coupon_code',array('notnull' => true))
	->addFieldToFilter('coupon_code',array('neq' => ''));
$coupon = Mage::getModel('affiliatepluscoupon/coupon')
	->setProgramId('0')
	->setProgramName('Affiliate Program');
foreach ($accounts as $account){
	if (!$account->getCouponCode()) continue;
	$coupon->setCouponCode($account->getCouponCode())
		->setAccountId($account->getId())
		->setAccountName($account->getName())
		->setId(null)->save();
}

$installer->endSetup();