<?php

$installer = $this;

$installer->startSetup();


Mage::helper ( 'rewards/mysql4_install' )->attemptQuery ( $installer, "
ALTER TABLE `{$this->getTable('rewardsref_referral')}` DROP FOREIGN KEY rewardsref_referral_child_fk1;
");

Mage::helper ( 'rewards/mysql4_install' )->attemptQuery ( $installer, "
ALTER TABLE `{$this->getTable('rewardsref_referral')}` DROP FOREIGN KEY rewardsref_referral_parent_fk;
");

Mage::helper ( 'rewards/mysql4_install' )->attemptQuery ( $installer, "
ALTER TABLE `{$this->getTable('rewardsref_referral')}`
    ADD CONSTRAINT `rewardsref_referral_child_fk1` 
      FOREIGN KEY (`referral_child_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`)
      ON DELETE SET NULL ON UPDATE CASCADE
;
");


Mage::helper ( 'rewards/mysql4_install' )->attemptQuery ( $installer, "
ALTER TABLE `{$this->getTable('rewardsref_referral')}`
    ADD CONSTRAINT `rewardsref_referral_parent_fk` 
      FOREIGN KEY (`referral_parent_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`)
      ON DELETE CASCADE ON UPDATE CASCADE
;
");



$installer->endSetup();



