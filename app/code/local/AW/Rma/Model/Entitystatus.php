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
 */
class AW_Rma_Model_Entitystatus extends Mage_Core_Model_Abstract {
    public function _construct() {
        $this->_init('awrma/entitystatus');
    }

    /**
     * Load status by name
     * @param string $name
     * @return AW_Rma_Model_Entitystatus
     */
    public function loadByName($name) {
        $this->load($name, 'name');
        return $this;
    }

    protected function _beforeSave() {
        if(is_array($this->getStore()))
            $this->setStore(implode(',', $this->getStore()));
    }

    protected function _afterLoad() {
        if(is_string($this->getStore()))
            $this->setStore(explode(',', $this->getStore()));
    }
}
