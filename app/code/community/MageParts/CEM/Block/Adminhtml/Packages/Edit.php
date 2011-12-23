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

class MageParts_CEM_Block_Adminhtml_Packages_Edit extends Mage_Adminhtml_Block_Template
{
	
	/**
	 * Constructor
	 */
    public function __construct()
    {
        $this->_controller  = 'adminhtml_packages';
        $this->_blockGroup 	= 'cem';
        
        parent::__construct();
        
        $this->setTemplate('cem/packages/install.phtml');
    }

    
    public function getHeaderText()
    {
        $package = Mage::registry('cem_package');
        if ($package->getPackageId()) {
            return Mage::helper('cem')->__("Update Extension '%s'", $this->htmlEscape($package->getIdentifier()));
        }
        else {
            return Mage::helper('cem')->__('Install New Extension');
        }
    }
    
    
    /**
     * Retrieve install url
     */
    public function getInstallUrl()
    {
    	return $this->getUrl('*/*/install');
    }
           
    
    /**
     * Retrieve update url
     */
    public function getUpdateUrl()
    {
    	return $this->getUrl('*/*/update');
    }
   

    /**
     * Retrieve package id
     */
    public function getPackageId()
    {
    	return Mage::registry('cem_package')->getPackageId();
    }
    
    
    /**
     * Retrieve package service url
     */
    public function getPackageServiceUrl()
    {
    	return Mage::getModel('cem/services')
    		->load(Mage::registry('cem_package')->getServiceId())
    		->getUrl();
    }
    
    
    /**
     * Retrieve package license key
     */
    public function getPackageLicenseKey()
    {
    	return Mage::getModel('cem/licenses')
    		->load(Mage::registry('cem_package')->getLicenseId())
    		->getLicenseKey();
    }
    
    
    /**
     * Retrieve package identifier
     */
    public function getPackageIdentifier()
    {
    	return Mage::registry('cem_package')->getIdentifier();
    }
    
    
    /**
     * Retrieve rollback identifier
     */
    public function getIdentifierRollback()
    {
    	return Mage::registry('cem_package')->getIdentifierRollback();
    }
        
        
    /**
     * Get package auto update
     */
    public function getPackageAutoUpdate()
    {
    	return Mage::registry('cem_package')->getAutoUpdate();
	}
    
}