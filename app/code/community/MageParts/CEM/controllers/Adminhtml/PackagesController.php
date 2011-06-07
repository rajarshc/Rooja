<?php
/**
 * MageParts
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
 * @category   MageParts
 * @package    MageParts_Adminhtml
 * @copyright  Copyright (c) 2009 MageParts (http://www.mageparts.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MageParts_CEM_Adminhtml_PackagesController extends Mage_Adminhtml_Controller_Action
{
	
	/**
	 * Init action
	 * 
	 * Creates breadcrumbs etc.
	 */
	public function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('system')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('System'), Mage::helper('adminhtml')->__('System'))
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Magento Connect'), Mage::helper('adminhtml')->__('Magento Connect'))
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Commercial Extension Manager'), Mage::helper('adminhtml')->__('Commercial Extension Manager'));
			
		return $this;
	}
	
	
	/**
     * Index action
     */
    public function indexAction()
    {
        $this->_initAction()
			->_addContent($this->getLayout()->createBlock('cem/adminhtml_packages'))
			->renderLayout();
    }
	
    
	/**
	 * Redirects a user to the editing area
	 */
	public function newAction()
	{
		$this->_forward('edit');
	}
	
	
	/**
	 * Redirects a user to the editing area
	 */
	public function editAction() 
	{
		$id = $this->getRequest()->getParam('package_id');
		$model = Mage::getModel('cem/packages');

		// If an ID was givven, load the object
		if($id) {
			$model->load($id);
            if (!$model->getPackageId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cem')->__('This package no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
		}
		
		// Register the current model object
		Mage::register('cem_package', $model);
		
		// Edit block   
       $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('cem')->__('Update Extension') : Mage::helper('cem')->__('Install New Extension'), $id ? Mage::helper('cem')->__('Update Extension') : Mage::helper('cem')->__('Install New Etension'))
            ->_addContent($this->getLayout()->createBlock('cem/adminhtml_packages_edit')->setData('action', $this->getUrl('*/*/install')))
            ->renderLayout();
	}
	
	
	/**
	 * Install action
	 */
	public function installAction()
	{
		// License key
		$licenseKey = $this->getRequest()->getPost('license_key');
		
		// Auth service
		$service = $this->getRequest()->getPost('service');

		// Package Identifier
		$packageIdentifier = $this->getRequest()->getPost('package_identifier');
		
		// Update package automatically
		$autoUpdate = $this->getRequest()->getPost('auto_update');
		
		try {
			// Get package model
			$packageModel = Mage::getModel('cem/packages');
			
			// Install extension
			if(!$packageModel->install($packageIdentifier,$licenseKey,$service,$autoUpdate)) {
				$error = Mage::registry('cem_error');
				
				if(!empty($error)) {
					throw new Exception($error);
				}
				else {
					throw new Exception(Mage::helper('cem')->__("The installation couldn't be completed due to an unknown error. Please contact your software retailer for assistance."));
				}
			}
			
			// Success
			$this->getResponse()->setBody("SUCCESS");
			return;
		}
		catch (Exception $e) {
           $this->getResponse()->setBody(Mage::helper('cem')->__($e->getMessage()));
           return;
        }
	}
	
	
	/**
	 * Update installed extension(s)
	 */
	public function updateAction()
	{
		// Package id
		$packageId = $this->getRequest()->getPost('id');
		
		// Ajax call
		$ajax = $this->getRequest()->getPost('ajax');

		// Get package module
		$packageModel = Mage::getModel('cem/packages');
			
		try {
			// Get package collection
			$packages = $packageModel->getCollection()
				->joinTables()
				->setIdFilter($packageId);

			// Check so that there were at least one valid package
			if($packages->getSize()<1) {
				throw new Exception("Unable to find the extension requested for update.");
			}
			
			// Message	
			$msg = 'No extensions were updated';
			
			// Update success
			$update = 0;
			
			// Update packages
			if($update = $packageModel->update($packages,true)) {
				$msg = 'Extensions was successfully updated';
			}

			if($ajax) {
				if($packages->getSize()===1 && $update===0) {
					$this->getResponse()->setBody(Mage::helper('cms')->__("The extension is already up-to-date"));
				}
				else {
					$this->getResponse()->setBody("SUCCESS");
				}
			}
			else {
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__($msg));
				$this->_redirect('*/*/');
			}
			return;
		}
		catch (Exception $e) {
			// display error message
			if($ajax) {
				$this->getResponse()->setBody(Mage::helper('cem')->__($e->getMessage()));
			}
			else {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/');
			}
			return;
        }
	}
	
	
	/**
	 * Set the CEM email
	 */
	public function setCemEmailAction()
	{
		$email = $this->getRequest()->getParam('email');
		
		if(!empty($email)) {
			try {
				if(!Mage::getModel('cem/licenses')->validateEmail($email)) {
					throw new Exception(Mage::helper('cem')->__("The email address you provided is invalid, please make sure that it's correct and try again."));
				}
				
				$configEmail = Mage::getStoreConfig('system/cem/email');
				
				if(empty($configEmail)) {
					Mage::getConfig()->saveConfig('system/cem/email', $email, 'default', 0);
					
					// Clean up the cache
					if(!Mage::app()->cleanCache()) {
						throw new Exception(Mage::helper('cem')->__("Unable to refresh cache, please refresh the cache manually."));
					}
				}
				
				// Success
				$this->getResponse()->setBody("SUCCESS");
				return;
			}
			catch (Exception $e) {
	           $this->getResponse()->setBody(Mage::helper('cem')->__($e->getMessage()));
	           return;
	        }
		}
		else {
			$this->getResponse()->setBody(Mage::helper('cem')->__("You need to provide a valid email address"));
			return;
		}
	}
	
	
	/**
	 * Set the CEM email
	 */
	public function licenseManagementAction()
	{
       $this->_initAction()
            ->_addBreadcrumb(Mage::helper('cem')->__('License Management'), Mage::helper('cem')->__('License Management'))
            ->_addContent($this->getLayout()->createBlock('cem/adminhtml_licenses_edit'))
            ->_addLeft($this->getLayout()->createBlock('cem/adminhtml_licenses_edit_tabs'))
            ->renderLayout();
	}
	
	
	/**
	 *  Make sure the user can access this controller
	 */
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('system/extensions/cem');
	}
	
}