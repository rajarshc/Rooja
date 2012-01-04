<?php

class TBT_Rewardssocial_Block_Facebook_Like_Rewards extends TBT_Rewardssocial_Block_Abstract {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
    

    public function getRewardUrl() {
        //$params = array();
        $url = $this->getUrl('rewardssocial/facebook_like/onLike');
        
        return $url;
    }
    

}