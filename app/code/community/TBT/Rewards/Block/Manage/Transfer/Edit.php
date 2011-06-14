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
 * Manage Transfer Edit
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Transfer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	
	protected $_backController;
	protected $_backModule;
	protected $_backAction;
	
	public function __construct() {
		$params = $this->getRequest ()->getParams ();
		
		unset ( $params ['key'] );
		unset ( $params ['id'] );
		
		if ($this->getRequest ()->getParam ( 'controller' )) {
			$this->_backController = $this->getRequest ()->getParam ( 'controller' );
			unset ( $params ['controller'] );
		} else {
			$this->_backController = '*';
		}
		
		if ($this->getRequest ()->getParam ( 'module' )) {
			$this->_backModule = $this->getRequest ()->getParam ( 'module' );
			unset ( $params ['module'] );
		} else {
			$this->_backModule = '*';
		}
		
		if ($this->getRequest ()->getParam ( 'action' )) {
			$this->_backAction = $this->getRequest ()->getParam ( 'action' );
			$this->_backAction .= '/';
			unset ( $params ['action'] );
		} else {
			$this->_backAction = '';
		}
		
		parent::__construct ();
		
		$this->_objectId = 'id';
		$this->_blockGroup = 'rewards';
		$this->_controller = 'manage_transfer';
		
		$this->_updateButton ( 'save', 'label', Mage::helper ( 'rewards' )->__ ( 'Save Transfer' ) );
		
		$this->_updateButton ( 'back', 'onclick', 'setLocation(\'' . $this->getBackUrl ( $params ) . '\')' );
		
		$this->_addButton ( 'saveandcontinue', array ('label' => Mage::helper ( 'adminhtml' )->__ ( 'Save And Continue Edit' ), 'onclick' => 'saveAndContinueEdit()', 'class' => 'save' ), - 100 );
		
		$this->_removeButton ( 'delete' );
		
		if ($this->_getTransfer () && $this->_getTransfer ()->getId ()) {
			$this->_addButton ( 'create_new', array ('label' => Mage::helper ( 'adminhtml' )->__ ( 'Create A New Transfer' ), 'onclick' => 'window.location=\'' . $this->getUrl ( '*/*/new', array ('back' => $this->_backController ) ) . '\'' ), - 100 );
		} else {
			$this->_updateButton ( 'save', 'label', Mage::helper ( 'rewards' )->__ ( 'Create New Transfer' ) );
			$this->_updateButton ( 'saveandcontinue', 'label', Mage::helper ( 'rewards' )->__ ( 'Create And Continue Edit' ) );
		}
		
		if ($this->_getTransfer () && $this->_getTransfer ()->getId () && ($this->_getTransfer ()->getReasonId () != TBT_Rewards_Model_Transfer_Reason::REASON_SYSTEM_REVOKED)) {
			
			$this->_addButton ( 'revoke', array ('label' => Mage::helper ( 'rewards' )->__ ( 'Revoke Transfer' ), 'onclick' => 'revokeTransfer()' ), - 100 );
		}
		
		$this->_formScripts [] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('transfer_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function clearGridSelections(id) {
                var nodes=document.getElementById('edit_form')[id];
                if(nodes instanceof NodeList) {
                  for(var i=0;i<nodes.length;i++) { nodes[i].checked=\"\"; }
                } else {
                  nodes.checked = \"\";
                }
            }
            
            function revokeTransfer() {
                // redirect to RevokeController
                window.location='" . $this->getUrl ( '*/*/revoke', array ('id' => $this->_getTransfer ()->getId () ) ) . "';
            }
        ";
	}
	
	public function getHeaderText() {
		if ($this->_getTransfer () && $this->_getTransfer ()->getId ()) {
			return Mage::helper ( 'rewards' )->__ ( "Edit Transfer #%s", $this->htmlEscape ( $this->_getTransfer ()->getId () ) );
		} else {
			return Mage::helper ( 'rewards' )->__ ( 'Create New Transfer' );
		}
	}
	
	/**
	 * Fetches the transfer we want to edit.
	 *
	 * @return TBT_Rewards_Model_Transfer
	 */
	protected function _getTransfer() {
		return Mage::registry ( 'transfer_data' );
	}
	
	/**
	 * Get URL for back (reset) button
	 *
	 * @param params - a list of values to be passed with the URL
	 * @return string
	 */
	public function getBackUrl($params = null) {
		if ($params === null) {
			$url = $this->getUrl ( $this->_backModule . '/' . $this->_backController . '/' . $this->_backAction );
		} else if (isset ( $params ['customer_id'] )) {
			if ($this->_backController != 'manage_customer_points') {
				$url = $this->getUrl ( $this->_backModule . '/' . $this->_backController . '/' . $this->_backAction, Array ('id' => $params ['customer_id'] ) );
			} else {
				$url = $this->getUrl ( $this->_backModule . '/' . $this->_backController . '/' . $this->_backAction );
			}
		} else {
			$url = $this->getUrl ( $this->_backModule . '/' . $this->_backController . '/' . $this->_backAction, $params );
		}
		return $url;
	}

}