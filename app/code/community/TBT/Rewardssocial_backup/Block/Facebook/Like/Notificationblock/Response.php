<?php

class TBT_Rewardssocial_Block_Facebook_Like_Notificationblock_Response extends TBT_Rewardssocial_Block_Abstract {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
    

    protected function _toHtml() {
        $response_html = "";
        
        // If no predicted points exist, don't display anything.
        if(!$this->getHasPredictedLikePoints()) return "&nbsp;";
        
        $msg = $this->getMsg();
        
        $text =  $msg->getText();
        
        $text = $this->getTextWithLoginLinks($text);
        
        if($msg->getType() == Mage_Core_Model_Message::ERROR) {
            $response_html = "<div class='facebook-like-rewards-notification-msg facebook-like-rewards-notification-error'>" . $text . "</div>";
        } else {
            $response_html = "<div class='facebook-like-rewards-notification-msg'>" . $text . "</div>";
        }
        
        return $response_html;
    }
}