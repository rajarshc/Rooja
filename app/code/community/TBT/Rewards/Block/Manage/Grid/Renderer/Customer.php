<?php

/**
 * WDCA - Sweet Tooth
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL:
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * By adding to, editing, or in any way modifying this code, WDCA is
 * not held liable for any inconsistencies or abnormalities in the
 * behaviour of this code.
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the
 * provided Sweet Tooth License.
 * Upon discovery of modified code in the process of support, the Licensee
 * is still held accountable for any and all billable time WDCA spent
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension.
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy
 * immediately.
 *
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer Renderer for customer points grid
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Grid_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	
	/**
	 * Contains a list of customers
	 * 
	 * @var array
	 */
	protected $_customers = array ();
	
	/**
	 * Renderer of the customer name
	 *
	 * @param Varien_Object $row
	 * @return string
	 */
	public function render(Varien_Object $row) {
		$str = '';
		if ($cid = $row->getId ()) {
			if ($customer = $this->_getCustomer ( $cid )) {
				$str = $customer->getName ();
				$url = $this->getUrl ( 'adminhtml/customer/edit/', array ('id' => $cid, 'rback' => $this->getUrlBase64 ( '*/*/' ) ) );
				$str = '<a href="' . $url . '">' . $str . '</a>';
			}
		}
		return $str;
	}
	
	/**
	 * Tries to load a customer from $this->_customer.
	 * If not present, loads a new customer from rewards/customer model
	 *
	 * @param int $cid
	 * @return TBT_Rewards_Model_Customer|bool
	 */
	protected function _getCustomer($cid) {
		if (isset ( $this->_customers [$cid] )) {
			return Mage::getModel('rewards/customer')->getRewardsCustomer($this->_customers [$cid]);
		}
	    
		$customer = Mage::getModel ( 'rewards/customer' )->load ( $cid );
		if ($customer->getId ()) {
			$this->_customers [$cid] = $customer;
			return $this->_customers [$cid];
		}
		return false;
	}

}