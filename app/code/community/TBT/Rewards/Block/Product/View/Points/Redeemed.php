<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
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
 * Product View Points
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Product_View_Points_Redeemed extends TBT_Rewards_Block_Product_View_Points_Abstract
{
    const POINTS_RULE_ID = TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID;
    const POINTS_USES = TBT_Rewards_Model_Catalogrule_Rule::POINTS_USES;
    
    protected $_redeemedPoints = null;
    
    /**
     * Checks if the specified rule already has a redemption applied to the current quote.
     * @param int $applicableRuleId
     */
    public function isSelectedRule($applicableRuleId)
    {
        if (!$this->_loadRedemptionData()) {
            return false;
        }
        
        foreach ($this->_redeemedPoints as $redemption) {
            $redeemedRuleId = $redemption->{self::POINTS_RULE_ID};
            if ($redeemedRuleId == $applicableRuleId) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Gets an array of redemption 'uses' counts, grouped by Rule ID.
     */
    public function getRuleUses()
    {
        if (!$this->_loadRedemptionData()) {
            return array();
        }
        
        $ruleUses = array();
        foreach ($this->_redeemedPoints as $redemption) {
            $ruleUses[$redemption->{self::POINTS_RULE_ID}] = $redemption->{self::POINTS_USES};
        }
        
        return $ruleUses;
    }
    
    /**
     * Loads redemption data from the current quote based on the Item ID passed into the request.
     * Returns boolean, stating whether it succeeded in gathering redemption data.
     */
    protected function _loadRedemptionData()
    {
        if (!$this->_redeemedPoints) {
            $itemId = $this->getRequest()->getParam('id', null);
            if (!$itemId) {
                return false;
            }
            
            $item = $this->_getCart()->getQuote()->getItemById($itemId);
            if (!$item) {
                return false;
            }
            
            $this->_redeemedPoints = Mage::helper('rewards')->unhashIt($item->getRedeemedPointsHash());
        }
        
        return true;
    }
    
    /**
     * Returns a singleton of the current cart.
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }
}
