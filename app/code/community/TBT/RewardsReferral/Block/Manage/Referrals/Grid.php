<?php

/**
 * J2T RewardsPoint2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TBT_RewardsReferral_Block_Manage_Referrals_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('referralsGrid');
        $this->setDefaultSort('rewardsref_referral_id ');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {

        $collection = Mage::getResourceModel('rewardsref/referral_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('id', array(
            'header' => Mage::helper('rewardsref')->__('Referral Link ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'rewardsref_referral_id',
        ));

        $this->addColumn('referral_parent_id', array(
            'header' => Mage::helper('rewardsref')->__('Affiliate Customer ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'referral_parent_id',
        ));

        $this->addColumn('parent_name', array(
            'header' => Mage::helper('rewardsref')->__('Affiliate Name'),
            'index' => 'referral_parent_id',
            'width' => '220px',
            'renderer' => 'rewardsref/manage_grid_renderer_referrer',
        ));

        $this->addColumn('referral_child_id', array(
            'header' => Mage::helper('rewardsref')->__('Referral Customer ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'referral_child_id',
        ));

        $this->addColumn('referral_name', array(
            'header' => Mage::helper('rewardsref')->__('Referral Name'),
            'index' => 'referral_name',
            'renderer' => 'rewardsref/manage_grid_renderer_referral',
        ));

        $this->addColumn('referral_email', array(
            'header' => Mage::helper('rewardsref')->__('Referral Email'),
            'index' => 'referral_email',
        ));



        $this->addColumn('referral_status', array(
            'header' => Mage::helper('rewardsref')->__('Status'),
            'index' => 'referral_status',
            'width' => '150px',
            'type' => 'options',
            'options' => Mage::getSingleton('rewardsref/referral_status')->getAllOptionsArray(),
        ));



        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('rewardsref_referral_id');
        $this->getMassactionBlock()->setFormFieldName('rewardsref_referral_ids');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => (Mage::helper('rewardsref')->__('Delete') . '&nbsp;&nbsp;'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('rewardsref')->__('Are you sure?')
        ));

        return $this;
    }

    protected function _afterLoadCollection() {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

}