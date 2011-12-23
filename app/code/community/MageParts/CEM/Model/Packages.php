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
 * @package    MageParts_CEM
 * @copyright  Copyright (c) 2009 MageParts (http://www.mageparts.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Error reporting, don't logg any strict errors here it gives us problems with the PEAR & NuSOAP classes
 */
error_reporting(E_ALL);

if(!@class_exists("Archive_Tar")) {
	// We need to add the include path to make sure that the file will be loaded properly
	set_include_path((Mage::getBaseDir('base') . DS . 'downloader' . DS . 'pearlib' . DS . 'php') . PS . get_include_path());
	$archiveFile =  Mage::getBaseDir('base') . DS . 'downloader' . DS . 'pearlib' . DS . 'php' . DS . 'Archive' . DS . 'Tar.php';
	
	if(file_exists($archiveFile)) { 
		require_once $archiveFile; 
	}
}

class MageParts_CEM_Model_Packages extends Mage_Core_Model_Abstract
{

	protected $_downloadDir = null;
	
	
	/**
	 * Constructor
	 */
	protected function _construct()
    {
        $this->_init('cem/packages');
    }
    
	
    /**
     * Install a new extension
     *
     * @param string $packageIdentifier
     * @param string $licenseKey
     * @param string $serviceUrl
     * @param boolean $autoUpdate
     * @param int $updatePackageId [optional]
     * @return unknown
     */
	public function install( $packageIdentifier, $licenseKey, $serviceUrl, $autoUpdate, $updatePackageId=0 )
	{	
		$tmpFile = '';
		
		try  {
			// 1. We need these values
			if(!empty($serviceUrl) && !empty($packageIdentifier) && !empty($licenseKey)) {
				// 2. Save new license key or load existsing one
				$licenseModel 	= Mage::getModel('cem/licenses');
				$newLicenseKey 	= false;
				
				if(!$licenseId = Mage::getResourceModel('cem/licenses')->licenseKeyExists($licenseKey)) {
					// Save new
					if(!$licenseModel->setLicenseKey($licenseKey)->save()) {
						throw new Exception(Mage::helper('cem')->__("Unable to save package information, please try again"));
					}
					$licenseId = $licenseModel->getLicenseId();
					$newLicenseKey = true;
				}
				else {
					// Load existing
					$licenseModel->load($licenseId);
				}

				// 3. Save new service url or load existing one 
				$serviceModel 	= Mage::getModel('cem/services');
				$newService		= false;
				
				if(!$serviceId = Mage::getResourceModel('cem/services')->urlExists($serviceUrl)) {
					// Save new
					if(!$serviceModel->setUrl($serviceUrl)->save()) {
						throw new Exception(Mage::helper('cem')->__("Unable to save package information, please try again"));
					}
					$serviceId = $serviceModel->getServiceId();
					$newService = true;
				}
				else {
					// Load existing
					$serviceModel->load($serviceId);
				}
				// 4. Chck if url exists
				//@nelkaake Added on Wednesday July 14, 2010: 
				if(!Mage::helper('cem/dl')->fopen($serviceUrl,'r')) {
					throw new Exception(Mage::helper('cem')->__("The provided service url is either invalid or unreachable. Make sure that the service url you provided is correct and try again in a few minutes. If the problem consists we advise you to contact your software retailer for assistance."));
				}
				
				// 6. Retrieve or create a new CEM key from the repository
				$cem_key = '';
				
				if(!$cem_key = Mage::getResourceModel('cem/services')->getCemKey($serviceId)) {
					// Soap call: create new CEM key
					$cemKeyCall = Mage::getModel('cem/soap')->call(
						'createCemKey',
						array(
							'email' => Mage::getStoreConfig('system/cem/email'),
							'clientHostname' => Mage::getStoreConfig('web/unsecure/base_url')
						),
						$serviceUrl
					);
					
					$cem_key = $cemKeyCall->getResults();
					
					// Check for soap errors
					if(is_null($cem_key)) {
						if(trim($cemKeyCall->getErrorMessage())=='') {
							throw new Exception("An unknown SOAP error occurred while the new CEM key was created. Please contact your retailer for assistance.");
						}
						throw new Exception(Mage::helper('cem')->__($cemKeyCall->getErrorMessage()));
					}
					
					// Save new CEM key
					if(!empty($cem_key)) {
						if(!Mage::getResourceModel('cem/services')->addCemKey(array('service_id' => $serviceId, 'key' => $cem_key))) {
							throw new Exception(Mage::helper('cem')->__("Unable to save CEM key, please contact your software retailer"));
						}
					}
					else {
						throw new Exception(Mage::helper('cem')->__("CEM key is empty, please contact your retailer"));
					}
				}
				
				// 7. Retrieve package information from service repository
				$call = Mage::getModel('cem/soap')->call(
					'install',
					array(
						'packageIdentifier' => $packageIdentifier,
						'licenseKey'		=> $licenseKey,
						'cemKey'			=> $cem_key,
						'clientHostname'	=> Mage::getStoreConfig('web/unsecure/base_url')
					),
					$serviceUrl
				);

				$response = $call->getResults();

				if(is_null($response)) {
					if(trim($call->getErrorMessage())=='') {
						throw new Exception("An unknown SOAP error occurred while installing the extension. Please contact your retailer for assistance.");
					}
					throw new Exception(Mage::helper('cem')->__($call->getErrorMessage()));
				}
				
				// 8. Minimum core version checkup
				if(!empty($response['min_core_version']) && (floatval(Mage::getVersion()) < floatval($response['min_core_version']))) {
					$minVersion = number_format($response['min_core_version'],2);
					$maxVersion = number_format($response['max_core_version'],2);
					
					if(!empty($response['max_core_version']) && $maxVersion > 0.0) {
						throw new Exception(Mage::helper('cem')->__("The version of your Magento installation is too old to support the requested version of this extension. The minimum required version of Magento is %s, the maximum allowed is %s. The currently installed version of Magento is %s. Please upgrade your Magento installation to meet these requirements and try again. If you require any further assistance we advise you to contact your software retailer.",$minVersion,$maxVersion,Mage::getVersion()));
					}
					
					throw new Exception(Mage::helper('cem')->__("The version of your Magento installation is too old to support the requested version of this extension. The minimum required version of Magento is %s. The currently installed version of Magento is %s. Please upgrade your Magento installation to meet these requirements and try again. If you any require further assistance we advise you to contact your software retailer.",$minVersion,Mage::getVersion()));
				}
				
				
				// 9. Maximum core version checkup
				if(!empty($response['max_core_version']) && (floatval(Mage::getVersion()) > floatval($response['max_core_version']))) {
					$maxVersion = number_format($response['max_core_version'],2);
					$minVersion = number_format($response['min_core_version'],2);
					
					if(!empty($response['min_core_version']) && $minVersion > 0.0) {
						throw new Exception(Mage::helper('cem')->__("The version of your Magento installation is too new to support the requested version of this extension. You can not use this extension version togheter with a Magento installation with a version above %s or below %s. The currently installed version of Magento is %s. Please downgrade your Magento installation to meet these requirements and try again. If you any require further assistance we advise you to contact your software retailer.",$maxVersion,$minVersion,Mage::getVersion()));
					}
					
					throw new Exception(Mage::helper('cem')->__("The version of your Magento installation is too new to support the requested version of this extension. You can not use this extension version togheter with a Magento installation with a version higher then %s. The currently installed version of Magento is %s. Please downgrade your Magento installation to meet these requirements and try again. If you any require further assistance we advise you to contact your software retailer.",$maxVersion,Mage::getVersion()));
				}

				// 10. Check writable paths
				$unwritablePaths = array();
				
				if(!empty($response['writable_paths'])) {
					foreach (explode(',',$response['writable_paths']) as $p) {
						$path = Mage::getBaseDir('base') . DS . $p;
						
						if(!is_writable($path) && !@chmod($path,777)) {
							// This path was not writable
							$unwritablePaths[] = $path;
						}
					}
				}
				
				// If there were any paths which weren't writable throw a new exception
				if(!empty($unwritablePaths)) {
					throw new Exception(Mage::helper('cem')->__("Unable to install extension, the following paths needs to be writable: \n\n".implode("\n\n",$unwritablePaths)));
				}
				
				// 11. Check if a package of the same type as the retrieved module already exists
				$moduleExists = $this->getCollection()
					->setModuleFilter($response['module_id']);
				
				$data = $moduleExists->getSize()>0 ? $moduleExists->getFirstItem()->getData() : null;	
				
				if($data) {
					$updatePackageId = $data['package_id'];
				}

				// 12. Check if the package is already installed
				if(Mage::getResourceModel('cem/packages')->packageIsInstalled($packageIdentifier)) {
					// Get package
					$package = $this->getCollection()
						->setIdentifierFilter($packageIdentifier)
						->getFirstItem();
						
					// Save auto update option
					$this->load($package->getPackageId())
							->setAutoUpdate($autoUpdate)
							->save();
					
					// throw exception
					throw new Exception(Mage::helper('cem')->__("This package is already installed"));
				}

				// 13. Get local download directory
				if(!$dir = $this->getDownloadDirectory()) {
					throw new Exception(Mage::helper('cem')->__("Unable to create / locate temp download directory. Your Magento folder might not have sufficient write permissions, which this web based downloader requires. Please make sure that your permissions are correct and try again. If you need further assistance, please contact your server administrator."));
				}
				
				// 14. Make sure that the download directory is writable
				if(!is_writable($dir)) {
					throw new Exception(Mage::helper('cem')->__("Unable to install the requested extension. Your var/tmp directory needs to be writable. If you are not sure how to make the directory writable we suggest that you contact your webadministrator for technical support."));
				}
				
				// 15. Save package information if package doesn't exists
				if(empty($updatePackageId)) {
					$this->setLicenseId($licenseModel->getLicenseId())
						->setServiceId($serviceModel->getServiceId())
						->setIdentifier($response['identifier'])
						->setTitle($response['title'])
						->setAutoUpdate($autoUpdate)
						->setModuleId($response['module_id'])
						->setVersion($response['version'])
						->save();	
				}

				// 16. Download remote package
				//@nelkaake Added on Wednesday July 14, 2010: 
	   			$file = Mage::helper('cem/dl')->file_get_contents($response['url']);
	   			
	   			$tmpFile = $dir . DS . $packageIdentifier . '.tar.gz';
	
	   			if($fp = fopen($tmpFile,'w')) {
			      	fwrite($fp,$file);
				}
				else {
					throw new Exception(Mage::helper('cem')->__("An error occurred while completing the download of the source package file. Your Magento folder might not have sufficient write permissions, which this web based downloader requires. Please make sure that your permissions are correct and try again. If you need further assistance, please contact your server administrator."));
				}
				
				fclose($fp);
				
				// 17. Install downloaded package
				
				// Make sure the PEAR class exists, otherwise we can't extract nothing
				if(!@class_exists("PEAR")) {
					throw new Exception("PEAR has not been loaded. Unable to extract installation file.");
				}
				
				// Make sure the Archive_Tar class exists, otherwise we can't extract nothing
				if(!@class_exists("Archive_Tar")) {
					throw new Exception("Archive_Tar has not been loaded. Unable to extract installation file.");
				}
				
				if($tar = new Archive_Tar($tmpFile,true)) {
					if(!$tar->extract(Mage::getBaseDir('base'))) {
						throw new Exception(Mage::helper('cem')->__("Unable to install package. Your Magento folder might not have sufficient write permissions, which this web based downloader requires. Please make sure that your permissions are correct and try again. If you need further assistance, please contact your server administrator."));
					}	
				}
				else {
					throw new Exception(Mage::helper('cem')->__("Unable to install package. Your Magento folder might not have sufficient write permissions, which this web based downloader requires. Please make sure that your permissions are correct and try again. If you need further assistance, please contact your server administrator."));
				}

				// 18. Update package information if it already exists
				if(!empty($updatePackageId)) {
					$packageModel = $this->load($updatePackageId);
					$packageModel->setTitle($response['title'])
						->setIdentifierRollback($packageModel->getIdentifier())
						->setIdentifier($response['identifier'])
						->setVersion($response['version'])
						->setAutoUpdate($autoUpdate)
						->setUpdateAvailable(0)
						->setLastUpdate(date("Y-m-d H:i:s",time()))
						->save();	
				}
				
				// 19. Report the successfull installation to the repository
				$installCompleted = Mage::getModel('cem/soap')->call(
					'installCompleted',
					array(
						'licenseKey' => $licenseKey
					),
					$serviceUrl
				);
				
				// 20. Clear the cache
				if(!Mage::app()->cleanCache()) {
					throw new Exception(Mage::helper('cem')->__("Unable to refresh cache, please refresh the cache manually."));
				}
				
				// 21. Delete local install file
				if(!@unlink($tmpFile)) {
					throw new Exception(Mage::helper('cem')->__("The installation was successfull but unable to cleanup installation files. Please make sure that your Magento directory has read and write permissions to avoid this problem in the future."));
				}

				// 22. Installation complete
				return true;
			}
		}
		catch (Exception $e) {
			// Clean the database tables
			if($newLicenseKey) {
				$licenseModel->delete();
			}
			
			// Clean the database tables
			/*if($newService) {
				$serviceModel->delete();
			}*/

			// Delete temporary downloaded file if any
			if(!empty($tmpFile)) {
				@unlink($tmpFile);
			}

			// Error
			Mage::unregister('cem_error');
			Mage::register('cem_error',$e->getMessage());
			return;
		}
	}
	
	
	/**
	 * Get information on the latest package in a module
	 *
	 * @param string $serviceUrl
	 * @param int $moduleId
	 * @return array
	 */
	public function getLatestVersion( $serviceUrl, $moduleId )
	{
		try {
			// Retrieve package information from service repository
			$call = Mage::getModel('cem/soap')->call(
				'getLatestVersion',
				array(
					'moduleId' => $moduleId
				),
				$serviceUrl
			);

			$response = $call->getResults();
			
			// Check for soap errors
			if(is_null($response)) {
				if(trim($call->getErrorMessage())=='') {
					throw new Exception("An unknown SOAP error occurred while gathering module information. Please contact your retailer for assistance.");
				}
				throw new Exception(Mage::helper('cem')->__($call->getErrorMessage()));
			}
			
			return $response[0];
		}
		catch (Exception $e) {
			// Error
			Mage::unregister('cem_error');
			Mage::register('cem_error',$e->getMessage());
			return;
		}
	}
	
	
	/**
	 * Get download directory path
	 *
	 * @return string
	 */
	public function getDownloadDirectory()
	{
		if(is_null($this->_downloadDir)) {
			// Dir
			$dir = Mage::getBaseDir('base') . DS . 'var/tmp';
			
			// Does dir exist
			if(!is_dir($dir)) {
				// Attempt to create it
				mkdir($dir,0777);
			}
			
			$this->_downloadDir = $dir;
		}

		return $this->_downloadDir;
	}
	
	
	/**
	 * Set package id
	 * 
	 * @param int $packageId
	 * @param boolean $updates
	 */
	public function hasUpdates( $packageId, $updates )
	{
		Mage::getModel('cem/packages')
			->load($packageId)
			->setUpdateAvailable($updates)
			->save();
	}
	
	
	/**
	 * Update a list of packages
	 *
	 * @param MageParts_CEM_Model_Mysql4_Packages_Collection $collection
	 * @param boolean $force
	 * @return int $packagesUpToDate
	 */
	public function update( MageParts_CEM_Model_Mysql4_Packages_Collection $collection, $force=false )
	{
		$packagesUpToDate = 0;
		
		foreach ($collection as $p) {
			$latest = $this->getLatestVersion($p->getUrl(),$p->getModuleId());

			if($latest['version'] > $p->getVersion()) {
				if($p->getAutoUpdate() || $force) {
					if($this->install($latest['identifier'],$p->getLicenseKey(),$p->getUrl(),$p->getAutoUpdate(),$p->getPackageId())) {
						$packagesUpToDate++;
					}
					else {
						$this->hasUpdates($p->getPackageId(),true);
						$packagesUpToDate++;
					}
				}
				else {
					$this->hasUpdates($p->getPackageId(),true);
					$packagesUpToDate++;
				}
			}
		}

		return $packagesUpToDate;
	}
	
	
	/**
	 * Check if there is a CEM email in the config already
	 */
	public function checkCemEmail()
	{
		$email = Mage::getStoreConfig('system/cem/email');

		if(!empty($email)) {
			return true;
		}
		else {
			return false;
		}
	}
	
}