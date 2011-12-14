<?php

/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
//class TBT_Rewards_Block_Html_Header extends Mage_Core_Block_Template
//{
//	
//    public function _construct()
//    {
//        parent::_construct();
//    }
//    
//    /**
//     * This modifies the default welcome message at the top of the store to display your current point amount
//     *
//     * @return string
//     */
//    public function getWelcome()
//    {
//    	die("test"); //not being reached
//    	$welcomeMessage = parent::getWelcome();
//    	if(Mage::isInstalled() && Mage::getSingleton('customer/session')->isLoggedIn()){
//    	   $welcomeMessage = $welcomeMessage . "<BR /> Test";
//    	}		
//
//    	return $welcomeMessage;
//    	
//    	
//    }
//} 