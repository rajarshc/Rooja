<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Edit_Tab_Program extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
        parent::__construct();
        $this->setId('affiliateplusprogramGrid');
        $this->setDefaultSort('program_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getAccount() && $this->getAccount()->getId()){
        	$this->setDefaultFilter(array('in_programs' => 1));
        }
    }
    
    protected function _addColumnFilterToCollection($column){
    	if ($column->getId() == 'in_programs'){
    		$programIds = $this->_getSelectedPrograms();
    		if (empty($programIds)) $programIds = 0;
    		if ($column->getFilter()->getValue())
    			$this->getCollection()->addFieldToFilter('program_id', array('in'=>$programIds));
    		elseif ($programIds)
    			$this->getCollection()->addFieldToFilter('program_id', array('nin'=>$programIds));
    		return $this;
    	}
    	return parent::_addColumnFilterToCollection($column);
    }
    
    protected function _prepareCollection(){
    	$collection = Mage::getResourceModel('affiliateplusprogram/program_collection');
    	if ($storeId = $this->getRequest()->getParam('store', 0))
    		$collection->setStoreId($storeId);
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
    	$this->addColumn('in_programs', array(
            'header_css_class'  => 'a-center',
			'type'              => 'checkbox',
			'name'              => 'in_programs',
			'values'            => $this->_getSelectedPrograms(),
			'align'             => 'center',
			'index'             => 'program_id',
			'use_index'			=> true,
        ));
    	
	  $this->addColumn('program_id', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'program_id',
      ));

      $this->addColumn('program_name', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Program Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));

      $this->addColumn('program_num_account', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Number of Accounts'),
          'align'     =>'left',
          'index'     => 'num_account',
      ));

      $this->addColumn('program_total_sales_amount', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Total Amount'),
          'align'     => 'left',
          'type'     => 'price',
          'index'     => 'total_sales_amount',
          'currency_code' => $this->getStore()->getBaseCurrencyCode(),
      ));
      
      $this->addColumn('program_created_date', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Created Date'),
          'align'     => 'left',
          'type'      => 'date',
          'index'     => 'created_date',
      ));

      $this->addColumn('program_status', array(
          'header'    => Mage::helper('affiliateplusprogram')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => Mage::getSingleton('affiliateplusprogram/status')->getOptionArray(),
      ));
        
		$this->addColumn('position', array(
			'header'    => Mage::helper('affiliateplusprogram')->__('Position'),
			'name'		=> 'position',
			'type'		=> 'number',
			'index'     => 'position',
			'editable'	=> true,
			'edit_only'	=> true,
		));
    }
    
    public function getRowUrl($row){
		return $this->getUrl('affiliateplusprogramadmin/adminhtml_program/edit', array(
			'id' 	=> $row->getId(),
			'store'	=>$this->getRequest()->getParam('store')
		));
	}
	
	public function getGridUrl(){
        return $this->getUrl('affiliateplusprogramadmin/adminhtml_program/programGrid',array(
        	'_current'	=>true,
        	'id'		=>$this->getRequest()->getParam('id'),
        	'store'		=>$this->getRequest()->getParam('store')
    	));
    }
    
    protected function _getSelectedPrograms(){
    	$programs = $this->getPrograms();
    	if (!is_array($programs))
    		$programs = array_keys($this->getSelectedRelatedPrograms());
    	return $programs;
    }
    
    public function getSelectedRelatedPrograms(){
    	$programs = array();
    	$account = $this->getAccount();
    	
    	$programCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
    		->addFieldToFilter('account_id',$account->getId());
    	
    	foreach ($programCollection as $program)
    		$programs[$program->getProgramId()] = array('position' => 0);
    	
    	return $programs;
    }
    
    /**
     * get Current Account
     *
     * @return Magestore_Affiliateplus_Model_Account
     */
    public function getAccount(){
    	return Mage::getModel('affiliateplus/account')->load($this->getRequest()->getParam('id'));
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