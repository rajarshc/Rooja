<?php
class Magestore_Affiliateplusprogram_Block_All extends Magestore_Affiliateplusprogram_Block_Abstract
{
	/**
	 * get Module helper
	 *
	 * @return Magestore_Affiliateplusprogram_Helper_Data
	 */
	protected function _getHelper(){
		return Mage::helper('affiliateplusprogram');
	}
	
	protected function _construct(){
		parent::_construct();
		
		$collection = Mage::getResourceModel('affiliateplusprogram/program_collection')
			->setStoreId(Mage::app()->getStore()->getId())
			->addFieldToFilter('main_table.program_id',array('nin' => $this->_getHelper()->getJoinedProgramIds()));
		
		$group = Mage::getSingleton('customer/session')->getCustomer()->getGroupId();
		$collection->getSelect()
			->where("scope = 0 OR (scope = 1 AND FIND_IN_SET($group,customer_groups) )");
		
		// join program name and filter status
        $collection->getSelect()
            ->joinLeft(array('n' => $collection->getTable('affiliateplusprogram/value')),
                "main_table.program_id = n.program_id AND n.attribute_code = 'name' AND n.store_id = ".
                    Mage::app()->getStore()->getId(),
                array('program_name' => 'IF (n.value IS NULL, main_table.name, n.value)')
            )->joinLeft(array('s' => $collection->getTable('affiliateplusprogram/value')),
                "main_table.program_id = s.program_id AND s.attribute_code = 'status' AND s.store_id = ".
                    Mage::app()->getStore()->getId(),
                array()
            )->where('IF(s.value IS NULL, main_table.status, s.value) = 1');
		
		$this->setCollection($collection);
	}
	
	public function _prepareLayout(){
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager','programs_pager')
                ->setTemplate('affiliateplus/html/pager.phtml')
                ->setCollection($this->getCollection());
		$this->setChild('programs_pager',$pager);
		
		$grid = $this->getLayout()->createBlock('affiliateplus/grid','programs_grid');
		
		$grid->addColumn('select',array(
			'header'	=> '<input type="checkbox" onclick="selectProgram(this);" />',
			'render'	=> 'getSelectProgram',
		));
		
		$grid->addColumn('id',array(
			'header'	=> $this->__('No.'),
			'align'		=> 'left',
			'render'	=> 'getNoNumber',
		));
		
		$grid->addColumn('program_name',array(
			'header'	=> $this->__('Program Name'),
			'render'	=> 'getProgramName',
            'filter_index'  => 'IF (n.value IS NULL, main_table.name, n.value)',
            'searchable'    => true,
		));
		
		$grid->addColumn('details',array(
			'header'	=> $this->__('Information'),
			'render'	=> 'getProgramDetails'
		));
		
		$grid->addColumn('created_date',array(
			'header'	=> $this->__('Created Date'),
			'type'		=> 'date',
			'format'	=> 'medium',
			'index'		=> 'created_date',
            'searchable'    => true,
		));
		
		$grid->addColumn('action',array(
			'header'	=> $this->__('Action'),
			'type'		=> 'action',
			'action'	=> array(
				'label'		=> $this->__('Join Program'),
				'url'		=> 'affiliateplusprogram/index/join',
				'name'		=> 'id',
				'field'		=> 'program_id'
			)
		));
		
		$this->setChild('programs_grid',$grid);
		return $this;
	}
	
	public function getSelectProgram($row){
		return '<input type="checkbox" name="program_ids[]" value="'.$row->getId().'" />';
	}
}