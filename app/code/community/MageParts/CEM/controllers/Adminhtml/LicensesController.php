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

/**
 * Error reporting, don't logg any strict errors here it gives us problems with the NuSOAP class
 */
error_reporting(E_ALL);

class MageParts_CEM_Adminhtml_LicensesController extends Mage_Adminhtml_Controller_Action
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
        $this->_forward('edit');
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
		// Edit block
       $this->_initAction()
            ->_addBreadcrumb(Mage::helper('cem')->__('License Management'), Mage::helper('cem')->__('License Management'))
            ->_addContent($this->getLayout()->createBlock('cem/adminhtml_licenses_edit'))
            ->_addLeft($this->getLayout()->createBlock('cem/adminhtml_licenses_edit_tabs'))
            ->renderLayout();
	}
	
	
	/**
	 * Request lost CEM key
	 */
	public function requestLostCemKeyAction()
	{
		$serviceUrl = $this->getRequest()->getParam('service_url');
		
		try {
			if(empty($serviceUrl)) {
				throw new Exception("Please provide a valid service url");
			}
			
			// Check if url exists
			//@nelkaake Added on Wednesday July 14, 2010: 
			if(!Mage::helper('cem/dl')->fopen($serviceUrl,'r')) {
				throw new Exception(Mage::helper('cem')->__("The provided service url is either invalid or unreachable. Make sure that the service url you provided is correct and try again in a few minutes. If the problem consists we advise you to contact your software retailer for assistance."));
			}
			
			// Make SOAP call to recover lost CEM key
			$response = Mage::getModel('cem/soap')->call(
				'lostCemKey',
				array(
					'email' => Mage::getStoreConfig('system/cem/email'),
					'clientHostname' => Mage::getStoreConfig('web/unsecure/base_url')
				),
				$serviceUrl
			);

			// Check for soap errors
			if($response->getResults()!==true) {
				if(!$response->getErrorMessage()) {
					throw new Exception("An unknown SOAP error occurred while requesting the CEM key. Please contact your retailer for assistance.");
				}
				throw new Exception($response->getErrorMessage());
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
	 * Request lost license key
	 */
	public function requestLostLicenseKeyAction()
	{
		$serviceUrl = $this->getRequest()->getParam('service_url');
		$moduleIdentifier = $this->getRequest()->getParam('module_identifier');
		
		try {
			// We need these values
			if(empty($serviceUrl) || empty($moduleIdentifier)) {
				throw new Exception("Please provide a valid service url and module identifier");
			}

			// Make sure the service url exists in the database
			if(!$serviceId = Mage::getResourceModel('cem/services')->urlExists($serviceUrl)) {
				throw new Exception("The provided service url doesn't exists in your database. No matching CEM key was found. You'll have to retrieve your CEM key from the provided service using the 'Lost CEM Key' section before you can retrieve any lost license keys.");
			}
			
			// Get CEM key
			if(!$cemKey = Mage::getResourceModel('cem/services')->getCemKey($serviceId)) {
				throw new Exception("Unable to retrieve any CEM key. Please contact your software retailer for assistance.");
			}
			
			// Make SOAP call to recover lost license key
			$response = Mage::getModel('cem/soap')->call(
				'lostLicenseKey',
				array(
					'cemKey' => $cemKey,
					'clientHostname' => Mage::getStoreConfig('web/unsecure/base_url'),
					'moduleIdentifier' => $moduleIdentifier
				),
				$serviceUrl
			);

			// Check for soap errors
			if($response->getResults()!==true) {
				if(!$response->getErrorMessage()) {
					throw new Exception("An unknown SOAP error occurred while requesting the CEM key. Please contact your retailer for assistance.");
				}
				throw new Exception($response->getErrorMessage());
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
	 * Request lost CEM key
	 */
	public function installCemKeyAction()
	{
		// Gather parameters
		$serviceUrl = $this->getRequest()->getParam('service_url');
		$cemKey = $this->getRequest()->getParam('cem_key');
		
		try {
			// We need these values
			if(empty($serviceUrl) || empty($cemKey)) {
				throw new Exception("Please provide a valid service url and CEM key");
			}
			
			// Get service model / resource model
			$serviceModel = Mage::getModel('cem/services');
			$serviceResourceModel = Mage::getResourceModel('cem/services');
			
			// Retrieve existing or create new service id
			$serviceId = 0;
			
			if(!$serviceResourceModel->urlExists($serviceUrl)) {
				if(!$serviceModel->setUrl($serviceUrl)->save()) {
					throw new Exception(Mage::helper('cem')->__("Unable to save service information in local database. Please contact your retailer for assistance."));
				}
				$serviceId = $serviceModel->getServiceId();
			}
			else {
				$serviceCollection = $serviceModel->getCollection()
					->setUrlFilter($serviceUrl)
					->getFirstItem();
					
				if(!$serviceId = $serviceCollection->getServiceId()) {
					throw new Exception(Mage::helper('cem')->__("Unable data recieved from service database. Please contact your software retailer for assistance."));
				}
			}
			
			// Check if the CEM key already exists in the database
			$checkCemKeyExists = $serviceResourceModel->getCemKey($serviceId);
			
			if(empty($checkCemKeyExists) || !$checkCemKeyExists || $checkCemKeyExists!==$cemKey) {
				// Save CEM key in local database
				if(!$serviceResourceModel->addCemKey(array('service_id' => $serviceId, 'key' => $cemKey))) {
					throw new Exception(Mage::helper('cem')->__("Unable to save CEM key information in local database. Please contact your retailer for assistance."));
				}
			}

			// Make SOAP call to install CEM key
			$call = Mage::getModel('cem/soap')->call(
				'gePackagesAndLicensesByCemKey',
				array(
					'cemKey' => $cemKey,
					'clientHostname' => Mage::getStoreConfig('web/unsecure/base_url'),
				),
				$serviceUrl
			);

			$response = $call->getResults();
			
			// Check for soap errors
			if(is_null($response)) {
				if(trim($call->getErrorMessage())=='') {
					throw new Exception("An unknown SOAP error occurred while install the CEM key. Please contact your retailer for assistance.");
				}
				throw new Exception($call->getErrorMessage());
			}

			// Install packages
			$failedInstallations = '';
			
			if(!is_array($response)) {
				throw new Exception("No packages could be found that were associated with the provided CEM key. Make sure that the CEM key is valid and try again.");
			}
			
			foreach ($response as $package) {
				if(!@Mage::getModel('cem/packages')->install($package['package_identifier'],$package['license_key'],$serviceUrl,1)) {
					$error = Mage::registry('cem_error');
					$error = empty($error) ? 'Unknown error' : $error;
					
					$failedInstallations.=  "\n\n[ {$package['package_identifier']} ]\n - {$error}";
				}
			}
			
			// Success
			$this->getResponse()->setBody("SUCCESS|{$failedInstallations}");
			return;
		}
		catch (Exception $e) {
           $this->getResponse()->setBody(Mage::helper('cem')->__($e->getMessage()));
           return;
        }
	}
	
	
	/**
	 *  Make sure the user can access this controller
	 */
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('system/extensions/cem');
	}
	
}