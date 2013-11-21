<?php

class Varien_Data_Form_Element_Thumbnail extends Varien_Data_Form_Element_Abstract
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('file');
    }

    public function getElementHtml()
    {
		
        $html = '';
            $url = $this->_getUrl();
			$url = Mage::getBaseUrl('media') . $url;
            $html = '<a onclick="imagePreview(\''.$this->getHtmlId().'_image\'); return false;" href="%27.$url.%27"><img src="'.$this->getHtmlId().'" id="'.$this->getHtmlId().'_image" class="small-image-preview v-middle" style="border: 1px solid rgb(214, 214, 214);" title="'.$this->getValue().'" src="%27.$url.%27" alt="'.$this->getValue().'" width="25" height="25"></a> ';
		
        $this->setClass('input-file');
        $html.= parent::getElementHtml();

        return $html;
    }

    protected function _getUrl()
    {
        return $this->getValue();
    }

    public function getName()
    {
        return  $this->getData('name');
    }
}


?>
