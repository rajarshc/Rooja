<?php

class Magestore_Affiliatepluscoupon_Block_Adminhtml_Account_Edit_Tab_Coupon extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
		parent::__construct();
		$this->setId('couponGrid');
		$this->setDefaultSort('coupons_coupon_id');
		if (Mage::helper('affiliatepluscoupon')->isMultiProgram())
			$this->setDefaultFilter(array('coupons_status' => '1'));
		$this->setUseAjax(true);
	}
	
	protected function _prepareCollection(){
		$accountId = $this->getRequest()->getParam('id');
		$collection = Mage::getResourceModel('affiliatepluscoupon/coupon_collection');
		
		if (Mage::helper('affiliatepluscoupon')->isMultiProgram()
            && Mage::helper('core')->isModuleOutputEnabled('Magestore_Affiliateplusprogram')
        ){
			$collection->getSelect()->joinLeft(
				array('p'	=> $collection->getTable('affiliateplusprogram/account')),
				'main_table.program_id = p.program_id AND main_table.account_id = p.account_id',
				array()
			)->joinLeft(
				array('r'	=> $collection->getTable('affiliateplusprogram/program')),
				'main_table.program_id = r.program_id',
				array('status' => 'IF (main_table.program_id = 0, 1, IF(p.id AND r.use_coupon, 1, 0))')
			);
		} else {
            $collection->addFieldToFilter('program_id', array('eq'=>0));
        }
		$collection->addFieldToFilter('main_table.account_id',$accountId);
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _addColumnFilterToCollection($column){
    	if ($column->getId() == 'in_coupons'){
    		$couponIds = $this->_getSelectedCoupons();
    		if (empty($couponIds)) $couponIds = 0;
    		if ($column->getFilter()->getValue())
    			$this->getCollection()->addFieldToFilter('main_table.coupon_id', array('in'=>$couponIds));
    		elseif ($couponIds)
    			$this->getCollection()->addFieldToFilter('main_table.coupon_id', array('nin'=>$couponIds));
    		return $this;
    	}
    	return parent::_addColumnFilterToCollection($column);
    }
	
	protected function _prepareColumns(){
		$this->addColumn('in_coupons', array(
            'header_css_class'  => 'a-center',
			'type'              => 'checkbox',
			'name'              => 'in_coupons',
			'values'            => $this->_getSelectedCoupons(),
			'align'             => 'center',
			'index'             => 'program_id',
			'use_index'			=> true,
        ));
		
		$this->addColumn('coupons_coupon_id',array(
			'header'	=> Mage::helper('affiliatepluscoupon')->__('ID'),
			'width'		=> '50px',
			'index'		=> 'coupon_id',
			'filter_index'	=> 'main_table.coupon_id',
		));
		
		$this->addColumn('coupons_program_name',array(
			'header'	=> Mage::helper('affiliatepluscoupon')->__('Program'),
			'index'		=> 'program_name',
			'filter_index'	=> 'main_table.program_name',
			'renderer'	=> 'affiliatepluscoupon/adminhtml_account_renderer_program',
		));
		
		$this->addColumn('coupon_code',array(
			'header'	=> Mage::helper('affiliatepluscoupon')->__('Coupon Code'),
			'name'		=> 'coupon_code',
			'index'		=> 'coupon_code',
			'filter_index'	=> 'main_table.coupon_code',
			'editable'	=> true,
			'edit_only'	=> true,
		));
		
		if (Mage::helper('affiliatepluscoupon')->isMultiProgram()){
			$this->addColumn('coupons_status',array(
				'header'	=> Mage::helper('affiliatepluscoupon')->__('Status'),
				'index'		=> 'status',
				'filter_index'	=> 'IF (main_table.program_id = 0, 1, IF(p.id AND r.use_coupon, 1, 0))',
				'type'		=> 'options',
				'options'	=> array(
					'0'	=> Mage::helper('affiliatepluscoupon')->__('Disable'),
					'1'	=> Mage::helper('affiliatepluscoupon')->__('Enable'),
				),
			));
		}
		
		return parent::_prepareColumns();
	}
	
	protected function _getSelectedCoupons(){
		$coupons = $this->getCoupons();
		if (!is_array($coupons))
			return array();
		return $coupons;
	}
	
	public function getSelectedRelatedCoupons(){
		return array();
	}
	
	public function getGridUrl(){
		return $this->getData('grid_url')
			? $this->getData('grid_url')
			: $this->getUrl('*/*/couponsGrid',array(
				'_current'	=> true,
				'id'	=> $this->getRequest()->getParam('id'),
			));
	}
	
	public function getRowUrl($row){
		return '';
	}
}