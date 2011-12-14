<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
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
 * Diagnostics controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Manage_DiagnosticsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * This controller action will remove the database install entry from the Magento
     * core_resource table. This in turn will force Magento to re-install the database scripts.
     */
    public function reinstalldbAction() {
    	
    	echo "Deleting core_resource table entries that have the code 'rewards_setup'...";
    	flush();
    	
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $conn->beginTransaction();
        
        
        $this->_clearDbInstallMemory($conn, 'rewards_setup');
        $this->_clearDbInstallMemory($conn, 'rewardssocial_setup');
        
    	echo "Done.";
    	flush();
    	
        $conn->commit();
        
    	echo "<br><br>\n"
    		."<a href='". $this->getUrl('adminhtml/notification') . "'>CLICK HERE</a> "
    		."to go back to the dashboard and module will retun it's own database install scripts over again ";
    	
    	exit;
    	
    }
      
    public function _clearDbInstallMemory($conn, $code) {
    	
        $table_prefix = Mage::getConfig()->getTablePrefix() ;
        
        $conn->query("
			DELETE FROM    `{$table_prefix}core_resource`  
			WHERE    `code` = '{$code}'
			;
        ");
        echo "Resource DB for {$code} has been cleared";
        
        return $this;
    }
     
    
}
?>