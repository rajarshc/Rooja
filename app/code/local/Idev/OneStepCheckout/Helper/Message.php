<?php
class Idev_OneStepCheckout_Helper_Message extends Mage_GiftMessage_Helper_Message
{
    public function getInline($type, Varien_Object $entity, $dontDisplayContainer=false)
    {
        if (in_array($type, array('onepage_checkout','multishipping_adress'))) {
            if (!$this->isMessagesAvailable('items', $entity)) {
                return '';
            }
        } elseif (!$this->isMessagesAvailable($type, $entity)) {
            return '';
        }

        $block = Mage::getSingleton('core/layout')->createBlock('giftmessage/message_inline')
        ->setId('giftmessage_form_' . $this->_nextId++)
        ->setDontDisplayContainer($dontDisplayContainer)
        ->setEntity($entity)
        ->setType($type)
        ->setTemplate('onestepcheckout/gift_message.phtml');

        //echo '<pre>' . print_r(get_class_methods($block),1) . '</pre>';

        return $block->toHtml();
    }
}