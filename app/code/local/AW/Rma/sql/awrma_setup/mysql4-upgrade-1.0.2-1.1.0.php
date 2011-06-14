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
$installer->run("
UPDATE {$this->getTable('awrma/entity_status')} SET `to_customer` = '<p>Your RMA {{var request.getTextId()}} has been approved.</p>\r\n{{depend request.getNotifyPrintlabelAllowed()}}<p>You can print a \"Return Shipping Authorization\" label with return address and other information by pressing link above. A \"Return Shipping Authorization\" label must be enclosed inside your package; you may want to keep a copy of \"Return Shipping Authorization\" label for your records.</p>\r\n{{/depend}}\r\n<p>Please send your package to:</p>\r\n<p>{{var request.getNotifyRmaAddress()}}</p>\r\n{{depend request.getConfirmShippingIsRequired()}}<p>and press \"Confirm Sending\" button after.</p>{{/depend}}',`to_chatbox` = 'Your RMA has been approved.\r\n{{depend request.getNotifyPrintlabelAllowed()}}You can print a \"Return Shipping Authorization\" label with return address and other information by pressing link above. A \"Return Shipping Authorization\" label must be enclosed inside your package; you may want to keep a copy of \"Return Shipping Authorization\" label for your records.\r\n{{/depend}}\r\nPlease send your package to:\r\n\r\n{{var request.getNotifyRmaAddress()}}\r\n\r\n{{depend request.getConfirmShippingIsRequired()}}and press \"Confirm Sending\" button after.{{/depend}}' WHERE `name` = 'Approved';
");
$installer->endSetup();
