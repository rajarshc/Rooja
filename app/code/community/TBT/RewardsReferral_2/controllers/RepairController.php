<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_RepairController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        /*
          $this->loadLayout();

          $customer = Mage::getSingleton('rewards/session')->getRewardsCustomer();
          Mage::register('customer', $customer);

          $this->renderLayout();
         */
        return $this;
    }

    public function fixRefAction() {
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table_prefix = Mage::getConfig()->getTablePrefix();

        // Update foreign keys with correct child id
        echo "Working...";
        flush();
        $result = $db->query("
            ALTER TABLE {$table_prefix}rewardsref_referral DROP FOREIGN KEY `rewardsref_referral_child_fk1`;
            ALTER TABLE {$table_prefix}rewardsref_referral 
                ADD CONSTRAINT `rewardsref_referral_child_fk1`
                  FOREIGN KEY (`referral_child_id`) 
                  REFERENCES `{$table_prefix}customer_entity` (`entity_id`)
            ;
            ALTER TABLE {$table_prefix}rewardsref_referral DROP FOREIGN KEY `rewardsref_referral_parent_fk` ;
            ALTER TABLE {$table_prefix}rewardsref_referral 
            ADD CONSTRAINT `rewardsref_referral_parent_fk`
              FOREIGN KEY (`referral_parent_id`) 
              REFERENCES `{$table_prefix}customer_entity` (`entity_id`)
            ;
        ");

        echo "Fixed foreign key issues properly. ";
        flush();
        //print_r($result);
        exit;
    }

    public function fixEmailTemplatesAction() {
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table_prefix = Mage::getConfig()->getTablePrefix();

        // Update foreign keys with correct child id
        echo "Working...";
        flush();
        // Update foreign keys with correct child id
        $result = $db->query("
            DELETE FROM     `{$table_prefix}core_config_data`  
            WHERE `path` = 'rewards/referral/subscription_email_template'   AND value LIKE 'rewards_referral_%'
              OR `path` = 'rewards/referral/confirmation_email_template'    AND value LIKE 'rewards_referral_%'
            ;
        ");
        echo "Fixed e-mail template issue.  Please update your e-mail templates. ";
        flush();
        //print_r($result);
        exit;
    }

    public function testRefAction() {
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table_prefix = Mage::getConfig()->getTablePrefix();

        try {
            // Update foreign keys with correct child id
            echo "Working...";
            flush();

            $t = time();

            $parent = Mage::getModel('customer/customer')->load(336);
            $child_email = "autotest" . $t . "_test@wdca.ca";
            $child_name = "Auto Test #" . $t;
            echo "Referring {$child_name}|{$child_email} by {$parent->getEmail()}...";
            flush();

            $m = $this->registerReferral2($parent, $child_email, $child_name);
            echo "Done. ID of referral model is {$m->getId()}..";
            flush();
            //print_r($result);
        } catch (Exception $e) {
            die($e);
        }
        exit;
    }

    //@nelkaake Added on Saturday June 26, 2010: Same as registerReferral but uses the child email and child name 
    //                                           (in case child is not a model yet)
    public function registerReferral2(Mage_Customer_Model_Customer $parent, $child_email, $child_name) {
        $m = Mage::getModel('rewardsref/referral')->setDoCheckData(true);
        Mage::helper('rewardsref')->log("1|" . print_r($m->getData(), true));
        if ($m->referralExists($child_email)) {
            return $m->loadByEmail($child_email);
        }
        $m->setReferralParentId($parent->getId())
                ->setReferralEmail($child_email)
                ->setReferralName($child_name);
        Mage::helper('rewardsref')->log("2|" . print_r($m->getData(), true));
        return $m->save();
    }

}