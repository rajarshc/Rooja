<?php

class Magestore_Affiliateplus_Model_Action extends Mage_Core_Model_Abstract {

    protected $_eventPrefix = 'affiliateplus_action';
    protected $_eventObject = 'affiliateplus_action';

    public function _construct() {
        parent::_construct();
        $this->_init('affiliateplus/action');
    }

    /**
     * get Helper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    public function _getHelper() {
        return Mage::helper('affiliateplus/config');
    }

    public function getTrafficInfo($type, $name, $title) {
        $info = array();
        $session = Mage::getSingleton('affiliateplus/session');
        $date = date('Y-m-d');
        $week = date('W');
        $month = date('m');
        $year = date('Y');
        if ($session->isLoggedIn()) {
            $account = $session->getAccount();
            $dateUniqueCollection = $this->getCollection()
                    ->addFieldToFilter('account_id', $account->getId())
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('created_date', $date)
                    ->addFieldToFilter('is_unique', 1)
            ;

            $dateRawCollection = $this->getCollection()
                    ->addFieldToFilter('account_id', $account->getId())
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('created_date', $date)
            //->addFieldToFilter('is_unique', 0)
            ;
            if ($this->_getHelper()->getSharingConfig('balance') == 'store') {
                $dateUniqueCollection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
                $dateRawCollection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
            }
            $dateRawCollection->getSelect()
                    ->group(array('created_date'))
                    ->columns(array('totals_raw' => 'SUM(totals)'));
            $dateRaw = $dateRawCollection->getFirstItem()->getTotalsRaw();
            if (!$dateRaw)
                $dateRaw = 0;
            $info['today'] = $dateUniqueCollection->getSize() . '/' . $dateRaw;
            /* ---------------------------------------------------------------- */
            $weekUniqueCollection = $this->getCollection()
                    ->addFieldToFilter('account_id', $account->getId())
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('week(created_date, 1)', $week)
                    ->addFieldToFilter('is_unique', 1)
            ;
            $weekRawCollection = $this->getCollection()
                    ->addFieldToFilter('account_id', $account->getId())
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('week(created_date, 1)', $week)
            //->addFieldToFilter('is_unique', 0)
            ;
            if ($this->_getHelper()->getSharingConfig('balance') == 'store') {
                $weekUniqueCollection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
                $weekRawCollection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
            }
            $weekRawCollection->getSelect()
                    ->group(array('week(created_date, 1)'))
                    ->columns(array('totals_raw' => 'SUM(totals)'));
            $weekRaw = $weekRawCollection->getFirstItem()->getTotalsRaw();
            if (!$weekRaw)
                $weekRaw = 0;
            $info['week'] = $weekUniqueCollection->getSize() . '/' . $weekRaw;
            /* ---------------------------------------------------------------- */
            $monthUniqueCollection = $this->getCollection()
                    ->addFieldToFilter('account_id', $account->getId())
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('month(created_date)', $month)
                    ->addFieldToFilter('is_unique', 1)
            ;
            $monthRawCollection = $this->getCollection()
                    ->addFieldToFilter('account_id', $account->getId())
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('month(created_date)', $month)
            //->addFieldToFilter('is_unique', 0)
            ;
            if ($this->_getHelper()->getSharingConfig('balance') == 'store') {
                $monthUniqueCollection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
                $monthRawCollection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
            }
            $monthRawCollection->getSelect()
                    ->group(array('month(created_date)'))
                    ->columns(array('totals_raw' => 'SUM(totals)'));
            $monthRaw = $monthRawCollection->getFirstItem()->getTotalsRaw();
            if (!$monthRaw)
                $monthRaw = 0;
            $info['month'] = $monthUniqueCollection->getSize() . '/' . $monthRaw;
            /* ---------------------------------------------------------------- */
            $yearUniqueCollection = $this->getCollection()
                    ->addFieldToFilter('account_id', $account->getId())
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('year(created_date)', $year)
                    ->addFieldToFilter('is_unique', 1)
            ;
            $yearRawCollection = $this->getCollection()
                    ->addFieldToFilter('account_id', $account->getId())
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('year(created_date)', $year)
            //->addFieldToFilter('is_unique', 0)
            ;
            if ($this->_getHelper()->getSharingConfig('balance') == 'store') {
                $yearUniqueCollection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
                $yearRawCollection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
            }
            $yearRawCollection->getSelect()
                    ->group(array('year(created_date)'))
                    ->columns(array('totals_raw' => 'SUM(totals)'));
            $yearRaw = $yearRawCollection->getFirstItem()->getTotalsRaw();
            if (!$yearRaw)
                $yearRaw = 0;
            $info['year'] = $yearUniqueCollection->getSize() . '/' . $yearRaw;
            /* ---------------------------------------------------------------- */
            $allUniqueCollection = $this->getCollection()
                    ->addFieldToFilter('account_id', $account->getId())
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('is_unique', 1)
            ;
            $allRawCollection = $this->getCollection()
                    ->addFieldToFilter('account_id', $account->getId())
                    ->addFieldToFilter('type', $type)
            //->addFieldToFilter('is_unique', 0)
            ;
            if ($this->_getHelper()->getSharingConfig('balance') == 'store') {
                $allUniqueCollection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
                $allRawCollection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
            }
            $allRawCollection->getSelect()
                    ->group(array('account_id'))
                    ->columns(array('totals_raw' => 'SUM(totals)'));
            $allRaw = $allRawCollection->getFirstItem()->getTotalsRaw();
            if (!$allRaw)
                $allRaw = 0;
            $info['all'] = $allUniqueCollection->getSize() . '/' . $allRaw;
            $info['name'] = $name;
            $info['title'] = $title;
            return $info;
        }
    }

    public function loadExist($accountId, $bannerId, $type, $storeId, $date, $ip, $domain) {
        $collection = $this->getCollection()
                ->addFieldToFilter('account_id', $accountId)
                ->addFieldToFilter('banner_id', $bannerId)
                ->addFieldToFilter('type', $type)
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('created_date', $date)
                ->addFieldToFilter('ip_address', $ip)
                ->addFieldToFilter('domain', $domain)
        ;
        if ($collection->getSize()) {
            return $this->load($collection->getFirstItem()->getId());
        }
        return $this;
    }

    public function saveAction($accountId, $bannerId, $type, $storeId, $isUnique, $ipAddress, $domain, $landing_page) {
        $date = New DateTime(now());
        $collection = $this->getCollection()
                ->addFieldToFilter('account_id', $accountId)
                ->addFieldToFilter('banner_id', $bannerId)
                ->addFieldToFilter('type', $type)
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('domain', $domain)
                ->addFieldToFilter('landing_page', $landing_page)
                ->addFieldToFilter('ip_address', $ipAddress)
                ->addFieldToFilter('created_date', $date->format('Y-m-d'));
        if ($collection->getSize()) {
            $action = $collection->getFirstItem();
        } else {
            $action = $this;
        }

        $action->setAccountId($accountId)
                ->setBannerId($bannerId)
                ->setType($type)
                ->setIpAddress($ipAddress)
                ->setTotals($action->getTotals() + 1)
                ->setCreatedDate($date->format('Y-m-d'))
                ->setUpdatedTime(now())
                ->setDomain($domain)
                ->setLandingPage($landing_page)
                ->setStoreId($storeId);
        if ($isUnique)
            $action->setIsUnique($isUnique);

        $account = Mage::getModel('affiliateplus/account')->load($accountId);
        $action->setAccountEmail($account->getEmail());
        if ($bannerId) {
            $banner = Mage::getModel('affiliateplus/banner')->load($bannerId);
            $action->setBannerTitle($banner->getTitle());
        }

        try {
            if ($directLink = Mage::app()->getRequest()->getParam('affiliateplus_direct_link')) {
                $action->setData('direct_link', $directLink);
            }
            if ($domain = $action->getDomain()) {
                $action->setReferer($this->refineDomain($domain));
            }
            $action->save();
        } catch (Exception $e) {
            
        }
        return $action;
    }

    public function refineDomain($domain) {
        $parseUrl = parse_url(trim($domain));
        $domain = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
        return $domain;
    }

    public function checkIpClick($ipAddress, $account_id, $domain, $banner_id, $type, $storeId = NULL) {
        $days = $this->_getHelper()->getActionConfig('resetclickby');
        if (!$storeId)
            $storeId = $storeId = Mage::app()->getStore()->getId();
        $collection = $this->getCollection()
                ->addFieldToFilter('type', $type)
                ->addFieldToFilter('account_id', $account_id)
                ->addFieldToFilter('domain', $domain)
                ->addFieldToFilter('banner_id', $banner_id)
                ->addFieldToFilter('ip_address', $ipAddress)
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('is_unique', 1);
        if ($days) {
            $date = New DateTime(now());
            $date->modify(-$days . 'days');
            $collection->addFieldToFilter('created_date', array('from' => $date->format('Y-m-d')));
        }
        if ($collection->getSize()) {
            return 0;
        }
        return 1;
    }

}