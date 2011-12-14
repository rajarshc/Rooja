<?php
/**
 * TODO: license
 */
/**
 * Adminhtml grid container block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class TBT_Rewards_Block_Manage_Widget_Grid_Container extends Mage_Adminhtml_Block_Widget_Grid_Container {
	
	public function __construct() {
		parent::__construct ();
		$this->setTemplate ( 'rewards/widget/grid/container.phtml' );
	
	}
	
	public function getHeaderWidth() {
		return '';
	}

}
