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

class MageParts_CEM_Model_Mysql4_Packages extends Mage_Core_Model_Mysql4_Abstract
{

	/**
	 * Constructor
	 */
    protected function _construct()
    {
        $this->_init('cem/packages', 'package_id');
    }
        
    
    /**
     * Check if a license key already has been used
     *
     * @param int $packageId [optional]
     * @return array
     */
   	public function licenseUsed( $licenseId, $packageId=0 )
	{
		// Read adapter
		$read = $this->_getReadAdapter();

        // Select
        $select = $read->select()
        	->from($this->getMainTable())
        	->where("license_id=?",$licenseId)
        	->limit(1);
        	
        // Package exception
        if(!empty($packageId)) {
        	$select->where("package_id != ?",$packageId);
        }
        	
        // Fetch row
        $row = $read->fetchRow($select);

        if(isset($row['package_id']) && !empty($row['package_id'])) {
        	return true;
        }
        
		return false;
	}
        
    
    /**
     * Check if a package already is installed
     *
     * @param string $identifier
     * @return array
     */
   	public function	packageIsInstalled( $identifier )
	{
		// Read adapter
		$read = $this->_getReadAdapter();

        // Select
        $select = $read->select()
        	->from($this->getMainTable())
        	->where("identifier='{$identifier}'")
        	->limit(1);

        // Fetch row
        $row = $read->fetchRow($select);
        
        if(isset($row['package_id']) && !empty($row['package_id'])) {
        	return true;
        }
        
        return false;
	}

}