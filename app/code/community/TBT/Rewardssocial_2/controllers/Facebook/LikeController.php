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
 * @copyright  Copyright (c) 2011 WDCA (http://www.wdca.ca)
 */

/**
 * Customer Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewardssocial_Facebook_LikeController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {

        if (Mage::getConfig()->getModuleConfig('TBT_Rewards')->is('active', 'false')) {
            throw new Exception(Mage::helper('rewardssocial')->__("Sweet Tooth must be installed on the server in order to use the Sweet Tooth Social system."));
        }
        die(Mage::helper('rewardssocial')->__("If you're seeing this page it confirms that Sweet Tooth is installed and the Sweet Tooth Social system is ready for use."));

        return $this;
    }

    /**
     * For liking and unliking products
     */
    public function onLikeAction() {
        try {
            $action = $this->getRequest()->getParam('action', 'unlike');
            $page_key = $this->getRequest()->getParam('page_key');
            
            // Check if customer is not logged in display a message and don't do any liking actions
            if ( ! $this->_rs()->isCustomerLoggedIn() ) {
                if ( $action == 'like' ) {
                    $this->_cs()->addError(//The long msg: $this->__('You were not logged-in to the the store, so we could not reward you for your Facebook mention. Please log-in to the store or create an account, then try again.')
                        $this->__('Please [login_link]log-in or create an account[/login_link] to earn Facebook points.')
                    );
                } else {
                    $this->_cs()->addError(
                        $this->__('Please [login_link]log-in to the store or create an account[/login_link], then try again.')
                    );
                }
                
            } else {
                // Pull variables from the request
                $liked_url = Mage::helper('rewardssocial/crypt')->decrypt($page_key); //encryption allows us to protect against programatic LIKING
                $facebook_account_id = - 1; // until we can get the facebook ID let's use -1
                $customer = $this->_rs()->getCustomer();
                
                if ( $action == 'like' ) {
                    $this->_getFacebookLikeValidator()->initReward($facebook_account_id, $liked_url, $customer);
                } elseif ( $action == 'unlike' ) {
                    $this->_getFacebookLikeValidator()->cancelLikeRewards($facebook_account_id, $liked_url, $customer);
                } else {
                    // Do nothing because the action specified was invalid.
                    Mage::helper('rewards/debug')->log( "Invalid Facebook LIKE action specified '{$action}' in TBT_Rewardssocial_Facebook_LikeController::onLikeAction(). Customer was not rewarded and facebook LIKE was not acknowledge by rewards system.");
                }
            }
            
        
        } catch ( Exception $e ) {
            Mage::helper('rewards/debug')->error((string) $e); // log the error
            $this->_cs()->addError($e->getMessage());
        }
        
        $this->getResponse()->setBody($this->_getSimpleMsgResponseHtml());
        
        return $this;
    }
    
    protected function _getSimpleMsgResponseHtml() {
        
         $result = array();
         $result [Mage_Core_Model_Message::ERROR] = array();
         $result [Mage_Core_Model_Message::NOTICE] = array();
         $result [Mage_Core_Model_Message::SUCCESS] = array();
         $result [Mage_Core_Model_Message::WARNING] = array();
         
         $all_msgs = $this->_cs()->getMessages(true)->getItems();
         
         foreach( $all_msgs  as $msg ) {
             $response_block = Mage::getBlockSingleton('rewardssocial/facebook_like_notificationblock_response');
             $response_block->setMsg($msg);
             $response_html = $response_block->toHtml();
             
             return $response_html;
         }
         
         return "";
    }
    
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }
    
    
    
    /**
     * @return TBT_Rewardssocial_Model_Facebook_Like_Validator
     */
    protected function _getFacebookLikeValidator() {
        return Mage::getSingleton('rewardssocial/facebook_like_validator');
    }
    /**
     * @return TBT_Rewards_Model_Session
     */
    protected function _rs() {
        return Mage::getSingleton('rewards/session');
    }
    

    /**
     * @return Mage_Core_Model_Session
     */
    protected function _cs() {
        return Mage::getSingleton('core/session');
    }
    
}