<?php

class Magestore_Affiliatepluslevel_Block_Adminhtml_Account_Edit_Tab_Toptier extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
    {
        parent::__construct();
        $this->setId('toptiergrid');
        $this->setDefaultSort('toptier_id');
        $this->setUseAjax(true);
		$this->setDefaultDir('DESC');
		if ($this->_getSelectedAccount()) {//->getAccount()->getId()) {
            $this->setDefaultFilter(array('in_toptiers'=>1));
        }
    }

   
	protected function _prepareCollection()
    {
		$storeId = $this->getRequest()->getParam('store');
		$accountId = $this->getRequest()->getParam('id');
		$collection = Mage::getModel('affiliateplus/account')->getCollection();
		$tierTable = Mage::getModel('core/resource')->getTableName('affiliatepluslevel_tier');
		$collection->getSelect()
			->joinLeft($tierTable, "$tierTable.tier_id = main_table.account_id", array('level'=>"if ($tierTable.level IS NULL, 0, $tierTable.level)"))
			;
		
		if($this->getRequest()->getParam('id'))
			$collection->addFieldToFilter('account_id', array('neq' => $accountId));
		
		
		$tierIds = Mage::helper('affiliatepluslevel')->getFullTierIds($accountId, $storeId);
		
		if(count($tierIds))
			$collection->addFieldToFilter('account_id', array('nin' => $tierIds));
		
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }


    protected function _addColumnFilterToCollection($column)
    {

        if ($column->getId() == 'in_toptiers') {
            $accountId = $this->_getSelectedAccount();
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('account_id', $accountId);
            } else {
                if($accountId) {
                    $this->getCollection()->addFieldToFilter('account_id', array('neq'=>$accountId));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
	
    protected function _prepareColumns()
    {
		$this->addColumn('in_toptiers', array(
			'header_css_class'  => 'a-center',
			'type'              => 'radio',
			'html_name'         => 'in_toptiers',
			'align'             => 'center',
			'index'             => 'account_id',
			'values'            => array($this->_getSelectedAccount()),
		)); 
		
		$currencyCode =$this->_getStore()->getBaseCurrency()->getCode();
		$this->addColumn('account_id', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'account_id',
		));

		$this->addColumn('name', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Name'),
			'align'     =>'left',
			'index'     => 'name',
		));

	  
      	$this->addColumn('email', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Email'),
			'width'     => '300px',
			'index'     => 'email',
      	));
	  
	  	$this->addColumn('level', array(
			'header'    => Mage::helper('affiliatepluslevel')->__('Level'),
			'align'     =>'right',
			'index'     => 'level',
			'width'     => '50px',
			'filter_condition_callback' => array(Mage::getSingleton('affiliatepluslevel/observer'),'filterLevelAffiliateAccount'),
      	));	
	  	
		$this->addColumn('status', array(
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
	  
	  	$this->addColumn('approved', array(
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
            : $this->getUrl('*/*/toptierGrid', array(
				'_current'=>true,
				'id'=>$this->getRequest()->getParam('id'),
				'store'	=> $this->getRequest()->getParam('store')
            ));
	
	}
	
	public function getRowUrl($row) {
		$id = $row->getId();
		return $this->getUrl('*/adminhtml_account/edit', array(
			'id' => $id,
		));
	}
	
    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
	
	public function getAccount()
	{
		return Mage::getModel('affiliateplus/account')->load($this->getRequest()->getParam('id'));	
	}
	
	protected function _getSelectedAccount(){// get top tier of current account
        if ($this->getRequest()->getParam('map_toptier_id') != null) {
            return $this->getRequest()->getParam('map_toptier_id');
        }
		$accountId = $this->getRequest()->getParam('id');
        if (!$accountId) return 0;
		$tier = Mage::getModel('affiliatepluslevel/tier')->getCollection()
					->addFieldToFilter('tier_id', $accountId)
					->getFirstItem();
		return $tier->getToptierId();
	}
    
    public function getSelectedAccount() {
        return $this->_getSelectedAccount();
    }
}