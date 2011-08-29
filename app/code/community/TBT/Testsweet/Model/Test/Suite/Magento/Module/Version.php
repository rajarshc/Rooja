<?php

class TBT_Testsweet_Model_Test_Suite_Magento_Module_Version extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Magento - Modules - Version information');
    }

    public function getDescription() {
        return $this->__('List module information.');
    }

    protected function generateSummary() {

        $this->addNotice($this->__("Magento version %s", Mage::getVersion()));

        $defaultModules = $this->getDefaultModules();
        $modules = (array) Mage::getConfig()->getNode('modules')->children();

        foreach ($modules as $key => $modele) {
            // mark none default modul es with a star
            $markModule = ' ';
            if (!in_array($key, $defaultModules)) {
                $markModule = '*';
            }
            $nactive = str_pad("Active: {$modele->active}", 15);
            $ncodePool = str_pad("CodePool: {$modele->codePool}", 20);
            $nversion = str_pad("Version: {$modele->version}", 20);
            $nmodule = str_pad("Modele:{$markModule}{$key}", 20);
            if ($markModule == '*') {
                $this->addNotice("$nactive $ncodePool $nversion $nmodule");
            } else {
                $this->addPass("$nactive $ncodePool $nversion $nmodule");
            }
        }
    }

    public static function getDefaultModules() {
        $return = array(
            'Enterprise_AdminGws',
            'Enterprise_Banner',
            'Enterprise_CatalogEvent',
            'Enterprise_CatalogPermissions',
            'Enterprise_Checkout',
            'Enterprise_Cms',
            'Enterprise_Customer',
            'Enterprise_CustomerBalance',
            'Enterprise_CustomerSegment',
            'Enterprise_Enterprise',
            'Enterprise_GiftCard',
            'Enterprise_GiftCardAccount',
            'Enterprise_GiftRegistry',
            'Enterprise_GiftWrapping',
            'Enterprise_Invitation',
            'Enterprise_License',
            'Enterprise_Logging',
            'Enterprise_PageCache',
            'Enterprise_Pbridge',
            'Enterprise_Pci',
            'Enterprise_Reminder',
            'Enterprise_Reward',
            'Enterprise_SalesArchive',
            'Enterprise_Search',
            'Enterprise_Staging',
            'Enterprise_TargetRule',
            'Enterprise_WebsiteRestriction',
            'Find_Feed',
            'Mage_Admin',
            'Mage_Adminhtml',
            'Mage_AdminNotification',
            'Mage_All',
            'Mage_Api',
            'Mage_Authorizenet',
            'Mage_Backup',
            'Mage_Bundle',
            'Mage_Catalog',
            'Mage_CatalogIndex',
            'Mage_CatalogInventory',
            'Mage_CatalogRule',
            'Mage_CatalogSearch',
            'Mage_Centinel',
            'Mage_Checkout',
            'Mage_Cms',
            'Mage_Compiler',
            'Mage_Connect',
            'Mage_Contacts',
            'Mage_Core',
            'Mage_Cron',
            'Mage_Customer',
            'Mage_Dataflow',
            'Mage_Directory',
            'Mage_Downloadable',
            'Mage_Eav',
            'Mage_GiftMessage',
            'Mage_GoogleAnalytics',
            'Mage_GoogleBase',
            'Mage_GoogleCheckout',
            'Mage_GoogleOptimizer',
            'Mage_ImportExport',
            'Mage_Index',
            'Mage_Install',
            'Mage_Log',
            'Mage_Media',
            'Mage_Newsletter',
            'Mage_Ogone',
            'Mage_Page',
            'Mage_PageCache',
            'Mage_Paygate',
            'Mage_Payment',
            'Mage_Paypal',
            'Mage_PaypalUk',
            'Mage_Persistent',
            'Mage_Poll',
            'Mage_ProductAlert',
            'Mage_Rating',
            'Mage_Reports',
            'Mage_Review',
            'Mage_Rss',
            'Mage_Rule',
            'Mage_Sales',
            'Mage_SalesRule',
            'Mage_Sendfriend',
            'Mage_Shipping',
            'Mage_Sitemap',
            'Mage_Tag',
            'Mage_Tax',
            'Mage_Usa',
            'Mage_Weee',
            'Mage_Widget',
            'Mage_Wishlist',
            'Mage_XmlConnect',
            'Phoenix_Moneybookers',
        );
        return $return;
    }

}
