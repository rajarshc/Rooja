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
 
class IG_CashOnDelivery_Block_Admin_Foreign_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
			'id'		=> 'edit_form',
			'action'	=> $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
			'method'	=> 'post',
		));
		
		$fieldset = $form->addFieldset('ig_cashondelivery_foreign', array('legend' => Mage::helper('ig_cashondelivery')->__('Cash on delivery fee')));

		$fieldset->addField('amount_from', 'text', array(
			'label'     => Mage::helper('ig_cashondelivery')->__('Apply from amount'),
			'required'  => true,
			'name'      => 'amount_from',
		));
		
		$fieldset->addField('fee_mode', 'select', array(
			'label'     => Mage::helper('ig_cashondelivery')->__('Apply'),
			'required'  => true,
			'name'      => 'fee_mode',
			'options'	=> array(
				'absolute'	=> Mage::helper('ig_cashondelivery')->__('By Fixed Amount'),
				'percent'	=> Mage::helper('ig_cashondelivery')->__('By Percentage'),
			)
		));

		$fieldset->addField('apply_fee', 'text', array(
			'label'     => Mage::helper('ig_cashondelivery')->__('Fee Amount'),
			'required'  => true,
			'name'      => 'apply_fee',
		));
		
		if (Mage::getSingleton('adminhtml/session')->getIgCashondeliveryData())
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getIgCashondeliveryData());
			Mage::getSingleton('adminhtml/session')->getIgCashondeliveryData(null);
		} 
		elseif (Mage::registry('ig_cashondelivery_data'))
		{
			$form->setValues(Mage::registry('ig_cashondelivery_data')->getData());
		}

		$form->setUseContainer(true);
		$this->setForm($form);
		
		return parent::_prepareForm();
	}
}