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

class MageParts_CEM_Model_Mysql4_Services extends Mage_Core_Model_Mysql4_Abstract
{

	/**
	 * Constructor
	 */
    protected function _construct()
    {
        $this->_init('cem/services', 'service_id');
    }
        
    
    /**
     * Check if a service url already exists
     *
     * @param string $url
     * @return array
     */
   	public function urlExists( $url )
	{
		// Read adapter
		$read = $this->_getReadAdapter();

        // Select
        $select = $read->select()
        	->from($this->getMainTable())
        	->where("url='{$url}'")
        	->limit(1);

        // Fetch row
        $row = $read->fetchRow($select);
        
        if(isset($row['service_id']) && !empty($row['service_id'])) {
        	return $row['service_id'];
        }
        
        return false;
	}
	
	
    /**
     * Retrieve CEM key
     *
     * @param int $serviceId
     * @return string
     */
   	public function getCemKey( $serviceId )
	{
		// Read adapter
		$read = $this->_getReadAdapter();

        // Select
        $select = $read->select()
        	->from($this->getTable("cem/service_keys"))
        	->where("service_id = ?",$serviceId)
        	->limit(1);

        // Fetch row
        $row = $read->fetchRow($select);
        
        if(isset($row['key']) && !empty($row['key'])) {
        	return $row['key'];
        }
        
        return false;
	}
        
    
    /**
     * Retrieve CEM key
     *
     * @param array $data
     * @return boolean
     */
   	public function addCemKey( array $data )
	{
		// Read adapter
		$read = $this->_getReadAdapter();

        // Select
        $select = $read->select()
        	->from($this->getTable("cem/service_keys"))
        	->where("service_id = ?",$data['service_id'])
        	->limit(1);

        // Fetch row
        $row = $read->fetchRow($select);
        
        if($data) {
			if(isset($row['key'])) {
				if($this->_getWriteAdapter()->update($this->getTable('cem/service_keys'), $data, "service_id={$data['service_id']}")) {
					return true;
				}
			}
			else {
				if($this->_getWriteAdapter()->insert($this->getTable('cem/service_keys'), $data)) {
					return true;
				}
			}
        }
		
		return false;
	}

}