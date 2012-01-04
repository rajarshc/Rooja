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
 * Debug for customer points indexer  
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */

require_once dirname(__FILE__) . '../AbstractController.php';

class TBT_Rewards_Debug_CustomerpointsController extends TBT_Rewards_Debug_AbstractController
{
    
    /**
     * Additional check for customer indexer data integrity
     * @name checkCustomerPointsAction
     * @access public
     * @return void
     */
    public function checkcustomerpointsAction()
    {
        
        $database = Mage::getSingleton('core/resource')->getConnection('core_read');
        $customerCollection = Mage::getModel('rewards/customer')
                                ->getCollection()
                                ->setPageSize(100)
                                ->setCurPage(1)
                                ->load();
        
        $customerPoints = array();
        $customerIds = array();
        // Fetch regular way to collect usable points
        foreach($customerCollection as $customer) {
            $customer = Mage::getModel('rewards/customer')->load($customer->getId());
            $customerPoints[] = array(
                'customer_id' => $customer->getId(), 
		'customer_points_usable' => array_sum($customer->getRealUsablePoints()) 
            );
            $customerIds[] = $customer->getId();
        }
        
        // Fetch database 
        $where = $database->quoteInto('`customer_id` IN (?)', $customerIds);
        $select = $database
                    ->select()
                    ->from(Mage::getSingleton('core/resource')->getTableName('rewards_customer_index_points'))
                    ->where($where);
        
        $customerIndexedPoints = $database->fetchAll($select);

        // Checks array sizes
        if(count($customerIndexedPoints) != count($customerPoints)) {
            echo "Difference in points collection count.";
            exit;
        }
        
        for($i = 0; $i < count($customerIndexedPoints); $i++) {
            $customerIndexedPointsVal = $customerIndexedPoints[$i];
            $customerPointsVal = $customerPoints[$i];
            if($customerPointsVal['customer_id'] != $customerIndexedPointsVal['customer_id'] || $customerPointsVal['customer_points_usable'] != $customerIndexedPointsVal['customer_points_usable']) {
                echo "Error:\n\n";
                echo "Regular Points:\n";
                print_r($customerPointsVal);
                echo "Indexed Points:\n";
                print_r($customerIndexedPointsVal);
                exit;
            }
        }
            
        echo "OK: No diff between point collections.";
        exit;
    }
    
}