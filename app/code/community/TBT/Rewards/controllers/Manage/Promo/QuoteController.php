<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require_once ('app/code/community/TBT/Rewards/controllers/Manage/SweettoothController.php');

class TBT_Rewards_Manage_Promo_QuoteController extends TBT_Rewards_Manage_SweettoothController {
	
	protected function _construct() {
		$this->_usedModuleName = 'rewards';
		parent::_construct ();
	}
	
	protected function _initRule() {
		Mage::register ( 'current_promo_quote_rule', Mage::getModel ( 'rewards/salesrule_rule' ) );
		if ($id = ( int ) $this->getRequest ()->getParam ( 'id' )) {
			Mage::registry ( 'current_promo_quote_rule' )->load ( $id );
		}
	}
	
	protected function _initAction() {
		$this->loadLayout ()->_setActiveMenu ( 'rewards/rules/quote' )->_addBreadcrumb ( Mage::helper ( 'rewards' )->__ ( 'Promotions' ), Mage::helper ( 'rewards' )->__ ( 'Promotions' ) );
		return $this;
	}
	
	public function indexAction() {
		$type = $this->getRequest ()->getParam ( 'type' ); // redemption type or distribution type
		

		$this->_initAction ()->_addBreadcrumb ( Mage::helper ( 'rewards' )->__ ( 'Catalog' ), Mage::helper ( 'rewards' )->__ ( 'Catalog' ) );
		if (Mage::helper ( 'rewards/rule_type' )->isRedemption ( $type )) {
			$this->_addContent ( $this->getLayout ()->createBlock ( 'rewards/manage_promo_quote_redemptions' ) );
		} else {
			$this->_addContent ( $this->getLayout ()->createBlock ( 'rewards/manage_promo_quote_distributions' ) );
		}
		$this->renderLayout ();
	}
	
	public function newAction() {
		$this->editAction ();
	}
	
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$type = $this->getRequest ()->getParam ( 'type' ); // redemption type or distribution type
		$model = Mage::getModel ( 'rewards/salesrule_rule' );
		
		if ($id) {
			$model->load ( $id );
			if (! $model->getRuleId ()) {
				//print_r($model->getData());
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'rewards' )->__ ( 'This rule no longer exists' ) );
				$this->_redirect ( '*/*', array ('type' => $type ) );
				return;
			}
			$type = $model->getRuleTypeId (); // redemption type or distribution type
		}
		// set entered data if was error when we do save
		$data = Mage::getSingleton ( 'adminhtml/session' )->getPageData ( true );
		if (! empty ( $data )) {
			$model->addData ( $data );
		}
		
		$model->getConditions ()->setJsFormObject ( 'rule_conditions_fieldset' );
		$model->getActions ()->setJsFormObject ( 'rule_actions_fieldset' );
		
		Mage::register ( 'current_promo_quote_rule', $model );
		
		$block = $this->getLayout ()->createBlock ( 'rewards/manage_promo_quote_edit' )->setData ( 'action', $this->getUrl ( '*/*/save', array ('type' => $type ) ) );
		
		$this->_initAction ();
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true )->setCanLoadRulesJs ( true );
		
		$this->_addBreadcrumb ( $id ? Mage::helper ( 'rewards' )->__ ( 'Edit Rule' ) : Mage::helper ( 'rewards' )->__ ( 'New Rule' ), $id ? Mage::helper ( 'rewards' )->__ ( 'Edit Rule' ) : Mage::helper ( 'rewards' )->__ ( 'New Rule' ) )->_addContent ( $block )->_addLeft ( $this->getLayout ()->createBlock ( 'rewards/manage_promo_quote_edit_tabs' ) )->renderLayout ();
	}
	
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			$model = Mage::getModel ( 'rewards/salesrule_rule' );
			
			if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4' )) {
				$data = $this->_filterDates ( $data, array ('from_date', 'to_date' ) );
				$id = $model->getId ();
				$session = Mage::getSingleton ( 'adminhtml/session' );
				
				$validateResult = $model->validateData ( new Varien_Object ( $data ) );
				if ($validateResult !== true) {
					foreach ( $validateResult as $errorMessage ) {
						$session->addError ( $errorMessage );
					}
					$session->setPageData ( $data );
					$this->_redirect ( '*/*/', array ('type' => $model->getRuleTypeId (), 'id' => $model->getId () ) );
					return;
				}
			}
			
			if (isset ( $data ['simple_action'] ) && $data ['simple_action'] == 'by_percent' && isset ( $data ['discount_amount'] )) {
				$data ['discount_amount'] = min ( 100, $data ['discount_amount'] );
			}
			if (isset ( $data ['rule'] ['conditions'] )) {
				$data ['conditions'] = $data ['rule'] ['conditions'];
			}
			if (isset ( $data ['rule'] ['actions'] )) {
				$data ['actions'] = $data ['rule'] ['actions'];
			}
			unset ( $data ['rule'] );
			try {
				
				$model->loadPost ( $data );
				Mage::getSingleton ( 'adminhtml/session' )->setPageData ( $model->getData () );
				$type = $model->getRuleTypeId (); // redemption type or distribution type
				

				$model->save ();
				$type = $model->getRuleTypeId (); // redemption type or distribution type (again in case it changed)
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'Rule was successfully saved' ) );
				Mage::getSingleton ( 'adminhtml/session' )->setPageData ( false );
				$this->_redirect ( '*/*/', array ('type' => $type, 'id' => $model->getId () ) );
				return;
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				Mage::getSingleton ( 'adminhtml/session' )->setPageData ( $data );
				$this->_redirect ( '*/*/edit', array ('id' => $this->getRequest ()->getParam ( 'rule_id' ), 'type' => $type ) );
				return;
			}
		}
		$this->_redirect ( '*/*/' );
	}
	
	public function deleteAction() {
		$type = $this->getRequest ()->getParam ( 'type' ); // redemption type or distribution type
		$id = $this->getRequest ()->getParam ( 'id' );
		try {
			$model = Mage::getModel ( 'rewards/salesrule_rule' );
			if (! $id) {
				throw new Exception ( 'No rule specified, so the rule could not be deleted.' );
			}
			$model->load ( $id );
			if (! $model->getId ()) {
				throw new Exception ( Mage::helper ( 'rewards' )->__ ( 'The rule you are trying to delete no longer exists' ) );
			}
			$id = $model->getId ();
			$type = $model->getRuleTypeId (); // redemption type or distribution type
			$model->delete ();
			Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'Rule was successfully deleted' ) );
			$this->_redirect ( '*/*/', array ('type' => $type ) );
			return;
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
			Mage::getSingleton ( 'adminhtml/session' )->setPageData ( $this->getRequest ()->getPost () );
			$this->_redirect ( '*/*/edit', array ('id' => $id, 'type' => $type ) );
			return;
		}
	}
	
	public function newConditionHtmlAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$typeArr = explode ( '|', str_replace ( '-', '/', (string)$this->getRequest ()->getParam ( 'type' ) ) );
		$type = $typeArr [0];
		
		$model = Mage::getModel ( $type )->setId ( $id )->setType ( $type )->setRule ( Mage::getModel ( 'salesrule/rule' ) )->setPrefix ( 'conditions' );
		if (! empty ( $typeArr [1] )) {
			$model->setAttribute ( $typeArr [1] );
		}
		
		if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
			$model->setJsFormObject ( $this->getRequest ()->getParam ( 'form' ) );
			$html = $model->asHtmlRecursive ();
		} else {
			$html = '';
		}
		$this->getResponse ()->setBody ( $html );
	}
	
	public function newActionHtmlAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$typeArr = explode ( '|', str_replace ( '-', '/', (string)$this->getRequest ()->getParam ( 'type' ) ) );
		$type = $typeArr [0];
		
		$model = Mage::getModel ( $type )->setId ( $id )->setType ( $type )->setRule ( Mage::getModel ( 'salesrule/rule' ) )->setPrefix ( 'actions' );
		if (! empty ( $typeArr [1] )) {
			$model->setAttribute ( $typeArr [1] );
		}
		
		if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
			$model->setJsFormObject ( $this->getRequest ()->getParam ( 'form' ) );
			$html = $model->asHtmlRecursive ();
		} else {
			$html = '';
		}
		$this->getResponse ()->setBody ( $html );
	}
	
	public function applyRulesAction() {
		$this->_initAction ();
		$this->renderLayout ();
	}
	
	public function gridAction() {
		$this->_initRule ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'rewards/manage_promo_quote_edit_tab_product' )->toHtml () );
	}
	
	protected function _isAllowed() {
		return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'promo/quote' );
	}
	
	public function preDispatch() {
		if (! Mage::helper ( 'rewards/loyalty_checker' )->isValid ()) {
			Mage::throwException ( "Please check your Sweet Tooth registration code your Magento configuration settings, or contact WDCA through contact@wdca.ca for a description of this problem." );
		}
		parent::preDispatch ();
	}
	
/* added in ... 1.4.1.**
      /**
     * Chooser source action
      public function chooserAction()
      {
      $uniqId = $this->getRequest()->getParam('uniq_id');
      $chooserBlock = $this->getLayout()->createBlock('adminhtml/promo_widget_chooser', '', array('id' => $uniqId ));
      $this->getResponse()->setBody($chooserBlock->toHtml());
      }
     */
}
