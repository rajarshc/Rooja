<?php

class TBT_Rewards_Model_Tag_Observer extends Varien_Object {
	
	/**
	 * The wrapped tag
	 * @var TBT_Rewards_Model_Tag_Wrapper
	 */
	protected $_tag;
	
	/**
	 * @var array
	 */
	protected $oldData;
	
	/**
	 * @var TBT_Rewards_Model_Tag_Wrapper
	 */
	protected $_wrapperModel;
	
	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->_wrapperModel = Mage::getModel ( 'rewards/tag_wrapper' );
	}

    /**
     * Manages the after_load observer 
     * @param Varien_Event_Observer $o
     * @return TBT_Rewards_Model_Tag_Observer
     */
    public function afterLoadTag(Varien_Event_Observer $o) {
        $tag = $o->getEvent()->getObject();
        
        if ( ! ($tag instanceof Mage_Tag_Model_Tag) ) return $this;
        
        //Before you save, pass all current data into a dummy model for comparison later.
        $this->oldData = $tag->getData();
        return $this;
    }

    /**
     * Manages the after_save observer
     * @param Varien_Event_Observer $o
     * @return TBT_Rewards_Model_Tag_Observer
     */
    public function afterSaveTag(Varien_Event_Observer $o) {
        $tag = $o->getEvent()->getObject();
        
        if ( ! ($tag instanceof Mage_Tag_Model_Tag) ) return $this;
        
        $tag = $this->_wrapperModel->wrap($tag);
        //If the tag becomes approved, approve all associated pending tranfser
        if ( $this->oldData['status'] == Mage_Tag_Model_Tag::STATUS_PENDING &&
         $tag->getTag()->getStatus() == Mage_Tag_Model_Tag::STATUS_APPROVED ) {
            $tag->approvePendingTransfers();
        } elseif ( $this->oldData['status'] == Mage_Tag_Model_Tag::STATUS_PENDING &&
         $tag->getTag()->getStatus() == Mage_Tag_Model_Tag::STATUS_DISABLED ) {
            $tag->discardPendingTransfers();
        
     //If the tag is new (hence not having an id before) get applicable rules, 
        //and create a pending transfer for each one
        } elseif ( $tag->getTag()->getTagId() && ! isset($this->oldData['tag_id']) ) {
            $tag->ifNewTag();
        }
        return $this;
    }
	
	/**
	 * @return TBT_Rewards_Model_Tag_Wrapper
	 */
	protected function _getTag() {
		return $this->_tag;
	}
	
	/**
	 * @return TBT_Rewards_Model_Tag_Validator
	 */
	protected function _getTagValidator() {
		return Mage::getSingleton ( 'rewards/tag_validator' );
	}

}