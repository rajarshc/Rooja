<?php
class Idev_OneStepCheckout_Model_Observers_SortFields extends Mage_Core_Model_Abstract {

    /**
     * Fields that we don't wan't to extract from DOM and handle as whole
     * @var Array
     */
    public $blackList = array('day', 'month', 'year', 'region' );

    /**
     *
     * @param Varien_Event_Observer $observer
     */
    public function applyFieldOrder(Varien_Event_Observer $observer) {

        //variables from Observer
        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();
        $html = $transport->getHtml();
        $quote = $block->getQuote();

        //config varialbes
        $sort = Mage::getStoreConfig('onestepcheckout/sortordering_fields');
        $whitelist = $this->getWhiteList(Mage::getStoreConfig('onestepcheckout/exclude_fields'));

        //deal with billing fields
        if ($block instanceof Mage_Checkout_Block_Onepage_Billing) {
            $html = $this->sortFields('billing', $html, $sort, $whitelist);
        }

        //deal with shipping fields
        if ($block instanceof Mage_Checkout_Block_Onepage_Shipping) {
            $html = $this->sortFields('shipping', $html, $sort, $whitelist);
        }

        //set the result if we succeed
        if (! empty($html)) {
            $transport->setHtml($html);
        }

        return $this;
    }

    /**
     *
     * @param array $config
     * @return array
     */
    public function getWhiteList(Array $config) {
        $prefix = 'exclude_';
        $newConfig = array();
        foreach ($config as $key => $value) {

            //@TODO rename this variable in config zip > postcode
            $key = str_replace('zip', 'postcode', $key);
            if (strstr($key, $prefix) && ! empty($value)) {
                $newConfig[] = str_replace($prefix, '', $key);
            }
        }
        return $newConfig;
    }

    /**
     *
     * @param String $type
     * @param String $html
     * @return String
     */
    public function sortFields(String $type, String $html, Array $sort, Array $whitelist) {
        $allFields = array();
        //get the html and load it to DOMelement
        $newHtml = new DOMDocument();
        $newHtml->loadHTML($html);
        //we want to query and find elements from this
        $x = new DOMXPath($newHtml);

        $xQuery = $x->query('//input[contains(@id, "billing:taxvat")]');
        if(!empty($xQuery->length)){
            $div = $newHtml->createElement('div');
            $div->setAttribute('class','field');
            $liNode = $xQuery->item(0)->parentNode->parentNode;

            $li = $liNode->cloneNode(true);
            $lenght = $li->childNodes->length;
            for($i = 0; $i <= $lenght; $i++) {
               $div->appendChild($li->childNodes->item($i));
               $liNode->removeChild($liNode->childNodes->item($i));
            }

            $liNode->appendChild($div);
            $liNode->setAttribute('class','fields');
        }

        //get all regular fields that have class field;
        $xQuery = $x->query('//div[contains(@class, "field")]');
        //do it backwards cause NodeList will change active tree, ruins foreach
        for($i = $xQuery->length; -- $i >= 0;) {
            $field = $xQuery->item($i);
            $xpe = $x->query($field->getNodePath() . '//*[contains(@id, "' . $type . '")]["0"]');
            for($n = $xpe->length; -- $n >= 0;) {
                $attributeId = str_replace($type . ':', '', $xpe->item($n)->getAttribute('id'));
                if (in_array($attributeId, $whitelist)) {
                    $parentNode = $field->parentNode;
                    $parentNode->removeChild($field);
                    if (! $parentNode->hasChildNodes()) {
                        $parentNode->parentNode->removeChild($parentNode);
                    }
                    continue;
                }
                if (! in_array($attributeId, $this->blackList)) {
                    if(in_array($attributeId, array('customer_password','confirm_password'))){
                        $allFields['password'][$attributeId] = $field->cloneNode(true);
                    } else {
                        $allFields[$attributeId] = $field->cloneNode(true);
                    }
                    $parentNode = $field->parentNode;
                    $parentNode->removeChild($field);
                    if (! $parentNode->hasChildNodes()) {
                        $parentNode->parentNode->removeChild($parentNode);
                    }
                }

            }
        }

        //get all tavat fields
        $xQuery = $x->query('//li[contains(@class, "wide")]');
        //do it backwards cause NodeList will change active tree, ruins foreach
        for($i = $xQuery->length; -- $i >= 0;) {
            $field = $xQuery->item($i);
            $xpe = $x->query($field->getNodePath() . '//*[contains(@id, "' . $type . '")]["0"]');
            for($n = $xpe->length; -- $n >= 0;) {
                $attributeId = str_replace($type . ':', '', $xpe->item($n)->getAttribute('id'));
                if (in_array('address', $whitelist)) {
                    $field->parentNode->removeChild($field);
                    continue;
                }
                if (! in_array($attributeId, $this->blackList)) {
                    $allFields['street'][] = $field->cloneNode(true);
                }
            }
        }



        $allFields = $this->getSortedFields($sort, $allFields);

        //remove empty elements , as childecount is not reset we have to remove all that dont have certain classes
        $xQuery = $x->query('//li/fieldset/ul/li');

        for($i = $xQuery->length; -- $i >= 0;) {
            $field = $xQuery->item($i);
            $class = $field->getAttribute('class');
            if(!empty($class) && !in_array($class, array('no-display')) && !strstr($class, 'osc')){
                $field->parentNode->removeChild($field);
            }
        }

        foreach ($allFields as $fields){

            $firstLi = $x->query('//li/fieldset/ul/li[1]');
            $li = $newHtml->createElement('li');

            $li->setAttribute('class','fields osc');
            foreach ($fields as $key => $field){

                switch ($key) {
                    case 'street':
                        $fieldCount = count($field);
                        for($i = $fieldCount; -- $i >= 0;) {
                            $field[$i]->setAttribute('class', 'wide osc');
                            $firstLi->item(0)->parentNode->appendChild($field[$i]);
                        }
                        $li = false;
                    break;

                    case 'password':
                        $field = array_reverse($field, true);
                        $li->setAttribute('id','register-customer-password');
                        foreach($field as $children){
                            $li->appendChild($children);
                        }
                    break;

                    default:
                        $li->appendChild($field);
                    break;
                }

            }
            if($li){
                $firstLi->item(0)->parentNode->appendChild($li);
            }

        }


        return $newHtml->saveHTML();
    }

    /**
     * Handle field array sort needs
     * @param sort
     * @param allFields
     */
    public function getSortedFields($sort, $allFields) {
        $tmp = array();
        $sort = array_flip($sort);
        ksort($sort);

        //add what is sorted
        foreach ($sort as $key => $value){
            if(!empty($allFields[$value])){
                $tmp[$value] = $allFields[$value];
                unset($allFields[$value]);
            }
        }

        //add what is not sorted
        foreach ($allFields as $key => $value){
            $tmp[$key] = $value;
        }
        $allFields = array();

        //make a new LI structure and add sorted values to it
        $i = 0;
        $o = 0;
        foreach($tmp as $key => $value){
           if($o % 2){

           } else {
               if(in_array($key, array('street','password','taxvat'))){
                   $o++;
               }
               $i++;
           }
           $allFields[$i][$key] = $value;
           $o++;
        }

        $tmp = array();
        return $allFields;
    }
}
