<?php

$installer = $this;

$installer->startSetup();

// Convert from old configuration to new configuration fields
$movingPre = 'affiliateplus/';
$movingMap = array(
    'multilevel/max_level'              => 'commission/max_level',
    'multilevel/tier_commission'        => 'commission/tier_commission',
    
    'multilevel/is_sent_email_account_new_transaction'  => 'email/multilevel_is_sent_email_account_new_transaction',
    'multilevel/is_sent_email_account_updated_transaction'  => 'email/multilevel_is_sent_email_account_updated_transaction',
    'multilevel/new_transaction_account_email_template' => 'email/multilevel_new_transaction_account_email_template',
    'multilevel/updated_transaction_account_email_template' => 'email/multilevel_updated_transaction_account_email_template',
);
$movingSql = '';
foreach ($movingMap as $moveFrom => $moveTo) {
    $movingSql .= "UPDATE {$this->getTable('core/config_data')} ";
    $movingSql .= "SET path = '" . $movingPre . $moveTo . "' ";
    $movingSql .= "WHERE path = '" . $movingPre . $moveFrom . "'; ";
}
$installer->run($movingSql);

$installer->endSetup();
