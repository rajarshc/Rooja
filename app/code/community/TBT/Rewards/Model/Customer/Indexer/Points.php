<?php

class TBT_Rewards_Model_Customer_Indexer_Points extends Mage_Index_Model_Indexer_Abstract {
	
	const REWARDS_TRANSFER_ENTITY = 'rewards/transfer';
	const REWARDS_CUSTOMER_ENTITY = 'rewards/customer';
	
	/**
	 * Matched entities list
	 * @name _matchedEntities
	 * @access protected 
	 * @var array
	 */
	protected $_matchedEntities = array (self::REWARDS_TRANSFER_ENTITY => array (Mage_Index_Model_Event::TYPE_SAVE ) );
	
	/**
	 * Class constructor
	 * @see Varien_Object::_construct()
	 */
	protected function _construct() {
		$this->_init ( 'rewards/customer_indexer_points', 'customer_id' );
	}
	
	/**
	 * Indexer name
	 * @see Mage_Index_Model_Indexer_Abstract::getName()
	 */
	public function getName() {
		return Mage::helper ( 'rewards' )->__ ( 'Customer Points Index' );
	}
	
	/**
	 * Indexer description
	 * @see Mage_Index_Model_Indexer_Abstract::getDescription()
	 */
	public function getDescription() {
		return Mage::helper ( 'rewards' )->__ ( 'Index Customer Points Balances' );
	}
	
	
	/**
	 * Match Event
	 * @param Mage_Index_Model_Event $event
	 * @see Mage_Index_Model_Indexer_Abstract::matchEvent()
	 * @return bool
	 */
	public function matchEvent(Mage_Index_Model_Event $event) {
		$data = $event->getNewData ();
		$resultKey = 'rewards_customer_points_match_result';
		if (isset ( $data [$resultKey] )) {
			return $data [$resultKey];
		}
		$result = null;
		$entity = $event->getEntity ();
		if ($entity == self::REWARDS_TRANSFER_ENTITY || $entity == self::REWARDS_CUSTOMER_ENTITY) {
			if ($event->getType () == Mage_Index_Model_Event::TYPE_DELETE) {
				$result = true;
			} else if ($event->getType () == Mage_Index_Model_Event::TYPE_SAVE) {
				/* @var $transfer TBT_Rewards_Model_Transfer */
				$result = true;
			} else {
				$result = false;
			}
		} else {
			$result = parent::matchEvent ( $event );
		}
		 
		$event->addNewData ( $resultKey, $result );
		return $result;
	}
	
	/**
	 * Register event
	 * @see Mage_Index_Model_Indexer_Abstract::_registerEvent()
	 * @param Mage_Index_Model_Event $event
	 * @return void
	 */
	protected function _registerEvent(Mage_Index_Model_Event $event) {
		$process = $event->getProcess ();
		if ($process->getMode () == Mage_Index_Model_Process::MODE_MANUAL) {
			$process->changeStatus ( Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX );
		}
	}
	
	/**
	 * Process event
	 * @param Mage_Index_Model_Event $event
	 * @return void
	 */
	protected function _processEvent(Mage_Index_Model_Event $event) {
		$data = $event->getNewData ();
		$event_model = $event->getDataObject ();
		if (! empty ( $data ['rewards_customer_points_match_result'] )) {
			try {
			    if($event->getEntity() == self::REWARDS_TRANSFER_ENTITY) {
    				if ($event_model) {
    				    $transfer = $event_model;
    					$this->_getResource ()->reindexUpdate ( $transfer->getCustomerId () );
    				}
			    } elseif($event->getEntity() == self::REWARDS_CUSTOMER_ENTITY) {
    				if ($event_model) {
    				    $customer = $event_model;
    					$this->_getResource ()->reindexUpdate ( $customer->getId () );
    				}
			    } else {
			        // Model entity type was not recognized
			    }
			} catch ( Exception $e ) {
			    Mage::logException($e);
			    Mage::helper('rewards/debug')->logException($e);
				$this->reindexAll ();
			}
		}
	}
	
	/**
	 * Fetches the customer rewards session.
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	protected function _getRewardsSess() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	/**
	 * Fetches the checkout session
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getCheckoutSession() {
		return Mage::getSingleton ( 'checkout/session' );
	}

}