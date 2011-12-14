<?php

class TBT_Rewards_Model_Mysql4_Transfer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
	protected $didSelectCustomerName = false;
	protected $didSelectCurrency = false;
	
	public function _construct() {
		$this->_init ( 'rewards/transfer' );
	}
	

	/**
     * Add all the references linked with the transfers.
     * This will also include multiple references associated with the same transfer 
     * and might cause transferes to be listed more than once.
     * 
     * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
     */
    public function addAllReferences() {
        $this->_addTransferReferences();
        return $this;
    }

    /**
     * 
     * 
     * (overrides parent method)
     */
    public function _initSelect () {
        parent::_initSelect();
        
        // Add a simplified version of the transfer references (1 reference per 1 transfer)
        $this->_addTransferReferences(  $this->_getSingleReferenceSelect()  );
        
        return $this;
    }
    
    /**
     * Adds transfer references to this current collection.  By default
     * adds all the transfer referneces, but you can pass a subquery into the $references 
     * parameter to only add specific references.
     * @param mixed $references [=null] joins the references table (which may create duplicate transfer entries) by default.
     */
    protected function _addTransferReferences($references = null) {
        if(empty($references)) {
            $references = $this->getTable('transfer_reference');
        }
        
        $references = $this->_getSingleReferenceSelect();
        $this->getSelect()->joinLeft(
            array('reference_table' => $references ), 
        	'main_table.rewards_transfer_id = reference_table.rewards_transfer_id', 
            array(
            	'rewards_transfer_reference_id' => 'rewards_transfer_reference_id', 
            	'reference_type' => "reference_table.reference_type", 
            	'reference_id' => "reference_table.reference_id", 
            	'transfer_id' => "reference_table.rewards_transfer_id"
            )
        );
        
    }
    
    /**
     * Returns a database select object that selects all references, but limits 1 reference per points transfer
     * @return Zend_Db_Select
     */
    protected function _getSingleReferenceSelect() {
        $references_table_name = $this->getTable('transfer_reference');
        $read_connection = $this->getResource()->getReadConnection();
        $single_references_select = $read_connection->select()->from($references_table_name)->group('rewards_transfer_id');
        
        return $single_references_select;
         
    }
	
	/**
	 * Also select the rules for the collection
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function addRules() {
		$alias = 'rule_name';
		$this->getSelect ()->joinLeft ( array ('salesrules' => $this->getTable ( 'salesrule/rule' ) ), 'reference_table.rule_id = salesrules.rule_id', array ('rule_id' => "reference_table.rule_id", 'salesrule_name' => "salesrules.name" ) );
		$this->getSelect ()->joinLeft ( array ('catalogrules' => $this->getTable ( 'catalogrule/rule' ) ), 'reference_table.rule_id = catalogrules.rule_id', array ('catalogrule_name' => "catalogrules.name" ) );
		
		//die("<PRE>".$this->getSelect()->__toString(). "</PRE>"); // ,
		

		/*
          $this->_joinFields[$alias] = array(
          'table' => false,
          'field' => $expr
          ); */
		return $this;
	}
	
	/**
	 *
	 * @return TBT_Rewards_Model_Transfer_Reference
	 */
	private function _getRTModel() {
		return Mage::getSingleton ( 'rewards/transfer_reference' );
	}
	
	/**
	 * Adds customer info to select
	 *
	 * @return  TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function selectCurrency() {
		if (! $this->didSelectCurrency) {
			$this->getSelect ()->joinLeft ( array ('currency_table' => $this->getTable ( 'currency' ) ), 'currency_table.rewards_currency_id=main_table.currency_id', array ('currency' => 'caption' ) );
			$this->didSelectCurrency = true;
		}
		return $this;
	}
	
	/**
	 * Add Filter by store
	 * @deprecated not supported in current stable version
	 *
	 * @param int|Mage_Core_Model_Store $store
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function addStoreFilter($store) {
		// TODO WDCA - integral to implementing multi-store capability
		//		if (!Mage::app()->isSingleStoreMode()) {
		//			if ($store instanceof Mage_Core_Model_Store) {
		//				$store = $store->getId();
		//			}
		//
		//			$this->getSelect()->join(
		//				array('store_currency_table' => $this->getTable('store_currency')),
		//				'main_table.currency_id = store_currency_table.currency_id',
		//				array()
		//			)
		//          ->where('store_currency_table.store_id', array('in' => array(0, $store)));
		//          return $this;
		//      }
		return $this;
	}
	
	/**
	 * Add Filter by store
	 *
	 * @param int|Mage_Core_Model_Store $store
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function addFullNameFilter($store) {
		if (! Mage::app ()->isSingleStoreMode ()) {
			if ($store instanceof Mage_Core_Model_Store) {
				$store = array ($store->getId () );
			}
			
			$this->getSelect ()->join ( array ('store_currency_table' => $this->getTable ( 'store_currency' ) ), 'main_table.currency_id = store_currency_table.currency_id', array () )->where ( 'store_currency_table.store_id in (?)', array (0, $store ) );
			
			return $this;
		}
		return $this;
	}
	
	/**
	 * Adds customer info to select
	 *
	 * @return  TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function selectCustomerName() {
		if (! $this->didSelectCustomerName) {
			/* @var $customer TBT_Rewards_Model_Customer */
			$customer = Mage::getModel ( 'rewards/customer' );
			$firstname = $customer->getAttribute ( 'firstname' );
			$lastname = $customer->getAttribute ( 'lastname' );
			
			//        $customersCollection = Mage::getModel('customer/customer')->getCollection();
			//        /* @var $customersCollection Mage_Customer_Model_Entity_Customer_Collection */
			//        $firstname = $customersCollection->getAttribute('firstname');
			//        $lastname  = $customersCollection->getAttribute('lastname');
			

			$this->getSelect ()->joinLeft ( array ('customer_lastname_table' => $lastname->getBackend ()->getTable () ), 'customer_lastname_table.entity_id=main_table.customer_id
                 AND customer_lastname_table.attribute_id = ' . ( int ) $lastname->getAttributeId () . '
                 ', array ('customer_lastname' => 'value' ) )->joinLeft ( array ('customer_firstname_table' => $firstname->getBackend ()->getTable () ), 'customer_firstname_table.entity_id=main_table.customer_id
                 AND customer_firstname_table.attribute_id = ' . ( int ) $firstname->getAttributeId () . '
                 ', array ('customer_firstname' => 'value' ) );
			$this->didSelectCustomerName = true;
		}
		return $this;
	}
	
	/**
	 * Adds the full customer name to the query.
	 *
	 * @param string|$alias What to name the column
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function selectFullCustomerName($alias = 'fullname') {
		$this->selectCustomerName ();
		
		$fields = array ();
		$fields ['firstname'] = 'firstname';
		$fields ['lastname'] = 'firstname';
		
		$expr = 'CONCAT(' . (isset ( $fields ['prefix'] ) ? 'IF({{prefix}} IS NOT NULL AND {{prefix}} != "", CONCAT({{prefix}}," "), ""),' : '') . '{{firstname}}' . (isset ( $fields ['middlename'] ) ? ',IF({{middlename}} IS NOT NULL AND {{middlename}} != "", CONCAT(" ",{{middlename}}), "")' : '') . '," ",{{lastname}}' . (isset ( $fields ['suffix'] ) ? ',IF({{suffix}} IS NOT NULL AND {{suffix}} != "", CONCAT(" ",{{suffix}}), "")' : '') . ')';
		
		$expr = str_replace ( "{{firstname}}", "customer_firstname_table.value", $expr );
		$expr = str_replace ( "{{lastname}}", "customer_lastname_table.value", $expr );
		
		$fullExpression = $expr;
		
		$this->getSelect ()->from ( null, array ($alias => $fullExpression ) );
		
		$this->_joinFields [$alias] = array ('table' => false, 'field' => $fullExpression );
		return $this;
	}
	
	/**
	 * Adds the full customer name to the query.
	 *
	 * @param string|$alias What to name the column
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function addFullCustomerNameFilter($filter) {
		$this->selectCustomerName ();
		
		$fields = array ();
		$fields ['firstname'] = 'firstname';
		$fields ['lastname'] = 'firstname';
		
		$expr = 'CONCAT(' . (isset ( $fields ['prefix'] ) ? 'IF({{prefix}} IS NOT NULL AND {{prefix}} != "", CONCAT({{prefix}}," "), ""),' : '') . '{{firstname}}' . (isset ( $fields ['middlename'] ) ? ',IF({{middlename}} IS NOT NULL AND {{middlename}} != "", CONCAT(" ",{{middlename}}), "")' : '') . '," ",{{lastname}}' . (isset ( $fields ['suffix'] ) ? ',IF({{suffix}} IS NOT NULL AND {{suffix}} != "", CONCAT(" ",{{suffix}}), "")' : '') . ')';
		
		$expr = str_replace ( "{{firstname}}", "customer_firstname_table.value", $expr );
		$expr = str_replace ( "{{lastname}}", "customer_lastname_table.value", $expr );
		
		$fullExpression = $expr;
		//$this->getSelect()->where($fullExpression, array('LIKE' => "%".$filter));
		

		return $this;
	}
	
	/**
	 * Adds the full customer name to the query.
	 *
	 * @param string|$alias What to name the column
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function selectPointsCaption($alias = 'points') {
		$this->selectCurrency ();
		
		$expr = 'CONCAT({{quantity}}, \' \', {{currency_caption}})';
		
		$expr = str_replace ( "{{currency_caption}}", "currency_table.caption", $expr );
		$expr = str_replace ( "{{quantity}}", "main_table.quantity", $expr );
		
		$fullExpression = $expr;
		
		$this->getSelect ()->from ( null, array ($alias => $fullExpression ) );
		
		$this->_joinFields [$alias] = array ('table' => false, 'field' => $fullExpression );
		return $this;
	}
	
	/**
	 * Only fetches points distributon types
	 *
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function selectOnlyDistributions() {
		$reasons = Mage::getSingleton ( 'rewards/transfer_reason' )->getDistributionReasonIds ();
		$this->getSelect ()->where ( 'main_table.reason_id IN (?)', array (0, $reasons ) )->order ( 'main_table.creation_ts DESC' );
		
		return $this;
	}
	
	/**
	 * Only fetches point redemption types
	 *
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function selectOnlyRedemptions() {
		$reasons = Mage::getSingleton ( 'rewards/transfer_reason' )->getRedemptionReasonIds ();
		$this->getSelect ()->where ( 'main_table.reason_id IN (?)', array (0, $reasons ) )->order ( 'main_table.creation_ts DESC' );
		
		return $this;
	}
	
	/**
	 * Only Fetches non redemption and non distribution types
	 *
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function selectOnlyOtherTransfers() {
		$reasons = Mage::getSingleton ( 'rewards/transfer_reason' )->getOtherReasonIds ();
		$this->getSelect ()->where ( 'main_table.reason_id IN (?)', array (0, $reasons ) )->order ( 'main_table.creation_ts DESC' );
		
		return $this;
	}
	
	/**
	 * Fetches only transfers that give points to the customer
	 *
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function selectOnlyPosTransfers() {
		$this->addFieldToFilter ( 'quantity', array ('gt' => 0 ) );
		return $this;
	}
	
	/**
	 * Fetches only transfers that deduct points from the customer
	 *
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 */
	public function selectOnlyNegTransfers() {
		$this->addFieldToFilter ( 'quantity', array ('lt' => 0 ) );
		return $this;
	}
	
	public function selectOnlyActive() {
		$countableStatusIds = Mage::getSingleton ( 'rewards/transfer_status' )->getCountableStatusIds ();
		$this->getSelect ()->where ( 'main_table.status IN (?)', $countableStatusIds );
		
		return $this;
	}
	
	/**
	 * Sums up the points by currency and grouped again by customer.
	 *
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection 
	 */
	public function groupByCustomers() {
		$this->selectCurrency ();
		
		$this->getSelect ()->group ( 'main_table.customer_id' );
		$this->sumPoints ();
		$this->getSelect ()->from ( null, array ("points" => "CONCAT(SUM(main_table.quantity), ' ', currency_table.caption)" ) );
		$this->getSelect ()->from ( null, array ("last_changed_ts" => "MAX(main_table.creation_ts)" ) );
		
		return $this;
	}
	
	public function groupByCurrency() {
		return $this->sumPoints ();
	}
	
	/**
	 * Sums up the points in the collection as the "points_count" field for
	 * each currency.
	 * <b>Please use the 'points_count' field instead of the quantity field</b>
	 * 
	 * @return TBT_Rewards_Model_Mysql4_Transfer_Collection
	 *
	 */
	public function sumPoints() {
		$this->getSelect ()->group ( 'main_table.currency_id' );
		$this->getSelect ()->from ( null, array ("points_count" => "SUM(main_table.quantity)" ) );
		return $this;
	}
	
	/**
	 * True if the collection only contains zero-point transfers (for some reason)
	 * or if the summed point quantities are zero for all currencies
	 * or if the collection does not contain any transfers.
	 *
	 * @return boolean
	 */
	public function isNoPoints() {
		foreach ( $this->getItems () as $item ) {
			if (isset ( $item ['points_count'] )) {
				if ($item ['points_count'] > 0) {
					return false;
				}
			} elseif (isset ( $item ['quantity'] )) {
				if ($item ['quantity'] > 0) {
					return false;
				}
			} else {
				// should never get here...	
			}
		}
		return true;
	}

}