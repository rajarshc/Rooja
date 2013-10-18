<?php
class Magestore_Affiliateplusprogram_Block_Detail extends Mage_Core_Block_Template
{
	/**
	 * get Account helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Account
	 */
	protected function _getAccountHelper(){
		return Mage::helper('affiliateplus/account');
	}
	
	public function getProgram(){
		if (!$this->hasData('program')){
			$this->setData('program',Mage::getModel('affiliateplusprogram/program')
				->setStoreId(Mage::app()->getStore()->getId())
				->load($this->getRequest()->getParam('id')));
		}
		return $this->getData('program');
	}
	
	public function isJoined(){
		if (!$this->hasData('is_joined')){
			$this->setData('is_joined',in_array($this->getProgram()->getId(),Mage::helper('affiliateplusprogram')->getJoinedProgramIds()));
		}
		return $this->getData('is_joined');
	}
	
	// protected function _construct(){
		// parent::_construct();
		
		// $collection = Mage::getResourceModel('catalog/product_collection')
			// ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
			// ->addAttributeToFilter('entity_id',array(
				// 'in' => Mage::helper('affiliateplusprogram')->getProgramProductIds($this->getRequest()->getParam('id'))
			// ));
		
		// Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		// Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
		
		// $this->setCollection($collection);
	// }
	
	// public function _prepareLayout(){
		// parent::_prepareLayout();
		// $pager = $this->getLayout()->createBlock('page/html_pager','products_pager')->setCollection($this->getCollection());
		// $this->setChild('products_pager',$pager);
		
		// $grid = $this->getLayout()->createBlock('affiliateplus/grid','products_grid')
            // ->setFilterUrl($this->getUrl('*/*/*', array('id' => $this->getRequest()->getParam('id'))));
		
		// Mage::dispatchEvent('affiliateplus_program_listall_prepare_grid',array('grid' => $grid));
		
		// prepare column
		// $grid->addColumn('id',array(
			// 'header'	=> $this->__('#'),
			// 'align'		=> 'left',
			// 'index'		=> 'entity_id',
		// ));
		
		// $grid->addColumn('product_name',array(
			// 'header'	=> $this->__('Product'),
			// 'render'	=> 'getProductName',
            // 'index'     => 'name',
            // 'searchable'    => true,
		// ));
		
		// $grid->addColumn('price',array(
			// 'header'	=> $this->__('Price'),
			// 'type'		=> 'baseprice',
			// 'index'		=> 'price',
            // 'searchable'    => true,
		// ));
		
		// $grid->addColumn('discount',array(
			// 'header'	=> $this->__('Discount'),
			// 'render'	=> 'getDiscount',
		// ));
		
		// $grid->addColumn('commission',array(
			// 'header'	=> $this->__('Commission'),
			// 'render'	=> 'getCommission'
		// ));
		
		// $this->setChild('products_grid',$grid);
		// return $this;
	// }
	
	public function getProductName($row){
		return sprintf('<a href="%s" title="%s">%s</a>'
			,$this->isJoined() ? Mage::helper('affiliateplus/url')->addAccToUrl($row->getProductUrl()) : $row->getProductUrl()
			,$this->__('View Product Detail')
			,$row->getName()
		);
	}
	
	// public function getDiscount($row){
		// if ($this->getProgram()->getDiscountType() == 'fixed'){
			// return Mage::helper('core')->currency(min($this->getProgram()->getDiscount(),$row->getPrice()));
		// }
		// return sprintf('%.2f',$this->getProgram()->getDiscount()).'%';
	// }
	
	// public function getCommission($row){
		// $standardCommission = $this->getProgram()->getCommission();
	
		// if ($this->getProgram()->getCommissionType() == 'fixed'){
			// return Mage::helper('core')->currency(min($standardCommission,$row->getPrice()));
		// }
		// return sprintf('%.2f',$standardCommission).'%';
	// }
	
	// public function getPagerHtml(){
    	// return $this->getChildHtml('products_pager');
    // }
    
    // public function getGridHtml(){
    	// return $this->getChildHtml('products_grid');
    // }
    
    // protected function _toHtml(){
    	// $this->getChild('products_grid')->setCollection($this->getCollection());
    	// return parent::_toHtml();
    // }
}