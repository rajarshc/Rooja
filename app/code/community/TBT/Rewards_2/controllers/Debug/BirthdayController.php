<?php

/**
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
require_once ("AbstractController.php");

class TBT_Rewards_Debug_BirthdayController extends TBT_Rewards_Debug_AbstractController {

    public function indexAction() {
        echo "<h2>This tests birthdays </h2>";
        echo "<p>set bithdays in backend > customer > (select customer) > account information</p>";
        echo "<a href='" . Mage::getUrl('rewards/debug_birthday/displayBirthdaysToday') . "'>Today's birhtday information - </a>Display information about todays birthdays and the rules that are valid.<BR />";
        echo "<a href='" . Mage::getUrl('rewards/debug_birthday/displayTransfersAssociatedWithBirthday') . "'>Display all birthday point transfers - </a>Display all the point transfers that have a reason matching birthday.<BR />";
        echo "<a href='" . Mage::getUrl('rewards/debug_birthday/proccess') . "'>Proccess birthdays</a> - This will do the same as the cron task but also display helpfull output.<BR />";
        echo "<a href='" . Mage::getUrl('rewards/debug_birthday/runCron') . "'>Force daily birthday cron to run once</a> - This will silently email, logs, and give points to customers.<BR />";

        exit();
    }

    public function runCronAction() {
        Mage::getSingleton('rewards/birthday_observer')->checkCustomerBirthdays(new Varien_Object());
        return $this;
    }

    public function proccessAction() {
        echo "<h2>Proccessing Today's birthday</h2>";
        echo "<h4>Magento's date: " . $this->_getHelper()->getMagentoDate() . "</h4>";
        echo "<h4>Min days between rewards: " . $this->_getHelper()->getMinDaysBetweenReward(null) . "</h4>";

        $proccessed = $this->_getProccess()->proccessAllCustomers();
        echo "Proccessed: $proccessed birthdays";

        return $this;
    }

    public function displayBirthdaysTodayAction() {
        echo "<h2>Today's birthdays</h2>";
        echo "<h4>Magento's date: " . $this->_getHelper()->getMagentoDate() . "</h4>";
        echo "<h4>Min days between rewards: " . $this->_getHelper()->getMinDaysBetweenReward(null) . "</h4>";

        $customers = $this->_getHelper()->getCustomersWithBirthdaysToday();
        foreach ($customers as $customer) {
            echo "<h4>CustomerId: " . $customer->getId() . "</h4>";
            echo '<pre>';
            print_r($customer->getData());
            echo '</pre>';
            $rules = $this->_getValidator()->getApplicableRulesOnBirthdayForCustomer($customer);
            if (count($rules) > 0) {
                echo '<h4>Will recive points for the point sum of these rules</h4>';
                foreach ($rules as $rule) {
                    echo '<pre>';
                    print_r($rule->getData());
                    echo '</pre>';
                }
            }
        }

        return $this;
    }

    public function displayTransfersAssociatedWithBirthdayAction() {
        echo "<h2>Transfers Associated With Birthday</h2>";
        echo "<h4>Magento's date: " . $this->_getHelper()->getMagentoDate() . "</h4>";
        echo "<h4>Min days between rewards: " . $this->_getHelper()->getMinDaysBetweenReward(null) . "</h4>";

        $transfers = $this->_getTransfer()->getTransfersAssociatedWithBirthday();
        foreach ($transfers as $transfer) {
            echo '<pre>';
            print_r($transfer->getData());
            echo '</pre>';
        }

        return $this;
    }

    /**
     *
     * @return TBT_Rewards_Model_Birthday_Helper
     */
    protected function _getHelper() {
        return Mage::helper('rewards/birthday');
    }

    /**
     * Get the transfer module
     * @return TBT_Rewards_Model_Birthday_Transfer 
     */
    public function _getTransfer() {
        return Mage::getSingleton('rewards/birthday_transfer');
    }

    /**
     * Get the notify module
     * @return TBT_Rewards_Model_Birthday_Proccess 
     */
    public function _getProccess() {
        return Mage::getSingleton('rewards/birthday_proccess');
    }

    /**
     * Get the validator module
     * @return TBT_Rewards_Model_Birthday_Validator
     */
    public function _getValidator() {
        return Mage::getSingleton('rewards/birthday_validator');
    }

}