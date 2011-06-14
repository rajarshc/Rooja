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
class AW_Rma_Block_Adminhtml_Status_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('rma_statuses')
            ->setDefaultSort('sort')
            ->setDefaultDir('ASC')
            ->setSaveParametersInSession(TRUE)
            ->setUseAjax(FALSE);
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => $this->__('Id'),
            'width' => '100px',
            'index' => 'id'
        ));

        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'index' => 'name'
        ));

        if(!Mage::app()->isSingleStoreMode())
            $this->addColumn('store', array(
                'header' => $this->__('Store'),
                'index' => 'store',
                'sortable' => FALSE,
                'type' => 'store',
                'store_all' => TRUE,
                'store_view' => TRUE,
                'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store',
                'filter_condition_callback' => array($this, '_filterStoreCondition')
            ));

        $this->addColumn('sort', array(
            'header' => $this->__('Sort'),
            'index' => 'sort',
            'width' => '100px'
        ));

        $this->addColumn('actions', array(
            'header' => $this->__('Actions'),
            'width' => '150px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $this->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                ),
                array(
                    'caption' => $this->__('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                    'confirm' => $this->__('Are you sure you want do this?')
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'is_system' => true
        ));
    }

    protected function _prepareCollection() {
        $this->setCollection(Mage::getModel('awrma/entitystatus')
            ->getCollection()
            ->setRemovedFilter());

        return parent::_prepareCollection();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _filterStoreCondition($collection, $column) {
        if(!($value = $column->getFilter()->getValue())) return;
        $collection->getSelect()->where('find_in_set(?, store)', $value)
            ->orWhere('find_in_set(0, store)');
    }
}
