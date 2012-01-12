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
  
class IG_CashOnDelivery_Model_Cashondelivery extends Mage_Payment_Model_Method_Abstract
{
	protected $_code = 'ig_cashondelivery';
	protected $_paymentMethod = 'ig_cashondelivery';
	protected $_store_config = 'payment/ig_cashondelivery';
	protected $_formBlockType = 'ig_cashondelivery/form';
	
	protected $_isGateway = false;
	protected $_canAuthorize = true;
	protected $_canCapture = false;
	protected $_canCapturePartial = false;
	protected $_canRefund = false;
	protected $_canVoid = true;
	protected $_canUseInternal = true;
	protected $_canUseCheckout = true;
	protected $_canUseForMultishipping = true;
	
	protected $_quote;
	
	protected $_ig_local_table = 'ig_cashondelivery_local';
	protected $_ig_foreign_table = 'ig_cashondelivery_foreign';
	
	protected function getCheckoutSession()
	{
		if (Mage::getDesign()->getArea() == 'adminhtml')
			return Mage::getSingleton('adminhtml/session_quote');
		
		return Mage::getSingleton('checkout/session');
	}
	
	protected function getQuote()
	{
		return $this->getCheckoutSession()->getQuote();
	}
	
	protected function getAmount()
	{
		if ($this->getCheckoutSession()->getSubtotal() && !$this->getQuote()->getSubtotal())
			return $this->getCheckoutSession()->getSubtotal();
			
		$this->getCheckoutSession()->setSubtotal(floatval($this->getQuote()->getSubtotal()));
		return floatval($this->getQuote()->getSubtotal());
	}
	
	public function getExtraLocalFee()
	{
		$read_sql = 'select apply_fee, fee_mode from '.$this->_ig_local_table.' where amount_from < '.$this->getAmount().' order by amount_from desc limit 1';
		$res = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchRow($read_sql);
		
		if ($res['fee_mode'] == 'absolute')
			return floatval($res['apply_fee']);
			
		return ($this->getAmount() / 100) * floatval($res['apply_fee']);
	}
	
	public function getExtraForeignFee()
	{
		$read_sql = 'select apply_fee, fee_mode from '.$this->_ig_foreign_table.' where amount_from < '.$this->getAmount().' order by amount_from desc limit 1';
		$res = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchRow($read_sql);
		
		if ($res['fee_mode'] == 'absolute')
			return floatval($res['apply_fee']);
			
		return ($this->getAmount() / 100) * floatval($res['apply_fee']);
	}
	
	public function getExtraFee()
	{
		if ($this->getQuote()->getShippingAddress()->getCountry() == Mage::getStoreConfig('shipping/origin/country_id'))
			return $this->getExtraLocalFee();
			
		return $this->getExtraForeignFee();
	}
}
