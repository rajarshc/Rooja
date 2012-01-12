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
 
class IG_CashOnDelivery_AdminController extends Mage_Adminhtml_Controller_Action
{
	protected $_ig_cod_zone='';
	
	public function indexAction()
	{
		if (!$this->_ig_cod_zone) { $this->norouteAction(); return; }
		
		$this->loadLayout()
			->_addContent($this->getLayout()->createBlock('ig_cashondelivery/admin_'.$this->_ig_cod_zone))
			->renderLayout();
	}
	
	public function newAction()
	{
		if (!$this->_ig_cod_zone) { $this->norouteAction(); return; }
		
		$this->_forward('edit');
	}
	
	public function editAction()
	{
		if (!$this->_ig_cod_zone) { $this->norouteAction(); return; }
		
		$id		= $this->getRequest()->getParam('id');
		$model	= Mage::getModel('ig_cashondelivery/'.$this->_ig_cod_zone)->load($id);
	
		if ($model->getId() || $id == 0)
		{
			Mage::register('ig_cashondelivery_data', $model);
			
			$this->loadLayout();
			$this->_addContent($this->getLayout()->createBlock('ig_cashondelivery/admin_'.$this->_ig_cod_zone.'_edit'));
			$this->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ig_cashondelivery')->__('Item does not exist'));
			$this->_redirect('*/*/index');
		}
	}
	
	public function saveAction()
    {
    	if (!$this->_ig_cod_zone) { $this->norouteAction(); return; }
    	
		if ($this->getRequest()->getPost())
		{
			try
			{
				$postData = $this->getRequest()->getPost();
				$model = Mage::getModel('ig_cashondelivery/'.$this->_ig_cod_zone);
				
				$model->setId($this->getRequest()->getParam('id'))
					->setAmountFrom($postData['amount_from'])
					->setApplyFee($postData['apply_fee'])
					->setFeeMode($postData['fee_mode'])
					->save();
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Rule was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setCashOndeliveryLocalData(false);
		
				$this->_redirect('*/*/index');
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setCashOndeliveryLocalData($this->getRequest()->getPost());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		
		$this->_redirect('*/*/index');
    }
    
    public function deleteAction()
	{
		if (!$this->_ig_cod_zone) { $this->norouteAction(); return; }
		
		if ($this->getRequest()->getParam('id') > 0)
		{
			try
			{
				$model = Mage::getModel('ig_cashondelivery/'.$this->_ig_cod_zone);
				
				$model->setId($this->getRequest()->getParam('id'))->delete();
					
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/index');
			}
			catch (Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		
		$this->_redirect('*/*/index');
	}
}