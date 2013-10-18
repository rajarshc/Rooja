<?php

class Magestore_Affiliateplus_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getBackendProductHtmls($productIds) {
        $productHtmls = array();
        $productIds = explode(',', $productIds);
        foreach ($productIds as $productId) {
            $productName = Mage::getModel('catalog/product')->load($productId)->getName();
            $productUrl = $this->_getUrl('adminhtml/catalog_product/edit/', array('_current' => true, 'id' => $productId));
            $productHtmls[] = '<a href="' . $productUrl . '" title="' . Mage::helper('affiliateplus')->__('View Product Detail') . '">' . $productName . '</a>';
        }
        return implode('<br />', $productHtmls);
    }

    public function getFrontendProductHtmls($productIds) {
        $productHtmls = array();
        $productIds = explode(',', $productIds);
        foreach ($productIds as $productId) {
            $product = Mage::getModel('catalog/product')->load($productId);
            $productName = $product->getName();
            $productUrl = $product->getProductUrl();
            $productHtmls[] = '<a href="' . $productUrl . '" title="' . Mage::helper('affiliateplus')->__('View Product Detail') . '">' . $productName . '</a>';
        }
        return implode('<br />', $productHtmls);
    }

    public function getStore($storeId) {
        return Mage::getModel('core/store')->load($storeId);
    }

    public function getAffiliateCustomerIds() {
        $customerIds = array();
        $collection = Mage::getModel('affiliateplus/account')->getCollection();

        foreach ($collection as $account) {
            $customerIds[] = $account->getCustomerId();
        }

        return $customerIds;
    }

    public function isBalanceIsGlobal() {
        $scope = Mage::getStoreConfig('affiliateplus/account/balance');
        if ($scope == 'store')
            return false;
        else
            return true;
    }

    public function multilevelIsActive() {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array) $modules;
        if (isset($modulesArray['Magestore_Affiliatepluslevel']) && is_object($modulesArray['Magestore_Affiliatepluslevel']))
            return $modulesArray['Magestore_Affiliatepluslevel']->is('active');
        return false;
    }

    public function isRobots() {
        $storeId = Mage::app()->getStore()->getId();
        if (!Mage::getStoreConfig('affiliateplus/action/detect_software'))
            return false;
        if (empty($_SERVER['HTTP_USER_AGENT']))
            return true;
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'])
            return true;
        define("UNKNOWN", 0);
        define("TRIDENT", 1);
        define("GECKO", 2);
        define("PRESTO", 3);
        define("WEBKIT", 4);
        define("VALIDATOR", 5);
        define("ROBOTS", 6);

        if (!isset($_SESSION["info"]['browser'])) {

            $_SESSION["info"]['browser']['engine'] = UNKNOWN;
            $_SESSION["info"]['browser']['version'] = UNKNOWN;
            $_SESSION["info"]['browser']['platform'] = 'Unknown';

            $navigator_user_agent = ' ' . strtolower($_SERVER['HTTP_USER_AGENT']);

            if (strpos($navigator_user_agent, 'linux')) :
                $_SESSION["info"]['browser']['platform'] = 'Linux';
            elseif (strpos($navigator_user_agent, 'mac')) :
                $_SESSION["info"]['browser']['platform'] = 'Mac';
            elseif (strpos($navigator_user_agent, 'win')) :
                $_SESSION["info"]['browser']['platform'] = 'Windows';
            endif;

            if (strpos($navigator_user_agent, "trident")) {
                $_SESSION["info"]['browser']['engine'] = TRIDENT;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "trident/") + 8, 3));
            } elseif (strpos($navigator_user_agent, "webkit")) {
                $_SESSION["info"]['browser']['engine'] = WEBKIT;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "webkit/") + 7, 8));
            } elseif (strpos($navigator_user_agent, "presto")) {
                $_SESSION["info"]['browser']['engine'] = PRESTO;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "presto/") + 6, 7));
            } elseif (strpos($navigator_user_agent, "gecko")) {
                $_SESSION["info"]['browser']['engine'] = GECKO;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "gecko/") + 6, 9));
            } elseif (strpos($navigator_user_agent, "robot"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "spider"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "bot"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "crawl"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "search"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "w3c_validator"))
                $_SESSION["info"]['browser']['engine'] = VALIDATOR;
            elseif (strpos($navigator_user_agent, "jigsaw"))
                $_SESSION["info"]['browser']['engine'] = VALIDATOR;
            else {
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            }
            if ($_SESSION["info"]['browser']['engine'] == ROBOTS)
                return true;
        }
        return false;
    }

    public function isProxys() {
        $useheader = Mage::helper('affiliateplus/config')->getActionConfig('detect_proxy');
        $usehostbyaddr = Mage::helper('affiliateplus/config')->getActionConfig('detect_proxy_hostbyaddr');
        $usebankip = Mage::helper('affiliateplus/config')->getActionConfig('detect_proxy_bankip');
        if ($useheader) {
            $header = Mage::helper('affiliateplus/config')->getActionConfig('detect_proxy_header');
            $arrindex = explode(',', $header);
            $headerarr = Mage::getModel('affiliateplus/system_config_source_headerdetectproxy')->getOptionList();
            foreach ($arrindex as $index) {
                if (isset($_SERVER[$headerarr[$index]])) {
                    return TRUE;
                }
            }
        }
        if ($usebankip) {
            $arrbankip = explode(';', $usebankip);
            $ip = $_SERVER['REMOTE_ADDR'];
            foreach ($arrbankip as $bankip) {
                if (preg_match('/' . $bankip . '/', $ip, $match))
                    return TRUE;
            }
        }
        if ($usehostbyaddr) {
            $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
            if ($host != $_SERVER['REMOTE_ADDR'])
                return TRUE;
        }
        return FALSE;
    }

    public function exitedCookie() {
        $usecookie = Mage::helper('affiliateplus/config')->getActionConfig('detect_cookie');
        if (!$usecookie)
            return FALSE;
        $check = FALSE;
        $days = Mage::helper('affiliateplus/config')->getActionConfig('resetclickby');
        $cookie = Mage::app()->getCookie();
        $expiredTime = Mage::helper('affiliateplus/config')->getGeneralConfig('expired_time');
        $params = Mage::app()->getRequest()->getParams();
        $link = '';
        foreach ($params as $param) {
            $link .=$param;
        }
        if ($expiredTime)
            $cookie->setLifeTime(intval($expiredTime) * 86400);
        $date = New DateTime(now());
        $date->modify(-$days . 'days');
        $datemodifyreset = $date->format('Y-m-d');
        $datenow = date('Y-m-d');
        $dateset = $cookie->get($link);
        if ($datemodifyreset <= $dateset && $dateset) {
            $check = TRUE;
        } else {
            $cookie->set($link, $datenow);
        }
        return $check;
    }
	
	/*
	** affiliate type is profit
	**/
	
	public function affiliateTypeIsProfit()
	{
		if(Mage::helper('affiliateplus/config')->getCommissionConfig('affiliate_type') == 'profit')
			return true;
		return false;
	}

}