<?php

class Aitoc_Aitsys_Model_News extends Aitoc_Aitsys_Abstract_Model
{
    
    protected function _construct()
    {
        $this->_init('aitsys/news');
    }
    
    public function isOld()
    {
        return strtotime($this->getDateAdded()) < time()-86400;
    }
    
}