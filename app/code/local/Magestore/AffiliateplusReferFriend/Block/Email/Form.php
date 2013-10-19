<?php

class Magestore_AffiliateplusReferFriend_Block_Email_Form
    extends Magestore_AffiliateplusReferFriend_Block_Refer
{
    public function getDefaultEmailContent() {
        $content = $this->_getHelper()->getReferConfig('email_content');
        $url = $this->getRequest()->getParam('url');
        $url = $url ? $url : $this->getPersonalUrl();
        return str_replace(
            array(
                '{{store_name}}',
                '{{personal_url}}',
                '{{account_name}}'
            ), array(
                Mage::app()->getStore()->getFrontendName(),
                $url,
                $this->getAccount()->getName()
            ), $content
        );
    }
}
