<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Edit_Tab_Account extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
        parent::__construct();
        $this->setId('accountGrid');
        $this->setDefaultSort('account_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getProgram() && $this->getProgram()->getId()){
        	$this->setDefaultFilter(array('in_accounts' => 1));
        }
    }
    
    protected function _addColumnFilterToCollection($column){
    	if ($column->getId() == 'in_accounts'){
    		$accountIds = $this->_getSelectedAccounts();
    		if (empty($accountIds)) $accountIds = 0;
    		if ($column->getFilter()->getValue())
    			$this->getCollection()->addFieldToFilter('account_id', array('in'=>$accountIds));
    		elseif ($accountIds)
    			$this->getCollection()->addFieldToFilter('account_id', array('nin'=>$accountIds));
    		return $this;
    	}
    	return parent::_addColumnFilterToCollection($column);
    }
    
    protected function _prepareCollection(){
    	$collection = Mage::getModel('affiliateplus/account')->getCollection();
    	if ($storeId = $this->getStore()->getId())
    		$collection->setStoreId($storeId);
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
    	$currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
    	
    	$this->addColumn('in_accounts', array(
            'header_css_class'  => 'a-center',
			'type'              => 'checkbox',
			'name'              => 'in_accounts',
			'values'            => $this->_getSelectedAccounts(),
			'align'             => 'center',
			'index'             => 'account_id',
			'use_index'			=> true,
        ));
    	
		$this->addColumn('account_id', array(
            'header'    => Mage::helper('affiliateplus')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'account_id'
        ));
        
        $this->addColumn('account_name', array(
			'header'    => Mage::helper('affiliateplus')->__('Name'),
			'align'     => 'left',
			'index'     => 'name',
		));
		
		$this->addColumn('account_email', array(
			'header'    => Mage::helper('affiliateplus')->__('Email'),
			'align'     => 'left',
			'index'     => 'email',
		));
		
		$this->addColumn('account_balance', array(
			'header'    => Mage::helper('affiliateplus')->__('Balance'),
			'width'     => '100px',
			'align'     => 'right',
			'index'     => 'balance',
			'type'		=> 'price',
			'currency_code' => $currencyCode,
		));
	
		$this->addColumn('account_total_commission_received', array(
			'header'    => Mage::helper('affiliateplus')->__('Total Commission Received'),
			'align'     => 'left',
			'index'     => 'total_commission_received',
			'type'  	=> 'price',
		  	'currency_code' => $currencyCode,
		));
	
		$this->addColumn('account_total_paid', array(
			'header'    => Mage::helper('affiliateplus')->__('Total Paid'),
			'align'     => 'left',
			'index'     => 'total_paid',
			'type'  	=> 'price',
		  	'currency_code' => $currencyCode,
		));
		
		$this->addColumn('account_status', array(
			'header'    => Mage::helper('affiliateplus')->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'status',
			'type'      => 'options',
			'options'   => array(
				1 => 'Enabled',
				2 => 'Disabled',
			),
		));
		
		$this->addColumn('position', array(
			'header'    => Mage::helper('affiliateplus')->__('Position'),
			'name'		=> 'position',
			'type'		=> 'number',
			'index'     => 'position',
			'editable'	=> true,
			'edit_only'	=> true,
		));
    }
    
    public function getRowUrl($row){
		return $this->getUrl('affiliateplusadmin/adminhtml_account/edit', array(
			'id' 	=> $row->getId(),
			'store'	=>$this->getRequest()->getParam('store')
		));
	}
	
	public function getGridUrl(){
        return $this->getUrl('*/*/accountGrid',array(
        	'_current'	=>true,
        	'id'		=>$this->getRequest()->getParam('id'),
        	'store'		=>$this->getRequest()->getParam('store')
    	));
    }
    
    protected function _getSelectedAccounts(){
    	$accounts = $this->getAccounts();
    	if (!is_array($accounts))
    		$accounts = array_keys($this->getSelectedRelatedAccounts());
    	return $accounts;
    }
    
    public function getSelectedRelatedAccounts(){
    	$accounts = array();
    	$program = $this->getProgram();
    	$accountCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
    		->addFieldToFilter('program_id',$program->getId());
    	foreach ($accountCollection as $account)
    		$accounts[$account->getAccountId()] = array('position' => 0);
    	return $accounts;
    }
    
    /**
     * get Current Program
     *
     * @return Magestore_Affiliateplusprogram_Model_Program
     */
    public function getProgram(){
    	return Mage::getModel('affiliateplusprogram/program')->load($this->getRequest()->getParam('id'));
    }
  
	/**
	 * get currrent store
	 *
	 * @return Mage_Core_Model_Store
	 */
	public function getStore(){
		$storeId = (int) $this->getRequest()->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}
}