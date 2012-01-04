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
 * Manage Special Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */

require_once (dirname(__FILE__) . DS . 'SweettoothController.php');

class TBT_Rewards_Manage_SpecialController extends TBT_Rewards_Manage_SweettoothController {
	
	protected function _construct() {
		$this->setUsedModuleName ( 'rewards' );
		parent::_construct ();
	}
	
	protected function _initAction() {
		$this->loadLayout ()->_setActiveMenu ( 'rewards/rules/special' );
		return $this;
	}
	
    protected function _isAllowed() {
        return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'rewards/rules' );
    }
	
	public function deleteAction() {
		if ($id = $this->getRequest ()->getParam ( 'id' )) {
			try {
				$model = Mage::getModel ( 'rewards/special' );
				$model->load ( $id );
				$model->delete ();
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'Rule was successfully deleted' ) );
				$this->_redirect ( '*/*/' );
				return;
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				$this->_redirect ( '*/*/edit', array ('id' => $this->getRequest ()->getParam ( 'id' ) ) );
				return;
			}
		}
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'rewards' )->__ ( 'Unable to find a page to delete' ) );
		$this->_redirect ( '*/*/' );
	}
	
	public function newAction() {
		$this->editAction ();
	}
	
	public function editAction() {
		
		$id = $this->getRequest ()->getParam ( 'id' );
		$model = Mage::getModel ( 'rewards/special' );
		
		if ($id) {
			$model->load ( $id );
			if (! $model->getRewardsSpecialId ()) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'rewards' )->__ ( 'This rule no longer exists' ) );
				$this->_redirect ( '*/*' );
				return;
			}
			$model->setIsOnholdEnabled($model->getOnholdDuration() != 0);
		}
		// set entered data if was error when we do save
		$data = Mage::getSingleton ( 'adminhtml/session' )->getPageData ( true );
		
		if (! empty ( $data )) {
			$model->addData ( $data );
		}
		
		Mage::register ( 'global_manage_special_rule', $model );
		
		$block = $this->getLayout()->createBlock('rewards/manage_special_edit');
		
		$url = $this->getUrl ( '*/*/save', array ('id' => $id ) );
		$block->setData ( 'action', $url );
		
		$this->_initAction ();
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true )->setCanLoadRulesJs ( true ); // TODO: remove this line;
		

		$breadcrumb = $id ? Mage::helper ( 'rewards' )->__ ( 'Edit Rule' ) : Mage::helper ( 'rewards' )->__ ( 'New Rule' );
		$this->_addBreadcrumb ( $breadcrumb, $breadcrumb )->// (label, title)
_addContent ( $block )->_addLeft ( $this->getLayout ()->createBlock ( 'rewards/manage_special_edit_tabs' ) )->renderLayout ();
	}
	
	public function gridAction() {
		$this->_initRule ();
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'rewards/manage_special_edit_tab_product' )->toHtml () );
	}
	
	public function indexAction() {
		$this->_initAction ()->renderLayout ();
	}
	
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			$model = Mage::getModel ( 'rewards/special' );
			
			if (is_array ( $data ['customer_group_ids'] )) {
				$data ['customer_group_ids'] = implode ( ',', $data ['customer_group_ids'] );
			}
			if (is_array ( $data ['website_ids'] )) {
				$data ['website_ids'] = implode ( ',', $data ['website_ids'] );
			}
			if (!$data['is_onhold_enabled']) {
			    $data['onhold_duration'] = 0;
			}
			if (isset ( $data ['rule'] ['actions'] )) {
				$data ['actions'] = $data ['rule'] ['actions'];
			}
			unset ( $data ['rule'] );
			
			try {
				$model->loadPost ( $data );
				
				Mage::getSingleton ( 'adminhtml/session' )->setPageData ( $model->getData () );
				$model->save ();
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'Rule was successfully saved' ) );
				
				if ($back = $this->getRequest ()->getParam ( 'back' )) {
					$this->_forward ( 'edit', 'manage_special', 'rewardsadmin', array ('id' => $model->getId () ) );
				} else {
					Mage::getSingleton ( 'adminhtml/session' )->setPageData ( false );
					$this->_redirect ( '*/*/', array ('id' => $model->getId () ) );
				}
				return;
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				Mage::getSingleton ( 'adminhtml/session' )->setPageData ( $data );
				$this->_redirect ( '*/*/edit', array ('id' => $data ['rewards_special_id'] ) );
				return;
			}
		}
		$this->_redirect ( '*/*/' );
	}
	
    public function fixBirthdaysAction()
    {
        // get all the active birthday rules
        $rules = Mage::getModel('rewards/special')->getCollection()
            ->addFieldToFilter('is_active', '1');
        $birthdayRules = array();
        $actionType = TBT_Rewards_Model_Birthday_Action::ACTION_CODE;
        foreach ($rules as $rule) {
            //Unserializes the conditions and checks if it is a birthday rule
            $ruleConditions = Mage::helper('rewards')->unhashIt($rule->getConditionsSerialized());
            if (is_array($ruleConditions)) {
                if (in_array($actionType, $ruleConditions)) {
                    $birthdayRules[] = $rule;
                }
            } else if ($rule_conditions == $actionType) {
                $birthdayRules[] = $rule;
            }
        }
        
        // get all the customers with birthdays
        $customers = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToSelect('dob', 'inner');
        $customersMissed = array();
        
        $now = date("Y-m-d", strtotime(Mage::getModel('core/date')->gmtDate()));
        foreach ($customers as $customer) {
            // this is their year-agnostic birthday
            $birthday = date("m-d", strtotime($customer->getDob()));
            foreach ($birthdayRules as $rule) {
                $customerGroupIds = explode(",", $rule->getCustomerGroupIds());
                if (array_search($customer->getGroupId(), $customerGroupIds) === false) {
                    continue;
                }
                
                // the effective end is either end-date of the rule, or NOW if the rule ends in the future
                $effectiveEnd = $rule->getToDate() ? min($rule->getToDate(), $now) : $now;
                $yearStart = date("Y", strtotime($rule->getFromDate()));
                $yearEnd = date("Y", strtotime($effectiveEnd));
                $yearDiff = $yearEnd - $yearStart; // roughly how many years the rule has lasted
                
                $birthdays = 0;
                // loop through each year the rule was supposedly active
                for ($i = 0; $i <= $yearDiff; $i++) {
                    // make all dates (rule start, rule end, birthday) relative to the "current" year
                    $year = $yearStart + $i;
                    $start = max("{$year}-01-01", $rule->getFromDate());
                    $end = min("{$year}-12-31", $effectiveEnd);
                    $tempBd = "{$year}-{$birthday}";
                    
                    // check if the customer's birthday fell within the span (could be bounded by rule start or end)
                    if ($tempBd >= $start && $tempBd <= $end) {
                        $birthdays++; // increment the number of birthdays the customer was SUPPOSED to have
                    }
                }
                
                // fetch all the birthday transfers the customer HAS received (hopefully for this rule).
                // it's not a sure thing, matching transfer with rule based on quantity and currency,
                // but we don't have much of a better method
                $bdTransfers = Mage::getResourceModel('rewards/transfer_collection')
                    ->addFieldToFilter('customer_id', $customer->getId())
                    ->addFieldToFilter('reason_id', TBT_Rewards_Model_Birthday_Reason::REASON_TYPE_ID)
                    ->addFieldToFilter('currency_id', $rule->getPointsCurrencyId())
                    ->addFieldToFilter('quantity', $rule->getPointsAmount());
                $missedBirthdays = $birthdays - count($bdTransfers); // how many birthday rewards have we missed?
                for ($i = 0; $i < $missedBirthdays; $i++) {
                    // send the customer a new birthday transfer for each birthday we missed
                    $success = Mage::getSingleton('rewards/birthday_transfer')->transferBirthdayPoints($customer, $rule);
                    if ($success) {
                        $pointsString = (string) Mage::getModel('rewards/points')->add($rule);
                        Mage::getSingleton('rewards/birthday_notify')->systemLog($customer, $pointsString, $birthday);
                    }
                }
            }
        }
        
        Mage::getSingleton('core/session')->addSuccess("All customers with outstanding birthdays have been rewarded.");
        $this->_redirect('rewardsadmin/manage_transfer');
        
        return $this;
    }
	
	public function preDispatch() {
		if (! Mage::helper ( 'rewards/loyalty_checker' )->isValid ()) {
			Mage::throwException ( "Please check your Sweet Tooth registration code your Magento configuration settings, or contact WDCA through contact@wdca.ca for a description of this problem." );
		}
		parent::preDispatch ();
	}

}