<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Rma
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
$installer = $this;
$installer->startSetup();

/**
 * Creating extensions tables and filling it with initial data
 */
try {
    $installer->run("
        CREATE TABLE IF NOT EXISTS `{$this->getTable('awrma/entity')}` (
            `id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
            `order_id` INT( 10 ) NOT NULL ,
            `order_items` TEXT NOT NULL ,
            `request_type` INT( 10 ) NOT NULL ,
            `package_opened` TINYINT( 1 ) NOT NULL ,
            `created_at` DATETIME NOT NULL ,
            `status` INT( 10 ) NOT NULL ,
            `approvement_code` TINYTEXT NOT NULL ,
            `tracking_code` TINYTEXT NOT NULL ,
            `customer_id` INT( 10 ) NOT NULL ,
            `customer_name` TINYTEXT NOT NULL ,
            `customer_email` TINYTEXT NOT NULL ,
            `external_link` TINYTEXT NOT NULL ,
            `admin_notes` TEXT NOT NULL ,
            `print_label` TEXT NOT NULL ,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = 'All RMA Requests';

        CREATE TABLE IF NOT EXISTS `{$this->getTable('awrma/entity_comments')}` (
            `id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
            `entity_id` INT( 10 ) NOT NULL ,
            `created_at` DATETIME NOT NULL ,
            `text` TEXT NOT NULL ,
            `attachments` TEXT NOT NULL ,
            `owner` TINYINT NOT NULL ,
            PRIMARY KEY (`id`) ,
            KEY `entity_id` (`entity_id`) ,
            CONSTRAINT `FK_ENTITY_ID` FOREIGN KEY (`entity_id`) REFERENCES `{$this->getTable('awrma/entity')}` (`id`) ON DELETE CASCADE
        ) ENGINE = InnoDB COMMENT = 'Table contain comments for RMA requests' DEFAULT CHARSET=utf8;

        CREATE TABLE IF NOT EXISTS `{$this->getTable('awrma/entity_types')}` (
            `id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
            `name` TINYTEXT NOT NULL ,
            `store` TINYTEXT NOT NULL ,
            `sort` SMALLINT NOT NULL ,
            `enabled` TINYINT( 1 ) NOT NULL DEFAULT '1' ,
            PRIMARY KEY (`id`)
        ) ENGINE = InnoDB COMMENT = 'Table contain possible types of RMA requests' DEFAULT CHARSET=utf8;

        INSERT IGNORE INTO `{$this->getTable('awrma/entity_types')}` (`id`, `name`, `store`, `sort`) VALUES (1, 'Replacement', 0, 1);
        INSERT IGNORE INTO `{$this->getTable('awrma/entity_types')}` (`id`, `name`, `store`, `sort`) VALUES (2, 'Refund', 0, 2);

        CREATE TABLE IF NOT EXISTS `{$this->getTable('awrma/entity_status')}` (
            `id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
            `name` TINYTEXT NOT NULL ,
            `store` TINYTEXT NOT NULL ,
            `sort` SMALLINT NOT NULL ,
            `resolve` TINYINT NOT NULL ,
            `to_customer` TEXT NOT NULL ,
            `to_admin` TEXT NOT NULL ,
            `to_chatbox` TEXT NOT NULL ,
            `removed` TINYINT NOT NULL DEFAULT '0' ,
            PRIMARY KEY ( `id` )
        )  ENGINE = InnoDB COMMENT = 'Table contain possible statuses of RMA requests' DEFAULT CHARSET=utf8;

        INSERT IGNORE INTO `{$this->getTable('awrma/entity_status')}` (`id`, `name`, `store`, `sort`, `resolve`, `to_customer`, `to_admin`, `to_chatbox`, `removed`) VALUES
            (".Mage::helper('awrma/status')->getPendingApprovalStatusId().", 'Pending Approval', '0', 1, 0, '<p>RMA {{var request.getTextId()}} successfully created.</p>', '<p>A new RMA {{var request.getTextId()}} is initiated by {{var request.getCustomerName()}} <{{var request.getCustomerEmail()}}> for order <a href=\"{{var request.getNotifyOrderAdminLink()}}\">#{{var request.getOrderId()}}</a>.</p>\r\n<p>Date: {{var request.getCreatedAt()}}<br />\r\nRequest Type:  {{var request.getRequestTypeName()}}<br />\r\nPackage Opened: {{var request.getPackageOpenedLabel()}}</p>\r\n<p>Items<br />\r\n{{layout handle=\"awrma_email_request_item\" rma_request=\$request}}</p>', 'Your RMA has been placed and waiting for approval.', 0),
            (".Mage::helper('awrma/status')->getApprovedStatusId().", 'Approved', '0', 2, 0, '<p>Your RMA {{var request.getTextId()}} has been approved.</p>\r\n{{depend request.getNotifyPrintlabelAllowed()}}<p>You can print a \"Return Shipping Authorization\" label with return address and other information by pressing link above. A \"Return Shipping Authorization\" label must be enclosed inside your package; you may want to keep a copy of \"Return Shipping Authorization\" label for your records.</p>\r\n{{/depend}}\r\n<p>Please send your package to:</p>\r\n<p>{{var request.getNotifyRmaAddress()}}</p>\r\n<p>and press \"Confirm Sending\" button after.</p>', '', 'Your RMA has been approved.\r\n{{depend request.getNotifyPrintlabelAllowed()}}You can print a \"Return Shipping Authorization\" label with return address and other information by pressing link above. A \"Return Shipping Authorization\" label must be enclosed inside your package; you may want to keep a copy of \"Return Shipping Authorization\" label for your records.\r\n{{/depend}}\r\nPlease send your package to:\r\n\r\n{{var request.getNotifyRmaAddress()}}\r\n\r\nand press \"Confirm Sending\" button after.', 0),
            (".Mage::helper('awrma/status')->getPackageSentStatusId().", 'Package sent', '0', 3, 0, '', '{{depend request.getNotifyStatusChanged()}}\r\n<p>RMA {{var request.getTextId()}} status changed to {{var request.getStatusName()}}</p>\r\n{{/depend}}\r\n<h3>RMA details</h3>\r\n<p>ID: {{var request.getTextId()}}<br />\r\nOrder ID: #<a href=\"{{var request.getNotifyOrderAdminLink()}}\">#{{var request.getOrderId()}}</a>.<br />\r\nCustomer: {{var request.getCustomerName()}} <{{var request.getCustomerEmail()}}><br />\r\nDate: {{var request.getCreatedAt()}}\r\nRequest Type: {{var request.getRequestTypeName()}}<br />\r\nPackage Opened: {{var request.getPackageOpenedLabel()}}</p>\r\n<p>Items<br />\r\n{{layout handle=\"awrma_email_request_item\" rma_request=\$request}}</p>', '', 0),
            (".Mage::helper('awrma/status')->getResolvedCanceledStatusId().", 'Resolved (canceled)', '0', 4, 1, 'Your RMA {{var request.getTextId()}} has been successfully resolved with status \"{{var request.getStatusName()}}\".', 'RMA {{var request.getTextId()}} has been canceled by customer\r\n\r\n<h3>RMA details</h3>\r\n\r\n<p>ID: {{var request.getTextId()}}<br />\r\nOrder ID: #<a href=\"{{var request.getNotifyOrderAdminLink()}}\">#{{var request.getOrderId()}}</a>.<br />\r\nCustomer: {{var request.getCustomerName()}} <{{var request.getCustomerEmail()}}><br />\r\nDate: {{var request.getCreatedAt()}}\r\nRequest Type: {{var request.getRequestTypeName()}}<br />\r\nPackage Opened: {{var request.getPackageOpenedLabel()}}</p>\r\n<p>Items<br />\r\n{{layout handle=\"awrma_email_request_item\" rma_request=\$request}}</p>', 'RMA {{var request.getTextId()}} has been successfully resolved with status \"{{var request.getStatusName()}}\".', 0),
            (5, 'Resolved (refunded)', '0', 5, 1, 'Your RMA {{var request.getTextId()}} has been successfully resolved with status \"{{var request.getStatusName()}}\".', '', 'RMA {{var request.getTextId()}} has been successfully resolved with status \"{{var request.getStatusName()}}\".', 0),
            (6, 'Resolved (replaced)', '0', 6, 1, 'Your RMA {{var request.getTextId()}} has been successfully resolved with status \"{{var request.getStatusName()}}\".', '', 'RMA {{var request.getTextId()}} has been successfully resolved with status \"{{var request.getStatusName()}}\".', 0);
    ");
} catch (Exception $ex) {
    Mage::logException($ex);
}

/**
 * Creating folder for uploads storage
 */
$path = Mage::getBaseDir('media').DS.Mage::helper('awrma/files')->getFolderName();

if(file_exists($path) || (!file_exists($path) && @mkdir($path)))
    @file_put_contents($path.DS.'.htaccess', 'Deny from all');

$installer->endSetup();
