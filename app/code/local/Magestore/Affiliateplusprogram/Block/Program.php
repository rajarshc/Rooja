<?php
class Magestore_Affiliateplusprogram_Block_Program extends Magestore_Affiliateplusprogram_Block_Abstract
{
	protected $_commission_array = array();
	
	protected function _construct(){
		parent::_construct();
		
		$collection = Mage::getResourceModel('affiliateplusprogram/program_collection');
		//$collection->setStoreId(Mage::app()->getStore()->getId());
		$collection->getSelect()->join(
			array('account' => $collection->getTable('affiliateplusprogram/account')),
			'main_table.program_id = account.program_id',
			array(
				'joined_at'	=> 'joined',
		))->where('account.account_id = ?',$this->_getAccountHelper()->getAccount()->getId());
        
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
		
		// prepare column
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
		
		$grid->addColumn('joined_at',array(
			'header'	=> $this->__('Joined On'),
			'type'		=> 'date',
			'format'	=> 'medium',
			'index'		=> 'joined_at',
            'searchable'    => true,
            'filter_index'  => 'account.joined',
		));
		
		$grid->addColumn('action',array(
			'header'	=> $this->__('Action'),
			'type'		=> 'action',
			'action'	=> array(
				'label'		=> $this->__('Opt out'),
				'url'		=> 'affiliateplusprogram/index/out',
				'name'		=> 'id',
				'field'		=> 'program_id'
			)
		));
		
		$this->setChild('programs_grid',$grid);
		return $this;
	}
	
    public function getAllProgramUrl(){
    	return $this->getUrl('affiliateplusprogram/index/all');
    }
    
    public function isShowDefaultProgram(){
    	return (Mage::helper('affiliateplus/config')->getCommissionConfig('commission') && Mage::helper('affiliateplus/config')->getDiscountConfig('discount'));
    }
    
    public function getDefaultProgramTotalCommission(){
    	return $this->_commission_array[0];
    }
    
    /*
     * Not used
    public function getDefaultProgramDetail(){
    	$row = new Varien_Object(array(
    		'id'				=> 0,
    		'discount'			=> Mage::helper('affiliateplus/config')->getDiscountConfig('discount'),
            'secondary_discount'=> Mage::helper('affiliateplus/config')->getDiscountConfig('secondary_discount'),
    		'discount_type'		=> Mage::helper('affiliateplus/config')->getDiscountConfig('discount_type'),
    		'commission'		=> Mage::helper('affiliateplus/config')->getCommissionConfig('commission'),
            'secondary_commission'  => Mage::helper('affiliateplus/config')->getCommissionConfig('secondary_commission'),
    		'commission_type'	=> Mage::helper('affiliateplus/config')->getCommissionConfig('commission_type'),
    	));
    	return $this->getProgramDetails($row);
    }
     * 
     */
}
