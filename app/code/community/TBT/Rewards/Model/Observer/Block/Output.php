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
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Observer_Block_Output extends Varien_Object {
	
	/**
	 * Executed from the core_block_abstract_to_html_after event
	 * @param Varien_Event $obj
	 */
	public function afterOutput($obj) {
		$block = $obj->getEvent ()->getBlock ();
		$transport = $obj->getEvent ()->getTransport ();
		
		// Magento 1.3 and lower dont have this transport, so we can't do autointegration : (
		if(empty($transport)) {
			return $this;
		}
		
		$this->appendRewardsHeader ( $block, $transport );
		$this->appendCartPointsSpender ( $block, $transport );
		$this->appendPointsSummary ( $block, $transport );
		$this->appendToCatalogListing ( $block, $transport );
		
		return $this;
	}
	
	/**
	 * Appends the points balance in the header somewhere
	 * @param unknown_type $block
	 * @param unknown_type $transport
	 */
	public function appendRewardsHeader($block, $transport) {
	
        if(!Mage::getStoreConfigFlag('rewards/autointegration/header_points_balance')) {
            return $this;
        }
        
		if ($block->getBlockAlias () == 'topLinks') {
			$orignal_html = $transport->getHtml ();
			$st_html = $block->getChildHtml ( 'rewards_points_balance' );
			$st_html .= $block->getChildHtml ( 'cart_points_js' );
			$new_html = $st_html . $orignal_html;
			$transport->setHtml ( $new_html );
		}
		
		return $this;
	}
	
	/**
	 * Append the shopping cart points spender box in the shopping box
	 * @param unknown_type $block
	 * @param unknown_type $transport
	 */
	public function appendCartPointsSpender($block, $transport) {
	
        if(!Mage::getStoreConfigFlag('rewards/autointegration/shopping_cart_under_coupon')) {
            return "";
        }
        
		if ($block->getBlockAlias () == 'coupon' && $block->getChild ( 'rewards_cartpoints_spender' )) {
			$orignal_html = $transport->getHtml ();
			$st_html = $block->getChildHtml ( 'rewards_cartpoints_spender_js' );
			$st_html = $block->getChildHtml ( 'rewards_cartpoints_spender' );
			$new_html = $st_html . $orignal_html;
			$transport->setHtml ( $new_html );
		}
		
		return $this;
	}
	
	/**
	 * Append the points summary message in the dashboard.
	 * @param unknown_type $block
	 * @param unknown_type $transport
	 */
	public function appendPointsSummary($block, $transport) {
        if(!Mage::getStoreConfigFlag('rewards/autointegration/customer_dashboard_summary')) {
            return $this;
        }
        
		if ($block->getBlockAlias () == 'top' && $block->getChild ( 'rewards_points_summary' )) {
			$orignal_html = $transport->getHtml ();
			$st_html = $block->getChildHtml ( 'rewards_points_summary' );
			$new_html = $st_html . $orignal_html;
			$transport->setHtml ( $new_html );
		}
		
		return $this;
	}
	
	
	/**
	 * Appends the points balance in the header somewhere
	 * @param Mage_Catalog_Block_Product_List $block
	 * @param Varien_Object $transport
	 */
	public function appendToCatalogListing($block, $transport) {
        // Should we be checking this auto-integration?
        if(!Mage::getStoreConfigFlag('rewards/autointegration/product_listing')) {
            return $this;
        }
        
	    // Block is a price block.
		if(!( $block instanceof Mage_Catalog_Block_Product_List )) {
            return $this;
        }
        
		
		$all_products = $block->getLoadedProductCollection();
		
		$html = $transport->getHtml ();
		
		$html = $this->_getNewCatalogListingHtml($html, $all_products);
		
	    $transport->setHtml ( $html );
		
		return $this;
		
	}
	
	/**
	 * 
	 * @param string $html
	 * @param Mage_Eav_Model_Entity_Collection_Abstract $all_products
	 */
	protected function _getNewCatalogListingHtml($html, $all_products) {
	    
	    $is_list_mode_display = strpos($html, 'class="products-list" id="products-list">') !== false;
	    
		foreach($all_products as $_product) {
    		$product_id = $_product->getId();
		    
    		$predict_points_block = Mage::getBlockSingleton('rewards/product_predictpoints');
        	$predict_points_block->setProduct($_product); 
        	$st_html = $predict_points_block->toHtml();
    		
    		//  If no content, dont integrate
    		if(empty($st_html)) {
    		    continue;
    	    }
		
    		// Check that content is not already integrated.
    		if(strpos($html, $st_html) !== false) {
    		    continue; 
    	    }
    	    
    		
    		$replaced_html = null;
    		if(Mage::helper('rewards/version')->isMageEnterprise()){
                $pattern = '/(<button )[^>]*(product\/'.  $product_id  .'\/)(.*)(<\/button>)/isU';
    		} else {
    		    if($is_list_mode_display) {
                    $pattern = '/(<ul class="add-to-links">)((\s)*)(<li>)((\s)*)(<a href=")[^>]*(product\/'.  $product_id  .'\/)(.*)(<\/a>)(.*)(<\/li>)((\s)*)(<\/ul>)/isU';
    		    } else {
                    $pattern = '/(<div class="actions">)((\s)*)(<button )[^>]*(product\/'.  $product_id  .'\/)(.*)(<\/button>)(.*)(<\/div>)/isU';
    		    }
    		}
    		
            $replacement = $st_html.'${0}';
            $replaced_html = preg_replace($pattern, $replacement, $html);
    		
            if(!empty($replaced_html)) {
                $html = $replaced_html;  
            }
            
		}
	    return $html;
		
	}
	
	
}