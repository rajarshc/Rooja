<?php

class Magestore_Affiliatepluslevel_Block_Adminhtml_Account_Edit_Tab_Tier extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
    {
        parent::__construct();
        $this->setId('tiergrid');
        $this->setDefaultSort('tier_id');
        $this->setUseAjax(true);
    }

    //return category collection filtered by store
	protected function _prepareCollection()
    {
		$accountId	= $this->getRequest()->getParam('id');
		$storeId = $this->getRequest()->getParam('store');
		$fullTierIds = Mage::helper('affiliatepluslevel')->getFullTierIds($accountId, $storeId);
		$collection = Mage::getModel('affiliateplus/account')->getCollection()
					->addFieldToFilter('account_id', array('in' => $fullTierIds));
					
		$tierTable = Mage::getModel('core/resource')->getTableName('affiliatepluslevel_tier');
		
		$collection->getSelect()
			->join($tierTable, "$tierTable.tier_id = main_table.account_id", array('toptier_id' => "$tierTable.toptier_id", 'level'=> 'level'));

		if($storeId)
			$collection->setStoreId($storeId);
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

		$currencyCode =$this->_getStore()->getBaseCurrency()->getCode();
		$prefix = 'tier_';
		$this->addColumn($prefix.'account_id', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'account_id',
		));

		$this->addColumn($prefix.'name', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Name'),
			'align'     =>'left',
			'index'     => 'name',
		));

	  
      	$this->addColumn($prefix.'email', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Email'),
			'index'     => 'email',
      	));
	  
	  	$this->addColumn($prefix.'balance', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Balance'),
			'width'     => '100px',
			'align'     =>'right',
			'index'     => 'balance',
			'type'		=> 'price',
			'currency_code' => $currencyCode,
      	));
	  
	  	$this->addColumn($prefix.'total_commission_received', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Total Commission'),
			'width'     => '100px',
			'align'     =>'right',
			'index'     => 'total_commission_received',
			'type'		=> 'price',
			'currency_code' => $currencyCode,
      	));
	  
	  	$this->addColumn($prefix.'total_paid', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Total Paid'),
			'width'     => '100px',
			'align'     =>'right',
			'index'     => 'total_paid',
			'type'		=> 'price',
			'currency_code' => $currencyCode,
      	));
	  
	  	$this->addColumn($prefix.'level', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Level'),
			'align'     =>'right',
			'index'     => 'level',
      	));	
	  	
		$this->addColumn($prefix.'status', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'status',
			'type'      => 'options',
			'options'   => array(
				1 => 'Enabled',
				2 => 'Disabled',
			),
		));
	  
	  	$this->addColumn($prefix.'approved', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Approved'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'approved',
			'type'      => 'options',
			'options'   => array(
				1 => 'Yes',
				2 => 'No',
			),
      	));
		
    }

    //return url
	public function getGridUrl(){
		return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/tierGrid', array(
				'_current'=>true,
				'id'=>$this->getRequest()->getParam('id'),
				'store'	=> $this->getRequest()->getParam('store')
            ));
	
	}
	
	public function getRowUrl($row) {
		$id = $row->getId();
		return $this->getUrl('affiliateplusadmin/adminhtml_account/edit', array(
			'id' => $id,
		));
	}
	
    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
}