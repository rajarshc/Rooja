<?php

class TBT_Testsweet_Model_Observer_Crontest {

    public function run() {
        $now = Mage::getModel('core/date')->timestamp(time());
        // TODO: perhaps there is a smarter way to save this timestamp?
        Mage::getConfig()->saveConfig('testsweet/crontest/timestamp', $now);
        //Mage::getConfig()->setNode('testsweet/crontest/timestamp', $now);
        
        //Mage::app()->reinitStores();
        //Mage::getConfig()->reinit();
    }

    public function getTimestamp() {
        $timestamp = Mage::getStoreConfig('testsweet/crontest/timestamp');
        return $timestamp;
    }

    public function isWorking() {
        $timestamp = $this->getTimestamp();
        if (empty($timestamp))
            return false;

        $now = Mage::getModel('core/date')->timestamp(time());

        //$timestamp_date;// = new DateTime($timestamp, DateTimeZone::UTC);
        //$now_date = new DateTime(null, DateTimeZone::UTC);
        //$datediff = $timestamp_date->diff($now_date);

        $seconds = $now - $timestamp;


        //$now = Mage::getModel('core/date')->timestamp(time());
        //Mage::getStoreConfig('testsweet_crontest')->getLastRun(time());
        // if the timestam is only < 15 minuets old
        return $seconds < (60 * 15);

        //return Mage::registry('testsweet_crontest_time');
    }

}