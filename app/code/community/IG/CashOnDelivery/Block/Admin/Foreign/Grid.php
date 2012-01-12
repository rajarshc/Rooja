<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@idealiagroup.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category   IG
 * @package    IG_CashOnDelivery
 * @copyright  Copyright (c) 2010-2011 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Riccardo Tempesta <tempesta@idealiagroup.com>
*/
 
class IG_CashOnDelivery_Block_Admin_Foreign_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('cashondeliveryGrid');
		$this->_controller = 'ig_cashondelivery';
	}
	
	protected function _prepareCollection()
	{
 		$model = Mage::getModel('ig_cashondelivery/foreign');
 		$collection = $model->getCollection();
 		$this->setCollection($collection);
 		
 		$this->setDefaultSort('amount_from');
		$this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);

		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
			'header'        => Mage::helper('ig_cashondelivery')->__('ID'),
			'align'         => 'right',
			'width'         => '50px',
			'index'         => 'ig_cashondelivery_foreign_id',
		));
		
		$this->addColumn('from', array(
			'header'        => Mage::helper('ig_cashondelivery')->__('From amount'),
			'align'         => 'left',
			'index'         => 'amount_from',
			'type'          => 'currency',
			'truncate'      => 50,
			'escape'        => true,
		));
		
		$this->addColumn('fee', array(
			'header'		=> Mage::helper('ig_cashondelivery')->__('Fee Amount'),
			'align'         => 'left',
			'index'         => 'apply_fee',
			'type'          => 'currency',
			'truncate'      => 30,
			'escape'        => true,
        ));
        
        $this->addColumn('apply_by', array(
			'header'		=> Mage::helper('ig_cashondelivery')->__('Apply'),
			'align'         => 'left',
			'index'         => 'fee_mode',
			'type'          => 'options',
			'options'		=> array(
				'absolute'	=> Mage::helper('ig_cashondelivery')->__('By Fixed Amount'),
				'percent'	=> Mage::helper('ig_cashondelivery')->__('By Percentage'),
			),
			'truncate'      => 30,
			'escape'        => true,
        ));

		$this->addColumn('action',
            array(
                'header'    => Mage::helper('ig_cashondelivery')->__('Action'),
                'width'     => '80px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(array(
                    'caption'   => Mage::helper('ig_cashondelivery')->__('Edit'),
                    'url'       => array(
                        'base'=>'*/*/edit'
                    ),
                    'field'   => 'id'
                )),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'ig_cashondelivery',
        ));
		
		return parent::_prepareColumns();
	}
	
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array(
			'id' => $row->getId(),
		));
	}
}