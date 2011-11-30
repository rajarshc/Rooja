<?php
class Idev_OneStepCheckout_Block_Links extends Mage_Core_Block_Template
{
    public function addCheckoutLink()
    {
        if (!$this->helper('checkout')->canOnepageCheckout()) {
            return $this;
        }
        if ($parentBlock = $this->getParentBlock()) {
            $text = $this->__('Checkout');
            $parentBlock->addLink($text, 'onestepcheckout', $text, true, array('_secure'=>true), 60, null, 'class="top-link-onestepcheckout"');
        }
        return $this;
    }

}