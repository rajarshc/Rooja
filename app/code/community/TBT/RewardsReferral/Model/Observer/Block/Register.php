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
 * Observer sales Order Invoice Pay
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_Model_Observer_Block_Register extends Varien_Object {

	/**
	 * Executed from the core_block_abstract_to_html_after event
	 * @param Varien_Event $obj
	 */
	public function afterOutput($obj) {
		$block = $obj->getEvent ()->getBlock ();
		$transport = $obj->getEvent ()->getTransport ();
		
		// Magento 1.3 and lower dont have this transport, so we can't do autointegration : (
		if(empty($transport)) {
			return $this;
		}
		
		$this->appendToSignupForm ( $block, $transport );
		$this->appendToOnepageCheckoutSignup ( $block, $transport );
		
		return $this;
	}
	
	/**
	 * Appends the points balance in the header somewhere
	 * @param unknown_type $block
	 * @param unknown_type $transport
	 */
	public function appendToSignupForm($block, $transport) {
	
        if(!( $block instanceof Mage_Customer_Block_Form_Register )) {
            return $this;
        }
        
        if(!Mage::getStoreConfigFlag('rewards/autointegration/customer_register_referral_field')) {
            return $this;
        }
		
		$html = $transport->getHtml ();
		$st_html = $block->getChildHtml ( 'rewards_referral' );
	    
		// Check that content is not already integrated.
		if(!empty($st_html) && strpos($html, $st_html) === false) {
    		// Find the correct HTML to integrate next to, otherwise the client should do a manual integration
    		if(strpos($html, '<div class="buttons-set') !== false) {
    		    $button_set_begin_html = '<div class="buttons-set';
    		    $html = str_replace($button_set_begin_html, $st_html . $button_set_begin_html, $html);
    		}
		}
		
		$transport->setHtml ( $html );
		return $this;
	}
	
	/**
	 * Appends the points balance in the header somewhere
	 * @param unknown_type $block
	 * @param unknown_type $transport
	 */
	public function appendToOnepageCheckoutSignup($block, $transport) {
	
		if(!( $block instanceof Mage_Checkout_Block_Onepage_Billing )) {
            return $this;
        }
        
        if(!Mage::getStoreConfigFlag('rewards/autointegration/onepage_billing_register_referral_field')) {
            return $this;
        }
        
		$html = $transport->getHtml ();
		$st_html = $block->getChildHtml ( 'rewards_referral_field' );
	
		// Check that content is not already integrated.
		if(!empty($st_html) && strpos($html, $st_html) === false) {
    		if(Mage::helper('rewards/version')->isMageEnterprise()
    		        && strpos($html, '<li class="fields" id="register-customer-password') !== false) {
    		    $html = $this->_appendToEnterpriseBillingAddressForm($html, $st_html);
    		} else {
    		    $html = $this->_appendToBillingAddressForm($html, $st_html);
    		}
	    }
	    
	    $transport->setHtml ( $html );
		return $this;
	}

	/**
	 * 
	 * @param unknown_type $orignal_html
	 * @param unknown_type $st_html
	 * @param unknown_type $transport
	 */
	protected function _appendToEnterpriseBillingAddressForm($original_html, $st_html) {
	    
	    $pass_field_begin_html = '<li class="fields" id="register-customer-password';
		$pass_field_pos = strpos($original_html, $pass_field_begin_html);
		if($pass_field_pos === false) {
		    // Could not find the correct HTML to integrate next to, so the client should do a manual integration
		    return $original_html;
		}
	
		
		$fieldset_end_pos = strpos($original_html, '</li>', $pass_field_pos);
		if($fieldset_end_pos === false) {
		    // Could not find the correct HTML to integrate next to, so the client should do a manual integration
		    return $original_html;
		}
		$fieldset_end_pos_end = $fieldset_end_pos + strlen('</li>') + 1;
		
		$replace_html = substr($original_html, $fieldset_end_pos_end);
		
		return str_replace($replace_html, $st_html . $replace_html, $original_html);
	}
	
	/**
	 * 
	 * @param unknown_type $orignal_html
	 * @param unknown_type $st_html
	 * @param unknown_type $transport
	 */
	protected function _appendToBillingAddressForm($original_html, $st_html) {
	    
		$billaddress_form_pos = strpos($original_html, '<li id="billing-new-address-form');
		if($billaddress_form_pos === false) {
		    // Could not find the correct HTML to integrate next to, so the client should do a manual integration
		    return $original_html;
		}
	
		
		$fieldset_end_pos = strpos($original_html, '</fieldset>', $billaddress_form_pos);
		if($fieldset_end_pos === false) {
		    // Could not find the correct HTML to integrate next to, so the client should do a manual integration
		    return $original_html;
		}
		$fieldset_end_pos_end = $fieldset_end_pos + strlen('</fieldset>') + 1;
		
		$replace_html = substr($original_html, $fieldset_end_pos_end);
		
		return str_replace($replace_html, $st_html . $replace_html, $original_html);
	}
}