<?php

class TBT_Rewards_Model_Test_Suite_Rewards_Birthday_Points extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Check birthday points');
    }

    public function getDescription() {
        return $this->__('Check if any customers have not received birthday points from any of the active rules.');
    }

    protected function generateSummary()
    {
        // get all the active birthday rules
        $rules = Mage::getModel('rewards/special')->getCollection()
            ->addFieldToFilter('is_active', '1');
        
        $birthdayRules = array();
        $actionType = TBT_Rewards_Model_Birthday_Action::ACTION_CODE;
        foreach ($rules as $rule) {
            //Unserializes the conditions and checks if it is a birthday rule
            $ruleConditions = Mage::helper('rewards')->unhashIt($rule->getConditionsSerialized());
            if (is_array($ruleConditions)) {
                if (in_array($actionType, $ruleConditions)) {
                    $birthdayRules[] = $rule;
                }
            } else if ($rule_conditions == $actionType) {
                $birthdayRules[] = $rule;
            }
        }
        
        // get all the customers with birthdays
        $customers = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToSelect('dob', 'inner');
        $customersMissed = array();
        
        $now = date("Y-m-d", strtotime(Mage::getModel('core/date')->gmtDate()));
        foreach ($customers as $customer) {
            // this is their year-agnostic birthday
            $birthday = date("m-d", strtotime($customer->getDob()));
            foreach ($birthdayRules as $rule) {
                $customerGroupIds = explode(",", $rule->getCustomerGroupIds());
                if (array_search($customer->getGroupId(), $customerGroupIds) === false) {
                    continue;
                }
                
                // the effective end is either end-date of the rule, or NOW if the rule ends in the future
                $effectiveEnd = $rule->getToDate() ? min($rule->getToDate(), $now) : $now;
                $yearStart = date("Y", strtotime($rule->getFromDate()));
                $yearEnd = date("Y", strtotime($effectiveEnd));
                $yearDiff = $yearEnd - $yearStart; // roughly how many years the rule has lasted
                
                $birthdays = 0;
                // loop through each year the rule was supposedly active
                for ($i = 0; $i <= $yearDiff; $i++) {
                    // make all dates (rule start, rule end, birthday) relative to the "current" year
                    $year = $yearStart + $i;
                    $start = max("{$year}-01-01", $rule->getFromDate());
                    $end = min("{$year}-12-31", $effectiveEnd);
                    $tempBd = "{$year}-{$birthday}";
                    
                    // check if the customer's birthday fell within the span (could be bounded by rule start or end)
                    if ($tempBd >= $start && $tempBd <= $end) {
                        $birthdays++; // increment the number of birthdays the customer was SUPPOSED to have
                    }
                }
                
                // fetch all the birthday transfers the customer HAS received (hopefully for this rule).
                // it's not a sure thing, matching transfer with rule based on quantity and currency,
                // but we don't have much of a better method
                $bdTransfers = Mage::getResourceModel('rewards/transfer_collection')
                    ->addFieldToFilter('customer_id', $customer->getId())
                    ->addFieldToFilter('reason_id', TBT_Rewards_Model_Birthday_Reason::REASON_TYPE_ID)
                    ->addFieldToFilter('currency_id', $rule->getPointsCurrencyId())
                    ->addFieldToFilter('quantity', $rule->getPointsAmount());
                $missedBirthdays = $birthdays - count($bdTransfers); // how many birthday rewards have we missed?
                $missedPoints = $rule->getPointsAmount() * $missedBirthdays;
                
                // add a FAIL for each customer that has not earned deserved points for at least one birthday
                if ($missedBirthdays > 0) {
                    $customersMissed[] = $customer->getId();
                    $this->addFail("Customer #{$customer->getId()} ({$customer->getEmail()}) missed "
                        . "{$missedBirthdays} birthdays on Rule #{$rule->getId()} ({$rule->getName()}), "
                        . "totalling {$missedPoints} points.");
                }
            }
        }
        
        $customersMissed = array_unique($customersMissed);
        if (count($customersMissed) == 0) {
            $this->addPass("No birthdays have been missed on any of the active rules.");
        } else {
            // add a notice at the end (if any customer is missing birthday points) with a link to auto-award them
            $url = Mage::getModel('adminhtml/url')->getUrl('rewardsadmin/manage_special/fixBirthdays');
            $this->addNotice("A total of " . count($customersMissed) . " customers have missed their birthday points.",
                "Click <a href='{$url}'>here</a> to automatically reward all these customers now, what they " .
                "should have received on their birthday.");
        }
        
        return $this;
    }

}
