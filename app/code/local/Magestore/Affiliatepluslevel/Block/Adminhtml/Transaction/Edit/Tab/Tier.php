<?php

class Magestore_Affiliatepluslevel_Block_Adminhtml_Transaction_Edit_Tab_Tier extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
    {
        parent::__construct();
        $this->setId('tiergrid');
        $this->setDefaultSort('real_level');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }


	protected function _prepareCollection()
    {
		$transactionId	= $this->getRequest()->getParam('id');
		$storeId = $this->getRequest()->getParam('store');
		
		$tierTable = Mage::getModel('core/resource')->getTableName('affiliatepluslevel_tier');
		$accountTable = Mage::getModel('core/resource')->getTableName('affiliateplus_account');
		
		$collection = Mage::getModel('affiliatepluslevel/transaction')->getCollection()
					->addFieldToFilter('transaction_id', $transactionId);
		
		if($storeId)
			$collection->addFieldToFilter('store_id', $storeId);
		
		$collection->getSelect()
			->columns('(main_table.level + 1) AS real_level')
			->joinLeft($tierTable, "$tierTable.tier_id = main_table.tier_id", array(/* 'level'=>'level' */))
			->joinLeft($accountTable, "$accountTable.account_id = main_table.tier_id", 
				array('name' => 'name', 'account_id' => "$accountTable.account_id", 'email' => 'email'));
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

		$currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
		/* $this->addColumn('account_id', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'account_id',
		)); */

		$this->addColumn('name', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Name'),
			'align'     =>'left',
			'index'     => 'name',
		));

	  
      	$this->addColumn('email', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Email'),
			'width'     => '250px',
			'index'     => 'email',
      	));
	  
	  	$this->addColumn('real_level', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Level'),
			'align'     =>'right',
			'index'     => 'real_level',
			'filter_index' => 'main_table.level + 1',
      	));	
	  	
		$this->addColumn('commission', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Commission'),
			'align'     => 'right',
			'index'     => 'commission',
			'type'		=> 'price',
			'currency_code' => $currencyCode,
      	));	
	  	
        Mage::dispatchEvent('affiliatepluslevel_transaction_prepare_columns', array(
            'grid'  => $this->setData('affiliatepluslevel_currency_code', $currencyCode)
        ));	
		
    }

    //return url
	public function getGridUrl(){
		return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/tierGrid', array(
				'_current'=>true,
				'id'=>$this->getRequest()->getParam('id'),
            ));
	
	}
	
	public function getRowUrl($row) {
		$id = $row->getAccountId();
		return $this->getUrl('affiliateplusadmin/adminhtml_account/edit', array(
			'id' => $id,
		));
	}
}