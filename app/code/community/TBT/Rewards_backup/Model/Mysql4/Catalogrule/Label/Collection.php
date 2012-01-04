<?php

class TBT_Rewards_Model_Mysql4_Catalogrule_Label_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
	protected function _construct() {
		$this->_init ( 'rewards/catalogrule_label', 'label_id' );
	}

}