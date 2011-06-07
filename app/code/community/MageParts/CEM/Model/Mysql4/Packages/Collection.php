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

class MageParts_CEM_Model_Mysql4_Packages_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

	/**
	 * Constructor
	 */
    protected function _construct()
    {
        $this->_init('cem/packages');
    }
    
    
    /**
     * Filter by package identifier
     *
     * @param string $identifier [optional]
     * @return MageParts_CEM_Model_Mysql4_Packages_Collection
     */
    public function setIdentifierFilter( $identifier='' )
    {
    	if(!empty($identifier)) {
    		$this->getSelect()->where('identifier = ?',$identifier);
    	}
    	return $this;
    }
    
    
    /**
     * Filter by package id
     *
     * @param int $id [optional]
     * @return MageParts_CEM_Model_Mysql4_Packages_Collection
     */
    public function setIdFilter( $id )
    {
    	if(!empty($id)) {
    		$this->getSelect()->where('package_id= ?',$id);
    	}
    	return $this;
    }
    
    
    /**
     * Filter by package auto update option
     *
     * @param int $autoUpdate [optional]
     * @return MageParts_CEM_Model_Mysql4_Packages_Collection
     */
    public function setAutoUpdateFilter( $autoUpdate=1 )
    {
		$this->getSelect()->where('auto_update = ?',$autoUpdate);
    	return $this;
    }
    
    
    /**
     * Filter by package module
     *
     * @param int $moduleId [optional]
     * @return MageParts_CEM_Model_Mysql4_Packages_Collection
     */
    public function setModuleFilter( $module=0 )
    {
    	if(!empty($module)) {
    		$this->getSelect()->where('module_id = ?',$module);
    	}
    	return $this;
    }
    
    
    /**
     * Include service and license information
     */
    public function joinTables()
    {
    	$this->getSelect()->join(
            array('services' => $this->getTable('cem/services')),
            'main_table.service_id=services.service_id',
            '*'
        );
        
        $this->getSelect()->join(
            array('licenses' => $this->getTable('cem/licenses')),
            'main_table.license_id=licenses.license_id',
            '*'
        );
        
    	return $this;
    }

}
