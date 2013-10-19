<?php

class Magestore_Affiliateplusprogram_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $_cache = array();
	
	public function getProgramOptions(){
		if (!isset($this->_cache['program_options'])){
			$options[0] = $this->__('Affiliate Program');
			$programCollection = Mage::getResourceModel('affiliateplusprogram/program_collection');
			foreach ($programCollection as $program){
				$options[$program->getId()] = $program->getName();
			}
			$this->_cache['program_options'] = $options;
		}
		return $this->_cache['program_options'];
	}
	
	public function getProgramOptionArray(){
		if (!isset($this->_cache['program_option_array'])){
			$optionArray = array();
			foreach ($this->getProgramOptions() as $value => $label){
				$optionArray[] = array(
					'value'	=> $value,
					'label'	=> $label,
				);
			}
			$this->_cache['program_option_array'] = $optionArray;
		}
		return $this->_cache['program_option_array'];
	}
	
	public function getJoinedProgramIds(){
		if (!isset($this->_cache['joined_program_ids'])){
			$joinedPrograms = array(0);
			$joinedColection = Mage::getResourceModel('affiliateplusprogram/account_collection')
				->addFieldToFilter('account_id',Mage::helper('affiliateplus/account')->getAccount()->getId());
			foreach ($joinedColection as $item)
				$joinedPrograms[] = $item->getProgramId();
			$this->_cache['joined_program_ids'] = $joinedPrograms;
		}
		return $this->_cache['joined_program_ids'];
	}
	
	public function getProgramProductIds($programId, $storeId = null){
		if (is_null($storeId))
			$storeId = Mage::app()->getStore()->getId();
		
		$cacheKey = 'program_'.$programId.'_product_ids_in_store_'.$storeId;
		if (isset($this->_cache[$cacheKey])) return $this->_cache[$cacheKey];
		
		$productIds = array();
		$categoryCollection = Mage::getResourceModel('affiliateplusprogram/category_collection')
			->addFieldToFilter('program_id',$programId)
			->addFieldToFilter('store_id',$storeId);
		if ($categoryCollection->getSize() == 0)
			$categoryCollection = Mage::getResourceModel('affiliateplusprogram/category_collection')
				->addFieldToFilter('program_id',$programId)
				->addFieldToFilter('store_id',0);
		$categoryIds = array();
		foreach ($categoryCollection as $category) $categoryIds[] = $category->getCategoryId();
		if (count($categoryIds)){
			$productCollection = Mage::getResourceModel('catalog/product_collection');
			$productCollection->getSelect()
				->join(
					array('c' => $productCollection->getTable('catalog/category_product_index')),
					'e.entity_id = c.product_id',
					array()
				)->where('c.category_id IN ('.implode(',',$categoryIds).')')
				->group('e.entity_id');
			$productIds = $productCollection->getAllIds();
		}
		
		$this->_cache[$cacheKey] = $productIds;
		return $this->_cache[$cacheKey];
	}
	
	/*public function getProgramProductIds($programId){
		$cacheKey = 'program_'.$programId.'_product_ids';
		if (isset($this->_cache[$cacheKey])) return $this->_cache[$cacheKey];
		
		$productIds = array();
		$this->refreshProgramProductIds($programId);
		
		$productCollection = Mage::getResourceModel('affiliateplusprogram/product_collection')
			->addFieldToFilter('program_id',$programId);
		foreach ($productCollection as $product)
			$productIds[] = $product->getProductId();
		
		$this->_cache[$cacheKey] = array_unique($productIds);
		return $this->_cache[$cacheKey];
	}
	
	public function refreshProgramProductIds($programId){
		if (isset($this->_cache['refresh_program_product_ids'])) return $this;
		
		$program = Mage::getModel('affiliateplusprogram/program')->load($programId);
		if ($program->getId()){
			if ($program->getIsProcess()){
				$this->_cache['refresh_program_product_ids'] = true;
				return $this;
			}
		} else {
			$this->_cache['refresh_program_product_ids'] = true;
			return $this;
		}
		
		$productCollection = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect('*');
		$productIds = array();
		foreach ($productCollection as $product)
			if ($program->validateItem($product))
				$productIds[] = $product->getId();
		try {
			Mage::getModel('affiliateplusprogram/product')
				->setProgramId($program->getId())
				->setProductIds($productIds)
				->saveAllProducts();
			$program->setProgramIsProcessed();
		} catch (Exception $e){}
		
		$this->_cache['refresh_program_product_ids'] = true;
		return $this;
	}*/
	
	public function initProgram($accountId, $order = null){
		if (isset($this->_cache["init_programs_$accountId"])) return $this;
		$joinedPrograms = Mage::getResourceModel('affiliateplusprogram/account_collection')
			->addFieldToFilter('account_id',$accountId)
			->setOrder('joined','DESC');
		$programs = array();
		if ($order)
			$quote = $order;
		else
			$quote = Mage::getSingleton('checkout/cart')->getQuote();
		foreach ($joinedPrograms as $joinedProgram){
			$program = Mage::getModel('affiliateplusprogram/program')
				->setStoreId(Mage::app()->getStore()->getId())
				->load($joinedProgram->getProgramId());
			if ($program->validateOrder($quote)) $programs[] = $program;
		}
		$this->_cache["init_programs_$accountId"] = $programs;
		return $this;
	}
	
	public function getProgramByItemAccount($itemProduct, $account){
		if (is_object($account))
			$accountId = $account->getId();
		else
			$accountId = $account;
		if (!isset($this->_cache["init_programs_$accountId"])) $this->initProgram($accountId);
		$programs = $this->_cache["init_programs_$accountId"];
		if (count($programs))
		foreach ($programs as $program)
			if ($program->validateItem($itemProduct))
				return $program;
		return null;
	}
	
	public function getProgramByProductAccount($product, $account){
		return $this->getProgramByItemAccount($product,$account);
	}
	
	public function multilevelIsActive(){
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;
		if (isset($modulesArray['Magestore_Affiliatepluslevel']) && is_object($modulesArray['Magestore_Affiliatepluslevel']))
			return $modulesArray['Magestore_Affiliatepluslevel']->is('active');
		return false;
	}
	
	public function getStandardCommissionPercent(){
		$storeId = Mage::app()->getStore()->getId();
		$perCommissions = Mage::getStoreConfig('affiliateplus/multilevel/commission_percentage', $storeId);
		$arrPerCommissions = explode(',', $perCommissions);
		return $arrPerCommissions[0];
	}
}