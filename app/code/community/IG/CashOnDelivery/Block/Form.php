<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@idealiagroup.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category   IG
 * @package    IG_CashOnDelivery
 * @copyright  Copyright (c) 2010-2011 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Riccardo Tempesta <tempesta@idealiagroup.com>
*/
  
class IG_CashOnDelivery_Block_Form extends Mage_Payment_Block_Form
{
	protected $_order;
	protected $_quote;
	
	protected function getSession()
	{
		if (Mage::getDesign()->getArea() == 'adminhtml')
			return Mage::getSingleton('adminhtml/session_quote');
		
		return Mage::getSingleton('checkout/session');
	}
	
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('ig_cashondelivery/form.phtml');
	}
	
	protected function getOrder()
	{
		if ($this->_order)
			return $this->_order;
			
		$this->_order = Mage::getModel('sales/order');
		$this->_order->loadByIncrementId($this->getSession()->getLastRealOrderId());
		
		return $this->_order;
	}
	
	protected function getQuote()
	{
		if ($this->_quote)
			return $this->_quote;
		
		$this->_quote = $this->getSession()->getQuote();
	
		return $this->_quote;
	}
}
