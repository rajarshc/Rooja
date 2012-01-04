<?php
$installer = $this;

$installer->startSetup();

/* create the index table if it doesn't exist already */
Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer,
		"CREATE TABLE IF NOT EXISTS `{$this->getTable ('rewards/customer_indexer_points' )}` (
			`customer_id` INT( 10 ) unsigned NOT NULL ,
			`customer_points_usable` INT( 11 ) NOT NULL ,
			`customer_points_pending_event` INT( 11 ) NOT NULL ,
			`customer_points_pending_time` INT( 11 ) NOT NULL ,
			`customer_points_pending_approval` INT( 11 ) NOT NULL ,
			`customer_points_active` INT( 11 ) NOT NULL ,
			PRIMARY KEY (  `customer_id` )
			) ENGINE=InnoDB DEFAULT CHARSET=utf8; "
);

/* in case updating ST from an older version, this will add the columns in if they don't already exist, otherwise nothing bad happens */
Mage::helper( 'rewards/mysql4_install' )->addColumns( $installer, $this->getTable( 'rewards_customer_index_points' ), 
    array(
		"`customer_points_pending_event` INT( 11 ) NOT NULL" ,
		"`customer_points_pending_time` INT( 11 ) NOT NULL" ,
		"`customer_points_pending_approval` INT( 11 ) NOT NULL" ,
		"`customer_points_active` INT( 11 ) NOT NULL"    		
    ) );

Mage::helper( 'rewards/customer_points_index' )->invalidate();


Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, "
    ALTER TABLE `{$this->getTable('rewards_customer_index_points')}`
    MODIFY customer_id int(10) UNSIGNED ;
" );

Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, "
    ALTER TABLE `{$this->getTable('rewards_transfer')}`
    MODIFY customer_id int(10) UNSIGNED ;
" );


/* 
 * @mhadianfard -a 19/12/11: 
 * before adding FK constraints make sure no orphaned transfers (missing customer) exisit
 * so that constraint violations dont occour  
 */
Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, "
			Delete from `{$this->getTable('rewards_transfer')}`
			where customer_id NOT IN (
				Select customers.`entity_id` from `{$this->getTable( 'customer_entity' )}` as customers
			);
		" );
	
Mage::helper( 'rewards/mysql4_install' )->attemptQuery( $installer, "
			Delete from `{$this->getTable( 'rewards_customer_index_points' )}`
			where customer_id NOT IN (
				Select customers.`entity_id` from `{$this->getTable( 'customer_entity' )}` as customers
			);
		" );

/*
 * //@mhadianfard -a 19/12/11: now we can add in the constraints:
 */

Mage::helper( 'rewards/mysql4_install' )->addFKey( $installer, "FK_TRANSFER_CUSTOMER_ID", $this->getTable( 'rewards_transfer' ), 
    "customer_id", $this->getTable( 'customer_entity' ), "entity_id", "CASCADE", "CASCADE" );

Mage::helper( 'rewards/mysql4_install' )->addFKey( $installer, "FK_CUSTOMER_INDEX_POINTS_CUSTOMER_ID", $this->getTable( 'rewards_customer_index_points' ), 
    "customer_id", $this->getTable( 'customer_entity' ), "entity_id", "CASCADE", "CASCADE" );


$install_version = Mage::getConfig ()->getNode ( 'modules/TBT_Rewards/version' );
$msg_title = "Sweet Tooth v{$install_version} was successfully installed!";
$msg_desc = "Sweet Tooth v{$install_version} was successfully installed on your store.";
Mage::helper( 'rewards/mysql4_install' )->createInstallNotice( $msg_title, $msg_desc );


// check if there are any active birthday rules
$hasActiveBirthdayRules = false;
$rules = Mage::getModel('rewards/special')->getCollection()
    ->addFieldToFilter('is_active', '1');
foreach ($rules as $rule) {
    $ruleConditions = Mage::helper('rewards')->unhashIt($rule->getConditionsSerialized());
    if (is_array($ruleConditions)) {
        if (in_array(TBT_Rewards_Model_Birthday_Action::ACTION_CODE, $ruleConditions)) {
            $hasActiveBirthdayRules = true;
            break;
        }
    } else if ($rule_conditions == TBT_Rewards_Model_Birthday_Action::ACTION_CODE) {
        $hasActiveBirthdayRules = true;
        break;
    }
}
if ($hasActiveBirthdayRules) {
    $msg_title = "Your customers may not have received Birthday Points!";
    $msg_desc = "Any customers who have had birthdays since you created your Birthday Points rule have not ".
        "received their birthday points.  To check if this has happened, go to Rewards > Configuration > Other ".
        "Configuration > Diagnostics & Support Tools > Run 'Test Sweet' Diagnostics.  Look for the ".
        "<b>Check birthday points</b> header.";
    $msg_url = "http://www.sweettoothrewards.com/wiki/index.php/Birthday_Points#Missed_Birthday_Awards";
    $msg_severity = Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR;
    Mage::helper('rewards/mysql4_install')->createInstallNotice($msg_title, $msg_desc, $msg_url, $msg_severity);
}


// Clear cache.
Mage::helper( 'rewards/mysql4_install' )->prepareForDb();

$installer->endSetup(); 
