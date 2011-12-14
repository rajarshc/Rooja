<?php

class TBT_Rewards_Model_Mysql4_Catalogrule_Label extends Mage_Core_Model_Mysql4_Abstract {
	/* Resource constructor
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 */
	protected function _construct() {
		$this->_init ( 'rewards/catalogrule_label', 'label_id' );
	}

}