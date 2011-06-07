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

class MageParts_CEM_Model_Observer
{
	
	const XML_FREQUENCY_PATH = 'system/cem/frequency';
	
    public function checkUpdate( $schedule )
    {
    	if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }
        
		// Get package module
		$packageModel = Mage::getModel('cem/packages');
		
		// Get package collection
		$packages = $packageModel->getCollection()
			->joinTables();
	
		// Update packages
		$update = $packageModel->update($packages);
		
		$this->setLastUpdate();
		
		return $this;
    }
    
    
    /**
     * Retrieve Update Frequency
     *
     * @return int
     */
    public function getFrequency()
    {
        return Mage::getStoreConfig(self::XML_FREQUENCY_PATH) * 3600;
    }

    
    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return Mage::app()->loadCache('cem_update_lastcheck');
    }

    
    /**
     * Set last update time (now)
     *
     * @return MageParts_CEM_Model_Observer
     */
    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'cem_update_lastcheck');
        return $this;
    }
    
}
