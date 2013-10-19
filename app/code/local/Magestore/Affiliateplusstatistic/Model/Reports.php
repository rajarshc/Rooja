<?php

class Magestore_Affiliateplusstatistic_Model_Reports extends Varien_Object
{
    /**
     * collection for reports model
     *
     * @var Varien_Object
     */
    protected $_collection;
    
    /**
     * filter for collection
     * $field => $conditions
     *
     * @var array
     */
    protected $_filters = array();
    
    /**
     * set collection for report
     *
     * @param Varien_Object $value
     * @return Magestore_Affiliateplusstatistic_Model_Reports
     */
    public function setCollection($value){
    	$this->_collection = $value;
    	return $this;
    }
    
    public function getCollection(){
    	if (method_exists($this->_collection,'addFieldToFilter'))
	    	foreach ($this->_filters as $field => $condition)
	    		$this->_collection->addFieldToFilter($field, $condition);
	    elseif (method_exists($this->_collection,'addAttributeToFilter'))
	    	foreach ($this->_filters as $field => $condition)
	    		$this->_collection->addAttributeToFilter($field, $condition);
    	return $this->_collection;
    }
    
    /**
     * reset column to empty
     *
     * @return Magestore_Affiliateplusstatistic_Model_Reports
     */
    public function resetSelectColumns(){
    	$this->_collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
    	return $this;
    }
    
    /**
     * set distinct for collection query
     *
     * @return Magestore_Affiliateplusstatistic_Model_Reports
     */
    public function setDistinct($value){
    	$this->_collection->distinct($value);
    	return $this;
    }
    
    /**
     * set filter for report model
     *
     * @param array $value
     * @return Magestore_Affiliateplusstatistic_Model_Reports
     */
    public function setFilters($value){
    	$this->_filters = $value;
    	return $this;
    }
    
    public function getFilters(){
    	return $this->_filters;
    }
    
    /**
     * add condition for filter
     *
     * @param string $field
     * @param mixed $condition
     * @return Magestore_Affiliateplusstatistic_Model_Reports
     */
    public function addFilter($field, $condition){
    	$this->_filters[$field] = $condition;
    	return $this;
    }
    
    /**
     * add column to count query
     *
     * @param string $alias
     * @param string $column
     * @return Magestore_Affiliateplusstatistic_Model_Reports
     */
    public function addCountColumn($alias, $column){
    	$this->_collection->getSelect()
    		->columns("COUNT($column) AS $alias");
    	return $this;
    }
    
    /**
     * add column to calculate sum in query
     *
     * @param string $alias
     * @param string $column
     * @return Magestore_Affiliateplusstatistic_Model_Reports
     */
    public function addSumColumn($alias, $column){
    	$this->_collection->getSelect()
    		->columns("SUM($column) AS $alias");
    	return $this;
    }
    
    /**
     * add column to group by (in sql query)
     *
     * @param string $column
     * @return Magestore_Affiliateplusstatistic_Model_Reports
     */
    public function addGroupColumn($column = null){
    	if ($column)
    		$this->_collection->getSelect()
    			->group($column);
    	return $this;
    }
    
    public function getDataObject(){
    	return $this->getCollection()->getFirstItem();
    }
}