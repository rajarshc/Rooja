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
 * Special Action
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Special_Action extends Varien_Object {
	// status values less than 1 means that transfer is ignored in
	// customer point calculations.
	const ACTION_RATING = 'customer_rating';
	const ACTION_POLL = 'customer_poll';
	const ACTION_SENDFRIEND = 'customer_send_friend';
	
	//@deprecated Referencing these codes from this class is deprecated. Use the individual module classes instead.	
	const ACTION_SIGN_UP = 'customer_sign_up';
	const ACTION_WRITE_REVIEW = 'customer_writes_review';
	const ACTION_NEWSLETTER = 'customer_newsletter';
	const ACTION_TAG = 'customer_tag';
	
	public function _construct() {
		parent::_construct ();
	}
	
	public function getActionArray() {
		$options = $this->getOptionsArray ();
		unset ( $options [''] );
		return $options;
	}
	
	public function getOptionsArray() {
		$this->loadSpecialModels ();
		$base_actions = array ('' => '', //include the null option so the user can pick nothing
self::ACTION_RATING => Mage::helper ( 'rewards' )->__ ( 'Rates a product' ), //Rating a product happens at the same time as making a review
self::ACTION_SIGN_UP => Mage::helper ( 'rewards' )->__ ( 'Signs up' ), self::ACTION_POLL => Mage::helper ( 'rewards' )->__ ( 'Votes in poll' ), self::ACTION_SENDFRIEND => Mage::helper ( 'rewards' )->__ ( 'Sends product to friend' ), self::ACTION_NEWSLETTER => Mage::helper ( 'rewards' )->__ ( 'Signs up for a newsletter' ), self::ACTION_TAG => Mage::helper ( 'rewards' )->__ ( 'Tags a product' ) );
		foreach ( $this->getSpecialConfigModels () as $code => $scm ) {
			$base_actions += $scm->getNewCustomerConditions ();
		}
		return $base_actions;
	}
	
	public function getActionOptionsArray() {
		$this->loadSpecialModels ();
		$base_actions = array ('grant_points' => Mage::helper ( 'salesrule' )->__ ( 'Give points to the customer' ) );
		foreach ( $this->getSpecialConfigModels () as $code => $scm ) {
			$base_actions += $scm->getNewActions ();
		}
		return $base_actions;
	}
	
	public function getAdminFormScripts() {
		$this->loadSpecialModels ();
		$base_scripts = array ();
		foreach ( $this->getSpecialConfigModels () as $code => $scm ) {
			$base_scripts = array_merge ( $base_scripts, $scm->getAdminFormScripts () );
		}
		return $base_scripts;
	}
	
	public function getAdminFormInitScripts() {
		$this->loadSpecialModels ();
		$base_scripts = array ();
		foreach ( $this->getSpecialConfigModels () as $code => $scm ) {
			$base_scripts = array_merge ( $base_scripts, $scm->getAdminFormInitScripts () );
		}
		return $base_scripts;
	}
	
	public function getSpecialActionCodes() {
		$code_nodes = Mage::getConfig ()->getNode ( 'rewards/specialrule/action' )->children ();
	}
	
	public function loadSpecialModels() {
		if ($this->getHasLoadedSpecialConfigModels ())
			return $this; //don't load more than once...
		$special_config = Mage::getConfig ()->getNode ( 'rewards/special' ); //@nelkaake 04/03/2010 4:25:50 PM : changed format of this so it doesn't give a ->children() error when no children exist
		$sms = array ();
		if ($special_config) {
			$code_nodes = $special_config->children ();
			foreach ( $code_nodes as $code => $special ) {
				$special = ( array ) $special;
				if (isset ( $special ['config'] )) {
					$model_code = $special ['config'];
				} else {
					throw new Exception ( "Action model for special rule code '$code' is not specified." );
				}
				$config_model = Mage::getModel ( $model_code );
				if (! ($config_model instanceof TBT_Rewards_Model_Special_Configabstract)) {
					throw new Exception ( "Config model for special rule code '$code' should extend TBT_Rewards_Model_Special_Configabstract but it doesn't." );
				}
				$sms [$code] = $config_model;
			}
		}
		
		$this->setSpecialConfigModels ( $sms );
		$this->setHasLoadedSpecialConfigModels ( true );
		return $this;
	}
	
	public function visitAdminActions(&$fieldset) {
		$this->loadSpecialModels ();
		foreach ( $this->getSpecialConfigModels () as $code => $scm ) {
			$scm->visitAdminActions ( $fieldset );
		}
		return $this;
	}
	
	public function visitAdminConditions(&$fieldset) {
		$this->loadSpecialModels ();
		foreach ( $this->getSpecialConfigModels () as $code => $scm ) {
			$scm->visitAdminConditions ( $fieldset );
		}
		return $this;
	}

}

?>