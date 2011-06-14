<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Rma
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */if(@!class_exists('Varien_Data_Form_Element_Awlink')) {
    if(@class_exists('Varien_Data_Form_Element_Link')) {
        class Varien_Data_Form_Element_Awlink extends Varien_Data_Form_Element_Link {
        }
    } else {
        class Varien_Data_Form_Element_Awlink extends Varien_Data_Form_Element_Abstract {
            public function __construct($attributes=array()) {
                parent::__construct($attributes);
                $this->setType('awlink');
            }

            /**
             * Generates element html
             *
             * @return string
             */
            public function getElementHtml() {
                $html = $this->getBeforeElementHtml();
                $html .= '<a id="'.$this->getHtmlId().'" '.$this->serialize($this->getHtmlAttributes()).'>'. $this->getEscapedValue() . "</a>\n";
                $html .= $this->getAfterElementHtml();
                return $html;
            }

            /**
             * Prepare array of anchor attributes
             *
             * @return array
             */
            public function getHtmlAttributes() {
                return array('charset', 'coords', 'href', 'hreflang', 'rel', 'rev', 'name',
                    'shape', 'target', 'accesskey', 'class', 'dir', 'lang', 'style',
                    'tabindex', 'title', 'xml:lang', 'onblur', 'onclick', 'ondblclick',
                    'onfocus', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover',
                    'onmouseup', 'onkeydown', 'onkeypress', 'onkeyup');
            }
        }
    }
}