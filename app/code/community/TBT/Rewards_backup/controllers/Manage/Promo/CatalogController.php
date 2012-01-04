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
 * Manage Promo Catalog Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
require_once ('app/code/community/TBT/Rewards/controllers/Manage/SweettoothController.php');
class TBT_Rewards_Manage_Promo_CatalogController extends TBT_Rewards_Manage_SweettoothController {
	protected function _construct() {
		$this->_usedModuleName = 'rewards';
		parent::_construct ();
	}
	
	protected function _initAction() {
		$this->loadLayout ()->_setActiveMenu ( 'rewards/rules/catalog' )->_addBreadcrumb ( Mage::helper ( 'rewards' )->__ ( 'Catalog Point Rules' ), Mage::helper ( 'rewards' )->__ ( 'Catalog Point Rule' ) );
		return $this;
	}
	
	public function indexAction() {
		$type = $this->getRequest ()->getParam ( 'type' ); // redemption type or distribution type
		

		$this->_initAction ();
		$this->_addBreadcrumb ( Mage::helper ( 'rewards' )->__ ( 'Catalog' ), Mage::helper ( 'rewards' )->__ ( 'Catalog' ) );
		
		if (Mage::helper ( 'rewards/rule_type' )->isRedemption ( $type )) {
			$this->_addContent ( $this->getLayout ()->createBlock ( 'rewards/manage_promo_catalog_redemptions' ) );
		} else {
			$this->_addContent ( $this->getLayout ()->createBlock ( 'rewards/manage_promo_catalog_distributions' ) );
		}
		$this->renderLayout ();
	}
	
	public function newAction() {
		$this->editAction ();
	}
	
	public function editAction($id = null) {
		if ($id == null) {
			$id = $this->getRequest ()->getParam ( 'id' );
		}
		$model = Mage::getModel ( 'rewards/catalogrule_rule' );
		
		if ($id) {
			$model->load ( $id );
			if (! $model->getRuleId ()) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'catalogrule' )->__ ( 'This rule no longer exists' ) );
				$this->_redirect ( '*/*' );
				return;
			}
		}
		$type = $model->getRuleTypeId (); // redemption type or distribution type
		if (! $type) {
			$type = $this->getRequest ()->getParam ( 'type' );
		}
		
		// set entered data if was error when we do save
		$data = Mage::getSingleton ( 'adminhtml/session' )->getPageData ( true );
		if (! empty ( $data )) {
			$model->addData ( $data );
		}
		$model->getConditions ()->setJsFormObject ( 'rule_conditions_fieldset' );
		
		Mage::register ( 'current_promo_catalog_rule', $model );
		
		$block = $this->getLayout ()->createBlock ( 'rewards/manage_promo_catalog_edit' )->setData ( 'action', $this->getUrl ( '*/promo_catalog/save', array ('type' => $type ) ) );
		
		$this->_initAction ();
		
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true )->setCanLoadRulesJs ( true );
		
		$this->_addBreadcrumb ( $id ? Mage::helper ( 'catalogrule' )->__ ( 'Edit Rule' ) : Mage::helper ( 'catalogrule' )->__ ( 'New Rule' ), $id ? Mage::helper ( 'catalogrule' )->__ ( 'Edit Rule' ) : Mage::helper ( 'catalogrule' )->__ ( 'New Rule' ) )->_addContent ( $block )->_addLeft ( $this->getLayout ()->createBlock ( 'rewards/manage_promo_catalog_edit_tabs' ) )->renderLayout ();
	}
	
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			$model = Mage::getModel ( 'rewards/catalogrule_rule' );
			$data ['conditions'] = $data ['rule'] ['conditions'];
			unset ( $data ['rule'] );
			
			if (! empty ( $data ['auto_apply'] )) {
				$autoApply = true;
				unset ( $data ['auto_apply'] );
			} else {
				$autoApply = false;
			}
			
			// If we're running Magento 1.4...
			if (Mage::helper ( 'rewards' )->isBaseMageVersionAtLeast ( '1.4' )) {
				$data = $this->_filterDates ( $data, array ('from_date', 'to_date' ) );
				$validateResult = $model->validateData ( new Varien_Object ( $data ) );
				if ($validateResult !== true) {
					foreach ( $validateResult as $errorMessage ) {
						$this->_getSession ()->addError ( $errorMessage );
					}
					$this->_getSession ()->setPageData ( $data );
					$this->_redirect ( '*/*/', array ('type' => $model->getRuleTypeId (), 'id' => $model->getId () ) );
					return;
				}
			}
			
			$model->loadPost ( $data );
			Mage::getSingleton ( 'adminhtml/session' )->setPageData ( $model->getData () );
			try {
				$model->save ();
				$id = $model->getId ();
				$type = $model->getRuleTypeId (); // redemption type or distribution type
				//Mage::register('current_promo_catalog_rule', $model);
				

				// Save labels
				$storeLabels = $this->getRequest ()->getParam ( 'store_labels', array () );
				$label = Mage::getModel ( 'rewards/catalogrule_label' );
				foreach ( $storeLabels as $storeId => $storeLabel ) {
					$storeLabel = trim ( $storeLabel );
					if (! empty ( $storeLabel )) {
						// Save new store label data
						try {
							$label->setData ( array ('store_id' => $storeId, 'rule_id' => $model->getId (), 'label' => $storeLabel ) )->save ();
						} catch ( Zend_Db_Statement_Exception $e ) {
							// Search for existing labels
							$labels = $label->getLabelsByRuleAndStoreId ( $model, $storeId );
							if ($labels->count ()) {
								$labels->getFirstItem ()->setLabel ( $storeLabel )->save ();
							}
						}
					} else {
						$label->removeLabelsByRuleAndStoreId ( $model, $storeId );
					}
				}
				// End save labels
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'catalogrule' )->__ ( 'Rule was successfully saved' ) );
				Mage::getSingleton ( 'adminhtml/session' )->setPageData ( false );
				if ($autoApply) {
					$this->_forward ( 'applyRules', 'manage_promo_catalog', 'rewardsadmin', array ('type' => $type, 'id' => $model->getId () ) );
				
		//$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
				} else {
					Mage::getSingleton ( 'adminhtml/session' )->addNotice ( Mage::helper ( 'catalogrule' )->__ ( 'You must still APPLY rules before they will take effect!' ) );
					Mage::app ()->saveCache ( 1, 'catalog_rules_dirty' );
					$this->_redirect ( '*/*/', array ('type' => $type, 'id' => $model->getId () ) );
				}
				return;
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				Mage::getSingleton ( 'adminhtml/session' )->setPageData ( $data );
				$this->_redirect ( '*/*/edit', array ('id' => $this->getRequest ()->getParam ( 'rule_id' ), 'type' => $this->getRequest ()->getParam ( 'type' ) ) );
				return;
			}
		}
		$this->_redirect ( '*/*/' );
	}
	
	public function deleteAction() {
		$type = $this->getRequest ()->getParam ( 'type' ); // redemption type or distribution type
		if ($id = $this->getRequest ()->getParam ( 'id' )) {
			try {
				$model = Mage::getModel ( 'rewards/catalogrule_rule' );
				$model->load ( $id );
				if (! $model->getId ()) {
					Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'catalogrule' )->__ ( 'The rule you are trying to delete no longer exists' ) );
					Mage::getSingleton ( 'adminhtml/session' )->setPageData ( $data );
					$this->_redirect ( '*/*/edit', array ('type' => $type ) );
					return;
				}
				$id = $model->getId ();
				$type = $model->getRuleTypeId (); // redemption type or distribution type
				$model->delete ();
				Mage::app ()->saveCache ( 1, 'catalog_rules_dirty' );
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'catalogrule' )->__ ( 'Rule was successfully deleted' ) );
				$this->_redirect ( '*/*/', array ('type' => $type ) );
				return;
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				$this->_redirect ( '*/*/edit', array ('id' => $this->getRequest ()->getParam ( 'id' ), 'type' => $type ) );
				return;
			}
		}
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'catalogrule' )->__ ( 'Unable to find a page to delete' ) );
		$this->_redirect ( '*/*/', array ('type' => $type ) );
	}
	
	public function newConditionHtmlAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$typeArr = explode ( '|', str_replace ( '-', '/', (string)$this->getRequest ()->getParam ( 'type' ) ) );
		$type = $typeArr [0];
		
		$model = Mage::getModel ( $type )->setId ( $id )->setType ( $type )->setRule ( Mage::getModel ( 'catalogrule/rule' ) )->setPrefix ( 'conditions' );
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
	
	public function chooserAction() {
		switch ($this->getRequest ()->getParam ( 'attribute' )) {
			case 'sku' :
				$type = 'adminhtml/promo_widget_chooser_sku';
				break;
			
			case 'categories' :
				$type = 'adminhtml/promo_widget_chooser_categories';
				break;
		}
		if (! empty ( $type )) {
			$block = $this->getLayout ()->createBlock ( $type );
			if ($block) {
				$this->getResponse ()->setBody ( $block->toHtml () );
			}
		}
	}
	
	public function newActionHtmlAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$typeArr = explode ( '|', str_replace ( '-', '/', (string)$this->getRequest ()->getParam ( 'type' ) ) );
		$type = $typeArr [0];
		
		$model = Mage::getModel ( $type )->setId ( $id )->setType ( $type )->setRule ( Mage::getModel ( 'catalogrule/rule' ) )->setPrefix ( 'actions' );
		if (! empty ( $typeArr [1] )) {
			$model->setAttribute ( $typeArr [1] );
		}
		
		if ($model instanceof Mage_Rule_Model_Action_Abstract) {
			$model->setJsFormObject ( $this->getRequest ()->getParam ( 'form' ) );
			$html = $model->asHtmlRecursive ();
		} else {
			$html = '';
		}
		$this->getResponse ()->setBody ( $html );
	}
	
	/**
	 * Apply all active catalog price rules
	 */
	public function applyRulesAction() {
		try {
			if (Mage::helper ( 'rewards/version' )->isMageVersionAtLeast ( '1.4.2' )) {
				Mage::getModel ( 'catalogrule/rule' )->applyAll ();
			} else {
				$resource = Mage::getResourceSingleton ( 'catalogrule/rule' );
				$resource->applyAllRulesForDateRange ();
			}
			Mage::app ()->removeCache ( 'catalog_rules_dirty' );
			Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'catalogrule' )->__ ( 'Rules were successfully applied' ) );
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'catalogrule' )->__ ( 'Unable to apply rules.' ) );
			throw $e;
		}
		$this->indexAction ();
	}
	
	public function addToAlertsAction() {
		$alerts = Mage::getResourceModel ( 'customeralert/type' )->getAlertsForCronChecking ();
		foreach ( $alerts as $val ) {
			Mage::getSingleton ( 'customeralert/config' )->getAlertByType ( 'price_is_changed' )->setParamValues ( $val )->updateForPriceRule ();
		}
	}
	
	protected function _isAllowed() {
		return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'rewards/rules' );
	}
	
	public function preDispatch() {
		if (! Mage::helper ( 'rewards/loyalty_checker' )->isValid ()) {
			Mage::throwException ( "Please check your Sweet Tooth registration code your Magento configuration settings, or contact WDCA through contact@wdca.ca for a description of this problem." );
		}
		parent::preDispatch ();
	}

}
