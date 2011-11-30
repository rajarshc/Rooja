<?php
class Idev_OneStepCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function checkValid($observer)
    {
        $layout = Mage::app()->getLayout();
        $content = $layout->getBlock('content');
        $block = $layout->createBlock('onestepcheckout/valid');
        $content->insert($block);
    }

    public function setCustomerComment($observer)
    {
        $enableComments = Mage::getStoreConfig('onestepcheckout/exclude_fields/enable_comments');
        $enableCommentsDefault = Mage::getStoreConfig('onestepcheckout/exclude_fields/enable_comments_default');
        $enableFeedback = Mage::getStoreConfig('onestepcheckout/feedback/enable_feedback');
        $orderComment = $this->_getRequest()->getPost('onestepcheckout_comments');
        $orderComment = trim($orderComment);

        if($enableComments && !$enableCommentsDefault) {
            if ($orderComment != ""){
                $observer->getEvent()->getOrder()->setOnestepcheckoutCustomercomment($orderComment);
            }
        }

        if($enableComments && $enableCommentsDefault) {
            if ($orderComment != ""){
                $observer->getEvent()->getOrder()->setState($observer->getEvent()->getOrder()->getStatus(), true, $orderComment, false );
            }
        }

        if($enableFeedback){

            $feedbackValues = unserialize(Mage::getStoreConfig('onestepcheckout/feedback/feedback_values'));
            $feedbackValue = $this->_getRequest()->getPost('onestepcheckout-feedback');
            $feedbackValueFreetext = $this->_getRequest()->getPost('onestepcheckout-feedback-freetext');

            if(!empty($feedbackValue)){
                if($feedbackValue!='freetext'){
                    $feedbackValue = $feedbackValues[$feedbackValue]['value'];
                } else {
                    $feedbackValue = $feedbackValueFreetext;
                }

                $observer->getEvent()->getOrder()->setOnestepcheckoutCustomerfeedback($feedbackValue);
            }

        }
    }

    public function isRewriteCheckoutLinksEnabled()
    {
        return Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links');
    }

    /**
     * If we are using enterprise wersion or not
     * @return int
     */
    public function isEnterPrise(){
        return (int)is_object(Mage::getConfig()->getNode('global/models/enterprise_enterprise'));
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array())
    {
        $json = Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        /* @var $inline Mage_Core_Model_Translate_Inline */
        $inline = Mage::getSingleton('core/translate_inline');
        if ($inline->isAllowed()) {
            $inline->setIsJson(true);
            $inline->processResponseBody($json);
            $inline->setIsJson(false);
        }

        return $json;
    }

    /**
     * Check if value is only -
     * @param mixed $value
     */
    public function clearDash($value = null){
        if($value == '-'){
            return '';
        }
        return $value;
    }
}
