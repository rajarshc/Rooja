<?php
Class J2t_All_Model_Feed extends Mage_AdminNotification_Model_Feed {

    const XML_FEED_URL_PATH_J2T = 'system/j2t_all/feed_url_j2t';

    protected $_feedUrlJ2t;

    public function checkUpdate()
    {
        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }

        $feedData = array();

        $feedXml = $this->getFeedData();

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $feedData[] = array(
                    'severity'      => (int)$item->severity,
                    'date_added'    => $this->getDate((string)$item->pubDate),
                    'title'         => (string)$item->title,
                    'description'   => (string)$item->description,
                    'url'           => (string)$item->link,
                );
            }

            
            if ($feedData) {
                Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));
            }
            
        }
        
        $this->setLastUpdate();

        return $this;
    }


    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return Mage::app()->loadCache('j2t_notifications_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return Mage_AdminNotification_Model_Feed
     */
    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'j2t_notifications_lastcheck');
        return $this;
    }



    public function getFeedUrl()
    {
        if (is_null($this->_feedUrlJ2t)) {
            $this->_feedUrlJ2t = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
                . Mage::getStoreConfig(self::XML_FEED_URL_PATH_J2T);
        }
        return $this->_feedUrlJ2t;
    }


    public function getFeedData()
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array(
            'timeout'   => 3
        ));

        $curl->write(Zend_Http_Client::GET, $this->getFeedUrl(), '1.0');
        $data = $curl->read();
        if ($data === false) {
            return false;
        }
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        $curl->close();

        try {
            $xml  = new SimpleXMLElement($data);
        }
        catch (Exception $e) {
            return false;
        }

        return $xml;
    }

}


?>
