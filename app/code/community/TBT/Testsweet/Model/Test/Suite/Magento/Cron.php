<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Cron extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Cron');
    }

    public function getDescription() {
        return $this->__('Check cron is running scheduled tasks');
    }

    protected function generateSummary() {

        $now = Mage::getModel('core/date')->timestamp(time());
        $timestamp = Mage::getModel('testsweet/observer_crontest')->getTimestamp();
        $isworking = Mage::getModel('testsweet/observer_crontest')->isWorking();

        if ($isworking)
            $this->addPass($this->__("Cron timestamp: [%s], current timestamp: [%s]", $timestamp, $now));
        else
            $this->addFail($this->__("Cron is not active. Crontest last timestamp: [%s], current system timestamp: [%s]", $timestamp, $now), $this->__("Check crontab has: */5 * * * * /bin/sh /[magento_path]/cron.sh"));
    }

}
