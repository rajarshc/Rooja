<?php

class Aitoc_Aitsys_Model_Notification_News extends Aitoc_Aitsys_Abstract_Model
{
    
    const CACHE_LIVE_TIME = 86400;
    
    protected $_news = array();
    
    protected $_cacheKey = 'AITOC_AITSYS_NEWS';
    
    protected $_method = 'getNews';
    
    protected $_type = 'news';
    
    /**
    * 
    * @return Aitoc_Aitsys_Model_Mysql4_News
    */
    protected function _getNewsResource()
    {
        return Mage::getResourceSingleton('aitsys/news');
    }
    
    /**
    * 
    * @return Aitoc_Aitsys_Model_Mysql4_News_Collection
    */
    protected function _getNewsCollection()
    {
        return Mage::getResourceModel('aitsys/news_collection')->addTypeFilter($this->_type);
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification_News
     */
    public function loadData()
    {
        try
        {
            $latest = $this->_getNewsResource()->getLatest($this->_type);
        }
        catch (Exception $exc)
        {
            Mage::logException($exc);
            return $this;
        }
        if (!$latest->isOld())
        {
            foreach ($this->_getNewsCollection() as $model)
            {
                $this->addNews(array(
                    'title' => $model->getTitle() ,
                    'content' => $model->getDescription()
                ));
            }
            return $this;
        }
        try
        {
            $this->tool()->testMsg('Load from service');
            $service = $this->tool()->platform()->getService()->setMethodPrefix('aitnewsad');
            $this->tool()->testMsg('Get data from:'.$service->getServiceUrl());
            $service->connect();
            if ($news = $service->{$this->_method}())
            {
                $this->tool()->testMsg($news);
                foreach($news as $item)
                {
                    $this->addNews($item);
                }
            }
            $service->disconnect();
            $this->saveData();
        }
        catch (Exception $exc)
        {
            Mage::logException($exc);
            $this->tool()->testMsg($exc);
        }

        $this->tool()->testMsg("Loaded news:");
        $this->tool()->testMsg($this->_news);
        return $this;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Notification_News
     */
    public function saveData()
    {
        $this->_getNewsResource()->clear($this->_type);
        if (!$this->_news)
        {
            Mage::getModel('aitsys/news')->setData(array(
                'date_added' => date('Y-m-d H:i:s') ,
                'title' => '' ,
                'description' => '' ,
                'type' => $this->_type
            ))->save();
        }
        else foreach ($this->_news as $item)
        {
            Mage::getModel('aitsys/news')->setData(array(
                'date_added' => date('Y-m-d H:i:s') ,
                'title' => $item['title'] ,
                'description' => $item['content'] ,
                'type' => $this->_type
            ))->save();
        }
        return $this;
    }
    
    /**
     * 
     * @param $item
     * @return Aitoc_Aitsys_Model_Notification_News
     */
    public function addNews( $item )
    {
        if ($item && !empty($item['content']))
        {
            $this->_news[] = $item;
        }
        return $this;
    }
    
    public function getNews()
    {
        return $this->_news;
    }
    
}